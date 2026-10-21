<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Format respon kegagalan validasi sesuai spesifikasi ErrorResponse OpenAPI (HTTP 400).
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => 'error',
            'code' => 'VALIDATION_ERROR',
            'message' => 'Validasi masukan gagal',
            'errors' => $validator->errors()->messages(),
        ], 400);

        throw new HttpResponseException($response);
    }
}
