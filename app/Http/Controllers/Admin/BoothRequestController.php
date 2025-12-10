<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoothRequest;
use App\Models\Booth;
use App\Models\Booking;
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
        
        return view('admin.booth-requests.index', compact('requests'));
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
        // Find the most recent pending booking for this request
        $booking = Booking::where('user_id', $boothRequest->user_id)
            ->where('exhibition_id', $boothRequest->exhibition_id)
            ->whereIn('booth_id', $boothRequest->booth_ids)
            ->where('approval_status', 'pending')
            ->latest()
            ->first();
        
        if (!$booking) {
            throw new \Exception('No pending booking found for this request');
        }
        
        $booking->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'status' => 'confirmed',
        ]);
        
        // Mark all booths in the booking as booked
        foreach ($boothRequest->booth_ids ?? [] as $boothId) {
            $booth = Booth::find($boothId);
            if ($booth && !$booth->is_booked) {
                $booth->update([
                    'is_booked' => true,
                    'is_available' => false,
                ]);
            }
        }
    }
}
