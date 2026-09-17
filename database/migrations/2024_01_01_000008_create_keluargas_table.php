<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form Identitas Keluarga (PRD Bagian 9) - tabel inti pendataan kemiskinan ekstrem.
     */
    public function up(): void
    {
        Schema::create('keluargas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pendataan')->unique(); // ID pendataan otomatis

            // Identitas keluarga
            $table->string('nomor_kk', 16);
            $table->string('nik_kepala_keluarga', 16);
            $table->string('nama_kepala_keluarga');
            $table->string('nomor_hp')->nullable();
            $table->unsignedInteger('jumlah_anggota_keluarga');
            $table->string('status_perkawinan')->nullable(); // kode dari config('referensi.status_perkawinan')

            // Alamat
            $table->text('alamat');
            $table->string('rt', 5);
            $table->string('rw', 5);
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->restrictOnDelete();
            $table->foreignId('desa_kelurahan_id')->constrained('desa_kelurahans')->restrictOnDelete();

            // Metadata otomatis
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('tanggal_input')->useCurrent();

            // Status pendataan & verifikasi (Bagian 25)
            $table->enum('status_data', [
                'draft', 'dikirim', 'dalam_verifikasi', 'perlu_perbaikan',
                'valid', 'tidak_valid', 'duplikat',
            ])->default('draft');
            $table->timestamp('tanggal_pendataan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['nik_kepala_keluarga']);
            $table->index(['nomor_kk']);
            $table->index(['kecamatan_id', 'desa_kelurahan_id']);
            $table->index(['status_data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keluargas');
    }
};
