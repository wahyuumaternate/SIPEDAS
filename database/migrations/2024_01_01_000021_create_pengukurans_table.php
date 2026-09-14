<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form Pengukuran Anak (PRD Bagian 21). Satu anak dapat mempunyai
     * banyak record pengukuran; data lama TIDAK ditimpa oleh pengukuran baru.
     */
    public function up(): void
    {
        Schema::create('pengukurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anaks')->cascadeOnDelete();

            $table->date('tanggal_pengukuran');
            $table->decimal('berat_badan', 5, 2); // kg
            $table->decimal('panjang_tinggi_badan', 5, 2); // cm
            $table->decimal('lingkar_kepala', 5, 2)->nullable(); // cm
            $table->decimal('lingkar_lengan_atas', 5, 2)->nullable(); // cm
            $table->foreignId('petugas_pengukur_id')->constrained('users')->restrictOnDelete();
            $table->string('tempat_pengukuran')->nullable();

            // Dihitung otomatis
            $table->unsignedInteger('usia_saat_pengukuran_bulan')->nullable();
            $table->string('indikator_antropometri')->nullable(); // contoh: BB/U, TB/U, BB/TB
            $table->string('hasil_kategori')->nullable(); // contoh: stunting, normal, gizi_kurang

            $table->timestamps();

            $table->index(['anak_id', 'tanggal_pengukuran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengukurans');
    }
};
