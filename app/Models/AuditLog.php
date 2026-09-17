<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // audit log hanya dicatat sekali (created_at)

    protected $fillable = [
        'user_id', 'modul', 'aksi', 'subjek_tipe', 'subjek_id',
        'deskripsi', 'data_lama', 'data_baru', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Relasi polimorfik opsional ke record subjek (subjek_tipe + subjek_id). */
    public function subjek(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'subjek_tipe', 'subjek_id');
    }

    /**
     * Catat satu aktivitas penting (PRD Bagian 46). Dipanggil dari controller
     * setelah aksi yang tercatat berhasil dilakukan.
     *
     * @param  array<string, mixed>|null  $dataLama
     * @param  array<string, mixed>|null  $dataBaru
     */
    public static function catat(
        string $modul,
        string $aksi,
        ?Model $subjek = null,
        ?array $dataLama = null,
        ?array $dataBaru = null,
        ?string $deskripsi = null,
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'modul' => $modul,
            'aksi' => $aksi,
            'subjek_tipe' => $subjek?->getMorphClass(),
            'subjek_id' => $subjek?->getKey(),
            'deskripsi' => $deskripsi,
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
