<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Exhibition;
use App\Mail\PaymentReceiptMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('Payment Management - View'), 403);
        $query = Payment::with(['booking.user', 'booking.exhibition', 'booking.booth']);

        // Filter by approval status (defaults to pending)
        if ($request->has('approval_status') && $request->approval_status) {
            $query->where('approval_status', $request->approval_status);
        } else {
            $query->where('approval_status', 'pending');
        }

        // Free-text search (payment #, transaction ID, exhibitor, exhibition)
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', '%' . $search . '%')
                    ->orWhere('transaction_id', 'like', '%' . $search . '%')
                    ->orWhereHas('booking.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('booking.exhibition', function ($exhibitionQuery) use ($search) {
                        $exhibitionQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter by exhibition
        if ($request->filled('exhibition_id')) {
            $exhibitionId = $request->get('exhibition_id');
            $query->whereHas('booking', function ($q) use ($exhibitionId) {
                $q->where('exhibition_id', $exhibitionId);
            });
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        // Filter by created date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Statistics (overall)
        $pendingCount = Payment::where('approval_status', 'pending')->count();
        $approvedCount = Payment::where('approval_status', 'approved')->count();
        $rejectedCount = Payment::where('approval_status', 'rejected')->count();

        $exhibitions = Exhibition::all();

        // Export branch: download CSV for current filters (or default pending list)
        if ($request->get('export') === '1') {
            abort_unless(auth()->user()->can('Payment Management - Download'), 403);
            $payments = $query->latest()->get();
            return $this->exportPayments($payments);
        }

        $payments = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.payments.index', compact('payments', 'pendingCount', 'approvedCount', 'rejectedCount', 'exhibitions'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('Payment Management - Create'), 403);
        $bookings = Booking::with(['user', 'exhibition'])->where('status', 'confirmed')->get();
        return view('admin.payments.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('Payment Management - Create'), 403);
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
        abort_unless(auth()->user()->can('Payment Management - View'), 403);
        $payment = Payment::with(['booking.user', 'booking.exhibition', 'booking.booth'])->findOrFail($id);
        return view('admin.payments.show', compact('payment'));
    }
    
    public function approve($id)
    {
        abort_unless(auth()->user()->can('Payment Management - Modify'), 403);
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
        abort_unless(auth()->user()->can('Payment Management - Modify'), 403);
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
    
    public function history(Request $request)
    {
        abort_unless(auth()->user()->can('Payment Management - View'), 403);
        $query = Payment::with(['booking.user', 'booking.exhibition', 'booking.booth'])
            ->where('approval_status', 'approved');
        
        // Search by payment number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('booking.user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('booking.exhibition', function($exhibitionQuery) use ($search) {
                      $exhibitionQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by payment type
        if ($request->has('payment_type') && $request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        // Filter by exhibition
        if ($request->filled('exhibition_id')) {
            $exhibitionId = $request->get('exhibition_id');
            $query->whereHas('booking', function ($q) use ($exhibitionId) {
                $q->where('exhibition_id', $exhibitionId);
            });
        }

        $exhibitions = Exhibition::all();

        // Export branch: download CSV for current filters (approved history)
        if ($request->get('export') === '1') {
            $payments = $query->latest('paid_at')->get();
            return $this->exportPayments($payments);
        }

        $payments = $query->latest('paid_at')->paginate(20)->appends($request->query());
        
        // Statistics
        $totalApproved = Payment::where('approval_status', 'approved')->count();
        $totalAmount = Payment::where('approval_status', 'approved')->sum('amount');
        $todayApproved = Payment::where('approval_status', 'approved')
            ->whereDate('paid_at', today())
            ->count();
        $todayAmount = Payment::where('approval_status', 'approved')
            ->whereDate('paid_at', today())
            ->sum('amount');
        
        return view('admin.payments.history', compact(
            'payments', 
            'totalApproved', 
            'totalAmount', 
            'todayApproved', 
            'todayAmount',
            'exhibitions'
        ));
    }

    /**
     * Export payments (for approvals or history) as CSV.
     */
    private function exportPayments($payments)
    {
        $fileName = 'payments-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');

            // CSV header
            fputcsv($handle, [
                'Payment #',
                'Exhibitor',
                'Exhibitor Email',
                'Exhibition',
                'Booth(s)',
                'Amount',
                'Payment Method',
                'Payment Type',
                'Approval Status',
                'Status',
                'Transaction ID',
                'Created At',
                'Paid At',
            ]);

            foreach ($payments as $payment) {
                $booking = $payment->booking;

                // Determine booth names (primary booth or from selected_booth_ids)
                $boothNames = '';
                if ($booking) {
                    if ($booking->booth) {
                        $boothNames = $booking->booth->name;
                    } elseif ($booking->selected_booth_ids) {
                        $selected = $booking->selected_booth_ids;
                        $ids = [];
                        if (is_array($selected)) {
                            $first = reset($selected);
                            if (is_array($first) && isset($first['id'])) {
                                $ids = collect($selected)->pluck('id')->filter()->values()->all();
                            } else {
                                $ids = collect($selected)->filter()->values()->all();
                            }
                        }
                        if (!empty($ids)) {
                            $boothNames = \App\Models\Booth::whereIn('id', $ids)->pluck('name')->implode(', ');
                        }
                    }
                }

                fputcsv($handle, [
                    $payment->payment_number,
                    optional(optional($booking)->user)->name,
                    optional(optional($booking)->user)->email,
                    optional(optional($booking)->exhibition)->name,
                    $boothNames,
                    $payment->amount,
                    $payment->payment_method,
                    $payment->payment_type,
                    $payment->approval_status,
                    $payment->status,
                    $payment->transaction_id,
                    optional($payment->created_at)->format('Y-m-d H:i:s'),
                    optional($payment->paid_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
