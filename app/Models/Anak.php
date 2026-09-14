<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anak extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_pendataan', 'nik_anak', 'nomor_kk', 'nama_anak', 'jenis_kelamin',
        'tempat_lahir', 'tanggal_lahir', 'usia_bulan',
        'nama_ayah', 'nama_ibu', 'nomor_hp_orang_tua',
        'alamat', 'kecamatan_id', 'desa_kelurahan_id',
        'petugas_id', 'tanggal_input', 'status_data', 'tanggal_pendataan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_input' => 'datetime',
        'tanggal_pendataan' => 'datetime',
    ];

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

    public function orangTua(): HasOne
    {
        return $this->hasOne(OrangTua::class);
    }

    public function riwayatKehamilan(): HasOne
    {
        return $this->hasOne(RiwayatKehamilan::class);
    }

    public function riwayatKelahiran(): HasOne
    {
        return $this->hasOne(RiwayatKelahiran::class);
    }

    /** Semua record pengukuran; data lama tidak pernah ditimpa (PRD Bagian 21). */
    public function pengukurans(): HasMany
    {
        return $this->hasMany(Pengukuran::class);
    }

    public function pengukuranTerbaru(): HasOne
    {
        return $this->hasOne(Pengukuran::class)->latestOfMany('tanggal_pengukuran');
    }

    public function riwayatKesehatan(): HasOne
    {
        return $this->hasOne(RiwayatKesehatan::class);
    }

    public function asiMpasi(): HasOne
    {
        return $this->hasOne(AsiMpasi::class);
    }

    public function sanitasi(): HasOne
    {
        return $this->hasOne(Sanitasi::class);
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenAnak::class);
    }

    public function riwayatVerifikasi(): HasMany
    {
        return $this->hasMany(VerifikasiStunting::class);
    }

    public function verifikasiTerbaru(): HasOne
    {
        return $this->hasOne(VerifikasiStunting::class)->latestOfMany();
    }

    /** Usia terkini dalam bulan, dihitung otomatis dari tanggal_lahir. */
    public function getUsiaTerkiniBulanAttribute(): ?int
    {
        return $this->tanggal_lahir
            ? Carbon::parse($this->tanggal_lahir)->diffInMonths(now())
            : null;
    }
}
