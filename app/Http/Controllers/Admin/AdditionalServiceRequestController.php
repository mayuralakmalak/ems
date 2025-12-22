<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdditionalServiceRequest;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Payment;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdditionalServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = AdditionalServiceRequest::with(['booking.user', 'booking.exhibition', 'service', 'approver'])
            ->latest();

        // Optional filter by exhibition for per-exhibition tab/view
        if ($request->filled('exhibition')) {
            $query->whereHas('booking', function ($q) use ($request) {
                $q->where('exhibition_id', $request->exhibition);
            });
        }

        $requests = $query->paginate(20);

        return view('admin.additional-service-requests.index', compact('requests'));
    }

    public function approve($id)
    {
        $request = AdditionalServiceRequest::with(['booking', 'service'])
            ->findOrFail($id);

        if ($request->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            // Update request status
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Add service to booking services
            BookingService::create([
                'booking_id' => $request->booking_id,
                'service_id' => $request->service_id,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_price' => $request->total_price,
            ]);

            // Update booking total amount
            $booking = $request->booking;
            $booking->increment('total_amount', $request->total_price);

            // Create a new payment for the additional service
            $exhibition = $booking->exhibition;
            $paymentSchedules = $exhibition->paymentSchedules()->orderBy('part_number', 'asc')->get();
            
            // Get the last payment part number to create next part
            $lastPayment = Payment::where('booking_id', $booking->id)
                ->orderBy('payment_number', 'desc')
                ->first();
            
            $partNumber = 1;
            if ($lastPayment) {
                // Extract part number from payment_number if it exists
                // Format: PM{timestamp}{booking_id}{part_number}{random}
                $partNumber = $paymentSchedules->count() + 1;
            }

            $paymentNumber = 'PM' . now()->format('YmdHis') . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . str_pad($partNumber, 2, '0', STR_PAD_LEFT) . rand(10, 99);

            Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'payment_number' => $paymentNumber,
            // Treat additional services as an extra installment payment part
            'payment_type' => 'installment',
                'payment_method' => 'online',
                'status' => 'pending',
                'approval_status' => 'pending',
                'amount' => round($request->total_price, 2),
                'gateway_charge' => 0,
                'due_date' => now()->addDays(7),
            ]);

            // Notify exhibitor
            \App\Models\Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'additional_service_request',
                'title' => 'Additional Service Approved',
                'message' => 'Your request for additional service "' . $request->service->name . '" has been approved. Please proceed with payment.',
                'notifiable_type' => AdditionalServiceRequest::class,
                'notifiable_id' => $request->id,
            ]);

            DB::commit();

            return back()->with('success', 'Additional service request approved successfully. Payment has been generated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error approving request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $serviceRequest = AdditionalServiceRequest::with(['booking'])
            ->findOrFail($id);

        if ($serviceRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $serviceRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Notify exhibitor
        \App\Models\Notification::create([
            'user_id' => $serviceRequest->booking->user_id,
            'type' => 'additional_service_request',
            'title' => 'Additional Service Request Rejected',
            'message' => 'Your request for additional service has been rejected. Reason: ' . $request->rejection_reason,
            'notifiable_type' => AdditionalServiceRequest::class,
            'notifiable_id' => $serviceRequest->id,
        ]);

        return back()->with('success', 'Additional service request rejected successfully.');
    }
}
