<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Keluarga;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalKeluarga = Keluarga::count();

        $kemiskinanStats = [
            'total_keluarga' => $totalKeluarga,
            'total_anggota' => (int) Keluarga::sum('jumlah_anggota_keluarga'),
            'belum_diverifikasi' => Keluarga::where('status_data', 'dikirim')->count(),
            'sedang_diverifikasi' => Keluarga::where('status_data', 'dalam_verifikasi')->count(),
            'valid' => Keluarga::where('status_data', 'valid')->count(),
            'perlu_perbaikan' => Keluarga::where('status_data', 'perlu_perbaikan')->count(),
            'tidak_valid' => Keluarga::where('status_data', 'tidak_valid')->count(),
            'duplikat' => Keluarga::where('status_data', 'duplikat')->count(),
        ];

        $maxPerKecamatan = Keluarga::selectRaw('kecamatan_id, count(*) as jumlah')
            ->groupBy('kecamatan_id')->max('jumlah') ?: 1;

        $keluargaPerKecamatan = Keluarga::query()
            ->join('kecamatans', 'kecamatans.id', '=', 'keluargas.kecamatan_id')
            ->selectRaw('kecamatans.nama as label, count(*) as jumlah')
            ->groupBy('kecamatans.id', 'kecamatans.nama')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'percent' => (int) round(($row->jumlah / $maxPerKecamatan) * 100),
            ])
            ->all();

        $statusColor = [
            'draft' => '#94a3b8', 'dikirim' => '#38bdf8', 'dalam_verifikasi' => '#f59e0b',
            'perlu_perbaikan' => '#f97316', 'valid' => '#22c55e', 'tidak_valid' => '#ef4444', 'duplikat' => '#a855f7',
        ];
        $statusLabel = [
            'draft' => 'Draft', 'dikirim' => 'Dikirim', 'dalam_verifikasi' => 'Dalam Verifikasi',
            'perlu_perbaikan' => 'Perlu Perbaikan', 'valid' => 'Valid', 'tidak_valid' => 'Tidak Valid', 'duplikat' => 'Duplikat',
        ];
        $statusPipelineKemiskinan = collect($statusLabel)->map(fn ($label, $status) => [
            'label' => $label,
            'count' => Keluarga::where('status_data', $status)->count(),
            'color' => $statusColor[$status],
        ])->values()->all();

        $keluargaTerbaru = Keluarga::with(['kecamatan', 'desaKelurahan', 'petugas'])
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
                'status' => $statusLabel[$k->status_data] ?? $k->status_data,
                'tanggal' => optional($k->tanggal_input)->format('Y-m-d'),
            ])
            ->all();

        $totalAnak = Anak::count();

        $stuntingStats = [
            'total_anak' => $totalAnak,
            'sudah_diukur' => Anak::has('pengukurans')->count(),
            'belum_diukur' => Anak::doesntHave('pengukurans')->count(),
            'belum_diverifikasi' => Anak::where('status_data', 'dikirim')->count(),
            'sedang_diverifikasi' => Anak::where('status_data', 'dalam_verifikasi')->count(),
            'valid' => Anak::where('status_data', 'valid')->count(),
            'perlu_perbaikan' => Anak::where('status_data', 'perlu_perbaikan')->count(),
            'tidak_valid' => Anak::where('status_data', 'tidak_valid')->count(),
            'duplikat' => Anak::where('status_data', 'duplikat')->count(),
        ];

        $maxAnakPerKecamatan = Anak::selectRaw('kecamatan_id, count(*) as jumlah')
            ->groupBy('kecamatan_id')->max('jumlah') ?: 1;

        $anakPerKecamatan = Anak::query()
            ->join('kecamatans', 'kecamatans.id', '=', 'anaks.kecamatan_id')
            ->selectRaw('kecamatans.nama as label, count(*) as jumlah')
            ->groupBy('kecamatans.id', 'kecamatans.nama')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'percent' => (int) round(($row->jumlah / $maxAnakPerKecamatan) * 100),
            ])
            ->all();

        $statusPipelineStunting = collect($statusLabel)->map(fn ($label, $status) => [
            'label' => $label,
            'count' => Anak::where('status_data', $status)->count(),
            'color' => $statusColor[$status],
        ])->values()->all();

        $anakTerbaru = Anak::with(['kecamatan', 'desaKelurahan'])
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
                'status' => $statusLabel[$a->status_data] ?? $a->status_data,
                'tanggal' => optional($a->tanggal_input)->format('Y-m-d'),
            ])
            ->all();

        return view('dashboard', [
            'kemiskinanStats' => $totalKeluarga > 0 ? $kemiskinanStats : null,
            'keluargaPerKecamatan' => $keluargaPerKecamatan,
            'statusPipelineKemiskinan' => $totalKeluarga > 0 ? $statusPipelineKemiskinan : null,
            'keluargaTerbaru' => $keluargaTerbaru,
            'stuntingStats' => $totalAnak > 0 ? $stuntingStats : null,
            'anakPerKecamatan' => $anakPerKecamatan,
            'statusPipelineStunting' => $totalAnak > 0 ? $statusPipelineStunting : null,
            'anakTerbaru' => $anakTerbaru,
        ]);
    }
}
