<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use App\Models\Booth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     * Save floorplan configuration (hall, grid, booths) as JSON per exhibition.
     */
    public function saveConfig(Request $request, $exhibitionId)
    {
        $request->validate([
            'hall' => 'required|array',
            'grid' => 'required|array',
            'booths' => 'required|array',
        ]);

        $exhibition = Exhibition::findOrFail($exhibitionId);

        $payload = $request->all();
        $payload['lastUpdated'] = now()->toDateTimeString();

        // Persist JSON where frontend and admin both expect it (original path)
        $path = "floorplans/exhibition_{$exhibition->id}.json";
        Storage::disk('local')->put($path, json_encode($payload, JSON_PRETTY_PRINT));

        $this->syncBoothsFromPayload($payload, $exhibitionId);

        return response()->json(['success' => true]);
    }

    /**
     * Load floorplan configuration JSON per exhibition.
     */
    public function loadConfig($exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        // Primary (original) path
        $primaryPath = "floorplans/exhibition_{$exhibition->id}.json";
        // Legacy/fallback path (in case existing data was saved to private/)
        $fallbackPath = "private/floorplans/exhibition_{$exhibition->id}.json";

        if (Storage::disk('local')->exists($primaryPath)) {
            $json = Storage::disk('local')->get($primaryPath);
            $payload = json_decode($json, true) ?: [];
            $this->syncBoothsFromPayload($payload, $exhibitionId);
            // Ensure booth size IDs from DB are reflected in the payload so UI can preselect correctly
            $payload = $this->applySizeIdsFromDatabase($payload, $exhibitionId);
            $json = json_encode($payload, JSON_PRETTY_PRINT);
            // Persist updated payload (with sizeId) for future loads
            Storage::disk('local')->put($primaryPath, $json);
            return response($json, 200)->header('Content-Type', 'application/json');
        }

        if (Storage::disk('local')->exists($fallbackPath)) {
            // Optionally promote fallback file to primary location for future loads
            $json = Storage::disk('local')->get($fallbackPath);
            Storage::disk('local')->put($primaryPath, $json);
            $payload = json_decode($json, true) ?: [];
            $this->syncBoothsFromPayload($payload, $exhibitionId);
            // Ensure booth size IDs from DB are reflected in the payload so UI can preselect correctly
            $payload = $this->applySizeIdsFromDatabase($payload, $exhibitionId);
            $json = json_encode($payload, JSON_PRETTY_PRINT);
            // Persist updated payload (with sizeId) for future loads
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
    private function syncBoothsFromPayload(array $payload, int $exhibitionId): void
    {
        $boothsData = collect($payload['booths'] ?? []);
        if ($boothsData->isEmpty()) {
            return;
        }

        $existingBooths = Booth::where('exhibition_id', $exhibitionId)
            ->whereIn('name', $boothsData->pluck('id')->filter())
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
                'name' => $boothName,
            ]);

            $booth->category = $boothData['category'] ?? $booth->category ?? 'Standard';
            $booth->booth_type = $booth->booth_type ?? 'Raw';
            $booth->size_sqft = $boothData['area'] ?? $booth->size_sqft ?? 0;
            $booth->sides_open = $boothData['openSides'] ?? $booth->sides_open ?? 1;
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

    /**
     * Ensure each booth payload entry has the correct sizeId and status from DB so that
     * the admin UI can show the already-selected size and current booking status.
     */
    private function applySizeIdsFromDatabase(array $payload, int $exhibitionId): array
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
        
        $bookedBookings = \App\Models\Booking::where('exhibition_id', $exhibitionId)
            ->where('approval_status', 'approved')
            ->where('status', 'confirmed')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get();
        
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

        $dbBooths = Booth::where('exhibition_id', $exhibitionId)
            ->whereIn('name', $boothNames)
            ->get()
            ->keyBy('name');

        foreach ($payload['booths'] as &$boothData) {
            $name = $boothData['id'] ?? null;
            if (!$name || !isset($dbBooths[$name])) {
                continue;
            }

            $dbBooth = $dbBooths[$name];
            if (!empty($dbBooth->exhibition_booth_size_id)) {
                $boothData['sizeId'] = $dbBooth->exhibition_booth_size_id;
            }
            
            // Update status based on actual booking status from database
            $boothId = $dbBooth->id;
            if (in_array($boothId, $bookedBoothIds) || $dbBooth->is_booked) {
                $boothData['status'] = 'booked';
            } elseif (in_array($boothId, $reservedBoothIds) || (!$dbBooth->is_available && !$dbBooth->is_booked)) {
                $boothData['status'] = 'reserved';
            } elseif ($dbBooth->is_merged) {
                $boothData['status'] = 'merged';
            } else {
                $boothData['status'] = 'available';
            }
        }
        unset($boothData);

        return $payload;
    }
}
