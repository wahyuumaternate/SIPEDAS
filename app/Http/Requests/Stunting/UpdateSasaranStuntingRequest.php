<?php

namespace App\Http\Requests\Stunting;

use App\Models\SasaranStunting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSasaranStuntingRequest extends FormRequest
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
        $aturanAngka = collect(SasaranStunting::KOLOM)
            ->mapWithKeys(fn ($label, string $kolom) => ["sasaran.*.{$kolom}" => ['nullable', 'integer', 'min:0', 'max:10000000']])
            ->all();

        return [
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'sasaran' => ['required', 'array'],
            'sasaran.*.kecamatan_id' => ['required', 'integer', 'distinct', 'exists:kecamatans,id'],
            ...$aturanAngka,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return collect(SasaranStunting::KOLOM)
            ->mapWithKeys(fn (string $label, string $kolom) => ["sasaran.*.{$kolom}" => $label])
            ->all();
    }
}
