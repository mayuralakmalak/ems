<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', config('app.name', 'EMS')) - Exhibition Management System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Unbounded:wght@200..900&display=swap" rel="stylesheet">

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
}

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
.navbar {
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.navbar-brand span {
    line-height: 1.1;
    display: inline-block;
    color: #0d2e5c;
    font-weight: 700;
    font-size: 1.25rem;
}
.nav-link {
    color: #333;
    font-weight: 500;
    margin: 0 12px;
    font-size: 0.95rem;
}
.nav-link.active { font-weight: 700; }
.nav-link:hover { color: var(--primary-purple); }

/* --- Hero Section --- */
.hero-section {
    background: linear-gradient(rgba(140, 82, 255, 0.75), rgba(140, 82, 255, 0.75)), 
                url('https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1920&h=1000&fit=crop');
    background-size: cover;
    background-position: center;
    color: white;
    padding-top: 80px;
    padding-bottom: 100px;
    position: relative;
    overflow: hidden;
}

.hero-purple-banner {
    background: var(--primary-purple);
    padding: 50px 40px;
    border-radius: 15px;
    margin-top: 30px;
    position: relative;
}

.hero-logo-section {
    position: absolute;
    top: 20px;
    left: 20px;
}

.hero-logo-section .logo-text {
    color: white;
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0;
}

.hero-logo-section .logo-tagline {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.7rem;
    margin-top: -5px;
}

.hero-presents {
    position: absolute;
    bottom: -20px;
    left: 40px;
    font-size: 0.75rem;
    opacity: 0.8;
}

.countdown-box {
    background: rgba(255, 255, 255, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    backdrop-filter: blur(5px);
    padding: 15px 20px;
    min-width: 80px;
}
.count-num { font-size: 1.8rem; font-weight: 800; line-height: 1; }
.count-label { font-size: 0.7rem; text-transform: uppercase; margin-top: 5px; letter-spacing: 0.5px; }

.btn-white-hero {
    background: white;
    color: var(--primary-purple);
    font-weight: 700;
    border-radius: 6px;
    padding: 12px 30px;
    text-transform: uppercase;
    font-size: 0.85rem;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}
.btn-white-hero:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateY(-2px);
    color: var(--primary-purple);
}

/* --- Overlap Section (The Floating Card) --- */
.overlap-wrapper {
    position: relative;
    margin-top: 40px;
    z-index: 10;
    padding-bottom: 60px;
}

.shape-bg-left {
    position: absolute;
    left: 0;
    top: 40px;
    width: 150px;
    height: 80%;
    background: #d4a5ff;
    transform: skewY(-10deg);
    z-index: -1;
    opacity: 0.5;
}
.shape-bg-right {
    position: absolute;
    right: 0;
    top: 80px;
    width: 200px;
    height: 350px;
    background: linear-gradient(180deg, #8C52FF 0%, #6e3bcc 100%);
    transform: skewY(10deg);
    z-index: -1;
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;
}

.main-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.1);
    overflow: hidden;
}
.card-img {
    height: 100%;
    min-height: 450px;
    object-fit: cover;
}
.card-content { padding: 50px; }

