<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BoothRequest;
use App\Models\Payment;
use App\Models\Wallet;
use App\Mail\PaymentReceiptMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

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
    
    public function create(int $bookingId, Request $request)
    {
        $booking = Booking::with(['exhibition', 'payments'])->where('user_id', auth()->id())->findOrFail($bookingId);

        $outstanding = $booking->total_amount - $booking->paid_amount;
        
        // Check if a specific payment ID is provided
        $paymentId = $request->get('payment_id');
        $specificPayment = null;
        
        if ($paymentId) {
            $specificPayment = Payment::where('id', $paymentId)
                ->where('booking_id', $booking->id)
                ->where('user_id', auth()->id())
                ->where('status', 'pending')
                ->firstOrFail();
        }
        
        // Get the pending payment to pay (either specific or next pending)
        if ($specificPayment) {
            // Use the specific payment amount
            $initialAmount = $specificPayment->amount;
            $initialPercent = ($initialAmount / $booking->total_amount) * 100;
        } else {
            // Get the next pending payment (initial first, then installments)
            $pendingPayment = $booking->payments()
                ->where('status', 'pending')
                ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
                ->orderBy('due_date', 'asc')
                ->first();
            
            if ($pendingPayment) {
                $initialAmount = $pendingPayment->amount;
                $initialPercent = ($initialAmount / $booking->total_amount) * 100;
            } else {
                // Fallback: Calculate from exhibition initial_payment_percent (backward compatibility)
                $initialPercent = $booking->exhibition->initial_payment_percent ?? 10;
                $initialAmount = ($booking->total_amount * $initialPercent) / 100;
            }
        }
        
        $walletBalance = auth()->user()->wallet_balance;

        return view('frontend.payments.create', compact('booking', 'outstanding', 'initialPercent', 'initialAmount', 'walletBalance', 'specificPayment'));
    }

    public function pay(int $paymentId)
    {
        $payment = Payment::with(['booking.exhibition', 'booking.booth', 'user'])
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->findOrFail($paymentId);

        $booking = $payment->booking;
        $outstanding = $booking->total_amount - $booking->paid_amount;
        $walletBalance = auth()->user()->wallet_balance;

        // Get all pending payments for this booking to show in summary
        $allPendingPayments = $booking->payments()
            ->where('status', 'pending')
            ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
            ->orderBy('due_date', 'asc')
            ->get();

        // Get completed payments count
        $completedPayments = $booking->payments()
            ->where('status', 'completed')
            ->count();

        return view('frontend.payments.pay', compact(
            'payment',
            'booking',
            'outstanding',
            'walletBalance',
            'allPendingPayments',
            'completedPayments'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|in:online,offline,rtgs,neft,wallet',
            'amount' => 'required|numeric|min:1',
            'payment_id' => 'nullable|exists:payments,id',
        ]);

        $booking = Booking::where('user_id', auth()->id())->findOrFail($request->booking_id);
        $amount = (float) $request->amount;
        $user = auth()->user();
        $boothIds = collect($booking->selected_booth_ids ?? [$booking->booth_id])
            ->map(function($entry) {
                if (is_array($entry)) {
                    return $entry['id'] ?? null;
                }
                return $entry;
            })
            ->filter()
            ->values()
            ->all();
        if (empty($boothIds)) {
            $boothIds = [$booking->booth_id];
        }

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

        // Find the payment to update (either specific payment_id or next pending payment)
        if ($request->payment_id) {
            // Update specific payment
            $payment = Payment::where('id', $request->payment_id)
                ->where('booking_id', $booking->id)
                ->where('user_id', auth()->id())
                ->where('status', 'pending')
                ->firstOrFail();
        } else {
            // Find next pending payment (initial first, then installments by due date)
            $payment = Payment::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
                ->orderBy('due_date', 'asc')
                ->first();
        }

        if ($payment) {
            // Update existing pending payment
            // Use the scheduled amount from the payment record, not the request amount
            // This ensures we use the correct amount from the payment schedule
            $scheduledAmount = $payment->amount;
            $isCompleted = $request->payment_method === 'wallet';
            
            $payment->update([
                'payment_method' => $request->payment_method,
                'status' => $isCompleted ? 'completed' : 'pending',
                'approval_status' => $isCompleted ? 'approved' : 'pending',
                'gateway_charge' => $request->payment_method === 'online' ? round($scheduledAmount * 0.025, 2) : 0,
                'paid_at' => $isCompleted ? now() : null,
                'transaction_id' => $request->transaction_id ?? null,
            ]);
            
            // Only update booking paid_amount if payment is completed (wallet payment)
            if ($isCompleted) {
                $booking->paid_amount += $scheduledAmount;
                
                // Reload payment with relationships for email
                $payment->load(['booking.exhibition', 'booking.booth', 'booking.bookingServices.service', 'user']);
                
                // Send payment receipt email to exhibitor
                try {
                    Mail::to($user->email)->send(new PaymentReceiptMail($payment, false));
                } catch (\Exception $e) {
                    Log::error('Failed to send payment receipt email to exhibitor: ' . $e->getMessage());
                }
                
                // Send payment receipt email to all admins
                $admins = \App\Models\User::role('Admin')->orWhere('id', 1)->get();
                foreach ($admins as $admin) {
                    try {
                        if ($admin->email) {
                            Mail::to($admin->email)->send(new PaymentReceiptMail($payment, true));
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to send payment receipt email to admin: ' . $e->getMessage());
                    }
                }
            }
        } else {
            // Fallback: Create new payment if no existing pending payment found (backward compatibility)
            $isCompleted = $request->payment_method === 'wallet';
            
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'payment_number' => 'PM' . now()->format('YmdHis') . rand(100, 999),
                'payment_type' => 'initial',
                'payment_method' => $request->payment_method,
                'status' => $isCompleted ? 'completed' : 'pending',
                'approval_status' => $isCompleted ? 'approved' : 'pending',
                'amount' => $amount,
                'gateway_charge' => $request->payment_method === 'online' ? round($amount * 0.025, 2) : 0,
                'paid_at' => $isCompleted ? now() : null,
                'transaction_id' => $request->transaction_id ?? null,
            ]);
            
            // Only update booking paid_amount if payment is completed (wallet payment)
            if ($isCompleted) {
                $booking->paid_amount += $amount;
                
                // Reload payment with relationships for email
                $payment->load(['booking.exhibition', 'booking.booth', 'booking.bookingServices.service', 'user']);
                
                // Send payment receipt email to exhibitor
                try {
                    Mail::to($user->email)->send(new PaymentReceiptMail($payment, false));
                } catch (\Exception $e) {
                    Log::error('Failed to send payment receipt email to exhibitor: ' . $e->getMessage());
                }
                
                // Send payment receipt email to all admins
                $admins = \App\Models\User::role('Admin')->orWhere('id', 1)->get();
                foreach ($admins as $admin) {
                    try {
                        if ($admin->email) {
                            Mail::to($admin->email)->send(new PaymentReceiptMail($payment, true));
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to send payment receipt email to admin: ' . $e->getMessage());
                    }
                }
            }
        }

        // Keep booking pending until admin approval even if fully paid
        $booking->status = 'pending';
        $booking->approval_status = $booking->approval_status ?? 'pending';
        $booking->save();

        // Create booth request after payment is initiated
        $existingRequest = BoothRequest::where('request_type', 'booking')
            ->where('exhibition_id', $booking->exhibition_id)
            ->where('user_id', $user->id)
            ->where('request_data->booking_id', $booking->id)
            ->first();

        if (!$existingRequest) {
            BoothRequest::create([
                'exhibition_id' => $booking->exhibition_id,
                'user_id' => $user->id,
                'request_type' => 'booking',
                'booth_ids' => $boothIds,
                'status' => 'pending',
                'request_data' => [
                    'booking_id' => $booking->id,
                    'total_amount' => $booking->total_amount,
                    'paid_amount' => $booking->paid_amount,
                    'payment_method' => $request->payment_method,
                ],
            ]);
        }

        // Mark booths as reserved (unavailable but not booked yet) when payment is made
        // This ensures booths show as reserved on the floorplan until admin approval
        // Only mark as reserved if there's at least one completed payment
        $hasCompletedPayment = $booking->payments()
            ->where('status', 'completed')
            ->exists();
        
        if ($hasCompletedPayment) {
            // Mark primary booth as reserved (unavailable but not booked)
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => false,
                    // Keep is_booked as false until admin approves
                ]);
            }
            
            // Also mark ALL booths in selected_booth_ids as reserved
            if ($booking->selected_booth_ids) {
                $selectedBoothIds = [];
                if (is_array($booking->selected_booth_ids)) {
                    // Handle array format: [{'id': 1, 'name': 'B001'}, ...]
                    $selectedBoothIds = collect($booking->selected_booth_ids)
                        ->pluck('id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                } else {
                    // Handle simple array format: [1, 2, 3]
                    $selectedBoothIds = collect($booking->selected_booth_ids)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }
                
                // Mark all selected booths as reserved
                if (!empty($selectedBoothIds)) {
                    \App\Models\Booth::whereIn('id', $selectedBoothIds)
                        ->where('exhibition_id', $booking->exhibition_id)
                        ->update([
                            'is_available' => false,
                            // Keep is_booked as false until admin approves
                        ]);
                }
            } else {
                // Fallback: mark booths from boothIds array
                foreach ($boothIds as $boothId) {
                    $booth = \App\Models\Booth::find($boothId);
                    if ($booth && $booth->id !== $booking->booth_id) {
                        $booth->update([
                            'is_available' => false,
                        ]);
                    }
                }
            }
        }

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
    
    public function uploadProof(Request $request, int $paymentId)
    {
        $payment = Payment::where('user_id', auth()->id())
            ->findOrFail($paymentId);
        
        $request->validate([
            'payment_proof' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);
        
        $path = $request->file('payment_proof')->store('payment-proofs', 'public');
        
        $payment->update([
            'payment_proof_file' => $path,
            'approval_status' => 'pending',
        ]);
        
        // Notify admins
        $admins = \App\Models\User::role('Admin')->orWhere('id', 1)->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'payment',
                'title' => 'Payment Proof Uploaded',
                'message' => auth()->user()->name . ' has uploaded payment proof for payment #' . $payment->payment_number,
                'notifiable_type' => Payment::class,
                'notifiable_id' => $payment->id,
            ]);
        }
        
        return back()->with('success', 'Payment proof uploaded successfully. Waiting for admin approval.');
    }
    
    public function download($paymentId)
    {
        $payment = Payment::where('user_id', auth()->id())
            ->with(['booking.exhibition', 'user'])
            ->findOrFail($paymentId);

        // If stored receipt/invoice exists, serve it
        if ($payment->receipt_file && \Storage::disk('public')->exists($payment->receipt_file)) {
            return \Storage::disk('public')->download($payment->receipt_file);
        }
        if ($payment->invoice_file && \Storage::disk('public')->exists($payment->invoice_file)) {
            return \Storage::disk('public')->download($payment->invoice_file);
        }

        // Otherwise generate PDF on the fly
        $html = view('frontend.payments.receipt', compact('payment'))->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf-temp'),
            'format' => 'A4',
            'margin_top' => 15,
            'margin_right' => 12,
            'margin_bottom' => 15,
            'margin_left' => 12,
            'fontDir' => array_merge($fontDirs, [
                resource_path('fonts'),
            ]),
            'fontdata' => $fontData,
            'default_font' => 'dejavusans',
        ]);

        $mpdf->SetTitle('Payment Receipt - ' . $payment->payment_number);
        $mpdf->WriteHTML($html);

        $pdfContent = $mpdf->Output('', 'S');
        $filename = 'Payment_Receipt_' . $payment->payment_number . '.pdf';

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
