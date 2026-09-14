<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenAnak extends Model
{
    use HasFactory;

    protected $fillable = [
        'anak_id', 'nama_file', 'path_file', 'jenis_dokumentasi',
        'tanggal_upload', 'user_id', 'keterangan',
    ];

    protected $casts = [
        'tanggal_upload' => 'datetime',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
