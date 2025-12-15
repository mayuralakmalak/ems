<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use App\Models\Booth;
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

        $path = "floorplans/exhibition_{$exhibition->id}.json";
        Storage::disk('local')->put($path, json_encode($payload, JSON_PRETTY_PRINT));

        return response()->json(['success' => true]);
    }

    /**
     * Load floorplan configuration JSON per exhibition.
     */
    public function loadConfig($exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        $path = "floorplans/exhibition_{$exhibition->id}.json";
        
        // Also check private subdirectory
        $privatePath = "private/floorplans/exhibition_{$exhibition->id}.json";

        if (Storage::disk('local')->exists($path)) {
            $json = Storage::disk('local')->get($path);
            return response($json, 200)->header('Content-Type', 'application/json');
        } elseif (Storage::disk('local')->exists($privatePath)) {
            $json = Storage::disk('local')->get($privatePath);
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
     * Sync booths from JSON configuration to database.
     */
    public function syncBoothsFromJson($exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        $path = "floorplans/exhibition_{$exhibition->id}.json";
        $privatePath = "private/floorplans/exhibition_{$exhibition->id}.json";
        
        $jsonPath = null;
        if (Storage::disk('local')->exists($path)) {
            $jsonPath = $path;
        } elseif (Storage::disk('local')->exists($privatePath)) {
            $jsonPath = $privatePath;
        }
        
        if (!$jsonPath) {
            return ['success' => false, 'message' => 'No JSON configuration found'];
        }
        
        $json = Storage::disk('local')->get($jsonPath);
        $config = json_decode($json, true);
        
        if (!isset($config['booths']) || !is_array($config['booths'])) {
            return ['success' => false, 'message' => 'Invalid JSON structure'];
        }
        
        $synced = 0;
        $updated = 0;
        
        foreach ($config['booths'] as $boothData) {
            $booth = Booth::where('exhibition_id', $exhibitionId)
                ->where('name', $boothData['id'])
                ->first();
            
            $boothAttributes = [
                'exhibition_id' => $exhibitionId,
                'name' => $boothData['id'],
                'category' => $boothData['category'] ?? 'Standard',
                'booth_type' => 'Raw', // Default
                'size_sqft' => $boothData['area'] ?? 100,
                'sides_open' => $boothData['openSides'] ?? 2,
                'price' => $boothData['price'] ?? 0,
                'position_x' => $boothData['x'] ?? 0,
                'position_y' => $boothData['y'] ?? 0,
                'width' => $boothData['width'] ?? 100,
                'height' => $boothData['height'] ?? 80,
                'is_available' => ($boothData['status'] ?? 'available') !== 'booked',
                'is_booked' => ($boothData['status'] ?? 'available') === 'booked',
                'is_free' => false,
            ];
            
            if ($booth) {
                $booth->update($boothAttributes);
                $updated++;
            } else {
                Booth::create($boothAttributes);
                $synced++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Synced {$synced} booths, updated {$updated} booths",
            'synced' => $synced,
            'updated' => $updated
        ];
    }
}
