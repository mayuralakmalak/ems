@extends('layouts.frontend')

@section('title', 'My Wallet')

@section('content')
<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-2"><i class="bi bi-wallet2 me-2"></i>My Wallet</h2>
            <p class="text-muted">Manage your wallet balance and view transaction history</p>
        </div>
    </div>

    <!-- Wallet Balance Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white p-4">
                    <h6 class="text-white-50 mb-3">Current Balance</h6>
                    <h1 class="display-4 fw-bold mb-0">₹{{ number_format($balance, 2) }}</h1>
                    <small class="text-white-50">Available for booking payments</small>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Wallet Information</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Wallet balance can only be used for booking exhibition booths
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Balance is credited when admin processes cancellation refunds
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            All transactions are recorded and can be viewed below
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Transaction History</h5>
        </div>
        <div class="card-body">
            @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $transaction->created_at->format('d M Y') }}</strong><br>
                                    <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                @if($transaction->transaction_type === 'credit')
                                <span class="badge bg-success">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Credit
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-arrow-up-circle me-1"></i>Debit
                                </span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-{{ $transaction->transaction_type === 'credit' ? 'success' : 'danger' }}">
                                    {{ $transaction->transaction_type === 'credit' ? '+' : '-' }}₹{{ number_format($transaction->amount, 2) }}
                                </strong>
                            </td>
                            <td>{{ $transaction->description ?? 'N/A' }}</td>
                            <td>
                                @if($transaction->reference_type && $transaction->reference_id)
                                <span class="badge bg-info">
                                    {{ ucfirst($transaction->reference_type) }} #{{ $transaction->reference_id }}
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $transactions->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 60px;"></i>
                <p class="text-muted mt-3 mb-0">No transactions yet</p>
                <p class="text-muted">Your wallet transactions will appear here</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

