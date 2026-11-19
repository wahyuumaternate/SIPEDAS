<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\SasaranStunting;
use App\Permission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class LaporanController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const STATUS_LABEL = [
        'dalam_verifikasi' => 'Dalam Verifikasi',
        'perlu_perbaikan' => 'Perlu Perbaikan',
        'valid' => 'Valid',
        'tidak_valid' => 'Tidak Valid',
    ];

    /**
     * Kategori kolom tabulasi DTSEN beserta label header-nya.
     *
     * @var array<string, string>
     */
    public const KATEGORI_DTSEN = [
        'desil_1' => 'Desil 1',
        'desil_2' => 'Desil 2',
        'desil_3' => 'Desil 3',
        'desil_4' => 'Desil 4',
        'desil_5' => 'Desil 5',
        'desil_6_10' => 'Desil 6 - 10',
        'belum' => 'Belum Pemeringkatan',
        'nonaktif' => 'Nonaktif',
    ];

    /**
     * Kategori masalah gizi pada Tabel 2 Prevalensi.
     *
     * @var array<string, string>
     */
    public const KATEGORI_PREVALENSI = [
        'stunting' => 'Stunting',
        'gizi_buruk' => 'Gizi Buruk',
        'gizi_kurang' => 'Gizi Kurang',
        'bb_kurang' => 'Berat Badan Kurang',
    ];

    /**
     * Kolom Tabel 1 Demografi per kecamatan (format Laporan Analisis Situasi).
     * Kolom yang tidak tersedia di aplikasi bernilai null dan ditampilkan sebagai "–".
     *
     * @var array<string, string>
     */
    public const KOLOM_DEMOGRAFI = [
        'penduduk' => 'Penduduk',
        'rumah_tangga' => 'Rumah Tangga',
        'anak_0_5' => 'Anak Usia 0–5 bulan',
        'anak_6_23' => 'Anak Usia 6–23 bulan',
        'anak_24_59' => 'Anak Usia 24–59 bulan',
        'remaja_putri' => 'Remaja Putri',
        'calon_pengantin' => 'Calon Pengantin',
        'ibu_hamil' => 'Ibu Hamil',
        'ibu_nifas' => 'Ibu Nifas',
    ];

    /**
     * Kelompok sasaran capaian layanan per kecamatan, mengikuti urutan & label Laporan Analisis Situasi.
     *
     * @var array<string, array{judul: string, label: string, kecil?: bool, indikator: array<string, string>}>
     */
    public const INDIKATOR_STUNTING = [
        'ibu_nifas' => ['judul' => 'Kelompok Sasaran: Ibu Nifas', 'label' => 'Ibu Nifas', 'indikator' => [
            'kb' => 'Ibu Nifas',
        ]],
        'ibu_hamil' => ['judul' => 'Kelompok Sasaran: Ibu Hamil', 'label' => 'Ibu Hamil', 'indikator' => [
            'ttd' => 'Konsumsi TTD/MMS',
            'anc' => 'Pemeriksaan Kehamilan 6 kali',
            'pmt_kek' => 'PMT Bumil KEK',
            'makanan_bergizi' => 'Makanan bergizi seimbang pada ibu hamil',
            'kb' => 'Keluarga Berencana dan jarak kelahiran',
        ]],
        'balita' => ['judul' => 'Capaian Layanan Balita Per Kecamatan', 'label' => 'Balita', 'indikator' => [
            'gizi_buruk' => 'Tata laksana gizi buruk (cl_16)',
            'dipantau' => 'Pantau pertumbuhan dan perkembangan (cl_17)',
            'gizi_kurang' => 'Gizi Kurang mendapat tambahan asupan gizi (cl_18)',
            'oralit_zinc' => 'Pemberian oralit dan zinc sesuai standar yang diare (cl_19)',
            'pneumonia' => 'Penemuan kasus pneumonia balita (cl_20)',
            'popm' => 'POPM cacingan pada balita (cl_21)',
        ]],
        'baduta' => ['judul' => 'Capaian Layanan Baduta Per Kecamatan', 'label' => 'Baduta', 'kecil' => true, 'indikator' => [
            'imd' => 'Inisiasi menyusui dini',
            'asi_eksklusif' => 'Bayi usia 0-5 bulan ASI ekslusif',
            'mpasi' => 'Anak usia 6-23 bulan MP-ASI',
            'asi_6_23' => 'Anak usia 6-23 bulan mendapat air susu ibu (ASI)',
            'gizi_buruk' => 'Anak gizi buruk yang mendapat tata laksana gizi buruk',
            'dipantau' => 'Anak baduta dipantau pertumbuhan dan perkembangan',
            'gizi_kurang' => 'Anak berusia baduta gizi kurang yang mendapat asupan gizi',
            'imunisasi' => 'Bayi baduta yang memperoleh imunisasi rutin lengkap',
            'oralit_zinc' => 'Pemberian oralit dan zinc pada anak usia 6-23 bulan yang diare',
            'popm' => 'Pemberian POPM cacingan pada anak usia 6-23',
        ]],
        'remaja_putri' => ['judul' => 'Capaian Layanan Remaja Putri', 'label' => 'Remaja Putri', 'indikator' => [
            'ttd' => 'Tablet Tambah darah',
            'anemia' => 'Pemeriksaan Anemia',
        ]],
        'keluarga' => ['judul' => 'Kelompok Sasaran: Keluarga dan Masyarakat', 'label' => 'Keluarga dan Masyarakat', 'indikator' => [
            'air_minum' => 'Akses Terhadap Air minum aman',
            'sanitasi' => 'Akses Terhadap Sanitasi aman',
            'jkn' => 'Memiliki JKN',
            'kependudukan' => 'Data Kependudukan',
        ]],
    ];

    /**
     * Sumbu grafik radar capaian layanan (urutan & label mengikuti Laporan Analisis Situasi),
     * beserta target (%) tiap indikator. Indikator yang belum ada datanya tidak digambar.
     *
     * @var array<string, array{label: string, target: int}>
     */
    public const RADAR_STUNTING = [
        'ibu_hamil.pmt_kek' => ['label' => 'PMT IH KEK', 'target' => 100],
        'ibu_hamil.ttd' => ['label' => 'TTD Ibu hamil', 'target' => 100],
        'ibu_hamil.makanan_bergizi' => ['label' => 'IH dapat makanan bergizi', 'target' => 100],
        'ibu_hamil.anc' => ['label' => 'IH Periksa', 'target' => 100],
        'ibu_nifas.kb' => ['label' => 'Ibu Nifas ber KB', 'target' => 100],
        'baduta.imd' => ['label' => 'Inisiasi Menyusui Dini', 'target' => 100],
        'baduta.asi_eksklusif' => ['label' => 'Asi Eksklusif', 'target' => 100],
        'baduta.mpasi' => ['label' => 'Bayi >6 bln dapat MP ASI', 'target' => 100],
        'baduta.asi_6_23' => ['label' => 'Usia 6-23 bln dapat ASI', 'target' => 100],
        'baduta.gizi_buruk' => ['label' => 'Tata laksana gizi buruk utk baduta gizi buruk', 'target' => 100],
        'baduta.dipantau' => ['label' => 'Anak 0-23 dipantau', 'target' => 100],
        'baduta.gizi_kurang' => ['label' => 'Tambahan gizi utk baduta gizi kurang', 'target' => 100],
        'baduta.imunisasi' => ['label' => 'Baduta IDL', 'target' => 100],
        'baduta.oralit_zinc' => ['label' => 'Baduta dapat oralit dan Zinc', 'target' => 100],
        'baduta.popm' => ['label' => 'Baduta dapat obat cacing', 'target' => 100],
        'balita.gizi_buruk' => ['label' => 'Tata laksana Balita gizi bermasalah', 'target' => 100],
        'balita.dipantau' => ['label' => 'Balita dipantau', 'target' => 100],
        'balita.gizi_kurang' => ['label' => 'Tambahan gizi utk balita gizi kurang', 'target' => 100],
        'balita.oralit_zinc' => ['label' => 'Anak 24-59 bln dapat oralit dan zinc', 'target' => 100],
        'balita.pneumonia' => ['label' => 'Balita pneumonia', 'target' => 100],
        'balita.popm' => ['label' => 'Balita dapat obat cacing', 'target' => 100],
        'remaja_putri.ttd' => ['label' => 'TTD Rematri', 'target' => 100],
        'remaja_putri.anemia' => ['label' => 'Skrining Anemia Rematri', 'target' => 100],
        'keluarga.air_minum' => ['label' => 'RT Air Minum Aman', 'target' => 100],
        'keluarga.sanitasi' => ['label' => 'RT Sanitasi Aman', 'target' => 100],
        'keluarga.jkn' => ['label' => 'Kepemilikan JKN', 'target' => 100],
    ];

    /**
     * Sumber air minum yang dianggap aman (layak).
     *
     * @var array<int, string>
     */
    private const SUMBER_AIR_AMAN = ['pdam', 'sumur_bor', 'beli'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $modul = $request->string('modul')->trim()->value();
        $modul = in_array($modul, ['kemiskinan', 'stunting'], true) ? $modul : ($user->hasPermission(Permission::KemiskinanView) ? 'kemiskinan' : 'stunting');

        abort_unless(
            ($modul === 'kemiskinan' && $user->hasPermission(Permission::KemiskinanView))
                || ($modul === 'stunting' && $user->hasPermission(Permission::StuntingView)),
            403
        );

        $filters = $request->only(['kecamatan_id', 'desa_kelurahan_id', 'status_data', 'dari', 'sampai']);

        $tabulasi = $modul === 'kemiskinan'
            ? ['tabulasiDtsen' => $this->tabulasiDtsen($filters)]
            : ['tabulasiStunting' => $this->tabulasiStunting($filters, $this->tahunSasaran($request))];

        return view('laporan.index', [
            'modul' => $modul,
            'bisaExport' => $user->hasPermission(Permission::Export),
            'bisaLihatKemiskinan' => $user->hasPermission(Permission::KemiskinanView),
            'bisaLihatStunting' => $user->hasPermission(Permission::StuntingView),
            'kecamatans' => Kecamatan::where('is_active', true)->orderBy('nama')->get(),
            'desaKelurahans' => DesaKelurahan::where('is_active', true)->orderBy('nama')->get(),
            'filters' => $filters,
            'periode' => $request->string('periode')->trim()->limit(100, '')->value(),
            'tahun' => $this->tahunSasaran($request),
            'daftarTahun' => range((int) now()->year + 1, (int) now()->year - 5),
            'statusLabel' => self::STATUS_LABEL,
            ...$tabulasi,
        ]);
    }

    public function export(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user->hasPermission(Permission::Export), 403);

        $modul = $request->string('modul')->trim()->value();
        $modul = in_array($modul, ['kemiskinan', 'stunting'], true) ? $modul : 'kemiskinan';

        abort_unless(
            ($modul === 'kemiskinan' && $user->hasPermission(Permission::KemiskinanView))
                || ($modul === 'stunting' && $user->hasPermission(Permission::StuntingView)),
            403
        );

        $filters = $request->only(['kecamatan_id', 'desa_kelurahan_id', 'status_data', 'dari', 'sampai']);
        $periode = $request->string('periode')->trim()->limit(100, '')->value();
        $waktu = now()->format('Ymd-His');

        if ($modul === 'kemiskinan') {
            return Pdf::loadView('laporan.pdf-dtsen', [
                'periode' => $periode,
                'tabulasi' => $this->tabulasiDtsen($filters),
            ])->setPaper('a4', 'landscape')->download('tabulasi-dtsen-'.$waktu.'.pdf');
        }

        return Pdf::loadView('laporan.pdf-tabulasi-stunting', [
            'periode' => $periode,
            'tahun' => $this->tahunSasaran($request),
            'tabulasi' => $this->tabulasiStunting($filters, $this->tahunSasaran($request)),
            'grafikRadar' => $this->gambarGrafikValid($request->input('grafik_radar')),
        ])->setPaper('a4', 'portrait')->download('tabulasi-stunting-'.$waktu.'.pdf');
    }

    /**
     * Tabulasi DTSEN per kecamatan & desa/kelurahan: jumlah KK/jiwa per desil,
     * belum pemeringkatan, dan nonaktif. Jumlah KK/jiwa hanya menghitung KK aktif.
     *
     * @param  array<string, mixed>  $filters
     * @return array{kecamatans: Collection<int, array{nama: string, desa: Collection<int, array{nama: string, nilai: array<string, array{kk: int, jiwa: int}>}>, jumlah: array<string, array{kk: int, jiwa: int}>}>, total: array<string, array{kk: int, jiwa: int}>}
     */
    private function tabulasiDtsen(array $filters): array
    {
        $kondisi = [
            'jumlah' => "keluargas.status_dtsen = 'aktif'",
            'desil_1' => "keluargas.status_dtsen = 'aktif' AND keluargas.desil = 1",
            'desil_2' => "keluargas.status_dtsen = 'aktif' AND keluargas.desil = 2",
            'desil_3' => "keluargas.status_dtsen = 'aktif' AND keluargas.desil = 3",
            'desil_4' => "keluargas.status_dtsen = 'aktif' AND keluargas.desil = 4",
            'desil_5' => "keluargas.status_dtsen = 'aktif' AND keluargas.desil = 5",
            'desil_6_10' => "keluargas.status_dtsen = 'aktif' AND keluargas.desil BETWEEN 6 AND 10",
            'belum' => "keluargas.status_dtsen = 'aktif' AND keluargas.desil IS NULL",
            'nonaktif' => "keluargas.status_dtsen = 'nonaktif'",
        ];

        $select = collect($kondisi)->map(fn (string $syarat, string $kunci) => "SUM(CASE WHEN {$syarat} THEN 1 ELSE 0 END) AS {$kunci}_kk, "
            ."SUM(CASE WHEN {$syarat} THEN keluargas.jumlah_anggota_keluarga ELSE 0 END) AS {$kunci}_jiwa")->implode(', ');

        $agregat = $this->terapkanFilter(Keluarga::query(), $filters)
            ->selectRaw('keluargas.desa_kelurahan_id, '.$select)
            ->groupBy('keluargas.desa_kelurahan_id')
            ->get()
            ->keyBy('desa_kelurahan_id');

        $desaKelurahans = $this->daftarDesaLaporan($filters, $agregat->keys());

        $kosong = collect($kondisi)->map(fn () => ['kk' => 0, 'jiwa' => 0])->all();
        $jumlahkan = function (array $a, array $b): array {
            foreach ($a as $kunci => $nilai) {
                $a[$kunci] = ['kk' => $nilai['kk'] + $b[$kunci]['kk'], 'jiwa' => $nilai['jiwa'] + $b[$kunci]['jiwa']];
            }

            return $a;
        };

        $kecamatans = $desaKelurahans->groupBy('kecamatan_id')->map(function (Collection $daftarDesa) use ($agregat, $kondisi, $kosong, $jumlahkan) {
            $desa = $daftarDesa->map(function (DesaKelurahan $desa) use ($agregat, $kondisi) {
                $baris = $agregat->get($desa->id);

                return [
                    'nama' => $desa->nama,
                    'nilai' => collect($kondisi)->map(fn ($syarat, string $kunci) => [
                        'kk' => (int) ($baris?->{$kunci.'_kk'} ?? 0),
                        'jiwa' => (int) ($baris?->{$kunci.'_jiwa'} ?? 0),
                    ])->all(),
                ];
            })->values();

            return [
                'nama' => $daftarDesa->first()->nama_kecamatan,
                'desa' => $desa,
                'jumlah' => $desa->reduce(fn (array $carry, array $baris) => $jumlahkan($carry, $baris['nilai']), $kosong),
            ];
        })->values();

        return [
            'kecamatans' => $kecamatans,
            'total' => $kecamatans->reduce(fn (array $carry, array $kecamatan) => $jumlahkan($carry, $kecamatan['jumlah']), $kosong),
        ];
    }

    /**
     * Desa/kelurahan untuk tabulasi: yang aktif ditambah yang memiliki data, sesuai filter wilayah,
     * diurutkan berdasarkan kode kecamatan lalu kode desa.
     *
     * @param  array<string, mixed>  $filters
     * @param  Collection<int, int>  $desaBerdata
     * @return Collection<int, DesaKelurahan>
     */
    private function daftarDesaLaporan(array $filters, Collection $desaBerdata): Collection
    {
        return DesaKelurahan::query()
            ->join('kecamatans', 'kecamatans.id', '=', 'desa_kelurahans.kecamatan_id')
            ->where(fn (Builder $q) => $q->where('desa_kelurahans.is_active', true)->orWhereIn('desa_kelurahans.id', $desaBerdata))
            ->when($filters['kecamatan_id'] ?? null, fn (Builder $q, $id) => $q->where('desa_kelurahans.kecamatan_id', $id))
            ->when($filters['desa_kelurahan_id'] ?? null, fn (Builder $q, $id) => $q->where('desa_kelurahans.id', $id))
            ->orderBy('kecamatans.kode')
            ->orderBy('desa_kelurahans.kode')
            ->select('desa_kelurahans.id', 'desa_kelurahans.nama', 'desa_kelurahans.kecamatan_id', 'kecamatans.nama as nama_kecamatan')
            ->get();
    }

    /**
     * Tahun data sasaran yang dipakai laporan stunting (default tahun berjalan).
     */
    private function tahunSasaran(Request $request): int
    {
        $tahun = $request->integer('tahun', (int) now()->year);

        return $tahun >= 2000 && $tahun <= 2100 ? $tahun : (int) now()->year;
    }

    /**
     * Tabulasi stunting mengikuti format Laporan Analisis Situasi: demografi per kecamatan,
     * prevalensi per desa/kelurahan (pengukuran terakhir tiap anak), dan capaian layanan per kecamatan.
     * Angka populasi (demografi, remaja putri) diambil dari Data Sasaran tahun terkait bila sudah diisi.
     *
     * @param  array<string, mixed>  $filters
     * @return array{demografi: Collection<int, array{nama: string, nilai: array<string, int|null>}>, prevalensi: array{desa: Collection<int, array{nama: string, diukur: int, kasus: int, prevalensi: float}>, total: array{diukur: int, kasus: int, prevalensi: float}}, capaian: array{kecamatans: Collection<int, array{nama: string, nilai: array<string, array<string, array{jml: int, sasaran: int, persen: float}|null>>}>, total: array<string, array<string, array{jml: int, sasaran: int, persen: float}|null>>}}
     */
    private function tabulasiStunting(array $filters, int $tahun): array
    {
        $anaks = $this->terapkanFilter(Anak::query(), $filters)
            // Anak nonaktif (usia lewat, pindah, meninggal) tidak dihitung; yang nonaktif karena sudah
            // tidak stunting tetap dihitung agar prevalensi tidak terlihat lebih tinggi dari sebenarnya.
            ->where(fn (Builder $q) => $q->where('anaks.status_pemantauan', 'aktif')->orWhere('anaks.alasan_nonaktif', 'sudah_tidak_stunting'))
            ->with(['pengukuranTerbaru', 'riwayatKehamilan', 'riwayatKelahiran', 'riwayatKesehatan', 'asiMpasi', 'sanitasi'])
            ->withCount('pengukurans')
            ->get();

        $desaKelurahans = $this->daftarDesaLaporan($filters, $anaks->pluck('desa_kelurahan_id')->unique()->values());
        $anakDalamWilayah = $anaks->whereIn('desa_kelurahan_id', $desaKelurahans->pluck('id'));
        $anakPerDesa = $anakDalamWilayah->groupBy('desa_kelurahan_id');
        $anakPerKecamatan = $anakDalamWilayah->groupBy('kecamatan_id');
        $kecamatans = $desaKelurahans->unique('kecamatan_id')->values();
        $sasaran = SasaranStunting::query()
            ->where('tahun', $tahun)
            ->whereIn('kecamatan_id', $kecamatans->pluck('kecamatan_id'))
            ->get()
            ->keyBy('kecamatan_id');

        return [
            'demografi' => $kecamatans->map(fn (DesaKelurahan $desa) => [
                'nama' => $desa->nama_kecamatan,
                'nilai' => $this->hitungDemografi($anakPerKecamatan->get($desa->kecamatan_id, collect()), $sasaran->get($desa->kecamatan_id)),
            ]),
            'prevalensi' => [
                'desa' => $desaKelurahans->map(fn (DesaKelurahan $desa) => [
                    'nama' => $desa->nama,
                    ...$this->hitungPrevalensi($anakPerDesa->get($desa->id, collect())),
                ])->values(),
                'total' => $this->hitungPrevalensi($anakDalamWilayah),
            ],
            'capaian' => [
                'kecamatans' => $kecamatans->map(fn (DesaKelurahan $desa) => [
                    'nama' => $desa->nama_kecamatan,
                    'nilai' => $this->hitungCapaianLayanan(
                        $anakPerKecamatan->get($desa->kecamatan_id, collect()),
                        $sasaran->only([$desa->kecamatan_id])
                    ),
                ]),
                'total' => $this->hitungCapaianLayanan($anakDalamWilayah, $sasaran),
            ],
        ];
    }

    /**
     * Kolom demografi dari Data Sasaran; jumlah anak per kelompok usia memakai data anak tercatat
     * bila sasarannya belum diisi.
     *
     * @param  Collection<int, Anak>  $anaks
     * @return array<string, int|null>
     */
    private function hitungDemografi(Collection $anaks, ?SasaranStunting $sasaran): array
    {
        $usia = $anaks->map(fn (Anak $a) => $a->usia_terkini_bulan)->filter(fn (?int $u) => $u !== null);
        $dariAnak = [
            'anak_0_5' => $usia->filter(fn (int $u) => $u <= 5)->count(),
            'anak_6_23' => $usia->filter(fn (int $u) => $u >= 6 && $u <= 23)->count(),
            'anak_24_59' => $usia->filter(fn (int $u) => $u >= 24 && $u <= 59)->count(),
        ];

        return collect(self::KOLOM_DEMOGRAFI)
            ->map(fn (string $label, string $kolom) => $sasaran?->{$kolom} ?? $dariAnak[$kolom] ?? null)
            ->all();
    }

    /**
     * Prevalensi masalah gizi dari pengukuran terakhir tiap anak; penyebut = anak yang sudah diukur.
     * Stunting dari TB/U, gizi buruk & gizi kurang dari BB/TB, berat badan kurang dari BB/U.
     * Kunci 'kasus' & 'prevalensi' adalah angka stunting.
     *
     * @param  Collection<int, Anak>  $anaks
     * @return array{diukur: int, kasus: int, prevalensi: float, kategori: array<string, array{kasus: int, persen: float}>}
     */
    private function hitungPrevalensi(Collection $anaks): array
    {
        $diukur = $anaks->filter(fn (Anak $anak) => $anak->pengukuranTerbaru !== null);
        $syarat = [
            'stunting' => fn (Anak $a) => str_starts_with((string) $a->pengukuranTerbaru->hasil_kategori, 'stunting'),
            'gizi_buruk' => fn (Anak $a) => $a->pengukuranTerbaru->status_gizi === 'gizi_buruk',
            'gizi_kurang' => fn (Anak $a) => $a->pengukuranTerbaru->status_gizi === 'gizi_kurang',
            'bb_kurang' => fn (Anak $a) => in_array($a->pengukuranTerbaru->status_bb_u, ['bb_kurang', 'bb_sangat_kurang'], true),
        ];

        $kategori = collect($syarat)->map(function (callable $cocok) use ($diukur) {
            $kasus = $diukur->filter($cocok)->count();

            return ['kasus' => $kasus, 'persen' => $this->persen($kasus, $diukur->count())];
        })->all();

        return [
            'diukur' => $diukur->count(),
            'kasus' => $kategori['stunting']['kasus'],
            'prevalensi' => $kategori['stunting']['persen'],
            'kategori' => $kategori,
        ];
    }

    /**
     * Capaian layanan per kelompok sasaran.
     * Penyebut indikator per anak/ibu = anak tercatat yang masuk kelompok tersebut (mis. baduta = usia 0–23 bulan,
     * gizi buruk = status gizi pengukuran terakhir, diare = riwayat diare). Remaja putri memakai Data Sasaran.
     *
     * @param  Collection<int, Anak>  $anaks
     * @param  Collection<int, SasaranStunting>  $sasaran
     * @return array<string, array<string, array{jml: int, sasaran: int, persen: float}|null>>
     */
    private function hitungCapaianLayanan(Collection $anaks, Collection $sasaran): array
    {
        $usia = fn (int $min, int $max): Collection => $anaks->filter(
            fn (Anak $a) => $a->usia_terkini_bulan !== null && $a->usia_terkini_bulan >= $min && $a->usia_terkini_bulan <= $max
        );
        $statusGizi = fn (Collection $kelompok, string $status): Collection => $kelompok->filter(
            fn (Anak $a) => $a->pengukuranTerbaru?->status_gizi === $status
        );
        $diare = fn (Collection $kelompok): Collection => $kelompok->filter(fn (Anak $a) => (bool) $a->riwayatKesehatan?->riwayat_diare);

        $ibu = $anaks->filter(fn (Anak $a) => $a->riwayatKehamilan !== null);
        $ibuKek = $ibu->filter(fn (Anak $a) => $a->riwayatKehamilan->status_kek);
        $baduta = $usia(0, 23);
        $anak6Sampai23 = $usia(6, 23);
        $balita = $usia(0, 59);
        $keluarga = $anaks->filter(fn (Anak $a) => $a->sanitasi !== null)->unique('nomor_kk');
        $dataKesehatan = $anaks->filter(fn (Anak $a) => $a->riwayatKesehatan !== null);

        $ukur = function (Collection $kelompok, callable $syarat): array {
            $jml = $kelompok->filter($syarat)->count();

            return ['jml' => $jml, 'sasaran' => $kelompok->count(), 'persen' => $this->persen($jml, $kelompok->count())];
        };
        $ukurSasaran = function (string $kolomCapaian) use ($sasaran): ?array {
            $capaian = $sasaran->pluck($kolomCapaian)->filter(fn ($n) => $n !== null);

            if ($capaian->isEmpty()) {
                return null;
            }

            $jml = (int) $capaian->sum();
            $jumlahSasaran = (int) $sasaran->sum('remaja_putri');

            return ['jml' => $jml, 'sasaran' => $jumlahSasaran, 'persen' => $this->persen($jml, $jumlahSasaran)];
        };

        $dipantau = fn (Anak $a) => $a->pengukurans_count > 0;
        $tataLaksana = fn (Anak $a) => (bool) $a->riwayatKesehatan?->tata_laksana_gizi_buruk;
        $tambahanAsupan = fn (Anak $a) => (bool) $a->riwayatKesehatan?->tambahan_asupan_gizi;
        $oralitZinc = fn (Anak $a) => (bool) $a->riwayatKesehatan?->oralit_zinc;
        $obatCacing = fn (Anak $a) => (bool) $a->riwayatKesehatan?->obat_cacing;
        $kb = $ukur($ibu, fn (Anak $a) => $a->riwayatKehamilan->kb_pascapersalinan);

        return [
            'ibu_nifas' => [
                'kb' => $kb,
            ],
            'ibu_hamil' => [
                // Standar minimal 180 tablet; data lama tanpa jumlah tablet memakai isian "rutin".
                'ttd' => $ukur($ibu, fn (Anak $a) => $a->riwayatKehamilan->jumlah_tablet_tambah_darah !== null
                    ? $a->riwayatKehamilan->jumlah_tablet_tambah_darah >= 180
                    : $a->riwayatKehamilan->konsumsi_tablet_tambah_darah === 'rutin'),
                'anc' => $ukur($ibu, fn (Anak $a) => (int) $a->riwayatKehamilan->jumlah_pemeriksaan_kehamilan >= 6),
                'pmt_kek' => $ukur($ibuKek, fn (Anak $a) => $a->riwayatKehamilan->pmt_kek),
                'makanan_bergizi' => $ukur($ibu, fn (Anak $a) => $a->riwayatKehamilan->makanan_bergizi_seimbang),
                'kb' => $kb,
            ],
            'balita' => [
                'gizi_buruk' => $ukur($statusGizi($balita, 'gizi_buruk'), $tataLaksana),
                'dipantau' => $ukur($balita, $dipantau),
                'gizi_kurang' => $ukur($statusGizi($balita, 'gizi_kurang'), $tambahanAsupan),
                'oralit_zinc' => $ukur($diare($usia(24, 59)), $oralitZinc),
                'pneumonia' => $ukur($balita, fn (Anak $a) => (bool) $a->riwayatKesehatan?->pneumonia),
                'popm' => $ukur($usia(12, 59), $obatCacing),
            ],
            'baduta' => [
                'imd' => $ukur($baduta, fn (Anak $a) => (bool) ($a->asiMpasi?->imd || $a->riwayatKelahiran?->imd)),
                'asi_eksklusif' => $ukur($usia(0, 5), fn (Anak $a) => (bool) $a->asiMpasi?->asi_eksklusif),
                'mpasi' => $ukur($anak6Sampai23, fn (Anak $a) => $a->asiMpasi?->usia_mulai_mpasi_bulan !== null),
                'asi_6_23' => $ukur($anak6Sampai23, fn (Anak $a) => (bool) $a->asiMpasi?->masih_asi),
                'gizi_buruk' => $ukur($statusGizi($baduta, 'gizi_buruk'), $tataLaksana),
                'dipantau' => $ukur($baduta, $dipantau),
                'gizi_kurang' => $ukur($statusGizi($baduta, 'gizi_kurang'), $tambahanAsupan),
                'imunisasi' => $ukur($baduta, fn (Anak $a) => $a->riwayatKesehatan?->status_imunisasi === 'lengkap'),
                'oralit_zinc' => $ukur($diare($anak6Sampai23), $oralitZinc),
                'popm' => $ukur($anak6Sampai23, $obatCacing),
            ],
            'remaja_putri' => [
                'ttd' => $ukurSasaran('remaja_putri_ttd'),
                'anemia' => $ukurSasaran('remaja_putri_skrining_anemia'),
            ],
            'keluarga' => [
                'air_minum' => $ukur($keluarga, fn (Anak $a) => in_array($a->sanitasi->sumber_air_minum, self::SUMBER_AIR_AMAN, true)),
                'sanitasi' => $ukur($keluarga, fn (Anak $a) => $a->sanitasi->jenis_jamban === 'leher_angsa' && $a->sanitasi->septic_tank),
                'jkn' => $ukur($dataKesehatan, fn (Anak $a) => $a->riwayatKesehatan->memiliki_jkn),
                'kependudukan' => $ukur($anaks, fn (Anak $a) => (bool) $a->memiliki_akta_kelahiran),
            ],
        ];
    }

    /**
     * Gambar grafik (Chart.js) dari browser hanya diterima bila benar-benar PNG berukuran wajar;
     * selain itu PDF memakai grafik SVG cadangan.
     */
    private function gambarGrafikValid(mixed $dataUri): ?string
    {
        $awalan = 'data:image/png;base64,';

        if (! is_string($dataUri) || ! str_starts_with($dataUri, $awalan) || strlen($dataUri) > 3_000_000) {
            return null;
        }

        $biner = base64_decode(substr($dataUri, strlen($awalan)), true);
        $info = $biner === false ? false : @getimagesizefromstring($biner);

        if ($info === false || $info[2] !== IMAGETYPE_PNG || $info[0] > 4000 || $info[1] > 4000) {
            return null;
        }

        return $awalan.base64_encode($biner);
    }

    private function persen(int $jumlah, int $penyebut): float
    {
        return $penyebut > 0 ? round($jumlah / $penyebut * 100, 2) : 0.0;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function terapkanFilter(Builder $query, array $filters): Builder
    {
        if ($kecamatanId = $filters['kecamatan_id'] ?? null) {
            $query->where($query->getModel()->getTable().'.kecamatan_id', $kecamatanId);
        }

        if ($desaKelurahanId = $filters['desa_kelurahan_id'] ?? null) {
            $query->where($query->getModel()->getTable().'.desa_kelurahan_id', $desaKelurahanId);
        }

        if ($status = $filters['status_data'] ?? null) {
            $query->where($query->getModel()->getTable().'.status_data', $status);
        }

        if ($dari = $filters['dari'] ?? null) {
            $query->whereDate($query->getModel()->getTable().'.tanggal_input', '>=', $dari);
        }

        if ($sampai = $filters['sampai'] ?? null) {
            $query->whereDate($query->getModel()->getTable().'.tanggal_input', '<=', $sampai);
        }

        return $query;
    }
}
