@extends('layouts.admin')

@section('title', 'Exhibitor Profile')
@section('page-title', 'Exhibitor Profile')

@push('styles')
<style>
    .communication-container {
        display: flex;
        gap: 20px;
        height: 500px;
    }
    .communication-container .left-panel,
    .communication-container .center-panel,
    .communication-container .right-panel {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .communication-container .left-panel {
        width: 230px;
    }
    .communication-container .center-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .communication-container .right-panel {
        width: 380px;
        display: flex;
        flex-direction: column;
    }
    .communication-container .folder-list {
        list-style: none;
        padding: 0;
        margin: 10px 0 0 0;
    }
    .communication-container .folder-item {
        padding: 10px 12px;
        border-radius: 8px;
        margin-bottom: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        background: #f8fafc;
    }
    .communication-container .folder-item.active {
        background: #eef2ff;
        color: #4f46e5;
    }
    .communication-container .folder-count {
        background: #4f46e5;
        color: #fff;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.75rem;
    }
    .communication-container .message-list {
        flex: 1;
        overflow-y: auto;
    }
    .communication-container .message-item {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .communication-container .message-item:hover {
        background: #f9fafb;
    }
    .communication-container .message-item.active {
        background: #f0f9ff;
        border-left: 3px solid #6366f1;
    }
    .communication-container .message-avatar {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .communication-container .message-sender {
        font-weight: 600;
        font-size: 0.9rem;
    }
    .communication-container .message-subject {
        font-size: 0.85rem;
        color: #6b7280;
    }
    .communication-container .message-time {
        font-size: 0.8rem;
        color: #9ca3af;
    }
    .conversation-header {
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 10px;
    }
    .conversation-title {
        font-weight: 600;
    }
    .conversation-participants {
        font-size: 0.85rem;
        color: #6b7280;
    }
    .conversation-messages {
        flex: 1;
        overflow-y: auto;
        margin-bottom: 10px;
    }
    .message-bubble {
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
    }
    .message-bubble.admin-message {
        align-items: flex-end;
    }
    .message-bubble.exhibitor-message {
        align-items: flex-start;
    }
    .message-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 6px;
        padding: 0 4px;
    }
    .message-text {
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 0.95rem;
        line-height: 1.5;
        max-width: 75%;
        word-wrap: break-word;
    }
    .message-bubble.admin-message .message-text {
        background: #6366f1;
        color: #ffffff;
        border-bottom-right-radius: 4px;
    }
    .message-bubble.exhibitor-message .message-text {
        background: #f3f4f6;
        color: #1f2937;
        border-bottom-left-radius: 4px;
    }
    .reply-box {
        border-top: 1px solid #e5e7eb;
        padding-top: 10px;
        margin-top: 10px;
    }
    .reply-input {
        width: 100%;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 12px;
        font-size: 0.95rem;
        font-family: inherit;
        resize: none;
        min-height: 80px;
        margin-bottom: 10px;
        line-height: 1.5;
    }
    .reply-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .reply-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .btn-attach {
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 0.9rem;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-attach:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }
    .btn-send {
        padding: 8px 20px;
        border-radius: 8px;
        border: none;
        background: #6366f1;
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-send:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
</style>
@endpush

@section('content')
<ul class="nav nav-tabs mb-4" id="exhibitorTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button">Contact</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="booth-tab" data-bs-toggle="tab" data-bs-target="#booth" type="button">Booth</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button">Bookings</button>
    </li>
</ul>

<div class="tab-content" id="exhibitorTabContent">
    <div class="tab-pane fade show active" id="contact" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Contact Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.exhibitors.update-contact', $exhibitor->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $exhibitor->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ $exhibitor->email }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ $exhibitor->phone }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ $exhibitor->company_name }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ $exhibitor->city }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ $exhibitor->state }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ $exhibitor->country }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ $exhibitor->address }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Contact</button>
                </form>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="booth" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Booth Assignment</h5>
            </div>
            <div class="card-body">
                @if($bookings->count() > 0)
                @foreach($bookings as $booking)
                <form action="{{ route('admin.exhibitors.update-booth', $exhibitor->id) }}" method="POST" class="mb-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exhibition <span class="text-danger">*</span></label>
                            <select name="exhibition_id" class="form-select" required>
                                @foreach($exhibitions as $exhibition)
                                <option value="{{ $exhibition->id }}" {{ $booking->exhibition_id == $exhibition->id ? 'selected' : '' }}>{{ $exhibition->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Booth <span class="text-danger">*</span></label>
                            <select name="booth_id" class="form-select" required>
                                @foreach($booths as $booth)
                                <option value="{{ $booth->id }}" {{ $booking->booth_id == $booth->id ? 'selected' : '' }}>{{ $booth->name }} ({{ $booth->size_sqft }} sqft)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" step="0.01" min="0" class="form-control" value="{{ $booking->total_amount }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount</label>
                            <select name="discount_id" class="form-select">
                                <option value="">No Discount</option>
                                @foreach($discounts as $discount)
                                <option value="{{ $discount->id }}">{{ $discount->code }} ({{ $discount->type === 'percentage' ? $discount->amount . '%' : number_format($discount->amount, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Booth Assignment</button>
                </form>
                @endforeach
                @else
                <p class="text-muted">No bookings found for this exhibitor.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="bookings" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Booking History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking #</th>
                                <th>Exhibition</th>
                                <th>Booth</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_number }}</td>
                                <td>{{ $booking->exhibition->name ?? '-' }}</td>
                                <td>{{ $booking->booth->name ?? '-' }}</td>
                                <td>₹{{ number_format($booking->total_amount, 0) }}</td>
                                <td>
                                    <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadAdminConversation(exhibitorId) {
    // Scroll to conversation and highlight the clicked item
    document.getElementById('adminMessageDetail').scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Mark the clicked item as active
    document.querySelectorAll('.message-item').forEach(item => {
        item.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
}
</script>
@endpush
@endsection
