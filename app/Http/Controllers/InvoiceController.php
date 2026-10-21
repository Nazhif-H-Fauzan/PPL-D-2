<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceHistory;
use App\Models\Termin;
use App\Services\D1ProjectServiceClient;
use App\Services\D1ServiceUnavailableException;
use App\Services\TaxCalculationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * API-K02-03: Menerbitkan invoice baru dari termin yang siap ditagih.
     */
    public function store(
        CreateInvoiceRequest $request,
        D1ProjectServiceClient $d1Client,
        TaxCalculationService $taxService
    ): JsonResponse {
        $terminId = $request->input('terminId');
        $invoiceDate = $request->input('invoiceDate');
        $dppAmount = (float) $request->input('dppAmount');

        // 1. Validasi Keberadaan Termin (404)
        $termin = Termin::with('contract')->find($terminId);
        if (!$termin) {
            return response()->json([
                'status' => 'error',
                'code' => 'TERMIN_NOT_FOUND',
                'message' => "Termin dengan ID '{$terminId}' tidak ditemukan",
            ], 404);
        }

        // 2. Validasi Status Termin & Pencegahan Duplikasi Penagihan (409)
        if ($termin->status !== 'SIAP_DITAGIH' || $termin->invoice()->exists()) {
            return response()->json([
                'status' => 'error',
                'code' => 'TERMIN_ALREADY_INVOICED',
                'message' => 'Termin ini sudah memiliki invoice atau belum berstatus siap ditagih',
            ], 409);
        }

        // 3. Validasi Eksternal ke Modul D-1 (PO Status & Klien Sementara)
        $projectId = $termin->contract->project_id ?? null;
        try {
            $project = $d1Client->getProject($projectId);
        } catch (D1ServiceUnavailableException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'DEPENDENCY_UNAVAILABLE',
                'message' => 'Layanan D-1 tidak dapat dihubungi saat pengecekan PO/klien. Tidak ada invoice yang tersimpan.',
            ], 503);
        }

        if (!$project) {
            return response()->json([
                'status' => 'error',
                'code' => 'PROJECT_NOT_FOUND',
                'message' => "Proyek '{$projectId}' tidak ditemukan di modul D-1",
            ], 422);
        }

        // Aturan Bisnis 1: Ketersediaan PO di Modul D-1 (422)
        if (empty($project['poReceived']) || $project['poStatus'] !== 'PO_ADA') {
            return response()->json([
                'status' => 'error',
                'code' => 'PO_NOT_FOUND_OR_BLOCKED',
                'message' => 'Penagihan diblokir: PO belum tersedia',
                'details' => [
                    'terminId' => $terminId,
                    'projectId' => $projectId,
                    'poStatus' => 'PO_TIDAK_ADA',
                    'reason' => 'Proyek terkait belum memiliki Purchase Order resmi yang disetujui di modul D-1.',
                ],
            ], 422);
        }

        // Aturan Bisnis 2: Klien Resmi vs Klien Sementara di Modul D-1 (422)
        if (!empty($project['isTemporaryClient'])) {
            return response()->json([
                'status' => 'error',
                'code' => 'TEMPORARY_CLIENT_BLOCKED',
                'message' => 'Penagihan diblokir: Klien proyek masih sementara',
                'details' => [
                    'terminId' => $terminId,
                    'projectId' => $projectId,
                    'isTemporaryClient' => true,
                    'reason' => 'Proyek masih berstatus klien sementara di modul D-1.',
                ],
            ], 422);
        }

        // 4. Perhitungan Pajak Otomatis berdasarkan Parameter Pajak Aktif pada invoiceDate
        $taxParam = $taxService->getApplicableTaxParameter($invoiceDate, $termin->contract_id);
        if (!$taxParam) {
            return response()->json([
                'status' => 'error',
                'code' => 'TAX_PARAM_NOT_FOUND',
                'message' => 'Tidak ada parameter pajak yang berlaku pada invoiceDate',
            ], 422);
        }

        $taxCalc = $taxService->calculate($dppAmount, $taxParam);

        // Identitas Petugas Keuangan dari context autentikasi
        $employeeId = $request->attributes->get('auth_employee_id', 'emp-demo-012');

        // Penomoran Invoice (Pola: INV-YYYY-XXXX)
        $year = substr($invoiceDate, 0, 4);
        $nextSeq = Invoice::whereYear('invoice_date', $year)->count() + 1;
        $invoiceNumber = sprintf('INV-%s-%04d', $year, $nextSeq);

        // ID Invoice unik (gunakan inv-demo-001 bila belum terpakai agar konsisten dengan OpenAPI example)
        $preferredId = 'inv-demo-001';
        $invoiceId = Invoice::where('invoice_id', $preferredId)->exists() ? 'inv-' . uniqid() : $preferredId;

        // 5. Eksekusi Transaksi Database (Atomic)
        $invoice = DB::transaction(function () use (
            $invoiceId,
            $invoiceNumber,
            $termin,
            $taxParam,
            $invoiceDate,
            $taxCalc,
            $employeeId
        ) {
            // A. Simpan data invoice
            $inv = Invoice::create([
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoiceNumber,
                'termin_id' => $termin->termin_id,
                'tax_param_id' => $taxParam->tax_param_id,
                'invoice_date' => $invoiceDate,
                'dpp_amount' => $taxCalc['dppAmount'],
                'ppn_amount' => $taxCalc['ppnAmount'],
                'pph_amount' => $taxCalc['pphAmount'],
                'total_amount' => $taxCalc['totalAmount'],
                'status' => 'TERTAGIH',
                'created_by' => $employeeId,
            ]);

            // B. Catat riwayat status penerbitan
            InvoiceHistory::create([
                'invoice_id' => $invoiceId,
                'status' => 'TERTAGIH',
                'changed_at' => Carbon::now('UTC'),
                'changed_by' => $employeeId,
                'note' => 'Invoice diterbitkan oleh petugas keuangan',
            ]);

            // C. Perubahan Status Otomatis: Termin SIAP_DITAGIH -> SUDAH_DITAGIHKAN
            $termin->update([
                'status' => 'SUDAH_DITAGIHKAN',
            ]);

            return $inv;
        });

        // 6. Respon HTTP 201 Created sesuai Skema CreateInvoiceResponse
        return response()->json([
            'status' => 'success',
            'message' => "Invoice {$invoice->invoice_number} berhasil dibuat",
            'data' => [
                'invoiceId' => $invoice->invoice_id,
                'invoiceNumber' => $invoice->invoice_number,
                'terminId' => $invoice->termin_id,
                'invoiceDate' => $invoice->invoice_date->format('Y-m-d'),
                'dppAmount' => number_format((float) $invoice->dpp_amount, 2, '.', ''),
                'ppnAmount' => number_format((float) $invoice->ppn_amount, 2, '.', ''),
                'pphAmount' => number_format((float) $invoice->pph_amount, 2, '.', ''),
                'totalAmount' => number_format((float) $invoice->total_amount, 2, '.', ''),
                'taxParamId' => $invoice->tax_param_id,
                'status' => $invoice->status,
                'createdBy' => $invoice->created_by,
                'createdAt' => $invoice->created_at->toISOString(),
            ],
        ], 201);
    }

    /**
     * API-K02-05: Mengambil detail lengkap invoice, status tagihan, dan keterkaitannya.
     */
    public function show(string $invoiceId, D1ProjectServiceClient $d1Client): JsonResponse
    {
        $invoice = Invoice::with(['termin.contract', 'histories'])->find($invoiceId);

        if (!$invoice) {
            return response()->json([
                'status' => 'error',
                'code' => 'INVOICE_NOT_FOUND',
                'message' => "Invoice dengan ID '{$invoiceId}' tidak ditemukan",
            ], 404);
        }

        $termin = $invoice->termin;
        $contract = $termin->contract ?? null;
        $projectId = $contract->project_id ?? null;

        $poStatus = null;
        if ($projectId) {
            try {
                $pData = $d1Client->getProject($projectId);
                $poStatus = $pData['poStatus'] ?? null;
            } catch (Exception $e) {
                $poStatus = null;
            }
        }

        $riwayat = $invoice->histories->map(function ($hist) {
            return [
                'status' => $hist->status,
                'changedAt' => $hist->changed_at ? $hist->changed_at->toISOString() : null,
                'changedBy' => $hist->changed_by,
                'note' => $hist->note,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Detail invoice berhasil dimuat',
            'data' => [
                'invoiceId' => $invoice->invoice_id,
                'invoiceNumber' => $invoice->invoice_number,
                'invoiceDate' => $invoice->invoice_date->format('Y-m-d'),
                'dppAmount' => number_format((float) $invoice->dpp_amount, 2, '.', ''),
                'ppnAmount' => number_format((float) $invoice->ppn_amount, 2, '.', ''),
                'pphAmount' => number_format((float) $invoice->pph_amount, 2, '.', ''),
                'totalAmount' => number_format((float) $invoice->total_amount, 2, '.', ''),
                'status' => $invoice->status,
                'terkait' => [
                    'terminId' => $termin->termin_id ?? '',
                    'terminNumber' => (int) ($termin->termin_number ?? 1),
                    'contractId' => $contract->contract_id ?? '',
                    'contractNumber' => $contract->contract_number ?? '',
                    'projectId' => $projectId ?? '',
                    'poStatus' => $poStatus,
                ],
                'riwayat' => $riwayat,
            ],
        ], 200);
    }
}
