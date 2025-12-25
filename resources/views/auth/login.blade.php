@extends('layouts.frontend')

@section('title', 'Sign in - ' . config('app.name', 'EMS'))

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
    
    .login-wrapper {
        display: flex;
        max-width: 520px;
        width: 100%;
        justify-content: center;
        align-items: center;
    }
    
    .login-form-container {
        background: #0f172a;
        background: linear-gradient(135deg, #0f172a 0%, #111827 40%, #1f2937 100%);
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
        padding: 36px;
        width: 100%;
        color: #e2e8f0;
        position: relative;
        overflow: hidden;
    }
    
    .login-form-container::before {
        content: "";
        position: absolute;
        top: -120px;
        right: -120px;
        width: 240px;
        height: 240px;
        background: radial-gradient(circle, rgba(99,102,241,0.25) 0%, rgba(99,102,241,0) 70%);
        transform: rotate(25deg);
        pointer-events: none;
    }
    
    .login-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #f8fafc;
        margin-bottom: 10px;
        text-align: center;
        letter-spacing: -0.02em;
        position: relative;
        z-index: 1;
    }
    
    .login-subtitle {
        text-align: center;
        color: #cbd5e1;
        font-size: 0.95rem;
        margin-bottom: 28px;
        position: relative;
        z-index: 1;
    }
    
    .login-toggle {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 28px;
        background: rgba(255,255,255,0.06);
        padding: 6px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.08);
        position: relative;
        z-index: 1;
    }
    
    .toggle-btn {
        padding: 12px;
        border: none;
        background: transparent;
        border-radius: 10px;
        font-weight: 600;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.2s ease;
        letter-spacing: 0.01em;
        font-size: 0.95rem;
    }
    
    .toggle-btn:hover {
        color: #cbd5e1;
    }
    
    .toggle-btn.active {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #f8fafc;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35);
    }
    
    .login-form {
        display: none;
        position: relative;
        z-index: 1;
    }
    
    .login-form.active {
        display: block;
    }
    
    .form-group {
        margin-bottom: 18px;
    }
    
    .form-label {
        font-weight: 600;
        color: #cbd5e1;
        margin-bottom: 8px;
        font-size: 0.95rem;
        display: block;
    }
    
    .form-control, .form-select {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid rgba(226, 232, 240, 0.35);
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: rgba(255,255,255,0.08);
        color: #ffffff !important;
        font-weight: 500;
    }
    
    .form-select {
        background-color: rgba(255,255,255,0.08);
        color: #ffffff !important;
    }
    
    .form-select option {
        background: #1f2937;
        color: #ffffff;
    }
    
    .input-group {
        display: flex;
        gap: 0;
    }
    
    .input-group .form-select {
        border-radius: 10px 0 0 10px;
        border-right: none;
    }
    
    .input-group .form-control {
        border-radius: 0 10px 10px 0;
        border-left: none;
    }
    
    .form-control::placeholder {
        color: #94a3b8;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        outline: none;
        background: rgba(255,255,255,0.12);
        color: #ffffff;
    }
    
    .form-control:-webkit-autofill,
    .form-control:-webkit-autofill:hover,
    .form-control:-webkit-autofill:focus {
        -webkit-text-fill-color: #ffffff !important;
        -webkit-box-shadow: 0 0 0px 1000px rgba(255,255,255,0.08) inset !important;
        transition: background-color 5000s ease-in-out 0s;
    }
    
    .btn-submit, .btn-verify {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 6px;
        letter-spacing: 0.01em;
        box-shadow: 0 14px 30px rgba(99, 102, 241, 0.35);
    }
    
    .btn-submit:hover, .btn-verify:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 32px rgba(99, 102, 241, 0.4);
    }
    
    .btn-submit:active, .btn-verify:active {
        transform: translateY(0);
        box-shadow: 0 10px 24px rgba(99, 102, 241, 0.35);
    }
    
    .text-danger {
        font-size: 0.85rem;
        margin-top: 5px;
        color: #f87171;
    }
    
    .is-invalid {
        border-color: #f87171;
    }
    
    .alert {
        padding: 12px 14px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 0.92rem;
        border: 1px solid transparent;
    }
    
    .alert-success {
        background: rgba(34, 197, 94, 0.12);
        color: #bbf7d0;
        border-color: rgba(74, 222, 128, 0.4);
    }
    
    .alert-danger {
        background: rgba(248, 113, 113, 0.12);
        color: #fecdd3;
        border-color: rgba(252, 165, 165, 0.45);
    }
    
    .register-links {
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid rgba(255,255,255,0.08);
    }
    
    .register-links p {
        font-size: 0.9rem;
        margin-bottom: 8px;
        color: #cbd5e1;
        text-align: center;
    }
    
    .register-link {
        display: block;
        text-align: center;
        color: #a5b4fc;
        text-decoration: none;
        font-weight: 600;
        padding: 10px;
        border-radius: 8px;
        transition: all 0.2s ease;
        font-size: 0.95rem;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.06);
    }
    
    .register-link:hover {
        background: rgba(255,255,255,0.08);
        color: #c7d2fe;
    }
    
    @media (max-width: 768px) {
        main {
            padding: 40px 20px;
        }
        
        .login-form-container {
            max-width: 100%;
            padding: 32px 26px;
        }
    }
