<?php

namespace App\Http\Controllers\Stunting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stunting\StoreAnakRequest;
use App\Http\Requests\Stunting\UpdateAnakRequest;
use App\Models\Anak;
use App\Models\AuditLog;
use App\Models\DokumenAnak;
use App\Models\Kecamatan;
use App\Models\Pengukuran;
use App\Models\VerifikasiStunting;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnakController extends Controller
{
    public function index(Request $request): View
    {
        $query = Anak::query()
            ->with(['kecamatan', 'desaKelurahan', 'petugas'])
            ->withCount('pengukurans')
            ->where('status_data', 'valid');

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('nik_anak', 'like', "%{$search}%")
                    ->orWhere('nomor_kk', 'like', "%{$search}%")
                    ->orWhere('nama_anak', 'like', "%{$search}%");
            });
        }

        if ($kecamatanId = $request->integer('kecamatan_id')) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        if ($desaKelurahanId = $request->integer('desa_kelurahan_id')) {
            $query->where('desa_kelurahan_id', $desaKelurahanId);
        }

        if ($jenisKelamin = $request->string('jenis_kelamin')->trim()->value()) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        if ($dari = $request->date('dari')) {
            $query->whereDate('tanggal_input', '>=', $dari);
        }

        if ($sampai = $request->date('sampai')) {
            $query->whereDate('tanggal_input', '<=', $sampai);
        }

        $anaks = $query->latest('tanggal_input')->paginate(15)->withQueryString();

        return view('stunting.index', [
            'anaks' => $anaks,
            'kecamatans' => Kecamatan::where('is_active', true)->orderBy('nama')->get(),
            'filters' => $request->only(['q', 'kecamatan_id', 'desa_kelurahan_id', 'jenis_kelamin', 'dari', 'sampai']),
        ]);
    }

    public function create(): View
    {
        return view('stunting.create', $this->formReferenceData());
    }

    public function store(StoreAnakRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $anak = DB::transaction(function () use ($data, $request) {
            $anak = Anak::create([
                'kode_pendataan' => $this->generateKodePendataan(),
                'nik_anak' => $data['nik_anak'],
                'nomor_kk' => $data['nomor_kk'],
                'nama_anak' => $data['nama_anak'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'usia_bulan' => (int) Carbon::parse($data['tanggal_lahir'])->diffInMonths(now()),
                'nama_ayah' => $data['nama_ayah'],
                'nama_ibu' => $data['nama_ibu'],
                'nomor_hp_orang_tua' => $data['nomor_hp_orang_tua'] ?? null,
                'alamat' => $data['alamat'],
                'kecamatan_id' => $data['kecamatan_id'],
                'desa_kelurahan_id' => $data['desa_kelurahan_id'],
                'petugas_id' => $request->user()->id,
                'tanggal_input' => now(),
                'status_data' => 'dalam_verifikasi',
                'tanggal_pendataan' => now(),
            ]);

            $this->syncOrangTua($anak, $data['orang_tua'] ?? []);
            $this->syncRiwayatKehamilan($anak, $data['riwayat_kehamilan'] ?? []);
            $this->syncRiwayatKelahiran($anak, $data['riwayat_kelahiran'] ?? []);
            $this->syncPengukuranAwal($anak, $request, $data['pengukuran'] ?? []);
            $this->syncRiwayatKesehatan($anak, $data['riwayat_kesehatan'] ?? []);
            $this->syncAsiMpasi($anak, $data['asi_mpasi'] ?? []);
            $this->syncSanitasi($anak, $data['sanitasi'] ?? []);
            $this->syncDokumen($anak, $request, $data['dokumen'] ?? []);

            $this->ubahStatus($anak, 'dalam_verifikasi', $request->user()->id, null);

            return $anak;
        });

        return redirect()->route('stunting.show', $anak)
            ->with('status', 'Pendataan berhasil disimpan dan berstatus Dalam Verifikasi.');
    }

    public function show(Anak $anak): View
    {
        $anak->load([
            'kecamatan', 'desaKelurahan', 'petugas',
            'orangTua',
            'riwayatKehamilan',
            'riwayatKelahiran',
            'pengukurans' => fn ($q) => $q->orderByDesc('tanggal_pengukuran'),
            'pengukurans.petugasPengukur',
            'riwayatKesehatan',
            'asiMpasi',
            'sanitasi',
            'dokumen.pengunggah',
            'riwayatVerifikasi.verifikator',
        ]);

        return view('stunting.show', [
            'anak' => $anak,
        ]);
    }

    public function edit(Anak $anak): View
    {
        $anak->load(['orangTua', 'riwayatKehamilan', 'riwayatKelahiran', 'riwayatKesehatan', 'asiMpasi', 'sanitasi', 'dokumen']);

        return view('stunting.edit', $this->formReferenceData() + ['anak' => $anak]);
    }

    public function update(UpdateAnakRequest $request, Anak $anak): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $anak) {
            $anak->update([
                'nik_anak' => $data['nik_anak'],
                'nomor_kk' => $data['nomor_kk'],
                'nama_anak' => $data['nama_anak'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'usia_bulan' => (int) Carbon::parse($data['tanggal_lahir'])->diffInMonths(now()),
                'nama_ayah' => $data['nama_ayah'],
                'nama_ibu' => $data['nama_ibu'],
                'nomor_hp_orang_tua' => $data['nomor_hp_orang_tua'] ?? null,
                'alamat' => $data['alamat'],
                'kecamatan_id' => $data['kecamatan_id'],
                'desa_kelurahan_id' => $data['desa_kelurahan_id'],
            ]);

            $this->syncOrangTua($anak, $data['orang_tua'] ?? []);
            $this->syncRiwayatKehamilan($anak, $data['riwayat_kehamilan'] ?? []);
            $this->syncRiwayatKelahiran($anak, $data['riwayat_kelahiran'] ?? []);
            $this->syncRiwayatKesehatan($anak, $data['riwayat_kesehatan'] ?? []);
            $this->syncAsiMpasi($anak, $data['asi_mpasi'] ?? []);
            $this->syncSanitasi($anak, $data['sanitasi'] ?? []);
            $this->syncDokumen($anak, $request, $data['dokumen'] ?? []);

            // Data yang diedit ulang setelah perlu perbaikan otomatis kembali ke antrean verifikasi.
            if ($anak->status_data === 'perlu_perbaikan') {
                $this->ubahStatus($anak, 'dalam_verifikasi', $request->user()->id, 'Dikirim kembali setelah perbaikan.');
            }
        });

        return redirect()->route('stunting.show', $anak)
            ->with('status', 'Perubahan data anak berhasil disimpan.');
    }

    public function destroy(Anak $anak): RedirectResponse
    {
        $dataLama = $anak->only(['kode_pendataan', 'nama_anak', 'nik_anak', 'nomor_kk', 'status_data']);

        $anak->delete();

        AuditLog::catat('anak', 'delete', $anak, $dataLama, null, "Menghapus data anak \"{$dataLama['nama_anak']}\" ({$dataLama['kode_pendataan']}).");

        return redirect()->route('stunting.index')->with('status', 'Data anak berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formReferenceData(): array
    {
        return [
            'kecamatans' => Kecamatan::where('is_active', true)->with(['desaKelurahans' => fn ($q) => $q->where('is_active', true)->orderBy('nama')])->orderBy('nama')->get(),
            'refPendidikan' => config('referensi.pendidikan_terakhir'),
            'refSumberAir' => config('referensi.sumber_air'),
            'refJenisJamban' => config('referensi.jenis_jamban'),
            'refPengelolaanSampah' => config('referensi.pengelolaan_sampah'),
        ];
    }

    private function generateKodePendataan(): string
    {
        do {
            $kode = 'AN-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Anak::where('kode_pendataan', $kode)->exists());

        return $kode;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncOrangTua(Anak $anak, array $row): void
    {
        if (blank($row['nama_ayah_lengkap'] ?? null) && blank($row['nama_ibu_lengkap'] ?? null)) {
            return;
        }

        $anak->orangTua()->updateOrCreate(['anak_id' => $anak->id], [
            'nik_ayah' => $row['nik_ayah'] ?? null,
            'nama_ayah_lengkap' => $row['nama_ayah_lengkap'] ?? null,
            'tanggal_lahir_ayah' => $row['tanggal_lahir_ayah'] ?? null,
            'pendidikan_ayah' => $row['pendidikan_ayah'] ?? null,
            'pekerjaan_ayah' => $row['pekerjaan_ayah'] ?? null,
            'penghasilan_ayah' => $row['penghasilan_ayah'] ?? null,
            'nik_ibu' => $row['nik_ibu'] ?? null,
            'nama_ibu_lengkap' => $row['nama_ibu_lengkap'] ?? null,
            'tanggal_lahir_ibu' => $row['tanggal_lahir_ibu'] ?? null,
            'pendidikan_ibu' => $row['pendidikan_ibu'] ?? null,
            'pekerjaan_ibu' => $row['pekerjaan_ibu'] ?? null,
            'penghasilan_ibu' => $row['penghasilan_ibu'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncRiwayatKehamilan(Anak $anak, array $row): void
    {
        if (blank(array_filter($row))) {
            return;
        }

        $anak->riwayatKehamilan()->updateOrCreate(['anak_id' => $anak->id], [
            'usia_ibu_saat_hamil' => $row['usia_ibu_saat_hamil'] ?? null,
            'kehamilan_ke' => $row['kehamilan_ke'] ?? null,
            'jumlah_kehamilan' => $row['jumlah_kehamilan'] ?? null,
            'jumlah_pemeriksaan_kehamilan' => $row['jumlah_pemeriksaan_kehamilan'] ?? null,
            'tempat_pemeriksaan' => $row['tempat_pemeriksaan'] ?? null,
            'kondisi_kehamilan' => $row['kondisi_kehamilan'] ?? null,
            'risiko_kehamilan' => (bool) ($row['risiko_kehamilan'] ?? false),
            'konsumsi_tablet_tambah_darah' => $row['konsumsi_tablet_tambah_darah'] ?? null,
            'status_kek' => (bool) ($row['status_kek'] ?? false),
            'lila' => $row['lila'] ?? null,
            'komplikasi_kehamilan' => $row['komplikasi_kehamilan'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncRiwayatKelahiran(Anak $anak, array $row): void
    {
        // tanggal_lahir & tempat_lahir adalah kolom NOT NULL; draft yang belum mengisi
        // bagian ini tidak membuat baris riwayat_kelahiran sama sekali (PRD Bagian 20).
        if (blank($row['tanggal_lahir'] ?? null) || blank($row['tempat_lahir'] ?? null)) {
            return;
        }

        $anak->riwayatKelahiran()->updateOrCreate(['anak_id' => $anak->id], [
            'tanggal_lahir' => $row['tanggal_lahir'],
            'tempat_lahir' => $row['tempat_lahir'],
            'penolong_persalinan' => $row['penolong_persalinan'] ?? null,
            'cara_persalinan' => $row['cara_persalinan'] ?? null,
            'berat_badan_lahir' => $row['berat_badan_lahir'] ?? null,
            'panjang_badan_lahir' => $row['panjang_badan_lahir'] ?? null,
            'status_prematur' => (bool) ($row['status_prematur'] ?? false),
            'status_bblr' => (bool) ($row['status_bblr'] ?? false),
            'imd' => (bool) ($row['imd'] ?? false),
            'kondisi_bayi_saat_lahir' => $row['kondisi_bayi_saat_lahir'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncPengukuranAwal(Anak $anak, Request $request, array $row): void
    {
        if (blank($row['tanggal_pengukuran'] ?? null)) {
            return;
        }

        Pengukuran::create([
            'anak_id' => $anak->id,
            'tanggal_pengukuran' => $row['tanggal_pengukuran'],
            'berat_badan' => $row['berat_badan'],
            'panjang_tinggi_badan' => $row['panjang_tinggi_badan'],
            'lingkar_kepala' => $row['lingkar_kepala'] ?? null,
            'lingkar_lengan_atas' => $row['lingkar_lengan_atas'] ?? null,
            'petugas_pengukur_id' => $request->user()->id,
            'tempat_pengukuran' => $row['tempat_pengukuran'] ?? null,
            'indikator_antropometri' => 'BB/U, TB/U, BB/TB',
            'hasil_kategori' => $row['hasil_kategori'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncRiwayatKesehatan(Anak $anak, array $row): void
    {
        if (blank(array_filter($row))) {
            return;
        }

        $anak->riwayatKesehatan()->updateOrCreate(['anak_id' => $anak->id], [
            'status_imunisasi' => $row['status_imunisasi'] ?? null,
            'imunisasi_terakhir' => $row['imunisasi_terakhir'] ?? null,
            'riwayat_penyakit' => $row['riwayat_penyakit'] ?? null,
            'riwayat_diare' => (bool) ($row['riwayat_diare'] ?? false),
            'riwayat_ispa' => (bool) ($row['riwayat_ispa'] ?? false),
            'penyakit_kronis' => (bool) ($row['penyakit_kronis'] ?? false),
            'jenis_penyakit_kronis' => $row['jenis_penyakit_kronis'] ?? null,
            'penyakit_bawaan' => (bool) ($row['penyakit_bawaan'] ?? false),
            'jenis_penyakit_bawaan' => $row['jenis_penyakit_bawaan'] ?? null,
            'riwayat_rawat_inap' => (bool) ($row['riwayat_rawat_inap'] ?? false),
            'akses_pelayanan_kesehatan' => (bool) ($row['akses_pelayanan_kesehatan'] ?? false),
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncAsiMpasi(Anak $anak, array $row): void
    {
        if (blank(array_filter($row))) {
            return;
        }

        $anak->asiMpasi()->updateOrCreate(['anak_id' => $anak->id], [
            'imd' => (bool) ($row['imd'] ?? false),
            'asi_eksklusif' => (bool) ($row['asi_eksklusif'] ?? false),
            'lama_pemberian_asi_bulan' => $row['lama_pemberian_asi_bulan'] ?? null,
            'kendala_pemberian_asi' => $row['kendala_pemberian_asi'] ?? null,
            'usia_mulai_mpasi_bulan' => $row['usia_mulai_mpasi_bulan'] ?? null,
            'frekuensi_makan' => $row['frekuensi_makan'] ?? null,
            'jenis_makanan' => $row['jenis_makanan'] ?? null,
            'sumber_protein' => $row['sumber_protein'] ?? null,
            'konsumsi_sayur' => (bool) ($row['konsumsi_sayur'] ?? false),
            'konsumsi_buah' => (bool) ($row['konsumsi_buah'] ?? false),
            'keragaman_makanan' => $row['keragaman_makanan'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncSanitasi(Anak $anak, array $row): void
    {
        if (blank(array_filter($row))) {
            return;
        }

        $anak->sanitasi()->updateOrCreate(['anak_id' => $anak->id], [
            'sumber_air_minum' => $row['sumber_air_minum'] ?? null,
            'sumber_air_memasak' => $row['sumber_air_memasak'] ?? null,
            'kepemilikan_jamban' => (bool) ($row['kepemilikan_jamban'] ?? false),
            'jenis_jamban' => $row['jenis_jamban'] ?? null,
            'septic_tank' => (bool) ($row['septic_tank'] ?? false),
            'saluran_pembuangan' => $row['saluran_pembuangan'] ?? null,
            'pengelolaan_sampah' => $row['pengelolaan_sampah'] ?? null,
            'kondisi_rumah' => $row['kondisi_rumah'] ?? null,
            'kepadatan_hunian' => $row['kepadatan_hunian'] ?? null,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncDokumen(Anak $anak, Request $request, array $rows): void
    {
        foreach ($rows as $index => $row) {
            $file = $request->file("dokumen.{$index}.file");

            if (! $file) {
                continue;
            }

            $path = $file->store("dokumen-anak/{$anak->id}", 'public');

            DokumenAnak::create([
                'anak_id' => $anak->id,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'jenis_dokumentasi' => $row['jenis_dokumentasi'],
                'tanggal_upload' => now(),
                'user_id' => $request->user()->id,
                'keterangan' => $row['keterangan'] ?? null,
            ]);
        }
    }

    private function ubahStatus(Anak $anak, string $status, int $verifikatorId, ?string $catatan): void
    {
        $anak->update(['status_data' => $status]);

        VerifikasiStunting::create([
            'anak_id' => $anak->id,
            'verifikator_id' => $verifikatorId,
            'status' => $status,
            'catatan' => $catatan,
            'tanggal_verifikasi' => now(),
        ]);
    }
}
