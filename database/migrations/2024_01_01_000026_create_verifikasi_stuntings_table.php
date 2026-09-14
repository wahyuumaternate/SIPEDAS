<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Riwayat Verifikasi & Validasi stunting (PRD Bagian 25), sama pola dengan kemiskinan. */
    public function up(): void
    {
        Schema::create('verifikasi_stuntings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anaks')->cascadeOnDelete();
            $table->foreignId('verifikator_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'draft', 'dikirim', 'dalam_verifikasi', 'perlu_perbaikan',
                'valid', 'tidak_valid', 'duplikat',
            ]);
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();

            $table->timestamps();

            $table->index(['anak_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_stuntings');
    }
};
