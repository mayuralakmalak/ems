<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Exhibition;
use App\Models\Booth;
use App\Models\Floor;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FloorplanController extends Controller
{
    public function show($id)
    {
        $exhibition = Exhibition::with('booths')->findOrFail($id);
        return view('admin.floorplan.show', compact('exhibition'));
    }

    public function updateBoothPosition(Request $request, $exhibitionId, $boothId)
    {
        $booth = Booth::where('exhibition_id', $exhibitionId)->findOrFail($boothId);
        
        $request->validate([
            'position_x' => 'required|numeric',
            'position_y' => 'required|numeric',
            'width' => 'nullable|numeric|min:50',
            'height' => 'nullable|numeric|min:50',
        ]);

        $booth->update([
            'position_x' => $request->position_x,
            'position_y' => $request->position_y,
            'width' => $request->width ?? $booth->width ?? 100,
            'height' => $request->height ?? $booth->height ?? 100,
        ]);

        return response()->json(['success' => true, 'message' => 'Booth position updated']);
    }

    public function mergeBooths(Request $request, $exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        
        $request->validate([
            'booth_ids' => 'required|array|min:2',
            'booth_ids.*' => 'exists:booths,id',
            'new_name' => 'required|string|max:255',
        ]);

        $booths = Booth::whereIn('id', $request->booth_ids)
            ->where('exhibition_id', $exhibitionId)
            ->where('is_booked', false)
            ->get();

        if ($booths->count() < 2) {
            return response()->json(['success' => false, 'message' => 'At least 2 available booths required'], 400);
        }

        // Calculate merged booth properties
        $totalSize = $booths->sum('size_sqft');
        $totalPrice = $booths->sum('price');
        $avgSidesOpen = round($booths->avg('sides_open'));
        $category = $booths->first()->category; // Use first booth's category

        // Calculate position for merged booth
        $minX = $booths->min('position_x') ?? 0;
        $minY = $booths->min('position_y') ?? 0;
        $maxX = $booths->max(function($b) { return ($b->position_x ?? 0) + ($b->width ?? 100); });
        $maxY = $booths->max(function($b) { return ($b->position_y ?? 0) + ($b->height ?? 80); });
        $mergedWidth = max(100, $maxX - $minX);
        $mergedHeight = max(80, $maxY - $minY);

        // Create merged booth
        $mergedBooth = Booth::create([
            'exhibition_id' => $exhibitionId,
            'name' => $request->new_name,
            'category' => $category,
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

        // Mark original booths as merged
        foreach ($booths as $booth) {
            $booth->update([
                'is_available' => false,
                'parent_booth_id' => $mergedBooth->id,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Booths merged successfully', 'booth' => $mergedBooth]);
    }

    public function splitBooth(Request $request, $exhibitionId, $boothId)
    {
        $booth = Booth::where('exhibition_id', $exhibitionId)
            ->where('is_booked', false)
            ->findOrFail($boothId);

        $request->validate([
            'split_count' => 'required|integer|min:2|max:4',
            'new_names' => 'required|array|size:' . $request->split_count,
            'new_names.*' => 'required|string|max:255',
        ]);

        if ($booth->is_booked) {
            return response()->json(['success' => false, 'message' => 'Cannot split a booked booth'], 400);
        }

        // Calculate split booth properties
        $sizePerBooth = $booth->size_sqft / $request->split_count;
        $pricePerBooth = $booth->price / $request->split_count;

        $splitBooths = [];
        $baseX = $booth->position_x ?? 0;
        $baseY = $booth->position_y ?? 0;
        $originalWidth = $booth->width ?? 100;
        $originalHeight = $booth->height ?? 80;
        
        // Calculate grid layout for split booths
        $cols = $request->split_count <= 2 ? $request->split_count : 2;
        $rows = ceil($request->split_count / $cols);
        $width = $originalWidth / $cols;
        $height = $originalHeight / $rows;

        // Create split booths
        for ($i = 0; $i < $request->split_count; $i++) {
            $col = $i % $cols;
            $row = floor($i / $cols);
            $x = $baseX + ($col * $width);
            $y = $baseY + ($row * $height);

            $splitBooth = Booth::create([
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
                'width' => $width,
                'height' => $height,
                'is_available' => true,
                'is_booked' => false,
            ]);

            $splitBooths[] = $splitBooth;
        }

        // Mark original booth as split
        $booth->update([
            'is_available' => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Booth split successfully', 'booths' => $splitBooths]);
    }

    /**
     * Save floorplan configuration (hall, grid, booths) as JSON per exhibition and floor.
     */
    public function saveConfig(Request $request, $exhibitionId)
    {
        $request->validate([
            'hall' => 'required|array',
            'grid' => 'required|array',
            'booths' => 'required|array',
            'floor_id' => 'nullable|exists:floors,id',
        ]);

        $exhibition = Exhibition::findOrFail($exhibitionId);
        $floorId = $request->input('floor_id');

        $hall = $request->input('hall');
        $booths = $request->input('booths', []);

        // Validate total stall area does not exceed usable area (70% of hall)
        $hallWidth = (float) ($hall['width'] ?? 0);
        $hallHeight = (float) ($hall['height'] ?? 0);
        $totalHallArea = $hallWidth * $hallHeight;
        $usableArea = $totalHallArea * 0.70;
        if ($usableArea > 0) {
            $sumBoothArea = 0;
            foreach ($booths as $booth) {
                $w = (float) ($booth['width'] ?? 0);
                $h = (float) ($booth['height'] ?? 0);
                $sumBoothArea += $w * $h;
            }
            if ($sumBoothArea > $usableArea) {
                return response()->json([
                    'success' => false,
                    'error' => 'Total stall area cannot exceed usable area (70% of hall).',
                ], 422);
            }
        }

        $payload = $request->all();
        $payload['lastUpdated'] = now()->toDateTimeString();

        // Determine the path based on whether floor_id is provided
        if ($floorId) {
            $floor = Floor::where('id', $floorId)
                ->where('exhibition_id', $exhibitionId)
                ->firstOrFail();
            $path = $floor->getFloorplanConfigPath();
        } else {
            // Backward compatibility: use old path for exhibitions without floors
            $path = "floorplans/exhibition_{$exhibition->id}.json";
        }

        Storage::disk('local')->put($path, json_encode($payload, JSON_PRETTY_PRINT));

        $this->syncBoothsFromPayload($payload, $exhibitionId, $floorId);

        $response = ['success' => true];
        $totalRevenuePct = 0;
        foreach ($booths as $booth) {
            $totalRevenuePct += (float) ($booth['revenuePercentage'] ?? $booth['revenue_percentage'] ?? 0);
        }
        if ($totalRevenuePct > 100) {
            $response['warning'] = 'Total revenue coverage exceeds 100%.';
        }
        return response()->json($response);
    }

    /**
     * Load floorplan configuration JSON per exhibition and floor.
     */
    public function loadConfig($exhibitionId, $floorId = null)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        
        // If floor_id is provided, load floor-specific config
        if ($floorId) {
            $floor = Floor::where('id', $floorId)
                ->where('exhibition_id', $exhibitionId)
                ->firstOrFail();
            $path = $floor->getFloorplanConfigPath();
            
            if (Storage::disk('local')->exists($path)) {
                $json = Storage::disk('local')->get($path);
                $payload = json_decode($json, true) ?: [];
                $this->syncBoothsFromPayload($payload, $exhibitionId, $floorId);
                $payload = $this->applySizeIdsFromDatabase($payload, $exhibitionId, $floorId);
                $json = json_encode($payload, JSON_PRETTY_PRINT);
                Storage::disk('local')->put($path, $json);
                return response($json, 200)->header('Content-Type', 'application/json');
            }
        }
        
        // Backward compatibility: try old path for exhibitions without floors
        $primaryPath = "floorplans/exhibition_{$exhibition->id}.json";
        $fallbackPath = "private/floorplans/exhibition_{$exhibition->id}.json";

        if (Storage::disk('local')->exists($primaryPath)) {
            $json = Storage::disk('local')->get($primaryPath);
            $payload = json_decode($json, true) ?: [];
            $this->syncBoothsFromPayload($payload, $exhibitionId, null);
            $payload = $this->applySizeIdsFromDatabase($payload, $exhibitionId, null);
            $json = json_encode($payload, JSON_PRETTY_PRINT);
            Storage::disk('local')->put($primaryPath, $json);
            return response($json, 200)->header('Content-Type', 'application/json');
        }

        if (Storage::disk('local')->exists($fallbackPath)) {
            $json = Storage::disk('local')->get($fallbackPath);
            Storage::disk('local')->put($primaryPath, $json);
            $payload = json_decode($json, true) ?: [];
            $this->syncBoothsFromPayload($payload, $exhibitionId, null);
            $payload = $this->applySizeIdsFromDatabase($payload, $exhibitionId, null);
            $json = json_encode($payload, JSON_PRETTY_PRINT);
            Storage::disk('local')->put($primaryPath, $json);
            return response($json, 200)->header('Content-Type', 'application/json');
        }

        // Default empty structure
        return response()->json([
            'hall' => [
                'width' => 1200,
                'height' => 800,
                'margin' => 0,
            ],
            'grid' => [
                'size' => 50,
                'show' => true,
                'snap' => true,
            ],
            'booths' => [],
            'lastUpdated' => null,
        ]);
    }

    /**
     * Sync booth payload into DB for frontend consumption.
     */
    private function syncBoothsFromPayload(array $payload, int $exhibitionId, ?int $floorId = null): void
    {
        $boothsData = collect($payload['booths'] ?? []);
        
        // Get query for existing booths
        $query = Booth::where('exhibition_id', $exhibitionId);
        
        // If floor_id is provided, only sync booths for that floor
        if ($floorId) {
            $query->where('floor_id', $floorId);
        } else {
            // For backward compatibility: only sync booths without floor_id
            $query->whereNull('floor_id');
        }

        // If payload has booths, sync them
        if ($boothsData->isNotEmpty()) {
            $existingBooths = $query->whereIn('name', $boothsData->pluck('id')->filter())
                ->get()
                ->keyBy('name');

            // Valid size IDs for this exhibition to avoid FK errors and to provide sensible defaults
            $validSizeIds = DB::table('exhibition_booth_sizes')
                ->where('exhibition_id', $exhibitionId)
                ->pluck('id')
                ->values()
                ->toArray();

            foreach ($boothsData as $boothData) {
                $boothName = $boothData['id'] ?? null;
                if (!$boothName) {
                    continue;
                }

                $booth = $existingBooths[$boothName] ?? new Booth([
                    'exhibition_id' => $exhibitionId,
                    'floor_id' => $floorId,
                    'name' => $boothName,
                ]);

                $booth->category = $boothData['category'] ?? $booth->category ?? 'Standard';
                $booth->booth_type = $booth->booth_type ?? 'Raw';
                $booth->size_sqft = $boothData['area'] ?? $booth->size_sqft ?? 0;
                $booth->sides_open = max(1, (int)($boothData['openSides'] ?? $booth->sides_open ?? 1));
                $booth->price = $boothData['price'] ?? $booth->price ?? 0;
                $booth->is_free = $booth->is_free ?? false;
                // Preserve existing status from database (don't overwrite with defaults)
                // Only set defaults if this is a new booth
                if ($booth->exists === false) {
                    $booth->is_available = true;
                    $booth->is_booked = false;
                }
                $booth->merged_booths = $boothData['merged_booths'] ?? $booth->merged_booths ?? null;
                $booth->position_x = $boothData['x'] ?? $booth->position_x;
                $booth->position_y = $boothData['y'] ?? $booth->position_y;
                $booth->width = $boothData['width'] ?? $booth->width;
                $booth->height = $boothData['height'] ?? $booth->height;

                // Optional per-booth discount configuration synced from floorplan editor
                $booth->discount_id = $boothData['discount_id'] ?? $boothData['discountId'] ?? $booth->discount_id;
                $booth->discount_user_id = $boothData['discount_user_id'] ?? $boothData['discountUserId'] ?? $booth->discount_user_id;
                
                // Set floor_id if provided
                if ($floorId) {
                    $booth->floor_id = $floorId;
                }

                // Determine size ID:
                // 1) Use explicit sizeId from payload if valid
                // 2) Otherwise, default to the first available exhibition booth size (if any)
                $sizeId = $boothData['sizeId'] ?? null;
                if (($sizeId === null || !in_array($sizeId, $validSizeIds)) && !empty($validSizeIds)) {
                    $sizeId = $validSizeIds[0];
                }
                $booth->exhibition_booth_size_id = ($sizeId && in_array($sizeId, $validSizeIds)) ? $sizeId : null;

                $booth->save();
            }
        }

        // After syncing booths from payload, identify and delete booths that are not in the payload
        $boothsInPayload = $boothsData->pluck('id')->filter()->values()->toArray();
        
        // Get all existing booths for this exhibition/floor (create fresh query to avoid cloning issues)
        // When floor_id is provided, also check booths with null floor_id for backward compatibility
        $allExistingBoothsQuery = Booth::where('exhibition_id', $exhibitionId);
        if ($floorId) {
            // Include both booths for this floor AND booths with null floor_id (legacy booths)
            // This ensures we can delete legacy booths that are no longer in the payload
            $allExistingBoothsQuery->where(function($q) use ($floorId) {
                $q->where('floor_id', $floorId)
                  ->orWhereNull('floor_id');
            });
        } else {
            $allExistingBoothsQuery->whereNull('floor_id');
        }
        $allExistingBooths = $allExistingBoothsQuery->get();
        
        // Find booths that are not in the payload
        $boothsToDelete = $allExistingBooths->filter(function($booth) use ($boothsInPayload) {
            return !in_array($booth->name, $boothsInPayload, true);
        });
        
        // Log for debugging
        Log::debug("Floorplan sync: Checking booths for deletion", [
            'exhibition_id' => $exhibitionId,
            'floor_id' => $floorId,
            'booths_in_payload' => $boothsInPayload,
            'total_existing_booths' => $allExistingBooths->count(),
            'booths_to_delete_count' => $boothsToDelete->count(),
            'booths_to_delete_names' => $boothsToDelete->pluck('name')->toArray(),
        ]);
        
        // Attempt to delete booths that are not in payload
        $deletedCount = 0;
        $skippedCount = 0;
        $skippedReasons = [];
        
        foreach ($boothsToDelete as $booth) {
            $canDelete = $this->canDeleteBooth($booth);
            
            if ($canDelete['can_delete']) {
                // Safe to delete - remove from database
                $booth->delete();
                $deletedCount++;
                Log::info("Deleted booth {$booth->id} ({$booth->name})", [
                    'exhibition_id' => $exhibitionId,
                    'floor_id' => $floorId,
                    'booth_id' => $booth->id,
                ]);
            } else {
                // Cannot delete - log reason
                $skippedCount++;
                $skippedReasons[] = "Booth '{$booth->name}': {$canDelete['reason']}";
                
                // Log to Laravel log for debugging
                Log::warning("Cannot delete booth {$booth->id} ({$booth->name}): {$canDelete['reason']}", [
                    'exhibition_id' => $exhibitionId,
                    'floor_id' => $floorId,
                    'booth_id' => $booth->id,
                ]);
            }
        }
        
        // Log summary if there were deletions or skips
        if ($deletedCount > 0 || $skippedCount > 0) {
            Log::info("Floorplan sync: Deleted {$deletedCount} booths, Skipped {$skippedCount} booths", [
                'exhibition_id' => $exhibitionId,
                'floor_id' => $floorId,
                'deleted_count' => $deletedCount,
                'skipped_count' => $skippedCount,
                'skipped_reasons' => $skippedReasons
            ]);
        }
    }

    /**
     * Check if a booth can be safely deleted.
     * Returns array with 'can_delete' boolean and 'reason' string if cannot delete.
     */
    private function canDeleteBooth(Booth $booth): array
    {
        // 1. Check if booth is marked as booked
        if ($booth->is_booked) {
            return ['can_delete' => false, 'reason' => 'Booth is marked as booked'];
        }

        // 2. Check if booth has any bookings (via booth_id)
        $hasBookings = \App\Models\Booking::where('booth_id', $booth->id)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->exists();
        
        if ($hasBookings) {
            return ['can_delete' => false, 'reason' => 'Booth has active bookings'];
        }

        // 3. Check if booth is referenced in any booking's selected_booth_ids
        $referencedInBookings = \App\Models\Booking::where('exhibition_id', $booth->exhibition_id)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get()
            ->filter(function($booking) use ($booth) {
                $selectedIds = $booking->selected_booth_ids ?? [];
                if (empty($selectedIds) || !is_array($selectedIds)) {
                    return false;
                }
                
                // Handle array of objects format: [{'id': 1, 'name': 'B001'}, ...]
                $firstItem = reset($selectedIds);
                if (is_array($firstItem) && isset($firstItem['id'])) {
                    return collect($selectedIds)->pluck('id')->contains($booth->id);
                }
                
                // Handle simple array format: [1, 2, 3]
                return in_array($booth->id, $selectedIds, true);
            })
            ->isNotEmpty();
        
        if ($referencedInBookings) {
            return ['can_delete' => false, 'reason' => 'Booth is referenced in active bookings'];
        }

        // 4. Check if booth has child booths (is a parent)
        if ($booth->childBooths()->exists()) {
            return ['can_delete' => false, 'reason' => 'Booth has child booths (split/merge relationship)'];
        }

        // 5. Check if booth is referenced in merged_booths array of other booths
        $referencedInMerged = Booth::where('exhibition_id', $booth->exhibition_id)
            ->whereNotNull('merged_booths')
            ->get()
            ->filter(function($otherBooth) use ($booth) {
                $mergedIds = $otherBooth->merged_booths ?? [];
                return is_array($mergedIds) && in_array($booth->id, $mergedIds, true);
            })
            ->isNotEmpty();
        
        if ($referencedInMerged) {
            return ['can_delete' => false, 'reason' => 'Booth is referenced in merged booth records'];
        }

        // 6. Check if booth has pending bookings (reserved)
        $hasPendingBookings = \App\Models\Booking::where('exhibition_id', $booth->exhibition_id)
            ->where('approval_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get()
            ->filter(function($booking) use ($booth) {
                // Check primary booth_id
                if ($booking->booth_id == $booth->id) {
                    return true;
                }
                
                // Check selected_booth_ids
                $selectedIds = $booking->selected_booth_ids ?? [];
                if (empty($selectedIds) || !is_array($selectedIds)) {
                    return false;
                }
                
                $firstItem = reset($selectedIds);
                if (is_array($firstItem) && isset($firstItem['id'])) {
                    return collect($selectedIds)->pluck('id')->contains($booth->id);
                }
                
                return in_array($booth->id, $selectedIds, true);
            })
            ->isNotEmpty();
        
        if ($hasPendingBookings) {
            return ['can_delete' => false, 'reason' => 'Booth has pending bookings (reserved)'];
        }

        return ['can_delete' => true, 'reason' => null];
    }

    /**
     * Extract booth IDs from a booking's selected_booth_ids array.
     * Handles multiple formats: array of objects, simple array, mixed types.
     * 
     * @param \App\Models\Booking $booking
     * @return array Array of booth IDs (integers)
     */
    private function extractBoothIdsFromBooking(\App\Models\Booking $booking): array
    {
        $boothIds = [];
        
        // Add primary booth_id if exists
        if ($booking->booth_id) {
            $boothIds[] = (int) $booking->booth_id;
        }
        
        // Extract from selected_booth_ids
        $selectedBoothIds = $booking->selected_booth_ids;
        
        if (empty($selectedBoothIds) || !is_array($selectedBoothIds)) {
            return $boothIds;
        }
        
        // Handle different array formats
        foreach ($selectedBoothIds as $item) {
            if (is_array($item)) {
                // Array of objects format: [{'id': 1, 'name': 'B001'}, ...]
                if (isset($item['id'])) {
                    $boothIds[] = (int) $item['id'];
                }
            } elseif (is_numeric($item)) {
                // Simple array format: [1, 2, 3] or ['1', '2', '3']
                $boothIds[] = (int) $item;
            } elseif (is_object($item)) {
                // Object format: stdClass with id property
                if (isset($item->id)) {
                    $boothIds[] = (int) $item->id;
                }
            }
        }
        
        return array_values(array_unique(array_filter($boothIds)));
    }

    /**
     * Ensure each booth payload entry has the correct sizeId and status from DB so that
     * the admin UI can show the already-selected size and current booking status.
     */
    private function applySizeIdsFromDatabase(array $payload, int $exhibitionId, ?int $floorId = null): array
    {
        if (empty($payload['booths']) || !is_array($payload['booths'])) {
            return $payload;
        }

        $boothNames = collect($payload['booths'])
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        if (empty($boothNames)) {
            return $payload;
        }

        // Get all reserved and booked booth IDs for this exhibition
        // Reserved = pending booking (regardless of payment status)
        $reservedBookings = \App\Models\Booking::where('exhibition_id', $exhibitionId)
            ->where('approval_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get();
        
        $reservedBoothIds = [];
        foreach ($reservedBookings as $booking) {
            $boothIds = $this->extractBoothIdsFromBooking($booking);
            $reservedBoothIds = array_merge($reservedBoothIds, $boothIds);
        }
        $reservedBoothIds = array_values(array_unique(array_filter($reservedBoothIds)));
        
        // Get booked booths - bookings that are confirmed (status = 'confirmed')
        // A booking with status 'confirmed' is considered booked regardless of approval_status
        // This matches the "Booked Booths" view which shows all confirmed bookings
        $bookedBookings = \App\Models\Booking::where('exhibition_id', $exhibitionId)
            ->where('status', 'confirmed')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get();
        
        $bookedBoothIds = [];
        foreach ($bookedBookings as $booking) {
            $boothIds = $this->extractBoothIdsFromBooking($booking);
            $bookedBoothIds = array_merge($bookedBoothIds, $boothIds);
            
            // Debug logging (remove in production if not needed)
            if (config('app.debug')) {
                Log::debug('Booked booking found', [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'approval_status' => $booking->approval_status,
                    'status' => $booking->status,
                    'booth_id' => $booking->booth_id,
                    'selected_booth_ids' => $booking->selected_booth_ids,
                    'extracted_booth_ids' => $boothIds,
                ]);
            }
        }
        $bookedBoothIds = array_values(array_unique(array_filter($bookedBoothIds)));

        // Map booth_id => company logo URL for booked/reserved booths (for admin floorplan display)
        $boothLogos = [];
        foreach ($reservedBookings as $booking) {
            if ($booking->logo && Storage::disk('public')->exists($booking->logo)) {
                $url = asset('storage/' . ltrim($booking->logo, '/'));
                foreach ($this->extractBoothIdsFromBooking($booking) as $bid) {
                    $boothLogos[$bid] = $url;
                }
            }
        }
        foreach ($bookedBookings as $booking) {
            if ($booking->logo && Storage::disk('public')->exists($booking->logo)) {
                $url = asset('storage/' . ltrim($booking->logo, '/'));
                foreach ($this->extractBoothIdsFromBooking($booking) as $bid) {
                    $boothLogos[$bid] = $url;
                }
            }
        }
        
        // Debug logging (remove in production if not needed)
        if (config('app.debug')) {
            Log::debug('All booked booth IDs', [
                'exhibition_id' => $exhibitionId,
                'total_booked_bookings' => $bookedBookings->count(),
                'booked_booth_ids' => $bookedBoothIds,
            ]);
        }

        // Query booths for this floor only (to avoid conflicts with same booth names across floors)
        $dbBoothsQuery = Booth::where('exhibition_id', $exhibitionId)
            ->whereIn('name', $boothNames);
        
        // Filter by floor_id if provided
        if ($floorId !== null) {
            $dbBoothsQuery->where('floor_id', $floorId);
        } else {
            // For backward compatibility: only get booths without floor_id
            $dbBoothsQuery->whereNull('floor_id');
        }
        
        $dbBooths = $dbBoothsQuery->get()->keyBy('name');

        foreach ($payload['booths'] as &$boothData) {
            $name = $boothData['id'] ?? null;
            if (!$name) {
                // If no name, default to available
                $boothData['status'] = $boothData['status'] ?? 'available';
                continue;
            }

            // If booth exists in database, use its data
            if (isset($dbBooths[$name])) {
                $dbBooth = $dbBooths[$name];
                if (!empty($dbBooth->exhibition_booth_size_id)) {
                    $boothData['sizeId'] = $dbBooth->exhibition_booth_size_id;
                }

                // Ensure discount configuration from DB is reflected in payload so admin UI can show it
                if (!empty($dbBooth->discount_id)) {
                    $boothData['discount_id'] = $dbBooth->discount_id;
                }
                if (!empty($dbBooth->discount_user_id)) {
                    $boothData['discount_user_id'] = $dbBooth->discount_user_id;
                }
                
                // Update status based on actual booking status from database
                // Priority: booked > reserved > merged > available
                $boothId = (int) $dbBooth->id;
                
                // Check if booked (highest priority)
                // Use loose comparison to handle string/int mismatches, but ensure types are consistent
                $isInBookedList = in_array($boothId, $bookedBoothIds, true);
                $isMarkedBooked = $dbBooth->is_booked;
                
                if ($isInBookedList || $isMarkedBooked) {
                    $boothData['status'] = 'booked';
                    if (!empty($boothLogos[$boothId])) {
                        $boothData['logo'] = $boothLogos[$boothId];
                    }
                    
                    // Debug logging (remove in production if not needed)
                    if (config('app.debug') && !$isInBookedList && $isMarkedBooked) {
                        Log::debug('Booth marked as booked but not in booked list', [
                            'booth_id' => $boothId,
                            'booth_name' => $name,
                            'is_booked' => $dbBooth->is_booked,
                            'booked_booth_ids' => $bookedBoothIds,
                        ]);
                    }
                } 
                // Check if reserved (second priority)
                elseif (in_array($boothId, $reservedBoothIds, true) || (!$dbBooth->is_available && !$dbBooth->is_booked)) {
                    $boothData['status'] = 'reserved';
                    if (!empty($boothLogos[$boothId])) {
                        $boothData['logo'] = $boothLogos[$boothId];
                    }
                } 
                // Check if merged
                elseif ($dbBooth->is_merged) {
                    $boothData['status'] = 'merged';
                } 
                // Default to available
                else {
                    $boothData['status'] = 'available';
                }
            } else {
                // Booth doesn't exist in database yet - check if it's in booked/reserved arrays by name
                // This handles cases where booth might be referenced by name in bookings
                // IMPORTANT: Filter by floor_id to avoid conflicts with same booth names across floors
                $boothByNameQuery = Booth::where('exhibition_id', $exhibitionId)
                    ->where('name', $name);
                
                // Filter by floor_id if provided
                if ($floorId !== null) {
                    $boothByNameQuery->where('floor_id', $floorId);
                } else {
                    // For backward compatibility: only get booths without floor_id
                    $boothByNameQuery->whereNull('floor_id');
                }
                
                $boothByName = $boothByNameQuery->first();
                
                if ($boothByName) {
                    $boothId = (int) $boothByName->id;
                    if (in_array($boothId, $bookedBoothIds, true) || $boothByName->is_booked) {
                        $boothData['status'] = 'booked';
                        if (!empty($boothLogos[$boothId])) {
                            $boothData['logo'] = $boothLogos[$boothId];
                        }
                    } elseif (in_array($boothId, $reservedBoothIds, true) || (!$boothByName->is_available && !$boothByName->is_booked)) {
                        $boothData['status'] = 'reserved';
                        if (!empty($boothLogos[$boothId])) {
                            $boothData['logo'] = $boothLogos[$boothId];
                        }
                    } elseif ($boothByName->is_merged) {
                        $boothData['status'] = 'merged';
                    } else {
                        $boothData['status'] = 'available';
                    }
                } else {
                    // Booth not in database - default to available
                    $boothData['status'] = $boothData['status'] ?? 'available';
                }
            }
        }
        unset($boothData);

        return $payload;
    }
}
