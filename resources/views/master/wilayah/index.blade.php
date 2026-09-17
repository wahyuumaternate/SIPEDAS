@extends('layouts.app')

@section('title', 'Master Wilayah')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-geo-alt',
        'eyebrow' => 'Master Data',
        'title' => 'Wilayah',
        'description' => 'Kelola data kecamatan dan desa/kelurahan.',
    ])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
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
        <div class="d-flex justify-content-between align-items-center p-3 pb-0">
            <h2 class="h5 mb-0">Kecamatan</h2>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKecamatan">
                <i class="bi bi-plus-lg"></i> Tambah Kecamatan
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Desa/Kelurahan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kecamatans as $kecamatan)
                        <tr>
                            <td>{{ $kecamatan->kode }}</td>
                            <td>{{ $kecamatan->nama }}</td>
                            <td>{{ $kecamatan->desa_kelurahans_count }}</td>
                            <td>
                                <span class="badge text-bg-{{ $kecamatan->is_active ? 'success' : 'secondary' }}">
                                    {{ $kecamatan->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalEditKecamatan-{{ $kecamatan->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('master.wilayah.kecamatan.destroy', $kecamatan) }}"
                                    class="d-inline" onsubmit="return confirm('Hapus kecamatan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data kecamatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @foreach ($kecamatans as $kecamatan)
        <div class="modal fade" id="modalEditKecamatan-{{ $kecamatan->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('master.wilayah.kecamatan.update', $kecamatan) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    @php $iniForm = 'modalEditKecamatan-'.$kecamatan->id; @endphp
                    <input type="hidden" name="_form" value="{{ $iniForm }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Kecamatan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode</label>
                            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                                value="{{ old('_form') === $iniForm ? old('kode') : $kecamatan->kode }}" required>
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                value="{{ old('_form') === $iniForm ? old('nama') : $kecamatan->nama }}" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                id="editKecamatanAktif-{{ $kecamatan->id }}" @checked($kecamatan->is_active)>
                            <label class="form-check-label" for="editKecamatanAktif-{{ $kecamatan->id }}">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <section class="panel">
        <div class="d-flex justify-content-between align-items-center p-3 pb-0">
            <h2 class="h5 mb-0">Desa/Kelurahan</h2>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahDesaKelurahan">
                <i class="bi bi-plus-lg"></i> Tambah Desa/Kelurahan
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Kecamatan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($desaKelurahans as $desaKelurahan)
                        <tr>
                            <td>{{ $desaKelurahan->kode }}</td>
                            <td>{{ $desaKelurahan->nama }}</td>
                            <td class="text-capitalize">{{ $desaKelurahan->jenis }}</td>
                            <td>{{ $desaKelurahan->kecamatan?->nama }}</td>
                            <td>
                                <span class="badge text-bg-{{ $desaKelurahan->is_active ? 'success' : 'secondary' }}">
                                    {{ $desaKelurahan->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalEditDesaKelurahan-{{ $desaKelurahan->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('master.wilayah.desa-kelurahan.destroy', $desaKelurahan) }}"
                                    class="d-inline" onsubmit="return confirm('Hapus desa/kelurahan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data desa/kelurahan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @foreach ($desaKelurahans as $desaKelurahan)
        <div class="modal fade" id="modalEditDesaKelurahan-{{ $desaKelurahan->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('master.wilayah.desa-kelurahan.update', $desaKelurahan) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    @php
                        $iniForm = 'modalEditDesaKelurahan-'.$desaKelurahan->id;
                        $kecamatanTerpilih = old('_form') === $iniForm ? (int) old('kecamatan_id') : $desaKelurahan->kecamatan_id;
                        $jenisTerpilih = old('_form') === $iniForm ? old('jenis') : $desaKelurahan->jenis;
                    @endphp
                    <input type="hidden" name="_form" value="{{ $iniForm }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Desa/Kelurahan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kecamatan</label>
                            <select name="kecamatan_id" class="form-select @error('kecamatan_id') is-invalid @enderror" required>
                                @foreach ($kecamatans as $opsiKecamatan)
                                    <option value="{{ $opsiKecamatan->id }}" @selected($opsiKecamatan->id === $kecamatanTerpilih)>{{ $opsiKecamatan->nama }}</option>
                                @endforeach
                            </select>
                            @error('kecamatan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode</label>
                            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                                value="{{ old('_form') === $iniForm ? old('kode') : $desaKelurahan->kode }}" required>
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                value="{{ old('_form') === $iniForm ? old('nama') : $desaKelurahan->nama }}" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-select" required>
                                <option value="desa" @selected($jenisTerpilih === 'desa')>Desa</option>
                                <option value="kelurahan" @selected($jenisTerpilih === 'kelurahan')>Kelurahan</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                id="editDesaAktif-{{ $desaKelurahan->id }}" @checked($desaKelurahan->is_active)>
                            <label class="form-check-label" for="editDesaAktif-{{ $desaKelurahan->id }}">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Modal tambah kecamatan --}}
    <div class="modal fade" id="modalTambahKecamatan" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('master.wilayah.kecamatan.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="_form" value="modalTambahKecamatan">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kecamatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                            value="{{ old('_form') === 'modalTambahKecamatan' ? old('kode') : '' }}" required>
                        @error('kode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                            value="{{ old('_form') === 'modalTambahKecamatan' ? old('nama') : '' }}" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal tambah desa/kelurahan --}}
    <div class="modal fade" id="modalTambahDesaKelurahan" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('master.wilayah.desa-kelurahan.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="_form" value="modalTambahDesaKelurahan">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Desa/Kelurahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kecamatan</label>
                        <select name="kecamatan_id" class="form-select @error('kecamatan_id') is-invalid @enderror" required>
                            <option value="">Pilih Kecamatan</option>
                            @foreach ($kecamatans as $opsiKecamatan)
                                <option value="{{ $opsiKecamatan->id }}" @selected(old('_form') === 'modalTambahDesaKelurahan' && (int) old('kecamatan_id') === $opsiKecamatan->id)>{{ $opsiKecamatan->nama }}</option>
                            @endforeach
                        </select>
                        @error('kecamatan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                            value="{{ old('_form') === 'modalTambahDesaKelurahan' ? old('kode') : '' }}" required>
                        @error('kode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                            value="{{ old('_form') === 'modalTambahDesaKelurahan' ? old('nama') : '' }}" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis</label>
                        @php $jenisBaru = old('_form') === 'modalTambahDesaKelurahan' ? old('jenis') : 'kelurahan'; @endphp
                        <select name="jenis" class="form-select" required>
                            <option value="kelurahan" @selected($jenisBaru === 'kelurahan')>Kelurahan</option>
                            <option value="desa" @selected($jenisBaru === 'desa')>Desa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @if ($errors->any() && old('_form'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById(@json(old('_form')));
                if (el) {
                    new bootstrap.Modal(el).show();
                }
            });
        </script>
    @endif

@endsection
