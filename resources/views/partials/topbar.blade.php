<nav class="navbar admin-navbar navbar-expand bg-white">
    <div class="container-fluid px-3 px-lg-4">
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true"
            aria-label="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
        </button>



        <div class="navbar-actions ms-auto">
            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme"
                title="Switch color theme">
                <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
                <button class="icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                    aria-label="Notifications">
                    @if (($unreadNotificationsCount ?? 0) > 0)
                        <span class="notification-dot"></span>
                    @endif
                    <i class="bi bi-bell" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-menu">
                    <div class="dropdown-header fw-bold text-body">Notifications</div>

                    @forelse ($notifications ?? [] as $notification)
                        <a class="dropdown-item" href="{{ $notification['url'] ?? '#' }}">
                            <span class="notification-title">{{ $notification['title'] }}</span>
                            <span class="notification-time">{{ $notification['time'] }}</span>
                        </a>
                    @empty
                        <a class="dropdown-item" href="{{ url('/users') }}">
                            <span class="notification-title">New user registered</span>
                            <span class="notification-time">4 minutes ago</span>
                        </a>
                        <a class="dropdown-item" href="{{ url('/charts') }}">
                            <span class="notification-title">Revenue target reached</span>
                            <span class="notification-time">32 minutes ago</span>
                        </a>
                        <a class="dropdown-item" href="{{ url('/settings') }}">
                            <span class="notification-title">Security review completed</span>
                            <span class="notification-time">1 hour ago</span>
                        </a>
                    @endforelse
                </div>
            </div>

            <div class="dropdown">
                <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <img class="avatar-img avatar-sm"
                        src="{{ auth()->user()->avatar_url ?? asset('assets/images/avatar/avatar.jpg') }}"
                        alt="{{ auth()->user()->name }}">
                    <span class="profile-name d-none d-sm-inline">{{ auth()->user()->nama }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ url('/profile') }}">Profile</a></li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Sign out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
