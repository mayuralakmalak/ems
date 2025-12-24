<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Document;
use App\Models\Wallet;
use App\Models\Notification;
use App\Mail\DocumentStatusMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['exhibition', 'booth', 'user', 'payments']);
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $bookings = $query->latest()->paginate(20);
        
        return view('admin.bookings.index', compact('bookings'));
    }

    public function edit($id)
    {
        $booking = Booking::with(['exhibition', 'booth', 'user'])->findOrFail($id);
        $statuses = ['pending', 'confirmed', 'cancelled', 'replaced'];

        return view('admin.bookings.edit', compact('booking', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::with('booth')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,replaced',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0|max:' . $request->total_amount,
        ]);

        $oldStatus = $booking->status;
        $booking->update([
            'status' => $request->status,
            'total_amount' => $request->total_amount,
            'paid_amount' => $request->paid_amount,
        ]);

        // Notify exhibitor if status changed to confirmed or rejected
        if ($oldStatus !== $request->status) {
            if ($request->status === 'confirmed') {
                \App\Models\Notification::create([
                    'user_id' => $booking->user_id,
                    'type' => 'booking',
                    'title' => 'Booking Approved',
                    'message' => 'Your booking request #' . $booking->booking_number . ' has been approved.',
                    'notifiable_type' => \App\Models\Booking::class,
                    'notifiable_id' => $booking->id,
                ]);
            } elseif ($request->status === 'cancelled') {
                \App\Models\Notification::create([
                    'user_id' => $booking->user_id,
                    'type' => 'booking',
                    'title' => 'Booking Cancelled',
                    'message' => 'Your booking #' . $booking->booking_number . ' has been cancelled.',
                    'notifiable_type' => \App\Models\Booking::class,
                    'notifiable_id' => $booking->id,
                ]);
            }
        }

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Booking updated successfully.');
    }

    public function bookedByExhibition($exhibitionId)
    {
        $bookings = Booking::with(['exhibition', 'booth', 'user'])
            ->where('exhibition_id', $exhibitionId)
            ->latest()
            ->paginate(20);

        return view('admin.bookings.booked-booths', compact('bookings', 'exhibitionId'));
    }
    
    public function cancellations()
    {
        $cancellationRequests = Booking::with(['exhibition', 'booth', 'user', 'payments'])
            ->where('status', 'cancelled')
            ->orWhereNotNull('cancellation_reason')
            ->latest()
            ->get();
        
        // Calculate statistics
        $totalBookings = Booking::count();
        $pendingCancellations = Booking::where('status', 'cancelled')
            ->whereNull('cancellation_type')
            ->count();
        $approvedRefunds = Booking::where('cancellation_type', 'refund')
            ->sum('cancellation_amount');
        $cancellationCharges = Booking::whereNotNull('cancellation_amount')
            ->sum('cancellation_amount');
        
        return view('admin.bookings.cancellations', compact(
            'cancellationRequests',
            'totalBookings',
            'pendingCancellations',
            'approvedRefunds',
            'cancellationCharges'
        ));
    }
    
    public function manageCancellation($id)
    {
        $booking = Booking::with(['exhibition', 'booth', 'user', 'payments', 'documents'])
            ->findOrFail($id);
        
        // Calculate cancellation charges (15% of total)
        $cancellationCharge = ($booking->total_amount * 15) / 100;
        $refundAmount = $booking->total_amount - $cancellationCharge;
        
        return view('admin.bookings.manage-cancellation', compact('booking', 'cancellationCharge', 'refundAmount'));
    }
    
    public function approveCancellation(Request $request, $id)
    {
        $booking = Booking::with(['user', 'booth'])->findOrFail($id);
        
        $request->validate([
            'cancellation_type' => 'required|in:refund,wallet_credit',
            'account_details' => 'required_if:cancellation_type,refund|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            $cancellationCharge = ($booking->total_amount * 15) / 100;
            $refundAmount = $booking->total_amount - $cancellationCharge;
            
            $booking->update([
                'cancellation_type' => $request->cancellation_type,
                'cancellation_amount' => $cancellationCharge,
                'account_details' => $request->account_details,
                'status' => 'cancelled',
            ]);
            
            // Process refund or wallet credit
            if ($request->cancellation_type === 'wallet_credit') {
                Wallet::create([
                    'user_id' => $booking->user_id,
                    'balance' => ($booking->user->wallet_balance ?? 0) + $refundAmount,
                    'transaction_type' => 'credit',
                    'amount' => $refundAmount,
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
            
            return redirect()->route('admin.bookings.cancellations')
                ->with('success', 'Cancellation approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve cancellation: ' . $e->getMessage());
        }
    }
    
    public function rejectCancellation(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $booking->update([
            'status' => 'confirmed',
            'cancellation_reason' => null,
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        return redirect()->route('admin.bookings.cancellations')
            ->with('success', 'Cancellation rejected.');
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

    public function destroy($id)
    {
        $booking = Booking::with('booth')->findOrFail($id);

        DB::transaction(function () use ($booking) {
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }
            $booking->delete();
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function approveDocument($documentId)
    {
        $document = Document::with(['booking', 'user', 'booking.exhibition', 'requiredDocument'])->findOrFail($documentId);
        $document->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        // Notify exhibitor
        Notification::create([
            'user_id' => $document->user_id,
            'type' => 'document',
            'title' => 'Document Approved',
            'message' => 'Your document "' . $document->name . '" has been approved.',
            'notifiable_type' => Document::class,
            'notifiable_id' => $document->id,
        ]);

        // Send approval email to exhibitor
        try {
            if ($document->user && $document->user->email) {
                Mail::to($document->user->email)->send(
                    new DocumentStatusMail($document, 'approved', null)
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send document approval email: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'user_email' => $document->user->email ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Document approved.');
    }

    public function rejectDocument(Request $request, $documentId)
    {
        $document = Document::with(['booking', 'user', 'booking.exhibition', 'requiredDocument'])->findOrFail($documentId);

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Notify exhibitor
        Notification::create([
            'user_id' => $document->user_id,
            'type' => 'document',
            'title' => 'Document Rejected',
            'message' => 'Your document "' . $document->name . '" has been rejected. Reason: ' . $request->rejection_reason,
            'notifiable_type' => Document::class,
            'notifiable_id' => $document->id,
        ]);

        // Send rejection email to exhibitor
        try {
            if ($document->user && $document->user->email) {
                Mail::to($document->user->email)->send(
                    new DocumentStatusMail($document, 'rejected', $request->rejection_reason)
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send document rejection email: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'user_email' => $document->user->email ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Document rejected with reason recorded.');
    }
}

