@extends('layouts.exhibitor')

@section('title', 'Payment Management')
@section('page-title', 'Payment Management')

@push('styles')
<style>
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .summary-label {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 10px;
    }
    
    .summary-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .section-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
    }
    
    .payment-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .payment-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .payment-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .payment-table tr:last-child td {
        border-bottom: none;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-failed {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .btn-download {
        padding: 6px 16px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-download:hover {
        background: #4f46e5;
    }
    
    .btn-pay-now {
        padding: 6px 16px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .btn-pay-now:hover {
        background: #059669;
        color: white;
        text-decoration: none;
    }
    
    .wallet-transaction {
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .wallet-transaction:last-child {
        border-bottom: none;
    }
    
    .transaction-date {
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .transaction-amount {
        font-weight: 600;
        color: #10b981;
    }
    
    .wallet-note {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 15px;
        font-style: italic;
    }
    
    .gateway-select {
        margin-top: 15px;
    }
    
    .gateway-select label {
        font-size: 0.9rem;
        color: #64748b;
        margin-right: 10px;
    }
    
    .gateway-select select {
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
    }
</style>
@endpush

@section('content')
<!-- Summary Cards -->
<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-label">Outstanding Balance</div>
        <div class="summary-value">₹{{ number_format($outstandingBalance, 2) }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Total Paid</div>
        <div class="summary-value">₹{{ number_format($totalPaid, 2) }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Pending</div>
        <div class="summary-value">₹{{ number_format($pending, 2) }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Overdue</div>
        <div class="summary-value">₹{{ number_format($overdue, 2) }}</div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Payment History -->
        <div class="section-card">
            <h5 class="section-title">Payment</h5>
            
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_number }}</td>
                        <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                        <td>
                            @if($payment->due_date)
                                {{ $payment->due_date->format('Y-m-d') }}
                                @if($payment->due_date < now() && $payment->status === 'pending')
                                    <span class="text-danger" style="font-size: 0.75rem; display: block;">(Overdue)</span>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $payment->booking->exhibition->name ?? 'Stall Booking' }}</td>
                        <td>₹{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>
                            @php
                                $displayStatus = $payment->status;
                                $statusClass = 'status-pending';
                                
                                if ($payment->status === 'completed') {
                                    $displayStatus = 'completed';
                                    $statusClass = 'status-completed';
                                } elseif ($payment->status === 'failed') {
                                    $displayStatus = 'failed';
                                    $statusClass = 'status-failed';
                                } elseif ($payment->status === 'pending' && $payment->payment_proof_file && $payment->approval_status === 'pending') {
                                    $displayStatus = 'waiting for approval';
                                    $statusClass = 'status-pending'; // Keep pending style but show different text
                                } else {
                                    $displayStatus = 'pending';
                                    $statusClass = 'status-pending';
                                }
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ ucfirst($displayStatus) }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                @php
                                    // Show Pay Now only for pending payments WITHOUT payment proof (not waiting for approval)
                                    $canPay = $payment->status === 'pending' && !$payment->payment_proof_file;
                                @endphp
                                
                                @if($canPay)
                                    <a href="{{ route('payments.pay', $payment->id) }}" class="btn-pay-now" style="text-decoration: none; display: inline-block;">
                                        <i class="bi bi-credit-card me-1"></i>Pay Now
                                    </a>
                                @endif
                                
                                @if($payment->receipt_file)
                                    <a href="{{ asset('storage/' . $payment->receipt_file) }}" download class="btn-download" style="text-decoration: none; display: inline-block;">
                                        <i class="bi bi-download me-1"></i>Receipt
                                    </a>
                                @elseif($payment->invoice_file)
                                    <a href="{{ asset('storage/' . $payment->invoice_file) }}" download class="btn-download" style="text-decoration: none; display: inline-block;">
                                        <i class="bi bi-download me-1"></i>Invoice
                                    </a>
                                @elseif($payment->status === 'completed')
                                    <a href="{{ route('payments.download', $payment->id) }}" class="btn-download" style="text-decoration: none; display: inline-block;">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">No payments found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($payments->hasPages())
            <div class="mt-3">
                {{ $payments->links() }}
            </div>
            @endif
        </div>
        
        <!-- Upcoming Payments -->
        <div class="section-card">
            <h5 class="section-title">Upcoming Payments</h5>
            
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Due Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($upcomingPayments as $payment)
                    <tr>
                        <td>{{ $payment->due_date ? $payment->due_date->format('Y-m-d') : 'N/A' }}</td>
                        <td>{{ $payment->booking->exhibition->name ?? 'Booth Upgrade' }}</td>
                        <td>₹{{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <a href="{{ route('payments.create', $payment->booking_id) }}" class="btn-pay-now">Pay Now</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No upcoming payments</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($upcomingPayments->count() > 0)
            <div class="gateway-select">
                <label>Select Payment Gateway:</label>
                <select class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option>Credit Card</option>
                    <option>UPI</option>
                    <option>Net Banking</option>
                    <option>Wallet</option>
                </select>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Wallet Balance -->
        <div class="section-card">
            <h5 class="section-title">Wallet Balance</h5>
            
            <div class="mb-3">
                <div class="summary-label">Current Balance:</div>
                <div class="summary-value" style="font-size: 1.5rem;">₹{{ number_format($walletBalance, 2) }}</div>
            </div>
            
            @if($walletTransactions->count() > 0)
            <div class="mt-4">
                @foreach($walletTransactions as $transaction)
                <div class="wallet-transaction">
                    <div class="transaction-date">{{ $transaction->created_at->format('Y-m-d') }}</div>
                    <div class="mt-2">
                        <strong>{{ $transaction->description }}</strong>
                    </div>
                    <div class="transaction-amount mt-1">
                        +₹{{ number_format($transaction->amount, 2) }}
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            
            <div class="wallet-note">
                Note: Wallet amount can only be used for booking stalls.
            </div>
        </div>
    </div>
</div>
@endsection

