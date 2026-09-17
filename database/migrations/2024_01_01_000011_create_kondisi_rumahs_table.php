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

            // Material — kode dari config('referensi.*), bukan lagi foreign key.
            $table->string('jenis_atap')->nullable();
            $table->string('jenis_dinding')->nullable();
            $table->string('jenis_lantai')->nullable();

            // Fasilitas
            $table->string('sumber_listrik')->nullable();
            $table->string('sumber_air')->nullable();
            $table->boolean('jamban')->default(false);
            $table->boolean('septic_tank')->default(false);
            $table->boolean('drainase')->default(false);
            $table->string('pengelolaan_sampah')->nullable();

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
