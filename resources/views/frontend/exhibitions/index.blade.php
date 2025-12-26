@extends('layouts.frontend')

@section('title', 'Home - Exhibition Management System')

@push('styles')

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
                    @php
                        $floorplanImages = is_array($exhibition->floorplan_images ?? null)
                            ? $exhibition->floorplan_images
                            : (array) ($exhibition->floorplan_image ? [$exhibition->floorplan_image] : []);
                        $primaryFloorplanImage = $floorplanImages[0] ?? null;
                    @endphp
                    @if($primaryFloorplanImage)
                        <img src="{{ asset('storage/' . $primaryFloorplanImage) }}" alt="{{ $exhibition->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="bi bi-calendar-event"></i>
                    @endif
                </div>
                <div class="exhibition-card-body">
                    <h5 class="exhibition-card-title">{{ $exhibition->name ?? 'Exhibition' }}</h5>
                    <p class="exhibition-card-date">
                        @if($exhibition->start_date && $exhibition->end_date)
                            {{ $exhibition->start_date->format('d M Y') }} - {{ $exhibition->end_date->format('d M Y') }}
                        @else
                            Date TBA
                        @endif
                    </p>
                    <p class="exhibition-card-location">{{ $exhibition->venue ?? 'Venue TBA' }}</p>
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
                    @php
                        $floorplanImages = is_array($exhibition->floorplan_images ?? null)
                            ? $exhibition->floorplan_images
                            : (array) ($exhibition->floorplan_image ? [$exhibition->floorplan_image] : []);
                        $primaryFloorplanImage = $floorplanImages[0] ?? null;
                    @endphp
                    @if($primaryFloorplanImage)
                        <img src="{{ asset('storage/' . $primaryFloorplanImage) }}" alt="{{ $exhibition->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="bi bi-calendar-event"></i>
                    @endif
                </div>
                <div class="exhibition-card-body">
                    <h5 class="exhibition-card-title">{{ $exhibition->name ?? 'Exhibition' }}</h5>
                    <p class="exhibition-card-date">
                        @if($exhibition->start_date && $exhibition->end_date)
                            {{ $exhibition->start_date->format('d M Y') }} - {{ $exhibition->end_date->format('d M Y') }}
                        @else
                            Date TBA
                        @endif
                    </p>
                    <p class="exhibition-card-location">{{ $exhibition->venue ?? 'Venue TBA' }}</p>
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

<!-- Footer is included in the layout -->
@endsection
