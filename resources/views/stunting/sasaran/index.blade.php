@extends('layouts.app')

@section('title', 'Data Sasaran Stunting')

@php($kolom = \App\Models\SasaranStunting::KOLOM)

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-people',
        'eyebrow' => 'Stunting',
        'title' => 'Data Sasaran per Kecamatan',
        'description' => 'Data populasi sasaran per tahun (Dukcapil, Dinkes, KUA) untuk Tabel Demografi dan capaian Remaja Putri pada laporan stunting.',
    ])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="panel mb-3">
        <form method="GET" action="{{ route('stunting.sasaran.index') }}" class="row g-2 align-items-end p-3">
            <div class="col-6 col-md-2">
                <label class="form-label small" for="tahun">Tahun</label>
                <select class="form-select form-select-sm" id="tahun" name="tahun" onchange="this.form.submit()">
                    @foreach ($daftarTahun as $pilihan)
                        <option value="{{ $pilihan }}" @selected($pilihan === $tahun)>{{ $pilihan }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </section>

    <section class="panel">
        <form method="POST" action="{{ route('stunting.sasaran.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="tahun" value="{{ $tahun }}">

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 tabel-sasaran">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-nowrap">Kecamatan</th>
                            <th colspan="9" class="text-center">Jumlah Sasaran</th>
                            <th colspan="2" class="text-center">Capaian Remaja Putri</th>
                        </tr>
                        <tr>
                            @foreach ($kolom as $label)
                                <th class="text-center small">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kecamatans as $kecamatan)
                            @php($baris = $sasaran->get($kecamatan->id))
                            <tr>
                                <td class="text-nowrap">
                                    {{ $kecamatan->nama }}
                                    <input type="hidden" name="sasaran[{{ $loop->index }}][kecamatan_id]" value="{{ $kecamatan->id }}">
                                </td>
                                @foreach (array_keys($kolom) as $kunci)
                                    <td>
                                        <input type="number" min="0" step="1" inputmode="numeric"
                                            class="form-control form-control-sm text-end @error('sasaran.'.$loop->parent->index.'.'.$kunci) is-invalid @enderror"
                                            name="sasaran[{{ $loop->parent->index }}][{{ $kunci }}]"
                                            value="{{ old('sasaran.'.$loop->parent->index.'.'.$kunci, $baris?->{$kunci}) }}"
                                            aria-label="{{ $kolom[$kunci] }} {{ $kecamatan->nama }}"
                                            @disabled(! $bisaUbah)>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ 1 + count($kolom) }}" class="text-center text-muted py-3">Belum ada kecamatan aktif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted small">Kosongkan kolom yang datanya belum tersedia; di laporan akan tampil "–".</span>
                @if ($bisaUbah && $kecamatans->isNotEmpty())
                    <button type="submit" class="btn btn-primary btn-sm ms-auto"><i class="bi bi-save"></i> Simpan Data Tahun {{ $tahun }}</button>
                @endif
            </div>
        </form>
    </section>

@push('styles')
    <style>
        .tabel-sasaran input.form-control { min-width: 92px; }
        .tabel-sasaran thead th { background: var(--bs-tertiary-bg); vertical-align: middle; }
    </style>
@endpush

@endsection
