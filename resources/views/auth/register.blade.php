@extends('layouts.frontend')

@section('title', 'Exhibitor Registration - ' . config('app.name', 'EMS'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* Only override body background, keep header/footer styles intact */
    body {
        background: radial-gradient(circle at 20% 20%, #eef2ff 0, #f8fafc 45%), 
                    radial-gradient(circle at 80% 0%, #e0f2fe 0, #f8fafc 40%), 
                    #f8fafc !important;
    }
    
    /* Ensure navbar and footer maintain their styles */
    body .navbar {
        background: white !important;
    }
    body footer {
        background-color: #6B3FA0 !important;
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
    
    .input-group-text {
        min-width: 60px;
        text-align: center;
        font-weight: 500;
    }
    
    .input-group select.form-select {
        font-size: 0.95rem;
    }
    
    .input-group select.form-select option {
        color: #0f172a;
        background: #ffffff;
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
                        <div style="display: flex; gap: 0;">
                            <div style="flex: 0 0 120px;">
                                <select 
                                    class="form-select @error('mobile_phone_code') is-invalid @enderror" 
                                    id="mobile_phone_code" 
                                    name="mobile_phone_code" 
                                    required
                                    style="width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(226, 232, 240, 0.35); color: #ffffff; border-right: 2px solid rgba(226, 232, 240, 0.5); border-radius: 10px 0 0 10px;">
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
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <div class="input-group" style="display: flex; gap: 0;">
                            <div style="flex: 0 0 120px;">
                                <select 
                                    class="form-select @error('phone_phone_code') is-invalid @enderror" 
                                    id="phone_phone_code" 
                                    name="phone_phone_code" 
                                    style="width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(226, 232, 240, 0.35); color: #ffffff; border-right: 2px solid rgba(226, 232, 240, 0.5); border-radius: 10px 0 0 10px;">
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
                                        $isSelected = old('phone_phone_code', '91') == $phoneCode;
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
                            @error('phone_phone_code')
                                <div class="text-danger" style="font-size: 0.85rem; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                            </div>
                            <div style="flex: 1;">
                                <input 
                                    type="tel" 
                                    class="form-control @error('phone_number') is-invalid @enderror" 
                                    id="phone_number" 
                                    name="phone_number" 
                                    value="{{ old('phone_number') }}" 
                                    placeholder="phone number"
                                    style="border-left: 2px solid rgba(226, 232, 240, 0.5); border-radius: 0 10px 10px 0;">
                                @error('phone_number')
                                    <div class="text-danger" style="font-size: 0.85rem; margin-top: 4px;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @error('phone_phone_code')
                            <div class="text-danger" style="font-size: 0.85rem; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                        @error('phone_phone_code')
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
                        <label for="country" class="form-label">Country</label>
                        <select 
                            class="form-select @error('country') is-invalid @enderror" 
                            id="country" 
                            name="country" 
                            required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                @php
                                    $phoneCode = !empty($country->phone_code) ? $country->phone_code : (!empty($country->phonecode) ? $country->phonecode : '');
                                    $emoji = $country->emoji ?? '';
                                    $displayName = $emoji ? $emoji . ' ' . $country->name : $country->name;
                                @endphp
                                <option
                                    value="{{ $country->id }}"
                                    data-id="{{ $country->id }}"
                                    data-phone-code="{{ $phoneCode }}"
                                    data-emoji="{{ $emoji }}"
                                    {{ old('country') == $country->id ? 'selected' : '' }}
                                >
                                    {{ $displayName }}
                                </option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="state" class="form-label">State</label>
                        <select 
                            class="form-select @error('state') is-invalid @enderror" 
                            id="state" 
                            name="state" 
                            required
                            data-old-value="{{ old('state') }}"
                            data-value-field="id">
                            <option value="">Select State</option>
                        </select>
                        @error('state')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
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
            </div>
            
            <div class="row">
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
                <div class="col-md-8">
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
<script>
    // Set API URL for country-state.js
    window.statesApiUrl = '{{ route("api.states") }}';
</script>
<script src="{{ asset('js/country-state.js') }}"></script>
<script>
// Function to make select dropdowns searchable/filterable
function makeSelectSearchable(selectId, searchPlaceholder) {
    const select = document.getElementById(selectId);
    if (!select) return;
    
    // Prevent multiple initializations
    if (select.hasAttribute('data-searchable-initialized')) {
        return;
    }
    select.setAttribute('data-searchable-initialized', 'true');
    
    // Store original options and remove duplicates
    let originalOptions = Array.from(select.options).map(opt => ({
        value: opt.value,
        text: opt.textContent,
        selected: opt.selected,
        dataPhoneCode: opt.getAttribute('data-phone-code'),
        dataEmoji: opt.getAttribute('data-emoji'),
        dataId: opt.getAttribute('data-id')
    }));
    
    // Remove duplicates based on value
    const seen = new Set();
    originalOptions = originalOptions.filter(opt => {
        if (opt.value === '') return true; // Always keep placeholder
        if (seen.has(opt.value)) return false;
        seen.add(opt.value);
        return true;
    });
    
    // Prevent default dropdown and add search functionality
    select.addEventListener('mousedown', function(e) {
        e.preventDefault();
        showSearch();
    });
    
    select.addEventListener('focus', function(e) {
        e.preventDefault();
        showSearch();
    });
    
    select.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            showSearch();
        }
    });
    
    function showSearch() {
        // Remove any existing overlay first
        const existingOverlay = document.getElementById(selectId + '_search_overlay');
        if (existingOverlay && existingOverlay.parentNode) {
            existingOverlay.parentNode.removeChild(existingOverlay);
        }
        
        // Create search overlay
        const overlay = document.createElement('div');
        overlay.id = selectId + '_search_overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.3)';
        overlay.style.zIndex = '9998';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        
        // Create search container
        const container = document.createElement('div');
        container.style.position = 'relative';
        container.style.width = '90%';
        container.style.maxWidth = '500px';
        container.style.backgroundColor = '#1e293b';
        container.style.borderRadius = '12px';
        container.style.padding = '20px';
        container.style.boxShadow = '0 20px 60px rgba(0,0,0,0.5)';
        
        // Create search input
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = searchPlaceholder || 'Type to search...';
        searchInput.style.width = '100%';
        searchInput.style.padding = '12px 16px';
        searchInput.style.border = '1px solid rgba(226, 232, 240, 0.35)';
        searchInput.style.borderRadius = '10px';
        searchInput.style.backgroundColor = 'rgba(255,255,255,0.08)';
        searchInput.style.color = '#ffffff';
        searchInput.style.fontSize = '1rem';
        searchInput.style.marginBottom = '12px';
        searchInput.autofocus = true;
        
        // Create results container
        const results = document.createElement('div');
        results.id = selectId + '_results';
        results.style.maxHeight = '300px';
        results.style.overflowY = 'auto';
        results.style.border = '1px solid rgba(226, 232, 240, 0.2)';
        results.style.borderRadius = '8px';
        results.style.backgroundColor = 'rgba(255,255,255,0.04)';
        
        container.appendChild(searchInput);
        container.appendChild(results);
        overlay.appendChild(container);
        document.body.appendChild(overlay);
        
        // Auto-focus search input after a small delay to ensure DOM is ready
        setTimeout(function() {
            searchInput.focus();
        }, 100);
        
        // Filter and display options
        function displayOptions(filterTerm = '') {
            results.innerHTML = '';
            const term = filterTerm.toLowerCase().trim();
            const displayedValues = new Set(); // Track displayed values to prevent duplicates
            
            originalOptions.forEach(function(opt) {
                // Skip if already displayed (duplicate)
                if (opt.value !== '' && displayedValues.has(opt.value)) {
                    return;
                }
                
                if (opt.value === '') {
                    // Always show placeholder
                    const item = createOptionItem(opt, '');
                    results.appendChild(item);
                    return;
                }
                
                if (!term) {
                    // Show all if no filter
                    displayedValues.add(opt.value);
                    const item = createOptionItem(opt, '');
                    results.appendChild(item);
                    return;
                }
                
                // Check if matches
                const optText = opt.text.toLowerCase();
                const optValue = opt.value.toLowerCase();
                const phoneCode = (opt.dataPhoneCode || optValue).replace(/\+/g, '').replace(/\s/g, '');
                const searchClean = term.replace(/\+/g, '').replace(/\s/g, '');
                
                if (optText.includes(term) || optValue.includes(term) || phoneCode.includes(searchClean)) {
                    displayedValues.add(opt.value);
                    const item = createOptionItem(opt, term);
                    results.appendChild(item);
                }
            });
        }
        
        function createOptionItem(opt, highlightTerm) {
            const item = document.createElement('div');
            item.style.padding = '12px 16px';
            item.style.cursor = 'pointer';
            item.style.borderBottom = '1px solid rgba(226, 232, 240, 0.1)';
            item.style.color = '#e2e8f0';
            item.style.transition = 'background 0.2s';
            
            if (opt.value === select.value) {
                item.style.backgroundColor = 'rgba(99, 102, 241, 0.2)';
            }
            
            // Add hover effect for focused option
            item.addEventListener('mouseenter', function() {
                this.classList.add('focused-option');
            });
            
            item.textContent = opt.text;
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(99, 102, 241, 0.15)';
                // Remove focus from other options
                results.querySelectorAll('.focused-option').forEach(el => el.classList.remove('focused-option'));
                this.classList.add('focused-option');
            });
            item.addEventListener('mouseleave', function() {
                if (opt.value !== select.value) {
                    this.style.backgroundColor = 'transparent';
                }
            });
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Set the value
                select.value = opt.value;
                
                // Close overlay immediately - use the overlay from parent scope
                const overlayElement = document.getElementById(selectId + '_search_overlay');
                if (overlayElement && overlayElement.parentNode) {
                    overlayElement.parentNode.removeChild(overlayElement);
                }
                
                // Trigger change event after overlay is removed
                setTimeout(function() {
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }, 50);
            });
            
            return item;
        }
        
        // Add CSS for focused option
        const style = document.createElement('style');
        style.textContent = `
            .focused-option {
                background-color: rgba(99, 102, 241, 0.25) !important;
            }
        `;
        document.head.appendChild(style);
        
        // Initial display
        displayOptions();
        
        // Filter on input
        searchInput.addEventListener('input', function() {
            displayOptions(this.value);
            // Remove focus from options when filtering
            results.querySelectorAll('.focused-option').forEach(el => el.classList.remove('focused-option'));
        });
        
        // Close on overlay click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                if (overlay && overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }
        });
        
        // Handle keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (overlay && overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                // Select first visible option
                const firstOption = results.querySelector('div[style*="cursor: pointer"]');
                if (firstOption) {
                    firstOption.click();
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                // Focus first option or move to next
                const options = Array.from(results.querySelectorAll('div[style*="cursor: pointer"]'));
                if (options.length > 0) {
                    const currentFocused = results.querySelector('.focused-option');
                    if (currentFocused) {
                        currentFocused.classList.remove('focused-option');
                        const currentIndex = options.indexOf(currentFocused);
                        if (currentIndex < options.length - 1) {
                            options[currentIndex + 1].classList.add('focused-option');
                            options[currentIndex + 1].scrollIntoView({ block: 'nearest' });
                        }
                    } else {
                        options[0].classList.add('focused-option');
                        options[0].scrollIntoView({ block: 'nearest' });
                    }
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const options = Array.from(results.querySelectorAll('div[style*="cursor: pointer"]'));
                if (options.length > 0) {
                    const currentFocused = results.querySelector('.focused-option');
                    if (currentFocused) {
                        currentFocused.classList.remove('focused-option');
                        const currentIndex = options.indexOf(currentFocused);
                        if (currentIndex > 0) {
                            options[currentIndex - 1].classList.add('focused-option');
                            options[currentIndex - 1].scrollIntoView({ block: 'nearest' });
                        }
                    }
                }
            }
        });
    }
    
    // Update originalOptions when select options change (for state dropdown)
    const observer = new MutationObserver(function() {
        const newOptions = Array.from(select.options).map(opt => ({
            value: opt.value,
            text: opt.textContent,
            selected: opt.selected,
            dataPhoneCode: opt.getAttribute('data-phone-code'),
            dataEmoji: opt.getAttribute('data-emoji'),
            dataId: opt.getAttribute('data-id')
        }));
        
        // Remove duplicates
        const seen = new Set();
        originalOptions = newOptions.filter(opt => {
            if (opt.value === '') return true; // Always keep placeholder
            if (seen.has(opt.value)) return false;
            seen.add(opt.value);
            return true;
        });
    });
    observer.observe(select, { childList: true });
}

