<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['booking.user', 'booking.exhibition'])
            ->latest()
            ->paginate(20);
        
        return view('admin.payments.index', compact('payments'));
    }

    public function create()
    {
        $bookings = Booking::with(['user', 'exhibition'])->where('status', 'confirmed')->get();
        return view('admin.payments.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,completed,failed',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        
        $payment = Payment::create([
            'booking_id' => $validated['booking_id'],
            'user_id' => $booking->user_id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'],
            'status' => $validated['status'],
            'transaction_id' => $validated['transaction_id'] ?? null,
        ]);

        // Update booking paid amount
        $booking->increment('paid_amount', $validated['amount']);

        return redirect()->route('admin.payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function show($id)
    {
        $payment = Payment::with(['booking.user', 'booking.exhibition', 'booking.booth'])->findOrFail($id);
        return view('admin.payments.show', compact('payment'));
    }
}
