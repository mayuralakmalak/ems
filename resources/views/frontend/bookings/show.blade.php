@extends('layouts.exhibitor')

@section('title', 'Booking Details')

@section('page-title', 'Booking Details - ' . $booking->booking_number)

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
        <h3 class="mb-1">Booking #{{ $booking->booking_number }}</h3>
        <p class="text-muted mb-0">Review your booking details and complete payment</p>
    </div>
</div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Exhibition & Booth Details</h5>
                </div>
                <div class="card-body">
                    <h5>{{ $booking->exhibition->name }}</h5>
                    <p class="text-muted mb-2">
                        <i class="bi bi-geo-alt me-1"></i>{{ $booking->exhibition->venue }}, {{ $booking->exhibition->city }}, {{ $booking->exhibition->country }}
                        <br>
                        <i class="bi bi-calendar3 me-1"></i>{{ $booking->exhibition->start_date->format('d M Y') }} - {{ $booking->exhibition->end_date->format('d M Y') }}
                    </p>

                    <hr>

                    <h6 class="mb-2">Booth Information</h6>
                    <p class="mb-1"><strong>Booth:</strong> {{ $booking->booth->name }}</p>
                    <p class="mb-1"><strong>Category:</strong> {{ $booking->booth->category }}</p>
                    <p class="mb-1"><strong>Type:</strong> {{ $booking->booth->booth_type }}</p>
                    <p class="mb-1"><strong>Size:</strong> {{ $booking->booth->size_sqft }} sq. ft.</p>
                    <p class="mb-0"><strong>Sides Open:</strong> {{ $booking->booth->sides_open }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Payment Summary</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2 d-flex justify-content-between">
                        <span>Total Amount:</span>
                        <strong>₹{{ number_format($booking->total_amount, 0) }}</strong>
                    </p>
                    <p class="mb-2 d-flex justify-content-between">
                        <span>Paid Amount:</span>
                        <strong>₹{{ number_format($booking->paid_amount, 0) }}</strong>
                    </p>
                    <p class="mb-3 d-flex justify-content-between">
                        <span>Outstanding:</span>
                        <strong>₹{{ number_format($booking->total_amount - $booking->paid_amount, 0) }}</strong>
                    </p>
                    <p class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </p>

                    @if($booking->status === 'pending' || $booking->status === 'confirmed')
                    <a href="{{ route('payments.create', $booking->id) }}" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-credit-card me-2"></i>Proceed to Payment
                    </a>
                    @endif

                    @if($booking->status !== 'cancelled')
                    <button type="button" class="btn btn-outline-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#replaceBoothModal">
                        <i class="bi bi-arrow-repeat me-2"></i>Replace Booth
                    </button>
                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelBookingModal">
                        <i class="bi bi-x-circle me-2"></i>Cancel Booking
                    </button>
                    @endif
                </div>
            </div>

            @if($booking->bookingServices->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Additional Services</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($booking->bookingServices as $bookingService)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $bookingService->service->name ?? 'N/A' }} (Qty: {{ $bookingService->quantity }})</span>
                            <strong>₹{{ number_format($bookingService->total_price, 0) }}</strong>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Replace Booth Modal -->
<div class="modal fade" id="replaceBoothModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bookings.replace', $booking->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Replace Booth</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Select a new booth to replace the current one:</p>
                    <select name="new_booth_id" class="form-select" required>
                        <option value="">Select New Booth</option>
                        @foreach($booking->exhibition->booths->where('is_available', true) as $booth)
                        <option value="{{ $booth->id }}">{{ $booth->name }} - ₹{{ number_format($booth->price, 0) }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Price difference will be calculated automatically.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Replace Booth</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger"><strong>Are you sure you want to cancel this booking?</strong></p>
                    <p>Admin will process the refund/wallet credit after reviewing your cancellation request.</p>
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason *</label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="Please provide a reason for cancellation"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Booking</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


