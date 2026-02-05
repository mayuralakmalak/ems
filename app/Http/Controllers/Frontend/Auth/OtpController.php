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
            'mobile_phone_code' => 'required|string|max:10',
            'mobile_number' => 'required|string|max:20',
        ]);

        // Clean the mobile number (remove any non-digit characters)
        $mobileNumber = preg_replace('/[^0-9]/', '', $request->mobile_number);
        $phoneCode = trim($request->mobile_phone_code);
        $phoneCode = ltrim($phoneCode, '+');
        $phoneCode = $phoneCode !== '' ? '+' . $phoneCode : '';

        // Normalize stored phone code for comparison (DB may have "+91" or "91")
        $normalizedCodeForQuery = $phoneCode;

        // Find user: try mobile_number + mobile_number_phone_code first
        $user = User::where('mobile_number', $mobileNumber)
            ->where(function ($q) use ($normalizedCodeForQuery) {
                $q->where('mobile_number_phone_code', $normalizedCodeForQuery)
                    ->orWhere('mobile_number_phone_code', ltrim($normalizedCodeForQuery, '+'))
                    ->orWhereRaw("REPLACE(REPLACE(mobile_number_phone_code, ' ', ''), '+', '') = ?", [ltrim($normalizedCodeForQuery, '+')]);
            })
            ->first();

        // If not found, try without leading zero (e.g. 09876543210 vs 9876543210)
        if (!$user && strlen($mobileNumber) === 11 && str_starts_with($mobileNumber, '0')) {
            $user = User::where('mobile_number', ltrim($mobileNumber, '0'))
                ->where(function ($q) use ($normalizedCodeForQuery) {
                    $q->where('mobile_number_phone_code', $normalizedCodeForQuery)
                        ->orWhere('mobile_number_phone_code', ltrim($normalizedCodeForQuery, '+'))
                        ->orWhereRaw("REPLACE(REPLACE(mobile_number_phone_code, ' ', ''), '+', '') = ?", [ltrim($normalizedCodeForQuery, '+')]);
                })
                ->first();
        }

        // Fallback: find by legacy full "phone" column (e.g. old users or different format)
        if (!$user) {
            $fullDigits = preg_replace('/[^0-9]/', '', $phoneCode . $mobileNumber);
            $user = User::whereNotNull('phone')
                ->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '+', ''), '-', '') = ?", [$fullDigits])
                ->first();
        }
        if (!$user && strlen($mobileNumber) === 11 && str_starts_with($mobileNumber, '0')) {
            $fullDigits = preg_replace('/[^0-9]/', '', $phoneCode . ltrim($mobileNumber, '0'));
            $user = User::whereNotNull('phone')
                ->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '+', ''), '-', '') = ?", [$fullDigits])
                ->first();
        }

        if (!$user) {
            return back()->withInput()->withErrors(['mobile_number' => 'Mobile number not found. Please check your number or register first.']);
        }

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        // Store full phone number for OTP verification (for backward compatibility)
        $fullPhoneNumber = $phoneCode . $mobileNumber;

        // Store OTP in users table
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // Also store in otp_verifications table for backward compatibility
        OtpVerification::updateOrCreate(
            ['phone' => $fullPhoneNumber, 'type' => 'login'],
            [
                'otp' => $otp,
                'expires_at' => $expiresAt,
                'is_verified' => false,
            ]
        );

        // Store in session for verification
        session([
            'otp_sent' => true,
            'phone' => $fullPhoneNumber,
            'mobile_number' => $mobileNumber,
            'mobile_phone_code' => $phoneCode,
            'user_id' => $user->id
        ]);

        // TODO: Integrate with SMS gateway (DLT registered)
        // For now, we'll return the OTP in development
        if (app()->environment('local')) {
            return back()->with('otp', $otp);
        }

        return back()->with('success', 'OTP sent to your phone number');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        // Get phone from session
        $phone = session('phone');
        $userId = session('user_id');

        if (!$phone || !$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please try again.');
        }

        // Find user by ID from session
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found. Please try again.');
        }

        // Check OTP from users table first
        $otpValid = false;
        if ($user->otp && $user->otp_expires_at && $user->otp_expires_at > now()) {
            if ($user->otp === $request->otp) {
                $otpValid = true;
            }
        }

        // Also check otp_verifications table for backward compatibility
        if (!$otpValid) {
            $otpRecord = OtpVerification::where('phone', $phone)
                ->where('otp', $request->otp)
                ->where('type', 'login')
                ->where('expires_at', '>', now())
                ->where('is_verified', false)
                ->first();

            if ($otpRecord) {
                $otpValid = true;
                // Mark OTP as verified
                $otpRecord->update(['is_verified' => true]);
            }
        }

        if (!$otpValid) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP']);
        }

        // Clear OTP from users table
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        auth()->login($user);

        // Clear session data
        session()->forget(['otp_sent', 'phone', 'mobile_number', 'mobile_phone_code', 'user_id']);

        // Redirect based on user role
        if ($user->hasRole('Admin') || $user->hasRole('Sub Admin')) {
            return redirect()->route('admin.dashboard')->with('success', 'Logged in successfully!');
        } else {
            // Redirect exhibitors to frontend homepage instead of dashboard
            return redirect()->route('home')->with('success', 'Logged in successfully!');
        }
    }
}
