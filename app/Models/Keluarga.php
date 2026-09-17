<?php

namespace App\Models;

use App\Models\Concerns\HasReferensiLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keluarga extends Model
{
    use HasFactory, HasReferensiLabel, SoftDeletes;

    protected $fillable = [
        'kode_pendataan', 'nomor_kk', 'nik_kepala_keluarga', 'nama_kepala_keluarga',
        'nomor_hp', 'jumlah_anggota_keluarga', 'status_perkawinan',
        'alamat', 'rt', 'rw', 'kecamatan_id', 'desa_kelurahan_id',
        'petugas_id', 'tanggal_input', 'status_data', 'tanggal_pendataan',
    ];

    protected $casts = [
        'tanggal_input' => 'datetime',
        'tanggal_pendataan' => 'datetime',
        'jumlah_anggota_keluarga' => 'integer',
    ];

    // Relasi wilayah & petugas
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function desaKelurahan(): BelongsTo
    {
        return $this->belongsTo(DesaKelurahan::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    // Relasi data anggota & kondisi (1 keluarga -> banyak/satu form turunan)
    public function anggotaKeluarga(): HasMany
    {
        return $this->hasMany(AnggotaKeluarga::class);
    }

    public function kondisiEkonomi(): HasOne
    {
        return $this->hasOne(KondisiEkonomi::class);
    }

    public function kondisiRumah(): HasOne
    {
        return $this->hasOne(KondisiRumah::class);
    }

    public function asetKeluarga(): HasMany
    {
        return $this->hasMany(AsetKeluarga::class);
    }

    public function kondisiSosial(): HasOne
    {
        return $this->hasOne(KondisiSosial::class);
    }

    public function kepesertaanProgram(): HasMany
    {
        return $this->hasMany(KepesertaanProgram::class);
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenKeluarga::class);
    }

    public function riwayatVerifikasi(): HasMany
    {
        return $this->hasMany(VerifikasiKemiskinan::class);
    }

    /** Verifikasi terakhir/terbaru untuk keluarga ini. */
    public function verifikasiTerbaru(): HasOne
    {
        return $this->hasOne(VerifikasiKemiskinan::class)->latestOfMany();
    }

    public function getStatusPerkawinanLabelAttribute(): ?string
    {
        return $this->labelReferensi('status_perkawinan', $this->status_perkawinan);
    }
}
