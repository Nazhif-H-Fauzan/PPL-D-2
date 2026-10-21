<?php

namespace App\Http\Requests;

class GetTerminsRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:BELUM_SIAP,SIAP_DITAGIH,SUDAH_DITAGIHKAN',
            'projectId' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'pageSize' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status termin harus bernilai BELUM_SIAP, SIAP_DITAGIH, atau SUDAH_DITAGIHKAN',
            'page.min' => 'Nomor halaman minimal 1',
            'pageSize.min' => 'Ukuran halaman minimal 1',
            'pageSize.max' => 'Ukuran halaman maksimal 100',
        ];
    }
}