$(function() {
    // Make country dropdown searchable
    makeSelectSearchable('country', 'Type country name...');
    
    // Make phone code dropdowns searchable
    makeSelectSearchable('mobile_phone_code', 'Type phone code (e.g., 91, 92)...');
    makeSelectSearchable('phone_phone_code', 'Type phone code (e.g., 91, 92)...');
    
    // Make state dropdown searchable (will be initialized after states are loaded)
    setTimeout(function() {
        const stateSelect = document.getElementById('state');
        if (stateSelect && !stateSelect.hasAttribute('data-searchable')) {
            stateSelect.setAttribute('data-searchable', 'true');
            makeSelectSearchable('state', 'Type state name...');
        }
    }, 500);
    
    if (typeof applyCountryState === 'function') {
        applyCountryState();
        
        // Re-initialize state searchable after states are loaded
        const countrySelect = document.getElementById('country');
        if (countrySelect) {
            countrySelect.addEventListener('change', function() {
                setTimeout(function() {
                    const stateSelect = document.getElementById('state');
                    if (stateSelect) {
                        // Remove old searchable if exists
                        const oldOverlay = document.getElementById('state_search_overlay');
                        if (oldOverlay) {
                            oldOverlay.remove();
                        }
                        // Remove initialization flag to allow re-initialization
                        stateSelect.removeAttribute('data-searchable-initialized');
                        // Re-initialize
                        makeSelectSearchable('state', 'Type state name...');
                    }
                }, 400);
            });
        }
    }

    // Prevent user from typing + in phone inputs
    $('#mobile_number, #phone_number').on('keypress', function(e) {
        if (e.key === '+') {
            e.preventDefault();
        }
    });
    
    // Also prevent pasting + at the start
    $('#mobile_number, #phone_number').on('paste', function(e) {
        const paste = (e.originalEvent || e).clipboardData.getData('text');
        if (paste.startsWith('+')) {
            e.preventDefault();
            const currentValue = $(this).val();
            $(this).val(currentValue + paste.substring(1));
        }
    });

    $('#registerForm').validate({
        ignore: [],
        errorElement: 'div',
        errorClass: 'text-danger',
        rules: {
            company_name: { required: true, minlength: 2 },
            name: { required: true, minlength: 2 },
            email: { required: true, email: true },
            mobile_phone_code: { required: true },
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
            mobile_phone_code: { required: 'Please select phone code' },
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
