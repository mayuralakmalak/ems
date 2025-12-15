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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Booking::with(['exhibition', 'booth'])
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
        // Load exhibition with booth visibility rules:
        //  - Show main booths (parent_booth_id null) that are NOT split parents
        //  - Show split children (parent_booth_id not null AND is_split true)
        //  - Hide merged originals (parent_booth_id not null AND is_split false)
        $exhibition = Exhibition::with(['booths' => function($query) {
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
        }, 'services', 'stallSchemes'])->findOrFail($exhibitionId);

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
            }]);
        }
        
        return view('frontend.bookings.book', compact('exhibition'));
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
            $booth->sides_open = $boothData['openSides'] ?? $booth->sides_open ?? 1;
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

            $booth->save();
        }
    }

    public function details(Request $request, $exhibitionId)
    {
        $exhibition = Exhibition::with(['booths', 'services', 'stallSchemes'])->findOrFail($exhibitionId);
        $boothIds = array_filter(explode(',', $request->query('booths', '')));
        
        if (empty($boothIds)) {
            return redirect()->route('bookings.book', $exhibitionId)
                ->with('error', 'Please select at least one booth to continue.');
        }

        $booths = Booth::whereIn('id', $boothIds)
            ->where('exhibition_id', $exhibition->id)
            ->get();

        if ($booths->isEmpty()) {
            return redirect()->route('bookings.book', $exhibitionId)
                ->with('error', 'Selected booths are not available. Please choose again.');
        }

        // Map client selection (type/sides) and recompute prices server-side
        $selectionMeta = json_decode($request->query('booth_meta', '{}'), true) ?: [];
        $boothSelections = [];
        $boothTotal = 0;

        foreach ($booths as $booth) {
            $meta = $selectionMeta[$booth->id] ?? [];
            $type = $meta['type'] ?? 'Raw';
            $sides = isset($meta['sides']) ? (int) $meta['sides'] : ($booth->sides_open ?? 1);
            $price = $this->calculateBoothPrice($booth, $exhibition, $type, $sides);

            $boothSelections[] = [
                'id' => $booth->id,
                'name' => $booth->name,
                'type' => $type,
                'sides' => $sides,
                'price' => $price,
                'size_sqft' => $booth->size_sqft,
                'category' => $booth->category,
            ];

            $boothTotal += $price;
        }
        
        // Calculate services total
        $serviceIds = array_filter(explode(',', $request->query('services', '')));
        $servicesTotal = 0;
        if (!empty($serviceIds)) {
            $services = Service::whereIn('id', $serviceIds)
                ->where('exhibition_id', $exhibition->id)
                ->where('is_active', true)
                ->get();
            $servicesTotal = $services->sum('price');
        }
        
        $totalAmount = $boothTotal + $servicesTotal;

        return view('frontend.bookings.details', [
            'exhibition' => $exhibition,
            'booths' => $booths,
            'boothIds' => $boothIds,
            'boothSelections' => $boothSelections,
            'totalAmount' => $totalAmount,
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
            'services.*.service_id' => 'exists:services,id',
            'services.*.quantity' => 'integer|min:1',
            'logo' => 'nullable|image|max:5120', // 5MB
            'brochures' => 'nullable|array|max:3',
            'brochures.*' => 'file|mimes:pdf|max:5120', // 5MB each
            'terms' => 'required|accepted',
        ]);

        $user = Auth::user();
        $exhibition = Exhibition::findOrFail($request->exhibition_id);
        
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
            // Check if booths are available
            $booths = Booth::whereIn('id', $boothIds)
                ->where('exhibition_id', $exhibition->id)
                ->where('is_available', true)
                ->get();

            if ($booths->count() !== count($boothIds)) {
                DB::rollBack();
                return back()->withInput()->with('error', 'One or more selected booths are not available. Please select different booths.');
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
                    $sides = isset($selection['sides']) ? (int) $selection['sides'] : ($booth->sides_open ?? 1);
                    $price = $this->calculateBoothPrice($booth, $exhibition, $type, $sides);

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

            // Calculate additional services total
            $servicesTotal = 0;
            if ($request->services) {
                foreach ($request->services as $serviceData) {
                    $service = Service::find($serviceData['service_id']);
                    if ($service && $service->exhibition_id === $exhibition->id) {
                        $quantity = $serviceData['quantity'] ?? 1;
                        $servicesTotal += $service->price * $quantity;
                    }
                }
            }

            $totalAmount += $servicesTotal;

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
                'contact_emails' => array_values($contactEmails),
                'contact_numbers' => array_values($contactNumbers),
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

            // Add additional services
            if ($request->services) {
                foreach ($request->services as $serviceData) {
                    $service = Service::find($serviceData['service_id']);
                    if ($service && $service->exhibition_id === $exhibition->id) {
                        $quantity = $serviceData['quantity'] ?? 1;
                        BookingService::create([
                            'booking_id' => $booking->id,
                            'service_id' => $service->id,
                            'quantity' => $quantity,
                            'unit_price' => $service->price,
                            'total_price' => $service->price * $quantity,
                        ]);
                    }
                }
            }

            DB::commit();

            // Notify all admins about new booking
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
        $mergedNames = $booths->pluck('name')->sort()->implode('');
        $totalSize = $booths->sum('size_sqft');
        $totalPrice = $booths->sum('price');
        $maxSidesOpen = $booths->max('sides_open');
        
        // Calculate merged price based on exhibition pricing
        $basePrice = $exhibition->price_per_sqft ?? 0;
        $mergedPrice = $totalSize * $basePrice;
        
        // Apply side open percentage
        $sideOpenPercent = 0;
        if ($maxSidesOpen == 1) $sideOpenPercent = $exhibition->side_1_open_percent ?? 0;
        elseif ($maxSidesOpen == 2) $sideOpenPercent = $exhibition->side_2_open_percent ?? 0;
        elseif ($maxSidesOpen == 3) $sideOpenPercent = $exhibition->side_3_open_percent ?? 0;
        elseif ($maxSidesOpen == 4) $sideOpenPercent = $exhibition->side_4_open_percent ?? 0;
        
        $mergedPrice = $mergedPrice * (1 + $sideOpenPercent / 100);

        // Create merged booth
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
        ]);

        return $mergedBooth;
    }

    private function calculateBoothPrice(Booth $booth, Exhibition $exhibition, string $type, int $sides): float
    {
        $size = $booth->size_sqft ?? 0;
        
        // Base price from exhibition (not multiplied by size)
        $basePrice = $exhibition->price_per_sqft ?? 0;
        
        // Add Raw/Orphand price per sqft * size to base price
        $rawOrphandPricePerSqft = $type === 'Raw'
            ? ($exhibition->raw_price_per_sqft ?? 0)
            : ($exhibition->orphand_price_per_sqft ?? 0);
        
        $rawOrphandAddition = $rawOrphandPricePerSqft * $size;
        
        // Start with base price + Raw/Orphand addition
        $price = $basePrice + $rawOrphandAddition;

        // Add side percentage amount (not multiplied, but added)
        $sidePercent = match ($sides) {
            1 => $exhibition->side_1_open_percent ?? 0,
            2 => $exhibition->side_2_open_percent ?? 0,
            3 => $exhibition->side_3_open_percent ?? 0,
            4 => $exhibition->side_4_open_percent ?? 0,
            default => 0,
        };

        $price = $price + $sidePercent;

        // Add/subtract category premium/economy price
        if (($booth->category ?? 'Standard') === 'Premium') {
            $price += $exhibition->premium_price ?? 0;
        } elseif (($booth->category ?? 'Standard') === 'Economy') {
            $price -= $exhibition->economy_price ?? 0;
        }

        return max(0, round($price, 2));
    }

    public function show(string $id)
    {
        $booking = Booking::with(['exhibition.booths', 'booth', 'payments', 'bookingServices.service', 'documents', 'badges'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('frontend.bookings.show', compact('booking'));
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
        $booking = Booking::where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'contact_emails' => 'nullable|array|max:5',
            'contact_emails.*' => 'email',
            'contact_numbers' => 'nullable|array|max:5',
        ]);

        $booking->update([
            'contact_emails' => $request->contact_emails ?? $booking->contact_emails,
            'contact_numbers' => $request->contact_numbers ?? $booking->contact_numbers,
        ]);

        return redirect()->route('bookings.show', $booking->id)
            ->with('success', 'Booking updated successfully.');
    }

    public function cancel(Request $request, string $id)
    {
        $booking = Booking::with('booth')->where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
            'refund_option' => 'nullable|in:full_refund,partial_refund,wallet_credit,bank_refund',
            'account_details' => 'nullable|string|max:2000',
        ]);

        $cancellationType = $request->refund_option === 'wallet_credit' ? 'wallet_credit' : 'refund';

        // Update booking status
        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancellation_type' => $cancellationType,
            'account_details' => $request->account_details,
            'cancellation_amount' => $booking->total_amount ? round($booking->total_amount * 0.15, 2) : null,
        ]);

        // Free up the booth
        if ($booking->booth) {
            $booking->booth->update([
                'is_available' => true,
                'is_booked' => false,
            ]);
        }

        // Admin will decide on refund later
        return back()->with('success', 'Booking cancelled. Admin will process refund/wallet credit.');
    }

    public function replace(Request $request, string $id)
    {
        $booking = Booking::where('user_id', auth()->id())->findOrFail($id);
        $exhibition = $booking->exhibition;
        
        $request->validate([
            'new_booth_id' => 'required|exists:booths,id',
        ]);

        $newBooth = Booth::where('id', $request->new_booth_id)
            ->where('exhibition_id', $exhibition->id)
            ->where('is_available', true)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Free old booth
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }

            // Calculate price difference
            $priceDifference = $newBooth->price - $booking->booth->price;
            $newTotalAmount = $booking->total_amount + $priceDifference;

            // Update booking
            $booking->update([
                'booth_id' => $newBooth->id,
                'status' => 'replaced',
                'total_amount' => $newTotalAmount,
            ]);

            // Mark new booth as booked
            $newBooth->update([
                'is_available' => false,
                'is_booked' => true,
            ]);

            DB::commit();

            return back()->with('success', 'Booth replaced successfully. Price difference: ₹' . number_format($priceDifference, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Booth replacement failed: ' . $e->getMessage());
        }
    }
}
