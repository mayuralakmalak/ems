@extends('layouts.admin')

@section('title', 'Payment History')
@section('page-title', 'Payment History')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="stat-label">Total Approved</div>
            <div class="stat-value">{{ number_format($totalApproved) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="stat-label">Total Amount</div>
            <div class="stat-value">₹{{ number_format($totalAmount, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info">
            <div class="stat-label">Today's Approved</div>
            <div class="stat-value">{{ number_format($todayApproved) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="stat-label">Today's Amount</div>
            <div class="stat-value">₹{{ number_format($todayAmount, 2) }}</div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.payments.history') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Payment #, Transaction ID, Exhibitor, Exhibition...">
            </div>
            <div class="col-md-2">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <option value="">All Methods</option>
                    <option value="online" {{ request('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ request('payment_method') == 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="rtgs" {{ request('payment_method') == 'rtgs' ? 'selected' : '' }}>RTGS</option>
                    <option value="neft" {{ request('payment_method') == 'neft' ? 'selected' : '' }}>NEFT</option>
                    <option value="wallet" {{ request('payment_method') == 'wallet' ? 'selected' : '' }}>Wallet</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="payment_type" class="form-label">Payment Type</label>
                <select class="form-select" id="payment_type" name="payment_type">
                    <option value="">All Types</option>
                    <option value="initial" {{ request('payment_type') == 'initial' ? 'selected' : '' }}>Initial</option>
                    <option value="installment" {{ request('payment_type') == 'installment' ? 'selected' : '' }}>Installment</option>
                    <option value="full" {{ request('payment_type') == 'full' ? 'selected' : '' }}>Full</option>
                    <option value="refund" {{ request('payment_type') == 'refund' ? 'selected' : '' }}>Refund</option>
                    <option value="wallet_credit" {{ request('payment_type') == 'wallet_credit' ? 'selected' : '' }}>Wallet Credit</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['search', 'payment_method', 'payment_type', 'date_from', 'date_to']))
            <div class="col-md-12">
                <a href="{{ route('admin.payments.history') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Approved Payment History</h5>
        <div>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Approvals
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Exhibitor</th>
                        <th>Exhibition</th>
                        <th>Booth</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Type</th>
                        <th>Transaction ID</th>
                        <th>Payment Proof</th>
                        <th>Paid Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td><strong>{{ $payment->payment_number }}</strong></td>
                        <td>
                            <div>{{ $payment->booking->user->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $payment->booking->user->email ?? '' }}</small>
                        </td>
                        <td>{{ $payment->booking->exhibition->name ?? 'N/A' }}</td>
                        <td>
                            @if($payment->booking->booth)
                                <span class="badge bg-info">{{ $payment->booking->booth->name }}</span>
                            @elseif($payment->booking->selected_booth_ids)
                                @php
                                    $boothIds = is_array($payment->booking->selected_booth_ids) 
                                        ? collect($payment->booking->selected_booth_ids)->pluck('id')->filter()->all()
                                        : (is_array($payment->booking->selected_booth_ids) ? $payment->booking->selected_booth_ids : []);
                                    $booths = \App\Models\Booth::whereIn('id', $boothIds)->pluck('name');
                                @endphp
                                @if($booths->isNotEmpty())
                                    <span class="badge bg-info">{{ $booths->implode(', ') }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <strong>₹{{ number_format($payment->amount, 2) }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($payment->payment_method) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($payment->payment_type ?? 'N/A') }}</span>
                        </td>
                        <td>
                            @if($payment->transaction_id)
                                <code>{{ $payment->transaction_id }}</code>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->payment_proof_file)
                                <a href="{{ asset('storage/' . $payment->payment_proof_file) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            @elseif($payment->receipt_file)
                                <a href="{{ asset('storage/' . $payment->receipt_file) }}" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-receipt"></i> Receipt
                                </a>
                            @else
                                <span class="text-muted">No proof</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->paid_at)
                                <div>{{ $payment->paid_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $payment->updated_at->format('h:i A') }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2">No approved payments found</p>
                            @if(request()->hasAny(['search', 'payment_method', 'payment_type', 'date_from', 'date_to']))
                                <a href="{{ route('admin.payments.history') }}" class="btn btn-sm btn-outline-primary mt-2">
                                    Clear Filters
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $payments->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
