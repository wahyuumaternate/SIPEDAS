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

            $table->foreignId('sumber_air_minum_id')->nullable()->constrained('referensis')->nullOnDelete();
            $table->foreignId('sumber_air_memasak_id')->nullable()->constrained('referensis')->nullOnDelete();
            $table->boolean('kepemilikan_jamban')->default(false);
            $table->foreignId('jenis_jamban_id')->nullable()->constrained('referensis')->nullOnDelete();
            $table->boolean('septic_tank')->default(false);
            $table->string('saluran_pembuangan')->nullable();
            $table->foreignId('pengelolaan_sampah_id')->nullable()->constrained('referensis')->nullOnDelete();
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
