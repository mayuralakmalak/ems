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

<!-- Cancellation Request Details -->
<div class="cancellation-section">
    <div class="section-header">
        <h3 class="section-title">Cancellation Request Details</h3>
        <button class="btn btn-primary">
            <i class="bi bi-plus me-2"></i>+ New Request
        </button>
    </div>
    
    @if($cancellationRequests->count() > 0)
        @foreach($cancellationRequests->take(1) as $booking)
        <div>
            <h4 class="mb-4">Manage Cancellation {{ $booking->booking_number }}</h4>
            
            <div class="tabs">
                <button class="tab active">Cancellation Details</button>
                <button class="tab">Booking Details</button>
                <button class="tab">Communication History</button>
                <button class="tab">Audit Log</button>
            </div>
            
            <div class="details-grid">
                <div>
                    <div class="detail-item">
                        <div class="detail-label">Booking ID</div>
                        <div class="detail-value">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="detail-link">
                                {{ $booking->booking_number }}
                            </a>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Booking Date</div>
                        <div class="detail-value">{{ $booking->created_at->format('Y.m.d') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Assigned Booth</div>
                        <div class="detail-value">
                            <a href="#" class="detail-link">
                                {{ $booking->booth->name ?? 'N/A' }}
                            </a>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Payment Status</div>
                        <div class="detail-value">
                            <a href="#" class="detail-link">
                                {{ $booking->paid_amount >= $booking->total_amount ? 'Paid in Full' : 'Partial' }}
                            </a>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Request Date/Time</div>
                        <div class="detail-value">{{ $booking->updated_at->format('Y.m.d h:i A') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Cancellation Charges</div>
                        <div class="detail-value">₹{{ number_format(($booking->total_amount * 15) / 100, 2) }} (15% of booking)</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Approval Status</div>
                        <div class="detail-value">{{ $booking->cancellation_type ? 'Approved' : 'Not Approved' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Cancellation Reason</div>
                        <div class="detail-value">{{ $booking->cancellation_reason ?? 'N/A' }}</div>
                    </div>
                </div>
                
                <div>
                    <div class="detail-item">
                        <div class="detail-label">Exhibitor ID</div>
                        <div class="detail-value">
                            <a href="#" class="detail-link">{{ $booking->user->email }}</a>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Booking Time</div>
                        <div class="detail-value">{{ $booking->created_at->format('h:i A') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Booking Status</div>
                        <div class="detail-value">
                            <a href="#" class="detail-link">{{ ucfirst($booking->status) }}</a>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Cancellation Request ID</div>
                        <div class="detail-value">{{ $booking->booking_number }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Cancellation Status</div>
                        <div class="detail-value">
                            <a href="#" class="detail-link">Pending Approval</a>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Refund Amount</div>
                        <div class="detail-value">₹{{ number_format($booking->total_amount - (($booking->total_amount * 15) / 100), 2) }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Refund Processed Date</div>
                        <div class="detail-value">{{ $booking->cancellation_type ? $booking->updated_at->format('Y.m.d') : 'Not Processed' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="charges-box">
                <div class="charges-label">Cancellation Charges:</div>
                <div class="charges-amount">₹{{ number_format(($booking->total_amount * 15) / 100, 2) }} (15% of booking)</div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="replacementBooking">
                    <label class="form-check-label" for="replacementBooking">
                        Register Replacement Booking Opportunity
                    </label>
                </div>
            </div>
            
            <div class="message-box">
                <div class="message-title">Exhibitor Cancellation Message (with attachment)</div>
                <div class="message-content">
                    We have received your cancellation request for Booking ID {{ $booking->booking_number }}. 
                    Your request is currently under review. The refund amount is ₹{{ number_format($booking->total_amount - (($booking->total_amount * 15) / 100), 2) }} 
                    will apply, resulting in a refund of ₹{{ number_format($booking->total_amount - (($booking->total_amount * 15) / 100), 2) }}. 
                    We will notify you once your request has been processed.
                </div>
            </div>
            
            <div class="message-box">
                <div class="message-title">Admin Internal Notes</div>
                <textarea class="form-control" rows="3" placeholder="Contact exhibitor to confirm details. Meeting manager approval needed for a replacement booking in appropriate quality."></textarea>
            </div>
            
            <div class="action-buttons">
                <form method="POST" action="{{ route('admin.bookings.reject-cancellation', $booking->id) }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="rejection_reason" value="Admin rejected cancellation request">
                    <button type="submit" class="btn-reject">Reject Cancellation</button>
                </form>
                <a href="{{ route('admin.bookings.manage-cancellation', $booking->id) }}" class="btn-approve">Approve Cancellation</a>
                <button class="btn-save">Save Notes</button>
            </div>
        </div>
        @endforeach
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

