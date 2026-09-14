<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsiMpasi extends Model
{
    use HasFactory;

    protected $table = 'asi_mpasis';

    protected $fillable = [
        'anak_id', 'imd', 'asi_eksklusif', 'lama_pemberian_asi_bulan', 'kendala_pemberian_asi',
        'usia_mulai_mpasi_bulan', 'frekuensi_makan', 'jenis_makanan', 'sumber_protein',
        'konsumsi_sayur', 'konsumsi_buah', 'keragaman_makanan',
    ];

    protected $casts = [
        'imd' => 'boolean',
        'asi_eksklusif' => 'boolean',
        'konsumsi_sayur' => 'boolean',
        'konsumsi_buah' => 'boolean',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }
}
