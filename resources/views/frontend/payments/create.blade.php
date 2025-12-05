@extends('layouts.frontend')

@section('title', 'Make Payment')

@section('content')
<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left me-2"></i>Back to Booking
            </a>
            <h1 class="mb-1">Make Payment for Booking #{{ $booking->booking_number }}</h1>
            <p class="text-muted mb-0">Exhibition: {{ $booking->exhibition->name }}</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2 d-flex justify-content-between">
                        <span>Total Amount:</span>
                        <strong>₹{{ number_format($booking->total_amount, 0) }}</strong>
                    </p>
                    <p class="mb-2 d-flex justify-content-between">
                        <span>Already Paid:</span>
                        <strong>₹{{ number_format($booking->paid_amount, 0) }}</strong>
                    </p>
                    <p class="mb-3 d-flex justify-content-between">
                        <span>Outstanding:</span>
                        <strong>₹{{ number_format($outstanding, 0) }}</strong>
                    </p>
                    <p class="mb-0 text-muted">
                        Recommended initial payment ({{ $initialPercent }}%): <strong>₹{{ number_format($initialAmount, 0) }}</strong>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Select Payment Method</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payments.store') }}">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        @if($walletBalance > 0)
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-wallet2 me-2"></i>
                            <strong>Wallet Balance:</strong> ₹{{ number_format($walletBalance, 2) }}
                            <a href="{{ route('wallet.index') }}" class="float-end">View Wallet</a>
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Method</label>
                            <select name="payment_method" class="form-select" id="payment_method" required>
                                @if($walletBalance > 0)
                                <option value="wallet">Wallet (Balance: ₹{{ number_format($walletBalance, 2) }})</option>
                                @endif
                                <option value="online">Online (Card/UPI/Net Banking) - 2.5% extra</option>
                                <option value="offline">Offline (Cash/Cheque)</option>
                                <option value="rtgs">RTGS</option>
                                <option value="neft">NEFT</option>
                            </select>
                            <small class="text-muted">Wallet can only be used for booking payments</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Amount</label>
                            <input type="number" name="amount" value="{{ $initialAmount }}" min="1" max="{{ $outstanding }}" step="1" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-2"></i>Confirm Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


