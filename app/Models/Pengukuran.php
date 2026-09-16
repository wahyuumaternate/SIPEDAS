<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengukuran extends Model
{
    use HasFactory;

    protected $fillable = [
        'anak_id', 'tanggal_pengukuran', 'berat_badan', 'panjang_tinggi_badan',
        'lingkar_kepala', 'lingkar_lengan_atas', 'petugas_pengukur_id', 'tempat_pengukuran',
        'usia_saat_pengukuran_bulan', 'indikator_antropometri', 'hasil_kategori',
    ];

    protected $casts = [
        'tanggal_pengukuran' => 'date',
        'berat_badan' => 'decimal:2',
        'panjang_tinggi_badan' => 'decimal:2',
        'lingkar_kepala' => 'decimal:2',
        'lingkar_lengan_atas' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Usia saat pengukuran dihitung otomatis dari tanggal lahir anak (PRD Bagian 21).
        static::saving(function (self $model) {
            if ($model->anak_id && $model->tanggal_pengukuran && $model->anak?->tanggal_lahir) {
                $model->usia_saat_pengukuran_bulan = (int) $model->anak->tanggal_lahir
                    ->diffInMonths($model->tanggal_pengukuran);
            }
        });
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function petugasPengukur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_pengukur_id');
    }
}
