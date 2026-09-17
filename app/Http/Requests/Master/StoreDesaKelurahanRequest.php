<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesaKelurahanRequest extends FormRequest
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
            'kode' => ['required', 'string', 'max:20', 'unique:desa_kelurahans,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:desa,kelurahan'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
