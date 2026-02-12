<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BoothRequest;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\Setting;
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
        $pendingPayment = null;

        if ($paymentId) {
            $paymentById = Payment::where('id', $paymentId)
                ->where('booking_id', $booking->id)
                ->where('user_id', auth()->id())
                ->first();
            if (!$paymentById) {
                abort(404);
            }
            if ($paymentById->status === 'completed') {
                return redirect()->route('payments.confirmation', $paymentById->id)
                    ->with('info', 'This payment is already completed.');
            }
            $specificPayment = $paymentById;
        }

        // When no specific payment: if booking has no pending payments, redirect to confirmation
        if (!$specificPayment) {
            $hasNoPending = $booking->payments()->exists()
                && $booking->payments()->where('status', 'pending')->doesntExist();
            if ($hasNoPending) {
                $lastCompleted = $booking->payments()->where('status', 'completed')->orderBy('paid_at', 'desc')->first();
                if ($lastCompleted) {
                    return redirect()->route('payments.confirmation', $lastCompleted->id)
                        ->with('info', 'All payments for this booking are already completed.');
                }
                return redirect()->route('bookings.show', $booking->id)->with('info', 'This booking has no pending payments.');
            }
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
        
        // Get all stored payments for this booking (ordered by part number/type)
        // This ensures we display the actual stored amounts, not recalculated ones
        $storedPayments = $booking->payments()
            ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
            ->orderBy('due_date', 'asc')
            ->get();
        
        // Calculate percentage and correct amount for each payment
        // Try to get from payment schedule first (original percentage), otherwise calculate from amount
        $paymentPercentages = [];
        $paymentCorrectAmounts = []; // Store correct amounts based on schedule
        $paymentCorrectDueDates = []; // Store correct due dates from schedule
        $exhibitionPaymentSchedules = $booking->exhibition->paymentSchedules()->orderBy('part_number', 'asc')->get();
        
        $paymentIndex = 0;
        foreach ($storedPayments as $payment) {
            $paymentIndex++;
            
            // Try to get percentage and due date from payment schedule (original values used when booking was created)
            $schedulePercentage = null;
            $scheduleDueDate = null;
            if ($exhibitionPaymentSchedules->count() > 0 && isset($exhibitionPaymentSchedules[$paymentIndex - 1])) {
                $schedule = $exhibitionPaymentSchedules[$paymentIndex - 1];
                $schedulePercentage = $schedule->percentage;
                $scheduleDueDate = $schedule->due_date;
            }
            
            // Use schedule percentage if available, otherwise calculate from stored amount
            if ($schedulePercentage !== null) {
                $paymentPercentages[$payment->id] = round($schedulePercentage, 2);
                // Calculate correct amount based on schedule percentage
                $paymentCorrectAmounts[$payment->id] = round(($booking->total_amount * $schedulePercentage) / 100, 2);
            } elseif ($booking->total_amount > 0) {
                $paymentPercentages[$payment->id] = round(($payment->amount / $booking->total_amount) * 100, 2);
                $paymentCorrectAmounts[$payment->id] = $payment->amount; // Use stored amount if no schedule
            } else {
                $paymentPercentages[$payment->id] = 0;
                $paymentCorrectAmounts[$payment->id] = 0;
            }
            
            // Store correct due date from schedule if available
            if ($scheduleDueDate) {
                $paymentCorrectDueDates[$payment->id] = $scheduleDueDate;
            } else {
                $paymentCorrectDueDates[$payment->id] = $payment->due_date; // Use stored due date if no schedule
            }
        }

        // Load payment method settings
        $paymentMethodSettings = Setting::getByGroup('payment_methods');
        $upiId = $paymentMethodSettings['upi_id'] ?? '';
        $upiQrCode = $paymentMethodSettings['upi_qr_code'] ?? '';
        $bankName = $paymentMethodSettings['payment_bank_name'] ?? '';
        $accountHolder = $paymentMethodSettings['payment_account_holder'] ?? '';
        $accountNumber = $paymentMethodSettings['payment_account_number'] ?? '';
        $ifscCode = $paymentMethodSettings['payment_ifsc_code'] ?? '';
        $branch = $paymentMethodSettings['payment_branch'] ?? '';
        $branchAddress = $paymentMethodSettings['payment_branch_address'] ?? '';

        // Get gateway charge for the current payment (if specific payment) or first pending payment
        $currentPayment = $specificPayment ?? $booking->payments()
            ->where('status', 'pending')
            ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
            ->orderBy('due_date', 'asc')
            ->first();
        
        // Get all payments to calculate total gateway fee from stored values
        $allPayments = $booking->payments()
            ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
            ->orderBy('due_date', 'asc')
            ->get();
        
        // Calculate total gateway fee from stored payment gateway charges (if they exist)
        // This ensures we use the exact values stored during booking creation - NO DOUBLE CALCULATION
        $totalGatewayFee = $allPayments->sum(function($payment) {
            return $payment->gateway_charge ?? 0;
        });
        
        // If total gateway fee is 0 (old bookings or not set), calculate it ONCE
        if ($totalGatewayFee == 0) {
            // Calculate 2.5% of total booking amount (ONLY ONCE)
            $totalGatewayFee = ($booking->total_amount * 2.5) / 100;
            
            // Distribute across all payments
            $totalPayments = $allPayments->count();
            if ($totalPayments > 0 && $totalGatewayFee > 0) {
                $baseFeePerPayment = floor($totalGatewayFee * 100 / $totalPayments) / 100;
                $remainingFee = $totalGatewayFee - ($baseFeePerPayment * $totalPayments);
                $remainingFeeCents = round($remainingFee * 100);
                
                foreach ($allPayments as $index => $payment) {
                    $paymentGatewayFee = $baseFeePerPayment;
                    if ($remainingFeeCents > 0 && $index < $remainingFeeCents) {
                        $paymentGatewayFee += 0.01;
                    }
                    // Store calculated fee for display (but don't update database)
                    $payment->calculated_gateway_charge = round($paymentGatewayFee, 2);
                }
            }
        }
        
        // Get gateway charge for current payment (use stored value, not recalculated)
        $gatewayCharge = 0;
        if ($currentPayment) {
            // Use stored gateway charge, or calculated one if stored is 0
            $gatewayCharge = $currentPayment->gateway_charge ?? ($currentPayment->calculated_gateway_charge ?? 0);
        }
        
        // Build gateway fee per payment array from stored or calculated values
        $gatewayFeePerPayment = [];
        foreach ($allPayments as $payment) {
            $gatewayFeePerPayment[$payment->id] = $payment->gateway_charge ?? ($payment->calculated_gateway_charge ?? 0);
        }

        $currentPayment = $specificPayment ?? $pendingPayment;
        // Already submitted = only when payment is actually completed
        // (do NOT treat updated_at > created_at as submitted, because apply/remove discount
        // operations also update payments while they remain pending)
        $currentPaymentAlreadySubmitted = $currentPayment && ($currentPayment->status === 'completed');

        return view('frontend.payments.create', compact(
            'booking', 
            'outstanding', 
            'initialPercent', 
            'initialAmount', 
            'walletBalance', 
            'specificPayment', 
            'currentPayment',
            'currentPaymentAlreadySubmitted',
            'storedPayments',
            'upiId',
            'upiQrCode',
            'bankName',
            'accountHolder',
            'accountNumber',
            'ifscCode',
            'branch',
            'branchAddress',
            'gatewayCharge',
            'totalGatewayFee',
            'gatewayFeePerPayment',
            'paymentPercentages',
            'paymentCorrectAmounts',
            'paymentCorrectDueDates'
        ));
    }

    public function pay(int $paymentId)
    {
        $payment = Payment::with(['booking.exhibition', 'booking.booth', 'user'])
            ->where('user_id', auth()->id())
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

        // Load payment method settings
        $paymentMethodSettings = Setting::getByGroup('payment_methods');
        $upiId = $paymentMethodSettings['upi_id'] ?? '';
        $upiQrCode = $paymentMethodSettings['upi_qr_code'] ?? '';
        $bankName = $paymentMethodSettings['payment_bank_name'] ?? '';
        $accountHolder = $paymentMethodSettings['payment_account_holder'] ?? '';
        $accountNumber = $paymentMethodSettings['payment_account_number'] ?? '';
        $ifscCode = $paymentMethodSettings['payment_ifsc_code'] ?? '';
        $branch = $paymentMethodSettings['payment_branch'] ?? '';
        $branchAddress = $paymentMethodSettings['payment_branch_address'] ?? '';

        // Get all payments to calculate total gateway fee from stored values
        $allPayments = $booking->payments()
            ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
            ->orderBy('due_date', 'asc')
            ->get();
        
        // Get payment schedule to calculate correct amounts and due dates
        $exhibitionPaymentSchedules = $booking->exhibition->paymentSchedules()->orderBy('part_number', 'asc')->get();
        $paymentCorrectAmounts = [];
        $paymentCorrectDueDates = [];
        $paymentPercentages = [];
        
        $paymentIndex = 0;
        foreach ($allPayments as $p) {
            $paymentIndex++;
            
            // Get schedule data for this payment
            $schedulePercentage = null;
            $scheduleDueDate = null;
            if ($exhibitionPaymentSchedules->count() > 0 && isset($exhibitionPaymentSchedules[$paymentIndex - 1])) {
                $schedule = $exhibitionPaymentSchedules[$paymentIndex - 1];
                $schedulePercentage = $schedule->percentage;
                $scheduleDueDate = $schedule->due_date;
            }
            
            // Store correct amount and percentage
            if ($schedulePercentage !== null) {
                $paymentPercentages[$p->id] = round($schedulePercentage, 2);
                $paymentCorrectAmounts[$p->id] = round(($booking->total_amount * $schedulePercentage) / 100, 2);
            } elseif ($booking->total_amount > 0) {
                $paymentPercentages[$p->id] = round(($p->amount / $booking->total_amount) * 100, 2);
                $paymentCorrectAmounts[$p->id] = $p->amount;
            } else {
                $paymentPercentages[$p->id] = 0;
                $paymentCorrectAmounts[$p->id] = 0;
            }
            
            // Store correct due date
            if ($scheduleDueDate) {
                $paymentCorrectDueDates[$p->id] = $scheduleDueDate;
            } else {
                $paymentCorrectDueDates[$p->id] = $p->due_date;
            }
        }
        
        // Calculate total gateway fee from stored payment gateway charges (if they exist)
        // This ensures we use the exact values stored during booking creation
        $totalGatewayFee = $allPayments->sum(function($p) {
            return $p->gateway_charge ?? 0;
        });
        
        // If total gateway fee is 0 (old bookings or not set), calculate it
        if ($totalGatewayFee == 0) {
            // Calculate 2.5% of total booking amount
            $totalGatewayFee = ($booking->total_amount * 2.5) / 100;
            
            // Distribute across all payments
            $totalPayments = $allPayments->count();
            if ($totalPayments > 0 && $totalGatewayFee > 0) {
                $baseFeePerPayment = floor($totalGatewayFee * 100 / $totalPayments) / 100;
                $remainingFee = $totalGatewayFee - ($baseFeePerPayment * $totalPayments);
                $remainingFeeCents = round($remainingFee * 100);
                
                foreach ($allPayments as $index => $p) {
                    $paymentGatewayFee = $baseFeePerPayment;
                    if ($remainingFeeCents > 0 && $index < $remainingFeeCents) {
                        $paymentGatewayFee += 0.01;
                    }
                    // Store calculated fee for display (but don't update database)
                    $p->calculated_gateway_charge = round($paymentGatewayFee, 2);
                }
            }
        }
        
        // Get gateway charge for this payment
        $gatewayCharge = $payment->gateway_charge ?? ($payment->calculated_gateway_charge ?? 0);
        
        // Build gateway fee per payment array from stored or calculated values
        $gatewayFeePerPayment = [];
        foreach ($allPayments as $p) {
            $gatewayFeePerPayment[$p->id] = $p->gateway_charge ?? ($p->calculated_gateway_charge ?? 0);
        }

        // Already submitted = only when payment is actually completed
        // (do NOT treat updated_at > created_at as submitted, because apply/remove discount
        // operations also update payments while they remain pending)
        $paymentAlreadySubmitted = ($payment->status === 'completed');

        return view('frontend.payments.pay', compact(
            'payment',
            'paymentAlreadySubmitted',
            'booking',
            'outstanding',
            'walletBalance',
            'allPendingPayments',
            'completedPayments',
            'upiId',
            'upiQrCode',
            'bankName',
            'accountHolder',
            'accountNumber',
            'ifscCode',
            'branch',
            'branchAddress',
            'gatewayCharge',
            'totalGatewayFee',
            'gatewayFeePerPayment',
            'paymentCorrectAmounts',
            'paymentCorrectDueDates',
            'paymentPercentages'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|in:online,offline,rtgs,neft,wallet',
            'amount' => 'required|numeric|min:1',
            'payment_id' => 'nullable|exists:payments,id',
            'payment_type_option' => 'nullable|in:full,part',
            'updated_payment_schedule' => 'nullable|string',
        ]);

        $booking = Booking::with('exhibition')->where('user_id', auth()->id())->findOrFail($request->booking_id);
        $amount = (float) $request->amount;
        $user = auth()->user();
        $paymentTypeOption = $request->payment_type_option ?? 'part'; // Default to part payment

        // If user chooses full payment and no coupon-based discount has been applied yet,
        // apply the exhibition full payment discount on top of any member discount,
        // respecting the maximum discount cap. This ensures the full payment discount
        // actually reduces the booking total (and the amount the user pays).
        if ($paymentTypeOption === 'full') {
            $discountType = $booking->discount_type ?? ($booking->discount_percent > 0 ? 'member' : null);
            $couponPercentBooking = (float) ($booking->coupon_discount_percent ?? 0);
            $hasCoupon = $discountType === 'coupon' || $discountType === 'both' || $couponPercentBooking > 0;

            if (! $hasCoupon) {
                $memberDiscountPercent = (float) ($booking->member_discount_percent
                    ?? ($discountType === 'member' ? $booking->discount_percent : 0));

                $maxPercent = $booking->exhibition->maximum_discount_apply_percent !== null
                    ? (float) $booking->exhibition->maximum_discount_apply_percent
                    : 100.0;

                $fullPaymentDiscountPercentRaw = (float) ($booking->exhibition->full_payment_discount_percent ?? 0);
                $fullPaymentEffectivePercent = min(
                    $fullPaymentDiscountPercentRaw,
                    max(0.0, $maxPercent - $memberDiscountPercent)
                );

                if ($fullPaymentEffectivePercent > 0) {
                    $currentTotal = (float) $booking->total_amount;
                    $originalBase = $memberDiscountPercent > 0
                        ? $currentTotal / (1 - ($memberDiscountPercent / 100))
                        : $currentTotal;

                    $newTotalDiscountPercent = min($memberDiscountPercent + $fullPaymentEffectivePercent, $maxPercent);
                    $newTotalAmount = round($originalBase * (1 - ($newTotalDiscountPercent / 100)), 2);

                    $booking->discount_type = $memberDiscountPercent > 0 ? 'member' : null;
                    $booking->discount_percent = $newTotalDiscountPercent;
                    $booking->member_discount_percent = $memberDiscountPercent > 0 ? $memberDiscountPercent : null;
                    $booking->coupon_discount_percent = null;
                    $booking->coupon_discount_percent_part = null;
                    $booking->total_amount = $newTotalAmount;
                    $booking->save();
                }
            }
        }
        // Normalize booth IDs once so both full and part payment flows can use them
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
        
        // Handle full payment: booking total_amount is already the final amount (priority: full payment + member + coupon applied at apply-discount)
        if ($paymentTypeOption === 'full') {
            $fullPaymentAmount = $booking->total_amount;
            $fullPaymentGatewayCharge = ($fullPaymentAmount * 2.5) / 100;
            $fullPaymentTotal = $fullPaymentAmount + $fullPaymentGatewayCharge;

            // Verify the amount matches (user should pay exactly the discounted booking total)
            if (abs($amount - $fullPaymentAmount) > 0.01) {
                return back()->with('error', 'Payment amount mismatch. Please refresh and try again.');
            }

            // Handle wallet payment for full payment (deduct, but keep payment pending for admin approval)
            if ($request->payment_method === 'wallet') {
                $walletBalance = $user->wallet_balance;
                if ($walletBalance < $fullPaymentTotal) {
                    return back()->with('error', 'Insufficient wallet balance. Your balance is ₹' . number_format($walletBalance, 2) . '. Required: ₹' . number_format($fullPaymentTotal, 2));
                }

                Wallet::create([
                    'user_id' => $user->id,
                    'balance' => $walletBalance - $fullPaymentTotal,
                    'transaction_type' => 'debit',
                    'amount' => $fullPaymentTotal,
                    'reference_type' => 'booking',
                    'reference_id' => $booking->id,
                    'description' => 'Full payment for booking #' . $booking->booking_number,
                ]);
            }

            $gatewayChargeToStore = $request->payment_method === 'online' ? $fullPaymentGatewayCharge : 0;

            // If there are no completed payments yet, collapse all scheduled installments into a single full payment
            $hasCompleted = $booking->payments()
                ->where('status', 'completed')
                ->exists();

            if (! $hasCompleted) {
                // Remove all existing pending scheduled payments for this booking
                Payment::where('booking_id', $booking->id)
                    ->where('status', 'pending')
                    ->delete();

                // Create a single pending payment record representing the full payment
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'payment_number' => 'PM' . now()->format('YmdHis') . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . '00' . rand(10, 99),
                    'payment_type' => 'full',
                    'payment_method' => $request->payment_method,
                    'status' => 'pending',
                    'approval_status' => 'pending',
                    'amount' => $fullPaymentAmount,
                    'gateway_charge' => $gatewayChargeToStore,
                    'due_date' => now(),
                ]);
            } else {
                // Fallback: if some payments are already completed, update remaining pending ones
                $allPayments = Payment::where('booking_id', $booking->id)
                    ->where('status', 'pending')
                    ->get();

                foreach ($allPayments as $p) {
                    $p->update([
                        'payment_method' => $request->payment_method,
                        'status' => 'pending',
                        'approval_status' => 'pending',
                        'gateway_charge' => ($p->payment_type === 'initial' ? $gatewayChargeToStore : 0),
                        'transaction_id' => $request->transaction_id ?? null,
                    ]);
                }

                $payment = $allPayments->first();
            }

            // Booking paid_amount will be updated only after admin approval (Admin\PaymentController@approve)

            // Ensure a booth request exists for full payment flows as well
            $existingRequest = BoothRequest::where('request_type', 'booking')
                ->where('exhibition_id', $booking->exhibition_id)
                ->where('user_id', $user->id)
                ->where('request_data->booking_id', $booking->id)
                ->first();

            if (! $existingRequest) {
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

            return redirect()->route('payments.confirmation', $payment?->id ?? $booking->payments()->latest('id')->value('id'))
                ->with('success', 'Full payment submitted successfully and is pending admin approval.');
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
            $payment->refresh();
            $scheduledAmount = $payment->amount;
            $isCompleted = $request->payment_method === 'wallet';
            
            // Determine gateway charge based on payment method
            // Gateway fee (2.5%) ONLY applies to: Credit/Debit Card, UPI, and Net Banking
            // These methods are mapped to 'online' in the frontend
            // Wallet, NEFT, and RTGS do NOT have gateway fees
            $gatewayCharge = 0;
            if ($request->payment_method === 'online' && $payment->gateway_charge > 0) {
                // Use the stored gateway charge for online payments (card/upi/netbanking only)
                $gatewayCharge = $payment->gateway_charge;
            }
            // For wallet, neft, rtgs - no gateway charge (gatewayCharge remains 0)
            
            $payment->update([
                'payment_method' => $request->payment_method,
                'status' => $isCompleted ? 'completed' : 'pending',
                'approval_status' => $isCompleted ? 'approved' : 'pending',
                'gateway_charge' => $gatewayCharge, // Preserve gateway charge for online, 0 for others
                'paid_at' => $isCompleted ? now() : null,
                'transaction_id' => $request->transaction_id ?? null,
            ]);
            
            // Only update booking paid_amount if payment is completed (wallet payment)
            // Use base amount (without gateway fee) for booking paid_amount
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
            // Do not create a new payment if booking has no pending payments (e.g. user went back from confirmation and resubmitted)
            if ($booking->payments()->exists() && $booking->payments()->where('status', 'pending')->doesntExist()) {
                $lastCompleted = $booking->payments()->where('status', 'completed')->orderBy('paid_at', 'desc')->first();
                if ($lastCompleted) {
                    return redirect()->route('payments.confirmation', $lastCompleted->id)->with('info', 'Payment already completed.');
                }
                return redirect()->route('bookings.show', $booking->id)->with('info', 'This booking has no pending payments.');
            }

            // Fallback: Create new payment if no existing pending payment found (backward compatibility)
            $isCompleted = $request->payment_method === 'wallet';
            
            // Determine gateway charge based on payment method
            // Gateway fee (2.5%) ONLY applies to: Credit/Debit Card, UPI, and Net Banking
            // These methods are mapped to 'online' in the frontend
            // Wallet, NEFT, and RTGS do NOT have gateway fees
            $gatewayCharge = 0;
            if ($request->payment_method === 'online') {
                // Calculate gateway fee: 2.5% of the amount
                // Note: This fallback case should rarely occur as payments are created during booking
                $gatewayCharge = ($amount * 2.5) / 100;
            }
            
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'payment_number' => 'PM' . now()->format('YmdHis') . rand(100, 999),
                'payment_type' => 'initial',
                'payment_method' => $request->payment_method,
                'status' => $isCompleted ? 'completed' : 'pending',
                'approval_status' => $isCompleted ? 'approved' : 'pending',
                'amount' => $amount,
                'gateway_charge' => $gatewayCharge,
                'paid_at' => $isCompleted ? now() : null,
                'transaction_id' => $request->transaction_id ?? null,
            ]);
            
            // Only update booking paid_amount if payment is completed (wallet payment)
            // Use base amount (without gateway fee) for booking paid_amount
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

        // Keep booking pending until admin approval when not yet confirmed. Do not override if already confirmed (e.g. when paying installments, additional badges, or additional services).
        if ($booking->status !== 'confirmed') {
            $booking->status = 'pending';
            $booking->approval_status = $booking->approval_status ?? 'pending';
            $booking->save();
        }

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

    /**
     * Apply a discount code for part payments.
     */
    public function applyDiscount(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'discount_code' => 'required|string|max:255',
            'payment_type_option' => 'nullable|in:full,part',
        ]);

        $booking = Booking::with(['exhibition', 'payments'])
            ->where('user_id', auth()->id())
            ->findOrFail($request->booking_id);

        // Do not allow changing discounts once any payment is completed
        if ($booking->paid_amount > 0 || $booking->payments()->where('status', 'completed')->exists()) {
            $message = 'Cannot apply discount: some payments have already been completed.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        // Prevent applying a second coupon (member discount can have one coupon stacked)
        $discountType = $booking->discount_type ?? ($booking->discount_percent > 0 ? 'member' : null);
        if ($discountType === 'coupon' || $discountType === 'both') {
            $message = 'A coupon is already applied to this booking.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        // Only active discounts for this exhibition
        $discount = \App\Models\Discount::where('code', $request->discount_code)
            ->where('status', 'active')
            ->where('exhibition_id', $booking->exhibition_id)
            ->first();

        if (!$discount) {
            $message = 'Invalid or inactive discount code for this exhibition.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 404);
            }
            return back()->with('error', $message);
        }

        // If discount is restricted to a specific email, only that user can apply it
        if (! empty(trim($discount->email ?? ''))) {
            $userEmail = auth()->user()->email ?? '';
            if (strtolower(trim($discount->email)) !== strtolower(trim($userEmail))) {
                $message = 'This discount is not valid for your account.';
                if ($request->expectsJson()) {
                    return response()->json(['status' => 'error', 'message' => $message], 403);
                }
                return back()->with('error', $message);
            }
        }

        $baseTotal = $booking->total_amount;
        if ($baseTotal <= 0) {
            $message = 'Cannot apply discount to an empty booking amount.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        // Original base (before any discount) for priority allocation
        $memberDiscountPercent = (float) ($booking->member_discount_percent ?? ($booking->discount_percent > 0 ? $booking->discount_percent : 0));
        $hasMemberDiscount = $discountType === 'member' || ($discountType === null && $booking->discount_percent > 0);
        $originalBase = $hasMemberDiscount && $memberDiscountPercent > 0
            ? $baseTotal / (1 - ($memberDiscountPercent / 100))
            : $baseTotal;

        // Calculate coupon discount from code (percentage or fixed on original base)
        $couponDiscountPercentFromCode = 0;
        if ($discount->type === 'percentage') {
            $couponDiscountPercentFromCode = min((float) $discount->amount, 100.0);
        } elseif ($discount->type === 'fixed') {
            $fixedAmount = min((float) $discount->amount, $originalBase);
            $couponDiscountPercentFromCode = $originalBase > 0 ? ($fixedAmount / $originalBase) * 100 : 0;
        } else {
            $message = 'Invalid discount type configured.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        if ($couponDiscountPercentFromCode <= 0) {
            $message = 'Discount amount calculated as zero. Please check the discount configuration.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        // Always compute BOTH full and part coupon so switching payment type shows correct breakdown
        $paymentTypeOption = strtolower(trim((string) $request->input('payment_type_option', 'full'))) === 'part' ? 'part' : 'full';
        $maxPercent = $booking->exhibition->maximum_discount_apply_percent !== null
            ? (float) $booking->exhibition->maximum_discount_apply_percent
            : 100.0;
        $fullPaymentReserved = min(
            (float) ($booking->exhibition->full_payment_discount_percent ?? 0),
            $maxPercent
        );

        // Coupon effective in full context (remaining after full + member) and in part context (remaining after member only)
        $remainingForCouponFull = max(0, $maxPercent - $fullPaymentReserved - $memberDiscountPercent);
        $remainingForCouponPart = max(0, $maxPercent - $memberDiscountPercent);
        $couponEffectiveForFull = min($couponDiscountPercentFromCode, $remainingForCouponFull);
        $couponEffectiveForPart = min($couponDiscountPercentFromCode, $remainingForCouponPart);

        if ($paymentTypeOption === 'part') {
            if ($couponEffectiveForPart <= 0) {
                $maxFormatted = number_format($maxPercent, 0);
                $message = 'Maximum discount already applied for part payment. You have reached the allowed limit of ' . $maxFormatted . '%';
                if ($memberDiscountPercent > 0) {
                    $message .= ' (Member ' . number_format($memberDiscountPercent, 0) . '%)';
                }
                $message .= '. Coupon cannot be applied.';
                if ($request->expectsJson()) {
                    return response()->json(['status' => 'error', 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }
        } else {
            if ($couponEffectiveForFull <= 0) {
                $maxFormatted = number_format($maxPercent, 0);
                $alreadyAppliedPercent = $fullPaymentReserved + $memberDiscountPercent;
                if ($alreadyAppliedPercent >= $maxPercent) {
                    $message = 'Maximum discount already applied. You have reached the allowed limit of ' . $maxFormatted . '%';
                    if ($fullPaymentReserved > 0 && $memberDiscountPercent > 0) {
                        $message .= ' (Full payment ' . number_format($fullPaymentReserved, 0) . '% + Member ' . number_format($memberDiscountPercent, 0) . '%)';
                    } elseif ($fullPaymentReserved > 0) {
                        $message .= ' (Full payment ' . number_format($fullPaymentReserved, 0) . '%)';
                    } elseif ($memberDiscountPercent > 0) {
                        $message .= ' (Member ' . number_format($memberDiscountPercent, 0) . '%)';
                    }
                    $message .= '. Coupon cannot be applied.';
                } else {
                    $message = 'Total discount cannot exceed the maximum allowed (' . $maxFormatted . '%). Coupon cannot be applied.';
                }
                if ($request->expectsJson()) {
                    return response()->json(['status' => 'error', 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }
        }

        // Store BOTH: coupon_discount_percent = for full display, coupon_discount_percent_part = for part display
        // Total discount is same: full+member+coupon_full = member+coupon_part (both = max when capped)
        $totalDiscountPercent = $fullPaymentReserved + $memberDiscountPercent + $couponEffectiveForFull;
        $totalDiscountFromPart = $memberDiscountPercent + $couponEffectiveForPart;
        $totalDiscountPercent = max($totalDiscountPercent, $totalDiscountFromPart);
        $totalDiscountPercent = min($totalDiscountPercent, $maxPercent);
        $newTotalAmount = round($originalBase * (1 - ($totalDiscountPercent / 100)), 2);
        $couponDiscountAmount = round($originalBase * (($paymentTypeOption === 'part' ? $couponEffectiveForPart : $couponEffectiveForFull) / 100), 2);

        $booking->discount_type = $hasMemberDiscount ? 'both' : 'coupon';
        $booking->member_discount_percent = $hasMemberDiscount ? round($memberDiscountPercent, 2) : null;
        $booking->coupon_discount_percent = round($couponEffectiveForFull, 2);
        $booking->coupon_discount_percent_part = round($couponEffectiveForPart, 2);
        $booking->discount_percent = round($totalDiscountPercent, 2);
        $booking->total_amount = $newTotalAmount;
        $booking->save();

        $paymentSchedules = $booking->exhibition->paymentSchedules()
            ->orderBy('part_number', 'asc')
            ->get();

        if ($paymentSchedules->isEmpty()) {
            $message = 'Payment schedule not found for this exhibition.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        $payments = $booking->payments()
            ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
            ->orderBy('due_date', 'asc')
            ->get();

        if ($payments->isEmpty()) {
            $message = 'No payments found for this booking.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        // Recalculate payment amounts so they sum to the new total
        $newTotalAmount = $booking->total_amount;
        $paymentIndex = 0;
        foreach ($payments as $payment) {
            if (isset($paymentSchedules[$paymentIndex])) {
                $schedule = $paymentSchedules[$paymentIndex];
                $newPaymentAmount = round(($newTotalAmount * $schedule->percentage) / 100, 2);
                $payment->amount = $newPaymentAmount;
                $payment->save();
            }
            $paymentIndex++;
        }

        // Amounts for display (all based on original base; priority: full → member → coupon)
        $fullPaymentDisplayPercent = min(
            (float) ($booking->exhibition->full_payment_discount_percent ?? 0),
            max(0, $booking->discount_percent - ($booking->member_discount_percent ?? 0) - ($booking->coupon_discount_percent ?? 0))
        );
        $memberDiscountAmount = $booking->member_discount_percent > 0
            ? round($originalBase * ($booking->member_discount_percent / 100), 2)
            : 0;
        $fullPaymentDiscountAmount = $fullPaymentDisplayPercent > 0
            ? round($originalBase * ($fullPaymentDisplayPercent / 100), 2)
            : 0;
        $displayCouponAmount = round($couponDiscountAmount, 2);
        $effectivePct = $paymentTypeOption === 'part' ? $couponEffectiveForPart : $couponEffectiveForFull;
        $remainingUsed = $paymentTypeOption === 'part' ? $remainingForCouponPart : $remainingForCouponFull;
        $successMessage = 'Discount code applied successfully!';
        if ($effectivePct < $couponDiscountPercentFromCode && $remainingUsed > 0) {
            $successMessage = 'Coupon applied. You received ' . number_format($effectivePct, 1) . '% discount (remaining allowance up to max ' . number_format($maxPercent, 0) . '%).';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $successMessage,
                'discount_type' => $booking->discount_type,
                'discount_percent' => $booking->discount_percent,
                'member_discount_percent' => $booking->member_discount_percent,
                'coupon_discount_percent' => $booking->coupon_discount_percent,
                'coupon_discount_percent_part' => $booking->coupon_discount_percent_part,
                'full_payment_discount_percent' => round($fullPaymentDisplayPercent, 2),
                'member_discount_amount' => $memberDiscountAmount,
                'discount_amount' => $displayCouponAmount,
                'coupon_discount_amount' => $displayCouponAmount,
                'full_payment_discount_amount' => $fullPaymentDiscountAmount,
                'original_base' => round($originalBase, 2),
                'total_amount' => $booking->total_amount,
                'payments' => $payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                    ];
                })->values(),
            ]);
        }

        return back()->with(
            'success',
            $successMessage . ' You saved ₹' . number_format($displayCouponAmount, 2) . '.'
        );
    }

    /**
     * Remove an applied discount for part payments.
     */
    public function removeDiscount(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::with(['exhibition', 'payments'])
            ->where('user_id', auth()->id())
            ->findOrFail($request->booking_id);

        // Do not allow changing discounts once any payment is completed
        if ($booking->paid_amount > 0 || $booking->payments()->where('status', 'completed')->exists()) {
            $message = 'Cannot remove discount: some payments have already been completed.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        $discountType = $booking->discount_type ?? ($booking->discount_percent > 0 ? 'member' : null);

        // If only member discount (no coupon), nothing to remove
        if ($discountType === 'member' || ($discountType === null && $booking->discount_percent > 0)) {
            $message = 'No coupon applied to remove.';
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'discount_type' => $booking->discount_type ?? 'member',
                    'discount_percent' => $booking->discount_percent,
                    'member_discount_percent' => $booking->member_discount_percent ?? $booking->discount_percent,
                    'coupon_discount_percent' => $booking->coupon_discount_percent ?? 0,
                    'total_amount' => $booking->total_amount,
                    'payments' => $booking->payments()
                        ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
                        ->orderBy('due_date', 'asc')
                        ->get()
                        ->map(function ($p) {
                            return ['id' => $p->id, 'amount' => $p->amount];
                        })->values(),
                ]);
            }
            return back()->with('info', $message);
        }

        if ($booking->discount_percent <= 0) {
            $message = 'No discount is currently applied to this booking.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        $currentTotal = $booking->total_amount;
        $paymentSchedules = $booking->exhibition->paymentSchedules()
            ->orderBy('part_number', 'asc')
            ->get();

        if ($paymentSchedules->isEmpty()) {
            $message = 'Payment schedule not found for this exhibition.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        $payments = $booking->payments()
            ->orderByRaw("CASE WHEN payment_type = 'initial' THEN 1 ELSE 2 END")
            ->orderBy('due_date', 'asc')
            ->get();

        if ($payments->isEmpty()) {
            $message = 'No payments found for this booking.';
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        if ($discountType === 'both') {
            // Remove only coupon; keep member discount
            $memberDiscountPercent = (float) $booking->member_discount_percent;
            $originalTotal = $currentTotal / (1 - ($booking->discount_percent / 100));
            $memberTotal = $originalTotal * (1 - ($memberDiscountPercent / 100));

            $booking->discount_type = 'member';
            $booking->coupon_discount_percent = null;
            $booking->coupon_discount_percent_part = null;
            $booking->discount_percent = round($memberDiscountPercent, 2);
            $booking->total_amount = round($memberTotal, 2);
            $booking->save();

            $newTotalAmount = $booking->total_amount;
            $paymentIndex = 0;
            foreach ($payments as $payment) {
                if (isset($paymentSchedules[$paymentIndex])) {
                    $schedule = $paymentSchedules[$paymentIndex];
                    $newPaymentAmount = round(($newTotalAmount * $schedule->percentage) / 100, 2);
                    $payment->amount = $newPaymentAmount;
                    $payment->save();
                }
                $paymentIndex++;
            }

            $removedAmount = round($currentTotal - $newTotalAmount, 2);

            $origBase = $booking->total_amount / (1 - ($booking->discount_percent / 100));
            $fullPaymentDisplayPct = min(
                (float) ($booking->exhibition->full_payment_discount_percent ?? 0),
                max(0, $booking->discount_percent - ($booking->member_discount_percent ?? 0) - 0)
            );
            $memberDiscountAmount = $booking->member_discount_percent > 0 ? round($origBase * ($booking->member_discount_percent / 100), 2) : 0;

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Coupon discount removed successfully.',
                    'discount_type' => 'member',
                    'discount_percent' => $booking->discount_percent,
                    'member_discount_percent' => $booking->member_discount_percent,
                    'coupon_discount_percent' => 0,
                    'coupon_discount_percent_part' => 0,
                    'full_payment_discount_percent' => round($fullPaymentDisplayPct, 2),
                    'member_discount_amount' => $memberDiscountAmount,
                    'full_payment_discount_amount' => round($origBase * ($fullPaymentDisplayPct / 100), 2),
                    'original_base' => round($origBase, 2),
                    'discount_amount' => $removedAmount,
                    'total_amount' => $booking->total_amount,
                    'payments' => $payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'amount' => $payment->amount,
                        ];
                    })->values(),
                ]);
            }
            return back()->with('success', 'Coupon discount removed successfully.');
        }

        // discount_type === 'coupon': remove entire discount
        $discountPercent = (float) $booking->discount_percent;
        if ($discountPercent >= 100) {
            return back()->with('error', 'Cannot remove a 100% discount safely.');
        }
        $originalTotal = $currentTotal / (1 - ($discountPercent / 100));

        $booking->discount_type = null;
        $booking->member_discount_percent = null;
        $booking->coupon_discount_percent = null;
        $booking->coupon_discount_percent_part = null;
        $booking->discount_percent = 0;
        $booking->total_amount = round($originalTotal, 2);
        $booking->save();

        $paymentIndex = 0;
        foreach ($payments as $payment) {
            if (isset($paymentSchedules[$paymentIndex])) {
                $schedule = $paymentSchedules[$paymentIndex];
                $originalPaymentAmount = round(($originalTotal * $schedule->percentage) / 100, 2);
                $payment->amount = $originalPaymentAmount;
                $payment->save();
            }
            $paymentIndex++;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Discount removed successfully.',
                'discount_type' => null,
                'discount_percent' => 0,
                'member_discount_percent' => null,
                'coupon_discount_percent' => null,
                'coupon_discount_percent_part' => null,
                'full_payment_discount_percent' => 0,
                'member_discount_amount' => 0,
                'full_payment_discount_amount' => 0,
                'original_base' => round($originalTotal, 2),
                'discount_amount' => round($originalTotal - $currentTotal, 2),
                'total_amount' => $booking->total_amount,
                'payments' => $payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                    ];
                })->values(),
            ]);
        }

        return back()->with('success', 'Discount removed successfully.');
    }
}
