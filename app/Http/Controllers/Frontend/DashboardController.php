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
        
        // Calculate stats
        $activeBookings = $bookings->where('status', 'confirmed')->count();
        $outstandingPayments = Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');
        $badgesPending = $badges->where('status', 'pending')->count();
        
        // Upcoming payment due dates (from payment schedules)
        $upcomingPayments = Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->with('booking.exhibition')
            ->orderBy('due_date', 'asc')
            ->get();
        
        // Recent activity (simulated from bookings, payments, documents)
        $recentActivity = collect();
        foreach ($bookings->take(5) as $booking) {
            $recentActivity->push([
                'type' => 'booking',
                'message' => 'Booking for "' . ($booking->exhibition->name ?? 'Exhibition') . '" confirmed',
                'time' => $booking->created_at->diffForHumans(),
                'date' => $booking->created_at,
            ]);
        }
        foreach ($payments->take(3) as $payment) {
            $recentActivity->push([
                'type' => 'payment',
                'message' => 'Payment receipt for "' . ($payment->booking->exhibition->name ?? 'Exhibition') . '" sent',
                'time' => $payment->created_at->diffForHumans(),
                'date' => $payment->created_at,
            ]);
        }
        $recentActivity = $recentActivity->sortByDesc('date')->take(5);

        return view('frontend.dashboard.index', compact(
            'user', 
            'bookings', 
            'payments', 
            'badges', 
            'documents', 
            'walletBalance',
            'activeBookings',
            'outstandingPayments',
            'badgesPending',
            'upcomingPayments',
            'recentActivity'
        ));
    }
}