</style>
@endpush

@section('content')
<div class="login-wrapper">
    <!-- Single Login Form Container -->
    <div class="login-form-container">
        <h2 class="login-title">Sign in</h2>
        <p class="login-subtitle">Access your dashboard with OTP or email.</p>
        
        <div class="login-toggle">
            <button type="button" class="toggle-btn {{ $errors->has('email') || $errors->has('password') ? '' : 'active' }}" id="otpTab" onclick="showOtpForm()">Login with OTP</button>
            <button type="button" class="toggle-btn {{ $errors->has('email') || $errors->has('password') ? 'active' : '' }}" id="emailTab" onclick="showEmailForm()">Login with Email</button>
        </div>
        
        <!-- OTP Login Form -->
        <div id="otpForm" class="login-form {{ $errors->has('email') || $errors->has('password') ? '' : 'active' }}">
            @if(session('otp_sent'))
                <div class="alert alert-success">
                    OTP sent! Check your phone. OTP: <strong>{{ session('otp') }}</strong> (Development only)
                </div>
            @endif
            
            <form method="POST" action="{{ route('otp.send') }}" id="otpLoginForm" novalidate>
                @csrf
                
                <div class="form-group">
                    <label for="mobile_number" class="form-label">Mobile Number</label>
                    <div style="display: flex; gap: 0;">
                        <div style="flex: 0 0 120px;">
                            <select 
                                class="form-select @error('mobile_phone_code') is-invalid @enderror" 
                                id="mobile_phone_code" 
                                name="mobile_phone_code" 
                                required
                                style="width: 100%; border-radius: 10px 0 0 10px; border-right: 2px solid rgba(226, 232, 240, 0.5);">
                                <option value="">Phone Code</option>
                                @foreach($countries as $country)
                                    @php
                                        $phoneCode = !empty($country->phone_code) ? $country->phone_code : (!empty($country->phonecode) ? $country->phonecode : '');
                                        $emoji = $country->emoji ?? '';
                                        $displayText = '';
                                        if ($phoneCode) {
                                            if ($emoji) {
                                                $displayText = $emoji . ' +' . $phoneCode;
                                            } else {
                                                $displayText = '+' . $phoneCode;
                                            }
                                        }
                                        $isSelected = old('mobile_phone_code', '91') == $phoneCode;
                                    @endphp
                                    @if($phoneCode)
                                        <option
                                            value="{{ $phoneCode }}"
                                            data-emoji="{{ $emoji }}"
                                            {{ $isSelected ? 'selected' : '' }}
                                        >
                                            {{ $displayText }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('mobile_phone_code')
                                <div class="text-danger" style="font-size: 0.85rem; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div style="flex: 1;">
                            <input 
                                type="tel" 
                                class="form-control @error('mobile_number') is-invalid @enderror" 
                                id="mobile_number" 
                                name="mobile_number" 
                                value="{{ old('mobile_number') }}" 
                                required
                                placeholder="mobile number"
                                style="border-left: 2px solid rgba(226, 232, 240, 0.5); border-radius: 0 10px 10px 0;">
                            @error('mobile_number')
                                <div class="text-danger" style="font-size: 0.85rem; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Submit</button>
            </form>
            
            @if(session('otp_sent'))
            <form method="POST" action="{{ route('otp.verify') }}" class="mt-4" id="verifyOtpForm" novalidate>
                @csrf
                
                <div class="form-group">
                    <label for="otp" class="form-label">OTP</label>
                    <input 
                        type="text" 
                        class="form-control @error('otp') is-invalid @enderror" 
                        id="otp" 
                        name="otp" 
                        required
                        maxlength="6"
                        placeholder="Enter OTP">
                    @error('otp')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <button type="submit" class="btn-verify">Verify</button>
            </form>
            @endif
            
            <div class="register-links">
                <p>New user?</p>
                <a href="{{ route('register') }}" class="register-link">Create an account / Register</a>
            </div>
        </div>
        
        <!-- Email/Password Login Form -->
        <div id="emailForm" class="login-form {{ $errors->has('email') || $errors->has('password') ? 'active' : '' }}">
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
            
            <form method="POST" action="{{ route('login') }}" id="emailLoginForm" novalidate>
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required
                        autofocus
                        placeholder="abc@gmail.com">
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="********">
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div style="text-align: right; margin-bottom: 12px;">
                    <a href="{{ route('password.request') }}" style="color: #a5b4fc; text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                        Forgot Password?
                    </a>
                </div>
                
                <button type="submit" class="btn-submit">Submit</button>
            </form>
            
            <div class="register-links">
                <p>New user?</p>
                <a href="{{ route('register') }}" class="register-link">Create an account / Register</a>
                <p style="margin-top: 12px; margin-bottom: 8px;">Didn't receive verification email?</p>
                <a href="{{ route('verification.resend.show') }}" class="register-link">Resend Verification Email</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showOtpForm() {
        // Hide email form, show OTP form
        document.getElementById('emailForm').classList.remove('active');
        document.getElementById('otpForm').classList.add('active');
        
        // Update toggle buttons
        document.getElementById('otpTab').classList.add('active');
        document.getElementById('emailTab').classList.remove('active');
    }
    
    function showEmailForm() {
        // Hide OTP form, show email form
        document.getElementById('otpForm').classList.remove('active');
        document.getElementById('emailForm').classList.add('active');
        
        // Update toggle buttons
        document.getElementById('otpTab').classList.remove('active');
        document.getElementById('emailTab').classList.add('active');
    }
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('js/country-state.js') }}"></script>
<script>
$(function() {
    if (typeof applyCountryState === 'function') {
        applyCountryState();
    }

    $('#otpLoginForm').validate({
        errorElement: 'div',
        errorClass: 'text-danger',
        rules: {
            mobile_phone_code: { required: true },
            mobile_number: { required: true, minlength: 8 }
        },
        messages: {
            mobile_phone_code: { required: 'Phone code is required' },
            mobile_number: { required: 'Mobile number is required' }
        }
    });

    $('#verifyOtpForm').validate({
        errorElement: 'div',
        errorClass: 'text-danger',
        rules: {
            otp: { required: true, minlength: 4, maxlength: 6 }
        },
        messages: {
            otp: { required: 'OTP is required' }
        }
    });

    $('#emailLoginForm').validate({
        errorElement: 'div',
        errorClass: 'text-danger',
        rules: {
            email: { required: true, email: true },
            password: { required: true }
        },
        messages: {
            email: { required: 'Email is required', email: 'Enter a valid email' },
            password: { required: 'Password is required' }
        }
    });
});
</script>
@endpush
