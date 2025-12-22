@extends('layouts.exhibitor')

@section('title', 'Sponsorship Payment')
@section('page-title', 'Payment - ' . $booking->booking_number)

@push('styles')
<style>
    .payment-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .summary-box {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .payment-method-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .payment-method-card:hover {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .payment-method-card.selected {
        border-color: #6366f1;
        background: #e0f2fe;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="payment-card">
            <h4 class="mb-4">Payment Information</h4>
            
            <div class="summary-box mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Amount:</span>
                    <strong>₹{{ number_format($booking->amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Paid Amount:</span>
                    <strong>₹{{ number_format($booking->paid_amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top">
                    <span class="fw-bold">Outstanding:</span>
                    <strong class="text-primary fs-5">₹{{ number_format($outstanding, 2) }}</strong>
                </div>
            </div>
            
            <form action="{{ route('sponsorships.payment.store', $booking->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4">
                    <label class="form-label mb-3">Select Payment Method <span class="text-danger">*</span></label>
                    
                    <div class="payment-method-card" onclick="selectMethod('online')">
                        <input type="radio" name="payment_method" value="online" id="method_online" required>
                        <label for="method_online" class="ms-2">
                            <strong>Online Payment</strong>
                            <small class="d-block text-muted">Credit/Debit Card, UPI, Net Banking</small>
                        </label>
                    </div>
                    
                    <div class="payment-method-card" onclick="selectMethod('wallet')">
                        <input type="radio" name="payment_method" value="wallet" id="method_wallet" required>
                        <label for="method_wallet" class="ms-2">
                            <strong>Wallet Payment</strong>
                            <small class="d-block text-muted">Use wallet balance: ₹{{ number_format(auth()->user()->wallet_balance ?? 0, 2) }}</small>
                        </label>
                    </div>
                    
                    <div class="payment-method-card" onclick="selectMethod('offline')">
                        <input type="radio" name="payment_method" value="offline" id="method_offline" required>
                        <label for="method_offline" class="ms-2">
                            <strong>Offline Payment</strong>
                            <small class="d-block text-muted">Bank Transfer, Cash, Cheque</small>
                        </label>
                    </div>
                    
                    <div class="payment-method-card" onclick="selectMethod('rtgs')">
                        <input type="radio" name="payment_method" value="rtgs" id="method_rtgs" required>
                        <label for="method_rtgs" class="ms-2">
                            <strong>RTGS</strong>
                            <small class="d-block text-muted">Real Time Gross Settlement</small>
                        </label>
                    </div>
                    
                    <div class="payment-method-card" onclick="selectMethod('neft')">
                        <input type="radio" name="payment_method" value="neft" id="method_neft" required>
                        <label for="method_neft" class="ms-2">
                            <strong>NEFT</strong>
                            <small class="d-block text-muted">National Electronic Funds Transfer</small>
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                    <input type="number" name="amount" step="0.01" min="1" max="{{ $outstanding }}" class="form-control" value="{{ $outstanding }}" required>
                    <small class="text-muted">Maximum: ₹{{ number_format($outstanding, 2) }}</small>
                </div>
                
                <div id="offlineFields" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Transaction ID / Reference Number</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="Enter transaction ID if available">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Proof</label>
                        <input type="file" name="payment_proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Upload receipt or screenshot (PDF, JPG, PNG)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Details</label>
                        <textarea name="payment_proof_text" rows="3" class="form-control" placeholder="Bank name, account number, date, etc."></textarea>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="Any additional information..."></textarea>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-credit-card me-2"></i>Proceed to Payment
                    </button>
                    <a href="{{ route('sponsorships.booking', $booking->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="payment-card">
            <h5 class="mb-3">Booking Summary</h5>
            <div class="mb-2">
                <small class="text-muted">Booking #</small>
                <div><strong>{{ $booking->booking_number }}</strong></div>
            </div>
            <div class="mb-2">
                <small class="text-muted">Sponsorship</small>
                <div><strong>{{ $booking->sponsorship->name }}</strong></div>
            </div>
            <div class="mb-2">
                <small class="text-muted">Exhibition</small>
                <div>{{ $booking->exhibition->name }}</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function selectMethod(method) {
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    document.getElementById('method_' + method).checked = true;
    
    const offlineFields = document.getElementById('offlineFields');
    if (['offline', 'rtgs', 'neft'].includes(method)) {
        offlineFields.style.display = 'block';
    } else {
        offlineFields.style.display = 'none';
    }
}
</script>
@endpush
@endsection

