@extends('layouts.admin')

@section('title', 'Payment Details')
@section('page-title', 'Payment Details')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Payment Details</h5>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Transaction Information</h6>
                <p><strong>Transaction ID:</strong> #{{ $payment->id }}</p>
                <p><strong>Amount:</strong> ₹{{ number_format($payment->amount, 2) }}</p>
                <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                <p><strong>Payment Date:</strong> {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') : 'N/A' }}</p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                @if($payment->booking)
                    <h6>Booking Information</h6>
                    <p><strong>Booking Number:</strong> {{ $payment->booking->booking_number }}</p>
                    <p><strong>Exhibition:</strong> {{ $payment->booking->exhibition->name ?? 'N/A' }}</p>
                    <p><strong>Total Amount:</strong> ₹{{ number_format($payment->booking->total_amount, 2) }}</p>
                    <p><strong>Paid Amount:</strong> ₹{{ number_format($payment->booking->paid_amount, 2) }}</p>
                @endif
                @if($payment->user)
                    <h6>User Information</h6>
                    <p><strong>User:</strong> {{ $payment->user->name }}</p>
                    <p><strong>Email:</strong> {{ $payment->user->email }}</p>
                @endif
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
