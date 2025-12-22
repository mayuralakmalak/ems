@extends('layouts.admin')

@section('title', 'Admin-booking and cancellation management')
@section('page-title', 'Admin-booking and cancellation management')

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
    
    .cancellation-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .tab {
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
    
    .tab:hover {
        color: #6366f1;
    }
    
    .tab.active {
        color: #6366f1;
        border-bottom-color: #6366f1;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .detail-item {
        padding: 15px;
        background: #f8fafc;
        border-radius: 8px;
    }
    
    .detail-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .detail-link {
        color: #6366f1;
        text-decoration: none;
    }
    
    .detail-link:hover {
        text-decoration: underline;
    }
    
    .charges-box {
        background: #fef3c7;
        border: 1px solid #fbbf24;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
    }
    
    .charges-label {
        color: #92400e;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .charges-amount {
        font-size: 1.2rem;
        font-weight: 700;
        color: #ef4444;
    }
    
    .message-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .message-title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 10px;
    }
    
    .message-content {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }
    
    .btn-reject {
        background: #ef4444;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .btn-approve {
        background: #6366f1;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .btn-save {
        background: #f3f4f6;
        color: #1e293b;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .insights-section {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-top: 30px;
    }
    
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
    }
    
    .chart-placeholder {
        height: 300px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<!-- Summary Cards -->
<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-label">Total Bookings</div>
        <div class="summary-value">{{ number_format($totalBookings) }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Pending Cancellations</div>
        <div class="summary-value">{{ $pendingCancellations }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Approved Refunds</div>
        <div class="summary-value">₹{{ number_format($approvedRefunds, 2) }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Cancellation Charges</div>
        <div class="summary-value">₹{{ number_format($cancellationCharges, 2) }}</div>
    </div>
</div>

<!-- Cancellation Requests List -->
<div class="cancellation-section">
    <div class="section-header">
        <h3 class="section-title">Cancellation Requests</h3>
    </div>
    
    @if($cancellationRequests->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Exhibitor</th>
                        <th>Exhibition</th>
                        <th>Booth</th>
                        <th>Requested On</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cancellationRequests as $booking)
                        @php
                            $isProcessed = !is_null($booking->cancellation_type);
                            $statusLabel = $isProcessed ? 'Processed' : 'Pending Review';
                            $statusClass = $isProcessed ? 'bg-success' : 'bg-warning';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="detail-link">
                                    {{ $booking->booking_number }}
                                </a>
                            </td>
                            <td>
                                {{ $booking->user->name ?? '-' }}<br>
                                <small class="text-muted">{{ $booking->user->email ?? '' }}</small>
                            </td>
                            <td>{{ $booking->exhibition->name ?? '-' }}</td>
                            <td>{{ $booking->booth->name ?? '-' }}</td>
                            <td>{{ $booking->updated_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <span class="text-muted" title="{{ $booking->cancellation_reason }}">
                                    {{ \Illuminate\Support\Str::limit($booking->cancellation_reason ?? 'N/A', 40) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                    View &amp; Process
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted text-center py-5">No cancellation requests found</p>
    @endif
</div>

<!-- Cancellation & Refund Insights -->
<div class="insights-section">
    <div class="chart-container">
        <h5 class="chart-title">Cancellation Reasons</h5>
        <div class="chart-placeholder">
            Breakdown of cancellation requests by reason
        </div>
    </div>
    
    <div class="chart-container">
        <h5 class="chart-title">Refund Status Distribution</h5>
        <div class="chart-placeholder">
            Breakdown of refund statuses for processed cancellations
        </div>
    </div>
</div>
@endsection

