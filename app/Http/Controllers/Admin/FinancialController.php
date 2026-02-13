<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class FinancialController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('Financial Management - View'), 403);
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $pendingAmount = Booking::sum('total_amount') - Booking::sum('paid_amount');

        $byMethod = Payment::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();

        $recentPayments = Payment::with('booking')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.financial.index', compact('totalRevenue', 'pendingAmount', 'byMethod', 'recentPayments'));
    }
}
