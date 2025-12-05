<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ config('app.name', 'EMS') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --gradient-start: #6366f1;
            --gradient-end: #8b5cf6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: flex;
            min-height: 600px;
        }
        
        .register-left {
            flex: 1;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .register-left::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -150px;
            right: -150px;
        }
        
        .register-left::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -100px;
            left: -100px;
        }
        
        .register-left h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        
        .register-left p {
            font-size: 1.1rem;
            opacity: 0.95;
            line-height: 1.6;
            position: relative;
            z-index: 1;
            text-align: center;
        }
        
        .register-left .icon-container {
            font-size: 5rem;
            margin-bottom: 30px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .register-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
            max-height: 600px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .register-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: #64748b;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-control {
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 10;
        }
        
        .input-group .form-control {
            padding-left: 50px;
        }
        
        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }
        
        .btn-register:active {
            transform: translateY(0);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 14px 18px;
            margin-bottom: 25px;
        }
        
        .text-danger {
            font-size: 0.85rem;
            margin-top: 6px;
        }
        
        .is-invalid {
            border-color: #ef4444;
        }
        
        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
                min-height: auto;
            }
            
            .register-left {
                padding: 40px 30px;
                min-height: 250px;
            }
            
            .register-left h1 {
                font-size: 1.8rem;
            }
            
            .register-left .icon-container {
                font-size: 3rem;
                margin-bottom: 20px;
            }
            
            .register-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-left">
            <div class="icon-container">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <h1>Join Us Today!</h1>
            <p>Create your account and start booking exhibition spaces. Manage your bookings, payments, and documents all in one place.</p>
        </div>
        
        <div class="register-right">
            <div class="register-header">
                <h2>Create Account</h2>
                <p>Fill in your details to get started</p>
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
                
                <div class="form-group">
                    <label for="name" class="form-label">
                        <i class="bi bi-person me-1"></i>Full Name
                    </label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}" 
                            required 
                            autofocus 
                            autocomplete="name"
                            placeholder="Enter your full name">
                    </div>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-1"></i>Email Address
                    </label>
                    <div class="input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input 
                            type="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autocomplete="username"
                            placeholder="Enter your email">
                    </div>
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="bi bi-phone me-1"></i>Phone Number
                    </label>
                    <div class="input-group">
                        <i class="bi bi-phone-fill input-icon"></i>
                        <input 
                            type="tel" 
                            class="form-control @error('phone') is-invalid @enderror" 
                            id="phone" 
                            name="phone" 
                            value="{{ old('phone') }}" 
                            autocomplete="tel"
                            placeholder="Enter your phone number">
                    </div>
                    @error('phone')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-1"></i>Password
                    </label>
                    <div class="input-group">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input 
                            type="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="new-password"
                            placeholder="Enter your password">
                    </div>
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        <i class="bi bi-lock me-1"></i>Confirm Password
                    </label>
                    <div class="input-group">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required 
                            autocomplete="new-password"
                            placeholder="Confirm your password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-register">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </button>
                
                <div class="login-link">
                    <a href="{{ route('login') }}">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Already have an account? Sign in
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
