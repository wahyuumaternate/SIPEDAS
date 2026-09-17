<?php

namespace App\Models;

use App\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id', 'nama', 'username', 'email', 'nomor_hp', 'password',
        'kecamatan_id', 'desa_kelurahan_id', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function desaKelurahan(): BelongsTo
    {
        return $this->belongsTo(DesaKelurahan::class);
    }

    /** Keluarga yang didata oleh petugas ini. */
    public function keluargasDidata(): HasMany
    {
        return $this->hasMany(Keluarga::class, 'petugas_id');
    }

    /** Anak yang didata oleh petugas ini. */
    public function anaksDidata(): HasMany
    {
        return $this->hasMany(Anak::class, 'petugas_id');
    }

    public function dokumenKeluargaDiunggah(): HasMany
    {
        return $this->hasMany(DokumenKeluarga::class, 'user_id');
    }

    public function dokumenAnakDiunggah(): HasMany
    {
        return $this->hasMany(DokumenAnak::class, 'user_id');
    }

    public function pengukuranDilakukan(): HasMany
    {
        return $this->hasMany(Pengukuran::class, 'petugas_pengukur_id');
    }

    public function verifikasiKemiskinanDilakukan(): HasMany
    {
        return $this->hasMany(VerifikasiKemiskinan::class, 'verifikator_id');
    }

    public function verifikasiStuntingDilakukan(): HasMany
    {
        return $this->hasMany(VerifikasiStunting::class, 'verifikator_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'super-admin';
    }

    /**
     * Cek apakah user memiliki permission tertentu, lewat hak_akses role-nya.
     * Super Admin selalu punya seluruh permission.
     */
    public function hasPermission(Permission|string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $value = $permission instanceof Permission ? $permission->value : $permission;

        return in_array($value, $this->role?->hak_akses ?? [], true);
    }
}
