<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
        }
        $booking->save();

        return redirect()->route('bookings.show', $booking->id)
            ->with('success', 'Payment recorded successfully.');
    }
}
