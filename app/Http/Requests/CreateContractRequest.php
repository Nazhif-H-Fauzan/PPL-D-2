<?php

namespace App\Http\Requests;

class CreateContractRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'projectId' => 'required|string|max:100',
            'contractNumber' => 'required|string|max:50|unique:contracts,contract_number',
            'contractValue' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'projectId.required' => 'Project ID wajib diisi',
            'contractNumber.required' => 'Nomor kontrak wajib diisi',
            'contractNumber.unique' => 'Nomor kontrak sudah terdaftar',
            'contractValue.required' => 'Nilai kontrak wajib diisi',
            'contractValue.gt' => 'Nilai kontrak harus lebih dari 0',
            'contractValue.regex' => 'Format nominal nilai kontrak tidak valid',
        ];
    }
}
