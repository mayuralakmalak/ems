<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\AdditionalServiceRequest;
use App\Models\Service;
use App\Models\ExhibitionAddonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdditionalServiceRequestController extends Controller
{
    public function store(Request $request, $bookingId)
    {
        $booking = Booking::with('exhibition.addonServices')
            ->where('user_id', auth()->id())
            ->findOrFail($bookingId);

        // Only allow requests for confirmed bookings
        if ($booking->status !== 'confirmed' || $booking->approval_status !== 'approved') {
            return back()->with('error', 'You can only request additional services for confirmed bookings.');
        }

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Get the service
        $service = Service::findOrFail($request->service_id);

        // Get exhibition-specific price and cut-off
        $addonService = ExhibitionAddonService::where('exhibition_id', $booking->exhibition_id)
            ->where('item_name', $service->name)
            ->first();

        if (!$addonService) {
            return back()->with('error', 'This service is not available for this exhibition.');
        }

        // Enforce cut-off date: per-service first, then fall back to exhibition-level cut-off (if configured)
        $cutoffDate = $addonService->cutoff_date ?? $booking->exhibition->addon_services_cutoff_date ?? null;
        if ($cutoffDate && now()->greaterThan($cutoffDate->endOfDay())) {
            return back()->with('error', 'The cut-off date for this additional service has passed. You can no longer request it.');
        }

        $unitPrice = $addonService->price_per_quantity;
        $totalPrice = $unitPrice * $request->quantity;

        // Check if there's already a pending request for this service
        $existingRequest = AdditionalServiceRequest::where('booking_id', $booking->id)
            ->where('service_id', $service->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending request for this service.');
        }

        // Create the request
        $serviceRequest = AdditionalServiceRequest::create([
            'booking_id' => $booking->id,
            'service_id' => $service->id,
            'quantity' => $request->quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        // Notify admins
        $admins = \App\Models\User::role('Admin')->orWhere('id', 1)->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'additional_service_request',
                'title' => 'New Additional Service Request',
                'message' => Auth::user()->name . ' has requested additional service "' . $service->name . '" for booking #' . $booking->booking_number,
                'notifiable_type' => AdditionalServiceRequest::class,
                'notifiable_id' => $serviceRequest->id,
            ]);
        }

        return back()->with('success', 'Additional service request submitted successfully. Admin will review and approve it.');
    }
}
