<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatKesehatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'anak_id', 'status_imunisasi', 'imunisasi_terakhir', 'riwayat_penyakit',
        'riwayat_diare', 'riwayat_ispa', 'penyakit_kronis', 'jenis_penyakit_kronis',
        'penyakit_bawaan', 'jenis_penyakit_bawaan', 'riwayat_rawat_inap',
        'akses_pelayanan_kesehatan', 'tata_laksana_gizi_buruk', 'tambahan_asupan_gizi',
        'oralit_zinc', 'pneumonia', 'obat_cacing', 'memiliki_jkn',
    ];

    protected $casts = [
        'riwayat_diare' => 'boolean',
        'riwayat_ispa' => 'boolean',
        'penyakit_kronis' => 'boolean',
        'penyakit_bawaan' => 'boolean',
        'riwayat_rawat_inap' => 'boolean',
        'akses_pelayanan_kesehatan' => 'boolean',
        'tata_laksana_gizi_buruk' => 'boolean',
        'tambahan_asupan_gizi' => 'boolean',
        'oralit_zinc' => 'boolean',
        'pneumonia' => 'boolean',
        'obat_cacing' => 'boolean',
        'memiliki_jkn' => 'boolean',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }
}
