<?php

namespace App\Http\Controllers;

use App\Services\TaxCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxParameterController extends Controller
{
    /**
     * API-K02-04: Mengambil parameter pajak yang berlaku.
     */
    public function applicable(Request $request, TaxCalculationService $taxService): JsonResponse
    {
        $contractId = $request->query('contractId');
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $param = $taxService->getApplicableTaxParameter($date, $contractId);

        if (!$param) {
            return response()->json([
                'status' => 'error',
                'code' => 'TAX_PARAM_NOT_FOUND',
                'message' => "Tidak ditemukan parameter pajak yang berlaku untuk tanggal {$date}",
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'taxParamId' => $param->tax_param_id,
                'contractId' => $param->contract_id,
                'ppnRate' => number_format((float) $param->ppn_rate, 2, '.', ''),
                'pphRate' => number_format((float) $param->pph_rate, 2, '.', ''),
                'effectiveFrom' => $param->effective_from->format('Y-m-d'),
                'effectiveTo' => $param->effective_to ? $param->effective_to->format('Y-m-d') : null,
            ],
        ], 200);
    }
}
