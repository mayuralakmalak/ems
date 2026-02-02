@extends('layouts.frontend')

@section('title', 'Payment Processing')

@push('styles')
<style>
    .stepper {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .step-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #475569;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .step-pill.active {
        background: #6366f1;
        color: #fff;
        box-shadow: 0 8px 20px rgba(99,102,241,0.25);
    }
    .step-pill .badge {
        background: rgba(255,255,255,0.2);
        color: inherit;
    }
    .payment-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .section-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .section-description {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 20px;
    }
    
    .breakdown-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .breakdown-item:last-child {
        border-bottom: none;
    }
    
    .breakdown-label {
        color: #64748b;
        font-size: 0.95rem;
    }
    
    .breakdown-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .total-due {
        display: flex;
        justify-content: space-between;
        padding: 20px;
        background: #f0f9ff;
        border: 2px solid #0ea5e9;
        border-radius: 8px;
        margin-top: 15px;
    }
    
    .total-due-label {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0ea5e9;
    }
    
    .total-due-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: #0ea5e9;
    }
    
    .booking-summary-item {
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .booking-summary-item:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .summary-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .payment-schedule-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .payment-schedule-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .payment-schedule-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .payment-schedule-table tr:last-child td {
        border-bottom: none;
    }
    
    .due-today {
        background: #f0f9ff;
        color: #0ea5e9;
        font-weight: 600;
    }
    
    .payment-method-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 150px;
    }
    
    .payment-method-card:hover {
        border-color: #6366f1;
        background: #f8fafc;
    }
    
    .payment-method-card.selected {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .payment-method-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        box-shadow: none;
    }
    
    .payment-method-icon {
        font-size: 2.5rem;
        color: #6366f1;
        margin-bottom: 10px;
    }
    
    .payment-method-label {
        font-weight: 500;
        color: #1e293b;
        font-size: 0.95rem;
    }
    
    .wallet-warning {
        display: none;
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 8px;
    }
    
    .wallet-warning.show {
        display: block;
    }
    
    .row.g-3 > .col-md-4 {
        /* display: flex; */
    }
    
    .payment-details-form {
        background: #f8fafc;
        border-radius: 12px;
        padding: 25px;
    }
    
    .form-label {
        font-weight: 500;
        color: #334155;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .form-control {
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 1rem;
    }
    
    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }
    
    .btn-payment {
        width: 100%;
        padding: 15px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1.1rem;
        margin-top: 20px;
    }
    
    .btn-payment:hover {
        background: #4f46e5;
    }
    
    .security-note {
        text-align: center;
        color: #64748b;
        font-size: 0.85rem;
        margin-top: 15px;
    }
    
    .security-note a {
        color: #6366f1;
        text-decoration: none;
    }
    
    .payment-option-card {
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .payment-option-card:hover {
        border-color: #6366f1 !important;
        background: #f0f9ff;
    }
    
    .payment-option-card input[type="radio"]:checked ~ label {
        color: #6366f1;
    }
    
    .payment-option-card:has(input[type="radio"]:checked) {
        border-color: #6366f1 !important;
        background: #f0f9ff;
    }
    
    .payment-option-card .form-check-label {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 60px;
        height: 100%;
    }
    
    .payment-option-card .form-check-input {
        margin-top: 0;
        margin-right: 8px;
    }
    
    /* Ensure both payment option cards have equal height */
    .row.g-2 > .col-md-6 {
        display: flex;
    }
    
    .row.g-2 > .col-md-6 > .payment-option-card {
        min-height: 80px;
    }
</style>
@endpush

@section('content')
@php
    // Calculate booth total from selected_booth_ids (works for both merged and non-merged booths)
    // For merged booths: selected_booth_ids contains the original booths that were merged
    // For non-merged: selected_booth_ids contains the selected booths
    // We always sum the prices from selected_booth_ids to get the correct booth rental total
    $boothEntries = collect($booking->selected_booth_ids ?? []);
    if ($boothEntries->isEmpty() && $booking->booth_id) {
        $boothEntries = collect([['id' => $booking->booth_id]]);
    }
    $boothIds = $boothEntries->map(fn($entry) => is_array($entry) ? ($entry['id'] ?? null) : $entry)
        ->filter()
        ->values();
    $booths = \App\Models\Booth::whereIn('id', $boothIds)->get()->keyBy('id');
    $boothDisplay = $boothEntries->map(function($entry) use ($booths) {
        $isArray = is_array($entry);
        $id = $isArray ? ($entry['id'] ?? null) : $entry;
        $model = $id ? ($booths[$id] ?? null) : null;
        return [
            'name' => $isArray ? ($entry['name'] ?? $model?->name) : ($model?->name),
            'type' => $isArray ? ($entry['type'] ?? null) : ($model?->booth_type),
            'sides' => $isArray ? ($entry['sides'] ?? null) : ($model?->sides_open),
            'price' => $isArray ? ($entry['price'] ?? $model?->price ?? 0) : ($model?->price ?? 0),
        ];
    })->filter(fn($b) => $b['name'] || $b['price']);
    
    // Calculate booth total by summing prices from selected_booth_ids
    // This works correctly for both merged and non-merged booths
    $boothTotal = $boothDisplay->sum(fn($b) => $b['price'] ?? 0);
@endphp
<div class="payment-container">
    {{-- Hidden forms for discount apply/remove to avoid nesting inside payment form --}}
    <form id="discountApplyForm" method="POST" action="{{ route('payments.apply-discount') }}" style="display:none;">
        @csrf
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
        <input type="hidden" name="discount_code" id="discount_code_hidden">
    </form>
    <form id="discountRemoveForm" method="POST" action="{{ route('payments.remove-discount') }}" style="display:none;">
        @csrf
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
    </form>
    <div class="stepper mb-2">
        <span class="step-pill"><span class="badge bg-light text-dark">1</span> Select Booth</span>
        <i class="bi bi-arrow-right text-secondary"></i>
        <span class="step-pill"><span class="badge bg-light text-dark">2</span> Booking Details</span>
        <i class="bi bi-arrow-right text-secondary"></i>
        <span class="step-pill active"><span class="badge bg-light text-dark">3</span> Payment</span>
    </div>
    <form method="POST" action="{{ route('payments.store') }}" id="paymentForm" @if(isset($currentPaymentAlreadySubmitted) && $currentPaymentAlreadySubmitted) onsubmit="return false;" @endif>
        @csrf
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
        @if(isset($specificPayment) && $specificPayment)
            <input type="hidden" name="payment_id" value="{{ $specificPayment->id }}">
        @endif
        <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="">
        <input type="hidden" name="amount" id="paymentAmount" value="{{ $initialAmount }}">
        <input type="hidden" name="payment_type_option" id="paymentTypeOption" value="full">
        
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Payment Information -->
                @if(isset($specificPayment) && $specificPayment)
                <div class="section-card" style="background: #f0f9ff; border: 2px solid #0ea5e9; margin-bottom: 20px;">
                    <h5 class="section-title" style="color: #0ea5e9;">
                        <i class="bi bi-info-circle me-2"></i>Payment Information
                    </h5>
                    <p class="section-description">
                        <strong>Payment Type:</strong> {{ ucfirst($specificPayment->payment_type) }} Payment<br>
                        <strong>Amount Due:</strong> ₹{{ number_format($specificPayment->amount, 2) }}<br>
                        @if($specificPayment->due_date)
                            <strong>Due Date:</strong> {{ $specificPayment->due_date->format('Y-m-d') }}
                            @if($specificPayment->due_date < now())
                                <span class="text-danger">(Overdue)</span>
                            @endif
                        @endif
                    </p>
                </div>
                @endif
                
                <!-- Payment Breakdown -->
                <div class="section-card">
                    <h5 class="section-title">Payment Breakdown</h5>
                    <p class="section-description">Review your booking costs and charges.</p>
                    
                    <div class="breakdown-item">
                        <span class="breakdown-label">Booth Rental</span>
                        <span class="breakdown-value">₹{{ number_format($boothTotal, 2) }}</span>
                    </div>
                    
                    @php
                        // Sum all add-on services attached to the booking
                        $servicesTotal = $booking->bookingServices->sum(function($bs) {
                            return $bs->quantity * $bs->unit_price;
                        });

                        // Sum any additional included item extras stored on the booking
                        $extrasTotal = 0;
                        $extrasRaw = $booking->included_item_extras ?? [];
                        if (is_array($extrasRaw)) {
                            foreach ($extrasRaw as $extra) {
                                // Prefer explicit total_price if present, otherwise qty * unit_price
                                $lineTotal = $extra['total_price'] ?? (
                                    (isset($extra['quantity'], $extra['unit_price']))
                                        ? ((float) $extra['quantity'] * (float) $extra['unit_price'])
                                        : 0
                                );
                                $extrasTotal += $lineTotal;
                            }
                        }
                        
                        // Calculate base total from breakdown items (Booth Rental + Services + Extras)
                        $baseTotal = $boothTotal + $servicesTotal + $extrasTotal;
                        
                        // Booking-level discounts: member and/or coupon
                        $discountType = $booking->discount_type ?? ($booking->discount_percent > 0 ? 'member' : null);
                        $memberDiscountPercent = (float) ($booking->member_discount_percent ?? ($discountType === 'member' || $discountType === 'both' ? $booking->discount_percent : 0));
                        $couponDiscountPercent = (float) ($booking->coupon_discount_percent ?? 0);
                        $originalBase = $booking->discount_percent > 0 ? $booking->total_amount / (1 - ($booking->discount_percent / 100)) : $baseTotal;
                        $memberDiscountAmount = $memberDiscountPercent > 0 ? $originalBase * ($memberDiscountPercent / 100) : 0;
                        $couponDiscountAmount = $couponDiscountPercent > 0 ? $originalBase * ($couponDiscountPercent / 100) : 0;
                        if ($booking->discount_percent > 0) {
                            $baseTotal = $booking->total_amount;
                        }
                    @endphp
                    
                    @if($servicesTotal > 0)
                    <div class="breakdown-item">
                        <span class="breakdown-label">Additional Services</span>
                        <span class="breakdown-value">₹{{ number_format($servicesTotal, 2) }}</span>
                    </div>
                    @endif
                    
                    @if($extrasTotal > 0)
                    <div class="breakdown-item">
                        <span class="breakdown-label">Booth Extras / Furnishing</span>
                        <span class="breakdown-value">₹{{ number_format($extrasTotal, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="breakdown-item" id="discountRowMember" @if($memberDiscountAmount <= 0) style="display:none;" @endif>
                        <span class="breakdown-label">
                            Member Discount
                            (<span id="memberDiscountPercentLabel">{{ number_format($memberDiscountPercent, 2) }}</span>%)
                        </span>
                        <span class="breakdown-value" style="color: #10b981;">
                            -₹<span id="memberDiscountAmountValue">{{ number_format($memberDiscountAmount, 2) }}</span>
                        </span>
                    </div>
                    <div class="breakdown-item" id="discountRowCoupon" @if($couponDiscountAmount <= 0) style="display:none;" @endif>
                        <span class="breakdown-label">
                            Coupon Code Discount
                            (<span id="couponDiscountPercentLabel">{{ number_format($couponDiscountPercent, 2) }}</span>%)
                        </span>
                        <span class="breakdown-value" style="color: #10b981;">
                            -₹<span id="couponDiscountAmountValue">{{ number_format($couponDiscountAmount, 2) }}</span>
                        </span>
                    </div>
                    
                    @php
                        $maximumDiscountApplyPercent = (float) ($booking->exhibition->maximum_discount_apply_percent ?? 100);
                        $fullPaymentDiscountPercentRaw = (float) ($booking->exhibition->full_payment_discount_percent ?? 0);
                        $maxFullPaymentPercent = max(0, $maximumDiscountApplyPercent - $memberDiscountPercent - $couponDiscountPercent);
                        $effectiveFullPaymentPercent = min($fullPaymentDiscountPercentRaw, $maxFullPaymentPercent);
                        $fullPaymentDiscountPercent = $effectiveFullPaymentPercent;
                        $fullPaymentDiscountAmount = ($baseTotal * $fullPaymentDiscountPercent) / 100;
                    @endphp
                    
                    <div class="breakdown-item" id="fullPaymentDiscountItem" style="display: none;">
                        <span class="breakdown-label">Full Payment Discount ({{ number_format($fullPaymentDiscountPercent, 2) }}%)</span>
                        <span class="breakdown-value" style="color: #10b981;" id="fullPaymentDiscountAmount">-₹{{ number_format($fullPaymentDiscountAmount, 2) }}</span>
                    </div>
                    
                    <div class="breakdown-item" id="gatewayFeeItem" style="display: none;">
                        <span class="breakdown-label">Payment Gateway Fee (2.5%) <small class="text-muted">(Card/UPI/Net Banking only)</small></span>
                        <span class="breakdown-value" id="gatewayFeeAmount">₹{{ number_format($totalGatewayFee ?? 0, 2) }}</span>
                    </div>
                    
                    <div class="total-due">
                        <span class="total-due-label">Total Due Amount</span>
                        <span class="total-due-value" id="totalDueAmount">₹{{ number_format($baseTotal, 2) }}</span>
                    </div>
                </div>
                
                @if(!(isset($currentPaymentAlreadySubmitted) && $currentPaymentAlreadySubmitted))
                <!-- Select Payment Method -->
                <div class="section-card">
                    <h5 class="section-title">Select Payment Method</h5>
                    <p class="section-description">Choose how you'd like to pay for your booking.</p>
                    
                    @php
                        // Wallet can only be used if balance covers the initial payment amount
                        $walletBalance = auth()->user()->wallet_balance ?? 0;
                        $canUseWallet = $walletBalance >= $initialAmount;
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="payment-method-card" data-method="card">
                                <i class="bi bi-credit-card payment-method-icon"></i>
                                <div class="payment-method-label">Credit/Debit Card</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method-card" data-method="upi">
                                <i class="bi bi-phone payment-method-icon"></i>
                                <div class="payment-method-label">UPI</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method-card" data-method="netbanking">
                                <i class="bi bi-bank payment-method-icon"></i>
                                <div class="payment-method-label">Net Banking</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method-card {{ $canUseWallet ? '' : 'disabled' }}"
                                 data-method="wallet"
                                 @unless($canUseWallet) data-disabled="1" @endunless>
                                <i class="bi bi-wallet2 payment-method-icon"></i>
                                <div class="payment-method-label">Wallet</div>
                                <small style="color: #64748b;">Balance: ₹{{ number_format($walletBalance, 2) }}</small>
                                @unless($canUseWallet)
                                    <small class="wallet-warning" id="walletWarning">
                                        Wallet balance is lower than the initial payment amount.
                                    </small>
                                @endunless
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method-card" data-method="neft">
                                <i class="bi bi-arrow-left-right payment-method-icon"></i>
                                <div class="payment-method-label">NEFT</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method-card" data-method="rtgs">
                                <i class="bi bi-building payment-method-icon"></i>
                                <div class="payment-method-label">RTGS</div>
                            </div>
                        </div>
                    </div>
                    
                    <small class="text-muted mt-3 d-block">For offline transfers (NEFT/RTGS), submit now and upload proof after you transfer.</small>
                </div>

                <!-- Payment Details (Card) -->
                <div class="section-card" id="cardPaymentDetails" style="display: none;">
                    <h5 class="section-title">Card Payment Details</h5>
                    <p class="section-description">Enter your card details.</p>
                    
                    <div class="payment-details-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name on card</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Card Number</label>
                                <input type="text" class="form-control" placeholder="XXXX XXXX XXXX XXXX" maxlength="19">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" placeholder="XXX" maxlength="3">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Gateway</label>
                            <div style="background: #e2e8f0; padding: 20px; border-radius: 8px; text-align: center; color: #64748b;">
                                <i class="bi bi-shield-check" style="font-size: 2rem;"></i>
                                <p class="mb-0 mt-2">Secure Payment Gateway</p>
                            </div>
                        </div>
                        
                        <div class="security-note">
                            All transactions are secure and encrypted.<br>
                            <a href="#">Terms & Conditions</a> | <a href="#">Privacy Policy</a>
                        </div>
                    </div>
                </div>

                <!-- UPI Payment Details -->
                <div class="section-card" id="upiPaymentDetails" style="display: none;">
                    <h5 class="section-title">UPI Payment</h5>
                    <p class="section-description">Pay using UPI ID or scan QR code.</p>
                    
                    <div class="payment-details-form">
                        @if($upiQrCode && \Storage::disk('public')->exists($upiQrCode))
                            <div class="mb-4 text-center">
                                <label class="form-label d-block mb-3">Scan QR Code to Pay</label>
                                <img src="{{ \Storage::url($upiQrCode) }}" alt="UPI QR Code" style="max-width: 300px; max-height: 300px; border: 2px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                            </div>
                        @endif
                        @if($upiId)
                            <div class="mb-3">
                                <label class="form-label">UPI ID</label>
                                <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; border: 2px solid #0ea5e9;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span style="font-size: 1.1rem; font-weight: 600; color: #0ea5e9;">{{ $upiId }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyUpiId('{{ $upiId }}')">
                                            <i class="bi bi-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Use this UPI ID to make payment from your UPI app</small>
                            </div>
                        @endif
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>After making payment via UPI, your payment will be processed automatically.
                        </div>
                    </div>
                </div>

                <!-- Net Banking Payment Details -->
                <div class="section-card" id="netbankingPaymentDetails" style="display: none;">
                    <h5 class="section-title">Net Banking Details</h5>
                    <p class="section-description">Bank account details for net banking payment.</p>
                    
                    <div class="payment-details-form">
                        @if($bankName || $accountNumber || $ifscCode)
                            <div class="row g-3">
                                @if($bankName)
                                <div class="col-md-6">
                                    <div class="booking-summary-item">
                                        <div class="summary-label">Bank Name</div>
                                        <div class="summary-value">{{ $bankName }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($accountHolder)
                                <div class="col-md-6">
                                    <div class="booking-summary-item">
                                        <div class="summary-label">Account Holder</div>
                                        <div class="summary-value">{{ $accountHolder }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($accountNumber)
                                <div class="col-md-6">
                                    <div class="booking-summary-item">
                                        <div class="summary-label">Account Number</div>
                                        <div class="summary-value">{{ $accountNumber }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($ifscCode)
                                <div class="col-md-6">
                                    <div class="booking-summary-item">
                                        <div class="summary-label">IFSC Code</div>
                                        <div class="summary-value">{{ $ifscCode }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($branch)
                                <div class="col-md-6">
                                    <div class="booking-summary-item">
                                        <div class="summary-label">Branch</div>
                                        <div class="summary-value">{{ $branch }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($branchAddress)
                                <div class="col-md-6">
                                    <div class="booking-summary-item">
                                        <div class="summary-label">Branch Address</div>
                                        <div class="summary-value">{{ $branchAddress }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @endif
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>Use these bank details to complete your net banking payment.
                        </div>
                    </div>
                </div>

                <!-- NEFT/RTGS Bank Transfer Instructions -->
                <div class="section-card" id="offlineInstructions" style="display: none;">
                    <h5 class="section-title">Bank Transfer Instructions</h5>
                    <p class="section-description">Use these details to complete your NEFT/RTGS transfer. After transferring, you'll upload proof on the confirmation screen for admin approval.</p>
                    <div class="row g-3">
                        @if($accountHolder)
                        <div class="col-md-6">
                            <div class="booking-summary-item">
                                <div class="summary-label">Account Holder Name</div>
                                <div class="summary-value">{{ $accountHolder }}</div>
                            </div>
                        </div>
                        @endif
                        @if($accountNumber)
                        <div class="col-md-6">
                            <div class="booking-summary-item">
                                <div class="summary-label">Account Number</div>
                                <div class="summary-value">{{ $accountNumber }}</div>
                            </div>
                        </div>
                        @endif
                        @if($ifscCode)
                        <div class="col-md-4">
                            <div class="booking-summary-item">
                                <div class="summary-label">IFSC Code</div>
                                <div class="summary-value">{{ $ifscCode }}</div>
                            </div>
                        </div>
                        @endif
                        @if($bankName)
                        <div class="col-md-4">
                            <div class="booking-summary-item">
                                <div class="summary-label">Bank Name</div>
                                <div class="summary-value">{{ $bankName }}</div>
                            </div>
                        </div>
                        @endif
                        @if($branch)
                        <div class="col-md-4">
                            <div class="booking-summary-item">
                                <div class="summary-label">Branch</div>
                                <div class="summary-value">{{ $branch }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="alert alert-warning mt-3" style="margin-bottom: 0;">
                        <i class="bi bi-info-circle me-2"></i>After you transfer via NEFT/RTGS, continue and upload payment proof on the confirmation screen for admin approval.
                    </div>
                </div>
                @endif

                <!-- Primary submit button / Already Paid when already submitted -->
                <div class="section-card" style="margin-top: -10px;">
                    @if(isset($currentPaymentAlreadySubmitted) && $currentPaymentAlreadySubmitted)
                    <button type="button" class="btn btn-payment" disabled style="background: #10b981; cursor: not-allowed;">
                        <i class="bi bi-check-circle me-2"></i>Already Paid
                    </button>
                    @if(isset($currentPayment) && $currentPayment)
                    <p class="mt-3 mb-0 text-center">
                        <a href="{{ route('payments.confirmation', $currentPayment->id) }}">View confirmation &amp; receipt</a>
                    </p>
                    @endif
                    @else
                    <button type="submit" class="btn btn-payment" id="makePaymentBtn">
                        <span id="paymentButtonLabel">Make Payment</span> - ₹<span id="paymentButtonAmount">{{ number_format($initialAmount, 2) }}</span>
                    </button>
                    <div class="security-note">
                        Online payments are secure and encrypted. NEFT/RTGS submissions stay pending until proof is approved.
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Booking Summary -->
                <div class="section-card">
                    <h5 class="section-title">Booking Summary</h5>
                    <p class="section-description">Booth details for your exhibition.</p>
                    
                    <div class="booking-summary-item">
                        <div class="summary-label">Exhibition Name</div>
                        <div class="summary-value">{{ $booking->exhibition->name }}</div>
                    </div>
                    <div class="booking-summary-item">
                        <div class="summary-label">Booth Selection</div>
                        <div class="summary-value">
                            @forelse($boothDisplay as $booth)
                                <div style="margin-bottom:6px;">
                                    <strong>{{ $booth['name'] ?? 'N/A' }}</strong>
                                    <div style="font-size:0.9rem; color:#475569;">
                                        {{ (($booth['type'] ?? '—') === 'Orphand' ? 'Shell' : ($booth['type'] ?? '—')) }} / {{ $booth['sides'] ?? '—' }} sides — ₹{{ number_format($booth['price'] ?? 0, 2) }}
                                    </div>
                                </div>
                            @empty
                                N/A
                            @endforelse
                        </div>
                    </div>
                    <div class="booking-summary-item">
                        <div class="summary-label">Booking Date</div>
                        <div class="summary-value">{{ $booking->created_at->format('Y.m.d') }}</div>
                    </div>
                    <div class="booking-summary-item">
                        <div class="summary-label">Exhibition Dates</div>
                        <div class="summary-value">
                            {{ $booking->exhibition->start_date->format('Y.m.d') }} to {{ $booking->exhibition->end_date->format('Y.m.d') }}
                        </div>
                    </div>
                </div>
                
                <!-- Payment Type Selection -->
                <div class="section-card mb-4">
                    <h5 class="section-title">Payment Option</h5>
                    <p class="section-description">Choose your payment method.</p>
                    <div class="row g-2">
                        <div class="col-md-6 d-flex">
                            <div class="form-check payment-option-card" style="border: 2px solid #e2e8f0; border-radius: 8px; padding: 12px; cursor: pointer; width: 100%;" onclick="selectPaymentType('full')">
                                <input class="form-check-input" type="radio" name="payment_type_option" id="paymentTypeFull" value="full" checked>
                                <label class="form-check-label" for="paymentTypeFull" style="cursor: pointer; width: 100%; margin: 0;">
                                    <strong style="display: block; margin-bottom: 4px;">Full Payment</strong>
                                    @if($booking->exhibition->full_payment_discount_percent > 0)
                                        <span class="text-success" style="font-size: 0.9rem;">Get {{ $booking->exhibition->full_payment_discount_percent }}% discount</span>
                                    @else
                                        <span class="text-muted" style="font-size: 0.9rem; visibility: hidden;">Placeholder</span>
                                    @endif
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="form-check payment-option-card" style="border: 2px solid #e2e8f0; border-radius: 8px; padding: 12px; cursor: pointer; width: 100%;" onclick="selectPaymentType('part')">
                                <input class="form-check-input" type="radio" name="payment_type_option" id="paymentTypePart" value="part">
                                <label class="form-check-label" for="paymentTypePart" style="cursor: pointer; width: 100%; margin: 0;">
                                    <strong style="display: block; margin-bottom: 4px;">Part Payment</strong>
                                    <span class="text-muted" style="font-size: 0.9rem;">Pay in installments</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discount Code Section (Full & Part Payment) -->
                <div class="section-card mb-4" id="discountCodeSection">
                    <h5 class="section-title">Apply Discount Code</h5>
                    <p class="section-description">Enter a discount code to save on your booking.</p>

                    <div class="input-group">
                        <input type="text"
                               id="discount_code_visible"
                               class="form-control"
                               placeholder="Enter discount code"
                               value="">
                        <button type="button"
                                id="discountActionButton"
                                class="btn"
                                style="background:#6366f1;border-color:#6366f1;color:#ffffff;white-space:nowrap;"
                                onclick="handleDiscountAction()">
                            <i class="bi bi-tag me-1"></i><span id="discountActionLabel">Apply</span>
                        </button>
                    </div>
                    <div id="discountMessage" class="mt-2 small"></div>
                </div>

                <!-- Full Payment Display -->
                <div class="section-card" id="fullPaymentSection">
                    <h5 class="section-title">Full Payment Details</h5>
                    <p class="section-description">Pay the full amount now and save with discount.</p>
                    @php
                        $maximumDiscountApplyPercentFP = (float) ($booking->exhibition->maximum_discount_apply_percent ?? 100);
                        $memberDiscountPercentFP = (float) ($booking->member_discount_percent ?? 0);
                        $couponDiscountPercentFP = (float) ($booking->coupon_discount_percent ?? 0);
                        $fullPaymentDiscountPercentRawFP = (float) ($booking->exhibition->full_payment_discount_percent ?? 0);
                        $maxFullPaymentPercentFP = max(0, $maximumDiscountApplyPercentFP - $memberDiscountPercentFP - $couponDiscountPercentFP);
                        $effectiveFullPaymentPercentFP = min($fullPaymentDiscountPercentRawFP, $maxFullPaymentPercentFP);
                        $fullPaymentDiscountPercentFP = $effectiveFullPaymentPercentFP;
                        $baseTotalFP = $booking->total_amount;
                        $fullPaymentDiscountAmountFP = ($baseTotalFP * $fullPaymentDiscountPercentFP) / 100;
                        $fullPaymentAmountFP = $baseTotalFP - $fullPaymentDiscountAmountFP;
                        $fullPaymentGatewayChargeFP = ($fullPaymentAmountFP * 2.5) / 100;
                        $fullPaymentTotalFP = $fullPaymentAmountFP + $fullPaymentGatewayChargeFP;
                    @endphp
                    <div class="breakdown-item">
                        <span class="breakdown-label">Total Booking Amount</span>
                        <span class="breakdown-value" id="fullPaymentSectionBaseTotal">₹{{ number_format($baseTotalFP, 2) }}</span>
                    </div>
                    <div class="breakdown-item" id="fullPaymentMemberDiscountRow" style="display: none;">
                        <span class="breakdown-label">Member Discount (<span id="fullPaymentMemberDiscountPct">0</span>%)</span>
                        <span class="breakdown-value" style="color: #10b981;" id="fullPaymentMemberDiscountAmt">-₹0.00</span>
                    </div>
                    <div class="breakdown-item" id="fullPaymentCouponDiscountRow" style="display: none;">
                        <span class="breakdown-label">Coupon Code Discount (<span id="fullPaymentCouponDiscountPct">0</span>%)</span>
                        <span class="breakdown-value" style="color: #10b981;" id="fullPaymentCouponDiscountAmt">-₹0.00</span>
                    </div>
                    <div class="breakdown-item" id="fullPaymentDiscountRow" @if($fullPaymentDiscountPercentFP <= 0) style="display: none;" @endif>
                        <span class="breakdown-label">Full Payment Discount (<span id="fullPaymentDiscountPctLabel">{{ number_format($fullPaymentDiscountPercentFP, 2) }}</span>%)</span>
                        <span class="breakdown-value" style="color: #10b981;" id="fullPaymentSectionDiscountAmt">-₹{{ number_format($fullPaymentDiscountAmountFP, 2) }}</span>
                    </div>
                    <div class="breakdown-item" id="fullPaymentAmountAfterRow" @if($fullPaymentDiscountPercentFP <= 0) style="display: none;" @endif>
                        <span class="breakdown-label">Amount After Discount</span>
                        <span class="breakdown-value" id="fullPaymentSectionAmountAfter">₹{{ number_format($fullPaymentAmountFP, 2) }}</span>
                    </div>
                    <div class="breakdown-item" id="fullPaymentGatewayChargeItem" style="display: none;">
                        <span class="breakdown-label">Gateway Charge (2.5%) <small class="text-muted">(Card/UPI/Net Banking only)</small></span>
                        <span class="breakdown-value" id="fullPaymentGatewayChargeAmount">₹{{ number_format($fullPaymentGatewayChargeFP, 2) }}</span>
                    </div>
                    <div class="total-due">
                        <span class="total-due-label">Total Amount to Pay</span>
                        <span class="total-due-value" id="fullPaymentTotal">₹{{ number_format($fullPaymentAmountFP, 2) }}</span>
                    </div>
                    <input type="hidden" id="fullPaymentAmount" value="{{ $fullPaymentAmountFP }}">
                    <input type="hidden" id="fullPaymentGatewayCharge" value="{{ $fullPaymentGatewayChargeFP }}">
                </div>

                <!-- Payment Schedule -->
                <div class="section-card" id="partPaymentSection" style="display: none;">
                    <h5 class="section-title">Payment Schedule</h5>
                    <p class="section-description">Initial payment and remaining due dates.</p>
                    
                    <table class="payment-schedule-table">
                        <thead>
                            <tr>
                                <th>Payment Type</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Use stored payments from database to ensure amounts don't change when admin updates payment schedule
                                // This preserves the original payment amounts that were set when the booking was created
                                $hasStoredPayments = isset($storedPayments) && $storedPayments->count() > 0;
                            @endphp
                            
                            @if($hasStoredPayments)
                                {{-- Display stored payments from database --}}
                                @php
                                    $installmentNumber = 0;
                                @endphp
                                @foreach($storedPayments as $payment)
                                @php
                                    $installmentNumber++;
                                    $isInitial = $payment->payment_type === 'initial';
                                    $percentage = $paymentPercentages[$payment->id] ?? 0;
                                    
                                    // Determine payment label - All payments are numbered sequentially
                                    // Initial payment is always 1st installment
                                    $ordinal = 'th';
                                    if ($installmentNumber == 1) $ordinal = 'st';
                                    elseif ($installmentNumber == 2) $ordinal = 'nd';
                                    elseif ($installmentNumber == 3) $ordinal = 'rd';
                                    elseif ($installmentNumber == 4) $ordinal = 'th';
                                    elseif ($installmentNumber >= 5) $ordinal = 'th';
                                    
                                    if ($isInitial) {
                                        $paymentLabel = $installmentNumber . $ordinal . ' Installment (Initial Payment)';
                                    } else {
                                        $paymentLabel = $installmentNumber . $ordinal . ' Installment';
                                    }
                                    
                                    // Get gateway fee for this payment
                                    $paymentGatewayFee = $gatewayFeePerPayment[$payment->id] ?? ($payment->gateway_charge ?? 0);
                                    
                                    // Use correct amount from schedule if available, otherwise use stored amount
                                    $displayAmount = $paymentCorrectAmounts[$payment->id] ?? $payment->amount;
                                    $paymentAmountWithFee = $displayAmount + $paymentGatewayFee;
                                    
                                    // Use correct due date from schedule if available
                                    $displayDueDate = $paymentCorrectDueDates[$payment->id] ?? $payment->due_date;
                                @endphp
                                <tr class="{{ $isInitial ? 'due-today' : '' }}" data-payment-id="{{ $payment->id }}" data-base-amount="{{ $displayAmount }}" data-gateway-fee="{{ $paymentGatewayFee }}" data-percentage="{{ $percentage }}">
                                    <td>
                                        <div>{{ $paymentLabel }}</div>
                                        <small class="text-muted">({{ number_format($percentage, 2) }}%)</small>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="payment-amount-base">₹{{ number_format($displayAmount, 2) }}</span>
                                            <span class="payment-gateway-fee" style="display: none;"> + ₹<span class="gateway-fee-value">{{ number_format($paymentGatewayFee, 2) }}</span></span>
                                        </div>
                                        <small class="payment-total-with-fee" style="display: none; color: #6366f1; font-weight: 600;">Total: ₹<span class="total-amount-value">{{ number_format($paymentAmountWithFee, 2) }}</span></small>
                                    </td>
                                    <td>
                                        @if($isInitial)
                                            Today
                                        @else
                                            @if($displayDueDate)
                                                @if($displayDueDate instanceof \Carbon\Carbon || $displayDueDate instanceof \DateTime)
                                                    {{ $displayDueDate->format('Y.m.d') }}
                                                @else
                                                    {{ \Carbon\Carbon::parse($displayDueDate)->format('Y.m.d') }}
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                {{-- Fallback: If no stored payments exist yet, show initial payment only --}}
                                <tr class="due-today">
                                    <td>Initial Payment</td>
                                    <td>
                                        <span class="payment-amount-base">₹{{ number_format($initialAmount, 2) }}</span>
                                        <span class="payment-gateway-fee" style="display: none;"> + ₹<span class="gateway-fee-value">{{ number_format($gatewayCharge ?? 0, 2) }}</span></span>
                                        <br>
                                        <small class="payment-total-with-fee" style="display: none; color: #6366f1; font-weight: 600;">Total: ₹<span class="total-amount-value">{{ number_format($initialAmount + ($gatewayCharge ?? 0), 2) }}</span></small>
                                    </td>
                                    <td>Today</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let selectedMethod = '';
// Global booking context for AJAX operations
let bookingId = {{ $booking->id }};
let csrfToken = '';
try {
    var metaTokenEl = document.querySelector('meta[name="csrf-token"]');
    if (metaTokenEl) {
        csrfToken = metaTokenEl.getAttribute('content') || '';
    }
    if (!csrfToken) {
        var hiddenTokenEl = document.querySelector('#discountApplyForm input[name="_token"]') ||
            document.querySelector('#paymentForm input[name="_token"]');
        if (hiddenTokenEl) {
            csrfToken = hiddenTokenEl.value || '';
        }
    }
} catch (e) {
    csrfToken = '';
}
// Base total = Booth Rental + Services
let baseTotal = {{ $baseTotal }};
let initialAmount = {{ $initialAmount }};
let gatewayCharge = {{ number_format($gatewayCharge ?? 0, 2, '.', '') }}; // Gateway fee for current payment
let totalGatewayFee = {{ number_format($totalGatewayFee ?? 0, 2, '.', '') }}; // Total gateway fee for all payments
// Maximum discount cap from exhibition (member + coupon + full payment <= this)
let maximumDiscountApplyPercent = {{ ($booking->exhibition->maximum_discount_apply_percent ?? null) !== null ? (float)($booking->exhibition->maximum_discount_apply_percent) : 100 }};
let memberDiscountPercentCurrent = {{ $memberDiscountPercent ?? 0 }};
let couponDiscountPercentCurrent = {{ $couponDiscountPercent ?? 0 }};
let fullPaymentDiscountPercentRaw = {{ $booking->exhibition->full_payment_discount_percent ?? 0 }};
// Effective full payment % capped so member + coupon + full payment <= maximum
function getEffectiveFullPaymentPercent() {
    const maxForFull = Math.max(0, maximumDiscountApplyPercent - memberDiscountPercentCurrent - couponDiscountPercentCurrent);
    return Math.min(fullPaymentDiscountPercentRaw, maxForFull);
}
let fullPaymentDiscountPercent = getEffectiveFullPaymentPercent();
let fullPaymentDiscountAmount = (baseTotal * fullPaymentDiscountPercent) / 100;
// Whether a coupon code is currently applied (show "Remove"); member-only discount shows "Apply"
let discountApplied = {{ ($booking->coupon_discount_percent ?? 0) > 0 ? 'true' : 'false' }};

// Store payment percentages for recalculation
let paymentPercentages = @json($paymentPercentages ?? []);
let bookingTotalAmount = {{ $booking->total_amount }};

// Payment method selection
document.querySelectorAll('.payment-method-card').forEach(card => {
    card.addEventListener('click', function() {
        const method = this.getAttribute('data-method');
        const walletWarning = document.getElementById('walletWarning');
        
        // Hide wallet warning when clicking other methods
        if (walletWarning && method !== 'wallet') {
            walletWarning.classList.remove('show');
        }
        
        // Block selection when card is disabled (e.g., wallet with insufficient balance)
        if (this.dataset.disabled === '1') {
            // Show the warning message when trying to select wallet with insufficient balance
            if (method === 'wallet' && walletWarning) {
                walletWarning.classList.add('show');
            }
            alert('Your wallet balance is not enough to pay the initial amount. Please use another payment method.');
            return;
        }
        
        document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        selectedMethod = method;
        document.getElementById('selectedPaymentMethod').value = selectedMethod;
        
        // Hide all payment detail sections
        document.getElementById('cardPaymentDetails').style.display = 'none';
        document.getElementById('upiPaymentDetails').style.display = 'none';
        document.getElementById('netbankingPaymentDetails').style.display = 'none';
        document.getElementById('offlineInstructions').style.display = 'none';
        
        // Show appropriate payment details based on method
        if (selectedMethod === 'card') {
            document.getElementById('cardPaymentDetails').style.display = 'block';
        } else if (selectedMethod === 'upi') {
            document.getElementById('upiPaymentDetails').style.display = 'block';
        } else if (selectedMethod === 'netbanking') {
            document.getElementById('netbankingPaymentDetails').style.display = 'block';
        } else if (['neft', 'rtgs'].includes(selectedMethod)) {
            document.getElementById('offlineInstructions').style.display = 'block';
        }

        // Update Full Payment gateway charge visibility
        const fullPaymentGatewayChargeItem = document.getElementById('fullPaymentGatewayChargeItem');
        const fullPaymentTotal = document.getElementById('fullPaymentTotal');
        const paymentTypeOption = document.getElementById('paymentTypeOption').value;
        
        if (paymentTypeOption === 'full') {
            const isOnlineMethod = ['card', 'upi', 'netbanking'].includes(selectedMethod);
            const fullAmount = parseFloat(document.getElementById('fullPaymentAmount').value);
            const fullGatewayCharge = parseFloat(document.getElementById('fullPaymentGatewayCharge').value);
            
            if (isOnlineMethod && fullGatewayCharge > 0) {
                // Show gateway charge
                if (fullPaymentGatewayChargeItem) {
                    fullPaymentGatewayChargeItem.style.display = 'flex';
                    document.getElementById('fullPaymentGatewayChargeAmount').textContent = '₹' + fullGatewayCharge.toFixed(2);
                }
                // Update total with gateway charge
                if (fullPaymentTotal) {
                    fullPaymentTotal.textContent = '₹' + (fullAmount + fullGatewayCharge).toFixed(2);
                }
            } else {
                // Hide gateway charge
                if (fullPaymentGatewayChargeItem) {
                    fullPaymentGatewayChargeItem.style.display = 'none';
                }
                // Update total without gateway charge
                if (fullPaymentTotal) {
                    fullPaymentTotal.textContent = '₹' + fullAmount.toFixed(2);
                }
            }
        }

        // Button label text
        const buttonLabel = document.getElementById('paymentButtonLabel');
        if (buttonLabel) {
            if (['neft', 'rtgs'].includes(selectedMethod)) {
                buttonLabel.textContent = 'Submit & Proceed';
            } else {
                buttonLabel.textContent = 'Make Payment';
            }
        }
        
        updatePaymentAmount();
    });
});

function updatePaymentAmount() {
    // Gateway fee (2.5%) ONLY applies to: Credit/Debit Card, UPI, and Net Banking
    // Wallet, NEFT, and RTGS do NOT have gateway fees
    const isOnlineMethod = ['card', 'upi', 'netbanking'].includes(selectedMethod);
    const gatewayFeeItem = document.getElementById('gatewayFeeItem');
    const gatewayFeeAmount = document.getElementById('gatewayFeeAmount');
    const totalDueAmount = document.getElementById('totalDueAmount');
    const paymentButtonAmount = document.getElementById('paymentButtonAmount');
    const paymentTypeOption = document.getElementById('paymentTypeOption').value;
    
    // Calculate base amount (with or without full payment discount)
    let calculatedBaseTotal = baseTotal;
    if (paymentTypeOption === 'full' && fullPaymentDiscountPercent > 0) {
        calculatedBaseTotal = baseTotal - fullPaymentDiscountAmount;
    }
    
    // Show/hide gateway fee based on payment method
    if (isOnlineMethod && totalGatewayFee > 0) {
        // For full payment, gateway fee is calculated on discounted amount
        // For part payment, use original totalGatewayFee
        let gatewayFeeToUse = totalGatewayFee;
        if (paymentTypeOption === 'full' && fullPaymentDiscountPercent > 0) {
            // Calculate gateway fee on discounted amount (2.5%)
            gatewayFeeToUse = (calculatedBaseTotal * 2.5) / 100;
        }
        
        // Calculate new total with gateway fee
        const totalWithGatewayFee = calculatedBaseTotal + gatewayFeeToUse;
        
        // Show total gateway fee in breakdown
        gatewayFeeItem.style.display = 'flex';
        gatewayFeeAmount.textContent = '₹' + gatewayFeeToUse.toFixed(2);
        
        // Update total due amount to include gateway fee
        totalDueAmount.textContent = '₹' + totalWithGatewayFee.toFixed(2);
        
        // Recalculate installments as percentages of the NEW total (with gateway fee)
        // Only for part payment - full payment doesn't show installments
        if (paymentTypeOption === 'part') {
            document.querySelectorAll('[data-payment-id]').forEach(row => {
                // Get percentage from data attribute or fallback to paymentPercentages object
                const paymentId = row.getAttribute('data-payment-id');
                const percentageFromData = parseFloat(row.getAttribute('data-percentage')) || 0;
                const percentageFromObject = paymentPercentages[paymentId] || 0;
                const percentage = percentageFromData > 0 ? percentageFromData : percentageFromObject;
                
                // Calculate new installment amount as percentage of totalWithGatewayFee
                const newInstallmentAmount = (totalWithGatewayFee * percentage) / 100;
            
            const baseAmountSpan = row.querySelector('.payment-amount-base');
            const gatewayFeeSpan = row.querySelector('.payment-gateway-fee');
            const totalWithFeeSpan = row.querySelector('.payment-total-with-fee');
            const totalAmountValue = row.querySelector('.total-amount-value');
            
            if (baseAmountSpan) {
                // Update to show the new calculated amount directly (gateway fee already included)
                baseAmountSpan.textContent = '₹' + newInstallmentAmount.toFixed(2);
            }
            
            // Hide the separate gateway fee display since it's already included
            if (gatewayFeeSpan) {
                gatewayFeeSpan.style.display = 'none';
            }
                if (totalWithFeeSpan) {
                    totalWithFeeSpan.style.display = 'none';
                }
            });
            
            // Update fallback payment row if exists
            const fallbackRow = document.querySelector('.due-today:not([data-payment-id])');
            if (fallbackRow) {
                // For fallback, use initial percentage (10% typically)
                const initialPercentage = (initialAmount / bookingTotalAmount) * 100;
                const newInitialAmount = (totalWithGatewayFee * initialPercentage) / 100;
                
                const baseAmountSpan = fallbackRow.querySelector('.payment-amount-base');
                const gatewayFeeSpan = fallbackRow.querySelector('.payment-gateway-fee');
                const totalWithFeeSpan = fallbackRow.querySelector('.payment-total-with-fee');
                
                if (baseAmountSpan) {
                    baseAmountSpan.textContent = '₹' + newInitialAmount.toFixed(2);
                }
                if (gatewayFeeSpan) {
                    gatewayFeeSpan.style.display = 'none';
                }
                if (totalWithFeeSpan) {
                    totalWithFeeSpan.style.display = 'none';
                }
                
                // Update payment button with new initial amount
                paymentButtonAmount.textContent = newInitialAmount.toFixed(2);
            } else {
                // Update payment button with new initial amount (first payment)
                const firstPaymentRow = document.querySelector('[data-payment-id]');
                if (firstPaymentRow) {
                    const firstPaymentId = firstPaymentRow.getAttribute('data-payment-id');
                    const percentageFromData = parseFloat(firstPaymentRow.getAttribute('data-percentage')) || 0;
                    const percentageFromObject = paymentPercentages[firstPaymentId] || 0;
                    const firstPercentage = percentageFromData > 0 ? percentageFromData : percentageFromObject;
                    const newFirstAmount = (totalWithGatewayFee * firstPercentage) / 100;
                    paymentButtonAmount.textContent = newFirstAmount.toFixed(2);
                }
            }
        } else {
            // Full payment - update button with full payment amount
            const fullAmount = parseFloat(document.getElementById('fullPaymentAmount').value);
            const fullGatewayCharge = parseFloat(document.getElementById('fullPaymentGatewayCharge').value);
            const fullPaymentTotal = fullAmount + (isOnlineMethod ? fullGatewayCharge : 0);
            paymentButtonAmount.textContent = fullPaymentTotal.toFixed(2);
            
            // Update Full Payment Details section gateway charge visibility
            const fullPaymentGatewayChargeItem = document.getElementById('fullPaymentGatewayChargeItem');
            const fullPaymentTotalElement = document.getElementById('fullPaymentTotal');
            
            if (isOnlineMethod && fullGatewayCharge > 0) {
                // Show gateway charge
                if (fullPaymentGatewayChargeItem) {
                    fullPaymentGatewayChargeItem.style.display = 'flex';
                    document.getElementById('fullPaymentGatewayChargeAmount').textContent = '₹' + fullGatewayCharge.toFixed(2);
                }
                // Update total with gateway charge
                if (fullPaymentTotalElement) {
                    fullPaymentTotalElement.textContent = '₹' + fullPaymentTotal.toFixed(2);
                }
            } else {
                // Hide gateway charge
                if (fullPaymentGatewayChargeItem) {
                    fullPaymentGatewayChargeItem.style.display = 'none';
                }
                // Update total without gateway charge
                if (fullPaymentTotalElement) {
                    fullPaymentTotalElement.textContent = '₹' + fullAmount.toFixed(2);
                }
            }
        }
    } else {
        // Hide gateway fee
        gatewayFeeItem.style.display = 'none';
        
        // Show base total (with or without full payment discount)
        totalDueAmount.textContent = '₹' + calculatedBaseTotal.toFixed(2);
        
        // Update payment button amount based on payment type
        if (paymentTypeOption === 'full') {
            const fullAmount = parseFloat(document.getElementById('fullPaymentAmount').value);
            const fullGatewayCharge = parseFloat(document.getElementById('fullPaymentGatewayCharge').value);
            const fullPaymentTotal = fullAmount + (isOnlineMethod ? fullGatewayCharge : 0);
            paymentButtonAmount.textContent = fullPaymentTotal.toFixed(2);
            
            // Update Full Payment Details section gateway charge visibility
            const fullPaymentGatewayChargeItem = document.getElementById('fullPaymentGatewayChargeItem');
            const fullPaymentTotalElement = document.getElementById('fullPaymentTotal');
            
            if (isOnlineMethod && fullGatewayCharge > 0) {
                // Show gateway charge
                if (fullPaymentGatewayChargeItem) {
                    fullPaymentGatewayChargeItem.style.display = 'flex';
                    document.getElementById('fullPaymentGatewayChargeAmount').textContent = '₹' + fullGatewayCharge.toFixed(2);
                }
                // Update total with gateway charge
                if (fullPaymentTotalElement) {
                    fullPaymentTotalElement.textContent = '₹' + fullPaymentTotal.toFixed(2);
                }
            } else {
                // Hide gateway charge
                if (fullPaymentGatewayChargeItem) {
                    fullPaymentGatewayChargeItem.style.display = 'none';
                }
                // Update total without gateway charge
                if (fullPaymentTotalElement) {
                    fullPaymentTotalElement.textContent = '₹' + fullAmount.toFixed(2);
                }
            }
        } else {
            paymentButtonAmount.textContent = initialAmount.toFixed(2);
        }
        
        // Reset payment schedule to show original amounts (only for part payment)
        if (paymentTypeOption === 'part') {
            document.querySelectorAll('[data-payment-id]').forEach(row => {
                const baseAmount = parseFloat(row.dataset.baseAmount);
                const baseAmountSpan = row.querySelector('.payment-amount-base');
                if (baseAmountSpan && baseAmount > 0) {
                    baseAmountSpan.textContent = '₹' + baseAmount.toFixed(2);
                }
            });
            
            // Hide gateway fees in payment schedule
            document.querySelectorAll('.payment-gateway-fee, .payment-total-with-fee').forEach(el => {
                el.style.display = 'none';
            });
        }
    }
}

// Form submission
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    if (!selectedMethod) {
        e.preventDefault();
        alert('Please select a payment method');
        return false;
    }
    
    // Map to backend payment methods
    const methodMap = {
        'card': 'online',
        'upi': 'online',
        'netbanking': 'online',
        'wallet': 'wallet',
        'neft': 'neft',
        'rtgs': 'rtgs'
    };
    
    document.getElementById('selectedPaymentMethod').value = methodMap[selectedMethod] || selectedMethod;
    
    // Calculate final payment amount
    // For online methods: gateway fee is already included in the recalculated amount
    // For wallet/neft/rtgs: use original amount without gateway fee
    const isOnlineMethod = ['card', 'upi', 'netbanking'].includes(selectedMethod);
    let finalAmount;
    
    if (isOnlineMethod && totalGatewayFee > 0) {
        // Gateway fee is already included in the recalculated amount shown on button
        // Get the amount from the payment button (which shows the recalculated amount)
        const buttonAmountText = document.getElementById('paymentButtonAmount').textContent;
        finalAmount = parseFloat(buttonAmountText.replace(/[₹,]/g, '')) || initialAmount;
    } else {
        // For wallet, neft, rtgs: use original amount without gateway fee
        finalAmount = parseFloat(initialAmount);
    }
    
    document.getElementById('paymentAmount').value = finalAmount.toFixed(2);
});

// Card number formatting
document.querySelector('input[placeholder="XXXX XXXX XXXX XXXX"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formatted;
});

// Expiry date formatting
document.querySelector('input[placeholder="MM/YY"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});

// Copy UPI ID function
function copyUpiId(upiId) {
    navigator.clipboard.writeText(upiId).then(function() {
        alert('UPI ID copied to clipboard!');
    }, function(err) {
        console.error('Failed to copy UPI ID:', err);
    });
}

// Payment type selection (Full Payment vs Part Payment)
function selectPaymentType(type) {
    document.getElementById('paymentTypeOption').value = type;
    document.getElementById('paymentTypeFull').checked = (type === 'full');
    document.getElementById('paymentTypePart').checked = (type === 'part');
    
    // Discount code section shown for both full and part payment
    const discountCodeSection = document.getElementById('discountCodeSection');
    const discountActionLabel = document.getElementById('discountActionLabel');
    const discountCodeInput = document.getElementById('discount_code_visible');
    if (discountCodeSection) {
        discountCodeSection.style.display = 'block';
    }
    // Configure discount button/label for both full and part (discount code input always enabled)
    if (discountActionLabel) {
        if (discountApplied) {
            discountActionLabel.textContent = 'Remove';
        } else {
            discountActionLabel.textContent = 'Apply';
        }
    }
    if (discountCodeInput) {
        discountCodeInput.disabled = false;
    }
    
    // Update payment breakdown section
    const fullPaymentDiscountItem = document.getElementById('fullPaymentDiscountItem');
    const totalDueAmount = document.getElementById('totalDueAmount');
    
    if (type === 'full') {
        document.getElementById('fullPaymentSection').style.display = 'block';
        document.getElementById('partPaymentSection').style.display = 'none';
        
        // Recalc full payment amounts with maximum discount cap (member + coupon + full payment <= max)
        fullPaymentDiscountPercent = getEffectiveFullPaymentPercent();
        fullPaymentDiscountAmount = (baseTotal * fullPaymentDiscountPercent) / 100;
        const fullAmount = baseTotal - fullPaymentDiscountAmount;
        const fullGatewayCharge = (fullAmount * 2.5) / 100;
        document.getElementById('fullPaymentAmount').value = fullAmount;
        document.getElementById('fullPaymentGatewayCharge').value = fullGatewayCharge;
        const fullPaymentSectionBaseTotal = document.getElementById('fullPaymentSectionBaseTotal');
        const fullPaymentMemberDiscountRow = document.getElementById('fullPaymentMemberDiscountRow');
        const fullPaymentMemberDiscountPct = document.getElementById('fullPaymentMemberDiscountPct');
        const fullPaymentMemberDiscountAmt = document.getElementById('fullPaymentMemberDiscountAmt');
        const fullPaymentCouponDiscountRow = document.getElementById('fullPaymentCouponDiscountRow');
        const fullPaymentCouponDiscountPct = document.getElementById('fullPaymentCouponDiscountPct');
        const fullPaymentCouponDiscountAmt = document.getElementById('fullPaymentCouponDiscountAmt');
        const fullPaymentDiscountRow = document.getElementById('fullPaymentDiscountRow');
        const fullPaymentSectionDiscountAmt = document.getElementById('fullPaymentSectionDiscountAmt');
        const fullPaymentAmountAfterRow = document.getElementById('fullPaymentAmountAfterRow');
        const fullPaymentSectionAmountAfter = document.getElementById('fullPaymentSectionAmountAfter');
        if (fullPaymentSectionBaseTotal) fullPaymentSectionBaseTotal.textContent = '₹' + baseTotal.toFixed(2);
        const memberPctEl = document.getElementById('memberDiscountPercentLabel');
        const memberAmtEl = document.getElementById('memberDiscountAmountValue');
        const couponPctEl = document.getElementById('couponDiscountPercentLabel');
        const couponAmtEl = document.getElementById('couponDiscountAmountValue');
        if (fullPaymentMemberDiscountRow && memberPctEl && memberAmtEl) {
            fullPaymentMemberDiscountRow.style.display = memberAmtEl.textContent !== '0.00' ? 'flex' : 'none';
            if (fullPaymentMemberDiscountPct) fullPaymentMemberDiscountPct.textContent = memberPctEl.textContent;
            if (fullPaymentMemberDiscountAmt) fullPaymentMemberDiscountAmt.textContent = '-₹' + (memberAmtEl.textContent || '0.00');
        }
        if (fullPaymentCouponDiscountRow && couponPctEl && couponAmtEl) {
            fullPaymentCouponDiscountRow.style.display = couponAmtEl.textContent !== '0.00' ? 'flex' : 'none';
            if (fullPaymentCouponDiscountPct) fullPaymentCouponDiscountPct.textContent = couponPctEl.textContent;
            if (fullPaymentCouponDiscountAmt) fullPaymentCouponDiscountAmt.textContent = '-₹' + (couponAmtEl.textContent || '0.00');
        }
        if (fullPaymentDiscountPercent > 0 && fullPaymentDiscountRow) {
            fullPaymentDiscountRow.style.display = 'flex';
            if (fullPaymentSectionDiscountAmt) fullPaymentSectionDiscountAmt.textContent = '-₹' + fullPaymentDiscountAmount.toFixed(2);
            if (fullPaymentAmountAfterRow) fullPaymentAmountAfterRow.style.display = 'flex';
            if (fullPaymentSectionAmountAfter) fullPaymentSectionAmountAfter.textContent = '₹' + fullAmount.toFixed(2);
        }
        if (fullPaymentDiscountItem) {
            fullPaymentDiscountItem.style.display = fullPaymentDiscountPercent > 0 ? 'flex' : 'none';
            document.getElementById('fullPaymentDiscountAmount').textContent = '-₹' + fullPaymentDiscountAmount.toFixed(2);
        }
        
        // Update total amount (baseTotal - discount)
        const discountedTotal = baseTotal - fullPaymentDiscountAmount;
        if (totalDueAmount) {
            totalDueAmount.textContent = '₹' + discountedTotal.toFixed(2);
        }
        
        document.getElementById('paymentAmount').value = fullAmount;
        
        // Update Full Payment Details gateway charge visibility based on current payment method
        const isOnlineMethod = ['card', 'upi', 'netbanking'].includes(selectedMethod);
        const fullPaymentGatewayChargeItem = document.getElementById('fullPaymentGatewayChargeItem');
        const fullPaymentTotalElement = document.getElementById('fullPaymentTotal');
        
        if (isOnlineMethod && fullGatewayCharge > 0) {
            // Show gateway charge
            if (fullPaymentGatewayChargeItem) {
                fullPaymentGatewayChargeItem.style.display = 'flex';
                document.getElementById('fullPaymentGatewayChargeAmount').textContent = '₹' + fullGatewayCharge.toFixed(2);
            }
            // Update total with gateway charge
            if (fullPaymentTotalElement) {
                fullPaymentTotalElement.textContent = '₹' + (fullAmount + fullGatewayCharge).toFixed(2);
            }
        } else {
            // Hide gateway charge
            if (fullPaymentGatewayChargeItem) {
                fullPaymentGatewayChargeItem.style.display = 'none';
            }
            // Update total without gateway charge
            if (fullPaymentTotalElement) {
                fullPaymentTotalElement.textContent = '₹' + fullAmount.toFixed(2);
            }
        }
        
        updatePaymentAmount();
    } else {
        document.getElementById('fullPaymentSection').style.display = 'none';
        document.getElementById('partPaymentSection').style.display = 'block';
        
        // Hide full payment discount in breakdown
        if (fullPaymentDiscountItem) {
            fullPaymentDiscountItem.style.display = 'none';
        }
        
        // Reset total amount to baseTotal (which may already include booking-level discount)
        if (totalDueAmount) {
            totalDueAmount.textContent = '₹' + baseTotal.toFixed(2);
        }
        
        // Reset to initial amount for part payment
        document.getElementById('paymentAmount').value = initialAmount;
        updatePaymentAmount();
    }
}

// Initialize payment amount display on page load
document.addEventListener('DOMContentLoaded', function() {
    // Default to full payment option on initial load; discount code works for both full and part payment
    selectPaymentType('full');
    updatePaymentAmount();
});

// Unified handler for the discount button (apply vs remove)
function handleDiscountAction() {
    // If a discount is already applied, clicking should remove it; otherwise apply
    if (discountApplied) {
        removeDiscountCode();
    } else {
        applyDiscountCode();
    }
}

// Apply discount code (full or part payment)
function applyDiscountCode() {
    const codeInput = document.getElementById('discount_code_visible');
    if (!codeInput) {
        return;
    }
    const code = codeInput.value.trim();
    if (!code) {
        alert('Please enter a discount code.');
        return;
    }
    const messageEl = document.getElementById('discountMessage');

    // Clear previous message
    if (messageEl) {
        messageEl.textContent = '';
        messageEl.className = 'mt-2 small';
    }

    // Perform AJAX request to apply discount without page refresh
    fetch("{{ route('payments.apply-discount') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            booking_id: bookingId,
            discount_code: code,
        }),
    })
    .then(async (response) => {
        const data = await response.json().catch(() => null);

        if (!response.ok || !data) {
            const errorMessage = (data && data.message) ? data.message : 'Failed to apply discount. Please try again.';
            if (messageEl) {
                messageEl.textContent = errorMessage;
                messageEl.className = 'mt-2 small text-danger';
            }
            return;
        }

        // Success - update UI with new totals and payment schedule
        discountApplied = true;

        if (messageEl) {
            messageEl.textContent = data.message || 'Discount code applied successfully!';
            messageEl.className = 'mt-2 small text-success';
        }

        // Update booking totals
        if (typeof data.total_amount === 'number') {
            baseTotal = data.total_amount;
            bookingTotalAmount = data.total_amount;
        }

        // Update discount display rows (Member Discount / Coupon Code Discount)
        const discountRowMember = document.getElementById('discountRowMember');
        const discountRowCoupon = document.getElementById('discountRowCoupon');
        const memberDiscountPercentLabel = document.getElementById('memberDiscountPercentLabel');
        const memberDiscountAmountValue = document.getElementById('memberDiscountAmountValue');
        const couponDiscountPercentLabel = document.getElementById('couponDiscountPercentLabel');
        const couponDiscountAmountValue = document.getElementById('couponDiscountAmountValue');
        const memberPct = typeof data.member_discount_percent === 'number' ? data.member_discount_percent : (data.member_discount_percent != null ? parseFloat(data.member_discount_percent) : 0);
        const couponPct = typeof data.coupon_discount_percent === 'number' ? data.coupon_discount_percent : (data.coupon_discount_percent != null ? parseFloat(data.coupon_discount_percent) : 0);
        const memberAmt = typeof data.member_discount_amount === 'number' ? data.member_discount_amount : (data.member_discount_amount != null ? parseFloat(data.member_discount_amount) : 0);
        const couponAmt = typeof data.coupon_discount_amount === 'number' ? data.coupon_discount_amount : (typeof data.discount_amount === 'number' ? data.discount_amount : (data.coupon_discount_amount != null ? parseFloat(data.coupon_discount_amount) : 0));
        if (discountRowMember) discountRowMember.style.display = memberPct > 0 ? 'flex' : 'none';
        if (discountRowCoupon) discountRowCoupon.style.display = couponPct > 0 ? 'flex' : 'none';
        if (memberDiscountPercentLabel) memberDiscountPercentLabel.textContent = memberPct.toFixed(2);
        if (memberDiscountAmountValue) memberDiscountAmountValue.textContent = memberAmt.toFixed(2);
        if (couponDiscountPercentLabel) couponDiscountPercentLabel.textContent = couponPct.toFixed(2);
        if (couponDiscountAmountValue) couponDiscountAmountValue.textContent = couponAmt.toFixed(2);

        // Update discount button to "Remove" (discount code input stays enabled)
        const discountActionLabel = document.getElementById('discountActionLabel');
        if (discountActionLabel) {
            discountActionLabel.textContent = 'Remove';
        }

        // If full payment selected, update full payment section amounts (with max discount cap)
        const paymentTypeOption = document.getElementById('paymentTypeOption').value;
        if (paymentTypeOption === 'full') {
            memberDiscountPercentCurrent = memberPct;
            couponDiscountPercentCurrent = couponPct;
            fullPaymentDiscountPercent = getEffectiveFullPaymentPercent();
            fullPaymentDiscountAmount = (baseTotal * fullPaymentDiscountPercent) / 100;
            const fullAmount = baseTotal - fullPaymentDiscountAmount;
            const fullGatewayCharge = (fullAmount * 2.5) / 100;
            document.getElementById('fullPaymentAmount').value = fullAmount;
            document.getElementById('fullPaymentGatewayCharge').value = fullGatewayCharge;
            const fullPaymentSectionBaseTotal = document.getElementById('fullPaymentSectionBaseTotal');
            const fullPaymentDiscountRow = document.getElementById('fullPaymentDiscountRow');
            const fullPaymentSectionDiscountAmt = document.getElementById('fullPaymentSectionDiscountAmt');
            const fullPaymentAmountAfterRow = document.getElementById('fullPaymentAmountAfterRow');
            const fullPaymentSectionAmountAfter = document.getElementById('fullPaymentSectionAmountAfter');
            const fullPaymentTotalEl = document.getElementById('fullPaymentTotal');
            if (fullPaymentSectionBaseTotal) fullPaymentSectionBaseTotal.textContent = '₹' + baseTotal.toFixed(2);
            const fullPaymentDiscountPctLabelEl = document.getElementById('fullPaymentDiscountPctLabel');
            if (fullPaymentDiscountPctLabelEl) fullPaymentDiscountPctLabelEl.textContent = fullPaymentDiscountPercent.toFixed(2);
            const fullPaymentMemberDiscountRowApply = document.getElementById('fullPaymentMemberDiscountRow');
            const fullPaymentMemberDiscountPctApply = document.getElementById('fullPaymentMemberDiscountPct');
            const fullPaymentMemberDiscountAmtApply = document.getElementById('fullPaymentMemberDiscountAmt');
            const fullPaymentCouponDiscountRowApply = document.getElementById('fullPaymentCouponDiscountRow');
            const fullPaymentCouponDiscountPctApply = document.getElementById('fullPaymentCouponDiscountPct');
            const fullPaymentCouponDiscountAmtApply = document.getElementById('fullPaymentCouponDiscountAmt');
            if (fullPaymentMemberDiscountRowApply) {
                fullPaymentMemberDiscountRowApply.style.display = memberPct > 0 ? 'flex' : 'none';
                if (fullPaymentMemberDiscountPctApply) fullPaymentMemberDiscountPctApply.textContent = memberPct.toFixed(2);
                if (fullPaymentMemberDiscountAmtApply) fullPaymentMemberDiscountAmtApply.textContent = '-₹' + memberAmt.toFixed(2);
            }
            if (fullPaymentCouponDiscountRowApply) {
                fullPaymentCouponDiscountRowApply.style.display = couponPct > 0 ? 'flex' : 'none';
                if (fullPaymentCouponDiscountPctApply) fullPaymentCouponDiscountPctApply.textContent = couponPct.toFixed(2);
                if (fullPaymentCouponDiscountAmtApply) fullPaymentCouponDiscountAmtApply.textContent = '-₹' + couponAmt.toFixed(2);
            }
            if (fullPaymentDiscountPercent > 0 && fullPaymentDiscountRow) {
                fullPaymentDiscountRow.style.display = 'flex';
                if (fullPaymentSectionDiscountAmt) fullPaymentSectionDiscountAmt.textContent = '-₹' + fullPaymentDiscountAmount.toFixed(2);
                if (fullPaymentAmountAfterRow) fullPaymentAmountAfterRow.style.display = 'flex';
                if (fullPaymentSectionAmountAfter) fullPaymentSectionAmountAfter.textContent = '₹' + fullAmount.toFixed(2);
            }
            if (fullPaymentTotalEl) fullPaymentTotalEl.textContent = '₹' + fullAmount.toFixed(2);
            document.getElementById('paymentAmount').value = fullAmount;
            const isOnlineMethod = ['card', 'upi', 'netbanking'].includes(selectedMethod);
            if (isOnlineMethod && fullGatewayCharge > 0) {
                document.getElementById('fullPaymentGatewayChargeItem').style.display = 'flex';
                document.getElementById('fullPaymentGatewayChargeAmount').textContent = '₹' + fullGatewayCharge.toFixed(2);
                if (fullPaymentTotalEl) fullPaymentTotalEl.textContent = '₹' + (fullAmount + fullGatewayCharge).toFixed(2);
            } else {
                const gwi = document.getElementById('fullPaymentGatewayChargeItem');
                if (gwi) gwi.style.display = 'none';
            }
            updatePaymentAmount();
        }

        // Update part-payment schedule amounts using returned payments
        if (Array.isArray(data.payments)) {
            // Map for quick lookup
            const paymentAmountMap = {};
            data.payments.forEach(p => {
                if (p && typeof p.id !== 'undefined' && typeof p.amount === 'number') {
                    paymentAmountMap[String(p.id)] = p.amount;
                }
            });

            // Update rows
            const rows = document.querySelectorAll('[data-payment-id]');
            let firstPaymentNewAmount = null;
            rows.forEach((row, index) => {
                const paymentId = row.getAttribute('data-payment-id');
                const newAmount = paymentAmountMap[paymentId];
                if (typeof newAmount === 'number') {
                    const baseAmountSpan = row.querySelector('.payment-amount-base');
                    row.dataset.baseAmount = newAmount.toFixed(2);
                    if (baseAmountSpan) {
                        baseAmountSpan.textContent = '₹' + newAmount.toFixed(2);
                    }
                    if (index === 0) {
                        firstPaymentNewAmount = newAmount;
                    }
                }
            });

            // Update initial amount (used for button/hidden input)
            if (firstPaymentNewAmount !== null) {
                initialAmount = firstPaymentNewAmount;
            }
        }

        // Refresh current payment type display (full or part)
        selectPaymentType(document.getElementById('paymentTypeOption').value);
        updatePaymentAmount();
    })
    .catch(() => {
        if (messageEl) {
            messageEl.textContent = 'Failed to apply discount. Please try again.';
            messageEl.className = 'mt-2 small text-danger';
        }
    });
}

// Remove discount code (full or part payment)
function removeDiscountCode() {
    const messageEl = document.getElementById('discountMessage');

    // Clear previous message
    if (messageEl) {
        messageEl.textContent = '';
        messageEl.className = 'mt-2 small';
    }

    // Perform AJAX request to remove discount without page refresh
    fetch("{{ route('payments.remove-discount') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            booking_id: bookingId,
        }),
    })
    .then(async (response) => {
        const data = await response.json().catch(() => null);

        if (!response.ok || !data) {
            const errorMessage = (data && data.message) ? data.message : 'Failed to remove discount. Please try again.';
            if (messageEl) {
                messageEl.textContent = errorMessage;
                messageEl.className = 'mt-2 small text-danger';
            }
            return;
        }

        // Success - coupon removed; member discount may still be present
        const hasCouponLeft = (data.coupon_discount_percent != null && parseFloat(data.coupon_discount_percent) > 0);
        discountApplied = hasCouponLeft;

        if (messageEl) {
            messageEl.textContent = data.message || 'Discount removed successfully.';
            messageEl.className = 'mt-2 small text-success';
        }

        // Update booking totals
        if (typeof data.total_amount === 'number') {
            baseTotal = data.total_amount;
            bookingTotalAmount = data.total_amount;
        }

        // Update discount display rows from API (Member / Coupon)
        const discountRowMemberRemove = document.getElementById('discountRowMember');
        const discountRowCouponRemove = document.getElementById('discountRowCoupon');
        const memberDiscountPercentLabelRemove = document.getElementById('memberDiscountPercentLabel');
        const memberDiscountAmountValueRemove = document.getElementById('memberDiscountAmountValue');
        const couponDiscountPercentLabelRemove = document.getElementById('couponDiscountPercentLabel');
        const couponDiscountAmountValueRemove = document.getElementById('couponDiscountAmountValue');
        const memberPctR = (data.member_discount_percent != null ? parseFloat(data.member_discount_percent) : 0);
        const couponPctR = (data.coupon_discount_percent != null ? parseFloat(data.coupon_discount_percent) : 0);
        const originalTotalForRemove = (memberPctR > 0 && data.total_amount) ? data.total_amount / (1 - (memberPctR / 100)) : 0;
        const memberAmtR = memberPctR > 0 && originalTotalForRemove > 0 ? originalTotalForRemove * (memberPctR / 100) : 0;
        const couponAmtR = 0;
        if (discountRowMemberRemove) discountRowMemberRemove.style.display = memberPctR > 0 ? 'flex' : 'none';
        if (discountRowCouponRemove) discountRowCouponRemove.style.display = couponPctR > 0 ? 'flex' : 'none';
        if (memberDiscountPercentLabelRemove) memberDiscountPercentLabelRemove.textContent = memberPctR.toFixed(2);
        if (memberDiscountAmountValueRemove) memberDiscountAmountValueRemove.textContent = memberAmtR.toFixed(2);
        if (couponDiscountPercentLabelRemove) couponDiscountPercentLabelRemove.textContent = couponPctR.toFixed(2);
        if (couponDiscountAmountValueRemove) couponDiscountAmountValueRemove.textContent = couponAmtR.toFixed(2);

        const fullPaymentMemberDiscountRowRemove = document.getElementById('fullPaymentMemberDiscountRow');
        const fullPaymentCouponDiscountRowRemove = document.getElementById('fullPaymentCouponDiscountRow');
        if (fullPaymentMemberDiscountRowRemove) {
            fullPaymentMemberDiscountRowRemove.style.display = memberPctR > 0 ? 'flex' : 'none';
            const fpMemberPct = document.getElementById('fullPaymentMemberDiscountPct');
            const fpMemberAmt = document.getElementById('fullPaymentMemberDiscountAmt');
            if (fpMemberPct) fpMemberPct.textContent = memberPctR.toFixed(2);
            if (fpMemberAmt) fpMemberAmt.textContent = '-₹' + memberAmtR.toFixed(2);
        }
        if (fullPaymentCouponDiscountRowRemove) {
            fullPaymentCouponDiscountRowRemove.style.display = couponPctR > 0 ? 'flex' : 'none';
            const fpCouponPct = document.getElementById('fullPaymentCouponDiscountPct');
            const fpCouponAmt = document.getElementById('fullPaymentCouponDiscountAmt');
            if (fpCouponPct) fpCouponPct.textContent = couponPctR.toFixed(2);
            if (fpCouponAmt) fpCouponAmt.textContent = '-₹' + couponAmtR.toFixed(2);
        }

        // Update discount button to "Apply" when no coupon, "Remove" when coupon still applied
        const discountActionLabel = document.getElementById('discountActionLabel');
        if (discountActionLabel) {
            discountActionLabel.textContent = hasCouponLeft ? 'Remove' : 'Apply';
        }

        // Clear the discount code input when coupon is removed
        const discountCodeInputToClear = document.getElementById('discount_code_visible');
        if (discountCodeInputToClear) {
            discountCodeInputToClear.value = '';
        }
        const discountCodeHidden = document.getElementById('discount_code_hidden');
        if (discountCodeHidden) {
            discountCodeHidden.value = '';
        }

        // Update part-payment schedule amounts using returned payments
        if (Array.isArray(data.payments)) {
            const paymentAmountMap = {};
            data.payments.forEach(p => {
                if (p && typeof p.id !== 'undefined' && typeof p.amount === 'number') {
                    paymentAmountMap[String(p.id)] = p.amount;
                }
            });

            const rows = document.querySelectorAll('[data-payment-id]');
            let firstPaymentNewAmount = null;
            rows.forEach((row, index) => {
                const paymentId = row.getAttribute('data-payment-id');
                const newAmount = paymentAmountMap[paymentId];
                if (typeof newAmount === 'number') {
                    const baseAmountSpan = row.querySelector('.payment-amount-base');
                    row.dataset.baseAmount = newAmount.toFixed(2);
                    if (baseAmountSpan) {
                        baseAmountSpan.textContent = '₹' + newAmount.toFixed(2);
                    }
                    if (index === 0) {
                        firstPaymentNewAmount = newAmount;
                    }
                }
            });

            if (firstPaymentNewAmount !== null) {
                initialAmount = firstPaymentNewAmount;
            }
        }

        // If full payment selected, update full payment section amounts (with max discount cap)
        const paymentTypeOptionRemove = document.getElementById('paymentTypeOption').value;
        if (paymentTypeOptionRemove === 'full') {
            memberDiscountPercentCurrent = memberPctR;
            couponDiscountPercentCurrent = couponPctR;
            fullPaymentDiscountPercent = getEffectiveFullPaymentPercent();
            fullPaymentDiscountAmount = (baseTotal * fullPaymentDiscountPercent) / 100;
            const fullAmount = baseTotal - fullPaymentDiscountAmount;
            const fullGatewayCharge = (fullAmount * 2.5) / 100;
            document.getElementById('fullPaymentAmount').value = fullAmount;
            document.getElementById('fullPaymentGatewayCharge').value = fullGatewayCharge;
            const fullPaymentSectionBaseTotal = document.getElementById('fullPaymentSectionBaseTotal');
            const fullPaymentDiscountRow = document.getElementById('fullPaymentDiscountRow');
            const fullPaymentSectionDiscountAmt = document.getElementById('fullPaymentSectionDiscountAmt');
            const fullPaymentAmountAfterRow = document.getElementById('fullPaymentAmountAfterRow');
            const fullPaymentSectionAmountAfter = document.getElementById('fullPaymentSectionAmountAfter');
            const fullPaymentTotalEl = document.getElementById('fullPaymentTotal');
            if (fullPaymentSectionBaseTotal) fullPaymentSectionBaseTotal.textContent = '₹' + baseTotal.toFixed(2);
            const fullPaymentDiscountPctLabelRemove = document.getElementById('fullPaymentDiscountPctLabel');
            if (fullPaymentDiscountPctLabelRemove) fullPaymentDiscountPctLabelRemove.textContent = fullPaymentDiscountPercent.toFixed(2);
            if (fullPaymentDiscountPercent > 0 && fullPaymentDiscountRow) {
                fullPaymentDiscountRow.style.display = 'flex';
                if (fullPaymentSectionDiscountAmt) fullPaymentSectionDiscountAmt.textContent = '-₹' + fullPaymentDiscountAmount.toFixed(2);
                if (fullPaymentAmountAfterRow) fullPaymentAmountAfterRow.style.display = 'flex';
                if (fullPaymentSectionAmountAfter) fullPaymentSectionAmountAfter.textContent = '₹' + fullAmount.toFixed(2);
            }
            if (fullPaymentTotalEl) fullPaymentTotalEl.textContent = '₹' + fullAmount.toFixed(2);
            document.getElementById('paymentAmount').value = fullAmount;
            const isOnlineMethod = ['card', 'upi', 'netbanking'].includes(selectedMethod);
            if (isOnlineMethod && fullGatewayCharge > 0) {
                document.getElementById('fullPaymentGatewayChargeItem').style.display = 'flex';
                document.getElementById('fullPaymentGatewayChargeAmount').textContent = '₹' + fullGatewayCharge.toFixed(2);
                if (fullPaymentTotalEl) fullPaymentTotalEl.textContent = '₹' + (fullAmount + fullGatewayCharge).toFixed(2);
            } else {
                const gwi = document.getElementById('fullPaymentGatewayChargeItem');
                if (gwi) gwi.style.display = 'none';
            }
            updatePaymentAmount();
        }

        // Refresh current payment type display
        selectPaymentType(document.getElementById('paymentTypeOption').value);
        updatePaymentAmount();
    })
    .catch(() => {
        if (messageEl) {
            messageEl.textContent = 'Failed to remove discount. Please try again.';
            messageEl.className = 'mt-2 small text-danger';
        }
    });
}
</script>
@endpush
@endsection
