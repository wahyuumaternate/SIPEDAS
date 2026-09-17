<?php

namespace App\Models;

use App\Models\Concerns\HasReferensiLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsetKeluarga extends Model
{
    use HasFactory, HasReferensiLabel;

    protected $fillable = [
        'keluarga_id', 'jenis_aset', 'jumlah', 'status_kepemilikan',
        'perkiraan_nilai', 'keterangan',
    ];

    protected $casts = [
        'perkiraan_nilai' => 'decimal:2',
        'jumlah' => 'integer',
    ];

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function getJenisAsetLabelAttribute(): ?string
    {
        return $this->labelReferensi('jenis_aset', $this->jenis_aset);
    }
}
