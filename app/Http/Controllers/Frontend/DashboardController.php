<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Badge;
use App\Models\Document;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $bookings = Booking::with(['exhibition', 'booth'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $payments = Payment::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $badges = Badge::where('user_id', $user->id)
            ->latest()
            ->get();

        $documents = Document::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $walletBalance = $user->wallet_balance;

        return view('frontend.dashboard.index', compact('user', 'bookings', 'payments', 'badges', 'documents', 'walletBalance'));
    }
}

