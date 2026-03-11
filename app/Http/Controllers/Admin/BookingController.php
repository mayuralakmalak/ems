<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Document;
use App\Models\Exhibition;
use App\Models\Booth;
use App\Models\BoothRequest;
use App\Models\User;
use App\Models\Service;
use App\Models\Wallet;
use App\Models\Notification;
use App\Models\Badge;
use App\Mail\DocumentStatusMail;
use App\Mail\CancellationProcessedMail;
use App\Mail\PossessionLetterMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;


class BookingController extends Controller
{
    private function computeBookingQuote(Exhibition $exhibition, Booth $booth, User $user, string $boothType, int $sidesOpen, array $servicesPayload, array $includedItemExtrasPayload, bool $applyFullPaymentDiscount): array
    {
        // Base booth price (includes per-booth discount + side-open surcharge) from frontend logic
        $frontendController = app(\App\Http\Controllers\Frontend\BookingController::class);
        $type = $boothType;
        $sides = max(1, min(4, $sidesOpen));
        $reflection = new \ReflectionClass($frontendController);
        $method = $reflection->getMethod('calculateBoothPrice');
        $method->setAccessible(true);
        $boothPrice = (float) $method->invoke($frontendController, $booth, $exhibition, $type, $sides, $user->id);

        $servicesTotal = 0.0;
        $postedServices = [];
        foreach ($servicesPayload as $serviceData) {
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

        $extrasTotal = 0.0;
        $includedItemExtras = [];
        foreach ($includedItemExtrasPayload as $extraData) {
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

        $baseTotal = round($boothPrice + $servicesTotal + $extrasTotal, 2);

        // Sqm + member discounts (same cap logic as frontend booking)
        $maxPercent = $exhibition->maximum_discount_apply_percent !== null
            ? (float) $exhibition->maximum_discount_apply_percent
            : 100.0;

        $totalSqm = (float) ($booth->size_sqft ?? 0);
        $sqmPercentRaw = (float) $exhibition->getSqmDiscountPercentForArea($totalSqm);
        $sqmPercent = $sqmPercentRaw > 0 ? min($sqmPercentRaw, $maxPercent) : 0.0;

        $memberRaw = ($user->is_member && (float) ($exhibition->member_discount_percent ?? 0) > 0)
            ? (float) $exhibition->member_discount_percent
            : 0.0;
        $memberPercent = $memberRaw > 0
            ? min($memberRaw, max(0.0, $maxPercent - $sqmPercent))
            : 0.0;

        $partDiscountPercent = round(min($maxPercent, $sqmPercent + $memberPercent), 2);
        $partTotal = $partDiscountPercent > 0
            ? round($baseTotal * (1 - ($partDiscountPercent / 100)), 2)
            : $baseTotal;

        $fullPaymentPercentRaw = (float) ($exhibition->full_payment_discount_percent ?? 0);
        $fullPaymentEffective = 0.0;
        $fullDiscountPercent = $partDiscountPercent;
        $fullTotal = $partTotal;

        if ($applyFullPaymentDiscount && $fullPaymentPercentRaw > 0) {
            $availableForFull = max(0.0, $maxPercent - $sqmPercent - $memberPercent);
            $fullPaymentEffective = min($fullPaymentPercentRaw, $availableForFull);
            $fullDiscountPercent = round(min($maxPercent, $sqmPercent + $memberPercent + $fullPaymentEffective), 2);
            $fullTotal = round($baseTotal * (1 - ($fullDiscountPercent / 100)), 2);
        }

        return [
            'booth_price' => round($boothPrice, 2),
            'services_total' => round($servicesTotal, 2),
            'extras_total' => round($extrasTotal, 2),
            'base_total' => $baseTotal,
            'sqm_discount_percent' => round($sqmPercent, 2),
            'member_discount_percent' => round($memberPercent, 2),
            'full_payment_discount_percent' => round($fullPaymentEffective, 2),
            'discount_percent_part' => $partDiscountPercent,
            'total_part' => $partTotal,
            'discount_percent_full' => $fullDiscountPercent,
            'total_full' => $fullTotal,
            'posted_services' => $postedServices,
            'included_item_extras' => $includedItemExtras,
        ];
    }

    public function quoteForExhibition(Request $request, Exhibition $exhibition)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);

        $request->validate([
            'exhibitor_mode' => 'required|in:existing,new',
            'user_id' => 'required_if:exhibitor_mode,existing|nullable|exists:users,id',
            'new_exhibitor_email' => 'required_if:exhibitor_mode,new|nullable|email',
            'booth_id' => 'required|exists:booths,id',
            'booth_type' => 'required|in:Raw,Orphand',
            'sides_open' => 'nullable|integer|min:1|max:4',
            'payment_coverage' => 'required|in:none,initial,full',
            'payment_mode' => 'nullable|string|max:50',
            'services' => 'nullable|array',
            'included_item_extras' => 'nullable|array',
        ]);

        $booth = Booth::where('id', $request->booth_id)
            ->where('exhibition_id', $exhibition->id)
            ->firstOrFail();

        // For quote, we need a real user to know is_member and booth-user discount eligibility.
        // If exhibitor_mode=new, we approximate is_member=false and ignore booth-user specific discount.
        if ($request->input('exhibitor_mode') === 'existing') {
            $user = User::findOrFail($request->user_id);
        } else {
            $user = new User([
                'id' => 0,
                'is_member' => false,
            ]);
        }

        $sidesOpen = (int) $request->input('sides_open', $booth->sides_open ?? 1);
        $sidesOpen = max(1, min(4, $sidesOpen));

        $applyFull = $request->input('payment_coverage') === 'full';
        $paymentMode = (string) $request->input('payment_mode', '');
        $isOnline = in_array($paymentMode, ['online', 'upi', 'credit_card', 'net_banking'], true);
        $quote = $this->computeBookingQuote(
            $exhibition,
            $booth,
            $user,
            $request->input('booth_type'),
            $sidesOpen,
            (array) ($request->input('services', [])),
            (array) ($request->input('included_item_extras', [])),
            $applyFull
        );

        // Discounted total corresponding to the current payment selection
        $discountedTotal = $applyFull
            ? (float) $quote['total_full']
            : (float) $quote['total_part'];

        $gateway = $isOnline ? round($discountedTotal * 2.5 / 100, 2) : 0.0;
        $payableNow = round($discountedTotal + $gateway, 2);

        $quote['gateway_charge'] = $gateway;
        $quote['payable_now'] = $payableNow;

        return response()->json(['ok' => true, 'quote' => $quote]);
    }
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('Booking Management - View'), 403);
        $query = Booking::with(['exhibition', 'booth', 'user', 'payments']);

        // Exhibition filter
        if ($request->filled('exhibition_id')) {
            $query->where('exhibition_id', $request->get('exhibition_id'));
        }

        // Status filter (blank or "all" means no filter)
        $status = $request->get('status');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // User filter (by name or email, partial match)
        if ($request->filled('user_name')) {
            $userSearch = $request->get('user_name');
            $query->whereHas('user', function ($q) use ($userSearch) {
                $q->where('name', 'like', '%' . $userSearch . '%')
                    ->orWhere('email', 'like', '%' . $userSearch . '%');
            });
        }

        // Booth number filter (by booth name / number)
        if ($request->filled('booth_number')) {
            $boothNumber = $request->get('booth_number');

            $boothIds = Booth::where('name', 'like', '%' . $boothNumber . '%')
                ->pluck('id')
                ->all();

            if (!empty($boothIds)) {
                $query->where(function ($q) use ($boothIds) {
                    // Match primary booth_id
                    $q->whereIn('booth_id', $boothIds);

                    // Match any ID inside selected_booth_ids JSON (supports both simple and object formats)
                    foreach ($boothIds as $boothId) {
                        $q->orWhereJsonContains('selected_booth_ids', $boothId)
                          ->orWhereJsonContains('selected_booth_ids->id', $boothId);
                    }
                });
            } else {
                // No booths matched the search term, force empty result
                $query->whereRaw('1 = 0');
            }
        }

        $exhibitions = Exhibition::orderBy('name')->get();
        $availableStatuses = ['pending', 'confirmed', 'cancelled', 'replaced'];

        // Export branch: when export=1, return CSV for current filters
        if ($request->get('export') === '1') {
            abort_unless(auth()->user()->can('Booking Management - Download'), 403);
            $bookings = $query->latest()->get();
            return $this->exportBookings($bookings);
        }

        $bookings = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.bookings.index', compact('bookings', 'exhibitions', 'availableStatuses'));
    }

    /**
     * Show admin booking form to book a booth on behalf of an exhibitor for a specific exhibition.
     */
    public function createForExhibition(Exhibition $exhibition, Request $request)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);

        $exhibition->load(['addonServices', 'boothSizes.items']);

        // Load all booths for this exhibition that are currently available (not booked/cancelled)
        $booths = Booth::where('exhibition_id', $exhibition->id)
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        // Basic list of exhibitor users (you can refine this by role if needed)
        $users = User::orderBy('name')->get();

        $preselectedBoothId = $request->query('booth_id');

        // Build add-on services options: global services + exhibition-specific price (matched by name)
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $addonPriceByName = $exhibition->addonServices
            ->keyBy(function ($row) {
                return mb_strtolower(trim((string) $row->item_name));
            });

        $addonServiceOptions = $services->map(function (Service $service) use ($addonPriceByName) {
            $key = mb_strtolower(trim((string) $service->name));
            $addon = $addonPriceByName->get($key);
            $unitPrice = $addon ? (float) ($addon->price_per_quantity ?? 0) : 0.0;
            return [
                'service' => $service,
                'unit_price' => $unitPrice,
            ];
        })->filter(function ($row) {
            // Only show services that have an exhibition-specific price configured (>0)
            return (float) ($row['unit_price'] ?? 0) > 0;
        })->values();

        // Included items per booth size for the create view JS
        $boothSizeItems = [];
        foreach ($exhibition->boothSizes as $size) {
            $boothSizeItems[(string) $size->id] = collect($size->items ?? [])->map(function ($it) {
                return [
                    'id' => $it->id,
                    'name' => $it->item_name,
                    'price' => (float) ($it->price ?? 0),
                ];
            })->values()->all();
        }

        return view('admin.bookings.create', [
            'exhibition' => $exhibition,
            'booths' => $booths,
            'users' => $users,
            'preselectedBoothId' => $preselectedBoothId,
            'boothSizes' => $exhibition->boothSizes,
            'addonServiceOptions' => $addonServiceOptions,
            'boothSizeItems' => $boothSizeItems,
        ]);
    }

    /**
     * Store a new booking created by admin on behalf of an exhibitor.
     */
    public function storeForExhibition(Request $request, Exhibition $exhibition)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);

        $request->validate([
            'exhibitor_mode' => 'required|in:existing,new',
            'user_id' => 'required_if:exhibitor_mode,existing|nullable|exists:users,id',
            'new_exhibitor_name' => 'required_if:exhibitor_mode,new|nullable|string|max:255',
            'new_exhibitor_email' => 'required_if:exhibitor_mode,new|nullable|email|unique:users,email',
            'new_exhibitor_phone' => 'nullable|string|max:50',
            'new_exhibitor_company' => 'nullable|string|max:255',
            'new_exhibitor_password' => 'required_if:exhibitor_mode,new|nullable|string|min:6',
            'booth_id' => 'required|exists:booths,id',
            'booth_type' => 'required|in:Raw,Orphand',
            'sides_open' => 'nullable|integer|min:1|max:4',
            'contact_email' => 'nullable|email',
            'contact_number' => 'nullable|string|max:50',
            'services' => 'nullable|array',
            'services.*.service_id' => 'nullable|exists:services,id',
            'services.*.quantity' => 'nullable|integer|min:0',
            'services.*.unit_price' => 'nullable|numeric|min:0',
            'services.*.name' => 'nullable|string|max:255',
            'included_item_extras' => 'nullable|array',
            'included_item_extras.*.item_id' => 'required_with:included_item_extras|exists:exhibition_booth_size_items,id',
            'included_item_extras.*.quantity' => 'required_with:included_item_extras|integer|min:0',
            'included_item_extras.*.unit_price' => 'required_with:included_item_extras|numeric|min:0',
            'logo' => 'nullable|image|max:5120',
            'payment_coverage' => 'required|in:none,initial,full',
            'payment_mode' => 'required_if:payment_coverage,initial,full|nullable|string|max:50',
        ]);

        // Resolve exhibitor (existing or new)
        if ($request->input('exhibitor_mode') === 'new') {
            $user = User::create([
                'name' => $request->new_exhibitor_name,
                'email' => $request->new_exhibitor_email,
                'phone' => $request->new_exhibitor_phone,
                'company_name' => $request->new_exhibitor_company,
                'is_active' => true,
                'password' => $request->new_exhibitor_password,
            ]);
            // Assign exhibitor role if you have one configured
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('Exhibitor');
                } catch (\Throwable $e) {
                    // ignore if role does not exist
                }
            }
        } else {
            $user = User::findOrFail($request->user_id);
        }

        DB::beginTransaction();
        try {
            $booth = Booth::where('id', $request->booth_id)
                ->where('exhibition_id', $exhibition->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$booth->is_available) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Selected booth is no longer available. Please choose another booth.');
            }

            $sidesOpen = (int) $request->input('sides_open', $booth->sides_open ?? 1);
            $sidesOpen = max(1, min(4, $sidesOpen));

            $applyFull = $request->input('payment_coverage') === 'full';
            $quote = $this->computeBookingQuote(
                $exhibition,
                $booth,
                $user,
                $request->input('booth_type', $booth->booth_type ?? 'Raw'),
                $sidesOpen,
                (array) ($request->input('services', [])),
                (array) ($request->input('included_item_extras', [])),
                $applyFull
            );

            $totalAmount = $applyFull ? (float) $quote['total_full'] : (float) $quote['total_part'];
            $discountPercent = $applyFull ? (float) $quote['discount_percent_full'] : (float) $quote['discount_percent_part'];
            $effectiveSqmPercent = (float) $quote['sqm_discount_percent'];
            $effectiveMemberPercent = (float) $quote['member_discount_percent'];
            $includedItemExtras = $quote['included_item_extras'] ?? [];
            $postedServices = $quote['posted_services'] ?? [];

            $discountType = null;
            if ($applyFull && (float) ($quote['full_payment_discount_percent'] ?? 0) > 0) {
                $discountType = 'full_payment';
            }
            if ($effectiveSqmPercent > 0 && $effectiveMemberPercent > 0) {
                $discountType = $applyFull ? 'sqm_member_full' : 'sqm_member';
            } elseif ($effectiveSqmPercent > 0) {
                $discountType = $applyFull ? 'sqm_full' : 'sqm';
            } elseif ($effectiveMemberPercent > 0) {
                $discountType = $applyFull ? 'member_full' : 'member';
            } elseif ($applyFull) {
                $discountType = 'full_payment';
            }

            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('bookings/logos', 'public');
            }

            $contactEmail = $request->input('contact_email') ?: $user->email;
            $contactNumber = $request->input('contact_number') ?: ($user->phone ?? '');

            $booking = Booking::create([
                'exhibition_id' => $exhibition->id,
                'user_id' => $user->id,
                'channel' => 'admin',
                'created_by_admin_id' => auth()->id(),
                'booth_id' => $booth->id,
                'selected_booth_ids' => [$booth->id],
                'booking_number' => 'BK' . now()->format('YmdHis') . rand(100, 999),
                'status' => 'confirmed',
                'approval_status' => 'approved',
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'discount_percent' => $discountPercent,
                'discount_type' => $discountType,
                'member_discount_percent' => $effectiveMemberPercent > 0 ? round($effectiveMemberPercent, 2) : null,
                'sqm_discount_percent' => $effectiveSqmPercent > 0 ? round($effectiveSqmPercent, 2) : null,
                'coupon_discount_percent' => null,
                'contact_emails' => $contactEmail ? [$contactEmail] : [],
                'contact_numbers' => $contactNumber ? [$contactNumber] : [],
                'included_item_extras' => !empty($includedItemExtras) ? $includedItemExtras : null,
                'logo' => $logoPath,
            ]);

            // Persist add-on services (booking_services)
            if (!empty($postedServices)) {
                foreach ($postedServices as $serviceRow) {
                    $serviceId = $serviceRow['service_id'] ?? null;
                    if (!$serviceId) {
                        continue;
                    }
                    BookingService::create([
                        'booking_id' => $booking->id,
                        'service_id' => $serviceId,
                        'quantity' => $serviceRow['quantity'],
                        'unit_price' => $serviceRow['unit_price'],
                        'total_price' => $serviceRow['total_price'],
                    ]);
                }
            }

            // Reserve booth immediately
            $booth->update([
                'is_available' => false,
            ]);

            // Create a matching booth request entry for tracking
            BoothRequest::create([
                'exhibition_id' => $exhibition->id,
                'user_id' => $user->id,
                'request_type' => 'booking',
                'booth_ids' => [$booth->id],
                'description' => 'Admin booking created for booth #' . ($booth->name ?? $booth->id),
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'request_data' => [
                    'booking_id' => $booking->id,
                    'channel' => 'admin',
                ],
            ]);

            // Create payments
            $paymentCoverage = $request->input('payment_coverage', 'none');
            $paymentMode = $request->input('payment_mode') ?: 'online';
            // Map UI payment mode options to DB enum values
            if (in_array($paymentMode, ['online', 'upi', 'credit_card', 'net_banking'], true)) {
                $paymentMethodEnum = 'online';
            } elseif (in_array($paymentMode, ['cash', 'cheque', 'bank_transfer', 'other'], true)) {
                $paymentMethodEnum = 'offline';
            } else {
                $paymentMethodEnum = 'online';
            }
            $isGatewayOnline = in_array($paymentMode, ['online', 'upi', 'credit_card', 'net_banking'], true);

            if ($paymentCoverage === 'full') {
                // Single completed payment for full amount received
                $gatewayCharge = $isGatewayOnline ? round(((float) $totalAmount * 2.5) / 100, 2) : 0.0;
                \App\Models\Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'payment_number' => 'PM' . now()->format('YmdHis') . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . str_pad(1, 2, '0', STR_PAD_LEFT) . rand(10, 99),
                    'payment_type' => 'full',
                    'payment_method' => $paymentMethodEnum,
                    'status' => 'completed',
                    'approval_status' => 'approved',
                    'amount' => round($totalAmount, 2),
                    'gateway_charge' => $gatewayCharge,
                    'due_date' => now(),
                ]);
                $booking->update(['paid_amount' => round($totalAmount, 2)]);
            } else {
                // Part payments schedule (same logic as frontend store)
                $paymentSchedules = $exhibition->paymentSchedules()->orderBy('part_number', 'asc')->get();

                $gatewayFeePercent = 2.5;
                $totalGatewayFee = ($totalAmount * $gatewayFeePercent) / 100;

                $paymentRecords = [];

                if ($paymentSchedules->isEmpty()) {
                    $initialPercent = $exhibition->initial_payment_percent ?? 10;
                    $initialAmount = ($totalAmount * $initialPercent) / 100;

                    $paymentRecords[] = [
                        'payment_type' => 'initial',
                        'amount' => round($initialAmount, 2),
                        'due_date' => now()->addDays(7),
                    ];
                } else {
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

                $paymentCount = count($paymentRecords);
                if ($paymentCount > 0 && $totalGatewayFee > 0) {
                    $baseFeePerPayment = floor($totalGatewayFee * 100 / $paymentCount) / 100;
                    $remainingFee = $totalGatewayFee - ($baseFeePerPayment * $paymentCount);
                    $remainingFeeCents = round($remainingFee * 100);

                    foreach ($paymentRecords as $index => &$record) {
                        $gatewayCharge = $baseFeePerPayment;
                        if ($remainingFeeCents > 0) {
                            $gatewayCharge += 0.01;
                            $remainingFeeCents--;
                        }
                        $record['gateway_charge'] = round($gatewayCharge, 2);
                    }
                    unset($record);
                } else {
                    foreach ($paymentRecords as &$record) {
                        $record['gateway_charge'] = 0;
                    }
                    unset($record);
                }

                foreach ($paymentRecords as $index => $record) {
                    $paymentType = $record['payment_type'];
                    $partNumber = $paymentSchedules->isEmpty() ? 1 : ($index + 1);

                    $payment = \App\Models\Payment::create([
                        'booking_id' => $booking->id,
                        'user_id' => $user->id,
                        'payment_number' => 'PM' . now()->format('YmdHis') . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . str_pad($partNumber, 2, '0', STR_PAD_LEFT) . rand(10, 99),
                        'payment_type' => $paymentType,
                            'payment_method' => $paymentMethodEnum,
                        'status' => 'pending',
                        'approval_status' => 'pending',
                        'amount' => $record['amount'],
                        'gateway_charge' => $record['gateway_charge'],
                        'due_date' => $record['due_date'],
                    ]);

                    if ($paymentCoverage === 'initial' && $index === 0) {
                        $payment->update([
                            'status' => 'completed',
                            'approval_status' => 'approved',
                            'payment_method' => $paymentMethodEnum,
                            'gateway_charge' => $isGatewayOnline ? (float) $payment->gateway_charge : 0.0,
                        ]);
                        $booking->update(['paid_amount' => (float) $payment->amount]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Booking created successfully on behalf of the exhibitor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    /**
     * Export filtered (or all) bookings as CSV.
     */
    private function exportBookings($bookings)
    {
        $fileName = 'bookings-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($bookings) {
            $handle = fopen('php://output', 'w');

            // CSV header
            fputcsv($handle, [
                'Booking #',
                'Exhibition',
                'User',
                'User Email',
                'Booths',
                'Status',
                'Approval Status',
                'Total Amount',
                'Paid Amount',
                'Created At',
            ]);

            foreach ($bookings as $booking) {
                // Build booth names list (supports multi-booth bookings)
                $boothEntries = collect($booking->selected_booth_ids ?? []);

                if ($boothEntries->isEmpty() && $booking->booth_id) {
                    // Fallback to primary booth if no selected_booth_ids
                    $boothEntries = collect([[
                        'id' => $booking->booth_id,
                        'name' => optional($booking->booth)->name,
                    ]]);
                }

                $boothIds = $boothEntries->map(function ($entry) {
                    return is_array($entry) ? ($entry['id'] ?? null) : $entry;
                })->filter()->values();

                $booths = Booth::whereIn('id', $boothIds)->get()->keyBy('id');

                $boothNames = $boothEntries->map(function ($entry) use ($booths) {
                    $isArray = is_array($entry);
                    $id = $isArray ? ($entry['id'] ?? null) : $entry;
                    $model = $id ? ($booths[$id] ?? null) : null;
                    return $isArray
                        ? ($entry['name'] ?? optional($model)->name ?? 'N/A')
                        : (optional($model)->name ?? 'N/A');
                })->filter(function ($name) {
                    return $name !== 'N/A';
                })->implode(', ');

                fputcsv($handle, [
                    $booking->booking_number,
                    optional($booking->exhibition)->name,
                    optional($booking->user)->name,
                    optional($booking->user)->email,
                    $boothNames,
                    ucfirst($booking->status),
                    $booking->approval_status ? ucfirst($booking->approval_status) : '',
                    $booking->total_amount,
                    $booking->paid_amount,
                    optional($booking->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with(['exhibition', 'booth', 'user'])->findOrFail($id);
        $statuses = ['pending', 'confirmed', 'cancelled', 'replaced'];

        return view('admin.bookings.edit', compact('booking', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with('booth')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,replaced',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0|max:' . $request->total_amount,
        ]);

        $oldStatus = $booking->status;
        $booking->update([
            'status' => $request->status,
            'total_amount' => $request->total_amount,
            'paid_amount' => $request->paid_amount,
        ]);

        // Notify exhibitor if status changed to confirmed or rejected
        if ($oldStatus !== $request->status) {
            if ($request->status === 'confirmed') {
                \App\Models\Notification::create([
                    'user_id' => $booking->user_id,
                    'type' => 'booking',
                    'title' => 'Booking Approved',
                    'message' => 'Your booking request #' . $booking->booking_number . ' has been approved.',
                    'notifiable_type' => \App\Models\Booking::class,
                    'notifiable_id' => $booking->id,
                ]);
            } elseif ($request->status === 'cancelled') {
                \App\Models\Notification::create([
                    'user_id' => $booking->user_id,
                    'type' => 'booking',
                    'title' => 'Booking Cancelled',
                    'message' => 'Your booking #' . $booking->booking_number . ' has been cancelled.',
                    'notifiable_type' => \App\Models\Booking::class,
                    'notifiable_id' => $booking->id,
                ]);
            }
        }

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Booking updated successfully.');
    }

    public function bookedByExhibition($exhibitionId)
    {
        abort_unless(auth()->user()->can('Booking Management - View'), 403);
        $bookings = Booking::with(['exhibition', 'booth', 'user'])
            ->where('exhibition_id', $exhibitionId)
            ->latest()
            ->paginate(20);

        return view('admin.bookings.booked-booths', compact('bookings', 'exhibitionId'));
    }
    
    public function cancellations()
    {
        abort_unless(auth()->user()->can('Booking Management - View'), 403);
        $cancellationRequests = Booking::with(['exhibition', 'booth', 'user', 'payments'])
            ->where('status', 'cancelled')
            ->orWhereNotNull('cancellation_reason')
            ->latest()
            ->get();
        
        // Calculate statistics
        $totalBookings = Booking::count();
        $pendingCancellations = Booking::where('status', 'cancelled')
            ->whereNull('cancellation_type')
            ->count();
        $approvedRefunds = Booking::where('cancellation_type', 'refund')
            ->sum('cancellation_amount');
        $cancellationCharges = Booking::whereNotNull('cancellation_amount')
            ->sum('cancellation_amount');
        
        return view('admin.bookings.cancellations', compact(
            'cancellationRequests',
            'totalBookings',
            'pendingCancellations',
            'approvedRefunds',
            'cancellationCharges'
        ));
    }
    
    public function manageCancellation($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with(['exhibition', 'booth', 'user', 'payments', 'documents'])
            ->findOrFail($id);
        
        // Calculate cancellation charges (15% of total)
        $cancellationCharge = ($booking->total_amount * 15) / 100;
        $refundAmount = $booking->total_amount - $cancellationCharge;
        
        return view('admin.bookings.manage-cancellation', compact('booking', 'cancellationCharge', 'refundAmount'));
    }
    
    public function approveCancellation(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with(['user', 'booth'])->findOrFail($id);
        
        $request->validate([
            'cancellation_type' => 'required|in:refund,wallet_credit',
            'account_details' => 'required_if:cancellation_type,refund|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            $cancellationCharge = ($booking->total_amount * 15) / 100;
            $refundAmount = $booking->total_amount - $cancellationCharge;
            
            $booking->update([
                'cancellation_type' => $request->cancellation_type,
                'cancellation_amount' => $cancellationCharge,
                'account_details' => $request->account_details,
                'status' => 'cancelled',
            ]);
            
            // Process refund or wallet credit
            if ($request->cancellation_type === 'wallet_credit') {
                Wallet::create([
                    'user_id' => $booking->user_id,
                    'balance' => ($booking->user->wallet_balance ?? 0) + $refundAmount,
                    'transaction_type' => 'credit',
                    'amount' => $refundAmount,
                    'reference_type' => 'booking_cancellation',
                    'reference_id' => $booking->id,
                    'description' => 'Cancellation credit for booking #' . $booking->booking_number,
                ]);
            } elseif ($request->cancellation_type === 'refund') {
                // If refunding less than paid, record the retained (discounted) portion as a wallet debit for traceability
                $paidAmount = (float) $booking->paid_amount;
                $discountedPortion = max(0.0, $paidAmount - (float) $refundAmount);
                if ($discountedPortion > 0) {
                    Wallet::create([
                        'user_id' => $booking->user_id,
                        'balance' => ($booking->user->wallet_balance ?? 0) - $discountedPortion,
                        'transaction_type' => 'debit',
                        'amount' => $discountedPortion,
                        'reference_type' => 'booking_cancellation_discount',
                        'reference_id' => $booking->id,
                        'description' => 'Retained discount on refund for booking #' . $booking->booking_number,
                    ]);
                }
            }
            
            // Free up the booth
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.bookings.cancellations')
                ->with('success', 'Cancellation approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve cancellation: ' . $e->getMessage());
        }
    }
    
    public function rejectCancellation(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $booking->update([
            'status' => 'confirmed',
            'cancellation_reason' => null,
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        return redirect()->route('admin.bookings.cancellations')
            ->with('success', 'Cancellation rejected.');
    }

    /**
     * Apply a special admin discount on an existing booking.
     *
     * - If booking is not fully paid: reduce outstanding amount logically (admin will reconcile payments).
     * - If booking is fully paid: record a wallet credit so that the discounted portion can be tracked for future refunds.
     */
    public function applySpecialDiscount(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);

        $booking = Booking::with('user')->findOrFail($id);

        $request->validate([
            'special_discount_type' => 'required|in:fixed,percent',
            'special_discount_value' => 'required|numeric|min:0.01',
            'special_discount_note' => 'nullable|string|max:1000',
        ]);

        $type = $request->input('special_discount_type');
        $value = (float) $request->input('special_discount_value');

        $baseAmount = (float) $booking->total_amount;
        if ($baseAmount <= 0) {
            return back()->with('error', 'Cannot apply special discount on zero amount booking.');
        }

        $discountAmount = $type === 'percent'
            ? round($baseAmount * ($value / 100), 2)
            : round(min($value, $baseAmount), 2);

        if ($discountAmount <= 0) {
            return back()->with('error', 'Calculated special discount amount is zero. Please adjust the value.');
        }

        DB::beginTransaction();
        try {
            $booking->special_discount_type = $type;
            $booking->special_discount_value = $value;
            $booking->special_discount_note = $request->input('special_discount_note');

            // If not fully paid, reduce total_amount but never below paid_amount
            if (!$booking->isFullyPaid()) {
                $currentTotal = (float) $booking->total_amount;
                $currentPaid = (float) $booking->paid_amount;
                $maxReducible = max(0.0, $currentTotal - $currentPaid);
                $effectiveDiscount = min($discountAmount, $maxReducible);

                if ($effectiveDiscount <= 0) {
                    DB::rollBack();
                    return back()->with('error', 'Special discount cannot be applied because it would make paid amount exceed total.');
                }

                $booking->special_discount_amount = $effectiveDiscount;
                $booking->total_amount = round($currentTotal - $effectiveDiscount, 2);
            } else {
                $booking->special_discount_amount = $discountAmount;
                // Fully paid: record wallet credit for this special discount so it can be consumed against future refunds.
                if ($booking->user) {
                    Wallet::create([
                        'user_id' => $booking->user_id,
                        'balance' => ($booking->user->wallet_balance ?? 0) + $discountAmount,
                        'transaction_type' => 'credit',
                        'amount' => $discountAmount,
                        'reference_type' => 'booking_special_discount',
                        'reference_id' => $booking->id,
                        'description' => 'Special discount credited for booking #' . $booking->booking_number,
                    ]);
                }
            }

            $booking->save();

            DB::commit();

            return back()->with('success', 'Special discount applied successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to apply special discount: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->can('Booking Management - View'), 403);
        $booking = Booking::with(['exhibition', 'booth', 'user', 'payments', 'documents', 'badges', 'bookingServices.service', 'additionalServiceRequests.service', 'additionalServiceRequests.approver'])
            ->findOrFail($id);
        
        return view('admin.bookings.show', compact('booking'));
    }

    public function processCancellation(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $booking = Booking::with(['user', 'booth', 'exhibition'])->findOrFail($id);
        
        $request->validate([
            'cancellation_type' => 'required|in:refund,wallet_credit',
            'cancellation_amount' => 'required|numeric|min:0|max:' . $booking->total_amount,
            'account_details' => 'required_if:cancellation_type,refund|nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Update booking
            $booking->update([
                'cancellation_type' => $request->cancellation_type,
                'cancellation_amount' => $request->cancellation_amount, // amount refunded/credited
                'account_details' => $request->account_details,
                'status' => 'cancelled',
            ]);

            // Process refund or wallet credit
            if ($request->cancellation_type === 'wallet_credit') {
                // Credit to wallet
                Wallet::create([
                    'user_id' => $booking->user_id,
                    'balance' => ($booking->user->wallet_balance ?? 0) + $request->cancellation_amount,
                    'transaction_type' => 'credit',
                    'amount' => $request->cancellation_amount,
                    'reference_type' => 'booking_cancellation',
                    'reference_id' => $booking->id,
                    'description' => 'Cancellation credit for booking #' . $booking->booking_number,
                ]);
            }

            // Free up ALL booths associated with this booking
            // 1. Free primary booth
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }

            // 2. Free all booths from selected_booth_ids (for merged/multiple booth bookings)
            $rawSelectedBoothIds = $booking->selected_booth_ids;
            if ($rawSelectedBoothIds) {
                $selectedBoothIds = [];

                // Work on a local array copy to avoid \"Indirect modification\" on casted attributes
                if (is_array($rawSelectedBoothIds)) {
                    // Handle array format: [{'id': 1, 'name': 'B001'}, ...] OR [1,2,3]
                    $firstItem = reset($rawSelectedBoothIds);
                    if (is_array($firstItem) && isset($firstItem['id'])) {
                        // Array of objects format - extract IDs
                        $selectedBoothIds = collect($rawSelectedBoothIds)
                            ->pluck('id')
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();
                    } else {
                        // Simple array format: [1, 2, 3] - use directly
                        $selectedBoothIds = collect($rawSelectedBoothIds)
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();
                    }
                }
                
                // Free all selected booths
                if (!empty($selectedBoothIds)) {
                    \App\Models\Booth::whereIn('id', $selectedBoothIds)
                        ->where('exhibition_id', $booking->exhibition_id)
                        ->update([
                            'is_available' => true,
                            'is_booked' => false,
                        ]);
                }
            }

            DB::commit();

            // Send cancellation processed email to exhibitor
            try {
                if ($booking->user && $booking->user->email) {
                    Mail::to($booking->user->email)->send(new CancellationProcessedMail($booking));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send cancellation processed email: ' . $e->getMessage());
            }

            // Log successful cancellation
            \Log::info('Cancellation processed successfully', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'cancellation_type' => $request->cancellation_type,
                'cancellation_amount' => $request->cancellation_amount,
            ]);

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Cancellation processed successfully. Booth(s) have been freed and made available for new bookings.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to process cancellation', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to process cancellation: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Delete'), 403);
        $booking = Booking::with('booth')->findOrFail($id);

        DB::transaction(function () use ($booking) {
            if ($booking->booth) {
                $booking->booth->update([
                    'is_available' => true,
                    'is_booked' => false,
                ]);
            }
            $booking->delete();
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function approveDocument($documentId)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $document = Document::with(['booking', 'user', 'booking.exhibition', 'requiredDocument'])->findOrFail($documentId);
        $document->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        // Notify exhibitor
        Notification::create([
            'user_id' => $document->user_id,
            'type' => 'document',
            'title' => 'Document Approved',
            'message' => 'Your document "' . $document->name . '" has been approved.',
            'notifiable_type' => Document::class,
            'notifiable_id' => $document->id,
        ]);

        // Send approval email to exhibitor
        try {
            if ($document->user && $document->user->email) {
                Mail::to($document->user->email)->send(
                    new DocumentStatusMail($document, 'approved', null)
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send document approval email: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'user_email' => $document->user->email ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Document approved.');
    }

    public function rejectDocument(Request $request, $documentId)
    {
        abort_unless(auth()->user()->can('Booking Management - Modify'), 403);
        $document = Document::with(['booking', 'user', 'booking.exhibition', 'requiredDocument'])->findOrFail($documentId);

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Notify exhibitor
        Notification::create([
            'user_id' => $document->user_id,
            'type' => 'document',
            'title' => 'Document Rejected',
            'message' => 'Your document "' . $document->name . '" has been rejected. Reason: ' . $request->rejection_reason,
            'notifiable_type' => Document::class,
            'notifiable_id' => $document->id,
        ]);

        // Send rejection email to exhibitor
        try {
            if ($document->user && $document->user->email) {
                Mail::to($document->user->email)->send(
                    new DocumentStatusMail($document, 'rejected', $request->rejection_reason)
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send document rejection email: ' . $e->getMessage(), [
                'document_id' => $document->id,
                'user_email' => $document->user->email ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Document rejected with reason recorded.');
    }

    /**
     * Approve a badge generated by an exhibitor.
     */
    public function approveBadge($badgeId)
    {
        abort_unless(auth()->user()->can('Badge Management - Modify'), 403);
        $badge = Badge::with(['booking', 'user', 'exhibition'])->findOrFail($badgeId);

        $badge->update([
            'status' => 'approved',
        ]);

        // Notify exhibitor that their badge has been approved
        if ($badge->user_id) {
            Notification::create([
                'user_id' => $badge->user_id,
                'type' => 'badge',
                'title' => 'Badge Approved',
                'message' => 'Your badge "' . ($badge->name ?? 'Staff') . '" for booking #' . ($badge->booking->booking_number ?? '') . ' has been approved.',
                'notifiable_type' => Badge::class,
                'notifiable_id' => $badge->id,
            ]);
        }

        return back()->with('success', 'Badge approved successfully.');
    }

    /**
     * Generate and send possession letter to exhibitor
     */
    public function generatePossessionLetter($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Download'), 403);
        $booking = Booking::with([
            'exhibition', 
            'booth', 
            'user', 
            'payments',
            'bookingServices.service'
        ])->findOrFail($id);

        // Check if booking is fully paid
        if (!$booking->isFullyPaid() || !$booking->areAllPaymentsCompleted()) {
            return back()->with('error', 'Cannot generate possession letter. All payments must be completed and approved.');
        }

        // Check if booking is approved
        if ($booking->approval_status !== 'approved' || $booking->status !== 'confirmed') {
            return back()->with('error', 'Cannot generate possession letter. Booking must be approved and confirmed.');
        }

        DB::beginTransaction();
        try {
            // Generate PDF
            $pdfPath = $this->generatePossessionLetterPDF($booking);

            // Mark possession letter as issued
            $booking->update([
                'possession_letter_issued' => true,
            ]);

            // Store the PDF path in documents table
            Document::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'name' => 'Possession Letter - ' . $booking->booking_number,
                'type' => 'Possession Letter',
                'file_path' => $pdfPath,
                'file_size' => Storage::disk('public')->size($pdfPath),
                'status' => 'approved',
            ]);

            // Send email to exhibitor
            try {
                Mail::to($booking->user->email)->send(new PossessionLetterMail($booking, $pdfPath));
                
                // Also send to contact emails if provided
                if ($booking->contact_emails && is_array($booking->contact_emails)) {
                    foreach ($booking->contact_emails as $email) {
                        if ($email && $email !== $booking->user->email) {
                            try {
                                Mail::to($email)->send(new PossessionLetterMail($booking, $pdfPath));
                            } catch (\Exception $e) {
                                Log::error('Failed to send possession letter to contact email: ' . $email, [
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send possession letter email: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'user_email' => $booking->user->email,
                ]);
            }

            // Notify exhibitor
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking',
                'title' => 'Possession Letter Generated',
                'message' => 'Your possession letter for booking #' . $booking->booking_number . ' has been generated and sent to your email.',
                'notifiable_type' => Booking::class,
                'notifiable_id' => $booking->id,
            ]);

            DB::commit();

            return back()->with('success', 'Possession letter generated and sent to exhibitor successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate possession letter: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to generate possession letter: ' . $e->getMessage());
        }
    }

    /**
     * Generate possession letter PDF
     */
    private function generatePossessionLetterPDF(Booking $booking)
    {
        $html = view('admin.bookings.possession-letter-pdf', compact('booking'))->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf-temp'),
            'format' => 'A4',
            'margin_top' => 20,
            'margin_right' => 15,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'fontDir' => array_merge($fontDirs, [
                resource_path('fonts'),
            ]),
            'fontdata' => $fontData,
            'default_font' => 'dejavusans',
        ]);

        $mpdf->SetTitle('Possession Letter - ' . $booking->booking_number);
        $mpdf->WriteHTML($html);

        // Save PDF to storage
        $filename = 'possession-letters/booking_' . $booking->id . '_' . $booking->booking_number . '_' . now()->format('YmdHis') . '.pdf';
        $pdfContent = $mpdf->Output('', 'S');
        
        Storage::disk('public')->put($filename, $pdfContent);

        return $filename;
    }

    /**
     * Download possession letter (Admin)
     */
    public function downloadPossessionLetter($id)
    {
        abort_unless(auth()->user()->can('Booking Management - Download'), 403);
        $booking = Booking::with(['exhibition', 'booth', 'user'])->findOrFail($id);

        if (!$booking->possession_letter_issued) {
            return back()->with('error', 'Possession letter has not been generated yet.');
        }

        // Find the possession letter document
        $document = Document::where('booking_id', $booking->id)
            ->where('type', 'Possession Letter')
            ->latest()
            ->first();

        if (!$document || !Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Possession letter file not found.');
        }

        return Storage::disk('public')->download($document->file_path, 'Possession_Letter_' . $booking->booking_number . '.pdf');
    }
}

