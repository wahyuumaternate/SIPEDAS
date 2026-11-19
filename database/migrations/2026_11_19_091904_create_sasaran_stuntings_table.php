<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data sasaran (populasi) per kecamatan per tahun untuk Laporan Analisis Situasi stunting:
     * demografi dan capaian remaja putri yang tidak bisa diturunkan dari data per anak.
     */
    public function up(): void
    {
        Schema::create('sasaran_stuntings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');

            $table->unsignedInteger('penduduk')->nullable();
            $table->unsignedInteger('rumah_tangga')->nullable();
            $table->unsignedInteger('anak_0_5')->nullable();
            $table->unsignedInteger('anak_6_23')->nullable();
            $table->unsignedInteger('anak_24_59')->nullable();
            $table->unsignedInteger('remaja_putri')->nullable();
            $table->unsignedInteger('calon_pengantin')->nullable();
            $table->unsignedInteger('ibu_hamil')->nullable();
            $table->unsignedInteger('ibu_nifas')->nullable();

            $table->unsignedInteger('remaja_putri_ttd')->nullable();
            $table->unsignedInteger('remaja_putri_skrining_anemia')->nullable();

            $table->timestamps();

            $table->unique(['kecamatan_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sasaran_stuntings');
    }
};
