<header class="mb-3">
    <h2 class="h6 mb-1">Informasi Profil</h2>
    <p class="text-muted small mb-0">Perbarui nama, username, email, dan nomor HP akun Anda.</p>
</header>

@if (session('status') === 'profile-updated')
    <div class="alert alert-success py-2">Profil berhasil diperbarui.</div>
@endif

<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('PATCH')

    <div class="mb-3">
        <label class="form-label" for="nama">Nama Lengkap</label>
        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama"
            value="{{ old('nama', $user->nama) }}" required autofocus autocomplete="name">
        @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username"
            value="{{ old('username', $user->username) }}" required autocomplete="username">
        @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
            value="{{ old('email', $user->email) }}" required autocomplete="email">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="nomor_hp">Nomor HP</label>
        <input type="text" class="form-control @error('nomor_hp') is-invalid @enderror" id="nomor_hp" name="nomor_hp"
            value="{{ old('nomor_hp', $user->nomor_hp) }}" autocomplete="tel">
        @error('nomor_hp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
</form>
