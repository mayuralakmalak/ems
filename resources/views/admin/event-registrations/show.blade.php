@extends('layouts.admin')

@section('title', 'Event Registration - ' . $registration->registration_number)
@section('page-title', 'Registration - ' . $registration->registration_number)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.event-registrations.index') }}" class="btn btn-outline-secondary">
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
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Registration Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Registration Number:</strong><br>
                        <span class="h5">{{ $registration->registration_number }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Type:</strong><br>
                        <span class="badge bg-secondary px-3 py-2">{{ ucfirst($registration->type) }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Exhibition:</strong><br>
                        {{ $registration->exhibition->name }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Fee Amount:</strong><br>
                        ₹{{ number_format($registration->fee_amount, 2) }}
                        @if($registration->fee_tier)
                            <span class="badge bg-info ms-1">{{ ucfirst(str_replace('_', ' ', $registration->fee_tier)) }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Name:</strong><br>
                        {{ $registration->full_name }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong><br>
                        {{ $registration->email }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone:</strong><br>
                        {{ $registration->phone }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Company:</strong><br>
                        {{ $registration->company ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Designation:</strong><br>
                        {{ $registration->designation ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>City / State / Country:</strong><br>
                        {{ trim(implode(', ', array_filter([$registration->city, $registration->state, $registration->country]))) ?: '—' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>ID Proof:</strong><br>
                        @if($registration->id_proof_file)
                            <a href="{{ route('admin.event-registrations.download-id-proof', $registration->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                        @else
                            —
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Approval Status:</strong><br>
                        <span class="badge bg-{{ $registration->approval_status === 'approved' ? 'success' : ($registration->approval_status === 'rejected' ? 'danger' : 'warning') }} px-3 py-2">
                            {{ ucfirst($registration->approval_status) }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Payment Status:</strong><br>
                        <span class="badge bg-{{ $registration->payment_status === 'paid' ? 'success' : ($registration->payment_status === 'partial' ? 'info' : 'secondary') }} px-3 py-2">
                            {{ ucfirst($registration->payment_status) }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Paid Amount:</strong><br>
                        ₹{{ number_format($registration->paid_amount, 2) }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Registered At:</strong><br>
                        {{ $registration->created_at->format('d M Y H:i') }}
                    </div>
                    @if($registration->approval_status === 'rejected' && $registration->rejection_reason)
                    <div class="col-12 mb-3">
                        <strong>Rejection Reason:</strong><br>
                        <div class="alert alert-danger mb-0">{{ $registration->rejection_reason }}</div>
                    </div>
                    @endif
                    @if($registration->approval_status === 'approved' && $registration->approver)
                    <div class="col-md-6 mb-3">
                        <strong>Approved By:</strong><br>
                        {{ $registration->approver->name }} on {{ $registration->approved_at?->format('d M Y') }}
                    </div>
                    @endif
                </div>

                @if($registration->approval_status === 'pending')
                <hr>
                <form action="{{ route('admin.event-registrations.approve', $registration->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Approve Registration</button>
                </form>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                <div class="modal fade" id="rejectModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.event-registrations.reject', $registration->id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject Registration</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                                    <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payments</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Approval</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registration->payments as $pay)
                            <tr>
                                <td>{{ $pay->payment_number }}</td>
                                <td>{{ strtoupper($pay->payment_method) }}</td>
                                <td>₹{{ number_format($pay->amount, 2) }}</td>
                                <td><span class="badge bg-{{ $pay->status === 'completed' ? 'success' : ($pay->status === 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($pay->status) }}</span></td>
                                <td><span class="badge bg-{{ $pay->approval_status === 'approved' ? 'success' : ($pay->approval_status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($pay->approval_status) }}</span></td>
                                <td>{{ $pay->created_at->format('d M Y') }}</td>
                                <td>
                                    @if($pay->payment_proof_file)
                                        <a href="{{ route('admin.event-registrations.download-payment-proof', $pay->id) }}" class="btn btn-sm btn-outline-secondary">Proof</a>
                                    @endif
                                    @if($pay->approval_status === 'pending')
                                        <form action="{{ route('admin.event-registrations.approve-payment', $pay->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectPayModal{{ $pay->id }}">Reject</button>
                                        <div class="modal fade" id="rejectPayModal{{ $pay->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.event-registrations.reject-payment', $pay->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Payment</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @if($pay->rejection_reason)
                            <tr>
                                <td colspan="7" class="small text-danger">Rejection: {{ $pay->rejection_reason }}</td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="7" class="text-muted">No payments yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
