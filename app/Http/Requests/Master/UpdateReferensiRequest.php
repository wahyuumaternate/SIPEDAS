<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateReferensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kategori' => Str::of((string) $this->input('kategori'))->trim()->lower()->snake()->value(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kategori' => ['required', 'string', 'max:100'],
            'kode' => [
                'required', 'string', 'max:100',
                Rule::unique('referensis')
                    ->where(fn ($query) => $query->where('kategori', $this->input('kategori')))
                    ->ignore($this->route('referensi')),
            ],
            'nilai' => ['required', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
