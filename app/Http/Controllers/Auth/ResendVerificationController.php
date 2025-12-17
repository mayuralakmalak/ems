<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResendVerificationController extends Controller
{
    /**
     * Show the form to request a verification email.
     */
    public function show(): View
    {
        return view('auth.resend-verification');
    }

    /**
     * Resend the email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('status', 'If that email exists, we have sent a verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Your email is already verified. You can log in now.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent! Please check your email.');
    }
}
