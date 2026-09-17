<header class="mb-3">
    <h2 class="h6 mb-1">Ubah Kata Sandi</h2>
    <p class="text-muted small mb-0">Gunakan kata sandi yang panjang dan acak agar akun tetap aman.</p>
</header>

@if (session('status') === 'password-updated')
    <div class="alert alert-success py-2">Kata sandi berhasil diperbarui.</div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label" for="update_password_current_password">Kata Sandi Saat Ini</label>
        <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
            id="update_password_current_password" name="current_password" autocomplete="current-password">
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="update_password_password">Kata Sandi Baru</label>
        <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror"
            id="update_password_password" name="password" autocomplete="new-password">
        @error('password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="update_password_password_confirmation">Konfirmasi Kata Sandi Baru</label>
        <input type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
            id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password">
        @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
</form>
