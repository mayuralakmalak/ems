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
        
        // Upcoming payment due dates (from payment schedules set by admin)
        // This includes both initial and installment (part) payments
        // Also includes overdue payments so users can see what needs urgent attention
        // Ordered by latest booking first (desc), then by part number (asc) within each booking
        $upcomingPayments = Payment::where('payments.user_id', $user->id)
            ->where('payments.status', 'pending')
            ->whereNotNull('payments.due_date')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->with(['booking.exhibition.paymentSchedules', 'booking.booth'])
            ->select('payments.*')
            ->orderBy('bookings.created_at', 'desc')
            ->orderBy('payments.due_date', 'asc')
            ->limit(20) // Get more to allow for sorting, then limit after
            ->get()
            ->map(function ($payment) {
                // Match payment with payment schedule to get accurate part number
                // The due_date in payment matches the due_date in payment schedule set by admin
                $partNumber = null;
                if ($payment->booking && $payment->booking->exhibition && $payment->due_date) {
                    $exhibition = $payment->booking->exhibition;
                    $paymentSchedules = $exhibition->paymentSchedules;
                    
                    // Find matching payment schedule by due_date
                    $matchingSchedule = $paymentSchedules->first(function ($schedule) use ($payment) {
                        return $schedule->due_date && 
                               $schedule->due_date->format('Y-m-d') === $payment->due_date->format('Y-m-d');
                    });
                    
                    if ($matchingSchedule) {
                        $partNumber = $matchingSchedule->part_number;
                    } elseif ($payment->payment_type === 'initial') {
                        // Initial payment is typically part 1
                        $partNumber = 1;
                    }
                }
                
                // Calculate days until due
                $daysUntilDue = $payment->due_date ? now()->startOfDay()->diffInDays($payment->due_date->startOfDay(), false) : null;
                
                $payment->part_number = $partNumber ?? 999; // Use 999 for null to sort last
                $payment->days_until_due = $daysUntilDue;
                $payment->booking_created_at = $payment->booking->created_at ?? now();
                return $payment;
            })
            ->sortBy([
                ['booking_created_at', 'desc'], // Latest booking first
                ['part_number', 'asc'],        // Part 1, 2, 3... within each booking
            ])
            ->take(10)
            ->values();
        
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

