@extends('layouts.frontend')

@section('title', $exhibition->name . ' - Details')

@section('content')
<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left me-2"></i>Back to Exhibitions
            </a>
        </div>
        <div class="col-lg-8">
            <h1 class="mb-2">{{ $exhibition->name ?? 'Exhibition' }}</h1>
            <p class="text-muted mb-3">
                <i class="bi bi-geo-alt me-1"></i>{{ $exhibition->venue ?? 'N/A' }}, {{ $exhibition->city ?? '' }}, {{ $exhibition->country ?? '' }}<br>
                <i class="bi bi-calendar3 me-1"></i>
                @if($exhibition->start_date && $exhibition->end_date)
                    {{ $exhibition->start_date->format('d M Y') }} - {{ $exhibition->end_date->format('d M Y') }}
                @else
                    Date TBA
                @endif
            </p>
            <p>{{ $exhibition->description ?? 'No description available.' }}</p>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Booking Info</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Base Price / Sq Ft:</strong> ₹{{ number_format($exhibition->price_per_sqft ?? 0, 0) }}</p>
                    <p class="mb-2"><strong>Raw Booth / Sq Ft:</strong> ₹{{ number_format($exhibition->raw_price_per_sqft ?? 0, 0) }}</p>
                    <p class="mb-2"><strong>Orphand Booth / Sq Ft:</strong> ₹{{ number_format($exhibition->orphand_price_per_sqft ?? 0, 0) }}</p>
                    <p class="mb-0 text-muted"><small>Pricing may vary based on booth type, size and sides open.</small></p>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <a href="{{ url('/exhibitions/' . $exhibition->id . '/floorplan') }}" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-diagram-3 me-2"></i>View Interactive Floorplan
                    </a>
                    @auth
                    <a href="{{ route('floorplan.show', $exhibition->id) }}" class="btn btn-success w-100">
                        <i class="bi bi-cart-check me-2"></i>Book Booth
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-success w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login to Book
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Available Booths</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Booth</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Size (sq ft)</th>
                                    <th>Sides Open</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exhibition->booths as $booth)
                                <tr>
                                    <td><strong>{{ $booth->name ?? 'N/A' }}</strong></td>
                                    <td>{{ $booth->category ?? 'N/A' }}</td>
                                    <td>{{ $booth->booth_type ?? 'N/A' }}</td>
                                    <td>{{ $booth->size_sqft ?? 'N/A' }}</td>
                                    <td>{{ $booth->sides_open ?? 'N/A' }}</td>
                                    <td>₹{{ number_format($booth->price ?? 0, 0) }}</td>
                                    <td>
                                        @if($booth->is_available ?? false)
                                            <span class="badge bg-success">Available</span>
                                        @else
                                            <span class="badge bg-secondary">Booked</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($booth->is_available ?? false)
                                            @auth
                                            <form action="{{ route('bookings.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="exhibition_id" value="{{ $exhibition->id }}">
                                                <input type="hidden" name="booth_id" value="{{ $booth->id }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-cart-plus me-1"></i>Book Booth
                                                </button>
                                            </form>
                                            @else
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">
                                                Login to Book
                                            </a>
                                            @endauth
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled>Not Available</button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No booths available for this exhibition yet.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


