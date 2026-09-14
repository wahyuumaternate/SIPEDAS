<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
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
}
