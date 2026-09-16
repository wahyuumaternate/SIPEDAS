<?php

namespace App\Http\Requests\Kemiskinan;

use Illuminate\Foundation\Http\FormRequest;

class StoreDokumenKeluargaRequest extends FormRequest
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
            'file' => ['required', 'file', 'image', 'max:5120'],
            'jenis_dokumentasi' => ['required', 'in:foto_rumah,foto_lingkungan,foto_dokumen_pendukung,lainnya'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
