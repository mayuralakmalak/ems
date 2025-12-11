<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Exhibitor Panel') - {{ config('app.name', 'EMS') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --gradient-start: #6366f1;
            --gradient-end: #8b5cf6;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            color: white;
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
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
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid white;
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
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
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-content">
                <div class="sidebar-brand-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-brand-info">
                    <div class="sidebar-brand-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-brand-role">Exhibitor Manager</div>
                </div>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('bookings.index') }}" class="{{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i>My Bookings
                </a>
            </li>
            <li>
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') || request()->routeIs('exhibitions.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-plus"></i>Book New Stall
                </a>
            </li>
            <li>
                <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i>My Payments
                </a>
            </li>
            <li>
                <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>Documents
                </a>
            </li>
            <li>
                <a href="{{ route('badges.index') }}" class="{{ request()->routeIs('badges.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>Badge Management
                </a>
            </li>
            <li>
                <a href="#" class="">
                    <i class="bi bi-star"></i>Additional Services
                </a>
            </li>
            <li>
                <a href="#" class="">
                    <i class="bi bi-trophy"></i>Sponsorship
                </a>
            </li>
            <li>
                <a href="#" class="">
                    <i class="bi bi-gear"></i>Settings
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
                <small class="text-muted">Welcome, {{ auth()->user()->name }}</small>
            </div>
            <div class="top-bar-user">
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
                <a href="{{ route('messages.index') }}" class="message-icon text-decoration-none">
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
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // Load notifications
        function loadNotifications() {
            $.ajax({
                url: '{{ route("notifications.index") }}',
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
                                <li class="px-3 py-2 ${isRead}" data-id="${notif.id}" style="cursor: pointer;">
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
                        
                        // Click to mark as read
                        list.find('li[data-id]').on('click', function() {
                            const id = $(this).data('id');
                            markAsRead(id);
                        });
                    }
                }
            });
        }
        
        function markAsRead(id) {
            $.ajax({
                url: '{{ route("notifications.read", ":id") }}'.replace(':id', id),
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
                url: '{{ route("notifications.read-all") }}',
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
    @stack('scripts')
</body>
</html>

