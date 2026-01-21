<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'EMS') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --dark-color: #1e293b;
            --light-bg: #f1f5f9;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 20px 0;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        /* Custom scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
        
        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-brand-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-brand-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
        }
        
        .sidebar-brand-info {
            flex: 1;
            min-width: 0;
        }
        
        .sidebar-brand-name {
            margin: 0;
            font-weight: 600;
            font-size: 15px;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-brand-role {
            margin: 0;
            font-size: 12px;
            color: rgba(255,255,255,0.8);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 5px 0;
        }
        
        .sidebar-menu a,
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
            margin: 0;
        }
        
        .sidebar-menu a i,
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            font-size: 1.1rem;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active,
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid white;
        }
        
        .sidebar .nav-link {
            border-radius: 0;
        }
        
        .sidebar-menu a .badge,
        .sidebar .nav-link .badge {
            margin-left: auto;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        }
        
        .top-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-bar-user {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-direction: row;
        }
        
        .top-bar-user .dropdown,
        .top-bar-user .message-icon,
        .top-bar-user button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }
        
        .top-bar-user .message-icon {
            color: #6366f1;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }
        
        .top-bar-user .message-icon:hover {
            color: #4f46e5;
            transform: scale(1.1);
        }
        
        .top-bar-user .btn-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            padding: 0;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        .top-navbar {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-radius: 0 0 1rem 1rem;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            /* transform: translateY(-2px); */
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
            border-radius: 0.5rem;
            padding: 0.625rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
        }
        
        .table {
            background: #fff;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        .table thead {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
        }
        
        .table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .stat-card.primary { border-left-color: #6366f1; }
        .stat-card.success { border-left-color: #10b981; }
        .stat-card.warning { border-left-color: #f59e0b; }
        .stat-card.info { border-left-color: #06b6d4; }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }
        
        .alert {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Reduce padding on edit buttons to match other buttons */
        .btn-primary[title="Edit"],
        .btn-primary[title*="Edit"] {
            padding: 0.25rem 0.5rem !important;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-content">
                @php
                    $generalSettings = \App\Models\Setting::getByGroup('general');
                    $companyLogo = $generalSettings['company_logo'] ?? null;
                @endphp
                @if($companyLogo && \Storage::disk('public')->exists($companyLogo))
                    <img src="{{ \Storage::url($companyLogo) }}" alt="Company Logo" style="width: 45px; height: 45px; object-fit: contain; border-radius: 8px; background: rgba(255,255,255,0.1); padding: 5px;">
                @else
                    <div class="sidebar-brand-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="sidebar-brand-info">
                    <div class="sidebar-brand-name">{{ $generalSettings['company_name'] ?? (auth()->user()->name) }}</div>
                    <div class="sidebar-brand-role">{{ auth()->user()->roles->first()->name ?? 'Admin' }}</div>
                </div>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('admin.exhibitions.index') }}" class="{{ request()->routeIs('admin.exhibitions.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i>Exhibitions
                </a>
            </li>
            <li>
                <a href="{{ route('admin.services.config') }}" class="{{ request()->routeIs('admin.services.config*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i>Service Configuration
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i>Settings
                </a>
            </li>
            <li>
                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>Categories
                </a>
            </li>
            <li>
                <a href="{{ route('admin.size-types.index') }}" class="{{ request()->routeIs('admin.size-types.*') ? 'active' : '' }}">
                    <i class="bi bi-rulers"></i>Size Type
                </a>
            </li>
            <li>
                <a href="{{ route('admin.discounts.index') }}" class="{{ request()->routeIs('admin.discounts.*') ? 'active' : '' }}">
                    <i class="bi bi-percent"></i>Discounts
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>Users
                </a>
            </li>
            <li>
                <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i>Roles & Permissions
                </a>
            </li>
            <li>
                <a href="{{ route('admin.exhibitors.index') }}" class="{{ request()->routeIs('admin.exhibitors.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>Exhibitors
                </a>
            </li>
            <li>
                <a href="{{ route('admin.financial.index') }}" class="{{ request()->routeIs('admin.financial.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i>Financial
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i>Reports
                </a>
            </li>
            <li>
                <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i>Bookings
                </a>
            </li>
            <li>
                <a href="{{ route('admin.bookings.cancellations') }}" class="{{ request()->routeIs('admin.bookings.cancellations') ? 'active' : '' }}">
                    <i class="bi bi-x-octagon"></i>Cancellations
                </a>
            </li>
            <li>
                <a href="{{ route('admin.sponsorships.index') }}" class="{{ request()->routeIs('admin.sponsorships.*') ? 'active' : '' }}">
                    <i class="bi bi-trophy"></i>Sponsorships
                </a>
            </li>
            <li>
                <a href="{{ route('admin.sponsorship-bookings.index') }}" class="{{ request()->routeIs('admin.sponsorship-bookings.*') ? 'active' : '' }}">
                    <i class="bi bi-trophy-fill"></i>Sponsorship Bookings
                    @php
                        $pendingSponsorshipBookings = \App\Models\SponsorshipBooking::where('approval_status', 'pending')->count();
                    @endphp
                    @if($pendingSponsorshipBookings > 0)
                        <span class="badge bg-danger ms-2">{{ $pendingSponsorshipBookings }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.index') || (request()->routeIs('admin.payments.*') && !request()->routeIs('admin.payments.history')) ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i>Payment Approvals
                    @php
                        $pendingPayments = \App\Models\Payment::where('approval_status', 'pending')->count();
                    @endphp
                    @if($pendingPayments > 0)
                        <span class="badge bg-danger ms-2">{{ $pendingPayments }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.payments.history') }}" class="{{ request()->routeIs('admin.payments.history') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>Payment History
                </a>
            </li>
            <li>
                <a href="{{ route('admin.booth-requests.index') }}" class="{{ request()->routeIs('admin.booth-requests.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>Booth Requests
                    @php
                        $pendingCount = \App\Models\BoothRequest::where('status', 'pending')->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="badge bg-danger ms-2">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.additional-service-requests.index') }}" class="{{ request()->routeIs('admin.additional-service-requests.*') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>Additional Service Requests
                    @php
                        $pendingAdditionalServiceRequests = \App\Models\AdditionalServiceRequest::where('status', 'pending')->count();
                    @endphp
                    @if($pendingAdditionalServiceRequests > 0)
                        <span class="badge bg-danger ms-2">{{ $pendingAdditionalServiceRequests }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.documents.index') }}" class="{{ request()->routeIs('admin.documents.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-check"></i>Document Verification
                </a>
            </li>
            <li>
                <a href="{{ route('admin.communications.index') }}" class="{{ request()->routeIs('admin.communications.*') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots"></i>Community Center
                </a>
            </li>
            @if(request()->routeIs('admin.booths.*'))
            <li>
                <a href="#" class="active">
                    <i class="bi bi-grid-3x3-gap"></i>Booths
                </a>
            </li>
            @endif
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <div class="top-bar">
            <div>
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
                <small class="text-muted">Welcome, {{ auth()->user()->name }}</small>
            </div>
            <div class="top-bar-user">
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-link position-relative p-0" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; color: #6366f1; border: none; background: none;">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none;">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <div id="notificationList">
                            <li class="px-3 py-2 text-muted text-center">Loading...</li>
                        </div>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#" id="markAllReadBtn">Mark all as read</a></li>
                    </ul>
                </div>
                <!-- New Chat (Admin) -->
                <a href="{{ route('admin.communications.create') }}" class="message-icon text-decoration-none" title="Start new chat with exhibitor">
                    <i class="bi bi-envelope"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link p-0" style="width: 40px; height: 40px; border-radius: 50%; background: #ef4444; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: all 0.3s ease; margin-left: 10px;" title="Logout" onmouseover="this.style.background='#dc2626'; this.style.transform='scale(1.1)'" onmouseout="this.style.background='#ef4444'; this.style.transform='scale(1)'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.36 6.64C19.6184 7.89879 20.4753 9.50244 20.8223 11.2482C21.1693 12.9939 20.9909 14.8034 20.3076 16.4478C19.6244 18.0921 18.4658 19.4976 16.9677 20.4864C15.4697 21.4752 13.6939 22.0029 11.88 22.0029C10.0661 22.0029 8.29026 21.4752 6.79219 20.4864C5.29412 19.4976 4.13554 18.0921 3.45231 16.4478C2.76908 14.8034 2.59066 12.9939 2.93768 11.2482C3.28469 9.50244 4.14159 7.89879 5.4 6.64" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 2V12" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php
        // Skip country/state preload to avoid dependency on those tables
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
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // Load notifications
        function loadNotifications() {
            $.ajax({
                url: '{{ route("admin.notifications.index") }}',
                method: 'GET',
                success: function(response) {
                    const badge = $('#notificationBadge');
                    const list = $('#notificationList');
                    
                    if (response.unreadCount > 0) {
                        badge.text(response.unreadCount).show();
                    } else {
                        badge.hide();
                    }
                    
                    if (response.notifications.length === 0) {
                        list.html('<li class="px-3 py-2 text-muted text-center">No notifications</li>');
                    } else {
                        let html = '';
                        response.notifications.forEach(function(notif) {
                            const isRead = notif.is_read ? '' : 'bg-light';
                            const timeAgo = new Date(notif.created_at).toLocaleString();
                            html += `
                                <li class="px-3 py-2 ${isRead}" data-id="${notif.id}" data-type="${notif.type || ''}" style="cursor: pointer;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <strong>${notif.title}</strong>
                                            <p class="mb-0 small text-muted">${notif.message}</p>
                                            <small class="text-muted">${timeAgo}</small>
                                        </div>
                                        ${!notif.is_read ? '<span class="badge bg-primary ms-2">New</span>' : ''}
                                    </div>
                                </li>
                            `;
                        });
                        list.html(html);
                        
                        // Click to mark as read and navigate if message notification
                        list.find('li[data-id]').on('click', function() {
                            const id = $(this).data('id');
                            const type = $(this).data('type');
                            
                            markAsRead(id);
                            
                            // If it's a message notification, navigate to community center
                            if (type === 'message') {
                                window.location.href = `{{ route('admin.communications.index') }}`;
                            }
                        });
                    }
                }
            });
        }
        
        function markAsRead(id) {
            $.ajax({
                url: '{{ route("admin.notifications.read", ":id") }}'.replace(':id', id),
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    loadNotifications();
                }
            });
        }
        
        $('#markAllReadBtn').on('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route("admin.notifications.read-all") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    loadNotifications();
                }
            });
        });
        
        // Load notifications on page load
        loadNotifications();
        
        // Refresh every 30 seconds
        setInterval(loadNotifications, 30000);
    </script>
    <script>
        // Global datepicker and timepicker: Make all date/time inputs open picker on click
        document.addEventListener('DOMContentLoaded', function() {
            // Function to handle date/time input click
            function setupDateTimeInput(input) {
                if (input && (input.type === 'date' || input.type === 'time')) {
                    input.addEventListener('click', function() {
                        // Try showPicker() method (modern browsers)
                        if (typeof this.showPicker === 'function') {
                            this.showPicker();
                        } else {
                            // Fallback: focus and click to open picker
                            this.focus();
                            this.click();
                        }
                    });
                }
            }
            
            // Setup all existing date and time inputs
            document.querySelectorAll('input[type="date"], input[type="time"]').forEach(setupDateTimeInput);
            
            // Watch for dynamically added date/time inputs (MutationObserver)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.type === 'date' || node.type === 'time') {
                                setupDateTimeInput(node);
                            }
                            // Also check children
                            node.querySelectorAll && node.querySelectorAll('input[type="date"], input[type="time"]').forEach(setupDateTimeInput);
                        }
                    });
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>
    <script>
        // Disable past dates for all date inputs (except filters)
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            
            function setMinDateForInput(input) {
                // Skip filter inputs - check by ID or name
                const inputId = input.id || '';
                const inputName = input.name || '';
                
                // Exclude filter inputs (date_from, date_to in filter contexts, analytics filters)
                if (inputId === 'date_from' || inputId === 'date_to' || 
                    inputName === 'date_from' || inputName === 'date_to' ||
                    (inputName === 'start_date' && input.closest('form')?.action?.includes('analytics')) ||
                    (inputName === 'end_date' && input.closest('form')?.action?.includes('analytics'))) {
                    return;
                }
                
                // Set min to today for all other date inputs
                if (input.type === 'date' && !input.hasAttribute('min')) {
                    input.setAttribute('min', today);
                }
            }
            
            // Set min date for all existing date inputs
            document.querySelectorAll('input[type="date"]').forEach(setMinDateForInput);
            
            // Watch for dynamically added date inputs
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.type === 'date') {
                                setMinDateForInput(node);
                            }
                            // Also check children
                            if (node.querySelectorAll) {
                                node.querySelectorAll('input[type="date"]').forEach(setMinDateForInput);
                            }
                        }
                    });
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>
    <script>
        // Scroll sidebar to show active menu item on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const activeMenuItem = sidebar?.querySelector('.sidebar-menu a.active');
            
            if (sidebar && activeMenuItem) {
                // Calculate the position to scroll to (center the active item in viewport)
                const sidebarRect = sidebar.getBoundingClientRect();
                const activeItemRect = activeMenuItem.getBoundingClientRect();
                const sidebarScrollTop = sidebar.scrollTop;
                const activeItemOffsetTop = activeItemRect.top - sidebarRect.top + sidebarScrollTop;
                const sidebarHeight = sidebar.clientHeight;
                const activeItemHeight = activeItemRect.height;
                
                // Scroll to center the active item in the sidebar viewport
                const scrollPosition = activeItemOffsetTop - (sidebarHeight / 2) + (activeItemHeight / 2);
                
                sidebar.scrollTo({
                    top: Math.max(0, scrollPosition),
                    behavior: 'smooth'
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
