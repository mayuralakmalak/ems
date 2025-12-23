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
        
        // Get badge configuration
        $badgeConfig = BadgeConfiguration::where('exhibition_id', $exhibition->id)
            ->where('badge_type', $request->badge_type)
            ->first();

        if (!$badgeConfig) {
            return back()->with('error', 'Badge configuration not found for this exhibition.');
        }

        // Current usage for this badge type on this booking
        $existingCount = Badge::where('booking_id', $booking->id)
            ->where('badge_type', $request->badge_type)
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

        // Load global additional badge settings (for admin approval on paid badges)
        $additionalConfig = BadgeConfiguration::where('exhibition_id', $exhibition->id)
            ->where('badge_type', 'Additional')
            ->first();

        // Create badge
        $badge = Badge::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'exhibition_id' => $exhibition->id,
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
    public function bookingLimits($bookingId)
    {
        $booking = Booking::where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->with('exhibition')
            ->findOrFail($bookingId);

        $exhibitionId = $booking->exhibition_id;

        // Get all badge configurations for this exhibition, keyed by badge_type
        $configs = BadgeConfiguration::where('exhibition_id', $exhibitionId)->get()->keyBy('badge_type');

        $badgeTypes = ['Primary', 'Secondary', 'Additional'];
        $data = [];

        foreach ($badgeTypes as $type) {
            $config = $configs->get($type);

            // Skip types that are not configured
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

        return response()->json([
            'success' => true,
            'data' => $data,
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
            ->where('status', '!=', 'approved')
            ->findOrFail($id);

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
