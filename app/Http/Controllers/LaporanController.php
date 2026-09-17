<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\Anak;
use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Pengukuran;
use App\Permission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const STATUS_LABEL = [
        'draft' => 'Draft',
        'dikirim' => 'Dikirim',
        'dalam_verifikasi' => 'Dalam Verifikasi',
        'perlu_perbaikan' => 'Perlu Perbaikan',
        'valid' => 'Valid',
        'tidak_valid' => 'Tidak Valid',
        'duplikat' => 'Duplikat',
    ];

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

        $rekap = $modul === 'kemiskinan'
            ? $this->rekapKemiskinan($filters)
            : $this->rekapStunting($filters);

        return view('laporan.index', [
            'modul' => $modul,
            'bisaExport' => $user->hasPermission(Permission::Export),
            'bisaLihatKemiskinan' => $user->hasPermission(Permission::KemiskinanView),
            'bisaLihatStunting' => $user->hasPermission(Permission::StuntingView),
            'kecamatans' => Kecamatan::where('is_active', true)->orderBy('nama')->get(),
            'desaKelurahans' => DesaKelurahan::where('is_active', true)->orderBy('nama')->get(),
            'filters' => $filters,
            'statusLabel' => self::STATUS_LABEL,
            ...$rekap,
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

        $format = $request->string('format')->trim()->lower()->value();
        $format = in_array($format, ['csv', 'xlsx', 'pdf'], true) ? $format : 'csv';

        $filters = $request->only(['kecamatan_id', 'desa_kelurahan_id', 'status_data', 'dari', 'sampai']);
        $namaFile = 'laporan-'.$modul.'-'.now()->format('Ymd-His');

        if ($format === 'pdf') {
            $rekap = $modul === 'kemiskinan' ? $this->rekapKemiskinan($filters) : $this->rekapStunting($filters);

            return Pdf::loadView('laporan.pdf', [
                'judul' => $modul === 'kemiskinan' ? 'Laporan Kemiskinan Ekstrem' : 'Laporan Stunting',
                'modul' => $modul,
                'statusLabel' => self::STATUS_LABEL,
                ...$rekap,
            ])->download($namaFile.'.pdf');
        }

        [$kolom, $baris] = $modul === 'kemiskinan'
            ? $this->dataDetailKemiskinan($filters)
            : $this->dataDetailStunting($filters);

        return $format === 'xlsx'
            ? Excel::download(new LaporanExport($kolom, $baris), $namaFile.'.xlsx')
            : $this->streamCsv($namaFile, $kolom, $baris);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function rekapKemiskinan(array $filters): array
    {
        $query = $this->terapkanFilter(Keluarga::query(), $filters);

        return [
            'totalData' => (clone $query)->count(),
            'rekapKecamatan' => (clone $query)
                ->join('kecamatans', 'kecamatans.id', '=', 'keluargas.kecamatan_id')
                ->selectRaw('kecamatans.nama as label, count(*) as jumlah_keluarga, sum(keluargas.jumlah_anggota_keluarga) as jumlah_anggota')
                ->groupBy('kecamatans.id', 'kecamatans.nama')
                ->orderByDesc('jumlah_keluarga')
                ->get(),
            'rekapDesa' => (clone $query)
                ->join('desa_kelurahans', 'desa_kelurahans.id', '=', 'keluargas.desa_kelurahan_id')
                ->selectRaw('desa_kelurahans.nama as label, count(*) as jumlah_keluarga')
                ->groupBy('desa_kelurahans.id', 'desa_kelurahans.nama')
                ->orderByDesc('jumlah_keluarga')
                ->get(),
            'rekapStatus' => $this->rekapStatus($query),
            'rekapProgram' => (clone $query)
                ->join('kepesertaan_programs', 'kepesertaan_programs.keluarga_id', '=', 'keluargas.id')
                ->where('kepesertaan_programs.status_penerima', 'penerima')
                ->selectRaw('kepesertaan_programs.nama_program as label, count(*) as jumlah')
                ->groupBy('kepesertaan_programs.nama_program')
                ->orderByDesc('jumlah')
                ->get(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function rekapStunting(array $filters): array
    {
        $query = $this->terapkanFilter(Anak::query(), $filters);

        $anakIds = (clone $query)->pluck('id');

        $latestPengukuranIds = Pengukuran::query()
            ->whereIn('anak_id', $anakIds)
            ->selectRaw('MAX(id) as id')
            ->groupBy('anak_id')
            ->pluck('id');

        return [
            'totalData' => (clone $query)->count(),
            'rekapKecamatan' => (clone $query)
                ->join('kecamatans', 'kecamatans.id', '=', 'anaks.kecamatan_id')
                ->selectRaw('kecamatans.nama as label, count(*) as jumlah_anak')
                ->groupBy('kecamatans.id', 'kecamatans.nama')
                ->orderByDesc('jumlah_anak')
                ->get(),
            'rekapDesa' => (clone $query)
                ->join('desa_kelurahans', 'desa_kelurahans.id', '=', 'anaks.desa_kelurahan_id')
                ->selectRaw('desa_kelurahans.nama as label, count(*) as jumlah_anak')
                ->groupBy('desa_kelurahans.id', 'desa_kelurahans.nama')
                ->orderByDesc('jumlah_anak')
                ->get(),
            'rekapStatus' => $this->rekapStatus($query),
            'rekapJenisKelamin' => (clone $query)
                ->selectRaw('jenis_kelamin as label, count(*) as jumlah')
                ->groupBy('jenis_kelamin')
                ->get(),
            'rekapKategoriStunting' => Pengukuran::query()
                ->whereIn('id', $latestPengukuranIds)
                ->whereNotNull('hasil_kategori')
                ->selectRaw('hasil_kategori as label, count(*) as jumlah')
                ->groupBy('hasil_kategori')
                ->orderByDesc('jumlah')
                ->get(),
        ];
    }

    private function rekapStatus(Builder $query): Collection
    {
        return collect(self::STATUS_LABEL)->map(fn ($label, $status) => [
            'label' => $label,
            'jumlah' => (clone $query)->where('status_data', $status)->count(),
        ])->values();
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

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: array<int, string>, 1: Collection<int, array<int, mixed>>}
     */
    private function dataDetailKemiskinan(array $filters): array
    {
        $keluargas = $this->terapkanFilter(
            Keluarga::query()->with(['kecamatan', 'desaKelurahan', 'petugas']),
            $filters
        )->orderBy('nama_kepala_keluarga')->get();

        $kolom = ['Kode Pendataan', 'Nama Kepala Keluarga', 'NIK', 'Nomor KK', 'Kecamatan', 'Desa/Kelurahan', 'Jumlah Anggota', 'Status', 'Petugas', 'Tanggal Input'];

        $baris = $keluargas->map(fn (Keluarga $k) => [
            $k->kode_pendataan,
            $k->nama_kepala_keluarga,
            $k->nik_kepala_keluarga,
            $k->nomor_kk,
            $k->kecamatan?->nama,
            $k->desaKelurahan?->nama,
            $k->jumlah_anggota_keluarga,
            self::STATUS_LABEL[$k->status_data] ?? $k->status_data,
            $k->petugas?->nama,
            optional($k->tanggal_input)->format('Y-m-d'),
        ]);

        return [$kolom, $baris];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: array<int, string>, 1: Collection<int, array<int, mixed>>}
     */
    private function dataDetailStunting(array $filters): array
    {
        $anaks = $this->terapkanFilter(
            Anak::query()->with(['kecamatan', 'desaKelurahan', 'petugas']),
            $filters
        )->orderBy('nama_anak')->get();

        $kolom = ['Kode Pendataan', 'Nama Anak', 'NIK', 'Nomor KK', 'Jenis Kelamin', 'Usia (bulan)', 'Kecamatan', 'Desa/Kelurahan', 'Status', 'Petugas', 'Tanggal Input'];

        $baris = $anaks->map(fn (Anak $a) => [
            $a->kode_pendataan,
            $a->nama_anak,
            $a->nik_anak,
            $a->nomor_kk,
            $a->jenis_kelamin,
            $a->usia_terkini_bulan,
            $a->kecamatan?->nama,
            $a->desaKelurahan?->nama,
            self::STATUS_LABEL[$a->status_data] ?? $a->status_data,
            $a->petugas?->nama,
            optional($a->tanggal_input)->format('Y-m-d'),
        ]);

        return [$kolom, $baris];
    }

    /**
     * @param  array<int, string>  $kolom
     * @param  Collection<int, array<int, mixed>>  $baris
     */
    private function streamCsv(string $namaFile, array $kolom, Collection $baris): StreamedResponse
    {
        $namaFile = $namaFile.'.csv';

        return response()->streamDownload(function () use ($kolom, $baris) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $kolom);

            foreach ($baris as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $namaFile, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
