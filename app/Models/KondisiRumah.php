<?php

namespace App\Models;

use App\Models\Concerns\HasReferensiLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KondisiRumah extends Model
{
    use HasFactory, HasReferensiLabel;

    protected $fillable = [
        'keluarga_id', 'status_kepemilikan', 'kondisi_bangunan',
        'jenis_atap', 'jenis_dinding', 'jenis_lantai',
        'sumber_listrik', 'daya_listrik', 'sumber_air', 'jamban', 'jenis_jamban', 'septic_tank', 'jenis_septic_tank',
        'pengelolaan_sampah', 'luas_tanah', 'luas_bangunan', 'jumlah_kamar', 'jumlah_penghuni',
    ];

    protected $casts = [
        'jamban' => 'boolean',
        'septic_tank' => 'boolean',
        'luas_tanah' => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
    ];

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function getJenisAtapLabelAttribute(): ?string
    {
        return $this->labelReferensi('jenis_atap', $this->jenis_atap);
    }

    public function getJenisDindingLabelAttribute(): ?string
    {
        return $this->labelReferensi('jenis_dinding', $this->jenis_dinding);
    }

    public function getJenisLantaiLabelAttribute(): ?string
    {
        return $this->labelReferensi('jenis_lantai', $this->jenis_lantai);
    }

    public function getSumberListrikLabelAttribute(): ?string
    {
        return $this->labelReferensi('sumber_listrik', $this->sumber_listrik);
    }

    public function getSumberAirLabelAttribute(): ?string
    {
        return $this->labelReferensi('sumber_air', $this->sumber_air);
    }

    public function getPengelolaanSampahLabelAttribute(): ?string
    {
        return $this->labelReferensi('pengelolaan_sampah', $this->pengelolaan_sampah);
    }

    public function getJenisJambanLabelAttribute(): ?string
    {
        return $this->labelReferensi('jenis_jamban', $this->jenis_jamban);
    }

    public function getJenisSepticTankLabelAttribute(): ?string
    {
        return $this->labelReferensi('jenis_septic_tank', $this->jenis_septic_tank);
    }

    public function getDayaListrikLabelAttribute(): ?string
    {
        return $this->labelReferensi('daya_listrik', $this->daya_listrik);
    }
}
