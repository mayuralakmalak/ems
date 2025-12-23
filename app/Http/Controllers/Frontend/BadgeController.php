<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Booking;
use App\Models\Exhibition;
use App\Models\BadgeConfiguration;
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
            'valid_for_date' => 'nullable|date',
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

        // Check quantity limits
        $existingCount = Badge::where('booking_id', $booking->id)
            ->where('badge_type', $request->badge_type)
            ->count();

        if ($existingCount >= $badgeConfig->quantity) {
            return back()->with('error', 'Maximum ' . $badgeConfig->quantity . ' ' . $request->badge_type . ' badges allowed.');
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('badges/photos', 'public');
        }

        // Calculate price
        $price = 0;
        $isPaid = false;
        if ($badgeConfig->pricing_type === 'Paid') {
            $price = $badgeConfig->price;
            $isPaid = true;
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
            'status' => $badgeConfig->needs_admin_approval ? 'pending' : 'approved',
            'is_paid' => $isPaid,
            'price' => $price,
            'access_permissions' => $request->access_permissions ?? [],
            'valid_for_date' => $request->valid_for_date,
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

        // If paid and needs payment, process payment
        if ($isPaid && $price > 0) {
            // Check if wallet payment or needs online payment
            // For now, mark as pending payment
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
            'valid_for_date' => 'nullable|date',
            'access_permissions' => 'nullable|array',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'valid_for_date' => $request->valid_for_date,
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
            ->where('status', 'approved')
            ->findOrFail($id);

        // Generate PDF or return badge view for download
        return view('frontend.badges.download', compact('badge'));
    }
}
