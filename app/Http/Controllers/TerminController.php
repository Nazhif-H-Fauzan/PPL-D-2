<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTerminsRequest;
use App\Models\Termin;
use App\Services\D1ProjectServiceClient;
use App\Services\D1ServiceUnavailableException;
use Exception;
use Illuminate\Http\JsonResponse;

class TerminController extends Controller
{
    /**
     * API-K02-02: Menampilkan daftar termin siap ditagih beserta status PO dari D-1.
     */
    public function index(GetTerminsRequest $request, D1ProjectServiceClient $d1Client): JsonResponse
    {
        $status = $request->query('status', 'SIAP_DITAGIH');
        $projectId = $request->query('projectId');
        $page = (int) $request->query('page', 1);
        $pageSize = (int) $request->query('pageSize', 20);

        // Jika filter projectId spesifik dikirimkan, validasi keberadaan proyek di Modul D-1
        if (!empty($projectId)) {
            try {
                $project = $d1Client->getProject($projectId);
                if ($project === null) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 'PROJECT_NOT_FOUND',
                        'message' => "Proyek dengan ID '{$projectId}' tidak ditemukan di modul D-1",
                    ], 404);
                }
            } catch (D1ServiceUnavailableException $e) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'DEPENDENCY_UNAVAILABLE',
                    'message' => 'Layanan eksternal D-1 tidak dapat dihubungi saat memvalidasi proyek',
                ], 503);
            }
        }

        // Query termin dengan relasi kontrak
        $query = Termin::with('contract')->orderBy('termin_number', 'asc');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($projectId)) {
            $query->whereHas('contract', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        }

        $totalItems = $query->count();
        $termins = $query->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        // Resolusi status PO dari D-1 untuk setiap proyek (dengan memoization lokal)
        $projectPoCache = [];
        $items = $termins->map(function ($termin) use ($d1Client, &$projectPoCache) {
            $pId = $termin->contract->project_id ?? null;
            $poStatus = null;

            if ($pId) {
                if (!array_key_exists($pId, $projectPoCache)) {
                    try {
                        $pData = $d1Client->getProject($pId);
                        $projectPoCache[$pId] = $pData['poStatus'] ?? null;
                    } catch (Exception $e) {
                        $projectPoCache[$pId] = null; // null jika D-1 tidak dapat dihubungi sesuai spesifikasi OpenAPI
                    }
                }
                $poStatus = $projectPoCache[$pId];
            }

            return [
                'terminId' => $termin->termin_id,
                'terminNumber' => (int) $termin->termin_number,
                'terminValue' => number_format((float) $termin->termin_value, 2, '.', ''),
                'dueDate' => $termin->due_date ? $termin->due_date->format('Y-m-d') : null,
                'status' => $termin->status,
                'contractId' => $termin->contract_id,
                'contractNumber' => $termin->contract->contract_number ?? '',
                'projectId' => $pId,
                'poStatus' => $poStatus,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar termin siap ditagih berhasil dimuat',
            'data' => [
                'items' => $items,
                'page' => $page,
                'pageSize' => $pageSize,
                'totalItems' => $totalItems,
            ],
        ], 200);
    }
}
