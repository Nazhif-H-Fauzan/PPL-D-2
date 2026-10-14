<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Modul D-2 (Kontrak Komersial, Termin, & Invoice)
|--------------------------------------------------------------------------
| Kelompok: 2 / D (PT Anaheim Nimbus Universal ERP)
| Berdasarkan Spesifikasi OpenAPI v0.1.0 (Pertemuan 3)
*/

Route::prefix('v1')->group(function () {

    // API-K02-02: Menampilkan daftar termin siap ditagih beserta status PO
    Route::get('/termins', function (Request $request) {
        $status = $request->query('status', 'SIAP_DITAGIH');
        $projectId = $request->query('projectId');
        $page = (int) $request->query('page', 1);
        $pageSize = (int) $request->query('pageSize', 20);

        $mockData = [
            [
                'terminId' => 'trm-demo-001',
                'terminNumber' => 1,
                'terminValue' => '100000000.00',
                'dueDate' => '2026-10-15',
                'status' => 'SIAP_DITAGIH',
                'contractId' => 'ktr-demo-001',
                'contractNumber' => 'KTR/2026/001',
                'projectId' => 'prj-demo-001',
                'poStatus' => 'PO_ADA',
            ],
            [
                'terminId' => 'trm-demo-002',
                'terminNumber' => 2,
                'terminValue' => '150000000.00',
                'dueDate' => '2026-12-15',
                'status' => 'SIAP_DITAGIH',
                'contractId' => 'ktr-demo-001',
                'contractNumber' => 'KTR/2026/001',
                'projectId' => 'prj-demo-001',
                'poStatus' => 'PO_ADA',
            ],
            [
                'terminId' => 'trm-demo-003',
                'terminNumber' => 1,
                'terminValue' => '80000000.00',
                'dueDate' => '2026-10-20',
                'status' => 'SIAP_DITAGIH',
                'contractId' => 'ktr-demo-004',
                'contractNumber' => 'KTR/2026/004',
                'projectId' => 'prj-demo-002',
                'poStatus' => 'PO_TIDAK_ADA',
            ],
        ];

        // Filter sederhana
        $filtered = array_filter($mockData, function ($item) use ($status, $projectId) {
            $matchStatus = empty($status) || $item['status'] === $status;
            $matchProject = empty($projectId) || $item['projectId'] === $projectId;
            return $matchStatus && $matchProject;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar termin siap ditagih berhasil dimuat',
            'data' => [
                'items' => array_values($filtered),
                'page' => $page,
                'pageSize' => $pageSize,
                'totalItems' => count($filtered),
            ],
        ], 200);
    });

    // API-K02-03: Menerbitkan invoice dari termin siap ditagih
    Route::post('/invoices', function (Request $request) {
        $terminId = $request->input('terminId');
        $invoiceDate = $request->input('invoiceDate');
        $dppAmount = $request->input('dppAmount');

        // Validasi input dasar
        if (empty($terminId) || empty($invoiceDate) || empty($dppAmount)) {
            return response()->json([
                'status' => 'error',
                'code' => 'VALIDATION_ERROR',
                'message' => 'Validasi masukan gagal',
                'errors' => [
                    'terminId' => empty($terminId) ? ['Termin ID wajib diisi'] : [],
                    'invoiceDate' => empty($invoiceDate) ? ['Tanggal invoice wajib diisi'] : [],
                    'dppAmount' => empty($dppAmount) ? ['DPP wajib diisi dan lebih dari 0'] : [],
                ],
            ], 400);
        }

        // Contoh simulasi penolakan jika PO belum ada (trm-demo-003)
        if ($terminId === 'trm-demo-003') {
            return response()->json([
                'status' => 'error',
                'code' => 'PO_NOT_FOUND_OR_BLOCKED',
                'message' => 'Penagihan diblokir: PO belum tersedia',
                'details' => [
                    'terminId' => 'trm-demo-003',
                    'projectId' => 'prj-demo-002',
                    'poStatus' => 'PO_TIDAK_ADA',
                    'reason' => 'Proyek terkait belum memiliki Purchase Order resmi yang disetujui di modul D-1.',
                ],
            ], 422);
        }

        // Perhitungan Pajak Otomatis (PPN 11%, PPh 2%)
        $dpp = (float) $dppAmount;
        $ppn = $dpp * 0.11;
        $pph = $dpp * 0.02;
        $total = $dpp + $ppn - $pph;

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice INV-2026-0001 berhasil dibuat',
            'data' => [
                'invoiceId' => 'inv-' . uniqid(),
                'invoiceNumber' => 'INV-2026-0001',
                'terminId' => $terminId,
                'invoiceDate' => $invoiceDate,
                'dppAmount' => number_format($dpp, 2, '.', ''),
                'ppnAmount' => number_format($ppn, 2, '.', ''),
                'pphAmount' => number_format($pph, 2, '.', ''),
                'totalAmount' => number_format($total, 2, '.', ''),
                'taxParamId' => 'pjk-demo-001',
                'status' => 'TERTAGIH',
                'createdBy' => 'emp-demo-012',
                'createdAt' => gmdate('Y-m-d\TH:i:s\Z'),
            ],
        ], 201);
    });

    // API-K02-05: Detail invoice
    Route::get('/invoices/{invoiceId}', function ($invoiceId) {
        return response()->json([
            'status' => 'success',
            'message' => 'Detail invoice berhasil dimuat',
            'data' => [
                'invoiceId' => $invoiceId,
                'invoiceNumber' => 'INV-2026-0001',
                'invoiceDate' => '2026-11-01',
                'dppAmount' => '100000000.00',
                'ppnAmount' => '11000000.00',
                'pphAmount' => '2000000.00',
                'totalAmount' => '109000000.00',
                'status' => 'TERTAGIH',
                'terkait' => [
                    'terminId' => 'trm-demo-001',
                    'terminNumber' => 1,
                    'contractId' => 'ktr-demo-001',
                    'contractNumber' => 'KTR/2026/001',
                    'projectId' => 'prj-demo-001',
                    'poStatus' => 'PO_ADA',
                ],
                'riwayat' => [
                    [
                        'status' => 'TERTAGIH',
                        'changedAt' => '2026-11-01T02:00:00Z',
                        'changedBy' => 'emp-demo-012',
                        'note' => 'Invoice diterbitkan oleh petugas keuangan',
                    ],
                ],
            ],
        ], 200);
    });

    // API-K02-04: Parameter pajak yang berlaku
    Route::get('/tax-parameters/applicable', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => [
                'taxParamId' => 'pjk-demo-001',
                'contractId' => null,
                'ppnRate' => '11.00',
                'pphRate' => '2.00',
                'effectiveFrom' => '2026-01-01',
                'effectiveTo' => null,
            ],
        ], 200);
    });
});
