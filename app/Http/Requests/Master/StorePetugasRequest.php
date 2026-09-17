<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StorePetugasRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'nomor_hp' => ['nullable', 'string', 'max:20'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'desa_kelurahan_id' => ['nullable', 'exists:desa_kelurahans,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
