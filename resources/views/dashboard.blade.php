@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-speedometer2',
        'eyebrow' => 'Ringkasan',
        'title' => 'Dashboard',
        'description' => 'Pantau progres pendataan kemiskinan ekstrem dan stunting Kota Ternate secara real-time.',
    ])

    {{--
    Catatan implementasi (PRD §7):
    - Dashboard hanya menampilkan HASIL PENDATAAN, bukan metrik transaksi/penjualan.
    - Ada 2 dashboard terpisah: Kemiskinan Ekstrem (§7.1) & Stunting (§8.2), masing-masing
      dengan "Statistik utama" (8 angka) + "Grafik" per wilayah/kategori.
    - Ditampilkan sebagai 2 tab agar tidak terlalu panjang scroll-nya, tapi tetap 1 halaman.
    - Variabel di-null-coalesce dengan data dummy supaya tetap bisa di-preview sebelum
      controller mengirim data asli. Ganti $kemiskinanStats, $stuntingStats, dst dari controller.
  --}}

    <style>
        /* ===== Tab switch dashboard (Kemiskinan / Stunting) ===== */
        .dashboard-tabs {
            display: inline-flex;
            gap: 4px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .dashboard-tabs .nav-link {
            border: 0;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dashboard-tabs .nav-link.active {
            background: #fff;
            color: #1e293b;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        /* ===== Baris statistik sekunder (stat strip) ===== */
        .stat-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .stat-strip-item {
            flex: 1 1 160px;
            padding: 14px 18px;
            border-right: 1px solid #e2e8f0;
        }

        .stat-strip-item:last-child {
            border-right: none;
        }

        .stat-strip-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-strip-label {
            font-size: 0.78rem;
            color: #64748b;
        }

        /* ===== Pipeline status verifikasi ===== */
        .status-pipeline-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .status-pipeline-item:last-child {
            border-bottom: none;
        }

        .status-pipeline-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            color: #334155;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
    </style>

    <ul class="nav dashboard-tabs" id="dashboardModeTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-kemiskinan-btn" data-bs-toggle="tab" data-bs-target="#tab-kemiskinan"
                type="button" role="tab" aria-controls="tab-kemiskinan" aria-selected="true">
                <i class="bi bi-house-heart" aria-hidden="true"></i> Kemiskinan Ekstrem
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-stunting-btn" data-bs-toggle="tab" data-bs-target="#tab-stunting"
                type="button" role="tab" aria-controls="tab-stunting" aria-selected="false">
                <i class="bi bi-heart-pulse" aria-hidden="true"></i> Stunting
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ============================= DASHBOARD KEMISKINAN EKSTREM (PRD §7.1) ============================= --}}
        <div class="tab-pane fade show active" id="tab-kemiskinan" role="tabpanel" aria-labelledby="tab-kemiskinan-btn">

            @php
                $kemiskinanStats = $kemiskinanStats ?? [
                    'total_keluarga' => 1284,
                    'total_anggota' => 4926,
                    'belum_diverifikasi' => 212,
                    'sedang_diverifikasi' => 96,
                    'valid' => 918,
                    'perlu_perbaikan' => 41,
                    'tidak_valid' => 12,
                    'duplikat' => 5,
                ];
            @endphp

            <section class="row g-3" aria-label="Statistik pendataan kemiskinan ekstrem">
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-primary">
                        <div class="metric-top">
                            <span class="metric-label">Total Keluarga Terdata</span>
                            <span class="metric-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">{{ number_format($kemiskinanStats['total_keluarga']) }}</div>
                        <div class="metric-meta">
                            <span>{{ number_format($kemiskinanStats['total_anggota']) }}</span>
                            <span>total anggota keluarga</span>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-success">
                        <div class="metric-top">
                            <span class="metric-label">Data Valid</span>
                            <span class="metric-icon"><i class="bi bi-patch-check-fill" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">{{ number_format($kemiskinanStats['valid']) }}</div>
                        <div class="metric-meta">
                            <span
                                class="text-success">{{ $kemiskinanStats['total_keluarga'] ? round(($kemiskinanStats['valid'] / $kemiskinanStats['total_keluarga']) * 100) : 0 }}%</span>
                            <span>dari total keluarga</span>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-warning">
                        <div class="metric-top">
                            <span class="metric-label">Dalam Proses Verifikasi</span>
                            <span class="metric-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">
                            {{ number_format($kemiskinanStats['belum_diverifikasi'] + $kemiskinanStats['sedang_diverifikasi']) }}
                        </div>
                        <div class="metric-meta">
                            <span>{{ number_format($kemiskinanStats['belum_diverifikasi']) }} belum diverifikasi</span>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-danger">
                        <div class="metric-top">
                            <span class="metric-label">Perlu Perbaikan</span>
                            <span class="metric-icon"><i class="bi bi-exclamation-triangle-fill"
                                    aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">{{ number_format($kemiskinanStats['perlu_perbaikan']) }}</div>
                        <div class="metric-meta">
                            <span class="text-danger">{{ number_format($kemiskinanStats['duplikat']) }} duplikat</span>
                            <span>perlu ditinjau</span>
                        </div>
                    </article>
                </div>
            </section>

            <section class="mt-3">
                <div class="stat-strip">
                    <div class="stat-strip-item">
                        <div class="stat-strip-value">{{ number_format($kemiskinanStats['total_anggota']) }}</div>
                        <div class="stat-strip-label">Total Anggota Keluarga</div>
                    </div>
                    <div class="stat-strip-item">
                        <div class="stat-strip-value">{{ number_format($kemiskinanStats['sedang_diverifikasi']) }}</div>
                        <div class="stat-strip-label">Sedang Diverifikasi</div>
                    </div>
                    <div class="stat-strip-item">
                        <div class="stat-strip-value">{{ number_format($kemiskinanStats['tidak_valid']) }}</div>
                        <div class="stat-strip-label">Data Tidak Valid</div>
                    </div>
                    <div class="stat-strip-item">
                        <div class="stat-strip-value">{{ number_format($kemiskinanStats['duplikat']) }}</div>
                        <div class="stat-strip-label">Data Duplikat</div>
                    </div>
                </div>
            </section>

            <section class="row g-3 mt-1">
                <div class="col-12 col-xl-8">
                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-bar-chart-line"
                                        aria-hidden="true"></i><span>Keluarga Berdasarkan Kecamatan</span></h2>
                                <p class="text-muted mb-0">Sebaran jumlah keluarga terdata per kecamatan.</p>
                            </div>
                            <a class="btn btn-light btn-sm" href="{{ url('/laporan') }}">Lihat Laporan</a>
                        </div>

                        <div class="chart-bars" aria-label="Grafik keluarga berdasarkan kecamatan">
                            @forelse (($keluargaPerKecamatan ?? []) as $point)
                                <div class="chart-column bar-{{ $point['percent'] }}">
                                    <span></span><small>{{ $point['label'] }}</small></div>
                            @empty
                                <div class="chart-column bar-58"><span></span><small>Ternate Tengah</small></div>
                                <div class="chart-column bar-72"><span></span><small>Ternate Selatan</small></div>
                                <div class="chart-column bar-45"><span></span><small>Ternate Utara</small></div>
                                <div class="chart-column bar-38"><span></span><small>Ternate Barat</small></div>
                                <div class="chart-column bar-27"><span></span><small>Pulau Ternate</small></div>
                                <div class="chart-column bar-19"><span></span><small>Moti</small></div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-list-check"
                                        aria-hidden="true"></i><span>Status Verifikasi</span></h2>
                                <p class="text-muted mb-0">Alur status data keluarga.</p>
                            </div>
                        </div>

                        @php
                            $statusPipelineKemiskinan = $statusPipelineKemiskinan ?? [
                                ['label' => 'Draft', 'count' => 58, 'color' => '#94a3b8'],
                                ['label' => 'Dikirim', 'count' => 154, 'color' => '#38bdf8'],
                                ['label' => 'Dalam Verifikasi', 'count' => 96, 'color' => '#f59e0b'],
                                ['label' => 'Perlu Perbaikan', 'count' => 41, 'color' => '#f97316'],
                                ['label' => 'Valid', 'count' => 918, 'color' => '#22c55e'],
                                ['label' => 'Tidak Valid', 'count' => 12, 'color' => '#ef4444'],
                                ['label' => 'Duplikat', 'count' => 5, 'color' => '#a855f7'],
                            ];
                        @endphp

                        <div>
                            @foreach ($statusPipelineKemiskinan as $status)
                                <div class="status-pipeline-item">
                                    <span class="status-pipeline-label">
                                        <span class="status-dot" style="background: {{ $status['color'] }}"></span>
                                        {{ $status['label'] }}
                                    </span>
                                    <strong>{{ number_format($status['count']) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel mt-3">
                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-house-heart"
                                aria-hidden="true"></i><span>Pendataan Kemiskinan Terbaru</span></h2>
                        <p class="text-muted mb-0">Data keluarga yang baru masuk atau diperbarui.</p>
                    </div>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('kemiskinan.index') }}">Lihat Semua Data</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Kepala Keluarga</th>
                                <th scope="col">Wilayah</th>
                                <th scope="col">Jml. Anggota</th>
                                <th scope="col">Petugas</th>
                                <th scope="col">Status</th>
                                <th scope="col">Tanggal Input</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusBadgeMap = [
                                    'draft' => 'secondary',
                                    'dikirim' => 'info',
                                    'dalam verifikasi' => 'warning',
                                    'perlu perbaikan' => 'warning',
                                    'valid' => 'success',
                                    'tidak valid' => 'danger',
                                    'duplikat' => 'purple',
                                ];
                                $keluargaTerbaru = $keluargaTerbaru ?? [
                                    [
                                        'nama' => 'Abdul Rasyid',
                                        'nik' => '8271xxxxxxxxxxx1',
                                        'kecamatan' => 'Ternate Tengah',
                                        'kelurahan' => 'Kalumpang',
                                        'jumlah_anggota' => 4,
                                        'petugas' => 'Sari Wulandari',
                                        'status' => 'Valid',
                                        'tanggal' => '2026-09-10',
                                        'id' => 1,
                                    ],
                                    [
                                        'nama' => 'Yusuf Bahar',
                                        'nik' => '8271xxxxxxxxxxx2',
                                        'kecamatan' => 'Ternate Selatan',
                                        'kelurahan' => 'Bastiong',
                                        'jumlah_anggota' => 6,
                                        'petugas' => 'Andi Pratama',
                                        'status' => 'Dalam Verifikasi',
                                        'tanggal' => '2026-09-11',
                                        'id' => 2,
                                    ],
                                    [
                                        'nama' => 'Halima Yusuf',
                                        'nik' => '8271xxxxxxxxxxx3',
                                        'kecamatan' => 'Ternate Utara',
                                        'kelurahan' => 'Sango',
                                        'jumlah_anggota' => 3,
                                        'petugas' => 'Sari Wulandari',
                                        'status' => 'Perlu Perbaikan',
                                        'tanggal' => '2026-09-12',
                                        'id' => 3,
                                    ],
                                    [
                                        'nama' => 'Muhtar Ali',
                                        'nik' => '8271xxxxxxxxxxx4',
                                        'kecamatan' => 'Pulau Ternate',
                                        'kelurahan' => 'Takome',
                                        'jumlah_anggota' => 5,
                                        'petugas' => 'Reza Mahendra',
                                        'status' => 'Draft',
                                        'tanggal' => '2026-09-13',
                                        'id' => 4,
                                    ],
                                    [
                                        'nama' => 'Nurjannah Saleh',
                                        'nik' => '8271xxxxxxxxxxx5',
                                        'kecamatan' => 'Ternate Barat',
                                        'kelurahan' => 'Loto',
                                        'jumlah_anggota' => 2,
                                        'petugas' => 'Andi Pratama',
                                        'status' => 'Valid',
                                        'tanggal' => '2026-09-13',
                                        'id' => 5,
                                    ],
                                ];
                            @endphp

                            @forelse ($keluargaTerbaru as $item)
                                <tr>
                                    <td>
                                        <p class="fw-semibold mb-0">{{ $item['nama'] }}</p>
                                        <p class="text-muted small mb-0">NIK: {{ $item['nik'] }}</p>
                                    </td>
                                    <td>
                                        <p class="mb-0">{{ $item['kecamatan'] }}</p>
                                        <p class="text-muted small mb-0">{{ $item['kelurahan'] }}</p>
                                    </td>
                                    <td>{{ $item['jumlah_anggota'] }} orang</td>
                                    <td>{{ $item['petugas'] }}</td>
                                    <td><span
                                            class="badge text-bg-{{ $statusBadgeMap[strtolower($item['status'])] ?? 'secondary' }}">{{ $item['status'] }}</span>
                                    </td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($item['tanggal'])->format('d M Y') }}</td>
                                    <td class="text-end"><a class="btn btn-light btn-sm"
                                            href="{{ route('kemiskinan.show', $item['id']) }}">Detail</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data pendataan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- ============================= DASHBOARD STUNTING (PRD §8.2) ============================= --}}
        <div class="tab-pane fade" id="tab-stunting" role="tabpanel" aria-labelledby="tab-stunting-btn">

            @php
                $stuntingStats = $stuntingStats ?? [
                    'total_anak' => 742,
                    'sudah_diukur' => 611,
                    'belum_diukur' => 131,
                    'belum_diverifikasi' => 88,
                    'sedang_diverifikasi' => 34,
                    'valid' => 512,
                    'perlu_perbaikan' => 19,
                    'tidak_valid' => 7,
                    'duplikat' => 3,
                ];
            @endphp

            <section class="row g-3" aria-label="Statistik pendataan stunting">
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-primary">
                        <div class="metric-top">
                            <span class="metric-label">Total Anak Terdata</span>
                            <span class="metric-icon"><i class="bi bi-person-hearts" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">{{ number_format($stuntingStats['total_anak']) }}</div>
                        <div class="metric-meta">
                            <span>{{ number_format($stuntingStats['belum_diukur']) }}</span>
                            <span>belum ada data pengukuran</span>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-success">
                        <div class="metric-top">
                            <span class="metric-label">Data Valid</span>
                            <span class="metric-icon"><i class="bi bi-patch-check-fill" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">{{ number_format($stuntingStats['valid']) }}</div>
                        <div class="metric-meta">
                            <span
                                class="text-success">{{ $stuntingStats['total_anak'] ? round(($stuntingStats['valid'] / $stuntingStats['total_anak']) * 100) : 0 }}%</span>
                            <span>dari total anak</span>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-warning">
                        <div class="metric-top">
                            <span class="metric-label">Dalam Proses Verifikasi</span>
                            <span class="metric-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">
                            {{ number_format($stuntingStats['belum_diverifikasi'] + $stuntingStats['sedang_diverifikasi']) }}
                        </div>
                        <div class="metric-meta">
                            <span>{{ number_format($stuntingStats['belum_diverifikasi']) }} belum diverifikasi</span>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-danger">
                        <div class="metric-top">
                            <span class="metric-label">Perlu Perbaikan</span>
                            <span class="metric-icon"><i class="bi bi-exclamation-triangle-fill"
                                    aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">{{ number_format($stuntingStats['perlu_perbaikan']) }}</div>
                        <div class="metric-meta">
                            <span class="text-danger">{{ number_format($stuntingStats['duplikat']) }} duplikat</span>
                            <span>perlu ditinjau</span>
                        </div>
                    </article>
                </div>
            </section>

            <section class="mt-3">
                <div class="stat-strip">
                    <div class="stat-strip-item">
                        <div class="stat-strip-value">{{ number_format($stuntingStats['sudah_diukur']) }}</div>
                        <div class="stat-strip-label">Sudah Diukur</div>
                    </div>
                    <div class="stat-strip-item">
                        <div class="stat-strip-value">{{ number_format($stuntingStats['sedang_diverifikasi']) }}</div>
                        <div class="stat-strip-label">Sedang Diverifikasi</div>
                    </div>
                    <div class="stat-strip-item">
                        <div class="stat-strip-value">{{ number_format($stuntingStats['tidak_valid']) }}</div>
                        <div class="stat-strip-label">Data Tidak Valid</div>
                    </div>
                    <div class="stat-strip-item">
                        <div class="stat-strip-value">{{ number_format($stuntingStats['duplikat']) }}</div>
                        <div class="stat-strip-label">Data Duplikat</div>
                    </div>
                </div>
            </section>

            <section class="row g-3 mt-1">
                <div class="col-12 col-xl-8">
                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-bar-chart-line"
                                        aria-hidden="true"></i><span>Anak Berdasarkan Kecamatan</span></h2>
                                <p class="text-muted mb-0">Sebaran jumlah anak terdata per kecamatan.</p>
                            </div>
                            <a class="btn btn-light btn-sm" href="{{ url('/laporan') }}">Lihat Laporan</a>
                        </div>

                        <div class="chart-bars" aria-label="Grafik anak berdasarkan kecamatan">
                            @forelse (($anakPerKecamatan ?? []) as $point)
                                <div class="chart-column bar-{{ $point['percent'] }}">
                                    <span></span><small>{{ $point['label'] }}</small></div>
                            @empty
                                <div class="chart-column bar-49"><span></span><small>Ternate Tengah</small></div>
                                <div class="chart-column bar-63"><span></span><small>Ternate Selatan</small></div>
                                <div class="chart-column bar-38"><span></span><small>Ternate Utara</small></div>
                                <div class="chart-column bar-31"><span></span><small>Ternate Barat</small></div>
                                <div class="chart-column bar-22"><span></span><small>Pulau Ternate</small></div>
                                <div class="chart-column bar-15"><span></span><small>Moti</small></div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-list-check"
                                        aria-hidden="true"></i><span>Status Verifikasi</span></h2>
                                <p class="text-muted mb-0">Alur status data anak.</p>
                            </div>
                        </div>

                        @php
                            $statusPipelineStunting = $statusPipelineStunting ?? [
                                ['label' => 'Draft', 'count' => 44, 'color' => '#94a3b8'],
                                ['label' => 'Dikirim', 'count' => 109, 'color' => '#38bdf8'],
                                ['label' => 'Dalam Verifikasi', 'count' => 34, 'color' => '#f59e0b'],
                                ['label' => 'Perlu Perbaikan', 'count' => 19, 'color' => '#f97316'],
                                ['label' => 'Valid', 'count' => 512, 'color' => '#22c55e'],
                                ['label' => 'Tidak Valid', 'count' => 7, 'color' => '#ef4444'],
                                ['label' => 'Duplikat', 'count' => 3, 'color' => '#a855f7'],
                            ];
                        @endphp

                        <div>
                            @foreach ($statusPipelineStunting as $status)
                                <div class="status-pipeline-item">
                                    <span class="status-pipeline-label">
                                        <span class="status-dot" style="background: {{ $status['color'] }}"></span>
                                        {{ $status['label'] }}
                                    </span>
                                    <strong>{{ number_format($status['count']) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel mt-3">
                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-heart-pulse"
                                aria-hidden="true"></i><span>Pendataan Stunting Terbaru</span></h2>
                        <p class="text-muted mb-0">Data anak yang baru masuk atau diperbarui.</p>
                    </div>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('stunting.index') }}">Lihat Semua Data</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Nama Anak</th>
                                <th scope="col">Usia</th>
                                <th scope="col">Wilayah</th>
                                <th scope="col">Status Pengukuran</th>
                                <th scope="col">Status Verifikasi</th>
                                <th scope="col">Tanggal Input</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $anakTerbaru = $anakTerbaru ?? [
                                    [
                                        'nama' => 'Fatimah Az-Zahra',
                                        'nik' => '8271xxxxxxxxxxx1',
                                        'usia' => '18 bln',
                                        'kecamatan' => 'Ternate Tengah',
                                        'kelurahan' => 'Kalumpang',
                                        'sudah_diukur' => true,
                                        'status' => 'Valid',
                                        'tanggal' => '2026-09-10',
                                        'id' => 1,
                                    ],
                                    [
                                        'nama' => 'Muhammad Rizky',
                                        'nik' => '8271xxxxxxxxxxx2',
                                        'usia' => '9 bln',
                                        'kecamatan' => 'Ternate Selatan',
                                        'kelurahan' => 'Bastiong',
                                        'sudah_diukur' => true,
                                        'status' => 'Dalam Verifikasi',
                                        'tanggal' => '2026-09-11',
                                        'id' => 2,
                                    ],
                                    [
                                        'nama' => 'Aisyah Putri',
                                        'nik' => '8271xxxxxxxxxxx3',
                                        'usia' => '24 bln',
                                        'kecamatan' => 'Ternate Utara',
                                        'kelurahan' => 'Sango',
                                        'sudah_diukur' => false,
                                        'status' => 'Perlu Perbaikan',
                                        'tanggal' => '2026-09-12',
                                        'id' => 3,
                                    ],
                                    [
                                        'nama' => 'Zainal Abidin',
                                        'nik' => '8271xxxxxxxxxxx4',
                                        'usia' => '12 bln',
                                        'kecamatan' => 'Pulau Ternate',
                                        'kelurahan' => 'Takome',
                                        'sudah_diukur' => false,
                                        'status' => 'Draft',
                                        'tanggal' => '2026-09-13',
                                        'id' => 4,
                                    ],
                                    [
                                        'nama' => 'Siti Nurhaliza',
                                        'nik' => '8271xxxxxxxxxxx5',
                                        'usia' => '30 bln',
                                        'kecamatan' => 'Ternate Barat',
                                        'kelurahan' => 'Loto',
                                        'sudah_diukur' => true,
                                        'status' => 'Valid',
                                        'tanggal' => '2026-09-13',
                                        'id' => 5,
                                    ],
                                ];
                            @endphp

                            @forelse ($anakTerbaru as $item)
                                <tr>
                                    <td>
                                        <p class="fw-semibold mb-0">{{ $item['nama'] }}</p>
                                        <p class="text-muted small mb-0">NIK: {{ $item['nik'] }}</p>
                                    </td>
                                    <td>{{ $item['usia'] }}</td>
                                    <td>
                                        <p class="mb-0">{{ $item['kecamatan'] }}</p>
                                        <p class="text-muted small mb-0">{{ $item['kelurahan'] }}</p>
                                    </td>
                                    <td>
                                        @if ($item['sudah_diukur'])
                                            <span class="badge text-bg-success">Sudah Diukur</span>
                                        @else
                                            <span class="badge text-bg-secondary">Belum Diukur</span>
                                        @endif
                                    </td>
                                    <td><span
                                            class="badge text-bg-{{ $statusBadgeMap[strtolower($item['status'])] ?? 'secondary' }}">{{ $item['status'] }}</span>
                                    </td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($item['tanggal'])->format('d M Y') }}</td>
                                    <td class="text-end"><a class="btn btn-light btn-sm"
                                            href="{{ route('stunting.show', $item['id']) }}">Detail</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data pendataan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

    </div>

@endsection
