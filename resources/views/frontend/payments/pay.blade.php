@extends('layouts.frontend')

@section('title', 'Make Payment')

@push('styles')
<style>
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
    
    .payment-info-card {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 25px;
    }
    
    .payment-info-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 20px;
    }
    
    .payment-info-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    
    .payment-info-item:last-child {
        border-bottom: none;
    }
    
    .payment-info-label {
        font-size: 0.95rem;
        opacity: 0.9;
    }
    
    .payment-info-value {
        font-size: 1.1rem;
        font-weight: 600;
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
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99,102,241,0.15);
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
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
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
    
    .summary-item {
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .summary-item:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        color: #64748b;
        font-size: 0.9rem;
    }
    
    .summary-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }
    
    .payment-schedule-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    
    .payment-schedule-table th,
    .payment-schedule-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .payment-schedule-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #1e293b;
    }
    
    .payment-schedule-table tr:last-child td {
        border-bottom: none;
    }
    
    .current-payment-row {
        background: #f0f9ff;
        font-weight: 600;
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
    
    .security-note {
        text-align: center;
        color: #64748b;
        font-size: 0.85rem;
        margin-top: 15px;
    }
</style>
@endpush

@section('content')
<div class="payment-container">
    <div class="payment-info-card">
        <div class="payment-info-title">
            <i class="bi bi-credit-card me-2"></i>Payment Details
        </div>
        <div class="payment-info-item">
            <span class="payment-info-label">Payment Number</span>
            <span class="payment-info-value">{{ $payment->payment_number }}</span>
        </div>
        <div class="payment-info-item">
            <span class="payment-info-label">Payment Type</span>
            <span class="payment-info-value">{{ ucfirst($payment->payment_type) }} Payment</span>
        </div>
        <div class="payment-info-item">
            <span class="payment-info-label">Amount Due</span>
            <span class="payment-info-value">₹{{ number_format($payment->amount, 2) }}</span>
        </div>
        @if($payment->due_date)
        <div class="payment-info-item">
            <span class="payment-info-label">Due Date</span>
            <span class="payment-info-value">
                {{ $payment->due_date->format('Y-m-d') }}
                @if($payment->due_date < now())
                    <span style="color: #fef3c7; margin-left: 10px;">(Overdue)</span>
                @endif
            </span>
        </div>
        @endif
        <div class="payment-info-item">
            <span class="payment-info-label">Exhibition</span>
            <span class="payment-info-value">{{ $payment->booking->exhibition->name }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('payments.store') }}" id="paymentForm">
        @csrf
        <input type="hidden" name="booking_id" value="{{ $payment->booking_id }}">
        <input type="hidden" name="payment_id" value="{{ $payment->id }}">
        <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="">
        <input type="hidden" name="amount" id="paymentAmount" value="{{ $payment->amount }}">
        
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Payment Breakdown -->
                <div class="section-card">
                    <h5 class="section-title">Payment Breakdown</h5>
                    <p class="section-description">Details for this payment installment.</p>
                    
                    <div class="breakdown-item">
                        <span class="breakdown-label">Payment Amount</span>
                        <span class="breakdown-value">₹{{ number_format($payment->amount, 2) }}</span>
                    </div>
                    
                    <div class="total-due">
                        <span class="total-due-label">Total Amount to Pay</span>
                        <span class="total-due-value" id="totalDueAmount">₹{{ number_format($payment->amount, 2) }}</span>
                    </div>
                </div>
                
                <!-- Select Payment Method -->
                <div class="section-card">
                    <h5 class="section-title">Select Payment Method</h5>
                    <p class="section-description">Choose how you'd like to pay for this installment.</p>
                    
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
                        @php
                            $canUseWallet = $walletBalance >= $payment->amount;
                        @endphp
                        <div class="col-md-4">
                            <div class="payment-method-card {{ $canUseWallet ? '' : 'disabled' }}"
                                 data-method="wallet"
                                 @unless($canUseWallet) data-disabled="1" @endunless>
                                <i class="bi bi-wallet2 payment-method-icon"></i>
                                <div class="payment-method-label">Wallet</div>
                                <small style="color: #64748b;">Balance: ₹{{ number_format($walletBalance, 2) }}</small>
                                @unless($canUseWallet)
                                    <small class="d-block mt-1" style="color: #ef4444; font-size: 0.8rem;">
                                        Wallet balance is lower than this payment amount.
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

                <!-- NEFT/RTGS Bank Transfer Instructions -->
                <div class="section-card" id="offlineInstructions" style="display: none;">
                    <h5 class="section-title">Bank Transfer Instructions</h5>
                    <p class="section-description">Use these details for NEFT/RTGS transfer.</p>
                    
                    <div class="row">
                        @if($accountHolder)
                        <div class="col-md-6">
                            <div class="summary-item">
                                <div class="summary-label">Account Holder Name</div>
                                <div class="summary-value">{{ $accountHolder }}</div>
                            </div>
                        </div>
                        @endif
                        @if($accountNumber)
                        <div class="col-md-6">
                            <div class="summary-item">
                                <div class="summary-label">Account Number</div>
                                <div class="summary-value">{{ $accountNumber }}</div>
                            </div>
                        </div>
                        @endif
                        @if($ifscCode)
                        <div class="col-md-6">
                            <div class="summary-item">
                                <div class="summary-label">IFSC Code</div>
                                <div class="summary-value">{{ $ifscCode }}</div>
                            </div>
                        </div>
                        @endif
                        @if($bankName)
                        <div class="col-md-6">
                            <div class="summary-item">
                                <div class="summary-label">Bank Name</div>
                                <div class="summary-value">{{ $bankName }}</div>
                            </div>
                        </div>
                        @endif
                        @if($branch)
                        <div class="col-md-6">
                            <div class="summary-item">
                                <div class="summary-label">Branch</div>
                                <div class="summary-value">{{ $branch }}</div>
                            </div>
                        </div>
                        @endif
                        @if($branchAddress)
                        <div class="col-md-6">
                            <div class="summary-item">
                                <div class="summary-label">Branch Address</div>
                                <div class="summary-value">{{ $branchAddress }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="alert alert-warning mt-3" style="margin-bottom: 0;">
                        <i class="bi bi-info-circle me-2"></i>After you transfer via NEFT/RTGS, continue and upload payment proof on the confirmation screen for admin approval.
                    </div>
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
                            <div class="row">
                                @if($bankName)
                                <div class="col-md-6 mb-3">
                                    <div class="summary-item">
                                        <div class="summary-label">Bank Name</div>
                                        <div class="summary-value">{{ $bankName }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($accountHolder)
                                <div class="col-md-6 mb-3">
                                    <div class="summary-item">
                                        <div class="summary-label">Account Holder</div>
                                        <div class="summary-value">{{ $accountHolder }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($accountNumber)
                                <div class="col-md-6 mb-3">
                                    <div class="summary-item">
                                        <div class="summary-label">Account Number</div>
                                        <div class="summary-value">{{ $accountNumber }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($ifscCode)
                                <div class="col-md-6 mb-3">
                                    <div class="summary-item">
                                        <div class="summary-label">IFSC Code</div>
                                        <div class="summary-value">{{ $ifscCode }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($branch)
                                <div class="col-md-6 mb-3">
                                    <div class="summary-item">
                                        <div class="summary-label">Branch</div>
                                        <div class="summary-value">{{ $branch }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($branchAddress)
                                <div class="col-md-6 mb-3">
                                    <div class="summary-item">
                                        <div class="summary-label">Branch Address</div>
                                        <div class="summary-value">{{ $branchAddress }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @endif
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>Use these bank details to complete your net banking payment.
                        </div>
                    </div>
                </div>

                <!-- Primary submit button -->
                <div class="section-card" style="margin-top: -10px;">
                    <button type="submit" class="btn btn-payment" id="makePaymentBtn">
                        <span id="paymentButtonLabel">Make Payment</span> - ₹<span id="paymentButtonAmount">{{ number_format($payment->amount, 2) }}</span>
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
                    <p class="section-description">Exhibition and booking details.</p>
                    
                    <div class="summary-item">
                        <div class="summary-label">Exhibition Name</div>
                        <div class="summary-value">{{ $payment->booking->exhibition->name }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Booking Number</div>
                        <div class="summary-value">{{ $payment->booking->booking_number }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Total Booking Amount</div>
                        <div class="summary-value">₹{{ number_format($payment->booking->total_amount, 2) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Paid Amount</div>
                        <div class="summary-value">₹{{ number_format($payment->booking->paid_amount, 2) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Outstanding Balance</div>
                        <div class="summary-value">₹{{ number_format($outstanding, 2) }}</div>
                    </div>
                </div>
                
                <!-- Payment Schedule -->
                <div class="section-card">
                    <h5 class="section-title">Payment Schedule</h5>
                    <p class="section-description">All payment installments for this booking.</p>
                    
                    <table class="payment-schedule-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->booking->payments->sortBy(function($p) {
                                return $p->payment_type === 'initial' ? 1 : 2;
                            })->sortBy('due_date') as $p)
                            <tr class="{{ $p->id === $payment->id ? 'current-payment-row' : '' }}">
                                <td>
                                    {{ ucfirst($p->payment_type) }}
                                    @if($p->id === $payment->id)
                                        <span style="color: #6366f1;">(Current)</span>
                                    @endif
                                </td>
                                <td>
                                    ₹{{ number_format($p->amount, 2) }}
                                </td>
                                <td>
                                    @if($p->status === 'completed')
                                        <span style="color: #10b981;">✓ Paid</span>
                                    @elseif($p->payment_proof_file)
                                        <span style="color: #f59e0b;">⏳ Waiting</span>
                                    @else
                                        <span style="color: #64748b;">Pending</span>
                                    @endif
                                </td>
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
@php
    $paymentAmount = $payment->amount;
@endphp
let paymentAmount = parseFloat({{ number_format($paymentAmount, 2, '.', '') }});
let paymentSchedule = @json($payment->booking->payments->map(function($p) {
    return [
        'id' => $p->id,
        'type' => $p->payment_type,
        'amount' => $p->amount,
        'status' => $p->status,
        'due_date' => $p->due_date ? $p->due_date->format('Y-m-d') : null,
    ];
}));

// Payment method selection
document.querySelectorAll('.payment-method-card').forEach(card => {
    card.addEventListener('click', function() {
        // Block selection when card is disabled (e.g., wallet with insufficient balance)
        if (this.dataset.disabled === '1') {
            alert('Your wallet balance is not enough to pay this amount. Please use another payment method.');
            return;
        }
        document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        selectedMethod = this.getAttribute('data-method');
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
    // Update total due amount display
    document.getElementById('totalDueAmount').textContent = '₹' + paymentAmount.toFixed(2);
    document.getElementById('paymentButtonAmount').textContent = paymentAmount.toFixed(2);
}

// Copy UPI ID function
function copyUpiId(upiId) {
    navigator.clipboard.writeText(upiId).then(function() {
        alert('UPI ID copied to clipboard!');
    }, function(err) {
        console.error('Failed to copy UPI ID:', err);
    });
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
    document.getElementById('paymentAmount').value = paymentAmount.toFixed(2);
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
