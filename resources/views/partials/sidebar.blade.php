<aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
    <div class="sidebar-header">
        <a class="brand-mark" href="{{ url('/') }}" aria-label="SIPEDAS dashboard">
            <img src="{{ asset('logo_kota.png') }}" width="40" alt="">
            <span class="brand-copy">
                <span class="brand-title">SIPEDAS</span>
                <span class="brand-subtitle">Pendataan Kemiskinan & Stunting</span>
            </span>
        </a>
    </div>

    <nav class="sidebar-nav" id="sidebarNav">

        {{-- Dashboard --}}
        <a class="nav-link @if (request()->is('/')) active @endif" href="{{ url('/') }}"
            @if (request()->is('/')) aria-current="page" @endif>
            <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
            <span class="nav-text">Dashboard</span>
        </a>

        {{-- Pendataan Kemiskinan Ekstrem (dropdown) --}}
        <a class="nav-link nav-link-toggle @if (request()->is('kemiskinan*')) active @endif" href="#groupKemiskinan"
            data-bs-toggle="collapse" data-bs-parent="#sidebarNav" role="button"
            aria-expanded="{{ request()->is('kemiskinan*') ? 'true' : 'false' }}" aria-controls="groupKemiskinan">
            <span class="nav-icon"><i class="bi bi-house-heart" aria-hidden="true"></i></span>
            <span class="nav-text">Kemiskinan</span>
            <span class="nav-caret"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
        </a>
        <div class="collapse nav-collapse-group @if (request()->is('kemiskinan*')) show @endif" id="groupKemiskinan">
            <div class="nav-submenu">
                <a class="nav-link nav-sublink @if (request()->is('kemiskinan/data*')) active @endif" href="{{ route('kemiskinan.index') }}"
                    @if (request()->is('kemiskinan/data*')) aria-current="page" @endif>
                    <span class="nav-text">Daftar Data</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('kemiskinan/create*')) active @endif" href="{{ route('kemiskinan.create') }}"
                    @if (request()->is('kemiskinan/create*')) aria-current="page" @endif>
                    <span class="nav-text">Pendataan Baru</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('kemiskinan/keluarga/*')) active @endif" href="{{ route('kemiskinan.index') }}"
                    @if (request()->is('kemiskinan/keluarga/*')) aria-current="page" @endif>
                    <span class="nav-text">Detail Keluarga</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('kemiskinan/verifikasi*')) active @endif" href="{{ route('kemiskinan.verifikasi.index') }}"
                    @if (request()->is('kemiskinan/verifikasi*')) aria-current="page" @endif>
                    <span class="nav-text">Verifikasi</span>
                </a>
            </div>
        </div>

        {{-- Pendataan Stunting (dropdown) --}}
        <a class="nav-link nav-link-toggle @if (request()->is('stunting*')) active @endif" href="#groupStunting"
            data-bs-toggle="collapse" data-bs-parent="#sidebarNav" role="button"
            aria-expanded="{{ request()->is('stunting*') ? 'true' : 'false' }}" aria-controls="groupStunting">
            <span class="nav-icon"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span>
            <span class="nav-text">Stunting</span>
            <span class="nav-caret"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
        </a>
        <div class="collapse nav-collapse-group @if (request()->is('stunting*')) show @endif" id="groupStunting">
            <div class="nav-submenu">
                <a class="nav-link nav-sublink @if (request()->is('stunting/data*')) active @endif" href="{{ route('stunting.index') }}"
                    @if (request()->is('stunting/data*')) aria-current="page" @endif>
                    <span class="nav-text">Daftar Data</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('stunting/create*')) active @endif" href="{{ route('stunting.create') }}"
                    @if (request()->is('stunting/create*')) aria-current="page" @endif>
                    <span class="nav-text">Pendataan Baru</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('stunting/anak/*')) active @endif" href="{{ route('stunting.index') }}"
                    @if (request()->is('stunting/anak/*')) aria-current="page" @endif>
                    <span class="nav-text">Detail Anak</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('stunting/verifikasi*')) active @endif" href="{{ route('stunting.verifikasi.index') }}"
                    @if (request()->is('stunting/verifikasi*')) aria-current="page" @endif>
                    <span class="nav-text">Verifikasi</span>
                </a>
            </div>
        </div>

        {{-- Verifikasi & Validasi --}}
        <a class="nav-link @if (request()->is('verifikasi-validasi*')) active @endif" href="#"
            @if (request()->is('verifikasi-validasi*')) aria-current="page" @endif>
            <span class="nav-icon"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
            <span class="nav-text">Verifikasi & Validasi</span>
        </a>

        {{-- Laporan --}}
        <a class="nav-link @if (request()->is('laporan*')) active @endif" href="#"
            @if (request()->is('laporan*')) aria-current="page" @endif>
            <span class="nav-icon"><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i></span>
            <span class="nav-text">Laporan</span>
        </a>

        {{-- Master Data (dropdown) --}}
        <a class="nav-link nav-link-toggle @if (request()->is('master*')) active @endif" href="#groupMaster"
            data-bs-toggle="collapse" data-bs-parent="#sidebarNav" role="button"
            aria-expanded="{{ request()->is('master*') ? 'true' : 'false' }}" aria-controls="groupMaster">
            <span class="nav-icon"><i class="bi bi-database" aria-hidden="true"></i></span>
            <span class="nav-text">Master Data</span>
            <span class="nav-caret"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
        </a>
        <div class="collapse nav-collapse-group @if (request()->is('master*')) show @endif" id="groupMaster">
            <div class="nav-submenu">
                <a class="nav-link nav-sublink @if (request()->is('master/wilayah*')) active @endif"
                    href="{{ route('master.wilayah.index') }}"
                    @if (request()->is('master/wilayah*')) aria-current="page" @endif>
                    <span class="nav-text">Wilayah</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('master/petugas*')) active @endif"
                    href="{{ route('master.petugas.index') }}"
                    @if (request()->is('master/petugas*')) aria-current="page" @endif>
                    <span class="nav-text">Petugas</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('master/referensi*')) active @endif"
                    href="{{ route('master.referensi.index') }}"
                    @if (request()->is('master/referensi*')) aria-current="page" @endif>
                    <span class="nav-text">Referensi</span>
                </a>
            </div>
        </div>

        {{-- Administrasi (dropdown) --}}
        <a class="nav-link nav-link-toggle @if (request()->is('administrasi*')) active @endif"
            href="#groupAdministrasi" data-bs-toggle="collapse" data-bs-parent="#sidebarNav" role="button"
            aria-expanded="{{ request()->is('administrasi*') ? 'true' : 'false' }}" aria-controls="groupAdministrasi">
            <span class="nav-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
            <span class="nav-text">Administrasi</span>
            <span class="nav-caret"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
        </a>
        <div class="collapse nav-collapse-group @if (request()->is('administrasi*')) show @endif"
            id="groupAdministrasi">
            <div class="nav-submenu">
                <a class="nav-link nav-sublink @if (request()->is('administrasi/pengguna*')) active @endif" href="#"
                    @if (request()->is('administrasi/pengguna*')) aria-current="page" @endif>
                    <span class="nav-text">Pengguna</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('administrasi/role*')) active @endif" href="#"
                    @if (request()->is('administrasi/role*')) aria-current="page" @endif>
                    <span class="nav-text">Role</span>
                </a>
                <a class="nav-link nav-sublink @if (request()->is('administrasi/audit-log*')) active @endif" href="#"
                    @if (request()->is('administrasi/audit-log*')) aria-current="page" @endif>
                    <span class="nav-text">Audit Log</span>
                </a>
            </div>
        </div>

    </nav>

    <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar"
            src="{{ auth()->user()->avatar_url ?? asset('assets/images/avatar/avatar.jpg') }}"
            alt="{{ auth()->user()->nama }}">
        <strong>{{ auth()->user()->nama }}</strong>
        <small>Active Workspace</small>
    </div>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        <span class="sidebar-footer-text">System running smoothly</span>
    </div>
</aside>
