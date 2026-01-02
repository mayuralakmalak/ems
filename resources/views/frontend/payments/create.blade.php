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
    <div class="stepper mb-2">
        <span class="step-pill"><span class="badge bg-light text-dark">1</span> Select Booth</span>
        <i class="bi bi-arrow-right text-secondary"></i>
        <span class="step-pill"><span class="badge bg-light text-dark">2</span> Booking Details</span>
        <i class="bi bi-arrow-right text-secondary"></i>
        <span class="step-pill active"><span class="badge bg-light text-dark">3</span> Payment</span>
    </div>
    <form method="POST" action="{{ route('payments.store') }}" id="paymentForm">
        @csrf
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
        @if(isset($specificPayment) && $specificPayment)
            <input type="hidden" name="payment_id" value="{{ $specificPayment->id }}">
        @endif
        <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="">
        <input type="hidden" name="amount" id="paymentAmount" value="{{ $initialAmount }}">
        
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
                        
                        // Apply discount if any
                        $discountAmount = 0;
                        if ($booking->discount_percent > 0) {
                            $discountAmount = ($baseTotal * $booking->discount_percent) / 100;
                            $baseTotal -= $discountAmount;
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
                    
                    @if($discountAmount > 0)
                    <div class="breakdown-item">
                        <span class="breakdown-label">Discount</span>
                        <span class="breakdown-value" style="color: #10b981;">-₹{{ number_format($discountAmount, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="breakdown-item" id="gatewayFeeItem" style="display: none;">
                        <span class="breakdown-label">Payment Gateway Fee (2.5%) <small class="text-muted">(Card/UPI/Net Banking only)</small></span>
                        <span class="breakdown-value" id="gatewayFeeAmount">₹{{ number_format($totalGatewayFee ?? 0, 2) }}</span>
                    </div>
                    
                    <div class="total-due">
                        <span class="total-due-label">Total Due Amount</span>
                        <span class="total-due-value" id="totalDueAmount">₹{{ number_format($baseTotal, 2) }}</span>
                    </div>
                </div>
                
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

                <!-- Primary submit button (always visible) -->
                <div class="section-card" style="margin-top: -10px;">
                    <button type="submit" class="btn btn-payment" id="makePaymentBtn">
                        <span id="paymentButtonLabel">Make Payment</span> - ₹<span id="paymentButtonAmount">{{ number_format($initialAmount, 2) }}</span>
                    </button>
                    <div class="security-note">
                        Online payments are secure and encrypted. NEFT/RTGS submissions stay pending until proof is approved.
                    </div>
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
                                        {{ $booth['type'] ?? '—' }} / {{ $booth['sides'] ?? '—' }} sides — ₹{{ number_format($booth['price'] ?? 0, 2) }}
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
                
                <!-- Payment Schedule -->
                <div class="section-card">
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
                                <tr class="{{ $isInitial ? 'due-today' : '' }}" data-payment-id="{{ $payment->id }}" data-base-amount="{{ $displayAmount }}" data-gateway-fee="{{ $paymentGatewayFee }}">
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
// Base total = Booth Rental + Services
let baseTotal = {{ $baseTotal }};
let initialAmount = {{ $initialAmount }};
let gatewayCharge = {{ number_format($gatewayCharge ?? 0, 2, '.', '') }}; // Gateway fee for current payment
let totalGatewayFee = {{ number_format($totalGatewayFee ?? 0, 2, '.', '') }}; // Total gateway fee for all payments

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
    
    // Show/hide gateway fee based on payment method
    if (isOnlineMethod && totalGatewayFee > 0) {
        // Show total gateway fee in breakdown
        gatewayFeeItem.style.display = 'flex';
        gatewayFeeAmount.textContent = '₹' + totalGatewayFee.toFixed(2);
        
        // Update total due amount to include gateway fee (baseTotal + totalGatewayFee)
        const totalWithGatewayFee = baseTotal + totalGatewayFee;
        totalDueAmount.textContent = '₹' + totalWithGatewayFee.toFixed(2);
        
        // Update payment button with current payment + gateway fee for this specific payment
        const paymentWithFee = initialAmount + gatewayCharge;
        paymentButtonAmount.textContent = paymentWithFee.toFixed(2);
        
        // Update payment schedule to show gateway fees
        document.querySelectorAll('[data-payment-id]').forEach(row => {
            const baseAmount = parseFloat(row.dataset.baseAmount);
            const fee = parseFloat(row.dataset.gatewayFee);
            const gatewayFeeSpan = row.querySelector('.payment-gateway-fee');
            const totalWithFeeSpan = row.querySelector('.payment-total-with-fee');
            const gatewayFeeValue = row.querySelector('.gateway-fee-value');
            const totalAmountValue = row.querySelector('.total-amount-value');
            
            if (gatewayFeeSpan && totalWithFeeSpan && gatewayFeeValue && totalAmountValue) {
                gatewayFeeSpan.style.display = 'inline';
                totalWithFeeSpan.style.display = 'block';
                gatewayFeeValue.textContent = fee.toFixed(2);
                totalAmountValue.textContent = (baseAmount + fee).toFixed(2);
            }
        });
        
        // Update fallback payment row if exists
        const fallbackRow = document.querySelector('.due-today:not([data-payment-id])');
        if (fallbackRow) {
            const gatewayFeeSpan = fallbackRow.querySelector('.payment-gateway-fee');
            const totalWithFeeSpan = fallbackRow.querySelector('.payment-total-with-fee');
            const gatewayFeeValue = fallbackRow.querySelector('.gateway-fee-value');
            const totalAmountValue = fallbackRow.querySelector('.total-amount-value');
            
            if (gatewayFeeSpan && totalWithFeeSpan && gatewayFeeValue && totalAmountValue) {
                gatewayFeeSpan.style.display = 'inline';
                totalWithFeeSpan.style.display = 'block';
                gatewayFeeValue.textContent = gatewayCharge.toFixed(2);
                totalAmountValue.textContent = (initialAmount + gatewayCharge).toFixed(2);
            }
        }
    } else {
        // Hide gateway fee
        gatewayFeeItem.style.display = 'none';
        
        // Show base total without gateway fee
        totalDueAmount.textContent = '₹' + baseTotal.toFixed(2);
        paymentButtonAmount.textContent = initialAmount.toFixed(2);
        
        // Hide gateway fees in payment schedule
        document.querySelectorAll('.payment-gateway-fee, .payment-total-with-fee').forEach(el => {
            el.style.display = 'none';
        });
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
    
    // Calculate final payment amount (include gateway fee ONLY for card/upi/netbanking)
    // Gateway fee does NOT apply to wallet, neft, or rtgs
    const isOnlineMethod = ['card', 'upi', 'netbanking'].includes(selectedMethod);
    const finalAmount = isOnlineMethod && gatewayCharge > 0 
        ? (parseFloat(initialAmount) + parseFloat(gatewayCharge))
        : parseFloat(initialAmount);
    
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

// Initialize payment amount display on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePaymentAmount();
});
</script>
@endpush
@endsection
