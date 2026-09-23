@extends('layouts.app')

@section('title', 'Laporan')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-file-earmark-bar-graph',
        'eyebrow' => 'Laporan',
        'title' => $modul === 'kemiskinan' ? 'Laporan Kemiskinan Ekstrem' : 'Laporan Stunting',
        'description' => 'Rekapitulasi data '.($modul === 'kemiskinan' ? 'kemiskinan ekstrem' : 'stunting').' berdasarkan wilayah dan status validasi.',
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
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('laporan.index', ['modul' => $modul]) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                @if ($bisaExport)
                    <div class="dropdown ms-auto">
                        <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i> Export sesuai filter
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button type="submit" class="dropdown-item" formaction="{{ route('laporan.export') }}" name="format" value="csv">CSV</button></li>
                            <li><button type="submit" class="dropdown-item" formaction="{{ route('laporan.export') }}" name="format" value="xlsx">Excel (.xlsx)</button></li>
                            <li><button type="submit" class="dropdown-item" formaction="{{ route('laporan.export') }}" name="format" value="pdf">PDF</button></li>
                        </ul>
                    </div>
                @endif
            </div>
        </form>
    </section>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <section class="panel">
                <div class="p-3 pb-0"><h2 class="h6 mb-0">Rekap per Kecamatan</h2></div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kecamatan</th>
                                <th class="text-end">Jumlah {{ $modul === 'kemiskinan' ? 'Keluarga' : 'Anak' }}</th>
                                @if ($modul === 'kemiskinan')
                                    <th class="text-end">Jumlah Anggota</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekapKecamatan as $row)
                                <tr>
                                    <td>{{ $row->label }}</td>
                                    <td class="text-end">{{ $modul === 'kemiskinan' ? $row->jumlah_keluarga : $row->jumlah_anak }}</td>
                                    @if ($modul === 'kemiskinan')
                                        <td class="text-end">{{ $row->jumlah_anggota }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-6">
            <section class="panel">
                <div class="p-3 pb-0"><h2 class="h6 mb-0">Rekap per Desa/Kelurahan</h2></div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Desa/Kelurahan</th>
                                <th class="text-end">Jumlah {{ $modul === 'kemiskinan' ? 'Keluarga' : 'Anak' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekapDesa as $row)
                                <tr>
                                    <td>{{ $row->label }}</td>
                                    <td class="text-end">{{ $modul === 'kemiskinan' ? $row->jumlah_keluarga : $row->jumlah_anak }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-6">
            <section class="panel">
                <div class="p-3 pb-0"><h2 class="h6 mb-0">Rekap Status Validasi</h2></div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rekapStatus as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="text-end">{{ $row['jumlah'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        @if ($modul === 'kemiskinan')
            <div class="col-12 col-lg-6">
                <section class="panel">
                    <div class="p-3 pb-0"><h2 class="h6 mb-0">Rekap Kepesertaan Program</h2></div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Program</th>
                                    <th class="text-end">Jumlah Penerima</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rekapProgram as $row)
                                    <tr>
                                        <td>{{ $row->label }}</td>
                                        <td class="text-end">{{ $row->jumlah }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-3">Belum ada data kepesertaan program.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        @else
            <div class="col-12 col-lg-3">
                <section class="panel">
                    <div class="p-3 pb-0"><h2 class="h6 mb-0">Rekap Jenis Kelamin</h2></div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Jenis Kelamin</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rekapJenisKelamin as $row)
                                    <tr>
                                        <td class="text-capitalize">{{ $row->label }}</td>
                                        <td class="text-end">{{ $row->jumlah }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-3">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="col-12 col-lg-3">
                <section class="panel">
                    <div class="p-3 pb-0"><h2 class="h6 mb-0">Rekap Kategori Stunting</h2></div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rekapKategoriStunting as $row)
                                    <tr>
                                        <td class="text-capitalize">{{ str_replace('_', ' ', $row->label) }}</td>
                                        <td class="text-end">{{ $row->jumlah }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-3">Belum ada hasil pengukuran.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        @endif
    </div>

@push('scripts')
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
