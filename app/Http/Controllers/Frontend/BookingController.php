<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\Booking;
use App\Models\Exhibition;
use App\Models\Service;
use App\Models\BookingService;
use App\Models\Wallet;
use App\Models\Notification;
use App\Models\User;
use App\Models\Payment;
use App\Mail\BookingConfirmationMail;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Booking::with(['exhibition.requiredDocuments', 'booth'])
            ->where('user_id', $user->id);
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('status', 'confirmed');
            } elseif ($request->status === 'completed') {
                $query->where('status', 'confirmed')
                    ->whereHas('exhibition', function($q) {
                        $q->where('end_date', '<', now());
                    });
            } elseif ($request->status === 'cancelled') {
                $query->where('status', 'cancelled');
            } elseif ($request->status === 'pending') {
                $query->where('approval_status', 'pending');
            }
        }
        
        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('exhibition', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('booth', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $bookings = $query->latest()->paginate(15);
        
        return view('frontend.bookings.index', compact('bookings'));
    }
    
    public function create($exhibitionId)
    {
        // Redirect to new booking interface instead of old create form
        return redirect()->route('bookings.book', $exhibitionId);
    }

    public function book($exhibitionId)
    {
        // Load exhibition with floors and booth visibility rules:
        //  - Show main booths (parent_booth_id null) that are NOT split parents
        //  - Show split children (parent_booth_id not null AND is_split true)
        //  - Hide merged originals (parent_booth_id not null AND is_split false)
        $exhibition = Exhibition::with(['floors' => function($query) {
            $query->where('is_active', true)->orderBy('floor_number', 'asc');
        }, 'booths' => function($query) {
            $query->where(function($q) {
                // Main booths (including merged booth itself) but not split parents
                $q->whereNull('parent_booth_id')
                  ->where('is_split', false);
            })->orWhere(function($q) {
                // Split children
                $q->whereNotNull('parent_booth_id')
                  ->where('is_split', true);
            })
            ->orderBy('name', 'asc');
        }, 'booths.exhibitionBoothSize.sizeType', 'stallSchemes', 'boothSizes', 'stallVariations', 'addonServices'])->findOrFail($exhibitionId);

        // Get selected floor from request or default to first active floor
        $selectedFloorId = request()->query('floor_id');
        $selectedFloor = null;
        
        if ($selectedFloorId) {
            $selectedFloor = $exhibition->floors->firstWhere('id', $selectedFloorId);
        }
        
        // If no floor selected or selected floor not found, use first active floor
        if (!$selectedFloor && $exhibition->floors->isNotEmpty()) {
            $selectedFloor = $exhibition->floors->first();
            $selectedFloorId = $selectedFloor->id;
        }

        // Filter booths by selected floor if floor is selected
        // Only filter if there are floors configured, otherwise show all booths
        if ($selectedFloor && $exhibition->floors->isNotEmpty()) {
            $exhibition->booths = $exhibition->booths->filter(function($booth) use ($selectedFloorId) {
                // If booth has no floor_id, include it (for backward compatibility)
                // Otherwise, match the selected floor
                return $booth->floor_id === null || $booth->floor_id == $selectedFloorId;
            });
        }

        // Get all booths that are reserved (pending booking - regardless of payment status)
        // A booth is reserved when:
        // 1. Booking exists with approval_status = 'pending' (user has submitted booking for admin approval)
        // 2. Booking is not cancelled or rejected
        // 3. Payment status doesn't matter - once booking is created and submitted, booth is reserved
        $reservedBookings = Booking::where('exhibition_id', $exhibitionId)
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
        $bookedBookings = Booking::where('exhibition_id', $exhibitionId)
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

        // Map booth_id => company logo URL for booked/reserved booths (for floor plan display)
        $boothLogos = [];
        $collectBoothIdsFromBooking = function (Booking $booking) {
            $ids = [];
            if ($booking->booth_id) {
                $ids[] = $booking->booth_id;
            }
            $selectedBoothIds = $booking->selected_booth_ids;
            if ($selectedBoothIds && is_array($selectedBoothIds) && !empty($selectedBoothIds)) {
                $firstItem = reset($selectedBoothIds);
                if (is_array($firstItem) && isset($firstItem['id'])) {
                    foreach ($selectedBoothIds as $item) {
                        if (isset($item['id'])) {
                            $ids[] = $item['id'];
                        }
                    }
                } else {
                    foreach ($selectedBoothIds as $boothId) {
                        if ($boothId) {
                            $ids[] = $boothId;
                        }
                    }
                }
            }
            return array_unique(array_filter($ids));
        };
        foreach ($reservedBookings as $booking) {
            if ($booking->logo && Storage::disk('public')->exists($booking->logo)) {
                $url = asset('storage/' . ltrim($booking->logo, '/'));
                foreach ($collectBoothIdsFromBooking($booking) as $bid) {
                    $boothLogos[$bid] = $url;
                }
            }
        }
        foreach ($bookedBookings as $booking) {
            if ($booking->logo && Storage::disk('public')->exists($booking->logo)) {
                $url = asset('storage/' . ltrim($booking->logo, '/'));
                foreach ($collectBoothIdsFromBooking($booking) as $bid) {
                    $boothLogos[$bid] = $url;
                }
            }
        }

        // If no booths in DB, try to hydrate from saved floorplan JSON
        if ($exhibition->booths->isEmpty()) {
            $this->syncBoothsFromFloorplan($exhibition);
            // Reload with booths after sync
            $exhibition->load(['booths' => function($query) {
                $query->where(function($q) {
                    $q->whereNull('parent_booth_id')->where('is_split', false);
                })->orWhere(function($q) {
                    $q->whereNotNull('parent_booth_id')->where('is_split', true);
                })
                ->orderBy('name', 'asc');
            }, 'addonServices']);
            
            // Re-filter by floor after reload
            if ($selectedFloor) {
                $exhibition->booths = $exhibition->booths->filter(function($booth) use ($selectedFloorId) {
                    return $booth->floor_id == $selectedFloorId;
                });
            }
        }
        
        // Get unique categories from ExhibitionBoothSize (size sqft management)
        // NOT from booth category property - use the already loaded boothSizes relationship
        $categories = $exhibition->boothSizes
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->pluck('category')
            ->map(function($category) {
                // Map category values to readable names
                $normalized = trim((string)$category);
                
                // Map numeric and text values to standard names
                if (in_array($normalized, ['1', 'Premium'])) {
                    return 'Premium';
                } elseif (in_array($normalized, ['2', 'Standard'])) {
                    return 'Standard';
                } elseif (in_array($normalized, ['3', 'Economy'])) {
                    return 'Economy';
                } else {
                    return $normalized;
                }
            })
            ->unique()
            ->sort()
            ->map(function($category) {
                return [
                    'value' => $category,
                    'label' => $category
                ];
            })
            ->values();

        return view('frontend.bookings.book', [
            'exhibition' => $exhibition,
            'reservedBoothIds' => $reservedBoothIds,
            'bookedBoothIds' => $bookedBoothIds,
            'boothLogos' => $boothLogos,
            'floors' => $exhibition->floors ?? collect(),
            'selectedFloor' => $selectedFloor,
            'selectedFloorId' => $selectedFloorId,
            'categories' => $categories,
        ]);
    }

    /**
     * Hydrate booths for an exhibition from stored floorplan JSON if present.
     */
    private function syncBoothsFromFloorplan(Exhibition $exhibition): void
    {
        $pathPrimary = "floorplans/exhibition_{$exhibition->id}.json";
        $pathFallback = "private/floorplans/exhibition_{$exhibition->id}.json";

        $json = null;
        if (Storage::disk('local')->exists($pathPrimary)) {
            $json = Storage::disk('local')->get($pathPrimary);
        } elseif (Storage::disk('local')->exists($pathFallback)) {
            $json = Storage::disk('local')->get($pathFallback);
        }

        if (!$json) {
            return;
        }

        $payload = json_decode($json, true);
        if (!$payload || empty($payload['booths'])) {
            return;
        }

        $boothsData = collect($payload['booths']);

        // Valid size IDs for this exhibition to avoid FK errors
        $validSizeIds = \DB::table('exhibition_booth_sizes')
            ->where('exhibition_id', $exhibition->id)
            ->pluck('id')
            ->toArray();

        $existing = Booth::where('exhibition_id', $exhibition->id)
            ->whereIn('name', $boothsData->pluck('id')->filter())
            ->get()
            ->keyBy('name');

        foreach ($boothsData as $boothData) {
            $boothName = $boothData['id'] ?? null;
            if (!$boothName) {
                continue;
            }

            $booth = $existing[$boothName] ?? new Booth([
                'exhibition_id' => $exhibition->id,
                'name' => $boothName,
            ]);

            $booth->category = $boothData['category'] ?? $booth->category ?? 'Standard';
            $booth->booth_type = $booth->booth_type ?? 'Raw';
            $booth->size_sqft = $boothData['area'] ?? $booth->size_sqft ?? 0;
            $booth->sides_open = max(1, (int)($boothData['openSides'] ?? $booth->sides_open ?? 1));
            $booth->price = $boothData['price'] ?? $booth->price ?? 0;
            $booth->is_free = $booth->is_free ?? false;
            $booth->is_available = true;
            $booth->is_booked = false;
            $booth->merged_booths = $boothData['merged_booths'] ?? $booth->merged_booths ?? null;
            $booth->position_x = $boothData['x'] ?? $booth->position_x;
            $booth->position_y = $boothData['y'] ?? $booth->position_y;
            $booth->width = $boothData['width'] ?? $booth->width;
            $booth->height = $boothData['height'] ?? $booth->height;

            $sizeId = $boothData['sizeId'] ?? null;
            $booth->exhibition_booth_size_id = ($sizeId && in_array($sizeId, $validSizeIds)) ? $sizeId : null;

            // Optional discount fields from floorplan JSON
            $booth->discount_id = $boothData['discount_id'] ?? $boothData['discountId'] ?? $booth->discount_id;
            $booth->discount_user_id = $boothData['discount_user_id'] ?? $boothData['discountUserId'] ?? $booth->discount_user_id;

            $booth->save();
        }
    }

    public function details(Request $request, $exhibitionId)
    {
        $exhibition = Exhibition::with(['booths', 'stallSchemes', 'boothSizes'])->findOrFail($exhibitionId);
        $boothIds = array_filter(explode(',', $request->query('booths', '')));
        $merge = (bool) $request->query('merge', false);
        
        if (empty($boothIds)) {
            return redirect()->route('bookings.book', $exhibitionId)
                ->with('error', 'Please select at least one booth to continue.');
        }

        $booths = Booth::with(['discount'])
            ->whereIn('id', $boothIds)
            ->where('exhibition_id', $exhibition->id)
            ->get();

        if ($booths->isEmpty()) {
            return redirect()->route('bookings.book', $exhibitionId)
                ->with('error', 'Selected booths are not available. Please choose again.');
        }

        // Map client selection (type) and recompute prices server-side. Sides open is set by admin per booth.
        $selectionMeta = json_decode($request->query('booth_meta', '{}'), true) ?: [];
        $boothSelections = [];
        $boothTotal = 0;

        foreach ($booths as $booth) {
            $meta = $selectionMeta[$booth->id] ?? [];
            $type = $meta['type'] ?? 'Raw';
            $sides = max(1, (int)($booth->sides_open ?? 1));

            // Price without discount (for display) and with discount (for totals)
            $originalPrice = $this->calculateBoothPrice($booth, $exhibition, $type, $sides, null);
            $price = $this->calculateBoothPrice($booth, $exhibition, $type, $sides, auth()->id());
            $discountAmount = max(0, $originalPrice - $price);

            $boothSelections[] = [
                'id' => $booth->id,
                'name' => $booth->name,
                'type' => $type,
                'sides' => $sides,
                'price' => $price,
                'original_price' => $originalPrice,
                'discount_amount' => $discountAmount,
                'size_sqft' => $booth->size_sqft,
                'category' => $booth->category,
            ];

            $boothTotal += $price;
        }
        
        // Calculate services total (supports optional quantities via JSON payload)
        $servicesTotal = 0;
        $selectedServices = [];

        $servicesPayload = json_decode($request->query('services_payload', '[]'), true) ?: [];
        $serviceIdsFromQuery = array_filter(explode(',', $request->query('services', '')));

        if (!empty($servicesPayload)) {
            foreach ($servicesPayload as $row) {
                $id = (int) ($row['id'] ?? 0);
                $qty = max(0, (int) ($row['quantity'] ?? 0));
                $unitPrice = (float) ($row['unit_price'] ?? 0);
                $name = (string) ($row['name'] ?? '');

                if (!$id || $qty <= 0 || $unitPrice <= 0) {
                    continue;
                }

                $lineTotal = $qty * $unitPrice;
                $servicesTotal += $lineTotal;

                $selectedServices[] = [
                    'id' => $id,
                    'name' => $name,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }
        } elseif (!empty($serviceIdsFromQuery)) {
            // Legacy support: when only IDs are provided, assume quantity=1 and look up price-less services as 0
            foreach ($serviceIdsFromQuery as $rawId) {
                $id = (int) $rawId;
                if (!$id) {
                    continue;
                }
                $selectedServices[] = [
                    'id' => $id,
                    'name' => '',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_price' => 0,
                ];
            }
        }
        
        // Included item extras (from booth size items)
        $includedItemsRaw = json_decode($request->query('included_items', '[]'), true) ?: [];
        $includedExtras = [];
        $extrasTotal = 0;

        if (!empty($includedItemsRaw) && is_array($includedItemsRaw)) {
            $itemIds = collect($includedItemsRaw)
                ->pluck('item_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($itemIds)) {
                $items = \App\Models\ExhibitionBoothSizeItem::whereIn('id', $itemIds)->get()->keyBy('id');

                foreach ($includedItemsRaw as $row) {
                    $itemId = (int) ($row['item_id'] ?? 0);
                    if (!$itemId || !isset($items[$itemId])) {
                        continue;
                    }

                    $item = $items[$itemId];
                    $qty = max(0, (int) ($row['quantity'] ?? 0));
                    $unitPrice = isset($row['unit_price'])
                        ? (float) $row['unit_price']
                        : ((float) $item->price ?? 0);

                    if ($qty <= 0 || $unitPrice <= 0) {
                        continue;
                    }

                    $lineTotal = $qty * $unitPrice;
                    $extrasTotal += $lineTotal;

                    $includedExtras[] = [
                        'item_id' => $itemId,
                        'name' => $item->item_name,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                }
            }
        }
        
        $totalAmount = $boothTotal + $servicesTotal + $extrasTotal;

        return view('frontend.bookings.details', [
            'exhibition' => $exhibition,
            'booths' => $booths,
            'boothIds' => $boothIds,
            'boothSelections' => $boothSelections,
            'includedExtras' => $includedExtras,
            'extrasTotal' => $extrasTotal,
            'boothTotal' => $boothTotal,
            'selectedServices' => $selectedServices,
            'servicesTotal' => $servicesTotal,
            'totalAmount' => $totalAmount,
            'merge' => $merge,
        ]);
    }

    public function store(Request $request)
    {
        // Handle both old format (booth_id) and new format (booth_ids[])
        $boothIds = $request->booth_ids ?? ($request->booth_id ? [$request->booth_id] : []);
        
        $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'booth_ids' => 'required_without:booth_id|array|min:1',
            'booth_ids.*' => 'exists:booths,id',
            'booth_id' => 'required_without:booth_ids|exists:booths,id',
            'booth_selections' => 'nullable|array',
            'booth_selections.*.type' => 'nullable|in:Raw,Orphand',
            'booth_selections.*.sides' => 'nullable|integer|in:1,2,3,4',
            'merge_booths' => 'nullable|boolean',
            'contact_emails' => 'nullable|array|max:5',
            'contact_emails.*' => 'nullable|email',
            'contact_numbers' => 'nullable|array|max:5',
            'contact_numbers.*' => 'nullable|string',
            'services' => 'nullable|array',
            // Additional items (add‑on services) now come from exhibition-specific configuration,
            // not the global `services` table, so we only validate basic shape here.
            'services.*.service_id' => 'nullable|integer|min:1',
            'services.*.quantity' => 'nullable|integer|min:1',
            'included_item_extras' => 'nullable|array',
            'included_item_extras.*.item_id' => 'required_with:included_item_extras|exists:exhibition_booth_size_items,id',
            'included_item_extras.*.quantity' => 'required_with:included_item_extras|integer|min:1',
            'included_item_extras.*.unit_price' => 'required_with:included_item_extras|numeric|min:0',
            'logo' => 'nullable|image|max:5120', // 5MB
            'brochures' => 'nullable|array|max:5',
            'brochures.*' => 'file|mimes:pdf|max:5120', // 5MB each
            'terms' => 'required|accepted',
        ]);

        $user = Auth::user();
        $exhibition = Exhibition::with(['boothSizes', 'paymentSchedules'])->findOrFail($request->exhibition_id);
        
        // Normalize booth IDs
        if (empty($boothIds) && $request->booth_id) {
            $boothIds = [$request->booth_id];
        }
        
        // Ensure we have at least one booth
        if (empty($boothIds)) {
            return back()->withInput()->with('error', 'Please select at least one booth to book.');
        }
        
        // Remove duplicates and filter empty values
        $boothIds = array_unique(array_filter($boothIds));
        
        if (empty($boothIds)) {
            return back()->withInput()->with('error', 'Please select at least one valid booth to book.');
        }
        
        DB::beginTransaction();
        try {
            /**
             * HARD CONCURRENCY GUARD
             *
             * We must ensure that when two exhibitors try to book the same booth
             * at the same time, only the first one can proceed.
             *
             * Strategy:
             *  1) Lock the selected booth rows FOR UPDATE so concurrent requests
             *     for the same booths serialize.
             *  2) After locking, re-check availability using the latest values.
             *  3) Immediately mark the booths as not available (is_available=false)
             *     while keeping is_booked=false – they will only become fully
             *     "booked" after admin approval.
             */

            // Lock booths first to avoid race conditions
        $booths = Booth::with(['discount'])
                ->whereIn('id', $boothIds)
                ->where('exhibition_id', $exhibition->id)
                ->lockForUpdate()
                ->get();

            // Ensure all requested booths actually exist for this exhibition
            if ($booths->count() !== count($boothIds)) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'One or more selected booths are not valid for this exhibition. Please select different booths.');
            }

            // Ensure all booths are still available at the moment of booking
            $unavailableBooths = $booths->filter(function ($booth) {
                return !$booth->is_available;
            });

            if ($unavailableBooths->isNotEmpty()) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'One or more selected booths were just booked by another exhibitor. Please select different booths.');
            }

            // Reserve booths immediately so a second concurrent request cannot take them
            foreach ($booths as $booth) {
                $booth->is_available = false;
                // Do NOT mark as fully booked here – that happens after admin approval
                $booth->save();
            }

            $boothSelections = [];

            // Handle booth merging
            if ($request->merge_booths && count($boothIds) > 1) {
                $mergedBooth = $this->mergeBooths($booths, $exhibition);
                $boothId = $mergedBooth->id;
                $totalAmount = $mergedBooth->price;
                $boothSelections = $booths->map(function($booth) {
                    return [
                        'id' => $booth->id,
                        'name' => $booth->name,
                        'type' => $booth->booth_type ?? 'Raw',
                        'sides' => $booth->sides_open ?? 1,
                        'price' => $booth->price,
                        'size_sqft' => $booth->size_sqft,
                        'category' => $booth->category,
                    ];
                })->toArray();
            } else {
                // Single booth or multiple separate booths using selected type/sides
                $boothSelections = [];
                $totalAmount = 0;

                foreach ($booths as $booth) {
                    $selection = $request->input("booth_selections.{$booth->id}", []);
                    $type = $selection['type'] ?? 'Raw';
                    $sides = max(1, (int)($booth->sides_open ?? 1));
                    $price = $this->calculateBoothPrice($booth, $exhibition, $type, $sides, $user->id);

                    $boothSelections[] = [
                        'id' => $booth->id,
                        'name' => $booth->name,
                        'type' => $type,
                        'sides' => $sides,
                        'price' => $price,
                        'size_sqft' => $booth->size_sqft,
                        'category' => $booth->category,
                    ];

                    $totalAmount += $price;
                }

                $boothId = $booths->first()->id; // Primary booth
            }

            // Calculate additional services total from posted payload (no direct Service dependency)
            $servicesTotal = 0;
            $postedServices = [];
            if ($request->services) {
                foreach ($request->services as $serviceData) {
                    $serviceId = (int) ($serviceData['service_id'] ?? 0);
                    $quantity = max(0, (int) ($serviceData['quantity'] ?? 0));
                    $unitPrice = (float) ($serviceData['unit_price'] ?? 0);
                    $name = (string) ($serviceData['name'] ?? '');

                    if ($serviceId && $quantity > 0 && $unitPrice > 0) {
                        $lineTotal = $unitPrice * $quantity;
                        $servicesTotal += $lineTotal;
                        $postedServices[] = [
                            'service_id' => $serviceId,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'total_price' => $lineTotal,
                            'name' => $name,
                        ];
                    }
                }
            }

            // Included item extras total
            $extrasTotal = 0;
            $includedItemExtras = [];
            if ($request->included_item_extras) {
                foreach ($request->included_item_extras as $extraData) {
                    $itemId = (int) ($extraData['item_id'] ?? 0);
                    $qty = max(0, (int) ($extraData['quantity'] ?? 0));
                    $unitPrice = (float) ($extraData['unit_price'] ?? 0);

                    if ($itemId && $qty > 0 && $unitPrice > 0) {
                        $lineTotal = $qty * $unitPrice;
                        $extrasTotal += $lineTotal;
                        $includedItemExtras[] = [
                            'item_id' => $itemId,
                            'quantity' => $qty,
                            'unit_price' => $unitPrice,
                            'total_price' => $lineTotal,
                        ];
                    }
                }
            }

            $totalAmount += $servicesTotal + $extrasTotal;

            // Apply member discount automatically if exhibitor is a member
            $discountPercent = 0;
            if ($user->is_member && $exhibition->member_discount_percent > 0) {
                $memberPercent = (float) $exhibition->member_discount_percent;
                $maxPercent = $exhibition->maximum_discount_apply_percent !== null
                    ? (float) $exhibition->maximum_discount_apply_percent
                    : 100;
                $effectivePercent = min($memberPercent, $maxPercent);
                $discountAmount = ($totalAmount * $effectivePercent) / 100;
                $totalAmount = round($totalAmount - $discountAmount, 2);
                $discountPercent = round($effectivePercent, 2);
            }

            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('bookings/logos', 'public');
            }

            // Filter out empty contact emails and numbers
            $contactEmails = array_filter($request->contact_emails ?? [], function($email) {
                return !empty($email);
            });
            $contactNumbers = array_filter($request->contact_numbers ?? [], function($number) {
                return !empty($number);
            });
            
            // Ensure at least one contact email and number
            if (empty($contactEmails)) {
                $contactEmails = [Auth::user()->email];
            }
            if (empty($contactNumbers)) {
                $contactNumbers = [Auth::user()->phone ?? ''];
            }
            
            // Create booking with approval status
            $booking = Booking::create([
                'exhibition_id' => $exhibition->id,
                'user_id' => $user->id,
                'booth_id' => $boothId,
                'selected_booth_ids' => !empty($boothSelections) ? $boothSelections : $boothIds,
                'booking_number' => 'BK' . now()->format('YmdHis') . rand(100, 999),
                'status' => 'pending',
                'approval_status' => 'pending', // Requires admin approval
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'discount_percent' => $discountPercent,
                'discount_type' => $discountPercent > 0 ? 'member' : null,
                'member_discount_percent' => $discountPercent > 0 ? $discountPercent : null,
                'coupon_discount_percent' => null,
                'contact_emails' => array_values($contactEmails),
                'contact_numbers' => array_values($contactNumbers),
                'included_item_extras' => !empty($includedItemExtras) ? $includedItemExtras : null,
                'logo' => $logoPath,
            ]);
            
            // Handle brochure uploads (store as documents)
            if ($request->hasFile('brochures')) {
                foreach ($request->file('brochures') as $brochure) {
                    $brochurePath = $brochure->store('bookings/brochures', 'public');
                    \App\Models\Document::create([
                        'booking_id' => $booking->id,
                        'user_id' => $user->id,
                        'name' => 'Promotional Brochure - ' . $brochure->getClientOriginalName(),
                        'type' => 'Promotional Brochure',
                        'file_path' => $brochurePath,
                        'file_size' => $brochure->getSize(),
                        'status' => 'pending',
                    ]);
                }
            }
            
            // DO NOT mark booths as booked yet - wait for admin approval
            // Booths will be marked as booked when admin approves the request

            // Add additional services, but only for service IDs that actually exist
            if (!empty($postedServices)) {
                foreach ($postedServices as $serviceRow) {
                    $serviceId = $serviceRow['service_id'] ?? null;
                    if (!$serviceId) {
                        continue;
                    }

                    // Guard against stale/missing services to avoid FK violations
                    $serviceModel = Service::find($serviceId);
                    if (!$serviceModel) {
                        continue;
                    }

                    BookingService::create([
                        'booking_id' => $booking->id,
                        'service_id' => $serviceModel->id,
                        'quantity' => $serviceRow['quantity'],
                        'unit_price' => $serviceRow['unit_price'],
                        'total_price' => $serviceRow['total_price'],
                    ]);
                }
            }

            // Create all part payments based on payment schedule
            // IMPORTANT: Payment amounts are calculated and stored at booking creation time.
            // If admin later changes the payment schedule percentages, existing bookings
            // will continue to use their stored payment amounts. Only new bookings will
            // use the updated payment schedule percentages.
            $paymentSchedules = $exhibition->paymentSchedules()->orderBy('part_number', 'asc')->get();
            
            // Calculate gateway fee: 2.5% of total amount
            $gatewayFeePercent = 2.5;
            $totalGatewayFee = ($totalAmount * $gatewayFeePercent) / 100;
            
            // Prepare payment records first to calculate distribution
            $paymentRecords = [];
            
            if ($paymentSchedules->isEmpty()) {
                // Fallback: If no payment schedule exists, create a single initial payment
                // using the initial_payment_percent from exhibition
                $initialPercent = $exhibition->initial_payment_percent ?? 10;
                $initialAmount = ($totalAmount * $initialPercent) / 100;
                
                $paymentRecords[] = [
                    'payment_type' => 'initial',
                    'amount' => round($initialAmount, 2),
                    'due_date' => now()->addDays(7), // Default 7 days for initial payment
                ];
            } else {
                // Create payment records for all parts based on payment schedule
                foreach ($paymentSchedules as $schedule) {
                    $paymentAmount = ($totalAmount * $schedule->percentage) / 100;
                    $paymentType = $schedule->part_number == 1 ? 'initial' : 'installment';
                    
                    $paymentRecords[] = [
                        'payment_type' => $paymentType,
                        'amount' => round($paymentAmount, 2),
                        'due_date' => $schedule->due_date,
                    ];
                }
            }
            
            // Distribute gateway fee across all payments
            $paymentCount = count($paymentRecords);
            if ($paymentCount > 0 && $totalGatewayFee > 0) {
                // Calculate base fee per payment (rounded down)
                $baseFeePerPayment = floor($totalGatewayFee * 100 / $paymentCount) / 100;
                $remainingFee = $totalGatewayFee - ($baseFeePerPayment * $paymentCount);
                
                // Distribute remaining fee (due to rounding) to first payments
                $remainingFeeCents = round($remainingFee * 100);
                
                foreach ($paymentRecords as $index => &$record) {
                    $gatewayCharge = $baseFeePerPayment;
                    
                    // Add 1 cent (0.01) to first payments if there's remaining fee
                    if ($remainingFeeCents > 0) {
                        $gatewayCharge += 0.01;
                        $remainingFeeCents--;
                    }
                    
                    $record['gateway_charge'] = round($gatewayCharge, 2);
                }
            } else {
                // No payments or no gateway fee
                foreach ($paymentRecords as &$record) {
                    $record['gateway_charge'] = 0;
                }
            }
            
            // Create payment records in database
            foreach ($paymentRecords as $index => $record) {
                $paymentType = $record['payment_type'];
                $partNumber = $paymentSchedules->isEmpty() ? 1 : ($index + 1);
                
                Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'payment_number' => 'PM' . now()->format('YmdHis') . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . str_pad($partNumber, 2, '0', STR_PAD_LEFT) . rand(10, 99),
                    'payment_type' => $paymentType,
                    'payment_method' => 'online', // Default, will be updated when user makes payment
                    'status' => 'pending',
                    'approval_status' => 'pending',
                    'amount' => $record['amount'],
                    'gateway_charge' => $record['gateway_charge'],
                    'due_date' => $record['due_date'],
                ]);
            }

            DB::commit();

            // Reload booking with relationships for email
            $booking->load(['exhibition', 'user', 'booth', 'bookingServices.service']);

            // Send booking confirmation email to exhibitor
            try {
                Mail::to($user->email)->send(new BookingConfirmationMail($booking, false));
            } catch (\Exception $e) {
                Log::error('Failed to send booking confirmation email to exhibitor: ' . $e->getMessage());
            }

            // Notify all admins about new booking and send emails
            $admins = \App\Models\User::role('Admin')->orWhere('id', 1)->get();
            foreach ($admins as $admin) {
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'booking',
                    'title' => 'New Booking Request',
                    'message' => $user->name . ' has submitted a new booking request for ' . $exhibition->name . ' (Booking #' . $booking->booking_number . ')',
                    'notifiable_type' => \App\Models\Booking::class,
                    'notifiable_id' => $booking->id,
                ]);

                // Send booking confirmation email to admin
                try {
                    if ($admin->email) {
                        Mail::to($admin->email)->send(new BookingConfirmationMail($booking, true));
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send booking confirmation email to admin: ' . $e->getMessage());
                }
            }

            return redirect()->route('payments.create', $booking->id)
                ->with('success', 'Booking captured. Please complete payment to submit your booth request to the admin.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking creation failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exhibition_id' => $exhibition->id,
                'booth_ids' => $boothIds,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Booking failed: ' . $e->getMessage() . '. Please try again or contact support.');
        }
    }

    private function mergeBooths($booths, $exhibition)
    {
        // Derive merged booth name from selected booth names (sorted, concatenated)
        $mergedNames = $booths->pluck('name')->sort()->implode('');
        $totalSize = $booths->sum('size_sqft');
        $maxSidesOpen = $booths->max('sides_open');

        // Calculate merged price based on exhibition pricing (same logic as floorplan merge)
        $basePrice = $exhibition->price_per_sqft ?? 0;
        $mergedPrice = $totalSize * $basePrice;

        // Apply side‑open percentage
        $sideOpenPercent = 0;
        if ($maxSidesOpen == 1) {
            $sideOpenPercent = $exhibition->side_1_open_percent ?? 0;
        } elseif ($maxSidesOpen == 2) {
            $sideOpenPercent = $exhibition->side_2_open_percent ?? 0;
        } elseif ($maxSidesOpen == 3) {
            $sideOpenPercent = $exhibition->side_3_open_percent ?? 0;
        } elseif ($maxSidesOpen == 4) {
            $sideOpenPercent = $exhibition->side_4_open_percent ?? 0;
        }

        $mergedPrice = $mergedPrice * (1 + $sideOpenPercent / 100);

        // Calculate merged booth position and size to cover all originals
        $positions = $booths->map(function ($booth) {
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

        // Create merged booth as a real booth in the floorplan
        $mergedBooth = Booth::create([
            'exhibition_id' => $exhibition->id,
            'name' => $mergedNames,
            'category' => $booths->first()->category,
            'booth_type' => $booths->first()->booth_type,
            'size_sqft' => $totalSize,
            'sides_open' => $maxSidesOpen,
            'price' => $mergedPrice,
            'is_merged' => true,
            'merged_booths' => $booths->pluck('id')->toArray(),
            'is_available' => true,   // will appear on floorplan but treated as booked via booking status
            'is_booked' => false,     // we rely on approval flow / pending booking to show as taken
            'position_x' => $minX,
            'position_y' => $minY,
            'width' => $mergedWidth,
            'height' => $mergedHeight,
        ]);

        // Mark original booths as merged children so they are hidden on the floorplan
        foreach ($booths as $booth) {
            $booth->update([
                'is_available' => false,
                'is_merged' => true,
                'parent_booth_id' => $mergedBooth->id,
            ]);
        }

        return $mergedBooth;
    }

    private function calculateBoothPrice(Booth $booth, Exhibition $exhibition, string $type, int $sides, ?int $userId = null): float
    {
        // If userId is null, always ignore booth-level discount (used when we need the undiscounted price)
        // Booth price comes strictly from selected size (row/orphan)
        $basePrice = $this->getSizePriceForType($exhibition, $booth, $type);

        // Apply per‑booth discount (only on base price) when configured for this user
        $discountedBase = $basePrice;
        if ($userId !== null && $basePrice > 0 && $booth->discount_id && $booth->discount && $booth->discount->status === 'active') {
            // If a specific user is configured, only apply when it matches
            if ($booth->discount_user_id === null || ($userId !== null && (int) $booth->discount_user_id === (int) $userId)) {
                $discountAmount = 0;
                if ($booth->discount->type === 'percentage') {
                    $discountAmount = $basePrice * ((float) $booth->discount->amount / 100);
                } else {
                    $discountAmount = (float) $booth->discount->amount;
                }
                $discountAmount = min($discountAmount, $basePrice);
                $discountedBase = max(0, $basePrice - $discountAmount);
            }
        }

        // Side-open surcharge is always calculated from the original base price
        $sidePercent = match ($sides) {
            1 => $exhibition->side_1_open_percent ?? 0,
            2 => $exhibition->side_2_open_percent ?? 0,
            3 => $exhibition->side_3_open_percent ?? 0,
            4 => $exhibition->side_4_open_percent ?? 0,
            default => 0,
        };

        $sideExtra = $basePrice * ($sidePercent / 100);

        return max(0, round($discountedBase + $sideExtra, 2));
    }

    /**
     * Resolve booth price from the exhibition booth size record for the chosen type.
     */
    private function getSizePriceForType(Exhibition $exhibition, Booth $booth, string $type): float
    {
        $exhibition->loadMissing('boothSizes');
        $sizeId = $booth->exhibition_booth_size_id;
        $size = $sizeId
            ? $exhibition->boothSizes->firstWhere('id', $sizeId)
            : $exhibition->boothSizes->firstWhere('size_sqft', $booth->size_sqft);

        // If no direct match by id or sqft, fall back to the first defined size configuration
        if (!$size) {
            $size = $exhibition->boothSizes->first();
        }

        if (!$size) {
            // Final fallback: use stored booth price so price is never 0
            return (float) ($booth->price ?? 0);
        }

        $rowPrice = (float) ($size->row_price ?? 0);
        $orphanPrice = (float) ($size->orphan_price ?? 0);

        return $type === 'Orphand'
            ? ($orphanPrice > 0 ? $orphanPrice : $rowPrice)
            : ($rowPrice > 0 ? $rowPrice : $orphanPrice);
    }

    public function show(string $id)
    {
        $booking = Booking::with([
            'exhibition.booths', 
            'exhibition.requiredDocuments',
            'exhibition.addonServices',
            'booth', 
            'payments', 
            'bookingServices.service', 
            'additionalServiceRequests.service',
            'documents' => function($query) {
                $query->orderBy('created_at', 'desc'); // Get latest documents first
            },
            'documents.requiredDocument', 
            'badges'
        ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('frontend.bookings.show', compact('booking'));
    }

    /**
     * Display a professional invoice view for a specific booking.
     */
    public function invoice(string $id)
    {
        $booking = Booking::with([
                'exhibition',
                'booth',
                'bookingServices.service',
                'payments',
                'user',
            ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        // General/company settings for logo and header details
        $generalSettings = Setting::getByGroup('general');

        return view('frontend.bookings.invoice', compact('booking', 'generalSettings'));
    }

    public function edit(string $id)
    {
        $booking = Booking::where('user_id', auth()->id())->findOrFail($id);
        return view('frontend.bookings.edit', compact('booking'));
    }

    public function showCancel(string $id)
    {
        $booking = Booking::with(['booth', 'exhibition'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $cancellationCharge = round($booking->total_amount * 0.15, 2);

        return view('frontend.bookings.cancel', compact('booking', 'cancellationCharge'));
    }

    public function update(Request $request, string $id)
    {
        $booking = Booking::with('documents')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        $request->validate([
            'contact_emails' => 'nullable|array|max:5',
            'contact_emails.*' => 'email',
            'contact_numbers' => 'nullable|array|max:5',
            'logo' => 'nullable|image|max:5120', // 5MB
            'brochures' => 'nullable|array|max:5',
            'brochures.*' => 'file|mimes:pdf|max:5120', // 5MB each
            'remove_brochure_ids' => 'nullable|array',
            'remove_brochure_ids.*' => 'integer',
        ]);

        // Update basic contact info if provided
        $booking->update([
            'contact_emails' => $request->has('contact_emails')
                ? ($request->contact_emails ?? [])
                : $booking->contact_emails,
            'contact_numbers' => $request->has('contact_numbers')
                ? ($request->contact_numbers ?? [])
                : $booking->contact_numbers,
        ]);

        // Handle removal of existing promotional brochures if requested
        $removeIds = collect($request->input('remove_brochure_ids', []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($removeIds)) {
            $brochuresToRemove = $booking->documents()
                ->where('type', 'Promotional Brochure')
                ->whereIn('id', $removeIds)
                ->get();

            foreach ($brochuresToRemove as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }
        }

        // Update company logo if a new file is uploaded
        if ($request->hasFile('logo')) {
            // Delete old logo file if it exists
            if ($booking->logo && Storage::disk('public')->exists($booking->logo)) {
                Storage::disk('public')->delete($booking->logo);
            }

            $logoPath = $request->file('logo')->store('bookings/logos', 'public');
            $booking->update([
                'logo' => $logoPath,
            ]);
        }

        // Allow exhibitor to upload additional promotional brochures (max 5 in total)
        if ($request->hasFile('brochures')) {
            $existingCount = $booking->documents()
                ->where('type', 'Promotional Brochure')
                ->count();

            $newFiles = $request->file('brochures', []);
            $newCount = is_array($newFiles) ? count($newFiles) : 0;

            if ($existingCount + $newCount > 5) {
                return back()
                    ->withInput()
                    ->with('error', 'You can upload a maximum of 5 promotional brochures per booking. You already have ' . $existingCount . ' uploaded.');
            }

            foreach ($newFiles as $brochure) {
                if (!$brochure) {
                    continue;
                }

                $brochurePath = $brochure->store('bookings/brochures', 'public');

                \App\Models\Document::create([
                    'booking_id' => $booking->id,
                    'user_id' => auth()->id(),
                    'name' => 'Promotional Brochure - ' . $brochure->getClientOriginalName(),
                    'type' => 'Promotional Brochure',
                    'file_path' => $brochurePath,
                    'file_size' => $brochure->getSize(),
                    'status' => 'pending',
                ]);
            }
        }

        return redirect()->route('bookings.show', $booking->id)
            ->with('success', 'Booking updated successfully.');
    }

    public function cancel(Request $request, string $id)
    {
        $booking = Booking::with('booth', 'exhibition')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        // Exhibitor can only REQUEST cancellation.
        // Do NOT change status or free the booth here.
        // Just record the reason so admin can review and decide refund / wallet credit.
        $booking->update([
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        // Notify all admins that a cancellation was requested
        $admins = \App\Models\User::role('Admin')->orWhere('id', 1)->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'booking',
                'title' => 'Booking Cancellation Requested',
                'message' => auth()->user()->name . ' has requested cancellation for booking #' . $booking->booking_number . ' (' . $booking->exhibition->name . ')',
                'notifiable_type' => \App\Models\Booking::class,
                'notifiable_id' => $booking->id,
            ]);
        }

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('success', 'Your cancellation request has been submitted. Admin will review and decide the refund or wallet credit.');
    }

    public function showReplaceBooth(string $id)
    {
        $booking = Booking::with(['booth', 'exhibition.floors', 'exhibition.booths'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        // Check if booking is cancelled
        if ($booking->status === 'cancelled') {
            return redirect()->route('bookings.show', $booking->id)
                ->with('error', 'Cannot replace booth for a cancelled booking.');
        }

        // Check if booth exists
        if (!$booking->booth) {
            return redirect()->route('bookings.show', $booking->id)
                ->with('error', 'No booth associated with this booking.');
        }

        $exhibition = $booking->exhibition;
        $currentBooth = $booking->booth;

        // Get selected floor from request or default to first active floor
        $selectedFloorId = request()->query('floor_id');
        $selectedFloor = null;
        
        // Load floors if not already loaded
        if (!$exhibition->relationLoaded('floors')) {
            $exhibition->load('floors');
        }
        
        if ($selectedFloorId && $exhibition->floors) {
            $selectedFloor = $exhibition->floors->firstWhere('id', $selectedFloorId);
        }
        
        // If no floor selected or selected floor not found, use first active floor
        if (!$selectedFloor && $exhibition->floors && $exhibition->floors->isNotEmpty()) {
            $selectedFloor = $exhibition->floors->where('is_active', true)->first();
            if ($selectedFloor) {
                $selectedFloorId = $selectedFloor->id;
            }
        }

        // Filter booths by:
        // 1. Same exhibition
        // 2. Same category
        // 3. Same size_sqft (booth configuration)
        // 4. Available and not booked
        // 5. Exclude current booth
        $availableBooths = Booth::where('exhibition_id', $exhibition->id)
            ->where('category', $currentBooth->category)
            ->where('size_sqft', $currentBooth->size_sqft)
            ->where('is_available', true)
            ->where('is_booked', false)
            ->where('id', '!=', $currentBooth->id)
            ->where(function($query) {
                // Show main booths (including merged booth itself) but not split parents
                $query->where(function($q) {
                    $q->whereNull('parent_booth_id')
                      ->where('is_split', false);
                })->orWhere(function($q) {
                    // Split children
                    $q->whereNotNull('parent_booth_id')
                      ->where('is_split', true);
                });
            });

        // Filter by selected floor if floor is selected
        if ($selectedFloor && $selectedFloorId) {
            $availableBooths->where('floor_id', $selectedFloorId);
        }

        $availableBooths = $availableBooths->orderBy('name', 'asc')->get();

        // Get all booths for floor plan display (to show unavailable ones too)
        $allBooths = Booth::where('exhibition_id', $exhibition->id);
        
        if ($selectedFloor && $selectedFloorId) {
            $allBooths->where('floor_id', $selectedFloorId);
        }
        
        $allBooths = $allBooths->where(function($query) {
            $query->where(function($q) {
                $q->whereNull('parent_booth_id')
                  ->where('is_split', false);
            })->orWhere(function($q) {
                $q->whereNotNull('parent_booth_id')
                  ->where('is_split', true);
            });
        })->orderBy('name', 'asc')->get();

        // Get reserved booths (pending bookings)
        $reservedBookings = Booking::where('exhibition_id', $exhibition->id)
            ->where('approval_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->get();
        
        $reservedBoothIds = [];
        foreach ($reservedBookings as $reservedBooking) {
            if ($reservedBooking->booth_id) {
                $reservedBoothIds[] = $reservedBooking->booth_id;
            }
            if ($reservedBooking->selected_booth_ids) {
                $reservedBoothIds = array_merge($reservedBoothIds, $reservedBooking->selected_booth_ids);
            }
        }

        // Get booked booths
        $bookedBoothIds = Booking::where('exhibition_id', $exhibition->id)
            ->where('status', 'confirmed')
            ->where('approval_status', 'approved')
            ->whereNotNull('booth_id')
            ->pluck('booth_id')
            ->toArray();

        return view('frontend.bookings.replace-booth', compact(
            'booking',
            'exhibition',
            'currentBooth',
            'availableBooths',
            'allBooths',
            'reservedBoothIds',
            'bookedBoothIds',
            'selectedFloor',
            'selectedFloorId'
        ));
    }

    public function replace(Request $request, string $id)
    {
        $booking = Booking::with('booth')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        // Check if booking is cancelled
        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cannot replace booth for a cancelled booking.');
        }

        // Check if booth exists
        if (!$booking->booth) {
            return back()->with('error', 'No booth associated with this booking.');
        }

        $exhibition = $booking->exhibition;
        $currentBooth = $booking->booth;
        
        $request->validate([
            'new_booth_id' => 'required|exists:booths,id',
        ]);

        $newBooth = Booth::where('id', $request->new_booth_id)
            ->where('exhibition_id', $exhibition->id)
            ->where('is_available', true)
            ->where('is_booked', false)
            ->firstOrFail();

        // Validate same category and size_sqft
        if ($newBooth->category !== $currentBooth->category) {
            return back()->with('error', 'Selected booth must have the same category (' . $currentBooth->category . ').');
        }

        if ($newBooth->size_sqft != $currentBooth->size_sqft) {
            return back()->with('error', 'Selected booth must have the same size (' . $currentBooth->size_sqft . ' sq ft).');
        }

        DB::beginTransaction();
        try {
            // Free old booth (immediately available)
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }

            // Update booking - keep total_amount the same
            // Services, items, payments, and badges are linked by booking_id, so they automatically stay
            $booking->update([
                'booth_id' => $newBooth->id,
            ]);

            // Mark new booth as booked
            $newBooth->update([
                'is_available' => false,
                'is_booked' => true,
            ]);

            DB::commit();

            return redirect()
                ->route('bookings.show', $booking->id)
                ->with('success', 'Booth replaced successfully. All additional services, items, payments, and badges have been preserved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Booth replacement failed: ' . $e->getMessage());
        }
    }

    /**
     * Download possession letter (Exhibitor)
     */
    public function downloadPossessionLetter($id)
    {
        $booking = Booking::with(['exhibition', 'booth', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if (!$booking->possession_letter_issued) {
            return back()->with('error', 'Possession letter has not been generated yet.');
        }

        // Find the possession letter document
        $document = \App\Models\Document::where('booking_id', $booking->id)
            ->where('type', 'Possession Letter')
            ->latest()
            ->first();

        if (!$document || !Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Possession letter file not found.');
        }

        return Storage::disk('public')->download($document->file_path, 'Possession_Letter_' . $booking->booking_number . '.pdf');
    }
}
