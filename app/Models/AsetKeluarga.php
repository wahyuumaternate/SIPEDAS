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
        'keluarga_id', 'jenis_aset', 'jenis_aset_lainnya', 'jumlah', 'status_kepemilikan',
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
        if ($this->jenis_aset === 'lainnya' && filled($this->jenis_aset_lainnya)) {
            return $this->jenis_aset_lainnya;
        }

        return $this->labelReferensi('jenis_aset', $this->jenis_aset);
    }
}
