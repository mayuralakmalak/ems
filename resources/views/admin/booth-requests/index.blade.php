@extends('layouts.admin')

@section('title', 'Booth Requests')
@section('page-title', 'Booth Requests - Pending Approvals')

@section('content')
<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Pending Requests</h5>
    </div>
    <div class="card-body">
        @if($requests->isEmpty())
            <p class="text-muted text-center">No pending requests</p>
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
                                <button class="btn btn-sm btn-success"
                                        data-approve-url="{{ url('admin/booth-requests/'.$request->id.'/approve') }}"
                                        onclick="approveRequest(this)">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                                <button class="btn btn-sm btn-danger"
                                        data-reject-url="{{ url('admin/booth-requests/'.$request->id.'/reject') }}"
                                        onclick="rejectRequest(this)">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
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
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endpush
@endsection

