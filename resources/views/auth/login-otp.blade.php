@extends('layouts.guest')

@section('content')
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('login') }}">Email/Password</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">OTP Login</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        @if(session('otp_sent'))
        <div class="alert alert-info">
            @if(app()->environment('local'))
                <strong>Development Mode:</strong> Your OTP is: <strong>{{ session('otp') }}</strong>
            @else
                OTP has been sent to {{ session('phone') }}
            @endif
        </div>
        @endif

        @if(session('otp_sent'))
        <form method="POST" action="{{ route('otp.verify') }}">
            @csrf
            <input type="hidden" name="phone" value="{{ session('phone') }}">
            <div class="mb-3">
                <label class="form-label">Enter OTP</label>
                <input type="text" name="otp" class="form-control" maxlength="6" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
            <a href="{{ route('login.otp') }}" class="btn btn-link w-100">Resend OTP</a>
        </form>
        @else
        <form method="POST" action="{{ route('otp.send') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="+1234567890" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send OTP</button>
        </form>
        @endif
    </div>
</div>
@endsection

