<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori', 'kode', 'nilai', 'urutan', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /** Scope: hanya referensi aktif. */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope: filter berdasarkan kategori, contoh: Referensi::kategori('jenis_aset'). */
    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }
}
