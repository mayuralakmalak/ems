@extends('layouts.admin')

@section('title', 'Financial Overview')
@section('page-title', 'Financial Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-1"><i class="bi bi-cash-coin me-2"></i>Financial Overview</h2>
        <p class="text-muted mb-0">Track payments, revenue and outstanding dues across all exhibitions</p>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="stat-card primary bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Total Revenue (Completed Payments)</h6>
            <h2 class="mb-0 text-primary">₹{{ number_format($totalRevenue, 0) }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card warning bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Estimated Outstanding Amount</h6>
            <h2 class="mb-0 text-warning">₹{{ number_format($pendingAmount, 0) }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card success bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Total Completed Payments</h6>
            <h2 class="mb-0 text-success">{{ $recentPayments->where('status', 'completed')->count() }}</h2>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Revenue by Payment Method</h5>
            </div>
            <div class="card-body">
                @if($byMethod->isEmpty())
                    <p class="text-muted mb-0">No payment data available yet.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($byMethod as $row)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ ucfirst($row->payment_method) }}</strong><br>
                                <small class="text-muted">{{ $row->count }} payments</small>
                            </div>
                            <span>₹{{ number_format($row->total, 0) }}</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Payments</h5>
            </div>
            <div class="card-body">
                @if($recentPayments->isEmpty())
                    <p class="text-muted mb-0">No payments recorded yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Payment #</th>
                                    <th>Booking</th>
                                    <th>Type</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_number }}</td>
                                    <td>{{ $payment->booking->booking_number ?? '-' }}</td>
                                    <td>{{ ucfirst($payment->payment_type) }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($payment->amount, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


