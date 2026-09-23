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
    Catatan implementasi (PRD Bagian 11-13):
    - Dashboard hanya menampilkan HASIL PENDATAAN, bukan metrik transaksi/penjualan.
    - Ada 2 dashboard terpisah: Kemiskinan Ekstrem & Stunting, masing-masing dengan
      "Statistik utama" + "Grafik" per wilayah/kategori. Ditampilkan sebagai 2 tab.
    - Tab disembunyikan sesuai hak akses (kemiskinan.view / stunting.view).
    - Seluruh data berasal dari controller (tidak ada data dummy) — kalau kosong,
      tampilkan status "belum ada data", bukan angka fiktif.
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

    <section class="panel mb-3">
        <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end p-3">
            <div class="col-6 col-md-3">
                <label class="form-label small" for="kecamatan_id">Kecamatan</label>
                <select class="form-select form-select-sm" id="kecamatan_id" name="kecamatan_id">
                    <option value="">Semua Kecamatan</option>
                    @foreach ($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->id }}" @selected(($filters['kecamatan_id'] ?? null) == $kecamatan->id)>{{ $kecamatan->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small" for="desa_kelurahan_id">Desa/Kelurahan</label>
                <select class="form-select form-select-sm" id="desa_kelurahan_id" name="desa_kelurahan_id">
                    <option value="">Semua Desa/Kelurahan</option>
                    @foreach ($desaKelurahans as $desaKelurahan)
                        <option value="{{ $desaKelurahan->id }}" @selected(($filters['desa_kelurahan_id'] ?? null) == $desaKelurahan->id)>{{ $desaKelurahan->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="status_data">Status Data</label>
                <select class="form-select form-select-sm" id="status_data" name="status_data">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status_data'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="dari">Dari Tanggal</label>
                <input type="date" class="form-control form-control-sm" id="dari" name="dari" value="{{ $filters['dari'] ?? '' }}">
            </div>
            <div class="col-6 col-md-1">
                <label class="form-label small" for="sampai">Sampai</label>
                <input type="date" class="form-control form-control-sm" id="sampai" name="sampai" value="{{ $filters['sampai'] ?? '' }}">
            </div>
            <div class="col-6 col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i></button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </section>

    @if (! $bisaLihatKemiskinan && ! $bisaLihatStunting)
        <div class="alert alert-info">Anda belum memiliki hak akses untuk melihat data pendataan.</div>
    @endif

    @if ($bisaLihatKemiskinan && $bisaLihatStunting)
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
    @endif

    <div class="tab-content">

        {{-- ============================= DASHBOARD KEMISKINAN EKSTREM (PRD Bagian 12) ============================= --}}
        @if ($bisaLihatKemiskinan)
        <div class="tab-pane fade show active" id="tab-kemiskinan" role="tabpanel" aria-labelledby="tab-kemiskinan-btn">

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
                            {{ number_format($kemiskinanStats['sedang_diverifikasi']) }}
                        </div>
                        <div class="metric-meta">
                            <span>menunggu keputusan verifikator</span>
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
                        <div class="stat-strip-value">{{ number_format($kemiskinanStats['valid']) }}</div>
                        <div class=\"stat-strip-label\">Data Valid</div>
                    </div>
                </div>
            </section>

            <section class="row g-3 mt-1">
                <div class="col-12 col-xl-6">
                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-bar-chart-line"
                                        aria-hidden="true"></i><span>Keluarga Berdasarkan Kecamatan</span></h2>
                                <p class="text-muted mb-0">Sebaran jumlah keluarga terdata per kecamatan.</p>
                            </div>
                            <a class="btn btn-light btn-sm" href="{{ route('laporan.index', ['modul' => 'kemiskinan']) }}">Lihat Laporan</a>
                        </div>

                        <div class="chart-bars" aria-label="Grafik keluarga berdasarkan kecamatan">
                            @forelse ($keluargaPerKecamatan as $point)
                                <div class="chart-column bar-{{ $point['percent'] }}">
                                    <span></span><small>{{ $point['label'] }}</small></div>
                            @empty
                                <p class="text-muted small mb-0">Belum ada data.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-3">
                    <div class="panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-list-check"
                                        aria-hidden="true"></i><span>Status Verifikasi</span></h2>
                                <p class="text-muted mb-0">Alur status data keluarga.</p>
                            </div>
                        </div>

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

                <div class="col-12 col-xl-3">
                    <div class="panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-award"
                                        aria-hidden="true"></i><span>Kepesertaan Program</span></h2>
                                <p class="text-muted mb-0">Jumlah keluarga penerima per program bantuan.</p>
                            </div>
                        </div>

                        <div>
                            @forelse ($rekapProgram as $program)
                                <div class="status-pipeline-item">
                                    <span class="status-pipeline-label">{{ $program->label }}</span>
                                    <strong>{{ number_format($program->jumlah) }}</strong>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">Belum ada data kepesertaan program.</p>
                            @endforelse
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
                                    'dalam verifikasi' => 'info',
                                    'perlu perbaikan' => 'warning',
                                    'valid' => 'success',
                                    'tidak valid' => 'danger',
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
        @endif

        {{-- ============================= DASHBOARD STUNTING (PRD Bagian 13) ============================= --}}
        @if ($bisaLihatStunting)
        <div class="tab-pane fade @if (! $bisaLihatKemiskinan) show active @endif" id="tab-stunting" role="tabpanel" aria-labelledby="tab-stunting-btn">

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
                            {{ number_format($stuntingStats['sedang_diverifikasi']) }}
                        </div>
                        <div class="metric-meta">
                            <span>menunggu keputusan verifikator</span>
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
                        <div class="stat-strip-value">{{ number_format($stuntingStats['valid']) }}</div>
                        <div class=\"stat-strip-label\">Data Valid</div>
                    </div>
                    @foreach ($rekapJenisKelamin as $jk)
                        <div class="stat-strip-item">
                            <div class="stat-strip-value">{{ number_format($jk->jumlah) }}</div>
                            <div class="stat-strip-label text-capitalize">{{ $jk->label }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="row g-3 mt-1">
                <div class="col-12 col-xl-6">
                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-bar-chart-line"
                                        aria-hidden="true"></i><span>Anak Berdasarkan Kecamatan</span></h2>
                                <p class="text-muted mb-0">Sebaran jumlah anak terdata per kecamatan.</p>
                            </div>
                            <a class="btn btn-light btn-sm" href="{{ route('laporan.index', ['modul' => 'stunting']) }}">Lihat Laporan</a>
                        </div>

                        <div class="chart-bars" aria-label="Grafik anak berdasarkan kecamatan">
                            @forelse ($anakPerKecamatan as $point)
                                <div class="chart-column bar-{{ $point['percent'] }}">
                                    <span></span><small>{{ $point['label'] }}</small></div>
                            @empty
                                <p class="text-muted small mb-0">Belum ada data.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-3">
                    <div class="panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-list-check"
                                        aria-hidden="true"></i><span>Status Verifikasi</span></h2>
                                <p class="text-muted mb-0">Alur status data anak.</p>
                            </div>
                        </div>

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

                <div class="col-12 col-xl-3">
                    <div class="panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-rulers"
                                        aria-hidden="true"></i><span>Hasil Pengukuran</span></h2>
                                <p class="text-muted mb-0">Kategori pengukuran terakhir per anak.</p>
                            </div>
                        </div>

                        <div>
                            @forelse ($rekapKategoriStunting as $kategori)
                                <div class="status-pipeline-item">
                                    <span class="status-pipeline-label text-capitalize">{{ str_replace('_', ' ', $kategori->label) }}</span>
                                    <strong>{{ number_format($kategori->jumlah) }}</strong>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">Belum ada hasil pengukuran.</p>
                            @endforelse
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
        @endif

    </div>

@endsection
