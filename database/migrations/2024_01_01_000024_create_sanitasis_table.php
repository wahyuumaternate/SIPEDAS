<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Sanitasi & Lingkungan (PRD Bagian 24), untuk data anak/stunting. */
    public function up(): void
    {
        Schema::create('sanitasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->unique()->constrained('anaks')->cascadeOnDelete();

            // Kode-kode berikut mengacu ke config('referensi.*), bukan lagi foreign key.
            $table->string('sumber_air_minum')->nullable();
            $table->string('sumber_air_memasak')->nullable();
            $table->boolean('kepemilikan_jamban')->default(false);
            $table->string('jenis_jamban')->nullable();
            $table->boolean('septic_tank')->default(false);
            $table->string('saluran_pembuangan')->nullable();
            $table->string('pengelolaan_sampah')->nullable();
            $table->enum('kondisi_rumah', ['permanen', 'semi_permanen', 'tidak_layak_huni'])->nullable();
            $table->decimal('kepadatan_hunian', 8, 2)->nullable(); // m2 per orang

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanitasis');
    }
};
