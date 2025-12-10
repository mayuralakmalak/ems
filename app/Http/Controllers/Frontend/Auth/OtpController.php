<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login-otp');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:15',
        ]);

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        OtpVerification::updateOrCreate(
            ['phone' => $request->phone, 'type' => 'login'],
            [
                'otp' => $otp,
                'expires_at' => $expiresAt,
                'is_verified' => false,
            ]
        );

        // TODO: Integrate with SMS gateway (DLT registered)
        // For now, we'll return the OTP in development
        if (app()->environment('local')) {
            return back()->with('otp_sent', true)->with('otp', $otp)->with('phone', $request->phone);
        }

        return back()->with('success', 'OTP sent to your phone number');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $otpRecord = OtpVerification::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->where('type', 'login')
            ->where('expires_at', '>', now())
            ->where('is_verified', false)
            ->first();

        if (!$otpRecord) {
            return back()->with('error', 'Invalid or expired OTP');
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'name' => 'User ' . substr($request->phone, -4),
                'email' => 'user_' . Str::random(8) . '@example.com',
                'password' => Hash::make(Str::random(16)),
            ]
        );

        if (!$user->hasRole('Exhibitor')) {
            $user->assignRole('Exhibitor');
        }

        // Mark OTP as verified
        $otpRecord->update(['is_verified' => true]);

        auth()->login($user);

        // Redirect exhibitors to frontend homepage instead of dashboard
        return redirect()->route('home')->with('success', 'Logged in successfully!');
    }
}
