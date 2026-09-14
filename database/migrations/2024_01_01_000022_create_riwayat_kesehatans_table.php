<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Riwayat Kesehatan Anak (PRD Bagian 22). */
    public function up(): void
    {
        Schema::create('riwayat_kesehatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->unique()->constrained('anaks')->cascadeOnDelete();

            $table->enum('status_imunisasi', ['lengkap', 'tidak_lengkap', 'tidak_imunisasi'])->nullable();
            $table->string('imunisasi_terakhir')->nullable();
            $table->text('riwayat_penyakit')->nullable();
            $table->boolean('riwayat_diare')->default(false);
            $table->boolean('riwayat_ispa')->default(false);
            $table->boolean('penyakit_kronis')->default(false);
            $table->string('jenis_penyakit_kronis')->nullable();
            $table->boolean('penyakit_bawaan')->default(false);
            $table->string('jenis_penyakit_bawaan')->nullable();
            $table->boolean('riwayat_rawat_inap')->default(false);
            $table->boolean('akses_pelayanan_kesehatan')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kesehatans');
    }
};
