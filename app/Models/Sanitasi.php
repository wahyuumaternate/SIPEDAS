<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanitasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'anak_id', 'sumber_air_minum_id', 'sumber_air_memasak_id',
        'kepemilikan_jamban', 'jenis_jamban_id', 'septic_tank', 'saluran_pembuangan',
        'pengelolaan_sampah_id', 'kondisi_rumah', 'kepadatan_hunian',
    ];

    protected $casts = [
        'kepemilikan_jamban' => 'boolean',
        'septic_tank' => 'boolean',
        'kepadatan_hunian' => 'decimal:2',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function sumberAirMinum(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'sumber_air_minum_id');
    }

    public function sumberAirMemasak(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'sumber_air_memasak_id');
    }

    public function jenisJamban(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'jenis_jamban_id');
    }

    public function pengelolaanSampah(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'pengelolaan_sampah_id');
    }
}
