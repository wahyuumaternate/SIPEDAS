<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatKehamilan extends Model
{
    use HasFactory;

    protected $fillable = [
        'anak_id', 'usia_ibu_saat_hamil', 'kehamilan_ke', 'jumlah_kehamilan',
        'jumlah_pemeriksaan_kehamilan', 'tempat_pemeriksaan', 'kondisi_kehamilan',
        'risiko_kehamilan', 'konsumsi_tablet_tambah_darah', 'jumlah_tablet_tambah_darah', 'status_kek',
        'pmt_kek', 'makanan_bergizi_seimbang', 'kb_pascapersalinan', 'lila',
        'komplikasi_kehamilan',
    ];

    protected $casts = [
        'risiko_kehamilan' => 'boolean',
        'status_kek' => 'boolean',
        'pmt_kek' => 'boolean',
        'makanan_bergizi_seimbang' => 'boolean',
        'kb_pascapersalinan' => 'boolean',
        'jumlah_tablet_tambah_darah' => 'integer',
        'lila' => 'decimal:2',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }
}
