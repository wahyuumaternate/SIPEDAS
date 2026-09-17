@php
    $role = $role ?? null;
    $hakAksesTerpilih = old('hak_akses', $role?->hak_akses ?? []);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nama">Nama Role</label>
        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama"
            value="{{ old('nama', $role?->nama) }}" required>
        @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="slug">Slug</label>
        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug"
            value="{{ old('slug', $role?->slug) }}" {{ $role && $role->slug === 'super-admin' ? 'readonly' : '' }} required>
        @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="deskripsi">Deskripsi</label>
        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="2">{{ old('deskripsi', $role?->deskripsi) }}</textarea>
        @error('deskripsi')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                @checked(old('is_active', $role?->is_active ?? true))>
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>

    <div class="col-12">
        <hr>
        <h2 class="h6">Hak Akses</h2>
        @if ($role && $role->slug === 'super-admin')
            <p class="text-muted small">Super Admin selalu memiliki seluruh hak akses secara otomatis, terlepas dari pilihan di bawah ini.</p>
        @endif
        @foreach ($permissionGroups as $kelompok => $permissions)
            <p class="fw-semibold small text-muted mb-1 mt-3">{{ $kelompok }}</p>
            <div class="row">
                @foreach ($permissions as $permission)
                    <div class="col-md-6 col-lg-4">
                        <div class="form-check">
                            <input type="checkbox" name="hak_akses[]" value="{{ $permission->value }}"
                                class="form-check-input" id="hak-{{ $permission->value }}"
                                @checked(in_array($permission->value, $hakAksesTerpilih, true))>
                            <label class="form-check-label" for="hak-{{ $permission->value }}">{{ $permission->label() }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
