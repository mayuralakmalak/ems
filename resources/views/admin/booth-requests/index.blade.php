@extends('layouts.admin')

@section('title', 'Booth Requests')
@section('page-title', 'Booth Requests - Pending Approvals')

@section('content')
<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Pending Requests</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.booth-requests.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Exhibition</label>
                <select name="exhibition_id" class="form-select">
                    <option value="">All Exhibitions</option>
                    @foreach($exhibitions as $exhibition)
                        <option value="{{ $exhibition->id }}" {{ (string) $exhibition->id === request('exhibition_id') ? 'selected' : '' }}>
                            {{ $exhibition->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @php
                        $currentStatus = request('status', 'pending');
                    @endphp
                    <option value="all" {{ $currentStatus === 'all' ? 'selected' : '' }}>All</option>
                    <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $currentStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $currentStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">User Name</label>
                <input type="text" name="user_name" class="form-control" value="{{ request('user_name') }}" placeholder="Search by user name">
            </div>
            <div class="col-md-2">
                <label class="form-label">Booth Number</label>
                <input type="text" name="booth_number" class="form-control" value="{{ request('booth_number') }}" placeholder="Search by booth no.">
            </div>
            <div class="col-md-2 d-flex align-items-end justify-content-end gap-2">
                <a href="{{ route('admin.booth-requests.index') }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                    Reset
                </a>
                <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                    Filter
                </button>
                @can('Booth Request Management - Download')
                <button type="submit" name="export" value="1" class="btn btn-success btn-sm text-nowrap">
                    Export
                </button>
                @endcan
            </div>
        </form>

        @if($requests->isEmpty())
            <p class="text-muted text-center">No booth requests found</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Request Type</th>
                            <th>Exhibition</th>
                            <th>User</th>
                            <th>Booths</th>
                            <th>Description</th>
                            <th>Requested At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                        @php
                            $booking = $request->booking;
                            $bookingLabel = $booking
                                ? ($booking->booking_number ?? ('Booking #'.$booking->id))
                                : null;
                            $paymentsForButton = [];
                            if ($booking && $booking->payments && $booking->payments->count()) {
                                foreach ($booking->payments as $payment) {
                                    $statusLabel = ucfirst($payment->status ?? 'pending');
                                    if ($payment->status === 'pending' && $payment->approval_status === 'pending') {
                                        $statusLabel = 'Pending (waiting for approval)';
                                    } elseif ($payment->status === 'completed' && $payment->approval_status === 'pending') {
                                        $statusLabel = 'Completed (awaiting admin approval)';
                                    } elseif ($payment->status === 'completed' && $payment->approval_status === 'approved') {
                                        $statusLabel = 'Completed & approved';
                                    } elseif ($payment->approval_status === 'rejected') {
                                        $statusLabel = 'Rejected';
                                    }
                                    $gatewayCharge = (float) ($payment->gateway_charge ?? 0);
                                    $totalWithGateway = $payment->amount + $gatewayCharge;
                                    $paymentsForButton[] = [
                                        'label' => $payment->payment_number ?? ('Payment #'.$payment->id),
                                        'amount' => number_format($payment->amount, 2, '.', ''),
                                        'gateway_charge' => number_format($gatewayCharge, 2, '.', ''),
                                        'total_with_gateway' => number_format($totalWithGateway, 2, '.', ''),
                                        'status' => $statusLabel,
                                    ];
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                <span class="badge bg-{{ $request->request_type === 'merge' ? 'primary' : ($request->request_type === 'split' ? 'warning' : 'success') }}">
                                    {{ ucfirst($request->request_type) }}
                                </span>
                            </td>
                            <td>{{ $request->exhibition->name }}</td>
                            <td>{{ $request->user->name ?? 'Admin' }}</td>
                            <td>
                                @foreach($request->booths() as $booth)
                                    <span class="badge bg-secondary">{{ $booth->name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $request->description ?? 'N/A' }}</td>
                            <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @can('Booth Request Management - View')
                                <a class="btn btn-sm btn-info me-1" href="{{ route('admin.booth-requests.show', $request->id) }}" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @endcan
                                @can('Booth Request Management - Modify')
                                <button class="btn btn-sm btn-success me-1"
                                        data-approve-url="{{ url('admin/booth-requests/'.$request->id.'/approve') }}"
                                        onclick="approveRequest(this)"
                                        title="Approve">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-danger"
                                        data-reject-url="{{ url('admin/booth-requests/'.$request->id.'/reject') }}"
                                        data-request-id="{{ $request->id }}"
                                        data-paid-amount="{{ $booking ? number_format($booking->paid_amount, 2, '.', '') : '0.00' }}"
                                        data-booking-label="{{ $bookingLabel }}"
                                        data-payments='@json($paymentsForButton)'
                                        onclick="rejectRequest(this)"
                                        title="Reject">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $requests->links() }}
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="rejectPaymentInfo" class="alert alert-warning py-2 px-3 small d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason *</label>
                        <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function approveRequest(btn) {
    const approveUrl = btn.getAttribute('data-approve-url');
    if (confirm('Are you sure you want to approve this request?')) {
        fetch(approveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Network response was not ok');
                }).catch(() => {
                    throw new Error('Network response was not ok');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error approving request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error approving request: ' + error.message);
        });
    }
}

function rejectRequest(btn) {
    const form = document.getElementById('rejectForm');
    form.action = btn.getAttribute('data-reject-url');
    
    const paymentInfoEl = document.getElementById('rejectPaymentInfo');
    const paidAmountRaw = btn.getAttribute('data-paid-amount') || '0';
    const paidAmount = parseFloat(paidAmountRaw);
    const bookingLabel = btn.getAttribute('data-booking-label') || 'this booking';
    const paymentsJson = btn.getAttribute('data-payments') || '[]';
    let payments = [];
    try {
        payments = JSON.parse(paymentsJson);
    } catch (e) {
        payments = [];
    }

    if (paymentInfoEl) {
        if (!isNaN(paidAmount) && paidAmount > 0) {
            paymentInfoEl.classList.remove('d-none');
            let html = '';
            html += '<p class="mb-2">Booking <strong>' + bookingLabel + '</strong> has already received payments totalling <strong>₹' +
                paidAmount.toFixed(2) + '</strong>.</p>';

            if (Array.isArray(payments) && payments.length > 0) {
                html += '<table class="table table-sm table-bordered mb-2">';
                html += '<thead><tr><th>Payment</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
                payments.forEach(function (p) {
                    var amt = parseFloat(p.amount || 0);
                    var gw = parseFloat(p.gateway_charge || 0);
                    var total = parseFloat(p.total_with_gateway || 0) || (amt + gw);
                    var amountText = '₹' + amt.toFixed(2);
                    if (gw > 0) {
                        amountText += ' + ₹' + gw.toFixed(2) + ' gateway = ₹' + total.toFixed(2);
                    }
                    html += '<tr>';
                    html += '<td>' + (p.label || '') + '</td>';
                    html += '<td>' + amountText + '</td>';
                    html += '<td>' + (p.status || '') + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
            }

            html += '<p class="mb-0 mt-2">Please ensure any refund or follow-up is handled before confirming rejection.</p>';
            paymentInfoEl.innerHTML = html;
        } else {
            paymentInfoEl.classList.add('d-none');
            paymentInfoEl.innerHTML = '';
        }
    }

    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

</script>
@endpush
@endsection

