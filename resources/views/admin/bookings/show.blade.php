@extends('layouts.admin')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details - ' . $booking->booking_number)

@section('content')
<div class="mb-4">
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Booking Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Booking Number:</strong><br>
                        <span class="h5">{{ $booking->booking_number }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning') }} px-3 py-2">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Exhibition:</strong><br>
                        {{ $booking->exhibition->name ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Booth:</strong><br>
                        {{ $booking->booth->name ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Exhibitor:</strong><br>
                        {{ $booking->user->name ?? '-' }}<br>
                        <small class="text-muted">{{ $booking->user->email ?? '' }}</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Total Amount:</strong><br>
                        ₹{{ number_format($booking->total_amount, 0) }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Paid Amount:</strong><br>
                        ₹{{ number_format($booking->paid_amount, 0) }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Outstanding:</strong><br>
                        ₹{{ number_format($booking->total_amount - $booking->paid_amount, 0) }}
                    </div>
                </div>

                @if($booking->contact_emails)
                <hr>
                <h6>Contact Emails:</h6>
                <ul>
                    @foreach($booking->contact_emails as $email)
                    <li>{{ $email }}</li>
                    @endforeach
                </ul>
                @endif

                @if($booking->contact_numbers)
                <h6>Contact Numbers:</h6>
                <ul>
                    @foreach($booking->contact_numbers as $number)
                    <li>{{ $number }}</li>
                    @endforeach
                </ul>
                @endif

                @if($booking->cancellation_reason)
                <hr>
                <div class="alert alert-warning">
                    <h6>Cancellation Details:</h6>
                    <p><strong>Reason:</strong> {{ $booking->cancellation_reason }}</p>
                    @if($booking->cancellation_type)
                    <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $booking->cancellation_type)) }}</p>
                    @endif
                    @if($booking->cancellation_amount)
                    <p><strong>Amount:</strong> ₹{{ number_format($booking->cancellation_amount, 0) }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>

        @if($booking->status === 'cancelled' && !$booking->cancellation_type)
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Process Cancellation</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.bookings.process-cancellation', $booking->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Cancellation Type *</label>
                        <select name="cancellation_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="refund">Refund to Bank Account</option>
                            <option value="wallet_credit">Credit to Wallet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cancellation Amount *</label>
                        <input type="number" name="cancellation_amount" class="form-control" step="0.01" min="0" max="{{ $booking->paid_amount }}" value="{{ $booking->paid_amount }}" required>
                        <small class="text-muted">Maximum: ₹{{ number_format($booking->paid_amount, 2) }}</small>
                    </div>
                    <div class="mb-3" id="accountDetailsField" style="display: none;">
                        <label class="form-label">Account Details *</label>
                        <textarea name="account_details" class="form-control" rows="3" placeholder="Bank account details for refund"></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-2"></i>Process Cancellation
                    </button>
                </form>
            </div>
        </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Documents</h5>
            </div>
            <div class="card-body">
                @if($booking->documents->count() > 0)
                    <div class="list-group">
                        @foreach($booking->documents as $document)
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $document->name ?? $document->type }}</strong><br>
                                <small class="text-muted">{{ ucfirst($document->status ?? 'pending') }}</small>
                                @if($document->rejection_reason)
                                    <div class="text-danger small mt-1">Reason: {{ $document->rejection_reason }}</div>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i> View
                                </a>
                                <form action="{{ route('admin.bookings.documents.approve', $document->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="collapse" data-bs-target="#rejectDoc{{ $document->id }}" aria-expanded="false">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="collapse border rounded p-3 mt-2" id="rejectDoc{{ $document->id }}">
                            <form action="{{ route('admin.bookings.documents.reject', $document->id) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Rejection Reason</label>
                                    <textarea name="rejection_reason" class="form-control" rows="2" required placeholder="Enter reason to show the exhibitor"></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-danger">Submit Rejection</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No documents uploaded.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Payments</h5>
            </div>
            <div class="card-body">
                @if($booking->payments->count() > 0)
                <div class="list-group">
                    @foreach($booking->payments as $payment)
                    <div class="list-group-item">
                        <strong>{{ $payment->payment_number }}</strong><br>
                        <small>₹{{ number_format($payment->amount, 0) }} - {{ ucfirst($payment->payment_method) }}</small><br>
                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($payment->status) }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted mb-0">No payments yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('select[name="cancellation_type"]').on('change', function() {
        if ($(this).val() === 'refund') {
            $('#accountDetailsField').show();
            $('textarea[name="account_details"]').prop('required', true);
        } else {
            $('#accountDetailsField').hide();
            $('textarea[name="account_details"]').prop('required', false);
        }
    });
});
</script>
@endpush
@endsection

