@extends('layouts.app')

@section('title', 'Daftar Data Stunting')

@section('page-actions')
    <a class="btn btn-primary btn-sm" href="{{ route('stunting.create') }}">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Pendataan Baru
    </a>
@endsection

@php
    $statusBadgeMap = [
        'dalam_verifikasi' => 'warning',
        'perlu_perbaikan' => 'warning',
        'valid' => 'success',
        'tidak_valid' => 'danger',
    ];
    $statusLabelMap = [
        'dalam_verifikasi' => 'Dalam Verifikasi',
        'perlu_perbaikan' => 'Perlu Perbaikan',
        'valid' => 'Valid',
        'tidak_valid' => 'Tidak Valid',
    ];
@endphp

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-heart-pulse',
        'eyebrow' => 'Stunting',
        'title' => 'Daftar Data Anak',
        'description' => 'Seluruh data pendataan stunting yang tersimpan di sistem.',
    ])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <section class="panel mb-3">
        <form method="GET" action="{{ route('stunting.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small" for="q">Cari</label>
                <input type="text" class="form-control form-control-sm" id="q" name="q"
                    value="{{ $filters['q'] ?? '' }}" placeholder="NIK, No. KK, atau nama anak">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="jenis_kelamin">Jenis Kelamin</label>
                <select class="form-select form-select-sm" id="jenis_kelamin" name="jenis_kelamin">
                    <option value="">Semua</option>
                    <option value="laki-laki" @selected(($filters['jenis_kelamin'] ?? null) === 'laki-laki')>Laki-laki</option>
                    <option value="perempuan" @selected(($filters['jenis_kelamin'] ?? null) === 'perempuan')>Perempuan</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small" for="kecamatan_id">Kecamatan</label>
                <select class="form-select form-select-sm" id="kecamatan_id" name="kecamatan_id">
                    <option value="">Semua Kecamatan</option>
                    @foreach ($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->id }}" @selected(($filters['kecamatan_id'] ?? null) == $kecamatan->id)>
                            {{ $kecamatan->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="status_data">Status</label>
                <select class="form-select form-select-sm" id="status_data" name="status_data">
                    <option value="">Semua Status</option>
                    @foreach ($statusLabelMap as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status_data'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"
                        aria-hidden="true"></i> Filter</button>
                <a href="{{ route('stunting.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Nama Anak</th>
                        <th scope="col">Usia</th>
                        <th scope="col">Wilayah</th>
                        <th scope="col">Petugas</th>
                        <th scope="col">Pengukuran</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tanggal Input</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($anaks as $anak)
                        <tr>
                            <td>
                                <p class="fw-semibold mb-0">{{ $anak->nama_anak }}</p>
                                <p class="text-muted small mb-0">NIK: {{ $anak->nik_anak }}</p>
                            </td>
                            <td>{{ $anak->usia_terkini_bulan }} bln</td>
                            <td>
                                <p class="mb-0">{{ $anak->kecamatan?->nama }}</p>
                                <p class="text-muted small mb-0">{{ $anak->desaKelurahan?->nama }}</p>
                            </td>
                            <td>{{ $anak->petugas?->nama }}</td>
                            <td>
                                @if ($anak->pengukurans_count > 0)
                                    <span class="badge text-bg-success">{{ $anak->pengukurans_count }}x Diukur</span>
                                @else
                                    <span class="badge text-bg-secondary">Belum Diukur</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $statusBadgeMap[$anak->status_data] ?? 'secondary' }}">
                                    {{ $statusLabelMap[$anak->status_data] ?? $anak->status_data }}
                                </span>
                            </td>
                            <td>{{ $anak->tanggal_input?->format('d M Y') }}</td>
                            <td class="text-end">
                                <a class="btn btn-light btn-sm" href="{{ route('stunting.show', $anak) }}">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada data pendataan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $anaks->links() }}
        </div>
    </section>

@endsection
