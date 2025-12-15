<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use App\Models\Booth;
use App\Models\PaymentSchedule;
use App\Models\BadgeConfiguration;
use App\Models\StallVariation;
use App\Models\ExhibitionAddonService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ExhibitionController extends Controller
{
    public function index()
    {
        // Show 10 exhibitions per page for manageable server-side pagination
        $exhibitions = Exhibition::latest()->paginate(10);
        return view('admin.exhibitions.index', compact('exhibitions'));
    }

    public function management()
    {
        $exhibitions = Exhibition::latest()->get();
        return view('admin.exhibitions.management', compact('exhibitions'));
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
            'location' => 'nullable|string|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        // Parse datetime fields
        $startDateTime = \Carbon\Carbon::parse($validated['start_datetime']);
        $endDateTime = \Carbon\Carbon::parse($validated['end_datetime']);

        $exhibition = Exhibition::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'venue' => $validated['venue'],
            'city' => $validated['location'] ?? '',
            'country' => 'India', // Default or from form
            'start_date' => $startDateTime->format('Y-m-d'),
            'end_date' => $endDateTime->format('Y-m-d'),
            'start_time' => $startDateTime->format('H:i:s'),
            'end_time' => $endDateTime->format('H:i:s'),
        ]);

        return redirect()->route('admin.exhibitions.step2', $exhibition->id);
    }

    public function step2($id)
    {
        $exhibition = Exhibition::with(['stallSchemes', 'booths', 'boothSizes.items', 'addonServices'])->findOrFail($id);
        return view('admin.exhibitions.step2', compact('exhibition'));
    }

    public function storeStep2(Request $request, $id)
    {
        $exhibition = Exhibition::findOrFail($id);
        
        $validated = $request->validate([
            'floorplan_image' => 'nullable|image|max:10240',
            'price_per_sqft' => 'nullable|numeric|min:0',
            'rear_price_per_sqft' => 'nullable|numeric|min:0',
            'orphaned_price_per_sqft' => 'nullable|numeric|min:0',
            'side_1_open_percent' => 'nullable|numeric',
            'side_2_open_percent' => 'nullable|numeric',
            'side_3_open_percent' => 'nullable|numeric',
            'side_4_open_percent' => 'nullable|numeric',
            'premium_price' => 'nullable|numeric|min:0',
            'standard_price' => 'nullable|numeric|min:0',
            'economy_price' => 'nullable|numeric|min:0',
            'booth_sizes' => 'nullable|array',
            'booth_sizes.*.size_sqft' => 'nullable|numeric|min:0',
            'booth_sizes.*.row_price' => 'nullable|numeric|min:0',
            'booth_sizes.*.orphan_price' => 'nullable|numeric|min:0',
            'booth_sizes.*.category' => 'nullable|string|max:255',
            'booth_sizes.*.items' => 'nullable|array',
            'booth_sizes.*.items.*.name' => 'nullable|string|max:255',
            'booth_sizes.*.items.*.quantity' => 'nullable|integer|min:0',
            'booth_sizes.*.items.*.price' => 'nullable|numeric|min:0',
            'booth_sizes.*.items.*.images' => 'nullable|array',
            'booth_sizes.*.items.*.images.*' => 'nullable|image|max:10240',
            'addon_services' => 'nullable|array',
            'addon_services.*.item_name' => 'nullable|string|max:255',
            'addon_services.*.price_per_quantity' => 'nullable|numeric|min:0',
        ]);

        $updateData = [
            'price_per_sqft' => $validated['price_per_sqft'] ?? $exhibition->price_per_sqft,
            'raw_price_per_sqft' => $validated['rear_price_per_sqft'] ?? $exhibition->raw_price_per_sqft,
            'orphand_price_per_sqft' => $validated['orphaned_price_per_sqft'] ?? $exhibition->orphand_price_per_sqft,
            'side_1_open_percent' => $validated['side_1_open_percent'] ?? null,
            'side_2_open_percent' => $validated['side_2_open_percent'] ?? null,
            'side_3_open_percent' => $validated['side_3_open_percent'] ?? null,
            'side_4_open_percent' => $validated['side_4_open_percent'] ?? null,
            'premium_price' => $validated['premium_price'] ?? $exhibition->premium_price ?? 0,
            'standard_price' => $validated['standard_price'] ?? $exhibition->standard_price ?? 0,
            'economy_price' => $validated['economy_price'] ?? $exhibition->economy_price ?? 0,
        ];

        if ($request->hasFile('floorplan_image')) {
            $updateData['floorplan_image'] = $request->file('floorplan_image')->store('floorplans', 'public');
        }

        $exhibition->update($updateData);

        // Handle booth size blocks and included items
        $exhibition->boothSizes()->delete();
        $boothSizes = $request->input('booth_sizes', []);

        foreach ($boothSizes as $sizeIndex => $boothSizeData) {
            $sizeSqft = $boothSizeData['size_sqft'] ?? null;
            $rowPrice = $boothSizeData['row_price'] ?? null;
            $orphanPrice = $boothSizeData['orphan_price'] ?? null;
            $category = $boothSizeData['category'] ?? null;

            if (is_null($sizeSqft) && is_null($rowPrice) && is_null($orphanPrice) && empty($category)) {
                continue;
            }

            $boothSize = $exhibition->boothSizes()->create([
                'size_sqft' => $sizeSqft,
                'row_price' => $rowPrice,
                'orphan_price' => $orphanPrice,
                'category' => $category,
            ]);

            $items = $boothSizeData['items'] ?? [];
            foreach ($items as $itemIndex => $itemData) {
                $name = $itemData['name'] ?? null;
                $quantity = $itemData['quantity'] ?? null;
                $price = $itemData['price'] ?? null;

                // Existing images coming from the edit form
                $existingImages = $itemData['existing_images'] ?? [];
                $removeImages = $itemData['remove_existing_images'] ?? [];

                if (!is_array($existingImages)) {
                    $existingImages = [];
                }
                if (!is_array($removeImages)) {
                    $removeImages = [];
                }

                // Compute which existing images should be kept
                $keptImages = array_values(array_diff($existingImages, $removeImages));

                // Physically delete any removed images
                if (!empty($removeImages)) {
                    Storage::disk('public')->delete($removeImages);
                }

                // New uploads for this item
                $itemFiles = $request->file("booth_sizes.$sizeIndex.items.$itemIndex.images") ?? [];
                $uploadedImages = [];
                foreach ($itemFiles as $imageFile) {
                    if ($imageFile) {
                        $uploadedImages[] = $imageFile->store('booth-size-items', 'public');
                    }
                }

                $finalImages = array_merge($keptImages, $uploadedImages);

                // Skip completely empty items (no name/qty/price and no images)
                if (empty($name) && is_null($quantity) && is_null($price) && empty($finalImages)) {
                    continue;
                }

                $boothSize->items()->create([
                    'item_name' => $name,
                    'quantity' => $quantity ?? 0,
                    'price' => $price ?? null,
                    'images' => $finalImages,
                ]);
            }
        }

        // Handle add-on services (separate from sizes)
        ExhibitionAddonService::where('exhibition_id', $exhibition->id)->delete();
        $addonServices = $request->input('addon_services', []);
        foreach ($addonServices as $service) {
            $name = $service['item_name'] ?? null;
            $price = $service['price_per_quantity'] ?? null;
            if (empty($name) && is_null($price)) {
                continue;
            }
            ExhibitionAddonService::create([
                'exhibition_id' => $exhibition->id,
                'item_name' => $name ?? '',
                'price_per_quantity' => $price ?? 0,
            ]);
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
        $exhibition = Exhibition::with(['paymentSchedules', 'boothSizes'])->findOrFail($id);
        return view('admin.exhibitions.step3', compact('exhibition'));
    }

    public function storeStep3(Request $request, $id)
    {
        $exhibition = Exhibition::findOrFail($id);
        
        $request->validate([
            'parts' => 'required|array',
            'parts.*.percentage' => 'required|numeric|min:0|max:100',
            'parts.*.due_date' => 'required|date',
            'addon_services_cutoff_date' => 'nullable|date',
            'document_upload_deadline' => 'nullable|date',
            'floorplan_images' => 'nullable|array',
            'floorplan_images.*' => 'nullable|image|max:10240',
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

        // Update cut-off dates
        $updateData = [
            'addon_services_cutoff_date' => $request->addon_services_cutoff_date,
            'document_upload_deadline' => $request->document_upload_deadline,
        ];

        // Handle multiple floorplan images (admin can upload, preview, and remove)
        $existingImages = $request->input('existing_floorplan_images', []);
        $removeImages = $request->input('remove_floorplan_images', []);

        if (!is_array($existingImages)) {
            $existingImages = [];
        }
        if (!is_array($removeImages)) {
            $removeImages = [];
        }

        // Keep only those existing images that are not marked for removal
        $keptImages = array_values(array_diff($existingImages, $removeImages));

        // Physically delete removed images from storage
        if (!empty($removeImages)) {
            Storage::disk('public')->delete($removeImages);
        }

        // Handle newly uploaded images
        $newImages = [];
        if ($request->hasFile('floorplan_images')) {
            foreach ($request->file('floorplan_images') as $imageFile) {
                if ($imageFile) {
                    $newImages[] = $imageFile->store('floorplans', 'public');
                }
            }
        }

        // Merge kept existing and new images
        $finalFloorplanImages = array_merge($keptImages, $newImages);
        $updateData['floorplan_images'] = $finalFloorplanImages;

        // For backward compatibility, keep single floorplan_image as the first image (if any)
        if (!empty($finalFloorplanImages)) {
            $updateData['floorplan_image'] = $finalFloorplanImages[0];
        }

        $exhibition->update($updateData);

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
            'stall_variations' => 'nullable|array',
            'stall_variations.*' => 'image|max:5120',
        ]);

        if ($request->hasFile('exhibition_manual_pdf')) {
            $exhibition->update([
                'exhibition_manual_pdf' => $request->file('exhibition_manual_pdf')->store('manuals', 'public')
            ]);
        }

        // Store badge configurations
        BadgeConfiguration::where('exhibition_id', $exhibition->id)->delete();
        foreach ($request->badge_configurations as $badgeType => $config) {
            $accessPermissions = $config['access_permissions'] ?? [];
            if (!is_array($accessPermissions)) {
                $accessPermissions = [];
            }
            
            BadgeConfiguration::create([
                'exhibition_id' => $exhibition->id,
                'badge_type' => $badgeType,
                'quantity' => $config['quantity'] ?? 0,
                'pricing_type' => $config['pricing_type'] ?? 'Free',
                'price' => ($config['pricing_type'] ?? 'Free') === 'Paid' ? ($config['price'] ?? 0) : 0,
                'needs_admin_approval' => isset($config['needs_admin_approval']) ? (bool)$config['needs_admin_approval'] : false,
                'access_permissions' => $accessPermissions,
            ]);
        }

        // Handle stall variations upload
        if ($request->hasFile('stall_variations')) {
            // Delete existing variations for this exhibition
            StallVariation::where('exhibition_id', $exhibition->id)->delete();
            
            $files = $request->file('stall_variations');
            if (count($files) >= 3) {
                // Create variation with all three views
                StallVariation::create([
                    'exhibition_id' => $exhibition->id,
                    'stall_type' => 'A - 1 Side Open',
                    'sides_open' => 1,
                    'front_view' => $files[0]->store('stall-variations', 'public'),
                    'side_view_left' => $files[1]->store('stall-variations', 'public'),
                    'side_view_right' => $files[2]->store('stall-variations', 'public'),
                ]);
            }
        }

        $exhibition->update(['status' => 'active']);
        return redirect()->route('admin.exhibitions.index')->with('success', 'Exhibition created successfully!');
    }

    public function show($id)
    {
        try {
            $exhibition = Exhibition::with(['booths', 'bookings'])->findOrFail($id);
            
            // Return JSON if requested via AJAX
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json($exhibition);
            }
            
            return view('admin.exhibitions.show', compact('exhibition'));
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->route('admin.exhibitions.index')
                ->with('error', 'Error loading exhibition: ' . $e->getMessage());
        }
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
        if ($request->boolean('redirect_step2')) {
            return redirect()->route('admin.exhibitions.step2', $exhibition->id)
                ->with('success', 'Exhibition updated successfully! Continue with Step 2.');
        }

        return redirect()->route('admin.exhibitions.index')->with('success', 'Exhibition updated successfully!');
    }

    public function destroy($id)
    {
        Exhibition::findOrFail($id)->delete();
        return redirect()->route('admin.exhibitions.index')->with('success', 'Exhibition deleted!');
    }
}
