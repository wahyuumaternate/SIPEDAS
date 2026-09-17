<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Data Orang Tua (PRD Bagian 18): data ayah dan ibu. */
    public function up(): void
    {
        Schema::create('orang_tuas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->unique()->constrained('anaks')->cascadeOnDelete();

            // Ayah
            $table->string('nik_ayah', 16)->nullable();
            $table->string('nama_ayah_lengkap')->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->string('pendidikan_ayah')->nullable(); // kode dari config('referensi.pendidikan_terakhir')
            $table->string('pekerjaan_ayah')->nullable();
            $table->decimal('penghasilan_ayah', 15, 2)->nullable();

            // Ibu
            $table->string('nik_ibu', 16)->nullable();
            $table->string('nama_ibu_lengkap')->nullable();
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('pendidikan_ibu')->nullable(); // kode dari config('referensi.pendidikan_terakhir')
            $table->string('pekerjaan_ibu')->nullable();
            $table->decimal('penghasilan_ibu', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_tuas');
    }
};
