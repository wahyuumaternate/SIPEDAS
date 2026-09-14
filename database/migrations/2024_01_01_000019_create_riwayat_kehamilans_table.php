<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Riwayat Kehamilan (PRD Bagian 19). */
    public function up(): void
    {
        Schema::create('riwayat_kehamilans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->unique()->constrained('anaks')->cascadeOnDelete();

            $table->unsignedInteger('usia_ibu_saat_hamil')->nullable();
            $table->unsignedInteger('kehamilan_ke')->nullable();
            $table->unsignedInteger('jumlah_kehamilan')->nullable();
            $table->unsignedInteger('jumlah_pemeriksaan_kehamilan')->nullable();
            $table->string('tempat_pemeriksaan')->nullable();
            $table->string('kondisi_kehamilan')->nullable();
            $table->boolean('risiko_kehamilan')->default(false);
            $table->enum('konsumsi_tablet_tambah_darah', ['tidak_pernah', 'kadang', 'rutin'])->nullable();
            $table->boolean('status_kek')->default(false); // kekurangan energi kronis
            $table->decimal('lila', 5, 2)->nullable(); // lingkar lengan atas ibu, cm
            $table->text('komplikasi_kehamilan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kehamilans');
    }
};
