@extends('layouts.app')

@section('title', 'Verifikasi Stunting')

@php
    $statusBadgeMap = [
        'dikirim' => 'info',
        'dalam_verifikasi' => 'warning',
        'perlu_perbaikan' => 'warning',
    ];
    $statusLabelMap = [
        'dikirim' => 'Dikirim',
        'dalam_verifikasi' => 'Dalam Verifikasi',
        'perlu_perbaikan' => 'Perlu Perbaikan',
    ];
@endphp

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-patch-check',
        'eyebrow' => 'Stunting',
        'title' => 'Antrian Verifikasi',
        'description' => 'Data yang menunggu atau sedang dalam proses verifikasi & validasi.',
    ])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <section class="panel mb-3">
        <form method="GET" action="{{ route('stunting.verifikasi.index') }}" class="row g-2 align-items-end">
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
                <a href="{{ route('stunting.verifikasi.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama Anak</th>
                        <th>Wilayah</th>
                        <th>Petugas</th>
                        <th>Status</th>
                        <th>Tanggal Dikirim</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($antrian as $anak)
                        <tr>
                            <td>
                                <p class="fw-semibold mb-0">{{ $anak->nama_anak }}</p>
                                <p class="text-muted small mb-0">{{ $anak->kode_pendataan }}</p>
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
        <div class="p-3">{{ $antrian->links() }}</div>
    </section>

@endsection
