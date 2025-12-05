<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Key Metrics
        $totalApplications = Booking::count();
        $totalListings = Exhibition::count();
        $totalEarnings = Payment::where('status', 'completed')->sum('amount');
        $pendingApprovals = Booking::where('approval_status', 'pending')->count();
        
        // Revenue Overview (Monthly)
        $revenueData = Payment::where('status', 'completed')
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
        
        // Booking Trends (Daily - Last 7 days)
        $bookingTrends = Booking::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        // Recent Activities
        $recentActivities = Booking::with('user')
            ->latest()
            ->take(6)
            ->get()
            ->map(function($booking) {
                return [
                    'user' => $booking->user->name ?? 'Unknown',
                    'action' => 'created new booking',
                    'item' => $booking->booking_number,
                    'time' => $booking->created_at->diffForHumans()
                ];
            });
        
        // Pending Approvals
        $pendingApprovalsList = Booking::with('user', 'exhibition')
            ->where('approval_status', 'pending')
            ->latest()
            ->take(6)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalApplications',
            'totalListings',
            'totalEarnings',
            'pendingApprovals',
            'revenueData',
            'bookingTrends',
            'recentActivities',
            'pendingApprovalsList'
        ));
    }
}


