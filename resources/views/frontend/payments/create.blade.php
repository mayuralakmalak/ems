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
    }
    
    .payment-method-card:hover {
        border-color: #6366f1;
        background: #f8fafc;
    }
    
    .payment-method-card.selected {
        border-color: #6366f1;
        background: #f0f9ff;
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
                        // This keeps the visible rows in sync with the amount shown in Total Due (before gateway fee)
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
                    
                    <div class="breakdown-item">
                        <span class="breakdown-label">Payment Gateway Fee</span>
                        <span class="breakdown-value" id="gatewayFee">₹0.00</span>
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
                            <div class="payment-method-card" data-method="wallet">
                                <i class="bi bi-wallet2 payment-method-icon"></i>
                                <div class="payment-method-label">Wallet</div>
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

                <!-- Offline Transfer Instructions -->
                @php
                    $bankDetails = [
                        'account_name' => env('BANK_ACCOUNT_NAME', 'Your Company Name'),
                        'account_number' => env('BANK_ACCOUNT_NUMBER', '0000000000'),
                        'ifsc' => env('BANK_IFSC', 'IFSC000000'),
                        'bank_name' => env('BANK_NAME', 'Your Bank Name'),
                        'branch' => env('BANK_BRANCH', 'Branch'),
                    ];
                @endphp
                <div class="section-card" id="offlineInstructions" style="display: none;">
                    <h5 class="section-title">Bank Transfer Instructions</h5>
                    <p class="section-description">Use these details to complete your NEFT/RTGS transfer. After transferring, you’ll upload proof on the confirmation screen for admin approval.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="booking-summary-item">
                                <div class="summary-label">Account Name</div>
                                <div class="summary-value">{{ $bankDetails['account_name'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="booking-summary-item">
                                <div class="summary-label">Account Number</div>
                                <div class="summary-value">{{ $bankDetails['account_number'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="booking-summary-item">
                                <div class="summary-label">IFSC</div>
                                <div class="summary-value">{{ $bankDetails['ifsc'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="booking-summary-item">
                                <div class="summary-label">Bank</div>
                                <div class="summary-value">{{ $bankDetails['bank_name'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="booking-summary-item">
                                <div class="summary-label">Branch</div>
                                <div class="summary-value">{{ $bankDetails['branch'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3" style="margin-bottom: 0;">
                        <i class="bi bi-info-circle me-2"></i>After you transfer via NEFT/RTGS, continue and upload payment proof on the confirmation screen for admin approval.
                    </div>
                </div>
                
                <!-- Payment Details (online only) -->
                <div class="section-card" id="paymentDetailsCard" style="display: none;">
                    <h5 class="section-title">Payment Details</h5>
                    <p class="section-description">Enter your chosen payment method details.</p>
                    
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
                            <tr class="due-today">
                                <td>Initial Payment</td>
                                <td>₹{{ number_format($initialAmount, 2) }}</td>
                                <td>Today</td>
                            </tr>
                            @php
                                $remainingAmount = $booking->total_amount - $initialAmount;
                                $installmentCount = $booking->exhibition->paymentSchedules->count() - 1;
                                $installmentAmount = $installmentCount > 0 ? $remainingAmount / $installmentCount : 0;
                            @endphp
                            @foreach($booking->exhibition->paymentSchedules->skip(1) as $schedule)
                            <tr>
                                <td>{{ $schedule->part_number }} Installment Payment</td>
                                <td>₹{{ number_format($installmentAmount, 2) }}</td>
                                <td>{{ $schedule->due_date->format('Y.m.d') }}</td>
                            </tr>
                            @endforeach
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
let gatewayFee = 0;
// Base total = Booth Rental + Services (already calculated in PHP, excluding gateway fee)
let baseTotal = {{ $baseTotal }};
let initialAmount = {{ $initialAmount }};

// Payment method selection
document.querySelectorAll('.payment-method-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        selectedMethod = this.getAttribute('data-method');
        document.getElementById('selectedPaymentMethod').value = selectedMethod;
        
        // Show payment details for card/upi/netbanking
        if (['card', 'upi', 'netbanking'].includes(selectedMethod)) {
            document.getElementById('paymentDetailsCard').style.display = 'block';
            // Gateway fee is 2.5% of the base total (Booth Rental + Services)
            gatewayFee = baseTotal * 0.025;
        } else {
            document.getElementById('paymentDetailsCard').style.display = 'none';
            gatewayFee = 0;
        }

        // Offline instructions for NEFT/RTGS
        const offlineBlock = document.getElementById('offlineInstructions');
        if (offlineBlock) {
            offlineBlock.style.display = ['neft', 'rtgs'].includes(selectedMethod) ? 'block' : 'none';
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
        
        updateGatewayFee();
    });
});

function updateGatewayFee() {
    document.getElementById('gatewayFee').textContent = '₹' + gatewayFee.toFixed(2);
    // Total Due = Base Total (Booth Rental + Services) + Gateway Fee
    let totalDue = baseTotal + gatewayFee;
    document.getElementById('totalDueAmount').textContent = '₹' + totalDue.toFixed(2);
    // Payment button shows the initial payment amount
    document.getElementById('paymentButtonAmount').textContent = initialAmount.toFixed(2);
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
    document.getElementById('paymentAmount').value = initialAmount;
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
</script>
@endpush
@endsection
