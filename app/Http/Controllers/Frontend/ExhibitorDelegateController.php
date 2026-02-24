<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExhibitorDelegateController extends Controller
{
    public function index($bookingId)
    {
        $booking = Booking::where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->with('exhibition')
            ->findOrFail($bookingId);

        $exhibition = $booking->exhibition;
        $freeLimit = (int) ($exhibition->delegate_free_count ?? 0);
        $paidFee = (float) ($exhibition->delegate_additional_fee ?? 0);

        $delegates = EventRegistration::where('booking_id', $booking->id)
            ->where('type', 'delegate')
            ->orderBy('created_at', 'asc')
            ->get();

        $totalDelegates = $delegates->count();
        $freeUsed = min($totalDelegates, $freeLimit);
        $paidUsed = max(0, $totalDelegates - $freeLimit);

        return view('frontend.delegates.index', compact(
            'booking',
            'exhibition',
            'delegates',
            'freeLimit',
            'paidFee',
            'totalDelegates',
            'freeUsed',
            'paidUsed'
        ));
    }

    public function store(Request $request, $bookingId)
    {
        $booking = Booking::where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->with('exhibition')
            ->findOrFail($bookingId);

        $exhibition = $booking->exhibition;

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'id_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'company' => 'nullable|string|max:200',
            'designation' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $currentCount = EventRegistration::where('booking_id', $booking->id)
            ->where('type', 'delegate')
            ->count();

        $freeLimit = (int) ($exhibition->delegate_free_count ?? 0);
        $additionalFee = (float) ($exhibition->delegate_additional_fee ?? 0);

        $isFree = $currentCount < $freeLimit;
        $feeAmount = $isFree ? 0.0 : $additionalFee;
        $feeTier = $isFree ? 'free' : 'paid';

        $idProofPath = $request->file('id_proof')->store('event-registration-id-proofs', 'public');

        DB::beginTransaction();
        try {
            $registration = EventRegistration::create([
                'exhibition_id' => $exhibition->id,
                'booking_id' => $booking->id,
                'type' => 'delegate',
                'registration_number' => EventRegistration::generateRegistrationNumber(),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'id_proof_file' => $idProofPath,
                'company' => $validated['company'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
                'fee_amount' => $feeAmount,
                'fee_tier' => $feeTier,
                'paid_amount' => 0,
                'approval_status' => 'pending',
                'payment_status' => $feeAmount > 0 ? 'pending' : 'paid',
                'token' => EventRegistration::generateToken(),
            ]);

            DB::commit();

            if ($feeAmount > 0) {
                return redirect()
                    ->route('register.payment', ['token' => $registration->token])
                    ->with('success', 'Delegate added successfully. Please complete payment for additional delegate.');
            }

            return redirect()
                ->route('bookings.delegates.index', $booking->id)
                ->with('success', 'Free delegate added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add delegate: ' . $e->getMessage())->withInput();
        }
    }
}

