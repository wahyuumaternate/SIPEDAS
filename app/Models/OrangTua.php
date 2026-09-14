<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrangTua extends Model
{
    use HasFactory;

    protected $table = 'orang_tuas';

    protected $fillable = [
        'anak_id',
        'nik_ayah', 'nama_ayah_lengkap', 'tanggal_lahir_ayah', 'pendidikan_ayah_id', 'pekerjaan_ayah', 'penghasilan_ayah',
        'nik_ibu', 'nama_ibu_lengkap', 'tanggal_lahir_ibu', 'pendidikan_ibu_id', 'pekerjaan_ibu', 'penghasilan_ibu',
    ];

    protected $casts = [
        'tanggal_lahir_ayah' => 'date',
        'tanggal_lahir_ibu' => 'date',
        'penghasilan_ayah' => 'decimal:2',
        'penghasilan_ibu' => 'decimal:2',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function pendidikanAyah(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'pendidikan_ayah_id');
    }

    public function pendidikanIbu(): BelongsTo
    {
        return $this->belongsTo(Referensi::class, 'pendidikan_ibu_id');
    }
}
