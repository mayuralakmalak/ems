@extends('layouts.exhibitor')

@section('title', 'Booking Cancellation')
@section('page-title', 'Booking Cancellation')

@push('styles')
<style>
    .cancellation-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .cancellation-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .cancellation-header {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 25px;
    }
    
    .booking-info {
        background: #f8fafc;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: #64748b;
        font-size: 0.95rem;
    }
    
    .info-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .form-label {
        font-weight: 500;
        color: #334155;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .form-control, .form-select {
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 1rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }
    
    .charges-box {
        background: #fef3c7;
        border: 1px solid #fbbf24;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
    }
    
    .charges-label {
        color: #92400e;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .charges-amount {
        font-size: 1.2rem;
        font-weight: 700;
        color: #ef4444;
    }
    
    .refund-option {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .refund-option:hover {
        border-color: #6366f1;
        background: #f8fafc;
    }
    
    .refund-option.selected {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .refund-option input[type="radio"] {
        margin-right: 10px;
    }
    
    .refund-option-label {
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
    }
    
    .terms-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 8px;
        margin: 25px 0;
    }
    
    .terms-checkbox input[type="checkbox"] {
        margin-top: 3px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .terms-checkbox label {
        flex: 1;
        color: #334155;
        font-size: 0.95rem;
        cursor: pointer;
    }
    
    .btn-submit {
        width: 100%;
        padding: 15px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        background: #dc2626;
    }
    
    .account-details {
        margin-top: 15px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 8px;
        display: none;
    }
</style>
@endpush

@section('content')
<div class="cancellation-container">
    <div class="cancellation-card">
        <h3 class="cancellation-header">Cancellation Request for Booking #{{ $booking->booking_number }}</h3>
        
        <div class="booking-info">
            <div class="info-row">
                <span class="info-label">Exhibition Name</span>
                <span class="info-value">{{ $booking->exhibition->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Booking Date</span>
                <span class="info-value">{{ $booking->created_at->format('F d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Current Status</span>
                <span class="info-value">{{ ucfirst($booking->status) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Booth Number</span>
                <span class="info-value">{{ $booking->booth->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Amount</span>
                <span class="info-value">₹{{ number_format($booking->total_amount, 2) }}</span>
            </div>
        </div>
        
        <form method="POST" action="{{ route('bookings.cancel', $booking->id) }}">
            @csrf
            
            <div class="mb-4">
                <label class="form-label">Cancellation Reason</label>
                <select name="cancellation_reason" class="form-select" required>
                    <option value="">Select a reason</option>
                    <option value="Change of plans">Change of plans</option>
                    <option value="Financial constraints">Financial constraints</option>
                    <option value="Found alternative event">Found alternative event</option>
                    <option value="Company policy change">Company policy change</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="charges-box">
                <div class="charges-label">Applicable Cancellation Charges:</div>
                <div class="charges-amount">15% of total booking amount ₹{{ number_format($cancellationCharge, 2) }}</div>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Refund Options</label>
                
                <div class="refund-option" onclick="selectRefundOption('full_refund')">
                    <input type="radio" name="refund_option" value="full_refund" id="full_refund" required>
                    <label for="full_refund" class="refund-option-label">Full refund minus charges</label>
                </div>
                
                <div class="refund-option" onclick="selectRefundOption('partial_refund')">
                    <input type="radio" name="refund_option" value="partial_refund" id="partial_refund">
                    <label for="partial_refund" class="refund-option-label">Partial Refund (50% remaining amount)</label>
                </div>
                
                <div class="refund-option" onclick="selectRefundOption('wallet_credit')">
                    <input type="radio" name="refund_option" value="wallet_credit" id="wallet_credit">
                    <label for="wallet_credit" class="refund-option-label">Credit to ExhiBook Wallet</label>
                </div>
                
                <div class="refund-option" onclick="selectRefundOption('bank_refund')">
                    <input type="radio" name="refund_option" value="bank_refund" id="bank_refund">
                    <label for="bank_refund" class="refund-option-label">Refund in Bank with Account Details</label>
                </div>
                
                <div class="account-details" id="accountDetails">
                    <label class="form-label">Account Details</label>
                    <textarea name="account_details" class="form-control" rows="4" placeholder="Enter bank account details for refund..."></textarea>
                </div>
            </div>
            
            <div class="terms-checkbox">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">I agree to the cancellation terms and conditions</label>
            </div>
            
            <button type="submit" class="btn-submit">Submit</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function selectRefundOption(value) {
    document.querySelectorAll('.refund-option').forEach(opt => opt.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    document.getElementById(value).checked = true;
    
    if (value === 'bank_refund') {
        document.getElementById('accountDetails').style.display = 'block';
    } else {
        document.getElementById('accountDetails').style.display = 'none';
    }
}

// Handle radio button clicks
document.querySelectorAll('input[name="refund_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'bank_refund') {
            document.getElementById('accountDetails').style.display = 'block';
        } else {
            document.getElementById('accountDetails').style.display = 'none';
        }
    });
});
</script>
@endpush
@endsection

