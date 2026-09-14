<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Form Kepesertaan Program/Bantuan (PRD Bagian 15).
     * Hanya mencatat STATUS kepesertaan, bukan pengajuan/pencairan/penyaluran.
     */
    public function up(): void
    {
        Schema::create('kepesertaan_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluargas')->cascadeOnDelete();

            $table->string('nama_program'); // contoh: PKH, Sembako, PBI JKN, BLT
            $table->enum('status_penerima', ['penerima', 'bukan_penerima', 'pernah_menerima']);
            $table->year('tahun_menerima')->nullable();
            $table->string('sumber_instansi')->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kepesertaan_programs');
    }
};
