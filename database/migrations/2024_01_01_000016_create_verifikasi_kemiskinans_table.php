<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat Verifikasi & Validasi kemiskinan ekstrem (PRD Bagian 25).
     * Alur status: draft -> dikirim -> dalam_verifikasi -> valid
     * atau -> perlu_perbaikan -> (petugas memperbaiki) -> dikirim kembali.
     * Setiap perubahan status dicatat sebagai satu baris riwayat.
     */
    public function up(): void
    {
        Schema::create('verifikasi_kemiskinans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluargas')->cascadeOnDelete();
            $table->foreignId('verifikator_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'draft', 'dikirim', 'dalam_verifikasi', 'perlu_perbaikan',
                'valid', 'tidak_valid', 'duplikat',
            ]);
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();

            $table->timestamps();

            $table->index(['keluarga_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_kemiskinans');
    }
};
