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
            'status' => ['required', 'in:valid,perlu_perbaikan,tidak_valid'],
            'catatan' => ['required_if:status,perlu_perbaikan,tidak_valid', 'nullable', 'string'],
        ];
    }
}
