@extends('layouts.frontend')

@section('title', 'Resend Verification Email - ' . config('app.name', 'EMS'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: radial-gradient(circle at 20% 20%, #eef2ff 0, #f8fafc 45%), 
                    radial-gradient(circle at 80% 0%, #e0f2fe 0, #f8fafc 40%), 
                    #f8fafc;
    }
    
    main {
        padding: 50px 20px;
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .resend-container {
        max-width: 500px;
        width: 100%;
        margin: 0 auto;
        background: linear-gradient(135deg, rgba(17,24,39,0.95) 0%, rgba(15,23,42,0.92) 40%, rgba(15,23,42,0.9) 100%);
        border-radius: 20px;
        box-shadow: 0 28px 60px rgba(15, 23, 42, 0.35);
        padding: 48px 46px;
        color: #e2e8f0;
        position: relative;
        overflow: hidden;
    }
    
    .resend-container::before {
        content: "";
        position: absolute;
        top: -140px;
        right: -140px;
        width: 280px;
        height: 280px;
        background: radial-gradient(circle, rgba(99,102,241,0.22) 0%, rgba(99,102,241,0) 70%);
        transform: rotate(22deg);
        pointer-events: none;
    }
    
    .resend-header {
        text-align: center;
        margin-bottom: 32px;
        position: relative;
        z-index: 1;
    }
    
    .resend-header .icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #fff;
        box-shadow: 0 12px 32px rgba(99, 102, 241, 0.4);
    }
    
    .resend-header h1 {
        font-size: 2rem;
        font-weight: 800;
        color: #f8fafc;
        margin-bottom: 10px;
        letter-spacing: -0.02em;
    }
    
    .resend-header p {
        font-size: 1rem;
        color: #cbd5e1;
    }
    
    .resend-content {
        position: relative;
        z-index: 1;
    }
    
    .alert-success {
        background: rgba(34, 197, 94, 0.15);
        border: 1px solid rgba(74, 222, 128, 0.3);
        color: #bbf7d0;
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        font-weight: 600;
        color: #cbd5e1;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .form-control {
        padding: 12px 16px;
        border: 1px solid rgba(226, 232, 240, 0.35);
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: rgba(255,255,255,0.04);
        color: #ffffff !important;
    }
    
    .form-control:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        outline: none;
        background: rgba(255,255,255,0.06);
    }
    
    .btn-resend {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.2s ease;
        letter-spacing: 0.01em;
        box-shadow: 0 18px 32px rgba(99, 102, 241, 0.35);
        cursor: pointer;
    }
    
    .btn-resend:hover {
        transform: translateY(-1px);
        box-shadow: 0 22px 36px rgba(99, 102, 241, 0.42);
    }
    
    .login-link {
        text-align: center;
        margin-top: 18px;
    }
    
    .login-link a {
        color: #c7d2fe;
        text-decoration: none;
        font-weight: 600;
        padding: 10px 12px;
        border-radius: 8px;
        display: inline-block;
        transition: all 0.2s ease;
    }
    
    .login-link a:hover {
        background: rgba(255,255,255,0.08);
        text-decoration: none;
        color: #e0e7ff;
    }
    
    @media (max-width: 768px) {
        main {
            padding: 30px 20px;
        }
        
        .resend-container {
            padding: 34px 22px;
        }
        
        .resend-header h1 {
            font-size: 1.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="resend-container">
    <div class="resend-header">
        <div class="icon">
            <i class="bi bi-envelope-paper"></i>
        </div>
        <h1>Resend Verification Email</h1>
        <p>Enter your email to receive a new verification link</p>
    </div>
    
    <div class="resend-content">
        @if (session('status'))
            <div class="alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.resend') }}" novalidate>
            @csrf
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    class="form-control @error('email') is-invalid @enderror" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required
                    autofocus
                    placeholder="Enter your email address">
                @error('email')
                    <div class="text-danger" style="margin-top: 5px; font-size: 0.85rem;">{{ $message }}</div>
                @enderror
            </div>
            
            <button type="submit" class="btn-resend">
                <i class="bi bi-send-fill me-2"></i>
                Send Verification Link
            </button>
        </form>
        
        <div class="login-link">
            <a href="{{ route('login') }}">
                Already verified? Sign in
            </a>
        </div>
    </div>
</div>
@endsection
