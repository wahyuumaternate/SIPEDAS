<?php

namespace App\Models;

use App\Models\Concerns\HasReferensiLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrangTua extends Model
{
    use HasFactory, HasReferensiLabel;

    protected $table = 'orang_tuas';

    protected $fillable = [
        'anak_id',
        'nik_ayah', 'nama_ayah_lengkap', 'tanggal_lahir_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
        'nik_ibu', 'nama_ibu_lengkap', 'tanggal_lahir_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
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

    public function getPendidikanAyahLabelAttribute(): ?string
    {
        return $this->labelReferensi('pendidikan_terakhir', $this->pendidikan_ayah);
    }

    public function getPendidikanIbuLabelAttribute(): ?string
    {
        return $this->labelReferensi('pendidikan_terakhir', $this->pendidikan_ibu);
    }
}
