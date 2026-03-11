@extends('layouts.admin')

@section('title', 'Wallet Refund Requests')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Wallet Refund Requests</h1>
            <p class="text-muted mb-0">Special discount refund requests raised by exhibitors (no booth cancellation).</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Requests</h5>
            <form method="GET" class="d-flex align-items-center gap-2">
                <label class="mb-0 me-2">Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    @php $currentStatus = $status ?? 'pending'; @endphp
                    <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $currentStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $currentStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </form>
        </div>
        <div class="card-body p-0">
            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Exhibitor</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Processed By</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    <td>
                                        <strong>{{ $request->created_at->format('d M Y') }}</strong><br>
                                        <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $request->user->name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $request->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <strong>₹{{ number_format($request->amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($request->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($request->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 220px;" title="{{ $request->reason }}">
                                            {{ $request->reason ?: '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($request->processor)
                                            <strong>{{ $request->processor->name }}</strong><br>
                                            <small class="text-muted">{{ optional($request->processed_at)->format('d M Y, h:i A') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.wallet-refunds.show', $request->id) }}" class="btn btn-sm btn-outline-primary">
                                            View &amp; Process
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $requests->withQueryString()->links() }}
                </div>
            @else
                <div class="p-5 text-center text-muted">
                    No wallet refund requests found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

