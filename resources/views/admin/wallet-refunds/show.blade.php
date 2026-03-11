@extends('layouts.admin')

@section('title', 'Wallet Refund Request Details')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('admin.wallet-refunds.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            &larr; Back to Requests
        </a>
        <h1 class="h3 mb-1">Wallet Refund Request #{{ $request->id }}</h1>
        <p class="text-muted mb-0">Refund of special discount credited to wallet (no booth cancellation).</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Exhibitor &amp; Amount</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Exhibitor</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $request->user->name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $request->user->email ?? '' }}</small>
                        </dd>

                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8">
                            <strong>₹{{ number_format($request->amount, 2) }}</strong>
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($request->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($request->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Requested At</dt>
                        <dd class="col-sm-8">
                            {{ $request->created_at->format('d M Y, h:i A') }}
                        </dd>

                        <dt class="col-sm-4">User Wallet Balance</dt>
                        <dd class="col-sm-8">
                            ₹{{ number_format($request->user->wallet_balance, 2) }}
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Original Wallet Transaction</h5>
                </div>
                <div class="card-body">
                    @if($request->wallet)
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Transaction ID</dt>
                            <dd class="col-sm-8">#{{ $request->wallet->id }}</dd>

                            <dt class="col-sm-4">Type</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-success">Credit</span>
                            </dd>

                            <dt class="col-sm-4">Amount</dt>
                            <dd class="col-sm-8">₹{{ number_format($request->wallet->amount, 2) }}</dd>

                            <dt class="col-sm-4">Description</dt>
                            <dd class="col-sm-8">{{ $request->wallet->description }}</dd>

                            <dt class="col-sm-4">Created At</dt>
                            <dd class="col-sm-8">{{ $request->wallet->created_at->format('d M Y, h:i A') }}</dd>
                        </dl>
                    @else
                        <p class="text-muted mb-0">Original wallet transaction not found.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Request Reason</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $request->reason ?: '-' }}</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Admin Action</h5>
                </div>
                <div class="card-body">
                    @if($request->status === 'pending')
                        <p class="text-muted">
                            Approving will debit the amount from exhibitor wallet and you should process the actual refund offline (e.g., bank transfer).
                            This does <strong>not cancel the booth or booking</strong>.
                        </p>
                        <form method="POST" action="{{ route('admin.wallet-refunds.approve', $request->id) }}" class="mb-3">
                            @csrf
                            <div class="mb-3">
                                <label for="admin_note" class="form-label">Admin Note (optional)</label>
                                <textarea name="admin_note" id="admin_note" rows="3" class="form-control" placeholder="Add a note about how refund will be processed..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success me-2">Approve &amp; Debit Wallet</button>
                        </form>

                        <form method="POST" action="{{ route('admin.wallet-refunds.reject', $request->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="reject_admin_note" class="form-label">Reason for Rejection (optional)</label>
                                <textarea name="admin_note" id="reject_admin_note" rows="3" class="form-control" placeholder="Add a note to explain why this refund is rejected..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger">Reject Request</button>
                        </form>
                    @else
                        <p class="mb-2">
                            This request has been
                            @if($request->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                            on {{ optional($request->processed_at)->format('d M Y, h:i A') }}.
                        </p>
                        @if($request->admin_note)
                            <div class="mb-2">
                                <h6 class="fw-bold">Admin Note</h6>
                                <p class="mb-0">{{ $request->admin_note }}</p>
                            </div>
                        @endif
                        @if($request->processor)
                            <p class="text-muted mb-0">
                                Processed by {{ $request->processor->name }}.
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

