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

                {{-- Company Assets: Logo + Promotional Brochures uploaded during booking --}}
                <hr>
                <h6>Company Assets</h6>

                {{-- Company Logo --}}
                <div class="mb-3">
                    <strong>Company Logo:</strong><br>
                    @if($booking->logo)
                        <img src="{{ asset('storage/' . $booking->logo) }}"
                             alt="Company Logo"
                             style="max-width: 220px; max-height: 120px; object-fit: contain; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; background: #f8fafc;">
                    @else
                        <span class="text-muted">No logo uploaded</span>
                    @endif
                </div>

                {{-- Promotional Brochures --}}
                @php
                    $promotionalBrochures = $booking->documents->where('type', 'Promotional Brochure');
                @endphp
                <div class="mb-2">
                    <strong>Promotional Brochures:</strong><br>
                    @if($promotionalBrochures->count() > 0)
                        <ul class="mt-2">
                            @foreach($promotionalBrochures as $brochure)
                                <li>
                                    <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>
                                    <a href="{{ asset('storage/' . $brochure->file_path) }}" target="_blank">
                                        {{ $brochure->name ?? 'Brochure' }}
                                    </a>
                                    @if($brochure->file_size)
                                        <small class="text-muted ms-1">
                                            ({{ number_format($brochure->file_size / 1024, 0) }} KB)
                                        </small>
                                    @endif
                                    <span class="badge bg-{{ $brochure->status === 'approved' ? 'success' : ($brochure->status === 'rejected' ? 'danger' : 'warning') }} ms-2">
                                        {{ ucfirst($brochure->status ?? 'pending') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="text-muted">No brochures uploaded</span>
                    @endif
                </div>

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

        {{-- Process cancellation only after exhibitor has requested it (reason set) and admin has not yet decided type/amount --}}
        @if($booking->cancellation_reason && !$booking->cancellation_type)
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Process Cancellation</h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('admin.bookings.process-cancellation', $booking->id) }}" method="POST" id="processCancellationForm">
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
                        <label class="form-label d-block">Cancellation Amount *</label>
                        <div class="btn-group mb-2" role="group" aria-label="Quick amount options">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="setCancellationAmount({{ $booking->paid_amount }})">
                                Full Paid (₹{{ number_format($booking->paid_amount, 2) }})
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="setCancellationAmount({{ $booking->paid_amount / 2 }})">
                                50% Paid
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="setCancellationAmount('')">
                                Custom
                            </button>
                        </div>
                        <input type="number"
                               name="cancellation_amount"
                               id="cancellation_amount_input"
                               class="form-control"
                               step="0.01"
                               min="0"
                               max="{{ $booking->total_amount }}"
                               value="{{ $booking->paid_amount }}"
                               required>
                        <small class="text-muted">
                            You can enter any amount up to the total booking amount:
                            ₹{{ number_format($booking->total_amount, 2) }}.
                        </small>
                    </div>
                    <div class="mb-3" id="accountDetailsField" style="display: none;">
                        <label class="form-label">Account Details *</label>
                        <textarea name="account_details" id="account_details_textarea" class="form-control" rows="3" placeholder="Enter bank account details for refund (e.g., Account Number, IFSC Code, Bank Name, Account Holder Name)"></textarea>
                        <small class="text-muted">Please provide complete bank account details for processing the refund.</small>
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

        <!-- Additional Service Requests -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Additional Service Requests</h5>
                <a href="{{ route('admin.additional-service-requests.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @php
                    $additionalRequests = $booking->additionalServiceRequests;
                @endphp
                @if($additionalRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($additionalRequests as $req)
                                <tr>
                                    <td>{{ $req->service->name }}</td>
                                    <td>{{ $req->quantity }}</td>
                                    <td>₹{{ number_format($req->unit_price, 2) }}</td>
                                    <td>₹{{ number_format($req->total_price, 2) }}</td>
                                    <td>
                                        @if($req->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($req->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($req->status === 'pending')
                                            <form action="{{ route('admin.additional-service-requests.approve', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve this request? A payment will be generated.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            
                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Request</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.additional-service-requests.reject', $req->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Rejection Reason *</label>
                                                                    <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">Reject</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <small class="text-muted">
                                                {{ $req->approver->name ?? 'Admin' }}<br>
                                                {{ $req->approved_at->format('d M Y') }}
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No additional service requests.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('select[name="cancellation_type"]').on('change', function() {
        const accountField = $('#accountDetailsField');
        const accountTextarea = $('#account_details_textarea');
        
        if ($(this).val() === 'refund') {
            accountField.show();
            accountTextarea.prop('required', true);
            accountTextarea.val(''); // Clear previous value
        } else {
            accountField.hide();
            accountTextarea.prop('required', false);
            accountTextarea.val(''); // Clear value when hidden
        }
    });
    
    // Trigger change on page load if refund is pre-selected
    if ($('select[name="cancellation_type"]').val() === 'refund') {
        $('select[name="cancellation_type"]').trigger('change');
    }

    window.setCancellationAmount = function(amount) {
        const input = document.getElementById('cancellation_amount_input');
        if (!input) return;
        if (amount === '') {
            input.value = '';
            input.focus();
        } else {
            input.value = parseFloat(amount).toFixed(2);
        }
    };

    // Handle form submission
    $('#processCancellationForm').on('submit', function(e) {
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable button and show loading state
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Processing...');
        
        // Re-enable if there's a validation error (form won't submit)
        setTimeout(function() {
            submitBtn.prop('disabled', false).html(originalText);
        }, 5000);
    });
});
</script>
@endpush
@endsection

