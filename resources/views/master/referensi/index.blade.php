@extends('layouts.app')

@section('title', 'Master Referensi')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-tags',
        'eyebrow' => 'Master Data',
        'title' => 'Referensi',
        'description' => 'Kelola data referensi (dropdown) yang dipakai formulir pendataan, contoh: jenis kelamin, pendidikan, jenis atap, dsb.',
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
            <form method="GET" action="{{ route('master.referensi.index') }}" class="d-flex gap-2 flex-grow-1">
                <select class="form-select form-select-sm w-auto" name="kategori" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori }}" @selected(($filters['kategori'] ?? null) === $kategori)>{{ $kategori }}</option>
                    @endforeach
                </select>
                @if ($filters['kategori'] ?? null)
                    <a href="{{ route('master.referensi.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                @endif
            </form>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahReferensi">
                <i class="bi bi-plus-lg"></i> Tambah Referensi
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Kode</th>
                        <th>Nilai</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($referensis as $referensi)
                        <tr>
                            <td><code>{{ $referensi->kategori }}</code></td>
                            <td>{{ $referensi->kode }}</td>
                            <td>{{ $referensi->nilai }}</td>
                            <td>{{ $referensi->urutan }}</td>
                            <td>
                                <span class="badge text-bg-{{ $referensi->is_active ? 'success' : 'secondary' }}">
                                    {{ $referensi->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalEditReferensi-{{ $referensi->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('master.referensi.destroy', $referensi) }}"
                                    class="d-inline" onsubmit="return confirm('Hapus referensi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data referensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $referensis->links() }}</div>
    </section>

    @foreach ($referensis as $referensi)
        <div class="modal fade" id="modalEditReferensi-{{ $referensi->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('master.referensi.update', $referensi) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    @php $iniForm = 'modalEditReferensi-'.$referensi->id; @endphp
                    <input type="hidden" name="_form" value="{{ $iniForm }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Referensi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror"
                                value="{{ old('_form') === $iniForm ? old('kategori') : $referensi->kategori }}" required>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode</label>
                            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                                value="{{ old('_form') === $iniForm ? old('kode') : $referensi->kode }}" required>
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nilai (label tampilan)</label>
                            <input type="text" name="nilai" class="form-control @error('nilai') is-invalid @enderror"
                                value="{{ old('_form') === $iniForm ? old('nilai') : $referensi->nilai }}" required>
                            @error('nilai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="urutan" class="form-control"
                                value="{{ old('_form') === $iniForm ? old('urutan') : $referensi->urutan }}" min="0">
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                id="editReferensiAktif-{{ $referensi->id }}" @checked($referensi->is_active)>
                            <label class="form-check-label" for="editReferensiAktif-{{ $referensi->id }}">Aktif</label>
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

    {{-- Modal tambah referensi --}}
    <div class="modal fade" id="modalTambahReferensi" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('master.referensi.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="_form" value="modalTambahReferensi">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Referensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror"
                            value="{{ old('_form') === 'modalTambahReferensi' ? old('kategori') : '' }}"
                            list="daftarKategori" placeholder="contoh: jenis_atap" required>
                        <datalist id="daftarKategori">
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori }}"></option>
                            @endforeach
                        </datalist>
                        @error('kategori')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                            value="{{ old('_form') === 'modalTambahReferensi' ? old('kode') : '' }}" required>
                        @error('kode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai (label tampilan)</label>
                        <input type="text" name="nilai" class="form-control @error('nilai') is-invalid @enderror"
                            value="{{ old('_form') === 'modalTambahReferensi' ? old('nilai') : '' }}" required>
                        @error('nilai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" name="urutan" class="form-control"
                            value="{{ old('_form') === 'modalTambahReferensi' ? old('urutan') : 0 }}" min="0">
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
