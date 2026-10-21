<?php

namespace App\Http\Requests;

class CreateInvoiceRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'terminId' => 'required|string',
            'invoiceDate' => 'required|date_format:Y-m-d',
            'dppAmount' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'terminId.required' => 'Termin ID wajib diisi',
            'invoiceDate.required' => 'Tanggal invoice wajib diisi',
            'invoiceDate.date_format' => 'Format tanggal invoice harus YYYY-MM-DD',
            'dppAmount.required' => 'DPP wajib diisi',
            'dppAmount.numeric' => 'DPP harus berupa angka desimal',
            'dppAmount.gt' => 'DPP harus lebih dari 0',
            'dppAmount.regex' => 'Format nilai DPP tidak valid (maksimal 2 desimal)',
        ];
    }
}
