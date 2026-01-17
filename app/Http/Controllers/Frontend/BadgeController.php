<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Booking;
use App\Models\Exhibition;
use App\Models\BadgeConfiguration;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QRCode;
use Illuminate\Support\Facades\Storage;

class BadgeController extends Controller
{
    public function index()
    {
        $badges = Badge::where('user_id', auth()->id())
            ->with(['booking', 'exhibition'])
            ->latest()
            ->get();
        return view('frontend.badges.index', compact('badges'));
    }

    public function create()
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->with('exhibition')
            ->get();
        return view('frontend.badges.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'booth_id' => 'nullable|exists:booths,id',
            'badge_type' => 'required|in:Primary,Secondary,Additional',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'valid_for_dates' => 'nullable|array',
            'valid_for_dates.*' => 'nullable|date',
            'access_permissions' => 'nullable|array',
        ]);

        $booking = Booking::where('user_id', auth()->id())
            ->with('exhibition')
            ->findOrFail($request->booking_id);

        $exhibition = $booking->exhibition;
        
        // Get booth and booth size from selected booth or booking's booths
        $selectedBoothId = $request->booth_id;
        $booth = null;
        $boothSizeId = null;
        
        // If booth_id is provided, use it
        if ($selectedBoothId) {
            $booth = \App\Models\Booth::where('id', $selectedBoothId)
                ->where('exhibition_id', $exhibition->id)
                ->first();
            
            if ($booth && $booth->exhibition_booth_size_id) {
                $boothSizeId = $booth->exhibition_booth_size_id;
            }
        } else {
            // Fallback: Get all booth IDs from booking
            $boothIds = [];
            if ($booking->booth_id) {
                $boothIds[] = $booking->booth_id;
            }
            
            // Extract from selected_booth_ids
            $selectedBoothIds = $booking->selected_booth_ids ?? [];
            if (is_array($selectedBoothIds)) {
                foreach ($selectedBoothIds as $item) {
                    if (is_array($item) && isset($item['id'])) {
                        $boothIds[] = (int) $item['id'];
                    } elseif (is_numeric($item)) {
                        $boothIds[] = (int) $item;
                    } elseif (is_object($item) && isset($item->id)) {
                        $boothIds[] = (int) $item->id;
                    }
                }
            }
            
            $boothIds = array_values(array_unique(array_filter($boothIds)));
            
            // Get the first booth's size (primary booth or first selected booth)
            if (!empty($boothIds)) {
                $booth = \App\Models\Booth::where('id', $boothIds[0])
                    ->where('exhibition_id', $exhibition->id)
                    ->first();
                
                if ($booth && $booth->exhibition_booth_size_id) {
                    $boothSizeId = $booth->exhibition_booth_size_id;
                }
            }
        }
        
        // Get badge configuration based on booth size
        $badgeConfigQuery = BadgeConfiguration::where('exhibition_id', $exhibition->id)
            ->where('badge_type', $request->badge_type);
        
        if ($boothSizeId) {
            $badgeConfigQuery->where('exhibition_booth_size_id', $boothSizeId);
        } else {
            // Fallback: if no booth size found, try without size filter (for backward compatibility)
            $badgeConfigQuery->whereNull('exhibition_booth_size_id');
        }
        
        $badgeConfig = $badgeConfigQuery->first();

        if (!$badgeConfig) {
            return back()->with('error', 'Badge configuration not found for this exhibition and booth size.');
        }

        // Validate that booth_id is provided when multiple booths exist
        if (!$selectedBoothId) {
            // Get all booth IDs from booking to check if multiple booths exist
            $boothIds = [];
            if ($booking->booth_id) {
                $boothIds[] = $booking->booth_id;
            }
            
            $selectedBoothIds = $booking->selected_booth_ids ?? [];
            if (is_array($selectedBoothIds)) {
                foreach ($selectedBoothIds as $item) {
                    if (is_array($item) && isset($item['id'])) {
                        $boothIds[] = (int) $item['id'];
                    } elseif (is_numeric($item)) {
                        $boothIds[] = (int) $item;
                    } elseif (is_object($item) && isset($item->id)) {
                        $boothIds[] = (int) $item->id;
                    }
                }
            }
            
            $boothIds = array_values(array_unique(array_filter($boothIds)));
            
            if (count($boothIds) > 1) {
                return back()->with('error', 'Please select a booth to create the badge for.');
            } elseif (count($boothIds) === 1) {
                $selectedBoothId = $boothIds[0];
            }
        }

        // Current usage for this badge type for this specific booth
        $existingCount = Badge::where('booking_id', $booking->id)
            ->where('badge_type', $request->badge_type)
            ->where('booth_id', $selectedBoothId)
            ->count();

        // Free quota per type = configured quantity
        $freeQuota = (int) $badgeConfig->quantity;
        $withinFreeQuota = $existingCount < $freeQuota;

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('badges/photos', 'public');
        }

        // Calculate price & payment status
        // Quantity = free quota, price = per additional badge beyond quota.
        // If we are still within free quota OR no price is configured, this badge is free.
        // Once free quota is exceeded and price > 0, this badge is chargeable at that price.
        $price = 0.0;
        $isPaid = false; // Will be marked true only after payment approval
        $unitPrice = (float) ($badgeConfig->price ?? 0);

        if (!$withinFreeQuota && $unitPrice > 0) {
            $price = $unitPrice;
        }

        // Generate QR code
        $qrData = json_encode([
            'badge_id' => null, // Will be set after creation
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'exhibition_id' => $exhibition->id,
            'badge_type' => $request->badge_type,
            'name' => $request->name,
        ]);

        // Normalise valid dates (remove empty values)
        $validDates = collect($request->input('valid_for_dates', []))
            ->filter()
            ->values()
            ->all();

        // Enforce that all selected dates fall within the exhibition date range
        if ($exhibition && $exhibition->start_date && $exhibition->end_date) {
            $startDate = \Carbon\Carbon::parse($exhibition->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($exhibition->end_date)->endOfDay();

            foreach ($validDates as $date) {
                try {
                    $d = \Carbon\Carbon::parse($date);
                } catch (\Exception $e) {
                    continue;
                }

                if ($d->lt($startDate) || $d->gt($endDate)) {
                    return back()
                        ->withInput()
                        ->with('error', 'Valid For Date(s) must be between the exhibition start and end dates.');
                }
            }
        }

        // Load additional badge settings for this booth size (for admin approval on paid badges)
        $additionalConfigQuery = BadgeConfiguration::where('exhibition_id', $exhibition->id)
            ->where('badge_type', 'Additional');
        
        if ($boothSizeId) {
            $additionalConfigQuery->where('exhibition_booth_size_id', $boothSizeId);
        } else {
            $additionalConfigQuery->whereNull('exhibition_booth_size_id');
        }
        
        $additionalConfig = $additionalConfigQuery->first();

        // Create badge
        $badge = Badge::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'exhibition_id' => $exhibition->id,
            'booth_id' => $selectedBoothId,
            'exhibition_booth_size_id' => $boothSizeId,
            'badge_type' => $request->badge_type,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'photo' => $photoPath,
            // Paid badges may require admin approval depending on Additional config
            'status' => ($price > 0 && ($additionalConfig->needs_admin_approval ?? false)) ? 'pending' : 'approved',
            'is_paid' => $isPaid,
            'price' => $price,
            'access_permissions' => $request->access_permissions ?? [],
            // If only one date is selected, also store it in the legacy single-date column
            'valid_for_date' => count($validDates) === 1 ? $validDates[0] : null,
            'valid_for_dates' => $validDates,
        ]);

        // Generate QR code with badge ID
        $qrData = json_encode([
            'badge_id' => $badge->id,
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'exhibition_id' => $exhibition->id,
            'badge_type' => $request->badge_type,
            'name' => $request->name,
        ]);

        $qrCodePath = 'badges/qrcodes/' . $badge->id . '.svg';
        $qrCode = QRCode::size(200)->generate($qrData);
        Storage::disk('public')->put($qrCodePath, $qrCode);

        $badge->update(['qr_code' => $qrCodePath]);

        // If this badge is chargeable, create a pending payment entry linked to the booking
        if ($price > 0) {
            Payment::create([
                'booking_id' => $booking->id,
                'user_id' => auth()->id(),
                'payment_number' => 'PM' . now()->format('YmdHis') . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . 'BG' . str_pad($badge->id, 4, '0', STR_PAD_LEFT),
                'payment_type' => 'installment',
                'payment_method' => 'online',
                'status' => 'pending',
                'approval_status' => 'pending',
                'amount' => round($price, 2),
                'gateway_charge' => 0,
                'due_date' => now()->addDays(7),
            ]);
        }

        return redirect()->route('badges.index')->with('success', 'Badge created successfully.');
    }

    /**
     * Return badge limits and current usage for a given booking.
     *
     * Used by the exhibitor panel on the badges page to show how many
     * badges are allowed and how many are already used for each category.
     */
    public function bookingLimits($bookingId, Request $request)
    {
        $booking = Booking::where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->with('exhibition')
            ->findOrFail($bookingId);

        $exhibitionId = $booking->exhibition_id;
        
        // If booth_id is provided, filter by that booth's size
        $selectedBoothId = $request->input('booth_id');
        $boothSizeIds = [];
        
        if ($selectedBoothId) {
            // Get the specific booth's size
            $booth = \App\Models\Booth::where('id', $selectedBoothId)
                ->where('exhibition_id', $exhibitionId)
                ->first();
            
            if ($booth && $booth->exhibition_booth_size_id) {
                $boothSizeIds = [$booth->exhibition_booth_size_id];
            }
        } else {
            // Get all booth IDs from booking
            $boothIds = [];
            if ($booking->booth_id) {
                $boothIds[] = $booking->booth_id;
            }
            
            // Extract from selected_booth_ids
            $selectedBoothIds = $booking->selected_booth_ids ?? [];
            if (is_array($selectedBoothIds)) {
                foreach ($selectedBoothIds as $item) {
                    if (is_array($item) && isset($item['id'])) {
                        $boothIds[] = (int) $item['id'];
                    } elseif (is_numeric($item)) {
                        $boothIds[] = (int) $item;
                    } elseif (is_object($item) && isset($item->id)) {
                        $boothIds[] = (int) $item->id;
                    }
                }
            }
            
            $boothIds = array_values(array_unique(array_filter($boothIds)));
            
            // Get booth sizes from booths
            if (!empty($boothIds)) {
                $booths = \App\Models\Booth::whereIn('id', $boothIds)
                    ->where('exhibition_id', $exhibitionId)
                    ->whereNotNull('exhibition_booth_size_id')
                    ->pluck('exhibition_booth_size_id')
                    ->unique()
                    ->toArray();
                
                $boothSizeIds = array_values($booths);
            }
        }
        
        // If no booth sizes found, return empty or use fallback
        if (empty($boothSizeIds)) {
            // Try to get configs without size filter (backward compatibility)
            $configs = BadgeConfiguration::where('exhibition_id', $exhibitionId)
                ->whereNull('exhibition_booth_size_id')
                ->get()
                ->keyBy('badge_type');
        } else {
            // Get badge configurations for all booth sizes in this booking
            $configs = BadgeConfiguration::where('exhibition_id', $exhibitionId)
                ->whereIn('exhibition_booth_size_id', $boothSizeIds)
                ->get()
                ->groupBy('exhibition_booth_size_id');
        }

        $badgeTypes = ['Primary', 'Secondary', 'Additional'];
        $data = [];

        // Get all booths from booking to show limits per booth
        $boothIds = [];
        if ($booking->booth_id) {
            $boothIds[] = $booking->booth_id;
        }
        
        // Extract from selected_booth_ids
        $selectedBoothIds = $booking->selected_booth_ids ?? [];
        if (is_array($selectedBoothIds)) {
            foreach ($selectedBoothIds as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $boothIds[] = (int) $item['id'];
                } elseif (is_numeric($item)) {
                    $boothIds[] = (int) $item;
                } elseif (is_object($item) && isset($item->id)) {
                    $boothIds[] = (int) $item->id;
                }
            }
        }
        
        $boothIds = array_values(array_unique(array_filter($boothIds)));
        
        // If we have booths, return limits per booth
        if (!empty($boothIds)) {
            $booths = \App\Models\Booth::whereIn('id', $boothIds)
                ->where('exhibition_id', $exhibitionId)
                ->with('exhibitionBoothSize.sizeType')
                ->get();
            
            foreach ($booths as $booth) {
                $boothSizeId = $booth->exhibition_booth_size_id;
                
                if (!$boothSizeId) {
                    continue;
                }
                
                // Get badge configs for this booth's size
                $sizeConfigs = $configs->get($boothSizeId);
                if (!$sizeConfigs) {
                    continue;
                }
                
                // Get booth size info
                $boothSize = $booth->exhibitionBoothSize;
                if (!$boothSize) {
                    continue;
                }
                
                $sizeLabel = $boothSize->size_sqft . ' sq meter';
                if ($boothSize->sizeType) {
                    $sizeLabel .= ' (' . $boothSize->sizeType->length . ' x ' . $boothSize->sizeType->width . ')';
                }
                
                foreach ($badgeTypes as $type) {
                    $config = $sizeConfigs->firstWhere('badge_type', $type);
                    
                    if (!$config) {
                        continue;
                    }
                    
                    $allowed = (int) $config->quantity;
                    
                    // Count badges for this specific booth (not booth size)
                    $used = Badge::where('booking_id', $booking->id)
                        ->where('badge_type', $type)
                        ->where('booth_id', $booth->id)
                        ->count();
                    
                    $remaining = max(0, $allowed - $used);
                    
                    $data[] = [
                        'badge_type' => $type,
                        'booth_id' => $booth->id,
                        'booth_name' => $booth->name,
                        'booth_size_id' => $boothSizeId,
                        'booth_size_label' => $sizeLabel,
                        'allowed' => $allowed,
                        'used' => $used,
                        'remaining' => $remaining,
                        'pricing_type' => $config->pricing_type,
                        'price' => (float) $config->price,
                    ];
                }
            }
        } else {
            // Fallback: use configs without size (backward compatibility)
            foreach ($badgeTypes as $type) {
                $config = $configs->get($type);
                
                if (!$config) {
                    continue;
                }
                
                $allowed = (int) $config->quantity;
                
                $used = Badge::where('booking_id', $booking->id)
                    ->where('badge_type', $type)
                    ->count();
                
                $remaining = max(0, $allowed - $used);
                
                $data[] = [
                    'badge_type' => $type,
                    'allowed' => $allowed,
                    'used' => $used,
                    'remaining' => $remaining,
                    'pricing_type' => $config->pricing_type,
                    'price' => (float) $config->price,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get booths for a booking with their sizes
     */
    public function bookingBooths($bookingId)
    {
        $booking = Booking::where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->with('exhibition')
            ->findOrFail($bookingId);

        $exhibitionId = $booking->exhibition_id;
        
        // Get all booth IDs from booking
        $boothIds = [];
        if ($booking->booth_id) {
            $boothIds[] = $booking->booth_id;
        }
        
        // Extract from selected_booth_ids
        $selectedBoothIds = $booking->selected_booth_ids ?? [];
        if (is_array($selectedBoothIds)) {
            foreach ($selectedBoothIds as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $boothIds[] = (int) $item['id'];
                } elseif (is_numeric($item)) {
                    $boothIds[] = (int) $item;
                } elseif (is_object($item) && isset($item->id)) {
                    $boothIds[] = (int) $item->id;
                }
            }
        }
        
        $boothIds = array_values(array_unique(array_filter($boothIds)));
        
        if (empty($boothIds)) {
            return response()->json([
                'success' => true,
                'data' => [],
                'single_booth' => false,
            ]);
        }
        
        // Get booths with their sizes
        $booths = \App\Models\Booth::whereIn('id', $boothIds)
            ->where('exhibition_id', $exhibitionId)
            ->with('exhibitionBoothSize.sizeType')
            ->get();
        
        $boothData = [];
        foreach ($booths as $booth) {
            $sizeLabel = 'N/A';
            if ($booth->exhibitionBoothSize) {
                $size = $booth->exhibitionBoothSize;
                $sizeLabel = $size->size_sqft . ' sq meter';
                if ($size->sizeType) {
                    $sizeLabel .= ' (' . $size->sizeType->length . ' x ' . $size->sizeType->width . ')';
                }
            }
            
            $boothData[] = [
                'id' => $booth->id,
                'name' => $booth->name,
                'size_id' => $booth->exhibition_booth_size_id,
                'size_label' => $sizeLabel,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $boothData,
            'single_booth' => count($boothData) === 1,
        ]);
    }

    public function show(string $id)
    {
        $badge = Badge::where('user_id', auth()->id())
            ->with(['booking', 'exhibition'])
            ->findOrFail($id);
        return view('frontend.badges.show', compact('badge'));
    }

    public function edit(string $id)
    {
        $badge = Badge::where('user_id', auth()->id())
            ->where('status', '!=', 'approved') // Can only edit pending badges
            ->findOrFail($id);
        $bookings = Booking::where('user_id', auth()->id())->get();
        return view('frontend.badges.edit', compact('badge', 'bookings'));
    }

    public function update(Request $request, string $id)
    {
        $badge = Badge::where('user_id', auth()->id())
            ->where('status', '!=', 'approved')
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'valid_for_dates' => 'nullable|array',
            'valid_for_dates.*' => 'nullable|date',
            'access_permissions' => 'nullable|array',
        ]);

        $validDates = collect($request->input('valid_for_dates', []))
            ->filter()
            ->values()
            ->all();

        // Enforce that all selected dates fall within the exhibition date range when updating
        $exhibition = $badge->exhibition;
        if ($exhibition && $exhibition->start_date && $exhibition->end_date) {
            $startDate = \Carbon\Carbon::parse($exhibition->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($exhibition->end_date)->endOfDay();

            foreach ($validDates as $date) {
                try {
                    $d = \Carbon\Carbon::parse($date);
                } catch (\Exception $e) {
                    continue;
                }

                if ($d->lt($startDate) || $d->gt($endDate)) {
                    return back()
                        ->withInput()
                        ->with('error', 'Valid For Date(s) must be between the exhibition start and end dates.');
                }
            }
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'valid_for_date' => count($validDates) === 1 ? $validDates[0] : null,
            'valid_for_dates' => $validDates,
            'access_permissions' => $request->access_permissions ?? [],
            'status' => 'pending', // Reset to pending when updated
        ];

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($badge->photo && Storage::disk('public')->exists($badge->photo)) {
                Storage::disk('public')->delete($badge->photo);
            }
            $updateData['photo'] = $request->file('photo')->store('badges/photos', 'public');
        }

        $badge->update($updateData);

        return redirect()->route('badges.index')->with('success', 'Badge updated successfully.');
    }

    public function destroy(string $id)
    {
        $badge = Badge::where('user_id', auth()->id())
            ->findOrFail($id);

        // Check if there's a payment associated with this badge
        $payment = Payment::where('booking_id', $badge->booking_id)
            ->where('payment_number', 'like', '%BG' . str_pad($badge->id, 4, '0', STR_PAD_LEFT))
            ->first();

        // If payment exists and is completed, prevent deletion
        if ($payment && $payment->status === 'completed') {
            return back()->with('error', 'Cannot delete badge with completed payment. Please contact admin for assistance.');
        }

        // Delete associated payment if it exists and is pending
        if ($payment && $payment->status === 'pending') {
            $payment->delete();
        }

        // Delete files
        if ($badge->photo && Storage::disk('public')->exists($badge->photo)) {
            Storage::disk('public')->delete($badge->photo);
        }
        if ($badge->qr_code && Storage::disk('public')->exists($badge->qr_code)) {
            Storage::disk('public')->delete($badge->qr_code);
        }

        $badge->delete();

        return back()->with('success', 'Badge deleted successfully.');
    }

    public function download(string $id)
    {
        $badge = Badge::where('user_id', auth()->id())
            ->findOrFail($id);

        if ($badge->status !== 'approved') {
            return redirect()->route('badges.index')
                ->with('error', 'This badge is not approved yet. Please wait for admin approval before downloading.');
        }

        // Generate PDF or return badge view for download
        return view('frontend.badges.download', compact('badge'));
    }
}
