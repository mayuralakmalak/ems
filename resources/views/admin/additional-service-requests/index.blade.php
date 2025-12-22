@extends('layouts.admin')

@section('title', 'Additional Service Requests')
@section('page-title', 'Additional Service Requests')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Additional Service Requests</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($requests->isEmpty())
            <p class="text-muted text-center py-4">No additional service requests</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Exhibitor</th>
                            <th>Exhibition</th>
                            <th>Service</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Requested At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                        <tr>
                            <td>
                                <a href="{{ route('admin.bookings.show', $request->booking_id) }}" class="text-decoration-none">
                                    {{ $request->booking->booking_number }}
                                </a>
                            </td>
                            <td>{{ $request->booking->user->name }}</td>
                            <td>{{ $request->booking->exhibition->name }}</td>
                            <td>{{ $request->service->name }}</td>
                            <td>{{ $request->quantity }}</td>
                            <td>₹{{ number_format($request->unit_price, 2) }}</td>
                            <td><strong>₹{{ number_format($request->total_price, 2) }}</strong></td>
                            <td>
                                @if($request->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($request->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @if($request->status === 'pending')
                                    <form action="{{ route('admin.additional-service-requests.approve', $request->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this request? A payment will be generated.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success mb-1">
                                            <i class="bi bi-check-circle"></i> Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-danger mb-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal"
                                            data-request-id="{{ $request->id }}">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                @elseif($request->status === 'approved')
                                    <small class="text-muted">Approved by {{ $request->approver->name ?? 'Admin' }}<br>{{ $request->approved_at->format('d M Y H:i') }}</small>
                                @elseif($request->status === 'rejected')
                                    <small class="text-muted">Rejected by {{ $request->approver->name ?? 'Admin' }}<br>{{ $request->approved_at->format('d M Y H:i') }}</small>
                                @endif
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
                <h5 class="modal-title">Reject Additional Service Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
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
document.addEventListener('DOMContentLoaded', function() {
    const rejectModal = document.getElementById('rejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-request-id');
            const form = document.getElementById('rejectForm');
            form.action = '{{ url("admin/additional-service-requests") }}/' + requestId + '/reject';
        });
    }
});
</script>
@endpush
@endsection

