@extends('layouts.exhibitor')

@section('title', 'Payment Confirmation')
@section('page-title', 'Payment Confirmation')

@push('styles')
<style>
    .confirmation-card {
        background: white;
        border-radius: 12px;
        padding: 40px;
        margin: 0 auto;
        max-width: 600px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
    }
    
    .success-icon {
        width: 80px;
        height: 80px;
        background: #d1fae5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2.5rem;
        color: #10b981;
    }
    
    .info-box {
        background: #f8fafc;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        text-align: left;
    }
</style>
@endpush

@section('content')
<div class="confirmation-card">
    @if(session('success'))
        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h3 class="mb-3">Payment {{ $payment->status === 'completed' ? 'Completed' : 'Submitted' }}!</h3>
        <p class="text-muted mb-4">{{ session('success') }}</p>
    @else
        <div class="success-icon" style="background: #dbeafe; color: #3b82f6;">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <h3 class="mb-3">Payment Pending</h3>
        <p class="text-muted mb-4">Your payment is being processed. Please wait for admin approval.</p>
    @endif
    
    <div class="info-box">
        <div class="row mb-2">
            <div class="col-6"><strong>Payment Number:</strong></div>
            <div class="col-6">{{ $payment->payment_number }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-6"><strong>Amount:</strong></div>
            <div class="col-6">₹{{ number_format($payment->amount, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-6"><strong>Payment Method:</strong></div>
            <div class="col-6">{{ ucfirst($payment->payment_method) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-6"><strong>Status:</strong></div>
            <div class="col-6">
                <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                    {{ ucfirst($payment->status) }}
                </span>
            </div>
        </div>
        @if($payment->transaction_id)
        <div class="row mb-2">
            <div class="col-6"><strong>Transaction ID:</strong></div>
            <div class="col-6">{{ $payment->transaction_id }}</div>
        </div>
        @endif
        <div class="row">
            <div class="col-6"><strong>Date:</strong></div>
            <div class="col-6">{{ $payment->created_at->format('M d, Y h:i A') }}</div>
        </div>
    </div>
    
    <div class="d-flex gap-3 justify-content-center mt-4">
        <a href="{{ route('sponsorships.booking', $payment->sponsorshipBooking->id) }}" class="btn btn-primary">
            <i class="bi bi-eye me-2"></i>View Booking
        </a>
        <a href="{{ route('sponsorships.my-bookings') }}" class="btn btn-outline-secondary">
            <i class="bi bi-list me-2"></i>My Bookings
        </a>
    </div>
</div>
@endsection

