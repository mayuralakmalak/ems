<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in - {{ config('app.name', 'EMS') }}</title>
    
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .login-wrapper {
            display: flex;
            gap: 30px;
            max-width: 1200px;
            width: 100%;
            justify-content: center;
        }
        
        .login-form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .login-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 8px;
        }
        
        .toggle-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            border-radius: 6px;
            font-weight: 500;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .toggle-btn.active {
            background: #6366f1;
            color: white;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 500;
            color: #334155;
            margin-bottom: 8px;
            font-size: 0.95rem;
            display: block;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }
        
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #1e293b;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            background: #334155;
        }
        
        .btn-verify {
            width: 100%;
            padding: 12px;
            background: #1e293b;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-verify:hover {
            background: #334155;
        }
        
        .login-form {
            display: none;
        }
        
        .login-form.active {
            display: block;
        }
        
        .text-danger {
            font-size: 0.85rem;
            margin-top: 5px;
            color: #ef4444;
        }
        
        .is-invalid {
            border-color: #ef4444;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }
            
            .login-form-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- OTP Login Form -->
        <div class="login-form-container">
            <h2 class="login-title">Sign in</h2>
            
            <div class="login-toggle">
                <button type="button" class="toggle-btn active" onclick="showOtpForm()">Login with OTP</button>
                <button type="button" class="toggle-btn" onclick="showEmailForm()">Login with Email</button>
            </div>
            
            <div id="otpForm" class="login-form active">
                @if(session('otp_sent'))
                    <div class="alert alert-success">
                        OTP sent! Check your phone. OTP: <strong>{{ session('otp') }}</strong> (Development only)
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                
                <form method="POST" action="{{ route('otp.send') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="phone_otp" class="form-label">Phone</label>
                        <input 
                            type="tel" 
                            class="form-control @error('phone') is-invalid @enderror" 
                            id="phone_otp" 
                            name="phone" 
                            value="{{ old('phone', session('phone')) }}" 
                            required
                            placeholder="+91 97234567890">
                        @error('phone')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn-submit">Submit</button>
                </form>
                
                @if(session('otp_sent'))
                <form method="POST" action="{{ route('otp.verify') }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="phone" value="{{ session('phone') }}">
                    
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
            </div>
        </div>
        
        <!-- Email/Password Login Form -->
        <div class="login-form-container">
            <h2 class="login-title">Sign in</h2>
            
            <div class="login-toggle">
                <button type="button" class="toggle-btn" onclick="showOtpForm()">Login with OTP</button>
                <button type="button" class="toggle-btn active" onclick="showEmailForm()">Login with Email</button>
            </div>
            
            <div id="emailForm" class="login-form active">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}">
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
                    
                    <button type="submit" class="btn-submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function showOtpForm() {
            // Hide email form, show OTP form
            document.getElementById('emailForm').classList.remove('active');
            document.getElementById('otpForm').classList.add('active');
            
            // Update toggle buttons
            const containers = document.querySelectorAll('.login-form-container');
            containers.forEach(container => {
                const toggles = container.querySelectorAll('.toggle-btn');
                toggles[0].classList.add('active');
                toggles[1].classList.remove('active');
            });
        }
        
        function showEmailForm() {
            // Hide OTP form, show email form
            document.getElementById('otpForm').classList.remove('active');
            document.getElementById('emailForm').classList.add('active');
            
            // Update toggle buttons
            const containers = document.querySelectorAll('.login-form-container');
            containers.forEach(container => {
                const toggles = container.querySelectorAll('.toggle-btn');
                toggles[0].classList.remove('active');
                toggles[1].classList.add('active');
            });
        }
    </script>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
