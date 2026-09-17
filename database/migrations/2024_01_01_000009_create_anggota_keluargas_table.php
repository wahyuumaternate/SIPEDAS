<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form Anggota Keluarga (PRD Bagian 10). Satu keluarga dapat mempunyai
     * banyak anggota keluarga.
     */
    public function up(): void
    {
        Schema::create('anggota_keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluargas')->cascadeOnDelete();

            $table->string('nik', 16)->nullable();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir');
            // Usia dihitung otomatis dari tanggal_lahir di level aplikasi,
            // disimpan sebagai cache agar mudah difilter/direkap.
            $table->unsignedInteger('usia')->nullable();

            $table->string('hubungan_keluarga'); // hubungan dengan kepala keluarga
            // Kode-kode berikut mengacu ke config('referensi.*), bukan lagi foreign key.
            $table->string('status_perkawinan')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('status_pekerjaan')->nullable();

            $table->boolean('disabilitas')->default(false);
            $table->string('jenis_disabilitas')->nullable();
            $table->boolean('penyakit_kronis')->default(false);
            $table->string('jenis_penyakit_kronis')->nullable();

            $table->timestamps();

            $table->index('nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_keluargas');
    }
};
