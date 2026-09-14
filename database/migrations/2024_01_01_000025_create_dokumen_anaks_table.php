<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Dokumentasi anak/stunting (mengikuti pola dokumen_keluargas, PRD Bagian 16). */
    public function up(): void
    {
        Schema::create('dokumen_anaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anaks')->cascadeOnDelete();

            $table->string('nama_file');
            $table->string('path_file');
            $table->enum('jenis_dokumentasi', ['foto_anak', 'foto_pengukuran', 'foto_dokumen_pendukung', 'lainnya']);
            $table->timestamp('tanggal_upload')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_anaks');
    }
};
