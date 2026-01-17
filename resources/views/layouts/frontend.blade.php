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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Unbounded:wght@200..900&display=swap" rel="stylesheet">
    <!-- Icofont -->
    <link rel="stylesheet" type="text/css" href="{{ asset('icofont/icofont.min.css') }}">
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
<link href="{{ asset('css/responsive.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --primary-purple: #8C52FF;
            --gradient-start: #8C52FF;
            --gradient-end: #C66BFF;
            --dark-navy: #1a1a40;
            --text-dark: #1C1C1C;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            background-color: #fcfcfc;
            color: #0f172a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Ensure header and footer styles are not overridden - Use high specificity */
        body .navbar,
        body nav.navbar,
        body .navbar.navbar-expand-lg {
            background: white !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05) !important;
            z-index: 1030 !important;
            padding: 10px 0 !important;
        }
        /* body footer,
        body footer#contact,
        body footer.main-footer { */
            /* background-color: #6B3FA0 !important; */
            /* color: white !important;
            padding-top: 80px !important;
            padding-bottom: 20px !important;
            position: relative !important;
            margin-top: auto !important;
            width: 100% !important;
            clear: both !important;
        } */

        /* --- Utility Classes --- */
        .fw-800 { font-weight: 800; }
        .text-gradient-purple {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .bg-gradient-purple {
            background: linear-gradient(90deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            color: white;
            border: none;
        }
        .btn-custom {
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            transition: transform 0.2s;
        }
        .btn-custom:hover { transform: translateY(-2px); color: white; }

        /* --- Navbar --- */
        .navbar,
        nav.navbar,
        .navbar.navbar-expand-lg {
            background: white !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05) !important;
            z-index: 1030 !important;
            padding: 10px 0 !important;
        }

        .navbar.sticky-top {
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar-brand,
        body .navbar-brand,
        nav.navbar .navbar-brand {
            display: flex !important;
            align-items: center !important;
        }

        .navbar-brand span,
        body .navbar-brand span {
            line-height: 1.1 !important;
            display: inline-block !important;
            color: #0d2e5c !important;
            font-weight: 700 !important;
            font-size: 1.25rem !important;
        }

        .navbar-brand img,
        body .navbar-brand img {
            max-height: 65px !important;
            width: auto !important;
        }

        .navbar-toggler {
            border: 1px solid rgba(0,0,0,0.1);
            padding: 4px 8px;
            border-radius: 4px;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(140, 82, 255, 0.25);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2833, 37, 41, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            width: 1.5em;
            height: 1.5em;
        }

        .nav-link,
        body .nav-link,
        nav.navbar .nav-link {
            color: #333 !important;
            font-weight: 500 !important;
            margin: 0 12px !important;
            font-size: 0.95rem !important;
            transition: all 0.3s !important;
        }
        .nav-link.active,
        body .nav-link.active { 
            font-weight: 700 !important; 
            border-bottom: 2px solid #333 !important; 
            color: var(--primary-purple) !important;
        }
        .nav-link:hover,
        body .nav-link:hover { 
            color: var(--primary-purple) !important; 
        }

        @media (max-width: 991px) {
            .navbar-collapse {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid rgba(0,0,0,0.1);
            }
            .nav-link {
                margin: 5px 0;
                padding: 8px 0;
            }
            .navbar .d-none.d-lg-block {
                display: block !important;
                margin-top: 10px;
            }
        }

        

        /* --- Footer --- */
        /* footer,
        footer#contact,
        footer.main-footer { */
            /* background-color: #6B3FA0 !important; */
            /* color: white !important;
            padding-top: 80px !important;
            padding-bottom: 20px !important;
            position: relative !important;
            margin-top: auto !important;
            width: 100% !important;
            clear: both !important;
        } */
        /* .footer-grid-overlay,
        body footer .footer-grid-overlay {
            position: absolute !important;
            right: 0 !important;
            top: 0 !important;
            width: 40% !important;
            height: 100% !important;
            background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px) !important;
            background-size: 20px 20px !important;
            opacity: 0.3 !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }
        footer .container,
        body footer .container {
            position: relative !important;
            z-index: 2 !important;
        }
        footer .container.position-relative,
        body footer .container.position-relative {
            position: relative !important;
        }

        footer .footer-logo,
        body footer .footer-logo { 
            margin-bottom: 20px !important; 
        }
        footer .footer-logo img,
        body footer .footer-logo img {
            max-width: 100% !important;
            height: auto !important;
        }
        footer .footer-logo-text,
        body footer .footer-logo-text {
            color: white !important;
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            margin-bottom: 5px !important;
        }
        footer .footer-logo-tagline,
        body footer .footer-logo-tagline {
            color: rgba(255, 255, 255, 0.7) !important;
            font-size: 0.7rem !important;
            margin-top: -5px !important;
        }
        footer .footer-social,
        body footer .footer-social {
            margin-top: 15px !important;
        }
        footer .footer-social a,
        body footer .footer-social a {
            display: inline-flex !important;
            width: 32px !important;
            height: 32px !important;
            background: rgba(255,255,255,0.1) !important;
            border-radius: 50% !important;
            color: white !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.8rem !important;
            margin-right: 8px !important;
            transition: 0.3s !important;
            text-decoration: none !important;
        }
        footer .footer-social a:hover,
        body footer .footer-social a:hover { 
            background: var(--primary-purple) !important; 
            color: white !important;
        }

        footer .footer-heading,
        body footer .footer-heading { 
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            margin-bottom: 20px !important;
            letter-spacing: 1px !important;
            color: white !important;
        }
        footer .footer-list,
        body footer .footer-list { 
            list-style: none !important;
            padding: 0 !important;
            font-size: 0.85rem !important;
            margin: 0 !important;
        }
        footer .footer-list li,
        body footer .footer-list li { 
            margin-bottom: 10px !important;
        }
        footer .footer-list a,
        body footer .footer-list a { 
            color: #b0b0cc !important;
            text-decoration: none !important;
            transition: 0.3s !important;
        }
        footer .footer-list a:hover,
        body footer .footer-list a:hover { 
            color: white !important;
            padding-left: 5px !important;
        }

        footer .contact-row,
        body footer .contact-row { 
            display: flex !important;
            gap: 15px !important;
            margin-bottom: 15px !important;
            align-items: flex-start !important;
        }
        footer .contact-icon,
        body footer .contact-icon {
            width: 35px !important;
            height: 35px !important;
            background: rgba(255,255,255,0.05) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.8rem !important;
            flex-shrink: 0 !important;
        }
        footer .contact-row .small,
        body footer .contact-row .small {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        footer .copyright-area,
        body footer .copyright-area {
            border-top: 1px solid rgba(255,255,255,0.1) !important;
            margin-top: 50px !important;
            padding-top: 20px !important;
            font-size: 0.75rem !important;
            color: #889 !important;
        }
        footer .copyright-area .row,
        body footer .copyright-area .row {
            margin: 0 !important;
        } */

        /* --- Footer --- */
footer {
    background-color: #6B3FA0;
    color: white;
    padding-top: 80px;
    padding-bottom: 20px;
    position: relative;
}
.footer-grid-overlay {
    position: absolute;
    right: 0;
    top: 0;
    width: 40%;
    height: 100%;
    background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 20px 20px;
    opacity: 0.3;
    pointer-events: none;
}

.footer-logo { margin-bottom: 20px; }
.footer-logo-text {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 5px;
}
.footer-logo-tagline {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.7rem;
    margin-top: -5px;
}
.footer-social a {
    display: inline-flex;
    width: 32px; height: 32px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    color: white;
    align-items: center; justify-content: center;
    font-size: 0.8rem;
    margin-right: 8px;
    transition: 0.3s;
    text-decoration: none;
}
.footer-social a:hover { background: var(--primary-purple); }

.footer-heading { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 1px; }
.footer-list { list-style: none; padding: 0; font-size: 0.85rem; }
.footer-list li { margin-bottom: 10px; }
.footer-list a { color: #b0b0cc; text-decoration: none; transition: 0.3s; }
.footer-list a:hover { color: white; padding-left: 5px; }

.contact-row { display: flex; gap: 15px; margin-bottom: 15px; }
.contact-icon {
    width: 35px; height: 35px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem;
}

.copyright-area {
    border-top: 1px solid rgba(255,255,255,0.1);
    margin-top: 50px;
    padding-top: 20px;
    font-size: 0.75rem;
    color: #889;
}

        /* Footer responsive styles */
        @media (max-width: 768px) {
            footer,
            body footer {
                padding-top: 60px !important;
                padding-bottom: 15px !important;
            }
            footer .footer-grid-overlay,
            body footer .footer-grid-overlay {
                width: 50% !important;
            }
            footer .footer-logo,
            body footer .footer-logo {
                text-align: center !important;
                margin-bottom: 30px !important;
            }
            footer .footer-social,
            body footer .footer-social {
                text-align: center !important;
                margin-top: 20px !important;
            }
            footer .copyright-area,
            body footer .copyright-area {
                margin-top: 30px !important;
                text-align: center !important;
            }
            footer .copyright-area .row > div,
            body footer .copyright-area .row > div {
                text-align: center !important;
                margin-bottom: 10px !important;
            }
        }

        .alert {
            margin-bottom: 0;
            border-radius: 10px;
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
    @include('partials.frontend-header')

    <main style="flex: 1;">
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

    @include('partials.frontend-footer')

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
                
                // Exclude filter inputs (date_from, date_to in filter contexts)
                if (inputId === 'date_from' || inputId === 'date_to' || 
                    inputName === 'date_from' || inputName === 'date_to') {
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
    @stack('scripts')
</body>
</html>

