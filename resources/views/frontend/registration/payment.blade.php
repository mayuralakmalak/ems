@extends('layouts.frontend')

@section('title', 'Complete Payment - ' . $registration->registration_number)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-gradient-purple text-white py-4">
                    <h1 class="h4 mb-0">Complete Payment</h1>
                    <p class="mb-0 opacity-90 small">Registration: {{ $registration->registration_number }} — {{ $registration->exhibition->name }}</p>
                </div>
                <div class="card-body p-4 p-lg-5">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-light border mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total fee:</span>
                            <strong>₹{{ number_format($registration->fee_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Already paid:</span>
                            <strong>₹{{ number_format($registration->paid_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="fw-bold">Amount to pay:</span>
                            <strong class="text-primary">₹{{ number_format($outstanding, 2) }}</strong>
                        </div>
                    </div>

                    <p class="text-muted small">Select a payment method and submit your payment details. Admin approval is required. You will receive an email once the payment is approved.</p>

                    <form action="{{ route('register.payment.store', $registration->token) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-600">Payment Method <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3 h-100">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pm_online" value="online" required>
                                        <label class="form-check-label" for="pm_online">
                                            <strong>Online</strong><br>
                                            <small class="text-muted">Card / UPI / Net Banking</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3 h-100">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pm_neft" value="neft">
                                        <label class="form-check-label" for="pm_neft">
                                            <strong>NEFT</strong><br>
                                            <small class="text-muted">National Electronic Funds Transfer</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3 h-100">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pm_rtgs" value="rtgs">
                                        <label class="form-check-label" for="pm_rtgs">
                                            <strong>RTGS</strong><br>
                                            <small class="text-muted">Real Time Gross Settlement</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3 h-100">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pm_offline" value="offline">
                                        <label class="form-check-label" for="pm_offline">
                                            <strong>Offline</strong><br>
                                            <small class="text-muted">Bank transfer / Cheque</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="{{ $outstanding }}" value="{{ $outstanding }}" required>
                            <small class="text-muted">Max: ₹{{ number_format($outstanding, 2) }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Transaction / Reference ID</label>
                            <input type="text" name="transaction_id" class="form-control" value="{{ old('transaction_id') }}" placeholder="Optional">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment proof (PDF / image, max 2MB)</label>
                            <input type="file" name="payment_proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">For NEFT/RTGS/Offline, upload screenshot or receipt.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-custom bg-gradient-purple px-4">Submit Payment</button>
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
