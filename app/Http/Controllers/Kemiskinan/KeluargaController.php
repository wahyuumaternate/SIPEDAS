<?php

namespace App\Http\Controllers\Kemiskinan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kemiskinan\StoreKeluargaRequest;
use App\Http\Requests\Kemiskinan\UpdateKeluargaRequest;
use App\Models\AnggotaKeluarga;
use App\Models\AsetKeluarga;
use App\Models\AuditLog;
use App\Models\DokumenKeluarga;
use App\Models\DraftFormulir;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\KepesertaanProgram;
use App\Models\VerifikasiKemiskinan;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KeluargaController extends Controller
{
    /**
     * Status pekerjaan anggota yang tidak dihitung sebagai "bekerja".
     *
     * @var array<int, string>
     */
    private const BUKAN_BEKERJA = ['tidak_bekerja', 'pelajar', 'mengurus_rumah_tangga', 'pensiunan'];

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
            'draftTertunda' => $request->user()->hasPermission('kemiskinan.create')
                ? DraftFormulir::query()->milik($request->user(), 'kemiskinan.create')->first()
                : null,
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
        $anggota = $this->anggotaTerisi($data);
        $kepala = $anggota->firstWhere('hubungan_keluarga', 'kepala_keluarga');

        $keluarga = DB::transaction(function () use ($data, $request, $anggota, $kepala) {
            $keluarga = Keluarga::create([
                'kode_pendataan' => $this->generateKodePendataan(),
                'nomor_kk' => $data['nomor_kk'],
                // Identitas kepala keluarga diambil dari anggota berhubungan "Kepala Keluarga" (diinput sekali).
                'nik_kepala_keluarga' => $kepala['nik'],
                'nama_kepala_keluarga' => $kepala['nama_lengkap'],
                'nomor_hp' => $data['nomor_hp'] ?? null,
                'jumlah_anggota_keluarga' => $anggota->count(),
                'status_perkawinan' => $kepala['status_perkawinan'] ?? null,
                'alamat' => $data['alamat'],
                'rt' => $data['rt'],
                'rw' => $data['rw'],
                'kecamatan_id' => $data['kecamatan_id'],
                'desa_kelurahan_id' => $data['desa_kelurahan_id'],
                'desil' => $data['desil'] ?? null,
                'status_dtsen' => $data['status_dtsen'] ?? 'aktif',
                'petugas_id' => $request->user()->id,
                'tanggal_input' => now(),
                'status_data' => 'dalam_verifikasi',
                'tanggal_pendataan' => now(),
            ]);

            $this->syncAnggota($keluarga, $data['anggota'] ?? []);
            $this->syncKondisiEkonomi($keluarga, $data['kondisi_ekonomi'] ?? [], $anggota, $kepala);
            $this->syncKondisiRumah($keluarga, $data['kondisi_rumah'] ?? []);
            $this->syncAset($keluarga, $data['aset'] ?? []);
            $this->syncKondisiSosial($keluarga, $data['kondisi_sosial'] ?? [], $anggota);
            $this->syncProgram($keluarga, $data['program'] ?? []);
            $this->syncDokumen($keluarga, $request, $data['dokumen'] ?? []);

            $this->ubahStatus($keluarga, 'dalam_verifikasi', $request->user()->id, null);

            return $keluarga;
        });

        DraftFormulir::selesai($request->user(), 'kemiskinan.create');

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
        $anggota = $this->anggotaTerisi($data);
        $kepala = $anggota->firstWhere('hubungan_keluarga', 'kepala_keluarga');

        DB::transaction(function () use ($data, $request, $keluarga, $anggota, $kepala) {
            $keluarga->update([
                'nomor_kk' => $data['nomor_kk'],
                // Identitas kepala keluarga diambil dari anggota berhubungan "Kepala Keluarga" (diinput sekali).
                'nik_kepala_keluarga' => $kepala['nik'],
                'nama_kepala_keluarga' => $kepala['nama_lengkap'],
                'nomor_hp' => $data['nomor_hp'] ?? null,
                'jumlah_anggota_keluarga' => $anggota->count(),
                'status_perkawinan' => $kepala['status_perkawinan'] ?? null,
                'alamat' => $data['alamat'],
                'rt' => $data['rt'],
                'rw' => $data['rw'],
                'kecamatan_id' => $data['kecamatan_id'],
                'desa_kelurahan_id' => $data['desa_kelurahan_id'],
                'desil' => $data['desil'] ?? null,
                'status_dtsen' => $data['status_dtsen'] ?? 'aktif',
            ]);

            $this->syncAnggota($keluarga, $data['anggota'] ?? []);
            $this->syncKondisiEkonomi($keluarga, $data['kondisi_ekonomi'] ?? [], $anggota, $kepala);
            $this->syncKondisiRumah($keluarga, $data['kondisi_rumah'] ?? []);
            $this->syncAset($keluarga, $data['aset'] ?? []);
            $this->syncKondisiSosial($keluarga, $data['kondisi_sosial'] ?? [], $anggota);
            $this->syncProgram($keluarga, $data['program'] ?? []);
            $this->syncDokumen($keluarga, $request, $data['dokumen'] ?? []);

            // Data yang diedit ulang setelah perlu perbaikan otomatis kembali ke antrean verifikasi.
            if ($keluarga->status_data === 'perlu_perbaikan') {
                $this->ubahStatus($keluarga, 'dalam_verifikasi', $request->user()->id, 'Dikirim kembali setelah perbaikan.');
            }
        });

        DraftFormulir::selesai($request->user(), 'kemiskinan.edit.'.$keluarga->id);

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
     * Baris anggota keluarga yang benar-benar diisi (punya nama).
     *
     * @param  array<string, mixed>  $data
     * @return Collection<int, array<string, mixed>>
     */
    private function anggotaTerisi(array $data): Collection
    {
        return collect($data['anggota'] ?? [])
            ->filter(fn (array $row) => filled($row['nama_lengkap'] ?? null))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function formReferenceData(): array
    {
        return [
            'kecamatans' => Kecamatan::where('is_active', true)->with(['desaKelurahans' => fn ($q) => $q->where('is_active', true)->orderBy('nama')])->orderBy('nama')->get(),
            'refStatusPerkawinan' => config('referensi.status_perkawinan'),
            'refHubunganKeluarga' => config('referensi.hubungan_keluarga'),
            'refPendidikanTerakhir' => config('referensi.pendidikan_terakhir'),
            'refStatusPekerjaan' => config('referensi.status_pekerjaan'),
            'refStatusSekolah' => config('referensi.status_sekolah'),
            'refPenyakitKronis' => config('referensi.penyakit_kronis'),
            'refBahanBakarMasak' => config('referensi.bahan_bakar_memasak'),
            'refJenisJamban' => config('referensi.jenis_jamban'),
            'refJenisSepticTank' => config('referensi.jenis_septic_tank'),
            'refDayaListrik' => config('referensi.daya_listrik'),
            'refBantuanPendidikan' => config('referensi.bantuan_pendidikan'),
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
                'usia' => (int) Carbon::parse($row['tanggal_lahir'])->diffInYears(now()),
                'hubungan_keluarga' => $row['hubungan_keluarga'],
                'status_perkawinan' => $row['status_perkawinan'] ?? null,
                'pendidikan_terakhir' => $row['pendidikan_terakhir'] ?? null,
                // Status sekolah hanya relevan untuk anggota berhubungan "Anak".
                'status_sekolah' => $row['hubungan_keluarga'] === 'anak' ? ($row['status_sekolah'] ?? null) : null,
                'status_pekerjaan' => $row['status_pekerjaan'] ?? null,
                'pekerjaan_lainnya' => ($row['status_pekerjaan'] ?? null) === 'lainnya' ? ($row['pekerjaan_lainnya'] ?? null) : null,
                'disabilitas' => (bool) ($row['disabilitas'] ?? false),
                'jenis_disabilitas' => $row['jenis_disabilitas'] ?? null,
                // Penyakit kronis ditentukan dari pilihan jenis penyakit (kosong = tidak ada).
                'penyakit_kronis' => filled($row['jenis_penyakit_kronis'] ?? null),
                'jenis_penyakit_kronis' => $row['jenis_penyakit_kronis'] ?? null,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncKondisiEkonomi(Keluarga $keluarga, array $row, Collection $anggota, array $kepala): void
    {
        $bekerja = $anggota->filter(fn (array $a) => filled($a['status_pekerjaan'] ?? null)
            && ! in_array($a['status_pekerjaan'], self::BUKAN_BEKERJA, true))->count();

        $keluarga->kondisiEkonomi()->updateOrCreate(['keluarga_id' => $keluarga->id], [
            // Status pekerjaan & jumlah anggota (tidak) bekerja diturunkan dari data anggota keluarga.
            'status_pekerjaan_kepala_keluarga' => $kepala['status_pekerjaan'] ?? null,
            'pekerjaan_utama' => $row['pekerjaan_utama'] ?? null,
            'pekerjaan_utama_lainnya' => ($row['pekerjaan_utama'] ?? null) === 'lainnya' ? ($row['pekerjaan_utama_lainnya'] ?? null) : null,
            'pekerjaan_tambahan' => $row['pekerjaan_tambahan'] ?? null,
            'perlindungan_ketenagakerjaan' => (bool) ($row['perlindungan_ketenagakerjaan'] ?? false),
            'bahan_bakar_memasak' => $row['bahan_bakar_memasak'] ?? null,
            'jumlah_anggota_bekerja' => $bekerja,
            'jumlah_anggota_tidak_bekerja' => $anggota->count() - $bekerja,
            'pendapatan_kepala_keluarga' => $row['pendapatan_kepala_keluarga'] ?? 0,
            'pendapatan_pasangan' => $row['pendapatan_pasangan'] ?? 0,
            'pendapatan_anggota_lainnya' => $row['pendapatan_anggota_lainnya'] ?? 0,
            'pengeluaran_makanan' => $row['pengeluaran_makanan'] ?? 0,
            'pengeluaran_pendidikan' => $row['pengeluaran_pendidikan'] ?? 0,
            'pengeluaran_kesehatan' => $row['pengeluaran_kesehatan'] ?? 0,
            'pengeluaran_listrik' => $row['pengeluaran_listrik'] ?? 0,
            'pengeluaran_air' => $row['pengeluaran_air'] ?? 0,
            'pengeluaran_transportasi' => $row['pengeluaran_transportasi'] ?? 0,
            'pengeluaran_pakaian' => $row['pengeluaran_pakaian'] ?? 0,
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
            // Kapasitas daya listrik hanya ditanyakan untuk rumah milik sendiri.
            'daya_listrik' => $row['status_kepemilikan'] === 'milik_sendiri' ? ($row['daya_listrik'] ?? null) : null,
            'sumber_air' => $row['sumber_air'] ?? null,
            // Kolom ya/tidak diturunkan dari pilihan jenis agar laporan lama tetap berjalan.
            'jenis_jamban' => $row['jenis_jamban'] ?? null,
            'jamban' => filled($row['jenis_jamban'] ?? null) && $row['jenis_jamban'] !== 'tidak_ada',
            'jenis_septic_tank' => $row['jenis_septic_tank'] ?? null,
            'septic_tank' => in_array($row['jenis_septic_tank'] ?? null, ['tangki_septik', 'ipal_komunal'], true),
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
                'jenis_aset_lainnya' => $row['jenis_aset'] === 'lainnya' ? ($row['jenis_aset_lainnya'] ?? null) : null,
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
    private function syncKondisiSosial(Keluarga $keluarga, array $row, Collection $anggota): void
    {
        $usia = $anggota->map(fn (array $a) => Carbon::parse($a['tanggal_lahir'])->age);
        $anak = $anggota->filter(fn (array $a) => ($a['hubungan_keluarga'] ?? null) === 'anak');
        $memilikiPbi = (bool) ($row['memiliki_pbi'] ?? false);

        $keluarga->kondisiSosial()->updateOrCreate(['keluarga_id' => $keluarga->id], [
            // Jumlah anak usia sekolah (7–18 th), lansia (≥ 60 th), disabilitas & sakit kronis diturunkan dari data anggota.
            'jumlah_anak_usia_sekolah' => $usia->filter(fn (int $u) => $u >= 7 && $u <= 18)->count(),
            // Jumlah anak bersekolah & putus sekolah dihitung dari status sekolah anggota berhubungan "Anak".
            'jumlah_anak_bersekolah' => $anak->where('status_sekolah', 'sedang_sekolah')->count(),
            'jumlah_anak_putus_sekolah' => $anak->where('status_sekolah', 'putus_sekolah')->count(),
            'jumlah_lansia' => $usia->filter(fn (int $u) => $u >= 60)->count(),
            'jumlah_penyandang_disabilitas' => $anggota->filter(fn (array $a) => (bool) ($a['disabilitas'] ?? false))->count(),
            'jumlah_anggota_sakit' => $anggota->filter(fn (array $a) => filled($a['jenis_penyakit_kronis'] ?? null))->count(),
            'akses_fasilitas_kesehatan' => (bool) ($row['akses_fasilitas_kesehatan'] ?? false),
            'jarak_fasilitas_kesehatan_km' => $row['jarak_fasilitas_kesehatan_km'] ?? null,
            'jarak_sekolah_km' => $row['jarak_sekolah_km'] ?? null,
            'bantuan_pendidikan' => $row['bantuan_pendidikan'] ?? null,
            'bantuan_pendidikan_lainnya' => ($row['bantuan_pendidikan'] ?? null) === 'lainnya' ? ($row['bantuan_pendidikan_lainnya'] ?? null) : null,
            // Peserta PBI otomatis tercatat memiliki JKN.
            'memiliki_jkn' => $memilikiPbi || (bool) ($row['memiliki_jkn'] ?? false),
            'memiliki_pbi' => $memilikiPbi,
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
