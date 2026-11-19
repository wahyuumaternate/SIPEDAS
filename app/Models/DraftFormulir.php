<?php

namespace App\Models;

use Database\Factories\DraftFormulirFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Draft isian formulir pendataan yang disimpan otomatis per pengguna.
 * Kunci: "{modul}.create" untuk data baru atau "{modul}.edit.{id}" untuk ubah data.
 */
class DraftFormulir extends Model
{
    /** @use HasFactory<DraftFormulirFactory> */
    use HasFactory;

    /** Pola kunci draft yang diizinkan. */
    public const POLA_KUNCI = '(kemiskinan|stunting)\.(create|edit\.\d+)';

    protected $fillable = ['user_id', 'kunci', 'isian', 'disimpan_pada'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'isian' => 'array',
            'disimpan_pada' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeMilik(Builder $query, User $user, string $kunci): void
    {
        $query->where('user_id', $user->id)->where('kunci', $kunci);
    }

    /**
     * Hapus draft setelah data berhasil disimpan.
     */
    public static function selesai(User $user, string $kunci): void
    {
        static::query()->milik($user, $kunci)->delete();
        session()->flash('draft_selesai', $kunci);
    }
}
