@php
    $petugas = $petugas ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nama">Nama Lengkap</label>
        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama"
            value="{{ old('nama', $petugas?->nama) }}" required>
        @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="role_id">Role</label>
        <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
            <option value="">Pilih Role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected((int) old('role_id', $petugas?->role_id) === $role->id)>{{ $role->nama }}</option>
            @endforeach
        </select>
        @error('role_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="username">Username</label>
        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username"
            value="{{ old('username', $petugas?->username) }}" required autocomplete="off">
        @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="email">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
            value="{{ old('email', $petugas?->email) }}" required autocomplete="off">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="nomor_hp">Nomor HP</label>
        <input type="text" class="form-control @error('nomor_hp') is-invalid @enderror" id="nomor_hp" name="nomor_hp"
            value="{{ old('nomor_hp', $petugas?->nomor_hp) }}">
        @error('nomor_hp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="status">Status</label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            <option value="aktif" @selected(old('status', $petugas?->status ?? 'aktif') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(old('status', $petugas?->status) === 'nonaktif')>Nonaktif</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="kecamatan_id">Kecamatan (wilayah kewenangan)</label>
        <select class="form-select @error('kecamatan_id') is-invalid @enderror" id="kecamatan_id" name="kecamatan_id">
            <option value="">Semua Wilayah</option>
            @foreach ($kecamatans as $kecamatan)
                <option value="{{ $kecamatan->id }}" @selected((int) old('kecamatan_id', $petugas?->kecamatan_id) === $kecamatan->id)>{{ $kecamatan->nama }}</option>
            @endforeach
        </select>
        @error('kecamatan_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="desa_kelurahan_id">Desa/Kelurahan (wilayah kewenangan)</label>
        <select class="form-select @error('desa_kelurahan_id') is-invalid @enderror" id="desa_kelurahan_id" name="desa_kelurahan_id">
            <option value="">Semua Desa/Kelurahan</option>
            @foreach ($kecamatans as $kecamatan)
                @foreach ($kecamatan->desaKelurahans as $desaKelurahan)
                    <option value="{{ $desaKelurahan->id }}" @selected((int) old('desa_kelurahan_id', $petugas?->desa_kelurahan_id) === $desaKelurahan->id)>
                        {{ $kecamatan->nama }} — {{ $desaKelurahan->nama }}
                    </option>
                @endforeach
            @endforeach
        </select>
        @error('desa_kelurahan_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @unless ($petugas)
        <div class="col-md-6">
            <label class="form-label" for="password">Kata Sandi</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                required autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                required autocomplete="new-password">
        </div>
    @endunless
</div>
