@extends('layouts.exhibitor')

@section('title', 'Payment Confirmation')
@section('page-title', 'Payment Processing')

@push('styles')
<style>
    .confirmation-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .confirmation-card {
        background: white;
        border-radius: 16px;
        padding: 60px 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .success-icon {
        width: 100px;
        height: 100px;
        background: #6366f1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
    }
    
    .success-icon i {
        font-size: 3rem;
        color: white;
    }
    
    .confirmation-message {
        font-size: 1.2rem;
        color: #1e293b;
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .confirmation-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #6366f1;
        margin: 30px 0;
        padding: 20px;
        background: #f0f9ff;
        border-radius: 12px;
    }
    
    .welcome-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 25px;
        margin: 30px 0;
        display: flex;
        align-items: center;
        gap: 15px;
        text-align: left;
    }
    
    .welcome-icon {
        font-size: 2rem;
        color: #6366f1;
    }
    
    .welcome-text {
        flex: 1;
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 40px;
    }
    
    .btn-action {
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-dashboard {
        background: #6366f1;
        color: white;
    }
    
    .btn-dashboard:hover {
        background: #4f46e5;
        color: white;
    }
    
    .btn-receipt {
        background: white;
        color: #6366f1;
        border: 2px solid #6366f1;
    }
    
    .btn-receipt:hover {
        background: #f0f9ff;
        color: #6366f1;
    }
</style>
@endpush

@section('content')
<div class="confirmation-container">
    <div class="confirmation-card">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        
        <div class="confirmation-message">
            Your booking has been confirmed! A detailed confirmation and receipt have been sent to {{ $payment->user->email }}.
        </div>
        
        <div class="confirmation-number">
            Booking Confirmation Number: {{ $payment->booking->booking_number }}
        </div>
        
        <div class="welcome-section">
            <i class="bi bi-search welcome-icon"></i>
            <div class="welcome-text">
                <strong>Welcome aboard!</strong><br>
                Your login credentials have been sent to your email address. Please check your inbox (and spam folder) to get started.
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="{{ route('dashboard') }}" class="btn-action btn-dashboard">
                Go to Dashboard
            </a>
            <a href="{{ route('bookings.show', $payment->booking->id) }}" class="btn-action btn-receipt">
                Download Receipt
            </a>
        </div>
    </div>
</div>
@endsection

