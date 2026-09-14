<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function desaKelurahans(): HasMany
    {
        return $this->hasMany(DesaKelurahan::class);
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
