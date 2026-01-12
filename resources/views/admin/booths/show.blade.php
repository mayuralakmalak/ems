@extends('layouts.admin')

@section('title', 'Booth Details')
@section('page-title', 'Booth Details - ' . $booth->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.booths.index', $exhibition->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Booths
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Booth Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Booth Name:</strong><br>
                        <span class="h5">{{ $booth->name }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong><br>
                        @if($booth->is_booked)
                            <span class="badge bg-danger px-3 py-2">Booked</span>
                        @elseif($booth->is_available)
                            <span class="badge bg-success px-3 py-2">Available</span>
                        @else
                            <span class="badge bg-secondary px-3 py-2">Unavailable</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Category:</strong><br>
                        {{ $booth->category }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Booth Type:</strong><br>
                        {{ $booth->booth_type }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Size:</strong><br>
                        {{ $booth->size_sqft }} sq meter
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Sides Open:</strong><br>
                        {{ $booth->sides_open }} Side{{ $booth->sides_open > 1 ? 's' : '' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Price:</strong><br>
                        @if($booth->is_free)
                            <span class="badge bg-info">Free</span>
                        @else
                            ₹{{ number_format($booth->price, 2) }}
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Exhibition:</strong><br>
                        {{ $exhibition->name }}
                    </div>
                </div>
            </div>
        </div>

        @if($booth->bookings->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Bookings</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Booking #</th>
                                <th>Exhibitor</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booth->bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_number }}</td>
                                <td>{{ $booking->user->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">{{ ucfirst($booking->status) }}</span></td>
                                <td>₹{{ number_format($booking->total_amount, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.booths.edit', [$exhibition->id, $booth->id]) }}" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-pencil me-2"></i>Edit Booth
                </a>
                @if(!$booth->is_booked)
                <form action="{{ route('admin.booths.destroy', [$exhibition->id, $booth->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this booth?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-trash me-2"></i>Delete Booth
                    </button>
                </form>
                @else
                <button class="btn btn-secondary w-100" disabled>
                    <i class="bi bi-lock me-2"></i>Cannot Delete (Booked)
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

