<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Dokumentasi keluarga (PRD Bagian 16). Tidak ada koordinat/GPS. */
    public function up(): void
    {
        Schema::create('dokumen_keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluargas')->cascadeOnDelete();

            $table->string('nama_file');
            $table->string('path_file');
            $table->enum('jenis_dokumentasi', ['foto_rumah', 'foto_lingkungan', 'foto_dokumen_pendukung', 'lainnya']);
            $table->timestamp('tanggal_upload')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // pengunggah
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_keluargas');
    }
};
