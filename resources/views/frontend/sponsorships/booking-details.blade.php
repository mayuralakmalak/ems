@extends('layouts.exhibitor')

@section('title', 'Sponsorship Booking Details')
@section('page-title', 'Booking Details - ' . $booking->booking_number)

@push('styles')
<style>
    .detail-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .detail-row:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        color: #64748b;
        font-weight: 500;
    }
    
    .detail-value {
        color: #1e293b;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="detail-card">
            <h4 class="mb-4">Booking Information</h4>
            <div class="detail-row">
                <span class="detail-label">Booking Number:</span>
                <span class="detail-value">{{ $booking->booking_number }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Sponsorship Package:</span>
                <span class="detail-value">{{ $booking->sponsorship->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Exhibition:</span>
                <span class="detail-value">{{ $booking->exhibition->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tier:</span>
                <span class="detail-value">{{ $booking->sponsorship->tier ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span class="detail-value">₹{{ number_format($booking->amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Paid Amount:</span>
                <span class="detail-value">₹{{ number_format($booking->paid_amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Outstanding:</span>
                <span class="detail-value">₹{{ number_format($booking->outstanding_amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">
                    <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value">
                    <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'partial' ? 'warning' : 'info') }}">
                        {{ ucfirst($booking->payment_status) }}
                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Booking Date:</span>
                <span class="detail-value">{{ $booking->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
        
        @if($booking->contact_emails)
        <div class="detail-card">
            <h5 class="mb-3">Contact Emails</h5>
            <ul>
                @foreach($booking->contact_emails as $email)
                <li>{{ $email }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        @if($booking->contact_numbers)
        <div class="detail-card">
            <h5 class="mb-3">Contact Numbers</h5>
            <ul>
                @foreach($booking->contact_numbers as $number)
                <li>{{ $number }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        @if($booking->notes)
        <div class="detail-card">
            <h5 class="mb-3">Notes</h5>
            <p>{{ $booking->notes }}</p>
        </div>
        @endif
        
        @if($booking->payments->count() > 0)
        <div class="detail-card">
            <h5 class="mb-3">Payment History</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_number }}</td>
                        <td>₹{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>
                            <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td>{{ $payment->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <div class="detail-card">
            <h5 class="mb-3">Deliverables</h5>
            <ul class="list-unstyled">
                @if(is_array($booking->sponsorship->deliverables))
                    @foreach($booking->sponsorship->deliverables as $deliverable)
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ $deliverable }}
                    </li>
                    @endforeach
                @endif
            </ul>
        </div>
        
        <div class="detail-card">
            <div class="d-grid gap-2">
                @if($booking->payment_status !== 'paid')
                    <a href="{{ route('sponsorships.payment', $booking->id) }}" class="btn btn-primary">
                        <i class="bi bi-credit-card me-2"></i>Make Payment
                    </a>
                @endif
                <a href="{{ route('sponsorships.my-bookings') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

