<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsetKeluarga extends Model
{
    use HasFactory;

    protected $fillable = [
        'keluarga_id', 'jenis_aset_id', 'jumlah', 'status_kepemilikan',
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

    public function jenisAset(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'jenis_aset_id');
    }
}