.btn-outline-custom {
    border: 1px solid #ddd;
    color: var(--primary-purple);
    font-weight: 600;
    text-transform: uppercase;
    padding: 10px 25px;
    border-radius: 6px;
    font-size: 0.85rem;
    margin-left: 10px;
    text-decoration: none;
    display: inline-block;
}
.btn-outline-custom:hover { background: #f9f9f9; color: var(--gradient-end); }

/* --- Stats Section --- */
.stats-section { padding: 40px 0 60px 0; }
.section-header { text-align: center; margin-bottom: 50px; }
.section-header h2 { font-weight: 800; text-transform: uppercase; font-size: 2rem; }
.stat-item { text-align: center; margin-bottom: 30px; }
.stat-icon { font-size: 2.5rem; color: #a0a0a0; margin-bottom: 15px; }
.stat-number { font-size: 2.5rem; font-weight: 800; color: var(--primary-purple); margin: 10px 0; }
.stat-label { font-size: 0.9rem; color: #666; text-transform: uppercase; letter-spacing: 1px; }
.stat-divider { width: 40px; height: 4px; background: #222; margin: 10px auto 0 auto; }

/* --- Why Choose Section --- */
.features-section { padding: 60px 0 100px 0; }
.feature-icon-box {
    width: 50px; 
    height: 50px; 
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    color: var(--primary-purple);
    margin-bottom: 10px;
}
.feature-title { font-weight: 800; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 5px; }
.feature-desc { font-size: 0.8rem; color: #777; line-height: 1.5; }
.center-feature-img {
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    width: 100%;
    height: auto;
}

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

.hero-event-date, .hero-event-title, .hero-event-location {
    margin-bottom: 15px;
}

.hero-event-title {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1.2;
}

.hero-event-date img, .hero-event-location img {
    margin-right: 8px;
    width: 20px;
    height: 20px;
}

@media (max-width: 768px) {
    .hero-event-title {
        font-size: 1.8rem;
    }
    .card-content {
        padding: 30px 20px;
    }
}
</style>
<link href="{{ asset('css/main.css') }}" rel="stylesheet">
<link href="{{ asset('css/responsive.css') }}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="{{ asset('icofont/icofont.min.css') }}">
</head>
<body>

@include('partials.frontend-header')

@if($featuredExhibition)
<section class="hero-section">
    <div class="container-fluid position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6">
                @if(file_exists(public_path('images/ban-left-img.png')))
                <img src="{{ asset('images/ban-left-img.png') }}" alt="Hero Left" class="img-fluid">
                @endif
            </div>
            <div class="col-lg-6">
                <div class="hero-purple-banner clearfix position-relative">
                    <div class="hero-event-date">
                        @if(file_exists(public_path('images/ban-calender-icon.png')))
                        <img src="{{ asset('images/ban-calender-icon.png') }}" alt="Calendar">
                        @else
                        <i class="fa-solid fa-calendar"></i>
                        @endif
                        @if($featuredExhibition->start_date && $featuredExhibition->end_date)
                            {{ $featuredExhibition->start_date->format('d M Y') }} TO {{ $featuredExhibition->end_date->format('d M Y') }}
                        @else
                            Date TBA
                        @endif
                    </div>
                    <h1 class="hero-event-title">{{ $featuredExhibition->name ?? 'GLOBAL AI TECH EXPO 2026' }}</h1>
                    <div class="hero-event-location">
                        @if(file_exists(public_path('images/ban-location-icon.png')))
                        <img src="{{ asset('images/ban-location-icon.png') }}" alt="Location">
                        @else
                        <i class="fa-solid fa-location-dot"></i>
                        @endif
                        {{ $featuredExhibition->venue ?? '23rd Avenue, 4th Street, Chicago, Illinois, USA' }}
                        @if($featuredExhibition->city)
                            , {{ $featuredExhibition->city }}
                        @endif
                    </div>
                    
                    @php
                        $daysUntil = $featuredExhibition->start_date ? (int)now()->diffInDays($featuredExhibition->start_date) : 90;
                        $hoursUntil = $featuredExhibition->start_date ? (int)(now()->diffInHours($featuredExhibition->start_date) % 24) : 40;
                    @endphp
                    <div class="d-flex align-items-center mb-4 countdown">
                        <div class="countdown-box">
                            <span class="count-num">{{ $daysUntil }}</span>
                            <span class="count-label">DAYS</span>
                        </div>
                        <div class="countdown-box">
                            <span class="count-num">{{ $hoursUntil }}</span>
                            <span class="count-label">HRS</span>
                        </div>
                        <div class="ms-4">
                            @auth
                                <a href="{{ route('exhibitions.show', $featuredExhibition->id) }}" class="btn btn-white-hero">BOOK YOUR STALL</a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-white-hero">BOOK YOUR STALL</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if($latestExhibition)
<section class="overlap-wrapper global-texh">
    <div class="container">
        <div class="main-card">
            <div class="row g-0">
                <div class="col-lg-5">
                    @php
                        $floorplanImages = is_array($latestExhibition->floorplan_images ?? null)
                            ? $latestExhibition->floorplan_images
                            : (array) ($latestExhibition->floorplan_image ? [$latestExhibition->floorplan_image] : []);
                        $primaryFloorplanImage = $floorplanImages[0] ?? null;
                    @endphp
                    @if($primaryFloorplanImage && \Storage::disk('public')->exists($primaryFloorplanImage))
                        <img src="{{ asset('storage/' . $primaryFloorplanImage) }}" class="img-fluid card-img w-100" alt="{{ $latestExhibition->name }}">
                    @else
                        <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600&h=600&fit=crop" class="img-fluid card-img w-100" alt="Exhibition">
                    @endif
                </div>
                <div class="col-lg-7 card-content d-flex flex-column justify-content-center">
                    <h3 class="fw-bold mb-3" style="font-size: 2.5rem;">{{ $latestExhibition->name }}</h3>
                    <p class="text-muted mb-4">
                        {{ $latestExhibition->description ?? 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.' }}
                    </p>
                    
                    <div class="mb-4 text-muted event-detail">
                        @if($latestExhibition->start_date)
                        <div class="mb-2">
                            <i class="fa-solid fa-calendar me-2" style="color:var(--primary-purple)!important;"></i> 
                            {{ $latestExhibition->start_date->format('d M, Y') }}
                            @if($latestExhibition->start_time)
                                Time {{ $latestExhibition->start_time }}
                            @else
                                Time 9:30 AM
                            @endif
                        </div>
                        @endif
                        <div>
                            <i class="fa-solid fa-location-dot me-2" style="color:var(--primary-purple)!important;"></i> 
                            {{ $latestExhibition->venue ?? 'Convention center' }}
                            @if($latestExhibition->city)
                                , {{ $latestExhibition->city }}
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        @auth
                            <a href="{{ route('exhibitions.show', $latestExhibition->id) }}" class="btn btn-custom bg-gradient-purple">BOOK A STALL</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-custom bg-gradient-purple">BOOK A STALL</a>
                        @endauth
                        <a href="{{ route('exhibitions.show', $latestExhibition->id) }}" class="btn btn-outline-custom">AS A VISITOR</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<section class="stats-section exhibitflow-global pb-0">
    <div class="container">
        <div class="section-header">
            <h2>EXHIBITFLOW AT A <span>GLOBAL</span></h2>
            <p class="text-muted small">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 stat-item">
                <div class="stat-icon">
                    @if(file_exists(public_path('images/usp-icon1.png')))
                    <img src="{{ asset('images/usp-icon1.png') }}" alt="Exhibitors">
                    @else
                    <i class="fa-solid fa-users"></i>
                    @endif
                </div>
                <div class="stat-number">{{ $stats['exhibitors'] }}+</div>
                <div class="stat-label">EXHIBITORS JOINED</div>
            </div>
            <div class="col-md-4 stat-item">
                <div class="stat-icon">
                    @if(file_exists(public_path('images/usp-icon2.png')))
                    <img src="{{ asset('images/usp-icon2.png') }}" alt="Events">
                    @else
                    <i class="fa-solid fa-calendar-check"></i>
                    @endif
                </div>
                <div class="stat-number">{{ $stats['events'] }}+</div>
                <div class="stat-label">EVENT ORGANIZED</div>
            </div>
            <div class="col-md-4 stat-item">
                <div class="stat-icon">
                    @if(file_exists(public_path('images/usp-icon3.png')))
                    <img src="{{ asset('images/usp-icon3.png') }}" alt="Visitors">
                    @else
                    <i class="fa-solid fa-user-group"></i>
                    @endif
                </div>
                <div class="stat-number">{{ $stats['visitors'] }}+</div>
                <div class="stat-label">VISITOR ATTENDED</div>
            </div>
        </div>
        <hr class="mt-4 text-muted opacity-25">
    </div>
</section>

<section class="features-section why-choose">
    <div class="container">
        <div class="section-header">
            <h2><span>WHY CHOOSE</span> EXHIBITFLOW</h2>
            <p class="text-muted small">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-3">
                <div class="d-flex flex-column align-items-center align-items-lg-end text-center text-lg-end mb-5">
                    <div class="feature-icon-box">
                        @if(file_exists(public_path('images/why-choose-icon.png')))
                        <img src="{{ asset('images/why-choose-icon.png') }}" alt="Feature">
                        @else
                        <i class="fa-solid fa-star"></i>
                        @endif
                    </div>
                    <div class="feature-title">SOME TITLE HERE</div>
                    <div class="feature-desc">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                </div>
                <div class="d-flex flex-column align-items-center align-items-lg-end text-center text-lg-end">
                    <div class="feature-icon-box">
                        @if(file_exists(public_path('images/why-choose-icon.png')))
                        <img src="{{ asset('images/why-choose-icon.png') }}" alt="Feature">
                        @else
                        <i class="fa-solid fa-star"></i>
                        @endif
                    </div>
                    <div class="feature-title">SOME TITLE HERE</div>
                    <div class="feature-desc">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                </div>
            </div>
            
            <div class="col-lg-6 px-4 mb-5 mb-lg-0">
                @if(file_exists(public_path('images/why-choose-img.png')))
                <img src="{{ asset('images/why-choose-img.png') }}" class="img-fluid center-feature-img" alt="Expo Floor">
                @else
                <img src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=800&h=600&fit=crop" class="img-fluid center-feature-img" alt="Expo Floor">
                @endif
            </div>
            
            <div class="col-lg-3">
                <div class="d-flex flex-column align-items-center align-items-lg-start text-center text-lg-start mb-5">
                    <div class="feature-icon-box">
                        @if(file_exists(public_path('images/why-choose-icon.png')))
                        <img src="{{ asset('images/why-choose-icon.png') }}" alt="Feature">
                        @else
                        <i class="fa-solid fa-star"></i>
                        @endif
                    </div>
                    <div class="feature-title">SOME TITLE HERE</div>
                    <div class="feature-desc">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                </div>
                <div class="d-flex flex-column align-items-center align-items-lg-start text-center text-lg-start">
                    <div class="feature-icon-box">
                        @if(file_exists(public_path('images/why-choose-icon.png')))
                        <img src="{{ asset('images/why-choose-icon.png') }}" alt="Feature">
                        @else
                        <i class="fa-solid fa-star"></i>
                        @endif
                    </div>
                    <div class="feature-title">SOME TITLE HERE</div>
                    <div class="feature-desc">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-custom bg-gradient-purple px-5">REGISTER YOUR COMPANY TODAY</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-custom bg-gradient-purple px-5">REGISTER YOUR COMPANY TODAY</a>
            @endauth
        </div>
    </div>
</section>

@include('partials.frontend-footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@if(file_exists(public_path('js/main.js')))
<script src="{{ asset('js/main.js') }}"></script>
@endif
</body>
</html>

