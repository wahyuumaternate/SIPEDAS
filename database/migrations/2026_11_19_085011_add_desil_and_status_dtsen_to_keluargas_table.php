<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Peringkat kesejahteraan DTSEN (Data Tunggal Sosial dan Ekonomi Nasional).
     * desil null = belum pemeringkatan; status_dtsen nonaktif = KK nonaktif di DTSEN.
     */
    public function up(): void
    {
        Schema::table('keluargas', function (Blueprint $table) {
            $table->unsignedTinyInteger('desil')->nullable()->after('desa_kelurahan_id');
            $table->enum('status_dtsen', ['aktif', 'nonaktif'])->default('aktif')->after('desil');

            $table->index(['desil', 'status_dtsen']);
        });
    }

    public function down(): void
    {
        Schema::table('keluargas', function (Blueprint $table) {
            $table->dropIndex(['desil', 'status_dtsen']);
            $table->dropColumn(['desil', 'status_dtsen']);
        });
    }
};
