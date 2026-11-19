@extends('layouts.app')

@section('title', 'Laporan')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-file-earmark-bar-graph',
        'eyebrow' => 'Laporan',
        'title' => $modul === 'kemiskinan' ? 'Laporan Kemiskinan Ekstrem' : 'Laporan Stunting',
        'description' => $modul === 'kemiskinan' ? 'Tabulasi Data Tunggal Sosial dan Ekonomi Nasional (DTSEN) per kecamatan dan kelurahan.' : 'Tabulasi data percepatan pencegahan dan penurunan stunting per kecamatan dan kelurahan.',
    ])

    @if ($bisaLihatKemiskinan && $bisaLihatStunting)
        <div class="btn-group mb-3" role="group">
            <a href="{{ route('laporan.index', ['modul' => 'kemiskinan']) }}"
                class="btn btn-sm {{ $modul === 'kemiskinan' ? 'btn-primary' : 'btn-outline-primary' }}">Kemiskinan Ekstrem</a>
            <a href="{{ route('laporan.index', ['modul' => 'stunting']) }}"
                class="btn btn-sm {{ $modul === 'stunting' ? 'btn-primary' : 'btn-outline-primary' }}">Stunting</a>
        </div>
    @endif

    <section class="panel mb-3">
        <form method="GET" action="{{ route('laporan.index') }}" class="row g-2 align-items-end p-3">
            <input type="hidden" name="modul" value="{{ $modul }}">
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
                        <option value="{{ $desaKelurahan->id }}" data-kecamatan="{{ $desaKelurahan->kecamatan_id }}" @selected(($filters['desa_kelurahan_id'] ?? null) == $desaKelurahan->id)>{{ $desaKelurahan->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="status_data">Status</label>
                <select class="form-select form-select-sm" id="status_data" name="status_data">
                    <option value="">Semua Status</option>
                    @foreach ($statusLabel as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status_data'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="dari">Dari Tanggal</label>
                <input type="date" class="form-control form-control-sm" id="dari" name="dari" value="{{ $filters['dari'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="sampai">Sampai Tanggal</label>
                <input type="date" class="form-control form-control-sm" id="sampai" name="sampai" value="{{ $filters['sampai'] ?? '' }}">
            </div>
            @if ($modul === 'stunting')
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="tahun">Tahun Sasaran</label>
                    <select class="form-select form-select-sm" id="tahun" name="tahun">
                        @foreach ($daftarTahun as $pilihan)
                            <option value="{{ $pilihan }}" @selected($pilihan === $tahun)>{{ $pilihan }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-12 col-md-3">
                <label class="form-label small" for="periode">Periode (judul PDF tabulasi)</label>
                <input type="text" class="form-control form-control-sm" id="periode" name="periode" maxlength="100"
                    placeholder="{{ $modul === 'kemiskinan' ? 'cth. Triwulan 3.2' : 'cth. Tahun 2026' }}" value="{{ request('periode') }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('laporan.index', ['modul' => $modul]) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                @if ($bisaExport)
                    <button type="submit" id="tombolExport" class="btn btn-outline-success btn-sm ms-auto" formaction="{{ route('laporan.export') }}">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF {{ $modul === 'kemiskinan' ? 'Tabulasi DTSEN' : 'Tabulasi Stunting' }}
                    </button>
                @endif
            </div>
        </form>
    </section>

    @if ($modul === 'kemiskinan')
        <section class="panel mb-3">
            <div class="p-3 pb-2 d-flex flex-wrap align-items-center gap-2">
                <h2 class="h6 mb-0">Tabulasi Data Tunggal Sosial dan Ekonomi Nasional (DTSEN)</h2>
                <span class="text-muted small">Jumlah KK/Jiwa hanya menghitung KK aktif; kolom Nonaktif ditampilkan terpisah.</span>
            </div>
            <div class="table-responsive">
                @include('laporan._tabulasi-dtsen', [
                    'tabulasi' => $tabulasiDtsen,
                    'tableClass' => 'table table-sm table-bordered align-middle mb-0 small text-nowrap',
                ])
            </div>
        </section>
    @else
        <section class="panel mb-3">
            <div class="p-3 pb-2">
                <h2 class="h6 mb-0">Tabulasi Data Stunting</h2>
            </div>
            <div class="px-3 pb-3 tabulasi-stunting">
                @include('laporan._tabulasi-stunting', [
                    'tabulasi' => $tabulasiStunting,
                    'periode' => $periode,
                    'tahun' => $tahun,
                    'tableClass' => 'table table-sm table-bordered align-middle mb-0 small',
                    'wrapClass' => 'table-responsive',
                ])
            </div>
        </section>
    @endif

@push('styles')
    <style>
        .tabel-dtsen thead th { background: var(--bs-tertiary-bg); text-align: center; vertical-align: middle; text-transform: uppercase; font-size: .72rem; }
        .tabel-dtsen td.angka { text-align: right; font-variant-numeric: tabular-nums; }
        .tabel-dtsen .tengah { text-align: center; }
        .tabel-dtsen td.kecamatan { font-weight: 600; }
        .tabel-dtsen tr.baris-jumlah td { background: #fde2c8; font-weight: 600; }
        .tabel-dtsen tr.baris-total td { background: #e26b0a; color: #fff; font-weight: 700; }
        .tabulasi-stunting h3.judul-tabel { font-size: .9rem; font-weight: 600; margin: 1.25rem 0 .5rem; }
        .tabulasi-stunting h3.judul-tabel:first-child { margin-top: 0; }
        .tabulasi-stunting h4.subjudul-tabel { font-size: .82rem; font-weight: 600; margin: 1rem 0 .4rem; }
        .tabulasi-stunting .tabel-ansit th { background: var(--bs-tertiary-bg); text-align: center; vertical-align: middle; font-size: .72rem; }
        .tabulasi-stunting .tabel-ansit td.angka { text-align: right; font-variant-numeric: tabular-nums; }
        .tabulasi-stunting .tabel-ansit tr.baris-total td { font-weight: 700; }
        .tabulasi-stunting .grafik-radar { margin: .5rem 0 1rem; }
        .tabulasi-stunting .grafik-radar-kanvas { position: relative; max-width: 720px; height: 520px; margin: 0 auto; }
        @media (max-width: 576px) { .tabulasi-stunting .grafik-radar-kanvas { height: 380px; } }
        .tabulasi-stunting p.keterangan { font-size: .75rem; color: var(--bs-secondary-color); font-style: italic; margin: .75rem 0 0; }
    </style>
@endpush

@push('scripts')
    @if ($modul === 'stunting')
        <script src="{{ asset('assets/js/chart.umd.min.js') }}"></script>
        <script>
            (function () {
                const kanvas = document.getElementById('grafikRadarStunting');

                if (!kanvas || typeof Chart === 'undefined') {
                    return;
                }

                const sumbu = JSON.parse(kanvas.dataset.sumbu);

                /** Konfigurasi radar; untukCetak = warna terang, latar putih, tanpa animasi (dipakai untuk PDF). */
                const konfigurasi = (warnaTeks, untukCetak) => ({
                    type: 'radar',
                    data: {
                        labels: sumbu.map((s) => s.label),
                        datasets: [
                            {
                                label: 'Target',
                                data: sumbu.map((s) => s.target),
                                borderColor: '#ff6384',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                pointBackgroundColor: '#ff6384',
                                borderWidth: 3,
                            },
                            {
                                label: 'Capaian',
                                data: sumbu.map((s) => s.capaian),
                                borderColor: '#36a2eb',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                pointBackgroundColor: '#36a2eb',
                                borderWidth: 3,
                            },
                        ],
                    },
                    options: {
                        responsive: !untukCetak,
                        maintainAspectRatio: false,
                        animation: untukCetak ? false : { duration: 600 },
                        devicePixelRatio: untukCetak ? 2 : undefined,
                        plugins: {
                            legend: { position: 'top', labels: { color: warnaTeks } },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.r.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%`,
                                },
                            },
                        },
                        scales: {
                            r: {
                                min: 0,
                                max: 100,
                                ticks: { stepSize: 10, backdropColor: 'transparent', color: '#888' },
                                pointLabels: { color: warnaTeks, font: { size: 11 } },
                                grid: { color: 'rgba(136, 136, 136, 0.25)' },
                                angleLines: { color: 'rgba(136, 136, 136, 0.25)' },
                            },
                        },
                    },
                    plugins: untukCetak ? [{
                        id: 'latarPutih',
                        beforeDraw: (chart) => {
                            const { ctx, width, height } = chart;
                            ctx.save();
                            ctx.fillStyle = '#ffffff';
                            ctx.fillRect(0, 0, width, height);
                            ctx.restore();
                        },
                    }] : [],
                });

                window.grafikRadarStunting = new Chart(kanvas, konfigurasi(getComputedStyle(document.body).color, false));

                /** Gambar PNG versi cetak untuk dikirim ke export PDF. */
                window.gambarRadarUntukPdf = function () {
                    const kanvasCetak = document.createElement('canvas');
                    kanvasCetak.width = 760;
                    kanvasCetak.height = 600;
                    const grafikCetak = new Chart(kanvasCetak, konfigurasi('#555555', true));
                    const gambar = grafikCetak.toBase64Image('image/png', 1);
                    grafikCetak.destroy();

                    return gambar;
                };
            })();

            (function () {
                const tombol = document.getElementById('tombolExport');

                if (!tombol) {
                    return;
                }

                // Gambar Chart.js dikirim lewat POST agar ikut tercetak di PDF (dompdf tidak menjalankan JavaScript).
                tombol.addEventListener('click', function (event) {
                    if (typeof window.gambarRadarUntukPdf !== 'function') {
                        return;
                    }

                    event.preventDefault();

                    const formulir = document.createElement('form');
                    formulir.method = 'POST';
                    formulir.action = tombol.getAttribute('formaction');
                    formulir.style.display = 'none';

                    const tambah = (nama, nilai) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = nama;
                        input.value = nilai;
                        formulir.appendChild(input);
                    };

                    new FormData(tombol.form).forEach((nilai, nama) => tambah(nama, nilai));
                    tambah('_token', @json(csrf_token()));
                    tambah('grafik_radar', window.gambarRadarUntukPdf());

                    document.body.appendChild(formulir);
                    formulir.submit();
                    formulir.remove();
                });
            })();
        </script>
    @endif
    <script>
        (function () {
            const kecamatan = document.getElementById('kecamatan_id');
            const desa = document.getElementById('desa_kelurahan_id');

            function filterDesa() {
                Array.from(desa.options).forEach(function (opt) {
                    if (!opt.value) return;
                    const cocok = !kecamatan.value || opt.dataset.kecamatan === kecamatan.value;
                    opt.hidden = !cocok;
                    opt.disabled = !cocok;
                });

                if (desa.selectedOptions[0] && desa.selectedOptions[0].disabled) {
                    desa.value = '';
                }
            }

            kecamatan.addEventListener('change', filterDesa);
            filterDesa();
        })();
    </script>
@endpush

@endsection
