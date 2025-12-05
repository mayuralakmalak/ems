<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use App\Models\Booth;
use App\Models\StallScheme;
use App\Models\PaymentSchedule;
use App\Models\BadgeConfiguration;
use App\Models\StallVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExhibitionController extends Controller
{
    public function index()
    {
        $exhibitions = Exhibition::with('booths')->latest()->paginate(15);
        return view('admin.exhibitions.index', compact('exhibitions'));
    }

    public function create()
    {
        return view('admin.exhibitions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
        ]);

        $exhibition = Exhibition::create($validated);
        return redirect()->route('admin.exhibitions.step2', $exhibition->id);
    }

    public function step2($id)
    {
        $exhibition = Exhibition::with(['stallSchemes', 'booths'])->findOrFail($id);
        return view('admin.exhibitions.step2', compact('exhibition'));
    }

    public function storeStep2(Request $request, $id)
    {
        $exhibition = Exhibition::findOrFail($id);
        
        $validated = $request->validate([
            'floorplan_image' => 'nullable|image|max:10240',
            'price_per_sqft' => 'required|numeric|min:0',
            'raw_price_per_sqft' => 'required|numeric|min:0',
            'orphand_price_per_sqft' => 'required|numeric|min:0',
            'side_1_open_percent' => 'nullable|numeric',
            'side_2_open_percent' => 'nullable|numeric',
            'side_3_open_percent' => 'nullable|numeric',
            'side_4_open_percent' => 'nullable|numeric',
            'premium_price' => 'nullable|numeric|min:0',
            'standard_price' => 'nullable|numeric|min:0',
            'economy_price' => 'nullable|numeric|min:0',
            'addon_services_cutoff_date' => 'nullable|date',
            'document_upload_deadline' => 'nullable|date',
        ]);

        if ($request->hasFile('floorplan_image')) {
            $validated['floorplan_image'] = $request->file('floorplan_image')->store('floorplans', 'public');
        }

        $exhibition->update($validated);

        // Handle booth creation/updates
        if ($request->has('booths') && is_array($request->booths)) {
            foreach ($request->booths as $boothData) {
                if (isset($boothData['id']) && $boothData['id']) {
                    // Update existing booth
                    $booth = Booth::where('id', $boothData['id'])
                        ->where('exhibition_id', $exhibition->id)
                        ->first();
                    
                    if ($booth) {
                        $price = $this->calculateBoothPrice($exhibition, $boothData);
                        $booth->update([
                            'name' => $boothData['name'],
                            'category' => $boothData['category'],
                            'booth_type' => $boothData['booth_type'],
                            'size_sqft' => $boothData['size_sqft'],
                            'sides_open' => $boothData['sides_open'],
                            'price' => $price,
                            'is_free' => isset($boothData['is_free']) ? 1 : 0,
                        ]);
                    }
                } else {
                    // Create new booth - check for duplicate name
                    $existingBooth = Booth::where('exhibition_id', $exhibition->id)
                        ->where('name', $boothData['name'])
                        ->first();
                    
                    if (!$existingBooth) {
                        $price = $this->calculateBoothPrice($exhibition, $boothData);
                        Booth::create([
                            'exhibition_id' => $exhibition->id,
                            'name' => $boothData['name'],
                            'category' => $boothData['category'],
                            'booth_type' => $boothData['booth_type'],
                            'size_sqft' => $boothData['size_sqft'],
                            'sides_open' => $boothData['sides_open'],
                            'price' => $price,
                            'is_free' => isset($boothData['is_free']) ? 1 : 0,
                            'is_available' => true,
                            'is_booked' => false,
                        ]);
                    }
                }
            }
        }

        // Handle booth deletions
        if ($request->has('delete_booths')) {
            Booth::whereIn('id', $request->delete_booths)
                ->where('exhibition_id', $exhibition->id)
                ->where('is_booked', false)
                ->delete();
        }

        return redirect()->route('admin.exhibitions.step3', $exhibition->id);
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

    public function step3($id)
    {
        $exhibition = Exhibition::with('paymentSchedules')->findOrFail($id);
        return view('admin.exhibitions.step3', compact('exhibition'));
    }

    public function storeStep3(Request $request, $id)
    {
        $exhibition = Exhibition::findOrFail($id);
        
        $request->validate([
            'payment_parts' => 'required|integer|min:1|max:10',
            'parts' => 'required|array',
            'parts.*.percentage' => 'required|numeric|min:0|max:100',
            'parts.*.due_date' => 'required|date',
        ]);

        // Delete existing schedules
        PaymentSchedule::where('exhibition_id', $exhibition->id)->delete();

        // Create new schedules
        foreach ($request->parts as $index => $part) {
            PaymentSchedule::create([
                'exhibition_id' => $exhibition->id,
                'part_number' => $index + 1,
                'percentage' => $part['percentage'],
                'due_date' => $part['due_date'],
            ]);
        }

        return redirect()->route('admin.exhibitions.step4', $exhibition->id);
    }

    public function step4($id)
    {
        $exhibition = Exhibition::with(['badgeConfigurations', 'stallVariations'])->findOrFail($id);
        return view('admin.exhibitions.step4', compact('exhibition'));
    }

    public function storeStep4(Request $request, $id)
    {
        $exhibition = Exhibition::findOrFail($id);
        
        $request->validate([
            'badge_configurations' => 'required|array',
            'exhibition_manual_pdf' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('exhibition_manual_pdf')) {
            $exhibition->update([
                'exhibition_manual_pdf' => $request->file('exhibition_manual_pdf')->store('manuals', 'public')
            ]);
    }

        // Store badge configurations
        BadgeConfiguration::where('exhibition_id', $exhibition->id)->delete();
        foreach ($request->badge_configurations as $config) {
            $accessPermissions = $config['access_permissions'] ?? [];
            if (is_string($accessPermissions)) {
                $accessPermissions = json_decode($accessPermissions, true) ?? [];
            }
            
            BadgeConfiguration::create([
                'exhibition_id' => $exhibition->id,
                'badge_type' => $config['badge_type'],
                'quantity' => $config['quantity'],
                'pricing_type' => $config['pricing_type'],
                'price' => $config['pricing_type'] === 'Paid' ? ($config['price'] ?? 0) : 0,
                'needs_admin_approval' => isset($config['needs_admin_approval']) ? (bool)$config['needs_admin_approval'] : false,
                'access_permissions' => $accessPermissions,
            ]);
        }

        $exhibition->update(['status' => 'active']);
        return redirect()->route('admin.exhibitions.index')->with('success', 'Exhibition created successfully!');
    }

    public function show($id)
    {
        $exhibition = Exhibition::with(['booths', 'bookings', 'services'])->findOrFail($id);
        return view('admin.exhibitions.show', compact('exhibition'));
    }

    public function edit($id)
    {
        $exhibition = Exhibition::findOrFail($id);
        return view('admin.exhibitions.edit', compact('exhibition'));
    }

    public function update(Request $request, $id)
    {
        $exhibition = Exhibition::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'status' => 'nullable|in:draft,active,completed,cancelled',
        ]);
        
        $exhibition->update($validated);
        return redirect()->route('admin.exhibitions.index')->with('success', 'Exhibition updated successfully!');
    }

    public function destroy($id)
    {
        Exhibition::findOrFail($id)->delete();
        return redirect()->route('admin.exhibitions.index')->with('success', 'Exhibition deleted!');
    }
}
