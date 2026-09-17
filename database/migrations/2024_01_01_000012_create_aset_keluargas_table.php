<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Kepemilikan Aset (PRD Bagian 13). Satu keluarga bisa punya banyak aset. */
    public function up(): void
    {
        Schema::create('aset_keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluargas')->cascadeOnDelete();

            $table->string('jenis_aset'); // kode dari config('referensi.jenis_aset')
            $table->unsignedInteger('jumlah')->default(1);
            $table->enum('status_kepemilikan', ['milik_sendiri', 'sewa', 'lainnya'])->default('milik_sendiri');
            $table->decimal('perkiraan_nilai', 15, 2)->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_keluargas');
    }
};
