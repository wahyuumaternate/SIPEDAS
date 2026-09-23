<?php

namespace App\Http\Controllers\Kemiskinan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kemiskinan\StoreKeluargaRequest;
use App\Http\Requests\Kemiskinan\UpdateKeluargaRequest;
use App\Models\AnggotaKeluarga;
use App\Models\AsetKeluarga;
use App\Models\AuditLog;
use App\Models\DokumenKeluarga;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\KepesertaanProgram;
use App\Models\VerifikasiKemiskinan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeluargaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Keluarga::query()
            ->with(['kecamatan', 'desaKelurahan', 'petugas'])
            ->withCount('anggotaKeluarga')
            ->where('status_data', 'valid');

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('nik_kepala_keluarga', 'like', "%{$search}%")
                    ->orWhere('nomor_kk', 'like', "%{$search}%")
                    ->orWhere('nama_kepala_keluarga', 'like', "%{$search}%");
            });
        }

        if ($kecamatanId = $request->integer('kecamatan_id')) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        if ($desaKelurahanId = $request->integer('desa_kelurahan_id')) {
            $query->where('desa_kelurahan_id', $desaKelurahanId);
        }

        if ($petugasId = $request->integer('petugas_id')) {
            $query->where('petugas_id', $petugasId);
        }

        if ($dari = $request->date('dari')) {
            $query->whereDate('tanggal_input', '>=', $dari);
        }

        if ($sampai = $request->date('sampai')) {
            $query->whereDate('tanggal_input', '<=', $sampai);
        }

        $keluargas = $query->latest('tanggal_input')->paginate(15)->withQueryString();

        return view('kemiskinan.index', [
            'keluargas' => $keluargas,
            'kecamatans' => Kecamatan::where('is_active', true)->orderBy('nama')->get(),
            'filters' => $request->only(['q', 'kecamatan_id', 'desa_kelurahan_id', 'petugas_id', 'dari', 'sampai']),
        ]);
    }

    public function create(): View
    {
        return view('kemiskinan.create', $this->formReferenceData());
    }

    public function store(StoreKeluargaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $keluarga = DB::transaction(function () use ($data, $request) {
            $keluarga = Keluarga::create([
                'kode_pendataan' => $this->generateKodePendataan(),
                'nomor_kk' => $data['nomor_kk'],
                'nik_kepala_keluarga' => $data['nik_kepala_keluarga'],
                'nama_kepala_keluarga' => $data['nama_kepala_keluarga'],
                'nomor_hp' => $data['nomor_hp'] ?? null,
                'jumlah_anggota_keluarga' => count(array_filter($data['anggota'] ?? [], fn ($row) => filled($row['nama_lengkap'] ?? null))),
                'status_perkawinan' => $data['status_perkawinan'] ?? null,
                'alamat' => $data['alamat'],
                'rt' => $data['rt'],
                'rw' => $data['rw'],
                'kecamatan_id' => $data['kecamatan_id'],
                'desa_kelurahan_id' => $data['desa_kelurahan_id'],
                'petugas_id' => $request->user()->id,
                'tanggal_input' => now(),
                'status_data' => 'dalam_verifikasi',
                'tanggal_pendataan' => now(),
            ]);

            $this->syncAnggota($keluarga, $data['anggota'] ?? []);
            $this->syncKondisiEkonomi($keluarga, $data['kondisi_ekonomi'] ?? []);
            $this->syncKondisiRumah($keluarga, $data['kondisi_rumah'] ?? []);
            $this->syncAset($keluarga, $data['aset'] ?? []);
            $this->syncKondisiSosial($keluarga, $data['kondisi_sosial'] ?? []);
            $this->syncProgram($keluarga, $data['program'] ?? []);
            $this->syncDokumen($keluarga, $request, $data['dokumen'] ?? []);

            $this->ubahStatus($keluarga, 'dalam_verifikasi', $request->user()->id, null);

            return $keluarga;
        });

        return redirect()->route('kemiskinan.show', $keluarga)
            ->with('status', 'Pendataan berhasil disimpan dan berstatus Dalam Verifikasi.');
    }

    public function show(Keluarga $keluarga): View
    {
        $keluarga->load([
            'kecamatan', 'desaKelurahan', 'petugas',
            'anggotaKeluarga',
            'kondisiEkonomi',
            'kondisiRumah',
            'asetKeluarga',
            'kondisiSosial',
            'kepesertaanProgram',
            'dokumen.pengunggah',
            'riwayatVerifikasi.verifikator',
        ]);

        return view('kemiskinan.show', [
            'keluarga' => $keluarga,
        ]);
    }

    public function edit(Keluarga $keluarga): View
    {
        $keluarga->load(['anggotaKeluarga', 'kondisiEkonomi', 'kondisiRumah', 'asetKeluarga', 'kondisiSosial', 'kepesertaanProgram', 'dokumen']);

        return view('kemiskinan.edit', $this->formReferenceData() + ['keluarga' => $keluarga]);
    }

    public function update(UpdateKeluargaRequest $request, Keluarga $keluarga): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $keluarga) {
            $keluarga->update([
                'nomor_kk' => $data['nomor_kk'],
                'nik_kepala_keluarga' => $data['nik_kepala_keluarga'],
                'nama_kepala_keluarga' => $data['nama_kepala_keluarga'],
                'nomor_hp' => $data['nomor_hp'] ?? null,
                'jumlah_anggota_keluarga' => count(array_filter($data['anggota'] ?? [], fn ($row) => filled($row['nama_lengkap'] ?? null))),
                'status_perkawinan' => $data['status_perkawinan'] ?? null,
                'alamat' => $data['alamat'],
                'rt' => $data['rt'],
                'rw' => $data['rw'],
                'kecamatan_id' => $data['kecamatan_id'],
                'desa_kelurahan_id' => $data['desa_kelurahan_id'],
            ]);

            $this->syncAnggota($keluarga, $data['anggota'] ?? []);
            $this->syncKondisiEkonomi($keluarga, $data['kondisi_ekonomi'] ?? []);
            $this->syncKondisiRumah($keluarga, $data['kondisi_rumah'] ?? []);
            $this->syncAset($keluarga, $data['aset'] ?? []);
            $this->syncKondisiSosial($keluarga, $data['kondisi_sosial'] ?? []);
            $this->syncProgram($keluarga, $data['program'] ?? []);
            $this->syncDokumen($keluarga, $request, $data['dokumen'] ?? []);

            // Data yang diedit ulang setelah perlu perbaikan otomatis kembali ke antrean verifikasi.
            if ($keluarga->status_data === 'perlu_perbaikan') {
                $this->ubahStatus($keluarga, 'dalam_verifikasi', $request->user()->id, 'Dikirim kembali setelah perbaikan.');
            }
        });

        return redirect()->route('kemiskinan.show', $keluarga)
            ->with('status', 'Perubahan data keluarga berhasil disimpan.');
    }

    public function destroy(Keluarga $keluarga): RedirectResponse
    {
        $dataLama = $keluarga->only(['kode_pendataan', 'nama_kepala_keluarga', 'nik_kepala_keluarga', 'nomor_kk', 'status_data']);

        $keluarga->delete();

        AuditLog::catat('keluarga', 'delete', $keluarga, $dataLama, null, "Menghapus data keluarga \"{$dataLama['nama_kepala_keluarga']}\" ({$dataLama['kode_pendataan']}).");

        return redirect()->route('kemiskinan.index')->with('status', 'Data keluarga berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formReferenceData(): array
    {
        return [
            'kecamatans' => Kecamatan::where('is_active', true)->with(['desaKelurahans' => fn ($q) => $q->where('is_active', true)->orderBy('nama')])->orderBy('nama')->get(),
            'refStatusPerkawinan' => config('referensi.status_perkawinan'),
            'refPendidikanTerakhir' => config('referensi.pendidikan_terakhir'),
            'refStatusPekerjaan' => config('referensi.status_pekerjaan'),
            'refJenisAtap' => config('referensi.jenis_atap'),
            'refJenisDinding' => config('referensi.jenis_dinding'),
            'refJenisLantai' => config('referensi.jenis_lantai'),
            'refSumberListrik' => config('referensi.sumber_listrik'),
            'refSumberAir' => config('referensi.sumber_air'),
            'refPengelolaanSampah' => config('referensi.pengelolaan_sampah'),
            'refJenisAset' => config('referensi.jenis_aset'),
        ];
    }

    private function generateKodePendataan(): string
    {
        do {
            $kode = 'KK-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Keluarga::where('kode_pendataan', $kode)->exists());

        return $kode;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncAnggota(Keluarga $keluarga, array $rows): void
    {
        $keluarga->anggotaKeluarga()->delete();

        foreach ($rows as $row) {
            if (blank($row['nama_lengkap'] ?? null)) {
                continue;
            }

            AnggotaKeluarga::create([
                'keluarga_id' => $keluarga->id,
                'nik' => $row['nik'] ?? null,
                'nama_lengkap' => $row['nama_lengkap'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'tempat_lahir' => $row['tempat_lahir'] ?? null,
                'tanggal_lahir' => $row['tanggal_lahir'],
                'usia' => now()->diffInYears($row['tanggal_lahir']),
                'hubungan_keluarga' => $row['hubungan_keluarga'],
                'status_perkawinan' => $row['status_perkawinan'] ?? null,
                'pendidikan_terakhir' => $row['pendidikan_terakhir'] ?? null,
                'status_pekerjaan' => $row['status_pekerjaan'] ?? null,
                'disabilitas' => (bool) ($row['disabilitas'] ?? false),
                'jenis_disabilitas' => $row['jenis_disabilitas'] ?? null,
                'penyakit_kronis' => (bool) ($row['penyakit_kronis'] ?? false),
                'jenis_penyakit_kronis' => $row['jenis_penyakit_kronis'] ?? null,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncKondisiEkonomi(Keluarga $keluarga, array $row): void
    {
        $keluarga->kondisiEkonomi()->updateOrCreate(['keluarga_id' => $keluarga->id], [
            'status_pekerjaan_kepala_keluarga' => $row['status_pekerjaan_kepala_keluarga'] ?? null,
            'pekerjaan_utama' => $row['pekerjaan_utama'] ?? null,
            'pekerjaan_tambahan' => $row['pekerjaan_tambahan'] ?? null,
            'jumlah_anggota_bekerja' => $row['jumlah_anggota_bekerja'] ?? 0,
            'jumlah_anggota_tidak_bekerja' => $row['jumlah_anggota_tidak_bekerja'] ?? 0,
            'pendapatan_kepala_keluarga' => $row['pendapatan_kepala_keluarga'] ?? 0,
            'pendapatan_pasangan' => $row['pendapatan_pasangan'] ?? 0,
            'pendapatan_anggota_lainnya' => $row['pendapatan_anggota_lainnya'] ?? 0,
            'pengeluaran_makanan' => $row['pengeluaran_makanan'] ?? 0,
            'pengeluaran_pendidikan' => $row['pengeluaran_pendidikan'] ?? 0,
            'pengeluaran_kesehatan' => $row['pengeluaran_kesehatan'] ?? 0,
            'pengeluaran_listrik' => $row['pengeluaran_listrik'] ?? 0,
            'pengeluaran_air' => $row['pengeluaran_air'] ?? 0,
            'pengeluaran_transportasi' => $row['pengeluaran_transportasi'] ?? 0,
            'pengeluaran_lainnya' => $row['pengeluaran_lainnya'] ?? 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncKondisiRumah(Keluarga $keluarga, array $row): void
    {
        // status_kepemilikan & kondisi_bangunan adalah kolom enum NOT NULL; draft yang belum
        // mengisi bagian ini tidak membuat baris kondisi_rumah sama sekali (lihat PRD Bagian 12).
        if (blank($row['status_kepemilikan'] ?? null) || blank($row['kondisi_bangunan'] ?? null)) {
            return;
        }

        $keluarga->kondisiRumah()->updateOrCreate(['keluarga_id' => $keluarga->id], [
            'status_kepemilikan' => $row['status_kepemilikan'],
            'kondisi_bangunan' => $row['kondisi_bangunan'],
            'jenis_atap' => $row['jenis_atap'] ?? null,
            'jenis_dinding' => $row['jenis_dinding'] ?? null,
            'jenis_lantai' => $row['jenis_lantai'] ?? null,
            'sumber_listrik' => $row['sumber_listrik'] ?? null,
            'sumber_air' => $row['sumber_air'] ?? null,
            'jamban' => (bool) ($row['jamban'] ?? false),
            'septic_tank' => (bool) ($row['septic_tank'] ?? false),
            'drainase' => (bool) ($row['drainase'] ?? false),
            'pengelolaan_sampah' => $row['pengelolaan_sampah'] ?? null,
            'luas_tanah' => $row['luas_tanah'] ?? null,
            'luas_bangunan' => $row['luas_bangunan'] ?? null,
            'jumlah_kamar' => $row['jumlah_kamar'] ?? null,
            'jumlah_penghuni' => $row['jumlah_penghuni'] ?? null,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncAset(Keluarga $keluarga, array $rows): void
    {
        $keluarga->asetKeluarga()->delete();

        foreach ($rows as $row) {
            if (blank($row['jenis_aset'] ?? null)) {
                continue;
            }

            AsetKeluarga::create([
                'keluarga_id' => $keluarga->id,
                'jenis_aset' => $row['jenis_aset'],
                'jumlah' => $row['jumlah'] ?? 1,
                'status_kepemilikan' => $row['status_kepemilikan'] ?? 'milik_sendiri',
                'perkiraan_nilai' => $row['perkiraan_nilai'] ?? null,
                'keterangan' => $row['keterangan'] ?? null,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncKondisiSosial(Keluarga $keluarga, array $row): void
    {
        $keluarga->kondisiSosial()->updateOrCreate(['keluarga_id' => $keluarga->id], [
            'jumlah_anak_usia_sekolah' => $row['jumlah_anak_usia_sekolah'] ?? 0,
            'jumlah_anak_bersekolah' => $row['jumlah_anak_bersekolah'] ?? 0,
            'jumlah_anak_putus_sekolah' => $row['jumlah_anak_putus_sekolah'] ?? 0,
            'jumlah_lansia' => $row['jumlah_lansia'] ?? 0,
            'jumlah_penyandang_disabilitas' => $row['jumlah_penyandang_disabilitas'] ?? 0,
            'jumlah_anggota_sakit' => $row['jumlah_anggota_sakit'] ?? 0,
            'akses_fasilitas_kesehatan' => (bool) ($row['akses_fasilitas_kesehatan'] ?? false),
            'jarak_fasilitas_kesehatan_km' => $row['jarak_fasilitas_kesehatan_km'] ?? null,
            'akses_pendidikan' => (bool) ($row['akses_pendidikan'] ?? false),
            'jarak_sekolah_km' => $row['jarak_sekolah_km'] ?? null,
            'kepemilikan_dokumen_kependudukan' => (bool) ($row['kepemilikan_dokumen_kependudukan'] ?? false),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncProgram(Keluarga $keluarga, array $rows): void
    {
        $keluarga->kepesertaanProgram()->delete();

        foreach ($rows as $row) {
            if (blank($row['nama_program'] ?? null)) {
                continue;
            }

            KepesertaanProgram::create([
                'keluarga_id' => $keluarga->id,
                'nama_program' => $row['nama_program'],
                'status_penerima' => $row['status_penerima'] ?? 'bukan_penerima',
                'tahun_menerima' => $row['tahun_menerima'] ?? null,
                'sumber_instansi' => $row['sumber_instansi'] ?? null,
                'keterangan' => $row['keterangan'] ?? null,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncDokumen(Keluarga $keluarga, Request $request, array $rows): void
    {
        foreach ($rows as $index => $row) {
            $file = $request->file("dokumen.{$index}.file");

            if (! $file) {
                continue;
            }

            $path = $file->store("dokumen-keluarga/{$keluarga->id}", 'public');

            DokumenKeluarga::create([
                'keluarga_id' => $keluarga->id,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'jenis_dokumentasi' => $row['jenis_dokumentasi'],
                'tanggal_upload' => now(),
                'user_id' => $request->user()->id,
                'keterangan' => $row['keterangan'] ?? null,
            ]);
        }
    }

    private function ubahStatus(Keluarga $keluarga, string $status, int $verifikatorId, ?string $catatan): void
    {
        $keluarga->update(['status_data' => $status]);

        VerifikasiKemiskinan::create([
            'keluarga_id' => $keluarga->id,
            'verifikator_id' => $verifikatorId,
            'status' => $status,
            'catatan' => $catatan,
            'tanggal_verifikasi' => now(),
        ]);
    }
}
