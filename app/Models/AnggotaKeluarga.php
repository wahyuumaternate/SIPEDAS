<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaKeluarga extends Model
{
    use HasFactory;

    protected $fillable = [
        'keluarga_id', 'nik', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir',
        'tanggal_lahir', 'usia', 'hubungan_keluarga', 'status_perkawinan_id',
        'pendidikan_terakhir_id', 'status_pekerjaan_id',
        'disabilitas', 'jenis_disabilitas', 'penyakit_kronis', 'jenis_penyakit_kronis',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'disabilitas' => 'boolean',
        'penyakit_kronis' => 'boolean',
    ];

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function statusPerkawinan(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'status_perkawinan_id');
    }

    public function pendidikanTerakhir(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'pendidikan_terakhir_id');
    }

    public function statusPekerjaan(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'status_pekerjaan_id');
    }

    /** Usia terkini dihitung otomatis dari tanggal_lahir (dalam tahun). */
    public function getUsiaTerkiniAttribute(): ?int
    {
        return $this->tanggal_lahir ? Carbon::parse($this->tanggal_lahir)->age : null;
    }
}
