@extends('layouts.admin')

@section('title', 'Edit Booking')
@section('page-title', 'Edit Booking - ' . $booking->booking_number)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
    <span class="text-muted small">Booking #: {{ $booking->booking_number }}</span>
    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-outline-primary">
        <i class="bi bi-eye me-2"></i>View
    </a>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Booking</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ $booking->status === $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Total Amount</label>
                    <input type="number" step="0.01" min="0" name="total_amount" class="form-control" value="{{ old('total_amount', $booking->total_amount) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Paid Amount</label>
                    <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="{{ old('paid_amount', $booking->paid_amount) }}" required>
                    <small class="text-muted">Must be <= total amount</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Exhibitor</label>
                    <input type="text" class="form-control" value="{{ $booking->user->name ?? '-' }}" disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Booth</label>
                    <input type="text" class="form-control" value="{{ $booking->booth->name ?? 'N/A' }}" disabled>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Save Changes
                </button>
                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
