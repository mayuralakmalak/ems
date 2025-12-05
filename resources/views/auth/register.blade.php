<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Exhibitor Registration - {{ config('app.name', 'EMS') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .register-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 50px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .register-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .register-header p {
            font-size: 1.1rem;
            color: #64748b;
        }
        
        .form-section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .section-description {
            font-size: 0.95rem;
            color: #64748b;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 500;
            color: #334155;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-control, .form-select {
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
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
            background: #6366f1;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-register:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }
        
        .text-danger {
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .is-invalid {
            border-color: #ef4444;
        }
        
        @media (max-width: 768px) {
            .register-container {
                padding: 30px 20px;
            }
            
            .register-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
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
        
        <form method="POST" action="{{ route('register') }}">
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
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
