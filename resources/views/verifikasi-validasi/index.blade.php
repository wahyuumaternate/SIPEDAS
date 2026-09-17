@extends('layouts.app')

@section('title', 'Verifikasi & Validasi')

@php
    $statusBadgeMap = [
        'dikirim' => 'info',
        'dalam_verifikasi' => 'warning',
    ];
    $statusLabelMap = [
        'dikirim' => 'Dikirim',
        'dalam_verifikasi' => 'Dalam Verifikasi',
    ];
@endphp

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-patch-check',
        'eyebrow' => 'Verifikasi & Validasi',
        'title' => 'Antrian Verifikasi & Validasi',
        'description' => 'Ringkasan gabungan data Kemiskinan Ekstrem dan Stunting yang menunggu atau sedang diverifikasi, beserta indikasi duplikasi dari validasi otomatis.',
    ])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-3">
        @if ($lihatKemiskinan)
            <div class="col-6 col-lg-3">
                <div class="panel p-3">
                    <p class="text-muted small mb-1">Kemiskinan · Dikirim</p>
                    <p class="h4 mb-0">{{ $ringkasan['kemiskinan_dikirim'] }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="panel p-3">
                    <p class="text-muted small mb-1">Kemiskinan · Dalam Verifikasi</p>
                    <p class="h4 mb-0">{{ $ringkasan['kemiskinan_dalam_verifikasi'] }}</p>
                </div>
            </div>
        @endif
        @if ($lihatStunting)
            <div class="col-6 col-lg-3">
                <div class="panel p-3">
                    <p class="text-muted small mb-1">Stunting · Dikirim</p>
                    <p class="h4 mb-0">{{ $ringkasan['stunting_dikirim'] }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="panel p-3">
                    <p class="text-muted small mb-1">Stunting · Dalam Verifikasi</p>
                    <p class="h4 mb-0">{{ $ringkasan['stunting_dalam_verifikasi'] }}</p>
                </div>
            </div>
        @endif
    </div>

    <section class="panel mb-3">
        <form method="GET" action="{{ route('verifikasi-validasi.index') }}" class="row g-2 align-items-end p-3">
            <div class="col-6 col-md-4">
                <label class="form-label small" for="status_data">Status</label>
                <select class="form-select form-select-sm" id="status_data" name="status_data">
                    <option value="">Semua Status</option>
                    @foreach ($statusLabelMap as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status_data'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label small" for="kecamatan_id">Kecamatan</label>
                <select class="form-select form-select-sm" id="kecamatan_id" name="kecamatan_id">
                    <option value="">Semua Kecamatan</option>
                    @foreach ($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->id }}" @selected(($filters['kecamatan_id'] ?? null) == $kecamatan->id)>{{ $kecamatan->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('verifikasi-validasi.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </section>

    @if ($lihatKemiskinan)
        <section class="panel mb-3">
            <div class="p-3 pb-0 d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Kemiskinan Ekstrem</h2>
                <a href="{{ route('kemiskinan.verifikasi.index') }}" class="btn btn-outline-secondary btn-sm">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kepala Keluarga</th>
                            <th>Wilayah</th>
                            <th>Petugas</th>
                            <th>Status</th>
                            <th>Tanggal Dikirim</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($antrianKemiskinan as $keluarga)
                            <tr>
                                <td>
                                    <p class="fw-semibold mb-0">{{ $keluarga->nama_kepala_keluarga }}</p>
                                    <p class="text-muted small mb-0">
                                        {{ $keluarga->kode_pendataan }}
                                        @if ($duplikatKeluarga->contains($keluarga->nik_kepala_keluarga) || $duplikatKeluarga->contains($keluarga->nomor_kk))
                                            <span class="badge text-bg-danger ms-1"><i class="bi bi-exclamation-triangle"></i> Berpotensi Duplikat</span>
                                        @endif
                                    </p>
                                </td>
                                <td>{{ $keluarga->kecamatan?->nama }}, {{ $keluarga->desaKelurahan?->nama }}</td>
                                <td>{{ $keluarga->petugas?->nama }}</td>
                                <td><span class="badge text-bg-{{ $statusBadgeMap[$keluarga->status_data] ?? 'secondary' }}">{{ $statusLabelMap[$keluarga->status_data] ?? $keluarga->status_data }}</span></td>
                                <td>{{ optional($keluarga->tanggal_pendataan)->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-primary btn-sm" href="{{ route('kemiskinan.show', $keluarga) }}">Verifikasi</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data dalam antrian verifikasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $antrianKemiskinan->links() }}</div>
        </section>
    @endif

    @if ($lihatStunting)
        <section class="panel">
            <div class="p-3 pb-0 d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Stunting</h2>
                <a href="{{ route('stunting.verifikasi.index') }}" class="btn btn-outline-secondary btn-sm">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Anak</th>
                            <th>Wilayah</th>
                            <th>Petugas</th>
                            <th>Status</th>
                            <th>Tanggal Dikirim</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($antrianStunting as $anak)
                            <tr>
                                <td>
                                    <p class="fw-semibold mb-0">{{ $anak->nama_anak }}</p>
                                    <p class="text-muted small mb-0">
                                        {{ $anak->kode_pendataan }}
                                        @if ($duplikatAnak->contains($anak->nik_anak) || $duplikatAnak->contains($anak->nomor_kk))
                                            <span class="badge text-bg-danger ms-1"><i class="bi bi-exclamation-triangle"></i> Berpotensi Duplikat</span>
                                        @endif
                                    </p>
                                </td>
                                <td>{{ $anak->kecamatan?->nama }}, {{ $anak->desaKelurahan?->nama }}</td>
                                <td>{{ $anak->petugas?->nama }}</td>
                                <td><span class="badge text-bg-{{ $statusBadgeMap[$anak->status_data] ?? 'secondary' }}">{{ $statusLabelMap[$anak->status_data] ?? $anak->status_data }}</span></td>
                                <td>{{ optional($anak->tanggal_pendataan)->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-primary btn-sm" href="{{ route('stunting.show', $anak) }}">Verifikasi</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data dalam antrian verifikasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $antrianStunting->links() }}</div>
        </section>
    @endif

@endsection
