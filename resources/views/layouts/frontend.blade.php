<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Exhibition Management System')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8fafc;
            color: #0f172a;
        }
        .app-header {
            background: linear-gradient(135deg, #0f172a 0%, #111827 50%, #0b1220 100%);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.25);
        }
        .app-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #e2e8f0 !important;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: -0.01em;
        }
        .app-brand-badge {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
        }
        .app-nav .nav-link {
            color: #cbd5e1 !important;
            font-weight: 600;
            padding: 10px 14px !important;
            border-radius: 10px;
            transition: all 0.15s ease;
        }
        .app-nav .nav-link:hover,
        .app-nav .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.08);
        }
        .app-footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 22px 0;
            margin-top: 40px;
            box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.18);
        }
        .alert {
            margin-bottom: 0;
            border-radius: 10px;
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg app-header">
        <div class="container py-2">
            <a class="app-brand" href="{{ route('home') }}">
                @php
                    $generalSettings = \App\Models\Setting::getByGroup('general');
                    $companyLogo = $generalSettings['company_logo'] ?? null;
                @endphp
                @if($companyLogo && \Storage::disk('public')->exists($companyLogo))
                    <img src="{{ \Storage::url($companyLogo) }}" alt="Company Logo" style="width: 36px; height: 36px; object-fit: contain; border-radius: 12px; margin-right: 8px;">
                @else
                    <span class="app-brand-badge"><i class="bi bi-calendar-event"></i></span>
                @endif
                <span>{{ $generalSettings['company_name'] ?? 'EMS' }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto app-nav ms-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('exhibitions.*') ? 'active' : '' }}" href="{{ route('exhibitions.list') }}">Exhibitions</a>
                    </li>
                </ul>
                <ul class="navbar-nav app-nav">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="app-footer">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} Exhibition Management System. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php
        // Skip country/state preload to avoid DB dependency on those tables
        $countryStateData = [];
    @endphp
    <script>
        window.countryStateData = @json($countryStateData);
    </script>
    <script src="{{ asset('js/country-state.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof applyCountryState === 'function') {
                applyCountryState();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

