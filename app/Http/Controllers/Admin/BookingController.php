<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['exhibition', 'booth', 'user', 'payments'])
            ->latest()
            ->paginate(20);
        
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show($id)
    {
        $booking = Booking::with(['exhibition', 'booth', 'user', 'payments', 'documents', 'badges', 'bookingServices.service'])
            ->findOrFail($id);
        
        return view('admin.bookings.show', compact('booking'));
    }

    public function processCancellation(Request $request, $id)
    {
        $booking = Booking::with(['user', 'booth'])->findOrFail($id);
        
        $request->validate([
            'cancellation_type' => 'required|in:refund,wallet_credit',
            'cancellation_amount' => 'required|numeric|min:0|max:' . $booking->paid_amount,
            'account_details' => 'required_if:cancellation_type,refund|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Update booking
            $booking->update([
                'cancellation_type' => $request->cancellation_type,
                'cancellation_amount' => $request->cancellation_amount,
                'account_details' => $request->account_details,
            ]);

            // Process refund or wallet credit
            if ($request->cancellation_type === 'wallet_credit') {
                // Credit to wallet
                Wallet::create([
                    'user_id' => $booking->user_id,
                    'balance' => ($booking->user->wallet_balance ?? 0) + $request->cancellation_amount,
                    'transaction_type' => 'credit',
                    'amount' => $request->cancellation_amount,
                    'reference_type' => 'booking_cancellation',
                    'reference_id' => $booking->id,
                    'description' => 'Cancellation credit for booking #' . $booking->booking_number,
                ]);
            }

            // Free up the booth
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Cancellation processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process cancellation: ' . $e->getMessage());
        }
    }
}

