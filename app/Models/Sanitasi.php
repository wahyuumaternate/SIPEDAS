<?php

namespace App\Models;

use App\Models\Concerns\HasReferensiLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanitasi extends Model
{
    use HasFactory, HasReferensiLabel;

    protected $fillable = [
        'anak_id', 'sumber_air_minum', 'sumber_air_memasak',
        'kepemilikan_jamban', 'jenis_jamban', 'septic_tank', 'saluran_pembuangan',
        'pengelolaan_sampah', 'kondisi_rumah', 'kepadatan_hunian',
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

    public function getSumberAirMinumLabelAttribute(): ?string
    {
        return $this->labelReferensi('sumber_air', $this->sumber_air_minum);
    }

    public function getSumberAirMemasakLabelAttribute(): ?string
    {
        return $this->labelReferensi('sumber_air', $this->sumber_air_memasak);
    }

    public function getJenisJambanLabelAttribute(): ?string
    {
        return $this->labelReferensi('jenis_jamban', $this->jenis_jamban);
    }

    public function getPengelolaanSampahLabelAttribute(): ?string
    {
        return $this->labelReferensi('pengelolaan_sampah', $this->pengelolaan_sampah);
    }
}
