<header class="mb-3">
    <h2 class="h6 mb-1">Hapus Akun</h2>
    <p class="text-muted small mb-0">
        Setelah akun dihapus, semua sesi Anda akan berakhir. Tindakan ini tidak dapat dibatalkan.
    </p>
</header>

<button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalHapusAkun">
    Hapus Akun
</button>

<div class="modal fade @if ($errors->userDeletion->isNotEmpty()) show @endif" id="modalHapusAkun" tabindex="-1"
    @if ($errors->userDeletion->isNotEmpty()) style="display: block;" @endif>
    <div class="modal-dialog">
        <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title">Yakin ingin menghapus akun?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Semua data dan sesi terkait akun Anda akan dihapus secara permanen. Masukkan kata sandi untuk konfirmasi.
                </p>
                <label class="form-label" for="password_hapus_akun">Kata Sandi</label>
                <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                    id="password_hapus_akun" name="password" placeholder="Kata sandi" autocomplete="current-password">
                @error('password', 'userDeletion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus Akun</button>
            </div>
        </form>
    </div>
</div>
@if ($errors->userDeletion->isNotEmpty())
    <div class="modal-backdrop fade show"></div>
@endif
