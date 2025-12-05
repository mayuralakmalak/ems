@extends('layouts.exhibitor')

@section('title', 'My Bookings')
@section('page-title', 'My Bookings')

@push('styles')
<style>
    .bookings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .filter-tab {
        padding: 12px 24px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        bottom: -2px;
    }
    
    .filter-tab:hover {
        color: #6366f1;
    }
    
    .filter-tab.active {
        color: #6366f1;
        border-bottom-color: #6366f1;
    }
    
    .search-box {
        position: relative;
        max-width: 300px;
    }
    
    .search-box input {
        padding: 10px 40px 10px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        width: 100%;
    }
    
    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
    }
    
    .bookings-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .bookings-table table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .bookings-table thead {
        background: #f8fafc;
    }
    
    .bookings-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .bookings-table td {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .bookings-table tbody tr:hover {
        background: #f8fafc;
    }
    
    .bookings-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-completed {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .status-confirmed {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-waiting {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .status-payment-due {
        background: #fef3c7;
        color: #92400e;
    }
    
    .btn-action {
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-right: 5px;
    }
    
    .btn-view {
        background: #6366f1;
        color: white;
    }
    
    .btn-view:hover {
        background: #4f46e5;
    }
    
    .btn-modify {
        background: #f3f4f6;
        color: #1e293b;
    }
    
    .btn-modify:hover {
        background: #e5e7eb;
    }
    
    .btn-cancel {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .btn-cancel:hover {
        background: #fecaca;
    }
    
    .exhibition-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 5px;
    }
    
    .booth-number {
        color: #64748b;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="bookings-header">
    <h2 class="mb-0">My Bookings</h2>
</div>

<div class="filter-tabs">
    <button class="filter-tab {{ request('status') == 'all' || !request('status') ? 'active' : '' }}" onclick="filterBookings('all')">
        All
    </button>
    <button class="filter-tab {{ request('status') == 'active' ? 'active' : '' }}" onclick="filterBookings('active')">
        Active
    </button>
    <button class="filter-tab {{ request('status') == 'completed' ? 'active' : '' }}" onclick="filterBookings('completed')">
        Completed
    </button>
    <button class="filter-tab {{ request('status') == 'cancelled' ? 'active' : '' }}" onclick="filterBookings('cancelled')">
        Cancelled
    </button>
    <button class="filter-tab {{ request('status') == 'pending' ? 'active' : '' }}" onclick="filterBookings('pending')">
        Pending
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <form method="GET" action="{{ route('bookings.index') }}" class="search-box">
        <input type="text" name="search" placeholder="Search bookings..." value="{{ request('search') }}">
        <i class="bi bi-search"></i>
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
    </form>
</div>

<div class="bookings-table">
    <table>
        <thead>
            <tr>
                <th>Exhibition Name</th>
                <th>Booth No.</th>
                <th>Booking Date</th>
                <th>Status</th>
                <th>Total Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
            <tr>
                <td>
                    <div class="exhibition-name">{{ $booking->exhibition->name }}</div>
                </td>
                <td>{{ $booking->booth->name ?? 'N/A' }}</td>
                <td>{{ $booking->created_at->format('Y-m-d') }}</td>
                <td>
                    @php
                        $statusClass = 'status-pending';
                        $statusText = 'Pending';
                        
                        if ($booking->status === 'confirmed' && $booking->exhibition->end_date < now()) {
                            $statusClass = 'status-completed';
                            $statusText = 'Completed';
                        } elseif ($booking->status === 'confirmed') {
                            $statusClass = 'status-confirmed';
                            $statusText = 'Booking Confirmed';
                        } elseif ($booking->status === 'cancelled') {
                            $statusClass = 'status-waiting';
                            $statusText = 'Cancelled';
                        } elseif ($booking->approval_status === 'pending') {
                            $statusClass = 'status-waiting';
                            $statusText = 'Waiting for Approval';
                        } elseif ($booking->paid_amount < $booking->total_amount) {
                            $statusClass = 'status-payment-due';
                            $statusText = $booking->paid_amount == 0 ? 'First Payment Pending' : 'Payment Due';
                        }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                </td>
                <td>₹{{ number_format($booking->total_amount, 2) }}</td>
                <td>
                    <a href="{{ route('bookings.show', $booking->id) }}" class="btn-action btn-view">View Details</a>
                    @if($booking->status === 'confirmed' && $booking->exhibition->end_date >= now())
                        <a href="{{ route('bookings.show', $booking->id) }}" class="btn-action btn-modify">Modify</a>
                        <a href="{{ route('bookings.cancel.show', $booking->id) }}" class="btn-action btn-cancel">Cancel</a>
                    @elseif($booking->approval_status === 'pending')
                        <a href="{{ route('bookings.show', $booking->id) }}" class="btn-action btn-modify">Modify</a>
                        <a href="{{ route('bookings.cancel.show', $booking->id) }}" class="btn-action btn-cancel">Cancel</a>
                    @elseif($booking->paid_amount < $booking->total_amount)
                        <a href="{{ route('bookings.show', $booking->id) }}" class="btn-action btn-modify">Modify</a>
                        <a href="{{ route('bookings.cancel.show', $booking->id) }}" class="btn-action btn-cancel">Cancel</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <p class="text-muted mt-3">No bookings found</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($bookings->hasPages())
<div class="mt-4">
    {{ $bookings->links() }}
</div>
@endif

@push('scripts')
<script>
function filterBookings(status) {
    const url = new URL(window.location.href);
    url.searchParams.set('status', status);
    window.location.href = url.toString();
}
</script>
@endpush
@endsection

