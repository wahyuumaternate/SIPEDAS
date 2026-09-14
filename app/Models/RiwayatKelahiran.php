<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatKelahiran extends Model
{
    use HasFactory;

    protected $fillable = [
        'anak_id', 'tanggal_lahir', 'tempat_lahir', 'penolong_persalinan', 'cara_persalinan',
        'berat_badan_lahir', 'panjang_badan_lahir', 'status_prematur', 'status_bblr',
        'imd', 'kondisi_bayi_saat_lahir',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'berat_badan_lahir' => 'decimal:2',
        'panjang_badan_lahir' => 'decimal:2',
        'status_prematur' => 'boolean',
        'status_bblr' => 'boolean',
        'imd' => 'boolean',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }
}
