<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SponsorshipBooking;
use App\Models\SponsorshipPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SponsorshipBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = SponsorshipBooking::with(['sponsorship', 'exhibition', 'user', 'payments']);
        
        // Filters
        if ($request->has('exhibition_id') && $request->exhibition_id) {
            $query->where('exhibition_id', $request->exhibition_id);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->has('approval_status') && $request->approval_status) {
            $query->where('approval_status', $request->approval_status);
        }
        
        $bookings = $query->latest()->paginate(20);
        $exhibitions = \App\Models\Exhibition::where('status', 'active')->get();
        
        return view('admin.sponsorship-bookings.index', compact('bookings', 'exhibitions'));
    }
    
    public function show($id)
    {
        $booking = SponsorshipBooking::with([
            'sponsorship',
            'exhibition',
            'user',
            'payments',
            'booking',
            'approver'
        ])->findOrFail($id);
        
        return view('admin.sponsorship-bookings.show', compact('booking'));
    }
    
    public function approve($id)
    {
        $booking = SponsorshipBooking::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $booking->update([
                'approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'status' => 'confirmed',
            ]);
            
            DB::commit();
            
            // Send email notification to user
            // TODO: Implement email notification
            
            return back()->with('success', 'Sponsorship booking approved successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve booking: ' . $e->getMessage());
        }
    }
    
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $booking = SponsorshipBooking::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $booking->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'status' => 'cancelled',
            ]);
            
            DB::commit();
            
            // Send email notification to user
            // TODO: Implement email notification
            
            return back()->with('success', 'Sponsorship booking rejected successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject booking: ' . $e->getMessage());
        }
    }
    
    public function approvePayment($paymentId)
    {
        $payment = SponsorshipPayment::findOrFail($paymentId);
        
        DB::beginTransaction();
        try {
            $payment->update([
                'approval_status' => 'approved',
                'status' => 'completed',
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
            
            // Send receipt email
            // TODO: Implement email notification
            
            return back()->with('success', 'Payment approved successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve payment: ' . $e->getMessage());
        }
    }
    
    public function rejectPayment(Request $request, $paymentId)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $payment = SponsorshipPayment::findOrFail($paymentId);
        
        $payment->update([
            'approval_status' => 'rejected',
            'status' => 'failed',
            'rejection_reason' => $validated['rejection_reason'],
        ]);
        
        return back()->with('success', 'Payment rejected successfully.');
    }
}

