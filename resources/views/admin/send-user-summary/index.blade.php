@extends('layouts.admin')

@section('title', 'Send User Summary Email')
@section('page-title', 'Send User Summary Email')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-envelope-paper me-2"></i>Send Booking & Payment Summary to User</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Select an exhibition first, then choose a user who has booked in that exhibition. The user will receive one email with their booking(s) and payment information for that exhibition: Payment Total, Pending, and Paid amounts.</p>

                <form action="{{ route('admin.send-user-summary.send') }}" method="POST" id="sendSummaryForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="exhibition_id" class="form-label">Exhibition <span class="text-danger">*</span></label>
                            <select name="exhibition_id" id="exhibition_id" class="form-select" required>
                                <option value="">Select exhibition</option>
                                @foreach($exhibitions as $ex)
                                    <option value="{{ $ex->id }}">{{ $ex->name }} ({{ $ex->start_date?->format('M Y') ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-select" required disabled>
                                <option value="">Select exhibition first</option>
                            </select>
                            <small class="text-muted" id="userEmailHint"></small>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="sendBtn">
                                <i class="bi bi-send me-1"></i>Send Summary Email
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const exhibitionSelect = document.getElementById('exhibition_id');
    const userSelect = document.getElementById('user_id');
    const userEmailHint = document.getElementById('userEmailHint');
    const sendBtn = document.getElementById('sendBtn');
    const form = document.getElementById('sendSummaryForm');

    function updateUserHint() {
        const opt = userSelect.options[userSelect.selectedIndex];
        if (opt && opt.value) {
            userEmailHint.textContent = 'Email will be sent to: ' + (opt.getAttribute('data-email') || opt.textContent);
        } else {
            userEmailHint.textContent = '';
        }
    }

    userSelect.addEventListener('change', updateUserHint);
    updateUserHint();

    // Show users only after exhibition is selected (users who have bookings in that exhibition)
    exhibitionSelect.addEventListener('change', function() {
        const exhibitionId = this.value || '';
        userSelect.innerHTML = '<option value="">Select exhibition first</option>';
        userSelect.disabled = true;
        userEmailHint.textContent = '';
        if (!exhibitionId) return;
        userSelect.innerHTML = '<option value="">Loading...</option>';
        fetch('{{ route('admin.send-user-summary.users') }}?exhibition_id=' + encodeURIComponent(exhibitionId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            userSelect.innerHTML = '<option value="">Select user</option>';
            (data.users || []).forEach(function(u) {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.setAttribute('data-email', u.email);
                opt.textContent = u.name + ' (' + u.email + ') – ' + u.bookings_count + ' booking(s)';
                userSelect.appendChild(opt);
            });
            userSelect.disabled = false;
            updateUserHint();
        })
        .catch(function() {
            userSelect.innerHTML = '<option value="">Select exhibition first</option>';
            userSelect.disabled = true;
        });
    });

    form.addEventListener('submit', function() {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
    });
});
</script>
@endpush
@endsection
