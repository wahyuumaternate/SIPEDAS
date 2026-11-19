<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penyesuaian modul kemiskinan sesuai catatan pengembangan:
     * pekerjaan & penyakit kronis berbentuk pilihan, status sekolah anak, pengeluaran pakaian,
     * bahan bakar memasak, perlindungan ketenagakerjaan, jamban/septic tank/daya listrik,
     * aset "lainnya", bantuan pendidikan, serta JKN/PBI.
     */
    public function up(): void
    {
        Schema::table('anggota_keluargas', function (Blueprint $table) {
            $table->string('pekerjaan_lainnya')->nullable()->after('status_pekerjaan');
            $table->string('status_sekolah')->nullable()->after('pendidikan_terakhir');
        });

        Schema::table('kondisi_ekonomis', function (Blueprint $table) {
            $table->string('pekerjaan_utama_lainnya')->nullable()->after('pekerjaan_utama');
            $table->boolean('perlindungan_ketenagakerjaan')->default(false)->after('pekerjaan_tambahan');
            $table->string('bahan_bakar_memasak')->nullable()->after('perlindungan_ketenagakerjaan');
            $table->decimal('pengeluaran_pakaian', 15, 2)->default(0)->after('pengeluaran_transportasi');
        });

        Schema::table('kondisi_rumahs', function (Blueprint $table) {
            $table->string('jenis_jamban')->nullable()->after('jamban');
            $table->string('jenis_septic_tank')->nullable()->after('septic_tank');
            $table->string('daya_listrik')->nullable()->after('sumber_listrik');
        });

        Schema::table('aset_keluargas', function (Blueprint $table) {
            $table->string('jenis_aset_lainnya')->nullable()->after('jenis_aset');
        });

        Schema::table('kondisi_sosials', function (Blueprint $table) {
            $table->string('bantuan_pendidikan')->nullable()->after('jarak_sekolah_km');
            $table->string('bantuan_pendidikan_lainnya')->nullable()->after('bantuan_pendidikan');
            $table->boolean('memiliki_jkn')->default(false)->after('bantuan_pendidikan_lainnya');
            $table->boolean('memiliki_pbi')->default(false)->after('memiliki_jkn');
        });

        // Data lama: pekerjaan utama berupa teks bebas -> kode pilihan (sisanya "lainnya" + teks aslinya).
        $kodePekerjaan = collect(config('referensi.status_pekerjaan'))
            ->mapWithKeys(fn (string $label, string $kode) => [mb_strtolower($label) => $kode, $kode => $kode]);

        DB::table('kondisi_ekonomis')->whereNotNull('pekerjaan_utama')->orderBy('id')->each(function ($baris) use ($kodePekerjaan) {
            $kode = $kodePekerjaan[mb_strtolower(trim($baris->pekerjaan_utama))] ?? null;

            DB::table('kondisi_ekonomis')->where('id', $baris->id)->update($kode
                ? ['pekerjaan_utama' => $kode]
                : ['pekerjaan_utama' => 'lainnya', 'pekerjaan_utama_lainnya' => $baris->pekerjaan_utama]);
        });

        // Data lama: jamban/septic tank ya-tidak -> pilihan.
        DB::table('kondisi_rumahs')->where('jamban', false)->update(['jenis_jamban' => 'tidak_ada']);
        DB::table('kondisi_rumahs')->where('septic_tank', true)->update(['jenis_septic_tank' => 'tangki_septik']);

        // Data lama: penyakit kronis tanpa jenis yang dikenali -> "lainnya".
        DB::table('anggota_keluargas')->where('penyakit_kronis', true)
            ->where(fn ($q) => $q->whereNull('jenis_penyakit_kronis')->orWhereNotIn('jenis_penyakit_kronis', array_keys(config('referensi.penyakit_kronis'))))
            ->update(['jenis_penyakit_kronis' => 'lainnya']);

        // Status kepemilikan "Rumah Dinas" dihapus.
        DB::table('kondisi_rumahs')->where('status_kepemilikan', 'rumah_dinas')->update(['status_kepemilikan' => 'lainnya']);
        Schema::table('kondisi_rumahs', function (Blueprint $table) {
            $table->enum('status_kepemilikan', ['milik_sendiri', 'sewa', 'menumpang', 'lainnya'])->change();
            $table->dropColumn('drainase');
        });

        Schema::table('kondisi_sosials', function (Blueprint $table) {
            $table->dropColumn('akses_pendidikan');
        });
    }

    public function down(): void
    {
        Schema::table('kondisi_sosials', function (Blueprint $table) {
            $table->boolean('akses_pendidikan')->default(false);
            $table->dropColumn(['bantuan_pendidikan', 'bantuan_pendidikan_lainnya', 'memiliki_jkn', 'memiliki_pbi']);
        });

        Schema::table('kondisi_rumahs', function (Blueprint $table) {
            $table->enum('status_kepemilikan', ['milik_sendiri', 'sewa', 'menumpang', 'rumah_dinas', 'lainnya'])->change();
            $table->boolean('drainase')->default(false);
            $table->dropColumn(['jenis_jamban', 'jenis_septic_tank', 'daya_listrik']);
        });

        Schema::table('aset_keluargas', function (Blueprint $table) {
            $table->dropColumn('jenis_aset_lainnya');
        });

        Schema::table('kondisi_ekonomis', function (Blueprint $table) {
            $table->dropColumn(['pekerjaan_utama_lainnya', 'perlindungan_ketenagakerjaan', 'bahan_bakar_memasak', 'pengeluaran_pakaian']);
        });

        Schema::table('anggota_keluargas', function (Blueprint $table) {
            $table->dropColumn(['pekerjaan_lainnya', 'status_sekolah']);
        });
    }
};
