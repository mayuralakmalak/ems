<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Calculate summary stats
        $allPayments = Payment::where('user_id', $user->id)->get();
        $outstandingBalance = Booking::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function($booking) {
                return $booking->total_amount - $booking->paid_amount;
            });
        $totalPaid = $allPayments->where('status', 'completed')->sum('amount');
        $pending = $allPayments->where('status', 'pending')->sum('amount');
        $overdue = Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->sum('amount');
        
        // Get payment history
        $payments = Payment::where('user_id', $user->id)
            ->with('booking.exhibition')
            ->latest()
            ->paginate(15);
        
        // Get upcoming payments
        $upcomingPayments = Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->with('booking.exhibition')
            ->orderBy('due_date', 'asc')
            ->get();
        
        // Get wallet balance and transactions
        $walletBalance = $user->wallet_balance;
        $walletTransactions = Wallet::where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->latest()
            ->take(5)
            ->get();
        
        return view('frontend.payments.index', compact(
            'outstandingBalance',
            'totalPaid',
            'pending',
            'overdue',
            'payments',
            'upcomingPayments',
            'walletBalance',
            'walletTransactions'
        ));
    }
    
    public function create(int $bookingId)
    {
        $booking = Booking::with('exhibition')->where('user_id', auth()->id())->findOrFail($bookingId);

        $outstanding = $booking->total_amount - $booking->paid_amount;
        $initialPercent = $booking->exhibition->initial_payment_percent ?? 10;
        $initialAmount = ($booking->total_amount * $initialPercent) / 100;
        $walletBalance = auth()->user()->wallet_balance;

        return view('frontend.payments.create', compact('booking', 'outstanding', 'initialPercent', 'initialAmount', 'walletBalance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|in:online,offline,rtgs,neft,wallet',
            'amount' => 'required|numeric|min:1',
        ]);

        $booking = Booking::where('user_id', auth()->id())->findOrFail($request->booking_id);
        $amount = (float) $request->amount;
        $user = auth()->user();

        // Handle wallet payment
        if ($request->payment_method === 'wallet') {
            $walletBalance = $user->wallet_balance;
            if ($walletBalance < $amount) {
                return back()->with('error', 'Insufficient wallet balance. Your balance is ₹' . number_format($walletBalance, 2));
            }

            // Deduct from wallet
            Wallet::create([
                'user_id' => $user->id,
                'balance' => $walletBalance - $amount,
                'transaction_type' => 'debit',
                'amount' => $amount,
                'reference_type' => 'booking',
                'reference_id' => $booking->id,
                'description' => 'Payment for booking #' . $booking->booking_number,
            ]);
        }

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'payment_number' => 'PM' . now()->format('YmdHis') . rand(100, 999),
            'payment_type' => 'initial',
            'payment_method' => $request->payment_method,
            'status' => $request->payment_method === 'wallet' ? 'completed' : ($request->payment_method === 'online' ? 'pending' : 'pending'),
            'amount' => $amount,
            'gateway_charge' => $request->payment_method === 'online' ? round($amount * 0.025, 2) : 0,
            'paid_at' => $request->payment_method === 'wallet' ? now() : null,
        ]);

        $booking->paid_amount += $amount;
        if ($booking->paid_amount >= $booking->total_amount) {
            $booking->status = 'confirmed';
            $booking->approval_status = 'approved';
        }
        $booking->save();

        // Redirect to payment confirmation
        return redirect()->route('payments.confirmation', $payment->id)
            ->with('success', 'Payment processed successfully.');
    }
    
    public function confirmation(int $paymentId)
    {
        $payment = Payment::with(['booking.exhibition', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($paymentId);
        
        return view('frontend.payments.confirmation', compact('payment'));
    }
}
