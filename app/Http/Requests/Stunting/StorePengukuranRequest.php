<?php

namespace App\Http\Requests\Stunting;

use Illuminate\Foundation\Http\FormRequest;

class StorePengukuranRequest extends FormRequest
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
            'tanggal_pengukuran' => ['required', 'date', 'before_or_equal:today'],
            'berat_badan' => ['required', 'numeric', 'min:0'],
            'panjang_tinggi_badan' => ['required', 'numeric', 'min:0'],
            'lingkar_kepala' => ['nullable', 'numeric', 'min:0'],
            'lingkar_lengan_atas' => ['nullable', 'numeric', 'min:0'],
            'tempat_pengukuran' => ['nullable', 'string', 'max:255'],
            'hasil_kategori' => ['nullable', 'in:normal,stunting_ringan,stunting_sedang,stunting_berat'],
        ];
    }
}
