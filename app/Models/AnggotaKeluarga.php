<?php

namespace App\Models;

use App\Models\Concerns\HasReferensiLabel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaKeluarga extends Model
{
    use HasFactory, HasReferensiLabel;

    protected $fillable = [
        'keluarga_id', 'nik', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir',
        'tanggal_lahir', 'usia', 'hubungan_keluarga', 'status_perkawinan',
        'pendidikan_terakhir', 'status_pekerjaan',
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

    /** Usia terkini dihitung otomatis dari tanggal_lahir (dalam tahun). */
    public function getUsiaTerkiniAttribute(): ?int
    {
        return $this->tanggal_lahir ? Carbon::parse($this->tanggal_lahir)->age : null;
    }

    public function getStatusPerkawinanLabelAttribute(): ?string
    {
        return $this->labelReferensi('status_perkawinan', $this->status_perkawinan);
    }

    public function getPendidikanTerakhirLabelAttribute(): ?string
    {
        return $this->labelReferensi('pendidikan_terakhir', $this->pendidikan_terakhir);
    }

    public function getStatusPekerjaanLabelAttribute(): ?string
    {
        return $this->labelReferensi('status_pekerjaan', $this->status_pekerjaan);
    }
}
