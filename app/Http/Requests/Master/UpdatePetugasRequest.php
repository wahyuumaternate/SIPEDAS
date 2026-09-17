<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePetugasRequest extends FormRequest
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
            'role_id' => ['required', 'exists:roles,id'],
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'username')->ignore($this->route('petugas'))],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('petugas'))],
            'nomor_hp' => ['nullable', 'string', 'max:20'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'desa_kelurahan_id' => ['nullable', 'exists:desa_kelurahans,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ];
    }
}
