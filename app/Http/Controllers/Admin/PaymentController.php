<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Mail\PaymentReceiptMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['booking.user', 'booking.exhibition']);
        
        // Filter by approval status
        if ($request->has('approval_status') && $request->approval_status) {
            $query->where('approval_status', $request->approval_status);
        } else {
            // Default: show pending approvals
            $query->where('approval_status', 'pending');
        }
        
        $payments = $query->latest()->paginate(20);
        
        $pendingCount = Payment::where('approval_status', 'pending')->count();
        $approvedCount = Payment::where('approval_status', 'approved')->count();
        $rejectedCount = Payment::where('approval_status', 'rejected')->count();
        
        return view('admin.payments.index', compact('payments', 'pendingCount', 'approvedCount', 'rejectedCount'));
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
        
        // If payment is completed, send receipt emails
        if ($validated['status'] === 'completed') {
            // Reload payment with relationships for email
            $payment->load(['booking.exhibition', 'booking.booth', 'booking.bookingServices.service', 'user']);
            
            // Send payment receipt email to exhibitor
            try {
                Mail::to($payment->user->email)->send(new PaymentReceiptMail($payment, false));
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

        return redirect()->route('admin.payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function show($id)
    {
        $payment = Payment::with(['booking.user', 'booking.exhibition', 'booking.booth'])->findOrFail($id);
        return view('admin.payments.show', compact('payment'));
    }
    
    public function approve($id)
    {
        $payment = Payment::with(['booking.exhibition', 'booking.booth', 'booking.bookingServices.service', 'user'])->findOrFail($id);
        
        $payment->update([
            'approval_status' => 'approved',
            'status' => 'completed',
            'paid_at' => now(),
        ]);
        
        // Update booking paid amount
        $payment->booking->increment('paid_amount', $payment->amount);
        
        // Notify exhibitor
        \App\Models\Notification::create([
            'user_id' => $payment->user_id,
            'type' => 'payment',
            'title' => 'Payment Approved',
            'message' => 'Your payment #' . $payment->payment_number . ' of ₹' . number_format($payment->amount, 2) . ' has been approved.',
            'notifiable_type' => Payment::class,
            'notifiable_id' => $payment->id,
        ]);
        
        // Send payment receipt email to exhibitor
        try {
            Mail::to($payment->user->email)->send(new PaymentReceiptMail($payment, false));
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
        
        return back()->with('success', 'Payment approved successfully.');
    }
    
    public function reject(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $payment->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        // Notify exhibitor
        \App\Models\Notification::create([
            'user_id' => $payment->user_id,
            'type' => 'payment',
            'title' => 'Payment Rejected',
            'message' => 'Your payment #' . $payment->payment_number . ' has been rejected. Reason: ' . $request->rejection_reason,
            'notifiable_type' => Payment::class,
            'notifiable_id' => $payment->id,
        ]);
        
        return back()->with('success', 'Payment rejected.');
    }
}
