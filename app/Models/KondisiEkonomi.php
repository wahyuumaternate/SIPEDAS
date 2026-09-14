<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KondisiEkonomi extends Model
{
    use HasFactory;

    protected $fillable = [
        'keluarga_id',
        'status_pekerjaan_kepala_keluarga_id', 'pekerjaan_utama', 'pekerjaan_tambahan',
        'jumlah_anggota_bekerja', 'jumlah_anggota_tidak_bekerja',
        'pendapatan_kepala_keluarga', 'pendapatan_pasangan', 'pendapatan_anggota_lainnya', 'total_pendapatan',
        'pengeluaran_makanan', 'pengeluaran_pendidikan', 'pengeluaran_kesehatan',
        'pengeluaran_listrik', 'pengeluaran_air', 'pengeluaran_transportasi',
        'pengeluaran_lainnya', 'total_pengeluaran',
    ];

    protected $casts = [
        'pendapatan_kepala_keluarga' => 'decimal:2',
        'pendapatan_pasangan' => 'decimal:2',
        'pendapatan_anggota_lainnya' => 'decimal:2',
        'total_pendapatan' => 'decimal:2',
        'pengeluaran_makanan' => 'decimal:2',
        'pengeluaran_pendidikan' => 'decimal:2',
        'pengeluaran_kesehatan' => 'decimal:2',
        'pengeluaran_listrik' => 'decimal:2',
        'pengeluaran_air' => 'decimal:2',
        'pengeluaran_transportasi' => 'decimal:2',
        'pengeluaran_lainnya' => 'decimal:2',
        'total_pengeluaran' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Total pendapatan & pengeluaran dihitung otomatis sesuai PRD Bagian 11.
        static::saving(function (self $model) {
            $model->total_pendapatan = collect([
                $model->pendapatan_kepala_keluarga,
                $model->pendapatan_pasangan,
                $model->pendapatan_anggota_lainnya,
            ])->sum();

            $model->total_pengeluaran = collect([
                $model->pengeluaran_makanan,
                $model->pengeluaran_pendidikan,
                $model->pengeluaran_kesehatan,
                $model->pengeluaran_listrik,
                $model->pengeluaran_air,
                $model->pengeluaran_transportasi,
                $model->pengeluaran_lainnya,
            ])->sum();
        });
    }

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function statusPekerjaanKepalaKeluarga(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'status_pekerjaan_kepala_keluarga_id');
    }
}
