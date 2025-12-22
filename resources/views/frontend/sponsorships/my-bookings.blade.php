@extends('layouts.exhibitor')

@section('title', 'My Sponsorship Bookings')
@section('page-title', 'My Sponsorship Bookings')

@push('styles')
<style>
    .bookings-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-confirmed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    
    .payment-status-pending { background: #dbeafe; color: #1e40af; }
    .payment-status-partial { background: #fef3c7; color: #92400e; }
    .payment-status-paid { background: #d1fae5; color: #065f46; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>My Sponsorship Bookings</h3>
    <a href="{{ route('sponsorships.index') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Book New Sponsorship
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="bookings-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Sponsorship</th>
                    <th>Exhibition</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td><strong>{{ $booking->booking_number }}</strong></td>
                    <td>{{ $booking->sponsorship->name ?? 'N/A' }}</td>
                    <td>
                        @if($booking->exhibition)
                            {{ $booking->exhibition->name }}
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>₹{{ number_format($booking->amount, 2) }}</td>
                    <td>₹{{ number_format($booking->paid_amount, 2) }}</td>
                    <td>
                        <span class="status-badge status-{{ $booking->status }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge payment-status-{{ $booking->payment_status }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </td>
                    <td>{{ $booking->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('sponsorships.booking', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                            @if($booking->payment_status !== 'paid')
                                <a href="{{ route('sponsorships.payment', $booking->id) }}" class="btn btn-sm btn-primary">
                                    Pay
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        No sponsorship bookings found. <a href="{{ route('sponsorships.index') }}">Book one now</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $bookings->links() }}
</div>
@endsection

