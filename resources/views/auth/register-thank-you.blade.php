@extends('layouts.frontend')

@section('title', 'Registration Successful - ' . config('app.name', 'EMS'))

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
    
    .thankyou-container {
        max-width: 720px;
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
    
    .thankyou-container::before {
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
    
    .thankyou-header {
        text-align: center;
        margin-bottom: 28px;
        position: relative;
        z-index: 1;
    }
    
    .thankyou-header .icon {
        width: 88px;
        height: 88px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.6rem;
        color: #fff;
        box-shadow: 0 12px 32px rgba(34, 197, 94, 0.4);
    }
    
    .thankyou-header h1 {
        font-size: 2rem;
        font-weight: 800;
        color: #f8fafc;
        margin-bottom: 10px;
        letter-spacing: -0.02em;
    }
    
    .thankyou-header p {
        font-size: 1rem;
        color: #cbd5e1;
    }
    
    .thankyou-body {
        position: relative;
        z-index: 1;
    }
    
    .thankyou-message {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(226, 232, 240, 0.18);
        border-radius: 14px;
        padding: 20px 22px;
        margin-bottom: 24px;
        color: #cbd5e1;
        font-size: 0.95rem;
        line-height: 1.7;
    }
    
    .thankyou-message strong {
        color: #e5e7eb;
    }
    
    .next-steps-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }
    
    .next-steps-list li {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 10px;
        font-size: 0.95rem;
        color: #e5e7eb;
    }
    
    .next-steps-list li i {
        color: #6366f1;
        margin-top: 2px;
    }
    
    .thankyou-actions {
        margin-top: 26px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .btn-primary-link {
        flex: 1;
        min-width: 200px;
        padding: 12px 22px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border-radius: 12px;
        color: #fff;
        font-weight: 700;
        font-size: 0.98rem;
        text-decoration: none;
        text-align: center;
        box-shadow: 0 18px 32px rgba(99, 102, 241, 0.35);
        transition: all 0.2s ease;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary-link:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 22px 36px rgba(99, 102, 241, 0.45);
    }
    
    .btn-secondary-link {
        flex: 1;
        min-width: 200px;
        padding: 12px 22px;
        border-radius: 12px;
        border: 1px solid rgba(226, 232, 240, 0.28);
        color: #e5e7eb;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        text-align: center;
        background: rgba(15,23,42,0.4);
        transition: all 0.2s ease;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }
    
    .btn-secondary-link:hover {
        color: #f9fafb;
        background: rgba(15,23,42,0.65);
        text-decoration: none;
    }
    
    @media (max-width: 768px) {
        main {
            padding: 30px 20px;
        }
        
        .thankyou-container {
            padding: 34px 24px;
        }
        
        .thankyou-header h1 {
            font-size: 1.75rem;
        }
        
        .thankyou-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="thankyou-container">
    <div class="thankyou-header">
        <div class="icon">
            <i class="bi bi-check2-circle"></i>
        </div>
        <h1>Registration Successful</h1>
        <p>Your exhibitor account has been created.</p>
    </div>

    <div class="thankyou-body">
        <div class="thankyou-message">
            <p class="mb-1"><strong>Next step: verify your email address.</strong></p>
            <p class="mb-0">
                We have sent a verification link to <strong>{{ $email }}</strong>.
                Please open your inbox and click on the verification link to activate your account.
                You will not be able to log in until your email is verified.
            </p>
        </div>

        <ul class="next-steps-list mb-0">
            <li>
                <i class="bi bi-envelope-open"></i>
                <span>Check your inbox (and spam / promotions folder) for the verification email.</span>
            </li>
            <li>
                <i class="bi bi-link-45deg"></i>
                <span>Click the verification link in the email to confirm your email address.</span>
            </li>
            <li>
                <i class="bi bi-box-arrow-in-right"></i>
                <span>After successful verification, log in using your email and password.</span>
            </li>
        </ul>

        <div class="thankyou-actions">
            <a href="{{ route('login') }}" class="btn-primary-link">
                <i class="bi bi-box-arrow-in-right"></i>
                Go to Login Page
            </a>
            <a href="{{ route('verification.resend.show') }}" class="btn-secondary-link">
                <i class="bi bi-envelope-paper"></i>
                Didn’t receive email? Resend
            </a>
        </div>
    </div>
</div>
@endsection
