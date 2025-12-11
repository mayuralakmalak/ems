@extends('layouts.admin')

@section('title', 'Payment Details')
@section('page-title', 'Payment Details')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Payments
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Payment Number:</strong><br>
                        {{ $payment->payment_number }}
                    </div>
                    <div class="col-md-6">
                        <strong>Amount:</strong><br>
                        ₹{{ number_format($payment->amount, 2) }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Payment Method:</strong><br>
                        {{ ucfirst($payment->payment_method) }}
                    </div>
                    <div class="col-md-6">
                        <strong>Payment Type:</strong><br>
                        {{ ucfirst($payment->payment_type) }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Approval Status:</strong><br>
                        <span class="badge bg-{{ $payment->approval_status === 'approved' ? 'success' : ($payment->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($payment->approval_status) }}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Transaction ID:</strong><br>
                        {{ $payment->transaction_id ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Payment Date:</strong><br>
                        {{ $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i') : 'N/A' }}
                    </div>
                </div>
                @if($payment->rejection_reason)
                <div class="alert alert-danger">
                    <strong>Rejection Reason:</strong> {{ $payment->rejection_reason }}
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Booking Information</h5>
            </div>
            <div class="card-body">
                @if($payment->booking)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Booking Number:</strong><br>
                        {{ $payment->booking->booking_number }}
                    </div>
                    <div class="col-md-6">
                        <strong>Exhibition:</strong><br>
                        {{ $payment->booking->exhibition->name ?? 'N/A' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Total Amount:</strong><br>
                        ₹{{ number_format($payment->booking->total_amount, 2) }}
                    </div>
                    <div class="col-md-6">
                        <strong>Paid Amount:</strong><br>
                        ₹{{ number_format($payment->booking->paid_amount, 2) }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Exhibitor Information</h5>
            </div>
            <div class="card-body">
                @if($payment->user)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Name:</strong><br>
                        {{ $payment->user->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        {{ $payment->user->email }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Company:</strong><br>
                        {{ $payment->user->company_name ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Phone:</strong><br>
                        {{ $payment->user->phone ?? 'N/A' }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payment Proof</h5>
            </div>
            <div class="card-body">
                @if($payment->payment_proof_file)
                    <div class="mb-3">
                        <a href="{{ asset('storage/' . $payment->payment_proof_file) }}" target="_blank" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-eye me-2"></i>View Payment Proof
                        </a>
                        <a href="{{ asset('storage/' . $payment->payment_proof_file) }}" download class="btn btn-outline-primary w-100">
                            <i class="bi bi-download me-2"></i>Download Proof
                        </a>
                    </div>
                    @php
                        $extension = pathinfo($payment->payment_proof_file, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                    @endphp
                    @if($isImage)
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $payment->payment_proof_file) }}" alt="Payment Proof" class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                    @elseif(strtolower($extension) === 'pdf')
                        <div class="text-center p-4 bg-light rounded">
                            <i class="bi bi-file-earmark-pdf" style="font-size: 4rem; color: #dc2626;"></i>
                            <p class="mt-2 mb-0">PDF Document</p>
                        </div>
                    @else
                        <div class="text-center p-4 bg-light rounded">
                            <i class="bi bi-file-earmark-text" style="font-size: 4rem; color: #64748b;"></i>
                            <p class="mt-2 mb-0">{{ strtoupper($extension) }} Document</p>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0">No payment proof uploaded</p>
                    </div>
                @endif
            </div>
        </div>

        @if($payment->approval_status === 'pending')
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-white">Payment Approval</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.payments.approve', $payment->id) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve this payment?');">
                        <i class="bi bi-check-circle me-2"></i>Approve Payment
                    </button>
                </form>
                
                <form method="POST" action="{{ route('admin.payments.reject', $payment->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this payment?');">
                        <i class="bi bi-x-circle me-2"></i>Reject Payment
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
