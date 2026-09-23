<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Pengukuran;
use App\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
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
     * @var array<string, string>
     */
    private const STATUS_COLOR = [
        'dalam_verifikasi' => '#f59e0b',
        'perlu_perbaikan' => '#f97316',
        'valid' => '#22c55e',
        'tidak_valid' => '#ef4444',
    ];

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $bisaLihatKemiskinan = $user->hasPermission(Permission::KemiskinanView);
        $bisaLihatStunting = $user->hasPermission(Permission::StuntingView);

        $filters = $request->only(['kecamatan_id', 'desa_kelurahan_id', 'status_data', 'dari', 'sampai']);

        return view('dashboard', [
            'bisaLihatKemiskinan' => $bisaLihatKemiskinan,
            'bisaLihatStunting' => $bisaLihatStunting,
            'filters' => $filters,
            'statusOptions' => self::STATUS_LABEL,
            'kecamatans' => Kecamatan::where('is_active', true)->orderBy('nama')->get(),
            'desaKelurahans' => DesaKelurahan::where('is_active', true)->orderBy('nama')->get(),
            ...($bisaLihatKemiskinan ? $this->dataKemiskinan($filters) : []),
            ...($bisaLihatStunting ? $this->dataStunting($filters) : []),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function dataKemiskinan(array $filters): array
    {
        $base = fn () => $this->terapkanFilter(Keluarga::query(), $filters);

        $statusCounts = collect(self::STATUS_LABEL)->mapWithKeys(
            fn ($label, $status) => [$status => (clone $base())->where('status_data', $status)->count()]
        );

        $kemiskinanStats = [
            'total_keluarga' => $statusCounts->sum(),
            'total_anggota' => (int) (clone $base())->sum('jumlah_anggota_keluarga'),
            'sedang_diverifikasi' => $statusCounts['dalam_verifikasi'],
            'valid' => $statusCounts['valid'],
            'perlu_perbaikan' => $statusCounts['perlu_perbaikan'],
            'tidak_valid' => $statusCounts['tidak_valid'],
        ];

        $perKecamatan = (clone $base())
            ->join('kecamatans', 'kecamatans.id', '=', 'keluargas.kecamatan_id')
            ->selectRaw('kecamatans.nama as label, count(*) as jumlah')
            ->groupBy('kecamatans.id', 'kecamatans.nama')
            ->orderByDesc('jumlah')
            ->get();
        $maxJumlah = $perKecamatan->max('jumlah') ?: 1;

        $keluargaPerKecamatan = $perKecamatan->map(fn ($row) => [
            'label' => $row->label,
            'percent' => (int) round(($row->jumlah / $maxJumlah) * 100),
        ])->all();

        $statusPipelineKemiskinan = collect(self::STATUS_LABEL)->map(fn ($label, $status) => [
            'label' => $label,
            'count' => $statusCounts[$status],
            'color' => self::STATUS_COLOR[$status],
        ])->values()->all();

        $rekapProgram = (clone $base())
            ->join('kepesertaan_programs', 'kepesertaan_programs.keluarga_id', '=', 'keluargas.id')
            ->where('kepesertaan_programs.status_penerima', 'penerima')
            ->selectRaw('kepesertaan_programs.nama_program as label, count(*) as jumlah')
            ->groupBy('kepesertaan_programs.nama_program')
            ->orderByDesc('jumlah')
            ->limit(5)
            ->get();

        $keluargaTerbaru = (clone $base())
            ->with(['kecamatan', 'desaKelurahan', 'petugas'])
            ->latest('tanggal_input')
            ->take(5)
            ->get()
            ->map(fn (Keluarga $k) => [
                'id' => $k->id,
                'nama' => $k->nama_kepala_keluarga,
                'nik' => $k->nik_kepala_keluarga,
                'kecamatan' => $k->kecamatan?->nama,
                'kelurahan' => $k->desaKelurahan?->nama,
                'jumlah_anggota' => $k->jumlah_anggota_keluarga,
                'petugas' => $k->petugas?->nama,
                'status' => self::STATUS_LABEL[$k->status_data] ?? $k->status_data,
                'tanggal' => optional($k->tanggal_input)->format('Y-m-d'),
            ])
            ->all();

        return compact('kemiskinanStats', 'keluargaPerKecamatan', 'statusPipelineKemiskinan', 'rekapProgram', 'keluargaTerbaru');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function dataStunting(array $filters): array
    {
        $base = fn () => $this->terapkanFilter(Anak::query(), $filters);

        $statusCounts = collect(self::STATUS_LABEL)->mapWithKeys(
            fn ($label, $status) => [$status => (clone $base())->where('status_data', $status)->count()]
        );

        $stuntingStats = [
            'total_anak' => $statusCounts->sum(),
            'sudah_diukur' => (clone $base())->has('pengukurans')->count(),
            'belum_diukur' => (clone $base())->doesntHave('pengukurans')->count(),
            'sedang_diverifikasi' => $statusCounts['dalam_verifikasi'],
            'valid' => $statusCounts['valid'],
            'perlu_perbaikan' => $statusCounts['perlu_perbaikan'],
            'tidak_valid' => $statusCounts['tidak_valid'],
        ];

        $perKecamatan = (clone $base())
            ->join('kecamatans', 'kecamatans.id', '=', 'anaks.kecamatan_id')
            ->selectRaw('kecamatans.nama as label, count(*) as jumlah')
            ->groupBy('kecamatans.id', 'kecamatans.nama')
            ->orderByDesc('jumlah')
            ->get();
        $maxJumlah = $perKecamatan->max('jumlah') ?: 1;

        $anakPerKecamatan = $perKecamatan->map(fn ($row) => [
            'label' => $row->label,
            'percent' => (int) round(($row->jumlah / $maxJumlah) * 100),
        ])->all();

        $statusPipelineStunting = collect(self::STATUS_LABEL)->map(fn ($label, $status) => [
            'label' => $label,
            'count' => $statusCounts[$status],
            'color' => self::STATUS_COLOR[$status],
        ])->values()->all();

        $rekapJenisKelamin = (clone $base())
            ->selectRaw('jenis_kelamin as label, count(*) as jumlah')
            ->groupBy('jenis_kelamin')
            ->get();

        $anakIds = (clone $base())->pluck('id');
        $latestPengukuranIds = Pengukuran::query()
            ->whereIn('anak_id', $anakIds)
            ->selectRaw('MAX(id) as id')
            ->groupBy('anak_id')
            ->pluck('id');

        $rekapKategoriStunting = Pengukuran::query()
            ->whereIn('id', $latestPengukuranIds)
            ->whereNotNull('hasil_kategori')
            ->selectRaw('hasil_kategori as label, count(*) as jumlah')
            ->groupBy('hasil_kategori')
            ->orderByDesc('jumlah')
            ->get();

        $anakTerbaru = (clone $base())
            ->with(['kecamatan', 'desaKelurahan'])
            ->withCount('pengukurans')
            ->latest('tanggal_input')
            ->take(5)
            ->get()
            ->map(fn (Anak $a) => [
                'id' => $a->id,
                'nama' => $a->nama_anak,
                'nik' => $a->nik_anak,
                'usia' => $a->usia_terkini_bulan.' bln',
                'kecamatan' => $a->kecamatan?->nama,
                'kelurahan' => $a->desaKelurahan?->nama,
                'sudah_diukur' => $a->pengukurans_count > 0,
                'status' => self::STATUS_LABEL[$a->status_data] ?? $a->status_data,
                'tanggal' => optional($a->tanggal_input)->format('Y-m-d'),
            ])
            ->all();

        return compact('stuntingStats', 'anakPerKecamatan', 'statusPipelineStunting', 'rekapJenisKelamin', 'rekapKategoriStunting', 'anakTerbaru');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function terapkanFilter(Builder $query, array $filters): Builder
    {
        $tabel = $query->getModel()->getTable();

        if ($kecamatanId = $filters['kecamatan_id'] ?? null) {
            $query->where("{$tabel}.kecamatan_id", $kecamatanId);
        }

        if ($desaKelurahanId = $filters['desa_kelurahan_id'] ?? null) {
            $query->where("{$tabel}.desa_kelurahan_id", $desaKelurahanId);
        }

        if ($status = $filters['status_data'] ?? null) {
            $query->where("{$tabel}.status_data", $status);
        }

        if ($dari = $filters['dari'] ?? null) {
            $query->whereDate("{$tabel}.tanggal_input", '>=', $dari);
        }

        if ($sampai = $filters['sampai'] ?? null) {
            $query->whereDate("{$tabel}.tanggal_input", '<=', $sampai);
        }

        return $query;
    }
}
