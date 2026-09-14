<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Kondisi Sosial & Layanan Dasar (PRD Bagian 14). */
    public function up(): void
    {
        Schema::create('kondisi_sosials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->unique()->constrained('keluargas')->cascadeOnDelete();

            $table->unsignedInteger('jumlah_anak_usia_sekolah')->default(0);
            $table->unsignedInteger('jumlah_anak_bersekolah')->default(0);
            $table->unsignedInteger('jumlah_anak_putus_sekolah')->default(0);
            $table->unsignedInteger('jumlah_lansia')->default(0);
            $table->unsignedInteger('jumlah_penyandang_disabilitas')->default(0);
            $table->unsignedInteger('jumlah_anggota_sakit')->default(0);

            $table->boolean('akses_fasilitas_kesehatan')->default(false);
            $table->decimal('jarak_fasilitas_kesehatan_km', 8, 2)->nullable();
            $table->boolean('akses_pendidikan')->default(false);
            $table->decimal('jarak_sekolah_km', 8, 2)->nullable();

            $table->boolean('kepemilikan_dokumen_kependudukan')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kondisi_sosials');
    }
};
