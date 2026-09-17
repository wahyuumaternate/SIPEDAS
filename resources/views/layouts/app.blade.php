<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="adminHMD professional admin dashboard template">
    <title>@yield('title', 'Dashboard') | SIPENTAS</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo_kota.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        /* ===== Dropdown toggle & caret ===== */
        /* .nav-link-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        } */

        .nav-link-toggle .nav-caret i {
            font-size: 0.75rem;
            color: #94a3b8;
            transition: transform 0.25s ease, color 0.25s ease;
        }

        .nav-link-toggle:hover .nav-caret i {
            color: var(--bs-primary, #4b7bec);
        }

        .nav-link-toggle[aria-expanded="true"] .nav-caret i {
            transform: rotate(180deg);
            color: var(--bs-primary, #4b7bec);
        }

        .nav-link-toggle.active {
            background: rgba(75, 123, 236, 0.08);
            border-radius: 8px;
        }

        /* ===== Submenu container ===== */
        .nav-collapse-group {
            transition: height 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-submenu {
            position: relative;
            margin: 2px 0 6px 0;
            padding-left: 28px;
        }

        /* garis vertikal tree-view di sisi kiri submenu */
        .nav-submenu::before {
            content: "";
            position: absolute;
            left: 14px;
            top: 2px;
            bottom: 10px;
            width: 1px;
            background: #e2e8f0;
        }

        /* ===== Sublinks ===== */
        .nav-submenu .nav-sublink {
            position: relative;
            display: flex;
            align-items: center;
            padding: 8px 12px 8px 16px;
            margin-bottom: 2px;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #64748b;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease, padding-left 0.2s ease;
        }

        /* konektor horizontal kecil dari garis vertikal ke setiap item */
        .nav-submenu .nav-sublink::after {
            content: "";
            position: absolute;
            left: -14px;
            top: 50%;
            width: 10px;
            height: 1px;
            background: #e2e8f0;
        }

        .nav-submenu .nav-sublink:hover {
            background: #f1f5f9;
            color: #334155;
            padding-left: 20px;
        }

        .nav-submenu .nav-sublink.active {
            background: rgba(75, 123, 236, 0.1);
            color: var(--bs-primary, #4b7bec);
            font-weight: 600;
        }

        .nav-submenu .nav-sublink.active::before {
            content: "";
            position: absolute;
            left: -18px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--bs-primary, #4b7bec);
            border-radius: 2px;
        }

        /* titik penanda di setiap sublink, aktif diperbesar */
        .nav-submenu .nav-sublink::before {
            content: "";
            position: absolute;
            left: -18px;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #cbd5e1;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .nav-submenu .nav-sublink.active::before {
            background: var(--bs-primary, #4b7bec);
            transform: scale(1.4);
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="admin-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>

        @include('partials.sidebar')

        <div class="admin-main">
            @include('partials.topbar')

            <main class="dashboard-content">
                <div class="container-fluid px-3 px-lg-4 py-4">
                    @yield('content')
                </div>
            </main>

            @include('partials.footer')
        </div>
    </div>

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('scripts')
</body>

</html>
