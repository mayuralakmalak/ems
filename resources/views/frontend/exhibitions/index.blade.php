@extends('layouts.frontend')

@section('title', 'Home - Exhibition Management System')

@push('styles')
<style>
    /* Hero Banner Section */
    .hero-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .hero-banner::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><path d="M0,0 L1200,0 L1200,600 L0,600 Z" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>') center/cover;
        opacity: 0.3;
    }
    .hero-banner-content {
        position: relative;
        z-index: 1;
        text-align: center;
        color: white;
        padding: 60px 20px;
    }
    .hero-banner h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
    }
    .hero-banner p {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.8;
    }
    
    /* Exhibition Cards */
    .exhibition-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        height: 100%;
        background: white;
    }
    .exhibition-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .exhibition-card-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    .exhibition-card-body {
        padding: 20px;
    }
    .exhibition-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #1e293b;
    }
    .exhibition-card-date {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }
    .exhibition-card-location {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }
    .exhibition-card-btn {
        width: 100%;
        padding: 10px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .exhibition-card-btn:hover {
        background: #4f46e5;
        transform: translateY(-2px);
    }
    
    /* Statistics Section */
    .stats-section {
        background: #f8fafc;
        padding: 80px 0;
    }
    .stat-box {
        text-align: center;
        padding: 40px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        color: #6366f1;
        margin-bottom: 10px;
    }
    .stat-label {
        font-size: 1rem;
        color: #64748b;
        font-weight: 500;
    }
    
    /* Why Choose Section */
    .why-choose-section {
        padding: 80px 0;
        background: white;
    }
    .why-choose-content {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }
    .why-choose-content h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 30px;
        color: #1e293b;
    }
    .why-choose-text {
        font-size: 1.1rem;
        color: #64748b;
        line-height: 1.8;
        margin-bottom: 30px;
        text-align: left;
        columns: 2;
        column-gap: 30px;
    }
    @media (max-width: 768px) {
        .why-choose-text {
            columns: 1;
        }
    }
    .learn-more-btn {
        padding: 12px 40px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    .learn-more-btn:hover {
        background: #4f46e5;
        transform: translateY(-2px);
    }
    
    /* Section Headings */
    .section-heading {
        text-align: center;
        margin-bottom: 50px;
    }
    .section-heading h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
    }
    .section-heading p {
        font-size: 1.1rem;
        color: #64748b;
    }
    
    /* Footer */
    .main-footer {
        background: #1e293b;
        color: white;
        padding: 60px 0 30px;
    }
    .footer-logo {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 20px;
    }
    .footer-column h5 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: white;
    }
    .footer-column ul {
        list-style: none;
        padding: 0;
    }
    .footer-column ul li {
        margin-bottom: 10px;
    }
    .footer-column ul li a {
        color: #cbd5e1;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    .footer-column ul li a:hover {
        color: white;
    }
    .footer-social {
        display: flex;
        gap: 15px;
        margin-top: 15px;
    }
    .footer-social a {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        color: white;
        transition: all 0.3s ease;
    }
    .footer-social a:hover {
        background: #6366f1;
        transform: translateY(-3px);
    }
    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: 40px;
        padding-top: 20px;
        text-align: center;
        color: #cbd5e1;
    }
</style>
@endpush

@section('content')
<!-- Hero Banner Section -->
<div class="hero-banner">
    <div class="hero-banner-content">
        <div class="container">
            <h1>Welcome to Exhibition Management System</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>
    </div>
</div>

<!-- Active Exhibitions Section -->
<div class="container py-5">
    <div class="section-heading">
        <h2>Active Exhibitions</h2>
    </div>
    
    <div class="row g-4">
        @forelse($activeExhibitions as $exhibition)
        <div class="col-md-4">
            <div class="exhibition-card">
                <div class="exhibition-card-img">
                    @if($exhibition->floorplan_image)
                        <img src="{{ asset('storage/' . $exhibition->floorplan_image) }}" alt="{{ $exhibition->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="bi bi-calendar-event"></i>
                    @endif
                </div>
                <div class="exhibition-card-body">
                    <h5 class="exhibition-card-title">{{ $exhibition->name }}</h5>
                    <p class="exhibition-card-date">{{ $exhibition->start_date->format('d M Y') }} - {{ $exhibition->end_date->format('d M Y') }}</p>
                    <p class="exhibition-card-location">{{ $exhibition->venue }}</p>
                    <a href="{{ route('exhibitions.show', $exhibition->id) }}" class="exhibition-card-btn">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">No active exhibitions at the moment.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Upcoming Exhibitions Section -->
<div class="container py-5">
    <div class="section-heading">
        <h2>Upcoming Exhibitions</h2>
    </div>
    
    <div class="row g-4">
        @forelse($upcomingExhibitions as $exhibition)
        <div class="col-md-4">
            <div class="exhibition-card">
                <div class="exhibition-card-img">
                    @if($exhibition->floorplan_image)
                        <img src="{{ asset('storage/' . $exhibition->floorplan_image) }}" alt="{{ $exhibition->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="bi bi-calendar-event"></i>
                    @endif
                </div>
                <div class="exhibition-card-body">
                    <h5 class="exhibition-card-title">{{ $exhibition->name }}</h5>
                    <p class="exhibition-card-date">{{ $exhibition->start_date->format('d M Y') }} - {{ $exhibition->end_date->format('d M Y') }}</p>
                    <p class="exhibition-card-location">{{ $exhibition->venue }}</p>
                    <a href="{{ route('exhibitions.show', $exhibition->id) }}" class="exhibition-card-btn">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">No upcoming exhibitions at the moment.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Statistics Section -->
<div class="stats-section">
    <div class="container">
        <div class="section-heading">
            <h2>Exhibitions at a global</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-box">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Exhibitions Hosted</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Cities Covered</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <div class="stat-number">10+</div>
                    <div class="stat-label">Years Experience</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Why Choose Section -->
<div class="why-choose-section">
    <div class="container">
        <div class="why-choose-content">
            <h2>Why choose Exhibitions</h2>
            <div class="why-choose-text">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>
                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
            <button class="learn-more-btn">Learn More</button>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="main-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="footer-logo">
                    <i class="bi bi-calendar-event me-2"></i>EMS
                </div>
                <p style="color: #cbd5e1; line-height: 1.6;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            </div>
            <div class="col-md-3">
                <div class="footer-column">
                    <h5>Company</h5>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Careers</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3">
                <div class="footer-column">
                    <h5>Support</h5>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3">
                <div class="footer-column">
                    <h5>Contact Us</h5>
                    <p style="color: #cbd5e1; margin-bottom: 10px;">
                        <i class="bi bi-envelope me-2"></i>info@exhibition.com
                    </p>
                    <p style="color: #cbd5e1; margin-bottom: 15px;">
                        <i class="bi bi-phone me-2"></i>+1 234 567 8900
                    </p>
                    <div class="footer-social">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Exhibition Management System. All rights reserved.</p>
        </div>
    </div>
</footer>
@endsection
