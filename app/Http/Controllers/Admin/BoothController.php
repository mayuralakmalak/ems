<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\Exhibition;
use Illuminate\Http\Request;

class BoothController extends Controller
{
    public function index($exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        $booths = Booth::where('exhibition_id', $exhibitionId)
            ->with('bookings')
            ->latest()
            ->get();
        
        return view('admin.booths.index', compact('exhibition', 'booths'));
    }

    public function create($exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        return view('admin.booths.create', compact('exhibition'));
    }

    public function store(Request $request, $exhibitionId)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:booths,name,NULL,id,exhibition_id,' . $exhibitionId,
            'category' => 'required|in:Premium,Standard,Economy',
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
        return view('admin.booths.edit', compact('exhibition', 'booth'));
    }

    public function update(Request $request, $exhibitionId, $id)
    {
        $exhibition = Exhibition::findOrFail($exhibitionId);
        $booth = Booth::where('exhibition_id', $exhibitionId)->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:booths,name,' . $id . ',id,exhibition_id,' . $exhibitionId,
            'category' => 'required|in:Premium,Standard,Economy',
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
        $booth = Booth::where('exhibition_id', $exhibitionId)->findOrFail($id);
        
        if ($booth->is_booked) {
            return back()->with('error', 'Cannot delete a booked booth.');
        }

        $booth->delete();

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
}
