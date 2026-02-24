@extends('layouts.admin')

@section('title', 'Event Registrations')
@section('page-title', 'Event Registrations (Visitor / Member / Delegate / VIP)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
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

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Registrations</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-2">
                <label class="form-label small">Exhibition</label>
                <select name="exhibition_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach($exhibitions as $ex)
                        <option value="{{ $ex->id }}" {{ request('exhibition_id') == $ex->id ? 'selected' : '' }}>{{ $ex->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Type</label>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="visitor" {{ request('type') == 'visitor' ? 'selected' : '' }}>Visitor</option>
                    <option value="member" {{ request('type') == 'member' ? 'selected' : '' }}>Member</option>
                    <option value="delegate" {{ request('type') == 'delegate' ? 'selected' : '' }}>Delegate</option>
                    <option value="vip" {{ request('type') == 'vip' ? 'selected' : '' }}>VIP</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Approval</label>
                <select name="approval_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Payment</label>
                <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Reg #, email, name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Reg #</th>
                        <th>Type</th>
                        <th>Exhibition</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Fee</th>
                        <th>Paid</th>
                        <th>Approval</th>
                        <th>Payment Status</th>
                        <th>Latest Payment / Proof</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $r)
                    @php
                        $latestPayment = $r->payments->sortByDesc('created_at')->first();
                    @endphp
                    <tr>
                        <td><strong>{{ $r->registration_number }}</strong></td>
                        <td><span class="badge bg-secondary">{{ ucfirst($r->type) }}</span></td>
                        <td>{{ $r->exhibition->name }}</td>
                        <td>{{ $r->full_name }}</td>
                        <td>{{ $r->email }}</td>
                        <td>₹{{ number_format($r->fee_amount, 2) }}</td>
                        <td>₹{{ number_format($r->paid_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $r->approval_status === 'approved' ? 'success' : ($r->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($r->approval_status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $r->payment_status === 'paid' ? 'success' : ($r->payment_status === 'partial' ? 'info' : 'secondary') }}">
                                {{ ucfirst($r->payment_status) }}
                            </span>
                        </td>
                        <td>
                            @if($latestPayment)
                                <div class="small">
                                    <div>#{{ $latestPayment->payment_number }}</div>
                                    <div>₹{{ number_format($latestPayment->amount, 2) }} • {{ strtoupper($latestPayment->payment_method) }}</div>
                                    <div>
                                        <span class="badge bg-{{ $latestPayment->approval_status === 'approved' ? 'success' : ($latestPayment->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($latestPayment->approval_status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    @if($latestPayment->payment_proof_file)
                                        <a href="{{ route('admin.event-registrations.download-payment-proof', $latestPayment->id) }}" class="btn btn-sm btn-outline-secondary mb-1">
                                            Proof
                                        </a>
                                    @endif
                                    @if($latestPayment->approval_status === 'pending')
                                        <form action="{{ route('admin.event-registrations.approve-payment', $latestPayment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success mb-1">Approve</button>
                                        </form>
                                        <form action="{{ route('admin.event-registrations.reject-payment', $latestPayment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="rejection_reason" value="Rejected from list view">
                                            <button type="submit" class="btn btn-sm btn-danger mb-1">Reject</button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted small">No payments</span>
                            @endif
                        </td>
                        <td>{{ $r->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.event-registrations.show', $r->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-4">No registrations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $registrations->links() }}
        </div>
    </div>
</div>
@endsection
