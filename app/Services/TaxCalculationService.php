<?php

namespace App\Services;

use App\Models\TaxParameter;

class TaxCalculationService
{
    /**
     * Mengambil parameter pajak yang berlaku pada tanggal dan kontrak tertentu.
     * Prioritas:
     * 1. Parameter khusus kontrak jika contractId diberikan dan ada record aktif.
     * 2. Parameter global (contract_id = null) yang aktif pada tanggal tersebut.
     *
     * @param string $date (YYYY-MM-DD)
     * @param string|null $contractId
     * @return TaxParameter|null
     */
    public function getApplicableTaxParameter(string $date, ?string $contractId = null): ?TaxParameter
    {
        if (!empty($contractId)) {
            $contractSpecific = TaxParameter::where('contract_id', $contractId)
                ->where('effective_from', '<=', $date)
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $date);
                })
                ->orderByDesc('effective_from')
                ->first();

            if ($contractSpecific) {
                return $contractSpecific;
            }
        }

        return TaxParameter::whereNull('contract_id')
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();
    }

    /**
     * Melakukan kalkulasi otomatis PPN, PPh, dan Total Tagihan.
     *
     * @param float $dpp
     * @param TaxParameter $taxParameter
     * @return array
     */
    public function calculate(float $dpp, TaxParameter $taxParameter): array
    {
        $ppnRate = (float) $taxParameter->ppn_rate;
        $pphRate = (float) $taxParameter->pph_rate;

        $ppnAmount = round($dpp * ($ppnRate / 100), 2);
        $pphAmount = round($dpp * ($pphRate / 100), 2);
        $totalAmount = round($dpp + $ppnAmount - $pphAmount, 2);

        return [
            'taxParamId' => $taxParameter->tax_param_id,
            'dppAmount' => number_format($dpp, 2, '.', ''),
            'ppnAmount' => number_format($ppnAmount, 2, '.', ''),
            'pphAmount' => number_format($pphAmount, 2, '.', ''),
            'totalAmount' => number_format($totalAmount, 2, '.', ''),
            'ppnRate' => number_format($ppnRate, 2, '.', ''),
            'pphRate' => number_format($pphRate, 2, '.', ''),
        ];
    }
}
