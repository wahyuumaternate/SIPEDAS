<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Ubah isian bebas hubungan keluarga (mis. "Kepala Keluarga", "istri ") menjadi kode
     * referensi seragam (config('referensi.hubungan_keluarga')). Isian yang tidak dikenali menjadi "lainnya".
     */
    public function up(): void
    {
        $kodeValid = array_keys(config('referensi.hubungan_keluarga'));
        $alias = [
            'kk' => 'kepala_keluarga',
            'kepala' => 'kepala_keluarga',
            'anak_kandung' => 'anak',
            'anak_angkat' => 'anak',
            'anak_tiri' => 'anak',
            'ayah' => 'orang_tua',
            'ibu' => 'orang_tua',
            'famili' => 'famili_lain',
            'keluarga_lain' => 'famili_lain',
        ];

        DB::table('anggota_keluargas')
            ->select('hubungan_keluarga')
            ->distinct()
            ->pluck('hubungan_keluarga')
            ->each(function (?string $nilai) use ($kodeValid, $alias) {
                $kode = Str::of((string) $nilai)->trim()->lower()->replaceMatches('/[^a-z]+/', '_')->trim('_')->value();
                $kode = $alias[$kode] ?? $kode;
                $kode = in_array($kode, $kodeValid, true) ? $kode : 'lainnya';

                if ($kode !== $nilai) {
                    DB::table('anggota_keluargas')->where('hubungan_keluarga', $nilai)->update(['hubungan_keluarga' => $kode]);
                }
            });
    }

    public function down(): void
    {
        foreach (config('referensi.hubungan_keluarga') as $kode => $label) {
            DB::table('anggota_keluargas')->where('hubungan_keluarga', $kode)->update(['hubungan_keluarga' => $label]);
        }
    }
};
