@extends('layouts.frontend')

@section('title', 'Forgot Password - ' . config('app.name', 'EMS'))

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
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
    }
    
    .forgot-password-container {
        max-width: 480px;
        width: 100%;
        background: linear-gradient(135deg, rgba(17,24,39,0.95) 0%, rgba(15,23,42,0.92) 40%, rgba(15,23,42,0.9) 100%);
        border-radius: 20px;
        box-shadow: 0 28px 60px rgba(15, 23, 42, 0.35);
        padding: 48px 46px;
        color: #e2e8f0;
        position: relative;
        overflow: hidden;
    }
    
    .forgot-password-container::before {
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
    
    .forgot-password-header {
        text-align: center;
        margin-bottom: 32px;
        position: relative;
        z-index: 1;
    }
    
    .forgot-password-header h1 {
        font-size: 2rem;
        font-weight: 800;
        color: #f8fafc;
        margin-bottom: 10px;
        letter-spacing: -0.02em;
    }
    
    .forgot-password-header p {
        font-size: 0.95rem;
        color: #cbd5e1;
    }
    
    .form-group {
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
    }
    
    .form-label {
        font-weight: 600;
        color: #cbd5e1;
        margin-bottom: 8px;
        font-size: 0.95rem;
        display: block;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid rgba(226, 232, 240, 0.35);
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: rgba(255,255,255,0.04);
        color: #ffffff !important;
    }
    
    .form-control::placeholder {
        color: #94a3b8;
    }
    
    .form-control:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        outline: none;
        background: rgba(255,255,255,0.06);
    }
    
    .btn-submit {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.2s ease;
        margin-top: 14px;
        letter-spacing: 0.01em;
        box-shadow: 0 18px 32px rgba(99, 102, 241, 0.35);
        cursor: pointer;
    }
    
    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 22px 36px rgba(99, 102, 241, 0.42);
    }
    
    .btn-submit:active {
        transform: translateY(0);
        box-shadow: 0 14px 28px rgba(99, 102, 241, 0.35);
    }
    
    .text-danger {
        font-size: 0.85rem;
        margin-top: 5px;
        color: #fecdd3;
    }
    
    .is-invalid {
        border-color: #f87171;
    }
    
    .alert {
        padding: 12px 14px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 0.92rem;
        position: relative;
        z-index: 1;
    }
    
    .alert-success {
        background: rgba(34, 197, 94, 0.12);
        color: #bbf7d0;
        border: 1px solid rgba(74, 222, 128, 0.4);
    }
    
    .alert-danger {
        background: rgba(248, 113, 113, 0.12);
        color: #fecdd3;
        border: 1px solid rgba(252, 165, 165, 0.45);
    }
    
    .back-link {
        text-align: center;
        margin-top: 20px;
        position: relative;
        z-index: 1;
    }
    
    .back-link a {
        color: #c7d2fe;
        text-decoration: none;
        font-weight: 600;
        padding: 10px 12px;
        border-radius: 8px;
        display: inline-block;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }
    
    .back-link a:hover {
        background: rgba(255,255,255,0.08);
        text-decoration: none;
        color: #e0e7ff;
    }
    
    @media (max-width: 768px) {
        main {
            padding: 30px 20px;
        }
        
        .forgot-password-container {
            padding: 34px 22px;
        }
        
        .forgot-password-header h1 {
            font-size: 1.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="forgot-password-container">
    <div class="forgot-password-header">
        <h1>Forgot Password</h1>
        <p>Enter your email address and we'll send you a link to reset your password.</p>
    </div>
    
    @if (session('status'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('status') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    
    <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" novalidate>
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
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <button type="submit" class="btn-submit">
            Send Password Reset Link
        </button>
    </form>
    
    <div class="back-link">
        <a href="{{ route('login') }}">
            ← Back to Login
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script>
$(function() {
    $('#forgotPasswordForm').validate({
        errorElement: 'div',
        errorClass: 'text-danger',
        rules: {
            email: { required: true, email: true }
        },
        messages: {
            email: { required: 'Email is required', email: 'Enter a valid email address' }
        },
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        }
    });
});
</script>
@endpush
