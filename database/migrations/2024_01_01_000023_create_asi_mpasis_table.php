<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form ASI & MPASI (PRD Bagian 23). */
    public function up(): void
    {
        Schema::create('asi_mpasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->unique()->constrained('anaks')->cascadeOnDelete();

            // ASI
            $table->boolean('imd')->default(false);
            $table->boolean('asi_eksklusif')->default(false);
            $table->unsignedInteger('lama_pemberian_asi_bulan')->nullable();
            $table->text('kendala_pemberian_asi')->nullable();

            // MPASI
            $table->unsignedInteger('usia_mulai_mpasi_bulan')->nullable();
            $table->string('frekuensi_makan')->nullable();
            $table->string('jenis_makanan')->nullable();
            $table->string('sumber_protein')->nullable();
            $table->boolean('konsumsi_sayur')->default(false);
            $table->boolean('konsumsi_buah')->default(false);
            $table->string('keragaman_makanan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asi_mpasis');
    }
};
