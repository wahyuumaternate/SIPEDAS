<?php

namespace App\Http\Requests\Master;

use App\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('nama')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'hak_akses' => ['array'],
            'hak_akses.*' => ['string', 'in:'.implode(',', Permission::all())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
