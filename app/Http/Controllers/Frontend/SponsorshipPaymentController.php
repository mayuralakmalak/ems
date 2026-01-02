<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SponsorshipBooking;
use App\Models\SponsorshipPayment;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SponsorshipPaymentController extends Controller
{
    public function create($bookingId)
    {
        $booking = SponsorshipBooking::where('user_id', auth()->id())
            ->with(['sponsorship', 'exhibition', 'payments'])
            ->findOrFail($bookingId);
        
        $outstanding = $booking->amount - $booking->paid_amount;
        
        if ($outstanding <= 0) {
            return redirect()->route('sponsorships.booking', $booking->id)
                ->with('info', 'This sponsorship booking is already fully paid.');
        }
        
        return view('frontend.sponsorships.payment', compact('booking', 'outstanding'));
    }
    
    public function store(Request $request, $bookingId)
    {
        $booking = SponsorshipBooking::where('user_id', auth()->id())
            ->findOrFail($bookingId);
        
        $validated = $request->validate([
            'payment_method' => 'required|in:online,offline,rtgs,neft,wallet',
            'amount' => 'required|numeric|min:1',
            'payment_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $outstanding = $booking->amount - $booking->paid_amount;
        
        if ($validated['amount'] > $outstanding) {
            return back()->with('error', 'Payment amount cannot exceed outstanding amount.');
        }
        
        // Handle wallet payment
        if ($validated['payment_method'] === 'wallet') {
            $user = auth()->user();
            if ($user->wallet_balance < $validated['amount']) {
                return back()->with('error', 'Insufficient wallet balance.');
            }
        }
        
        DB::beginTransaction();
        try {
            // Generate payment number
            $paymentNumber = 'SPP' . now()->format('YmdHis') . str_pad(SponsorshipPayment::count() + 1, 6, '0', STR_PAD_LEFT);
            
            // Handle payment proof upload
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('sponsorship-payment-proofs', 'public');
            }
            
            // Create payment record
            $payment = SponsorshipPayment::create([
                'sponsorship_booking_id' => $booking->id,
                'user_id' => auth()->id(),
                'payment_number' => $paymentNumber,
                'payment_method' => $validated['payment_method'],
                'status' => $validated['payment_method'] === 'wallet' ? 'completed' : 'pending',
                'approval_status' => $validated['payment_method'] === 'online' ? 'pending' : 'pending',
                'amount' => $validated['amount'],
                'gateway_charge' => 0,
                'transaction_id' => $validated['transaction_id'] ?? null,
                'payment_proof' => $request->input('payment_proof_text'),
                'payment_proof_file' => $paymentProofPath,
                'notes' => $validated['notes'] ?? null,
                'due_date' => now()->addDays(7), // 7 days for offline payments
            ]);
            
            // Handle wallet payment
            if ($validated['payment_method'] === 'wallet') {
                // Deduct from wallet
                $user = auth()->user();
                $user->decrement('wallet_balance', $validated['amount']);
                
                // Create wallet transaction
                Wallet::create([
                    'user_id' => $user->id,
                    'amount' => $validated['amount'],
                    'transaction_type' => 'debit',
                    'description' => 'Sponsorship payment - ' . $booking->booking_number,
                    'reference_id' => $payment->id,
                    'reference_type' => 'sponsorship_payment',
                ]);
                
                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'approval_status' => 'approved',
                    'paid_at' => now(),
                ]);
                
                // Update booking
                $booking->increment('paid_amount', $validated['amount']);
                
                if ($booking->paid_amount >= $booking->amount) {
                    $booking->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                    ]);
                } else {
                    $booking->update(['payment_status' => 'partial']);
                }
            }
            
            DB::commit();
            
            if ($validated['payment_method'] === 'wallet') {
                return redirect()->route('sponsorships.payment.confirmation', $payment->id)
                    ->with('success', 'Payment completed successfully using wallet.');
            } elseif ($validated['payment_method'] === 'online') {
                // Redirect to payment gateway
                return redirect()->route('sponsorships.payment.gateway', $payment->id);
            } else {
                return redirect()->route('sponsorships.payment.confirmation', $payment->id)
                    ->with('success', 'Payment request submitted. Please wait for admin approval.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process payment: ' . $e->getMessage())->withInput();
        }
    }
    
    public function confirmation($paymentId)
    {
        $payment = SponsorshipPayment::where('user_id', auth()->id())
            ->with(['sponsorshipBooking.sponsorship', 'sponsorshipBooking.exhibition'])
            ->findOrFail($paymentId);
        
        return view('frontend.sponsorships.payment-confirmation', compact('payment'));
    }
    
    public function gateway($paymentId)
    {
        $payment = SponsorshipPayment::where('user_id', auth()->id())
            ->with(['sponsorshipBooking.sponsorship', 'sponsorshipBooking.exhibition'])
            ->findOrFail($paymentId);
        
        // Here you would integrate with payment gateway (Razorpay, Stripe, etc.)
        // For now, we'll create a simple view
        return view('frontend.sponsorships.payment-gateway', compact('payment'));
    }
    
    public function callback(Request $request, $paymentId)
    {
        $payment = SponsorshipPayment::where('user_id', auth()->id())
            ->findOrFail($paymentId);
        
        // Handle payment gateway callback
        // This would typically verify the payment with the gateway
        // For now, we'll simulate a successful payment
        
        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'completed',
                'approval_status' => 'approved',
                'transaction_id' => $request->input('transaction_id'),
                'paid_at' => now(),
            ]);
            
            $booking = $payment->sponsorshipBooking;
            $booking->increment('paid_amount', $payment->amount);
            
            if ($booking->paid_amount >= $booking->amount) {
                $booking->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);
            } else {
                $booking->update(['payment_status' => 'partial']);
            }
            
            DB::commit();
            
            return redirect()->route('sponsorships.payment.confirmation', $payment->id)
                ->with('success', 'Payment completed successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sponsorships.payment', $payment->sponsorship_booking_id)
                ->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }
}

