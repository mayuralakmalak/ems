@extends('layouts.exhibitor')

@section('title', 'Payment Gateway')
@section('page-title', 'Complete Payment')

@push('styles')
<style>
    .gateway-card {
        background: white;
        border-radius: 12px;
        padding: 40px;
        margin: 0 auto;
        max-width: 500px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
    }
    
    .amount-display {
        font-size: 2.5rem;
        font-weight: 700;
        color: #6366f1;
        margin: 20px 0;
    }
</style>
@endpush

@section('content')
<div class="gateway-card">
    <h3 class="mb-4">Complete Payment</h3>
    
    <div class="mb-4">
        <div class="text-muted">Amount to Pay</div>
        <div class="amount-display">₹{{ number_format($payment->amount, 2) }}</div>
    </div>
    
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Payment gateway integration will be implemented here.
        <br><br>
        For now, you can simulate payment completion by clicking the button below.
    </div>
    
    <form action="{{ route('sponsorships.payment.callback', $payment->id) }}" method="POST">
        @csrf
        <input type="hidden" name="transaction_id" value="TXN{{ now()->format('YmdHis') }}{{ rand(1000, 9999) }}">
        
        <button type="submit" class="btn btn-primary btn-lg w-100">
            <i class="bi bi-check-circle me-2"></i>Complete Payment (Simulate)
        </button>
    </form>
    
    <div class="mt-4">
        <a href="{{ route('sponsorships.booking', $payment->sponsorshipBooking->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Cancel
        </a>
    </div>
</div>
@endsection

