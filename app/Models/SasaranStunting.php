<?php

namespace App\Models;

use Database\Factories\SasaranStuntingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Data sasaran (populasi) stunting per kecamatan per tahun, diinput manual dari
 * Dukcapil/Dinkes/KUA untuk Laporan Analisis Situasi.
 */
class SasaranStunting extends Model
{
    /** @use HasFactory<SasaranStuntingFactory> */
    use HasFactory;

    /**
     * Kolom angka sasaran beserta labelnya, sesuai urutan di formulir dan laporan.
     *
     * @var array<string, string>
     */
    public const KOLOM = [
        'penduduk' => 'Penduduk',
        'rumah_tangga' => 'Rumah Tangga',
        'anak_0_5' => 'Anak 0–5 bln',
        'anak_6_23' => 'Anak 6–23 bln',
        'anak_24_59' => 'Anak 24–59 bln',
        'remaja_putri' => 'Remaja Putri',
        'calon_pengantin' => 'Calon Pengantin',
        'ibu_hamil' => 'Ibu Hamil',
        'ibu_nifas' => 'Ibu Nifas',
        'remaja_putri_ttd' => 'Rematri Dapat TTD',
        'remaja_putri_skrining_anemia' => 'Rematri Skrining Anemia',
    ];

    protected $fillable = [
        'kecamatan_id', 'tahun',
        'penduduk', 'rumah_tangga', 'anak_0_5', 'anak_6_23', 'anak_24_59',
        'remaja_putri', 'calon_pengantin', 'ibu_hamil', 'ibu_nifas',
        'remaja_putri_ttd', 'remaja_putri_skrining_anemia',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_fill_keys(['tahun', ...array_keys(self::KOLOM)], 'integer');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }
}
