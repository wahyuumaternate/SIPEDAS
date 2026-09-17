<?php

namespace App\Http\Requests\Master;

use App\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('roles', 'slug')->ignore($this->route('role'))],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'hak_akses' => ['array'],
            'hak_akses.*' => ['string', 'in:'.implode(',', Permission::all())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
