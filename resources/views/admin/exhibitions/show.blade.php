@extends('layouts.admin')

@section('title', 'Exhibition Details')
@section('page-title', 'Exhibition Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Exhibitions
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>General Information</h5>
            </div>
            <div class="card-body">
                <h3 class="mb-3">{{ $exhibition->name }}</h3>
                <p class="text-muted mb-4">{{ $exhibition->description ?? 'No description provided.' }}</p>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong><i class="bi bi-building text-primary me-2"></i>Venue:</strong>
                        <p class="mb-0">{{ $exhibition->venue }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="bi bi-geo-alt text-primary me-2"></i>Location:</strong>
                        <p class="mb-0">{{ $exhibition->city }}, {{ $exhibition->state ?? '' }} {{ $exhibition->country }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="bi bi-calendar3 text-primary me-2"></i>Start Date:</strong>
                        <p class="mb-0">{{ $exhibition->start_date->format('d M Y') }} {{ $exhibition->start_time ? 'at ' . $exhibition->start_time : '' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="bi bi-calendar-check text-primary me-2"></i>End Date:</strong>
                        <p class="mb-0">{{ $exhibition->end_date->format('d M Y') }} {{ $exhibition->end_time ? 'at ' . $exhibition->end_time : '' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="bi bi-tag text-primary me-2"></i>Status:</strong>
                        <p class="mb-0">
                            <span class="badge bg-{{ $exhibition->status === 'active' ? 'success' : ($exhibition->status === 'completed' ? 'info' : 'secondary') }}">
                                {{ ucfirst($exhibition->status) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Booths ({{ $exhibition->booths->count() }})</h5>
            </div>
            <div class="card-body">
                @if($exhibition->booths->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exhibition->booths->take(10) as $booth)
                            <tr>
                                <td>{{ $booth->name }}</td>
                                <td>{{ $booth->booth_type }}</td>
                                <td>{{ $booth->size_sqft ?? 'N/A' }} sq ft</td>
                                <td>₹{{ number_format($booth->price ?? 0, 0) }}</td>
                                <td>
                                    <span class="badge bg-{{ $booth->is_available ? 'success' : 'secondary' }}">
                                        {{ $booth->is_available ? 'Available' : 'Booked' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($exhibition->booths->count() > 10)
                    <p class="text-muted text-center mt-2">Showing first 10 of {{ $exhibition->booths->count() }} booths</p>
                    @endif
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.booths.index', $exhibition->id) }}" class="btn btn-primary me-2">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Manage All Booths
                    </a>
                    <a href="{{ route('admin.floorplan.show', $exhibition->id) }}" class="btn btn-success">
                        <i class="bi bi-diagram-3 me-2"></i>Interactive Floorplan
                    </a>
                </div>
                @else
                <p class="text-muted text-center">No booths configured yet.</p>
                <div class="mt-3 text-center">
                    <a href="{{ route('admin.booths.create', $exhibition->id) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create First Booth
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Base Price / Sq Ft:</strong> ₹{{ number_format($exhibition->price_per_sqft ?? 0, 0) }}</p>
                <p><strong>Raw Booth / Sq Ft:</strong> ₹{{ number_format($exhibition->raw_price_per_sqft ?? 0, 0) }}</p>
                <p><strong>Orphand Booth / Sq Ft:</strong> ₹{{ number_format($exhibition->orphand_price_per_sqft ?? 0, 0) }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.exhibitions.edit', $exhibition->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-2"></i>Edit Exhibition
                    </a>
                    <form action="{{ route('admin.exhibitions.destroy', $exhibition->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this exhibition? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-2"></i>Delete Exhibition
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

