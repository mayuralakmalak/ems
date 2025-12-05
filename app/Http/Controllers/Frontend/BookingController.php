<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\Booking;
use App\Models\Exhibition;
use App\Models\Service;
use App\Models\BookingService;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // Handle both old format (booth_id) and new format (booth_ids[])
        $boothIds = $request->booth_ids ?? ($request->booth_id ? [$request->booth_id] : []);
        
        $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'booth_ids' => 'required_without:booth_id|array|min:1',
            'booth_ids.*' => 'exists:booths,id',
            'booth_id' => 'required_without:booth_ids|exists:booths,id',
            'merge_booths' => 'nullable|boolean',
            'contact_emails' => 'nullable|array|max:5',
            'contact_emails.*' => 'email',
            'contact_numbers' => 'nullable|array|max:5',
            'services' => 'nullable|array',
            'services.*.service_id' => 'exists:services,id',
            'services.*.quantity' => 'integer|min:1',
        ]);

        $user = auth()->user();
        $exhibition = Exhibition::findOrFail($request->exhibition_id);
        
        // Normalize booth IDs
        if (empty($boothIds) && $request->booth_id) {
            $boothIds = [$request->booth_id];
        }
        
        DB::beginTransaction();
        try {
            // Check if booths are available
            $booths = Booth::whereIn('id', $boothIds)
                ->where('exhibition_id', $exhibition->id)
                ->where('is_available', true)
                ->get();

            if ($booths->count() !== count($boothIds)) {
                return back()->with('error', 'One or more booths are not available.');
            }

            // Handle booth merging
            if ($request->merge_booths && count($boothIds) > 1) {
                $mergedBooth = $this->mergeBooths($booths, $exhibition);
                $boothId = $mergedBooth->id;
                $totalAmount = $mergedBooth->price;
            } else {
                // Single booth or multiple separate booths
                if (count($boothIds) === 1) {
                    $booth = $booths->first();
                    $boothId = $booth->id;
                    $totalAmount = $booth->price;
                } else {
                    // Multiple booths - create separate bookings for each
                    $totalAmount = $booths->sum('price');
                    $boothId = $booths->first()->id; // Primary booth
                }
            }

            // Calculate additional services total
            $servicesTotal = 0;
            if ($request->services) {
                foreach ($request->services as $serviceData) {
                    $service = Service::find($serviceData['service_id']);
                    if ($service && $service->exhibition_id === $exhibition->id) {
                        $quantity = $serviceData['quantity'] ?? 1;
                        $servicesTotal += $service->price * $quantity;
                    }
                }
            }

            $totalAmount += $servicesTotal;

            // Create booking
            $booking = Booking::create([
                'exhibition_id' => $exhibition->id,
                'user_id' => $user->id,
                'booth_id' => $boothId,
                'booking_number' => 'BK' . now()->format('YmdHis') . rand(100, 999),
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'contact_emails' => $request->contact_emails ?? [],
                'contact_numbers' => $request->contact_numbers ?? [],
            ]);

            // Mark booths as booked
            foreach ($booths as $booth) {
                $booth->update([
                    'is_available' => false,
                    'is_booked' => true,
                ]);
            }

            // Add additional services
            if ($request->services) {
                foreach ($request->services as $serviceData) {
                    $service = Service::find($serviceData['service_id']);
                    if ($service && $service->exhibition_id === $exhibition->id) {
                        $quantity = $serviceData['quantity'] ?? 1;
                        BookingService::create([
                            'booking_id' => $booking->id,
                            'service_id' => $service->id,
                            'quantity' => $quantity,
                            'unit_price' => $service->price,
                            'total_price' => $service->price * $quantity,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('bookings.show', $booking->id)
                ->with('success', 'Booth booked successfully! Please complete payment to confirm.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Booking failed: ' . $e->getMessage());
        }
    }

    private function mergeBooths($booths, $exhibition)
    {
        $mergedNames = $booths->pluck('name')->sort()->implode('');
        $totalSize = $booths->sum('size_sqft');
        $totalPrice = $booths->sum('price');
        $maxSidesOpen = $booths->max('sides_open');
        
        // Calculate merged price based on exhibition pricing
        $basePrice = $exhibition->price_per_sqft ?? 0;
        $mergedPrice = $totalSize * $basePrice;
        
        // Apply side open percentage
        $sideOpenPercent = 0;
        if ($maxSidesOpen == 1) $sideOpenPercent = $exhibition->side_1_open_percent ?? 0;
        elseif ($maxSidesOpen == 2) $sideOpenPercent = $exhibition->side_2_open_percent ?? 0;
        elseif ($maxSidesOpen == 3) $sideOpenPercent = $exhibition->side_3_open_percent ?? 0;
        elseif ($maxSidesOpen == 4) $sideOpenPercent = $exhibition->side_4_open_percent ?? 0;
        
        $mergedPrice = $mergedPrice * (1 + $sideOpenPercent / 100);

        // Create merged booth
        $mergedBooth = Booth::create([
            'exhibition_id' => $exhibition->id,
            'name' => $mergedNames,
            'category' => $booths->first()->category,
            'booth_type' => $booths->first()->booth_type,
            'size_sqft' => $totalSize,
            'sides_open' => $maxSidesOpen,
            'price' => $mergedPrice,
            'is_merged' => true,
            'merged_booths' => $booths->pluck('id')->toArray(),
        ]);

        return $mergedBooth;
    }

    public function show(string $id)
    {
        $booking = Booking::with(['exhibition.booths', 'booth', 'payments', 'bookingServices.service', 'documents', 'badges'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('frontend.bookings.show', compact('booking'));
    }

    public function update(Request $request, string $id)
    {
        $booking = Booking::where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'contact_emails' => 'nullable|array|max:5',
            'contact_emails.*' => 'email',
            'contact_numbers' => 'nullable|array|max:5',
        ]);

        $booking->update([
            'contact_emails' => $request->contact_emails ?? $booking->contact_emails,
            'contact_numbers' => $request->contact_numbers ?? $booking->contact_numbers,
        ]);

        return back()->with('success', 'Booking updated successfully.');
    }

    public function cancel(Request $request, string $id)
    {
        $booking = Booking::with('booth')->where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        // Update booking status
        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        // Free up the booth
        if ($booking->booth) {
            $booking->booth->update([
                'is_available' => true,
                'is_booked' => false,
            ]);
        }

        // Admin will decide on refund later
        return back()->with('success', 'Booking cancelled. Admin will process refund/wallet credit.');
    }

    public function replace(Request $request, string $id)
    {
        $booking = Booking::where('user_id', auth()->id())->findOrFail($id);
        $exhibition = $booking->exhibition;
        
        $request->validate([
            'new_booth_id' => 'required|exists:booths,id',
        ]);

        $newBooth = Booth::where('id', $request->new_booth_id)
            ->where('exhibition_id', $exhibition->id)
            ->where('is_available', true)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Free old booth
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }

            // Calculate price difference
            $priceDifference = $newBooth->price - $booking->booth->price;
            $newTotalAmount = $booking->total_amount + $priceDifference;

            // Update booking
            $booking->update([
                'booth_id' => $newBooth->id,
                'status' => 'replaced',
                'total_amount' => $newTotalAmount,
            ]);

            // Mark new booth as booked
            $newBooth->update([
                'is_available' => false,
                'is_booked' => true,
            ]);

            DB::commit();

            return back()->with('success', 'Booth replaced successfully. Price difference: ₹' . number_format($priceDifference, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Booth replacement failed: ' . $e->getMessage());
        }
    }
}
