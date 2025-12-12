@extends('layouts.frontend')

@section('title', 'Exhibitor Registration - ' . config('app.name', 'EMS'))

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
    }
    
    .register-container {
        max-width: 980px;
        margin: 0 auto;
        background: linear-gradient(135deg, rgba(17,24,39,0.95) 0%, rgba(15,23,42,0.92) 40%, rgba(15,23,42,0.9) 100%);
        border-radius: 20px;
        box-shadow: 0 28px 60px rgba(15, 23, 42, 0.35);
        padding: 48px 46px;
        color: #e2e8f0;
        position: relative;
        overflow: hidden;
    }
    
    .register-container::before {
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
    
    .register-header {
        text-align: center;
        margin-bottom: 40px;
        position: relative;
        z-index: 1;
    }
    
    .register-header h1 {
        font-size: 2.4rem;
        font-weight: 800;
        color: #f8fafc;
        margin-bottom: 10px;
        letter-spacing: -0.02em;
    }
    
    .register-header p {
        font-size: 1.1rem;
        color: #cbd5e1;
    }
    
    .form-section {
        margin-bottom: 40px;
        position: relative;
        z-index: 1;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #e2e8f0;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title::before {
        content: "";
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        box-shadow: 0 0 0 6px rgba(99,102,241,0.18);
    }
    
    .section-description {
        font-size: 0.95rem;
        color: #cbd5e1;
        margin-bottom: 22px;
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
    
    .form-control, .form-select {
        padding: 12px 16px;
        border: 1px solid rgba(226, 232, 240, 0.35);
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: rgba(255,255,255,0.04);
        color: #ffffff !important;
    }
    .form-select option {
        color: #0f172a;
    }
    
    .form-control::placeholder, .form-select::placeholder {
        color: #94a3b8;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        outline: none;
        background: rgba(255,255,255,0.06);
    }
    
    .input-group {
        position: relative;
    }
    
    .input-group .input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        z-index: 10;
    }
    
    .input-group .form-control {
        padding-left: 45px;
    }
    
    .btn-register {
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
    
    .btn-register:hover {
        transform: translateY(-1px);
        box-shadow: 0 22px 36px rgba(99, 102, 241, 0.42);
    }
    
    .btn-register:active {
        transform: translateY(0);
        box-shadow: 0 14px 28px rgba(99, 102, 241, 0.35);
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
    
    .form-check-input:checked {
        background-color: #6366f1;
        border-color: #6366f1;
    }
    
    .form-check-label {
        color: #cbd5e1;
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
        border-radius: 10px;
        position: relative;
        z-index: 1;
    }
    
    @media (max-width: 768px) {
        main {
            padding: 30px 20px;
        }
        
        .register-container {
            padding: 34px 22px;
        }
        
        .register-header h1 {
            font-size: 2.05rem;
        }
    }
</style>
@endpush

@section('content')
<div class="register-container">
    <div class="register-header">
        <h1>Exhibitor Registration</h1>
        <p>Join our exhibition to enhance your exhibition management!</p>
    </div>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    
    <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
        @csrf
        
        <!-- Company Details Section -->
        <div class="form-section">
            <h3 class="section-title">Company Details</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="company_name" class="form-label">Company name</label>
                        <input 
                            type="text" 
                            class="form-control @error('company_name') is-invalid @enderror" 
                            id="company_name" 
                            name="company_name" 
                            value="{{ old('company_name') }}" 
                            required
                            placeholder="company name">
                        @error('company_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="company_website" class="form-label">Company website</label>
                        <input 
                            type="url" 
                            class="form-control @error('company_website') is-invalid @enderror" 
                            id="company_website" 
                            name="company_website" 
                            value="{{ old('company_website') }}" 
                            placeholder="company website">
                        @error('company_website')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Person Section -->
        <div class="form-section">
            <h3 class="section-title">Contact Person</h3>
            <p class="section-description">Key contact for booking and coordination.</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}" 
                            required
                            autofocus
                            placeholder="full name">
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="designation" class="form-label">Designation</label>
                        <input 
                            type="text" 
                            class="form-control @error('designation') is-invalid @enderror" 
                            id="designation" 
                            name="designation" 
                            value="{{ old('designation') }}" 
                            placeholder="designation">
                        @error('designation')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input 
                            type="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required
                            placeholder="email">
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="mobile_number" class="form-label">Mobile Number</label>
                        <div class="input-group">
                            <i class="bi bi-phone-fill input-icon"></i>
                            <input 
                                type="tel" 
                                class="form-control @error('mobile_number') is-invalid @enderror" 
                                id="mobile_number" 
                                name="mobile_number" 
                                value="{{ old('mobile_number') }}" 
                                required
                                placeholder="mobile number should start with +91">
                        </div>
                        @error('mobile_number')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <i class="bi bi-telephone-fill input-icon"></i>
                            <input 
                                type="tel" 
                                class="form-control @error('phone_number') is-invalid @enderror" 
                                id="phone_number" 
                                name="phone_number" 
                                value="{{ old('phone_number') }}" 
                                placeholder="phone number">
                        </div>
                        @error('phone_number')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            type="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="password">
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required
                            placeholder="confirm password">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="company_address" class="form-label">Company Address</label>
                <input 
                    type="text" 
                    class="form-control @error('company_address') is-invalid @enderror" 
                    id="company_address" 
                    name="company_address" 
                    value="{{ old('company_address') }}" 
                    required
                    placeholder="company address">
                @error('company_address')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="city" class="form-label">City</label>
                        <input 
                            type="text" 
                            class="form-control @error('city') is-invalid @enderror" 
                            id="city" 
                            name="city" 
                            value="{{ old('city') }}" 
                            required
                            placeholder="city">
                        @error('city')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="country" class="form-label">Country</label>
                        <input 
                            type="text" 
                            class="form-control @error('country') is-invalid @enderror" 
                            id="country" 
                            name="country" 
                            value="{{ old('country') }}" 
                            required
                            placeholder="country">
                        @error('country')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="zip_code" class="form-label">Zip Code</label>
                        <input 
                            type="text" 
                            class="form-control @error('zip_code') is-invalid @enderror" 
                            id="zip_code" 
                            name="zip_code" 
                            value="{{ old('zip_code') }}" 
                            required
                            placeholder="zip code">
                        @error('zip_code')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="form-label">State</label>
                        <input 
                            type="text" 
                            class="form-control @error('state') is-invalid @enderror" 
                            id="state" 
                            name="state" 
                            value="{{ old('state') }}" 
                            required
                            placeholder="state">
                        @error('state')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="industry_category" class="form-label">Industry Category</label>
                        <select 
                            class="form-select @error('industry_category') is-invalid @enderror" 
                            id="industry_category" 
                            name="industry_category">
                            <option value="">Select industry category</option>
                            <option value="Technology" {{ old('industry_category') == 'Technology' ? 'selected' : '' }}>Technology</option>
                            <option value="Healthcare" {{ old('industry_category') == 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                            <option value="Finance" {{ old('industry_category') == 'Finance' ? 'selected' : '' }}>Finance</option>
                            <option value="Retail" {{ old('industry_category') == 'Retail' ? 'selected' : '' }}>Retail</option>
                            <option value="Manufacturing" {{ old('industry_category') == 'Manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                            <option value="Other" {{ old('industry_category') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('industry_category')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Terms & Conditions -->
        <div class="form-group">
            <div class="form-check">
                <input 
                    class="form-check-input @error('terms') is-invalid @enderror" 
                    type="checkbox" 
                    id="terms" 
                    name="terms" 
                    required>
                <label class="form-check-label" for="terms">
                    I agree to the terms & conditions
                </label>
            </div>
            @error('terms')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <button type="submit" class="btn btn-register">
            Register your exhibitor account
        </button>
        
        <div class="login-link">
            <a href="{{ route('login') }}">
                Already have an account? Sign in
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('js/country-state.js') }}"></script>
<script>
$(function() {
    if (typeof applyCountryState === 'function') {
        applyCountryState();
    }

    $('#registerForm').validate({
        ignore: [],
        errorElement: 'div',
        errorClass: 'text-danger',
        rules: {
            company_name: { required: true, minlength: 2 },
            name: { required: true, minlength: 2 },
            email: { required: true, email: true },
            mobile_number: { required: true, minlength: 8 },
            phone_number: { minlength: 6 },
            password: { required: true, minlength: 6 },
            password_confirmation: { required: true, equalTo: '#password' },
            company_address: { required: true },
            city: { required: true },
            country: { required: true },
            state: { required: true },
            zip_code: { required: true },
            industry_category: { required: true },
            terms: { required: true }
        },
        messages: {
            company_name: { required: 'Company name is required' },
            name: { required: 'Full name is required' },
            email: { required: 'Email is required', email: 'Enter a valid email' },
            mobile_number: { required: 'Mobile number is required' },
            password: { required: 'Password is required', minlength: 'Minimum 6 characters' },
            password_confirmation: { equalTo: 'Passwords must match' },
            company_address: { required: 'Company address is required' },
            city: { required: 'City is required' },
            country: { required: 'Select a country' },
            state: { required: 'Select a state' },
            zip_code: { required: 'Zip code is required' },
            industry_category: { required: 'Select an industry category' },
            terms: { required: 'You must agree to the terms' }
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
