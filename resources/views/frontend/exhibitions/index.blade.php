@extends('layouts.frontend')

@section('title', 'Home - Exhibition Management System')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 80px 0;
        margin-bottom: 60px;
    }
    .exhibition-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-radius: 15px;
        overflow: hidden;
    }
    .exhibition-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    }
    .exhibition-card .card-img-top {
        height: 220px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .badge-custom {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Discover Amazing Exhibitions</h1>
                <p class="lead mb-4">Book your perfect exhibition space and showcase your business to thousands of visitors. Join the most prestigious events in the industry.</p>
                <a href="{{ route('exhibitions.list') }}" class="btn btn-light btn-lg px-5">
                    <i class="bi bi-search me-2"></i>Explore Exhibitions
                </a>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-calendar-event" style="font-size: 200px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Exhibitions Section -->
<div class="container mb-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="display-5 fw-bold mb-3">Active & Upcoming Exhibitions</h2>
            <p class="text-muted">Choose from our curated selection of premium exhibition events</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($exhibitions as $exhibition)
        <div class="col-md-4 mb-4">
            <div class="exhibition-card card h-100">
                <div class="position-relative">
                    @if($exhibition->floorplan_image)
                    <img src="{{ asset('storage/' . $exhibition->floorplan_image) }}" class="card-img-top" alt="{{ $exhibition->name }}">
                    @else
                    <div class="card-img-top d-flex align-items-center justify-content-center bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="bi bi-calendar-event" style="font-size: 80px;"></i>
                    </div>
                    @endif
                    <span class="badge badge-custom bg-success position-absolute top-0 end-0 m-3">
                        <i class="bi bi-check-circle me-1"></i>Active
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">{{ $exhibition->name }}</h5>
                    <p class="card-text text-muted mb-3">{{ Str::limit($exhibition->description, 120) }}</p>
                    <div class="mb-3">
                        <p class="mb-2">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                            <strong>{{ $exhibition->venue }}</strong>
                        </p>
                        <p class="mb-2 text-muted">
                            <i class="bi bi-geo text-muted me-2"></i>
                            {{ $exhibition->city }}, {{ $exhibition->country }}
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-calendar3 text-info me-2"></i>
                            {{ $exhibition->start_date->format('d M Y') }} - {{ $exhibition->end_date->format('d M Y') }}
                        </p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary">
                            <i class="bi bi-grid me-1"></i>{{ $exhibition->booths->count() }} Booths Available
                        </span>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="{{ route('exhibitions.show', $exhibition->id) }}" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>View Details & Book Now
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 80px;"></i>
                    <h4 class="mt-4 mb-2">No exhibitions available</h4>
                    <p class="text-muted">Check back later for upcoming exhibitions.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection

