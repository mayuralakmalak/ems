@extends('layouts.admin')

@section('title', 'Sponsorship Booking Details')
@section('page-title', 'Booking Details - ' . $booking->booking_number)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.sponsorship-bookings.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to List
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Booking Information -->
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
                        {{ $booking->exhibition->name }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Sponsorship Package:</strong><br>
                        {{ $booking->sponsorship->name }}
                        @if($booking->sponsorship->tier)
                            <span class="badge bg-info ms-2">{{ $booking->sponsorship->tier }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Exhibitor:</strong><br>
                        {{ $booking->user->name }}<br>
                        <small class="text-muted">{{ $booking->user->email }}</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Total Amount:</strong><br>
                        ₹{{ number_format($booking->amount, 2) }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Paid Amount:</strong><br>
                        ₹{{ number_format($booking->paid_amount, 2) }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Outstanding:</strong><br>
                        ₹{{ number_format($booking->amount - $booking->paid_amount, 2) }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Payment Status:</strong><br>
                        <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'partial' ? 'warning' : 'info') }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Approval Status:</strong><br>
                        <span class="badge bg-{{ $booking->approval_status === 'approved' ? 'success' : ($booking->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($booking->approval_status) }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Booking Date:</strong><br>
                        {{ $booking->created_at->format('M d, Y h:i A') }}
                    </div>
                    @if($booking->approved_by)
                    <div class="col-md-6 mb-3">
                        <strong>Approved By:</strong><br>
                        {{ $booking->approver->name ?? 'N/A' }}<br>
                        <small class="text-muted">{{ $booking->approved_at ? $booking->approved_at->format('M d, Y') : '' }}</small>
                    </div>
                    @endif
                    @if($booking->rejection_reason)
                    <div class="col-12 mb-3">
                        <strong>Rejection Reason:</strong><br>
                        <div class="alert alert-danger mb-0">{{ $booking->rejection_reason }}</div>
                    </div>
                    @endif
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

                @if($booking->logo)
                <hr>
                <h6>Company Logo:</h6>
                <img src="{{ asset('storage/' . $booking->logo) }}"
                     alt="Company Logo"
                     style="max-width: 220px; max-height: 120px; object-fit: contain; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; background: #f8fafc;">
                @endif

                @if($booking->notes)
                <hr>
                <h6>Notes:</h6>
                <p>{{ $booking->notes }}</p>
                @endif
            </div>
        </div>

        <!-- Deliverables -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Deliverables</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    @if(is_array($booking->sponsorship->deliverables))
                        @foreach($booking->sponsorship->deliverables as $deliverable)
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            {{ $deliverable }}
                        </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>

        <!-- Payment History -->
        @if($booking->payments->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Approval</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->payments as $payment)
                            <tr>
                                <td><strong>{{ $payment->payment_number }}</strong></td>
                                <td>₹{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>
                                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $payment->approval_status === 'approved' ? 'success' : ($payment->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($payment->approval_status) }}
                                    </span>
                                </td>
                                <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($payment->approval_status === 'pending')
                                        <form action="{{ route('admin.sponsorship-payments.approve', $payment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal{{ $payment->id }}">
                                            <i class="bi bi-x"></i> Reject
                                        </button>
                                        
                                        <!-- Reject Payment Modal -->
                                        <div class="modal fade" id="rejectPaymentModal{{ $payment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.sponsorship-payments.reject', $payment->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Payment</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                                <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Payment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Approval Actions -->
        @if($booking->approval_status === 'pending')
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Pending Approval</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sponsorship-bookings.approve', $booking->id) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check-circle me-2"></i>Approve Booking
                    </button>
                </form>
                
                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle me-2"></i>Reject Booking
                </button>
            </div>
        </div>
        @endif

        <!-- Booking Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Sponsorship Package</small>
                    <div><strong>{{ $booking->sponsorship->name }}</strong></div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Exhibition</small>
                    <div>{{ $booking->exhibition->name }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Total Amount</small>
                    <div class="h5 text-primary">₹{{ number_format($booking->amount, 2) }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Paid</small>
                    <div>₹{{ number_format($booking->paid_amount, 2) }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Outstanding</small>
                    <div class="h6">₹{{ number_format($booking->amount - $booking->paid_amount, 2) }}</div>
                </div>
            </div>
        </div>

        @if($booking->booking)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Linked Booth Booking</h5>
            </div>
            <div class="card-body">
                <p><strong>Booking #:</strong> {{ $booking->booking->booking_number }}</p>
                <a href="{{ route('admin.bookings.show', $booking->booking->id) }}" class="btn btn-sm btn-outline-primary">
                    View Booth Booking
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Reject Booking Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sponsorship-bookings.reject', $booking->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Sponsorship Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

