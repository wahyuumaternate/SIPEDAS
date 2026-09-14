<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KepesertaanProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'keluarga_id', 'nama_program', 'status_penerima',
        'tahun_menerima', 'sumber_instansi', 'keterangan',
    ];

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }
}
