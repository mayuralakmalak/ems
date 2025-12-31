@extends('layouts.admin')

@section('title', 'Payment Approvals')
@section('page-title', 'Payment Approvals')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="stat-label">Pending Approvals</div>
            <div class="stat-value">{{ $pendingCount }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="stat-label">Approved</div>
            <div class="stat-value">{{ $approvedCount }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="stat-label">Rejected</div>
            <div class="stat-value">{{ $rejectedCount }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payment Approvals</h5>
        <div class="d-flex gap-2">
            <div class="btn-group">
                <a href="{{ route('admin.payments.index', ['approval_status' => 'pending']) }}" class="btn btn-sm {{ request('approval_status') == 'pending' || !request('approval_status') ? 'btn-primary' : 'btn-outline-primary' }}">Pending</a>
                <a href="{{ route('admin.payments.index', ['approval_status' => 'approved']) }}" class="btn btn-sm {{ request('approval_status') == 'approved' ? 'btn-primary' : 'btn-outline-primary' }}">Approved</a>
                <a href="{{ route('admin.payments.index', ['approval_status' => 'rejected']) }}" class="btn btn-sm {{ request('approval_status') == 'rejected' ? 'btn-primary' : 'btn-outline-primary' }}">Rejected</a>
            </div>
            <a href="{{ route('admin.payments.history') }}" class="btn btn-sm btn-success">
                <i class="bi bi-clock-history"></i> Payment History
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
                        <th>Payment Proof</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td><strong>{{ $payment->payment_number }}</strong></td>
                        <td>{{ $payment->booking->user->name ?? 'N/A' }}</td>
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
                        <td>₹{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>
                            @if($payment->payment_proof_file)
                                <a href="{{ asset('storage/' . $payment->payment_proof_file) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View Proof
                                </a>
                            @else
                                <span class="text-muted">No proof</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $payment->approval_status === 'approved' ? 'success' : ($payment->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($payment->approval_status) }}
                            </span>
                        </td>
                        <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">No payments found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
