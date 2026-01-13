<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoothRequest;
use App\Models\Booth;
use App\Models\Booking;
use App\Models\ExhibitionBoothSizeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoothRequestController extends Controller
{
    public function index()
    {
        $requests = BoothRequest::with(['exhibition', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        // Attach related booking details (latest matching booking) for quick view
        $requests->getCollection()->transform(function ($request) {
            $request->booking = Booking::where('user_id', $request->user_id)
                ->where('exhibition_id', $request->exhibition_id)
                ->when($request->booth_ids, function ($query) use ($request) {
                    $query->whereIn('booth_id', $request->booth_ids);
                })
                ->latest()
                ->first();
            return $request;
        });
        
        return view('admin.booth-requests.index', compact('requests'));
    }

    public function show($id)
    {
        $boothRequest = BoothRequest::with(['exhibition', 'user'])->findOrFail($id);

        $booking = Booking::with(['exhibition', 'user', 'booth', 'payments', 'documents', 'bookingServices.service'])
            ->where('user_id', $boothRequest->user_id)
            ->where('exhibition_id', $boothRequest->exhibition_id)
            ->when($boothRequest->booth_ids, function ($query) use ($boothRequest) {
                $query->whereIn('booth_id', $boothRequest->booth_ids);
            })
            ->latest()
            ->first();
        
        // Load all booths from booth request with their details
        $allBooths = collect();
        if ($boothRequest->booth_ids) {
            $allBooths = Booth::whereIn('id', $boothRequest->booth_ids)->get();
        }
        
        // Also load booths from booking's selected_booth_ids if available
        $selectedBooths = collect();
        $boothTypeMap = []; // Map booth ID to type from selected_booth_ids
        if ($booking && $booking->selected_booth_ids) {
            $selectedBoothIds = [];
            if (is_array($booking->selected_booth_ids)) {
                // Handle array format: [{'id': 1, 'name': 'B001', 'type': 'Orphand'}, ...]
                if (isset($booking->selected_booth_ids[0]) && is_array($booking->selected_booth_ids[0])) {
                    $selectedBoothIds = collect($booking->selected_booth_ids)
                        ->pluck('id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                    
                    // Build type map from selected_booth_ids
                    foreach ($booking->selected_booth_ids as $boothData) {
                        if (is_array($boothData) && isset($boothData['id']) && isset($boothData['type'])) {
                            $boothTypeMap[$boothData['id']] = $boothData['type'];
                        }
                    }
                } else {
                    // Handle simple array format: [1, 2, 3]
                    $selectedBoothIds = collect($booking->selected_booth_ids)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }
            }
            
            if (!empty($selectedBoothIds)) {
                $selectedBooths = Booth::whereIn('id', $selectedBoothIds)->get();
                // Override booth_type from selected_booth_ids if available
                foreach ($selectedBooths as $booth) {
                    if (isset($boothTypeMap[$booth->id])) {
                        $booth->booth_type = $boothTypeMap[$booth->id];
                    }
                }
            }
        }
        
        // Merge all booths, prioritizing selected booths from booking
        $displayBooths = $selectedBooths->isNotEmpty() ? $selectedBooths : $allBooths;
        
        // Override booth_type from selected_booth_ids for all display booths if available
        if ($booking && $booking->selected_booth_ids && is_array($booking->selected_booth_ids)) {
            // Build type map from selected_booth_ids if not already built
            if (empty($boothTypeMap) && isset($booking->selected_booth_ids[0]) && is_array($booking->selected_booth_ids[0])) {
                foreach ($booking->selected_booth_ids as $boothData) {
                    if (is_array($boothData) && isset($boothData['id']) && isset($boothData['type'])) {
                        $boothTypeMap[$boothData['id']] = $boothData['type'];
                    }
                }
            }
            
            // Apply type from selected_booth_ids to all display booths
            foreach ($displayBooths as $booth) {
                if (isset($boothTypeMap[$booth->id])) {
                    $booth->booth_type = $boothTypeMap[$booth->id];
                }
            }
        }
        
        // Map included item extras to their item definitions (for names)
        $extraItemsMap = collect();
        if ($booking && !empty($booking->included_item_extras)) {
            $itemIds = collect($booking->included_item_extras)
                ->pluck('item_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($itemIds)) {
                $extraItemsMap = ExhibitionBoothSizeItem::whereIn('id', $itemIds)
                    ->get()
                    ->keyBy('id');
            }
        }

        return view('admin.booth-requests.show', compact('boothRequest', 'booking', 'extraItemsMap', 'displayBooths'));
    }

    public function approve($id)
    {
        $boothRequest = BoothRequest::findOrFail($id);
        
        DB::beginTransaction();
        try {
            if ($boothRequest->request_type === 'merge') {
                $this->processMerge($boothRequest);
            } elseif ($boothRequest->request_type === 'split') {
                $this->processSplit($boothRequest);
            } elseif ($boothRequest->request_type === 'booking') {
                $this->processBooking($boothRequest);
            }

            $boothRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Request approved successfully']);
            }
            return back()->with('success', 'Request approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error processing request: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error processing request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $boothRequest = BoothRequest::findOrFail($id);
        
        $boothRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // If this was a booking request, mark related pending bookings as rejected
        if ($boothRequest->request_type === 'booking') {
            Booking::where('user_id', $boothRequest->user_id)
                ->where('exhibition_id', $boothRequest->exhibition_id)
                ->whereIn('booth_id', $boothRequest->booth_ids ?? [])
                ->where('approval_status', 'pending')
                ->update([
                    'approval_status' => 'rejected',
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Request rejected']);
        }
        return back()->with('success', 'Request rejected');
    }

    private function processMerge($boothRequest)
    {
        $booths = Booth::whereIn('id', $boothRequest->booth_ids)
            ->where('exhibition_id', $boothRequest->exhibition_id)
            ->where('is_booked', false)
            ->get();

        if ($booths->count() < 2) {
            throw new \Exception('At least 2 available booths required');
        }

        $totalSize = $booths->sum('size_sqft');
        $totalPrice = $booths->sum('price');
        $avgSidesOpen = round($booths->avg('sides_open'));

        // Calculate position for merged booth
        $minX = $booths->min('position_x') ?? 0;
        $minY = $booths->min('position_y') ?? 0;
        $maxX = $booths->max(function($b) { return ($b->position_x ?? 0) + ($b->width ?? 100); });
        $maxY = $booths->max(function($b) { return ($b->position_y ?? 0) + ($b->height ?? 80); });
        $mergedWidth = max(100, $maxX - $minX);
        $mergedHeight = max(80, $maxY - $minY);

        $mergedBooth = Booth::create([
            'exhibition_id' => $boothRequest->exhibition_id,
            'name' => $boothRequest->request_data['new_name'],
            'category' => $booths->first()->category,
            'booth_type' => $booths->first()->booth_type,
            'size_sqft' => $totalSize,
            'sides_open' => $avgSidesOpen,
            'price' => $totalPrice,
            'is_merged' => true,
            'merged_booths' => $booths->pluck('id')->toArray(),
            'position_x' => $minX,
            'position_y' => $minY,
            'width' => $mergedWidth,
            'height' => $mergedHeight,
            'is_available' => true,
            'is_booked' => false,
        ]);

        foreach ($booths as $booth) {
            $booth->update([
                'is_available' => false,
                'parent_booth_id' => $mergedBooth->id,
            ]);
        }
    }

    private function processSplit($boothRequest)
    {
        $booth = Booth::where('id', $boothRequest->booth_ids[0])
            ->where('exhibition_id', $boothRequest->exhibition_id)
            ->where('is_booked', false)
            ->firstOrFail();

        $splitCount = $boothRequest->request_data['split_count'];
        $sizePerBooth = $booth->size_sqft / $splitCount;
        $pricePerBooth = $booth->price / $splitCount;

        $baseX = $booth->position_x ?? 0;
        $baseY = $booth->position_y ?? 0;
        $originalWidth = $booth->width ?? 100;
        $originalHeight = $booth->height ?? 80;
        
        // Calculate grid layout for split booths
        $cols = $splitCount <= 2 ? $splitCount : 2;
        $rows = ceil($splitCount / $cols);
        $width = $originalWidth / $cols;
        $height = $originalHeight / $rows;

        for ($i = 0; $i < $splitCount; $i++) {
            $col = $i % $cols;
            $row = floor($i / $cols);
            $x = $baseX + ($col * $width);
            $y = $baseY + ($row * $height);

            Booth::create([
                'exhibition_id' => $boothRequest->exhibition_id,
                'name' => $boothRequest->request_data['new_names'][$i],
                'category' => $booth->category,
                'booth_type' => $booth->booth_type,
                'size_sqft' => $sizePerBooth,
                'sides_open' => $booth->sides_open,
                'price' => $pricePerBooth,
                'is_split' => true,
                'parent_booth_id' => $booth->id,
                'position_x' => $x,
                'position_y' => $y,
                'width' => $width,
                'height' => $height,
                'is_available' => true,
                'is_booked' => false,
            ]);
        }

        $booth->update(['is_available' => false]);
    }

    private function processBooking($boothRequest)
    {
        // Prefer an explicit booking_id from the request payload (created at payment time)
        $bookingIdFromRequest = $boothRequest->request_data['booking_id'] ?? null;

        if ($bookingIdFromRequest) {
            $booking = Booking::where('id', $bookingIdFromRequest)
                ->where('user_id', $boothRequest->user_id)
                ->where('exhibition_id', $boothRequest->exhibition_id)
                ->where('approval_status', 'pending')
                ->first();
        } else {
            // Fallback: locate by booth_id list (legacy behaviour)
            $booking = Booking::where('user_id', $boothRequest->user_id)
                ->where('exhibition_id', $boothRequest->exhibition_id)
                ->whereIn('booth_id', $boothRequest->booth_ids ?? [])
                ->where('approval_status', 'pending')
                ->latest()
                ->first();
        }
        
        if (!$booking) {
            throw new \Exception('No pending booking found for this request');
        }
        
        $booking->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'status' => 'confirmed',
        ]);
        
        // Mark the primary booth on the booking as booked (this may be a merged booth)
        if ($booking->booth) {
            $booking->booth->update([
                'is_booked' => true,
                'is_available' => false,
            ]);
        }
        
        // Mark ALL booths in selected_booth_ids as booked
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
            
            // Mark all selected booths as booked
            if (!empty($selectedBoothIds)) {
                Booth::whereIn('id', $selectedBoothIds)
                    ->where('exhibition_id', $booking->exhibition_id)
                    ->update([
                        'is_booked' => true,
                        'is_available' => false,
                    ]);
            }
        }
    }
}
