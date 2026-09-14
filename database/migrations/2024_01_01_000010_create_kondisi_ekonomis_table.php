<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Kondisi Ekonomi (PRD Bagian 11): pekerjaan, pendapatan, pengeluaran. */
    public function up(): void
    {
        Schema::create('kondisi_ekonomis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->unique()->constrained('keluargas')->cascadeOnDelete();

            // Pekerjaan
            $table->foreignId('status_pekerjaan_kepala_keluarga_id')->nullable()->constrained('referensis')->nullOnDelete();
            $table->string('pekerjaan_utama')->nullable();
            $table->string('pekerjaan_tambahan')->nullable();
            $table->unsignedInteger('jumlah_anggota_bekerja')->default(0);
            $table->unsignedInteger('jumlah_anggota_tidak_bekerja')->default(0);

            // Pendapatan
            $table->decimal('pendapatan_kepala_keluarga', 15, 2)->default(0);
            $table->decimal('pendapatan_pasangan', 15, 2)->default(0);
            $table->decimal('pendapatan_anggota_lainnya', 15, 2)->default(0);
            $table->decimal('total_pendapatan', 15, 2)->default(0); // dihitung otomatis

            // Pengeluaran
            $table->decimal('pengeluaran_makanan', 15, 2)->default(0);
            $table->decimal('pengeluaran_pendidikan', 15, 2)->default(0);
            $table->decimal('pengeluaran_kesehatan', 15, 2)->default(0);
            $table->decimal('pengeluaran_listrik', 15, 2)->default(0);
            $table->decimal('pengeluaran_air', 15, 2)->default(0);
            $table->decimal('pengeluaran_transportasi', 15, 2)->default(0);
            $table->decimal('pengeluaran_lainnya', 15, 2)->default(0);
            $table->decimal('total_pengeluaran', 15, 2)->default(0); // dihitung otomatis

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kondisi_ekonomis');
    }
};
