<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\Category;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BoothController extends Controller
{
    public function index($exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        $booths = Booth::where('exhibition_id', $exhibitionId)
            ->with('bookings')
            ->latest()
            ->get();
        
        // Return JSON if requested (for floor plan editor)
        if (request()->wantsJson()) {
            return response()->json($booths);
        }
        
        return view('admin.booths.index', compact('exhibition', 'booths'));
    }

    public function create($exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        $categories = $this->activeCategories();

        return view('admin.booths.create', compact('exhibition', 'categories'));
    }

    public function store(Request $request, $exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        
        // Handle JSON requests from floor plan editor
        if ($request->wantsJson() || $request->isJson()) {
            $jsonAllowedCategories = $this->allowedCategories();

            $request->validate([
                'booth_id' => 'nullable|string|max:255',
                'name' => 'required|string|max:255',
                'x' => 'required|numeric|min:0',
                'y' => 'required|numeric|min:0',
                'width' => 'required|numeric|min:0',
                'height' => 'required|numeric|min:0',
                'status' => 'nullable|in:available,reserved,booked',
                'size' => 'nullable|in:small,medium,large',
                'area' => 'nullable|numeric|min:0',
                'price' => 'nullable|numeric|min:0',
                'open_sides' => 'nullable|integer|in:1,2,3,4',
                'category' => ['nullable', Rule::in($jsonAllowedCategories)],
                'included_items' => 'nullable|array',
            ]);

            $boothId = $request->input('booth_id') ?: $request->input('name');
            
            // Check if booth exists by name
            $booth = Booth::where('exhibition_id', $exhibitionId)
                ->where('name', $boothId)
                ->first();

            $boothData = [
                'exhibition_id' => $exhibitionId,
                'name' => $boothId,
                'category' => $request->input('category', 'Standard'),
                'booth_type' => 'Raw', // Default
                'size_sqft' => $request->input('area', 100),
                'sides_open' => $request->input('open_sides', 2),
                'price' => $request->input('price', 10000),
                'position_x' => $request->input('x', 0),
                'position_y' => $request->input('y', 0),
                'width' => $request->input('width', 100),
                'height' => $request->input('height', 80),
                'is_available' => $request->input('status') !== 'booked',
                'is_booked' => $request->input('status') === 'booked',
                'is_free' => false,
            ];

            if ($booth) {
                $booth->update($boothData);
            } else {
                $booth = Booth::create($boothData);
            }

            return response()->json(['success' => true, 'booth' => $booth]);
        }
        
        // Handle regular form requests
        $allowedCategories = $this->allowedCategories();

        $request->validate([
            'name' => 'required|string|max:255|unique:booths,name,NULL,id,exhibition_id,' . $exhibitionId,
            'category' => ['required', Rule::in($allowedCategories)],
            'booth_type' => 'required|in:Raw,Orphand',
            'size_sqft' => 'required|numeric|min:0',
            'sides_open' => 'required|integer|in:1,2,3,4',
            'is_free' => 'nullable|boolean',
        ]);

        $price = $this->calculateBoothPrice($exhibition, $request->all());

        Booth::create([
            'exhibition_id' => $exhibitionId,
            'name' => $request->name,
            'category' => $request->category,
            'booth_type' => $request->booth_type,
            'size_sqft' => $request->size_sqft,
            'sides_open' => $request->sides_open,
            'price' => $price,
            'is_free' => $request->has('is_free') ? 1 : 0,
            'is_available' => true,
            'is_booked' => false,
        ]);

        return redirect()->route('admin.booths.index', $exhibitionId)
            ->with('success', 'Booth created successfully.');
    }

    public function show($exhibitionId, $id)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        $booth = Booth::where('exhibition_id', $exhibitionId)->findOrFail($id);
        
        // If request wants JSON (for AJAX)
        if (request()->wantsJson()) {
            return response()->json($booth);
        }
        
        return view('admin.booths.show', compact('exhibition', 'booth'));
    }

    public function edit($exhibitionId, $id)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        $booth = Booth::where('exhibition_id', $exhibitionId)->findOrFail($id);
        $categories = $this->activeCategories();

        return view('admin.booths.edit', compact('exhibition', 'booth', 'categories'));
    }

    public function update(Request $request, $exhibitionId, $id)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        $booth = Booth::where('exhibition_id', $exhibitionId)->findOrFail($id);
        
        $allowedCategories = $this->allowedCategories([$booth->category]);

        $request->validate([
            'name' => 'required|string|max:255|unique:booths,name,' . $id . ',id,exhibition_id,' . $exhibitionId,
            'category' => ['required', Rule::in($allowedCategories)],
            'booth_type' => 'required|in:Raw,Orphand',
            'size_sqft' => 'required|numeric|min:0',
            'sides_open' => 'required|integer|in:1,2,3,4',
            'is_free' => 'nullable|boolean',
        ]);

        $price = $this->calculateBoothPrice($exhibition, $request->all());

        $booth->update([
            'name' => $request->name,
            'category' => $request->category,
            'booth_type' => $request->booth_type,
            'size_sqft' => $request->size_sqft,
            'sides_open' => $request->sides_open,
            'price' => $price,
            'is_free' => $request->has('is_free') ? 1 : 0,
        ]);

        return redirect()->route('admin.booths.index', $exhibitionId)
            ->with('success', 'Booth updated successfully.');
    }

    public function destroy($exhibitionId, $id)
    {
        $booth = Booth::where('exhibition_id', $exhibitionId)
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('name', $id);
            })
            ->firstOrFail();
        
        if ($booth->is_booked) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Cannot delete a booked booth.'], 400);
            }
            return back()->with('error', 'Cannot delete a booked booth.');
        }

        $booth->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.booths.index', $exhibitionId)
            ->with('success', 'Booth deleted successfully.');
    }

    private function calculateBoothPrice($exhibition, $boothData)
    {
        if (isset($boothData['is_free']) && $boothData['is_free']) {
            return 0;
        }

        $basePrice = $boothData['booth_type'] === 'Raw' 
            ? ($exhibition->raw_price_per_sqft ?? 0)
            : ($exhibition->orphand_price_per_sqft ?? 0);
        
        $size = $boothData['size_sqft'] ?? 0;
        $sidesOpen = $boothData['sides_open'] ?? 1;
        
        // Apply side open percentage
        $sidePercent = $exhibition->{'side_' . $sidesOpen . '_open_percent'} ?? 0;
        $sideMultiplier = 1 + ($sidePercent / 100);
        
        // Base price calculation
        $calculatedPrice = $basePrice * $size * $sideMultiplier;
        
        // Add category premium
        $category = $boothData['category'] ?? 'Standard';
        if ($category === 'Premium' && $exhibition->premium_price) {
            $calculatedPrice += $exhibition->premium_price;
        } elseif ($category === 'Economy' && $exhibition->economy_price) {
            $calculatedPrice -= ($exhibition->economy_price ?? 0);
        }
        
        return round(max(0, $calculatedPrice), 2);
    }

    private function activeCategories()
    {
        return Category::where('status', true)
            ->orderBy('title')
            ->get();
    }

    private function allowedCategories(array $additional = []): array
    {
        $active = Category::where('status', true)->orderBy('title')->pluck('title')->toArray();
        $defaults = ['Premium', 'Standard', 'Economy', 'VIP'];
        $base = $active ?: $defaults;

        return array_values(array_unique(array_merge($base, $additional)));
    }
}
