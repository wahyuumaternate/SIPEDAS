<?php

namespace App\Http\Requests\Stunting;

use Illuminate\Foundation\Http\FormRequest;

class StoreVerifikasiStuntingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:dalam_verifikasi,valid,perlu_perbaikan,tidak_valid,duplikat'],
            'catatan' => ['required_if:status,perlu_perbaikan,tidak_valid,duplikat', 'nullable', 'string'],
        ];
    }
}
