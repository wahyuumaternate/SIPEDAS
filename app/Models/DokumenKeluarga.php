<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenKeluarga extends Model
{
    use HasFactory;

    protected $fillable = [
        'keluarga_id', 'nama_file', 'path_file', 'jenis_dokumentasi',
        'tanggal_upload', 'user_id', 'keterangan',
    ];

    protected $casts = [
        'tanggal_upload' => 'datetime',
    ];

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    /** User pengunggah dokumen. */
    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
