<?php

namespace App\Models;

use App\Models\Concerns\HasReferensiLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KondisiSosial extends Model
{
    use HasFactory, HasReferensiLabel;

    protected $fillable = [
        'keluarga_id',
        'jumlah_anak_usia_sekolah', 'jumlah_anak_bersekolah', 'jumlah_anak_putus_sekolah',
        'jumlah_lansia', 'jumlah_penyandang_disabilitas', 'jumlah_anggota_sakit',
        'akses_fasilitas_kesehatan', 'jarak_fasilitas_kesehatan_km',
        'jarak_sekolah_km', 'bantuan_pendidikan', 'bantuan_pendidikan_lainnya',
        'memiliki_jkn', 'memiliki_pbi', 'kepemilikan_dokumen_kependudukan',
    ];

    protected $casts = [
        'akses_fasilitas_kesehatan' => 'boolean',
        'memiliki_jkn' => 'boolean',
        'memiliki_pbi' => 'boolean',
        'kepemilikan_dokumen_kependudukan' => 'boolean',
        'jarak_fasilitas_kesehatan_km' => 'decimal:2',
        'jarak_sekolah_km' => 'decimal:2',
    ];

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function getBantuanPendidikanLabelAttribute(): ?string
    {
        if ($this->bantuan_pendidikan === 'lainnya' && filled($this->bantuan_pendidikan_lainnya)) {
            return $this->bantuan_pendidikan_lainnya;
        }

        return $this->labelReferensi('bantuan_pendidikan', $this->bantuan_pendidikan);
    }
}
