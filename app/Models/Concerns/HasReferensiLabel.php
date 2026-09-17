<?php

namespace App\Models\Concerns;

trait HasReferensiLabel
{
    /**
     * Terjemahkan kode referensi (mis. 'seng') menjadi label tampilan (mis. 'Seng')
     * berdasarkan daftar tetap di config/referensi.php. Mengembalikan kode aslinya
     * kalau tidak ditemukan, supaya data lama tetap terlihat meski kodenya berubah.
     */
    protected function labelReferensi(string $kategori, ?string $kode): ?string
    {
        if (blank($kode)) {
            return null;
        }

        return config("referensi.{$kategori}.{$kode}", $kode);
    }
}
