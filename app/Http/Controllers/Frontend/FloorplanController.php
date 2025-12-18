<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use App\Models\Booth;
use App\Models\BoothRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FloorplanController extends Controller
{
    public function show($id)
    {
        $exhibition = Exhibition::with('booths')->findOrFail($id);
        
        // Get all booths that are reserved (pending booking - regardless of payment status)
        // A booth is reserved when booking exists with approval_status = 'pending'
        $reservedBookings = \App\Models\Booking::where('exhibition_id', $id)
            ->where('approval_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get();
        
        // Collect all reserved booth IDs (including from selected_booth_ids)
        $reservedBoothIds = [];
        foreach ($reservedBookings as $booking) {
            // Add primary booth_id
            if ($booking->booth_id) {
                $reservedBoothIds[] = $booking->booth_id;
            }
            
            // Also include booths from selected_booth_ids
            // Get the array value first to avoid indirect modification error
            $selectedBoothIds = $booking->selected_booth_ids;
            if ($selectedBoothIds && is_array($selectedBoothIds) && !empty($selectedBoothIds)) {
                // Check if it's array of objects: [{'id': 1, 'name': 'B001'}, ...]
                $firstItem = reset($selectedBoothIds);
                if (is_array($firstItem) && isset($firstItem['id'])) {
                    // Array of objects format - extract IDs
                    foreach ($selectedBoothIds as $item) {
                        if (isset($item['id'])) {
                            $reservedBoothIds[] = $item['id'];
                        }
                    }
                } else {
                    // Simple array format: [1, 2, 3] - use directly
                    foreach ($selectedBoothIds as $boothId) {
                        if ($boothId) {
                            $reservedBoothIds[] = $boothId;
                        }
                    }
                }
            }
        }
        $reservedBoothIds = array_values(array_unique(array_filter($reservedBoothIds)));
        
        // Get all booths that are booked (approved)
        $bookedBookings = \App\Models\Booking::where('exhibition_id', $id)
            ->where('approval_status', 'approved')
            ->where('status', 'confirmed')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get();
        
        // Collect all booked booth IDs (including from selected_booth_ids)
        $bookedBoothIds = [];
        foreach ($bookedBookings as $booking) {
            // Add primary booth_id
            if ($booking->booth_id) {
                $bookedBoothIds[] = $booking->booth_id;
            }
            
            // Also include booths from selected_booth_ids
            // Get the array value first to avoid indirect modification error
            $selectedBoothIds = $booking->selected_booth_ids;
            if ($selectedBoothIds && is_array($selectedBoothIds) && !empty($selectedBoothIds)) {
                // Check if it's array of objects: [{'id': 1, 'name': 'B001'}, ...]
                $firstItem = reset($selectedBoothIds);
                if (is_array($firstItem) && isset($firstItem['id'])) {
                    // Array of objects format - extract IDs
                    foreach ($selectedBoothIds as $item) {
                        if (isset($item['id'])) {
                            $bookedBoothIds[] = $item['id'];
                        }
                    }
                } else {
                    // Simple array format: [1, 2, 3] - use directly
                    foreach ($selectedBoothIds as $boothId) {
                        if ($boothId) {
                            $bookedBoothIds[] = $boothId;
                        }
                    }
                }
            }
        }
        $bookedBoothIds = array_values(array_unique(array_filter($bookedBoothIds)));
        
        // If user is authenticated, use exhibitor layout, otherwise use public layout
        if (auth()->check()) {
            // Get user's bookings for this exhibition
            $bookings = \App\Models\Booking::with(['booth', 'payments'])
                ->where('user_id', auth()->id())
                ->where('exhibition_id', $id)
                ->latest()
                ->get();
            
            // Get payments for this exhibition
            $payments = \App\Models\Payment::whereHas('booking', function($query) use ($id) {
                $query->where('exhibition_id', $id)->where('user_id', auth()->id());
            })
            ->with('booking')
            ->latest()
            ->get();
            
            return view('frontend.floorplan.show', compact('exhibition', 'bookings', 'payments', 'reservedBoothIds', 'bookedBoothIds'));
        } else {
            return view('frontend.floorplan.public', compact('exhibition', 'reservedBoothIds', 'bookedBoothIds'));
        }
    }

    public function requestMerge(Request $request, $exhibitionId)
    {
        $request->validate([
            'booth_ids' => 'required|array|min:2',
            'booth_ids.*' => 'exists:booths,id',
            'new_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Verify booths belong to exhibition and are available
        $booths = Booth::whereIn('id', $request->booth_ids)
            ->where('exhibition_id', $exhibitionId)
            ->where('is_booked', false)
            ->where('is_available', true)
            ->get();

        if ($booths->count() < 2) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'At least 2 available booths required for merging'], 400);
            }
            return back()->with('error', 'At least 2 available booths required for merging');
        }

        $exhibition = Exhibition::findOrFail($exhibitionId);

            // Merge booths immediately (no admin approval needed)
        DB::beginTransaction();
        try {
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

            // Use provided name or generate from booth names
            $finalName = $request->new_name ?: $mergedNames;

            // Calculate merged booth position to cover original booths
            $positions = $booths->map(function($booth) {
                return [
                    'x1' => $booth->position_x ?? 0,
                    'y1' => $booth->position_y ?? 0,
                    'x2' => ($booth->position_x ?? 0) + ($booth->width ?? 100),
                    'y2' => ($booth->position_y ?? 0) + ($booth->height ?? 80),
                ];
            });

            $minX = $positions->min('x1');
            $minY = $positions->min('y1');
            $maxX = $positions->max('x2');
            $maxY = $positions->max('y2');

            $mergedWidth = max(100, $maxX - $minX);
            $mergedHeight = max(80, $maxY - $minY);
            
            // Create merged booth
            $mergedBooth = Booth::create([
                'exhibition_id' => $exhibitionId,
                'name' => $finalName,
                'category' => $booths->first()->category,
                'booth_type' => $booths->first()->booth_type,
                'size_sqft' => $totalSize,
                'sides_open' => $maxSidesOpen,
                'price' => $mergedPrice,
                'is_merged' => true,
                'merged_booths' => $booths->pluck('id')->toArray(),
                'is_available' => true, // Merged booth is available for booking
                'is_booked' => false,
                'position_x' => $minX,
                'position_y' => $minY,
                'width' => $mergedWidth,
                'height' => $mergedHeight,
            ]);

            // Mark original booths as merged and unavailable
            foreach ($booths as $booth) {
                $booth->update([
                    'is_available' => false,
                    'is_merged' => true,
                    'parent_booth_id' => $mergedBooth->id,
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Booths merged successfully! The merged booth is now available for booking.',
                    'merged_booth_id' => $mergedBooth->id,
                    'merged_booth_name' => $mergedBooth->name,
                    'merged_booth_price' => $mergedBooth->price,
                    'merged_booth_size' => $mergedBooth->size_sqft,
                    'redirect' => route('bookings.book', $exhibitionId)
                ]);
            }

            return redirect()->route('bookings.book', $exhibitionId)
                ->with('success', 'Booths merged successfully! The merged booth "' . $mergedBooth->name . '" is now available for booking.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Merge failed: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Merge failed: ' . $e->getMessage());
        }
    }

    public function requestSplit(Request $request, $exhibitionId, $boothId)
    {
        $booth = Booth::where('exhibition_id', $exhibitionId)
            ->where('is_booked', false)
            ->where('is_available', true)
            ->findOrFail($boothId);

        $request->validate([
            'split_count' => 'required|integer|min:2|max:4',
            'new_names' => 'required|array|size:' . $request->split_count,
            'new_names.*' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $splitCount = $request->split_count;
            $sizePerBooth = $booth->size_sqft / $splitCount;
            $pricePerBooth = $booth->price / $splitCount;

            $baseX = $booth->position_x ?? 0;
            $baseY = $booth->position_y ?? 0;
            $originalWidth = $booth->width ?? 100;
            $originalHeight = $booth->height ?? 80;

            // Determine grid layout (max 2 columns for better shape)
            $cols = $splitCount <= 2 ? $splitCount : 2;
            $rows = (int) ceil($splitCount / $cols);
            $childWidth = $originalWidth / $cols;
            $childHeight = $originalHeight / $rows;

            for ($i = 0; $i < $splitCount; $i++) {
                $col = $i % $cols;
                $row = (int) floor($i / $cols);
                $x = $baseX + ($col * $childWidth);
                $y = $baseY + ($row * $childHeight);

                Booth::create([
                    'exhibition_id' => $exhibitionId,
                    'name' => $request->new_names[$i],
                    'category' => $booth->category,
                    'booth_type' => $booth->booth_type,
                    'size_sqft' => $sizePerBooth,
                    'sides_open' => $booth->sides_open,
                    'price' => $pricePerBooth,
                    'is_split' => true,
                    'parent_booth_id' => $booth->id,
                    'position_x' => $x,
                    'position_y' => $y,
                    'width' => $childWidth,
                    'height' => $childHeight,
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }

            // Mark original booth unavailable
            $booth->update([
                'is_available' => false,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booth split successfully. New booths are now available for booking.',
                    'redirect' => route('bookings.book', $exhibitionId)
                ]);
            }

            return redirect()->route('bookings.book', $exhibitionId)
                ->with('success', 'Booth split successfully. New booths are now available for booking.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Split failed: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Split failed: ' . $e->getMessage());
        }
    }
}
