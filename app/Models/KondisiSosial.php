<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KondisiSosial extends Model
{
    use HasFactory;

    protected $fillable = [
        'keluarga_id',
        'jumlah_anak_usia_sekolah', 'jumlah_anak_bersekolah', 'jumlah_anak_putus_sekolah',
        'jumlah_lansia', 'jumlah_penyandang_disabilitas', 'jumlah_anggota_sakit',
        'akses_fasilitas_kesehatan', 'jarak_fasilitas_kesehatan_km',
        'akses_pendidikan', 'jarak_sekolah_km', 'kepemilikan_dokumen_kependudukan',
    ];

    protected $casts = [
        'akses_fasilitas_kesehatan' => 'boolean',
        'akses_pendidikan' => 'boolean',
        'kepemilikan_dokumen_kependudukan' => 'boolean',
        'jarak_fasilitas_kesehatan_km' => 'decimal:2',
        'jarak_sekolah_km' => 'decimal:2',
    ];

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }
}
