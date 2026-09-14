<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesaKelurahan extends Model
{
    use HasFactory;

    protected $fillable = ['kecamatan_id', 'kode', 'nama', 'jenis', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function keluargas(): HasMany
    {
        return $this->hasMany(Keluarga::class);
    }

    public function anaks(): HasMany
    {
        return $this->hasMany(Anak::class);
    }
}
