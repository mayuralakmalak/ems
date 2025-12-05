@extends('layouts.admin')

@section('title', 'Admin Role Management 5')
@section('page-title', 'Admin Role Management 5')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Admin Role Management 5</h4>
            <span class="text-muted">29 / 36</span>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5>Admin Panel</h5>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-primary me-3">Dashboard</a>
                <a href="{{ route('admin.roles.index') }}" class="text-primary me-3">Roles</a>
                <a href="{{ route('admin.exhibitions.index') }}" class="text-primary me-3">Exhibitions</a>
                <a href="{{ route('admin.payments.index') }}" class="text-primary">Payments</a>
            </div>
        </div>
    </div>
</div>

<!-- Payment Operations Section -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Payment Management</h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">A section dedicated to handling all payment-related operations and records.</p>
        
        <div class="d-flex gap-3">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-primary">
                View Payments
            </a>
            <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
                Add New Payment
            </a>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                Payment Settings
            </a>
        </div>
    </div>
</div>

<!-- Recent Transactions Section -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Recent Transactions</h5>
    </div>
    <div class="card-body">
        @forelse($recentTransactions as $transaction)
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <div>
                <strong>Transaction ID: #{{ $transaction->id }}</strong>
                <br>
                <small class="text-muted">
                    Amount: ₹{{ number_format($transaction->amount, 2) }} - 
                    Date: {{ $transaction->payment_date ? \Carbon\Carbon::parse($transaction->payment_date)->format('Y-m-d') : $transaction->created_at->format('Y-m-d') }}
                </small>
                @if($transaction->booking)
                    <br>
                    <small class="text-muted">Booking: {{ $transaction->booking->booking_number }}</small>
                @endif
            </div>
            <div>
                <a href="{{ route('admin.payments.show', $transaction->id) }}" class="btn btn-primary btn-sm">
                    Details
                </a>
            </div>
        </div>
        @empty
        <p class="text-muted mb-0">No recent transactions found.</p>
        @endforelse
    </div>
</div>
@endsection
