<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDesaKelurahanRequest extends FormRequest
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
            'kecamatan_id' => ['required', 'exists:kecamatans,id'],
            'kode' => ['required', 'string', 'max:20', Rule::unique('desa_kelurahans', 'kode')->ignore($this->route('desaKelurahan'))],
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:desa,kelurahan'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
