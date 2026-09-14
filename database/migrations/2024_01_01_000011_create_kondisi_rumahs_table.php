<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Kondisi Rumah (PRD Bagian 12). */
    public function up(): void
    {
        Schema::create('kondisi_rumahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->unique()->constrained('keluargas')->cascadeOnDelete();

            $table->enum('status_kepemilikan', ['milik_sendiri', 'sewa', 'menumpang', 'rumah_dinas', 'lainnya']);
            $table->enum('kondisi_bangunan', ['permanen', 'semi_permanen', 'tidak_layak_huni']);

            // Material
            $table->foreignId('jenis_atap_id')->nullable()->constrained('referensis')->nullOnDelete();
            $table->foreignId('jenis_dinding_id')->nullable()->constrained('referensis')->nullOnDelete();
            $table->foreignId('jenis_lantai_id')->nullable()->constrained('referensis')->nullOnDelete();

            // Fasilitas
            $table->foreignId('sumber_listrik_id')->nullable()->constrained('referensis')->nullOnDelete();
            $table->foreignId('sumber_air_id')->nullable()->constrained('referensis')->nullOnDelete();
            $table->boolean('jamban')->default(false);
            $table->boolean('septic_tank')->default(false);
            $table->boolean('drainase')->default(false);
            $table->foreignId('pengelolaan_sampah_id')->nullable()->constrained('referensis')->nullOnDelete();

            // Kondisi fisik
            $table->decimal('luas_tanah', 10, 2)->nullable(); // m2
            $table->decimal('luas_bangunan', 10, 2)->nullable(); // m2
            $table->unsignedInteger('jumlah_kamar')->nullable();
            $table->unsignedInteger('jumlah_penghuni')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kondisi_rumahs');
    }
};
