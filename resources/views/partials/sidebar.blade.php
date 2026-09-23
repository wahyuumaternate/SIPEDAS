<aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
    <div class="sidebar-header">
        <a class="brand-mark" href="{{ url('/') }}" aria-label="SIPENTAS dashboard">
            <img src="{{ asset('logo_kota.png') }}" width="40" alt="">
            <span class="brand-copy">
                <span class="brand-title">SIPENTAS</span>
                <span class="brand-subtitle" title="Sistem Informasi Pendataan Kemiskinan Ekstrem dan Stunting">Sistem Informasi Pendataan Kemiskinan Ekstrem dan Stunting</span>
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

        @php
            $bisaLihatKemiskinan = auth()->user()->hasPermission(\App\Permission::KemiskinanView);
            $bisaBuatKemiskinan = auth()->user()->hasPermission(\App\Permission::KemiskinanCreate);
            $bisaLihatStunting = auth()->user()->hasPermission(\App\Permission::StuntingView);
            $bisaBuatStunting = auth()->user()->hasPermission(\App\Permission::StuntingCreate);
            $bisaKelolaMaster = auth()->user()->hasPermission(\App\Permission::MasterManage);
            $bisaKelolaUser = auth()->user()->hasPermission(\App\Permission::UserManage);
            $bisaLihatAuditLog = auth()->user()->hasPermission(\App\Permission::AuditLogView);
        @endphp

        {{-- Pendataan Kemiskinan Ekstrem (dropdown) --}}
        @if ($bisaLihatKemiskinan)
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
                    @if ($bisaBuatKemiskinan)
                        <a class="nav-link nav-sublink @if (request()->is('kemiskinan/create*')) active @endif" href="{{ route('kemiskinan.create') }}"
                            @if (request()->is('kemiskinan/create*')) aria-current="page" @endif>
                            <span class="nav-text">Pendataan Baru</span>
                        </a>
                    @endif
                    <a class="nav-link nav-sublink @if (request()->is('kemiskinan/verifikasi*')) active @endif" href="{{ route('kemiskinan.verifikasi.index') }}"
                        @if (request()->is('kemiskinan/verifikasi*')) aria-current="page" @endif>
                        <span class="nav-text">Verifikasi</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Pendataan Stunting (dropdown) --}}
        @if ($bisaLihatStunting)
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
                    @if ($bisaBuatStunting)
                        <a class="nav-link nav-sublink @if (request()->is('stunting/create*')) active @endif" href="{{ route('stunting.create') }}"
                            @if (request()->is('stunting/create*')) aria-current="page" @endif>
                            <span class="nav-text">Pendataan Baru</span>
                        </a>
                    @endif
                    <a class="nav-link nav-sublink @if (request()->is('stunting/verifikasi*')) active @endif" href="{{ route('stunting.verifikasi.index') }}"
                        @if (request()->is('stunting/verifikasi*')) aria-current="page" @endif>
                        <span class="nav-text">Verifikasi</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Verifikasi & Validasi --}}
        @if (auth()->user()->hasPermission(\App\Permission::KemiskinanVerify) || auth()->user()->hasPermission(\App\Permission::StuntingVerify))
            <a class="nav-link @if (request()->is('verifikasi-validasi*')) active @endif" href="{{ route('verifikasi-validasi.index') }}"
                @if (request()->is('verifikasi-validasi*')) aria-current="page" @endif>
                <span class="nav-icon"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
                <span class="nav-text">Verifikasi & Validasi</span>
            </a>
        @endif

        {{-- Laporan --}}
        @if ($bisaLihatKemiskinan || $bisaLihatStunting)
            <a class="nav-link @if (request()->is('laporan*')) active @endif" href="{{ route('laporan.index') }}"
                @if (request()->is('laporan*')) aria-current="page" @endif>
                <span class="nav-icon"><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i></span>
                <span class="nav-text">Laporan</span>
            </a>
        @endif

        {{-- Master Data & Administrasi (dropdown gabungan) --}}
        @if ($bisaKelolaMaster || $bisaKelolaUser || $bisaLihatAuditLog)
            <a class="nav-link nav-link-toggle @if (request()->is('master*') || request()->is('audit-log*')) active @endif"
                href="#groupMaster" data-bs-toggle="collapse" data-bs-parent="#sidebarNav" role="button"
                aria-expanded="{{ request()->is('master*') || request()->is('audit-log*') ? 'true' : 'false' }}" aria-controls="groupMaster">
                <span class="nav-icon"><i class="bi bi-database" aria-hidden="true"></i></span>
                <span class="nav-text">Master Data</span>
                <span class="nav-caret"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
            </a>
            <div class="collapse nav-collapse-group @if (request()->is('master*') || request()->is('audit-log*')) show @endif" id="groupMaster">
                <div class="nav-submenu">
                    @if ($bisaKelolaMaster)
                        <a class="nav-link nav-sublink @if (request()->is('master/wilayah*')) active @endif"
                            href="{{ route('master.wilayah.index') }}"
                            @if (request()->is('master/wilayah*')) aria-current="page" @endif>
                            <span class="nav-text">Wilayah</span>
                        </a>
                    @endif
                    @if ($bisaKelolaUser)
                        <a class="nav-link nav-sublink @if (request()->is('master/petugas*')) active @endif"
                            href="{{ route('master.petugas.index') }}"
                            @if (request()->is('master/petugas*')) aria-current="page" @endif>
                            <span class="nav-text">Petugas</span>
                        </a>
                        <a class="nav-link nav-sublink @if (request()->is('master/role*')) active @endif"
                            href="{{ route('master.role.index') }}"
                            @if (request()->is('master/role*')) aria-current="page" @endif>
                            <span class="nav-text">Role</span>
                        </a>
                    @endif
                    @if ($bisaLihatAuditLog)
                        <a class="nav-link nav-sublink @if (request()->is('audit-log*')) active @endif"
                            href="{{ route('audit-log.index') }}"
                            @if (request()->is('audit-log*')) aria-current="page" @endif>
                            <span class="nav-text">Audit Log</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

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
