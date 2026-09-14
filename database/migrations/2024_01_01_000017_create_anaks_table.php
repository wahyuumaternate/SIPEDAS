<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Identitas Anak (PRD Bagian 17.1) - tabel inti pendataan stunting. */
    public function up(): void
    {
        Schema::create('anaks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pendataan')->unique();

            $table->string('nik_anak', 16);
            $table->string('nomor_kk', 16);
            $table->string('nama_anak');
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->unsignedInteger('usia_bulan')->nullable(); // dihitung otomatis, cache

            $table->string('nama_ayah');
            $table->string('nama_ibu');
            $table->string('nomor_hp_orang_tua')->nullable();

            $table->text('alamat');
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->restrictOnDelete();
            $table->foreignId('desa_kelurahan_id')->constrained('desa_kelurahans')->restrictOnDelete();

            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('tanggal_input')->useCurrent();

            $table->enum('status_data', [
                'draft', 'dikirim', 'dalam_verifikasi', 'perlu_perbaikan',
                'valid', 'tidak_valid', 'duplikat',
            ])->default('draft');
            $table->timestamp('tanggal_pendataan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('nik_anak');
            $table->index('nomor_kk');
            $table->index(['kecamatan_id', 'desa_kelurahan_id']);
            $table->index('status_data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anaks');
    }
};
