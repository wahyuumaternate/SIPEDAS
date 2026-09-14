<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KondisiRumah extends Model
{
    use HasFactory;

    protected $fillable = [
        'keluarga_id', 'status_kepemilikan', 'kondisi_bangunan',
        'jenis_atap_id', 'jenis_dinding_id', 'jenis_lantai_id',
        'sumber_listrik_id', 'sumber_air_id', 'jamban', 'septic_tank', 'drainase',
        'pengelolaan_sampah_id', 'luas_tanah', 'luas_bangunan', 'jumlah_kamar', 'jumlah_penghuni',
    ];

    protected $casts = [
        'jamban' => 'boolean',
        'septic_tank' => 'boolean',
        'drainase' => 'boolean',
        'luas_tanah' => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
    ];

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function jenisAtap(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'jenis_atap_id');
    }

    public function jenisDinding(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'jenis_dinding_id');
    }

    public function jenisLantai(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'jenis_lantai_id');
    }

    public function sumberListrik(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'sumber_listrik_id');
    }

    public function sumberAir(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'sumber_air_id');
    }

    public function pengelolaanSampah(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'pengelolaan_sampah_id');
    }
}
