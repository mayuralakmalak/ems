@extends('layouts.exhibitor')

@section('title', 'Exhibitor Dashboard')

@push('styles')
<style>
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .stat-card.wallet-card {
        border: 2px solid #6366f1;
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 10px;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 5px;
    }
    
    .stat-value.wallet-value {
        color: #6366f1;
    }
    
    .stat-hint {
        font-size: 0.85rem;
        color: #94a3b8;
    }
    
    .section-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 25px;
        height: 100%;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .section-title i {
        margin-right: 10px;
        color: #6366f1;
    }
    
    .activity-item {
        padding: 15px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-message {
        color: #1e293b;
        font-weight: 500;
        margin-bottom: 5px;
    }
    
    .activity-time {
        color: #64748b;
        font-size: 0.85rem;
    }
    
    .quick-action-btn {
        width: 100%;
        padding: 15px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-align: center;
        transition: all 0.3s ease;
        text-decoration: none;
        color: #1e293b;
        display: block;
        margin-bottom: 15px;
    }
    
    .quick-action-btn:hover {
        border-color: #6366f1;
        background: #f8fafc;
        transform: translateY(-2px);
        color: #6366f1;
    }
    
    .quick-action-btn i {
        font-size: 2rem;
        display: block;
        margin-bottom: 10px;
        color: #6366f1;
    }
    
    .quick-action-label {
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    .payment-table {
        width: 100%;
    }
    
    .payment-table th {
        background: #f8fafc;
        padding: 12px;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .payment-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .payment-table tr:last-child td {
        border-bottom: none;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-paid {
        background: #d1fae5;
        color: #065f46;
    }
    
    .btn-pay-now {
        padding: 6px 16px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-pay-now:hover {
        background: #4f46e5;
    }
    
    .checklist-item {
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .checklist-item:last-child {
        border-bottom: none;
    }
    
    .checklist-info {
        flex: 1;
    }
    
    .checklist-name {
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 5px;
    }
    
    .checklist-due {
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .checklist-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .due-date-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .due-date-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .due-date-badge {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 12px;
        display: inline-block;
        width: fit-content;
    }
    
    .due-date-urgent {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .due-date-soon {
        background: #fef3c7;
        color: #92400e;
    }
    
    .due-date-normal {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .payment-type-badge {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 12px;
        background: #e0e7ff;
        color: #4338ca;
        font-weight: 500;
        display: inline-block;
    }
    
    .top-bar-user {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }
    
    .user-info {
        display: flex;
        flex-direction: column;
    }
    
    .user-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }
    
    .user-role {
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .notification-icon, .message-icon {
        position: relative;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .notification-icon:hover, .message-icon:hover {
        background: #e2e8f0;
    }
    
    .notification-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        width: 18px;
        height: 18px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
</style>
@endpush

@section('page-title', 'Dashboard')

@section('content')
<!-- Stat Cards -->
<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Active Bookings</div>
            <div class="stat-value">{{ $activeBookings }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Outstanding Payments</div>
            <div class="stat-value">₹{{ number_format($outstandingPayments, 0) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Badges Issued Pending</div>
            <div class="stat-value">{{ $badgesPending }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <a href="{{ route('wallet.index') }}" class="text-decoration-none">
            <div class="stat-card wallet-card">
                <div class="stat-label">Wallet Balance</div>
                <div class="stat-value wallet-value">₹{{ number_format($walletBalance, 0) }}</div>
                <div class="stat-hint">Click to view details</div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Recent Activity -->
    <div class="col-lg-6">
        <div class="section-card">
            <h5 class="section-title">
                <i class="bi bi-clock-history"></i>Recent Activity
            </h5>
            @if($recentActivity->isEmpty())
                <p class="text-muted">No recent activity</p>
            @else
                @foreach($recentActivity as $activity)
                <div class="activity-item">
                    <div class="activity-message">{{ $activity['message'] }}</div>
                    <div class="activity-time">{{ $activity['time'] }}</div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-6">
        <div class="section-card">
            <h5 class="section-title">
                <i class="bi bi-lightning-charge"></i>Quick Actions
            </h5>
            <div class="row g-3">
                <div class="col-6">
                    <a href="{{ route('exhibitions.list') }}" class="quick-action-btn">
                        <i class="bi bi-calendar-plus"></i>
                        <div class="quick-action-label">Book New Booth</div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('documents.create') }}" class="quick-action-btn">
                        <i class="bi bi-cloud-upload"></i>
                        <div class="quick-action-label">Upload Documents</div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('badges.create') }}" class="quick-action-btn">
                        <i class="bi bi-person-badge"></i>
                        <div class="quick-action-label">Generate Badges</div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('exhibitions.list') }}" class="quick-action-btn">
                        <i class="bi bi-grid-3x3-gap"></i>
                        <div class="quick-action-label">View Floorplan</div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('sponsorships.index') }}" class="quick-action-btn">
                        <i class="bi bi-trophy"></i>
                        <div class="quick-action-label">Sponsorships</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Payment Due Dates -->
<div class="section-card mb-4">
    <h5 class="section-title">
        <i class="bi bi-calendar-check"></i>Upcoming Payment Due Dates
    </h5>
    @if($upcomingPayments->isEmpty())
        <p class="text-muted">No upcoming payments</p>
    @else
        <table class="payment-table">
            <thead>
                <tr>
                    <th>EVENT NAME</th>
                    <th>PAYMENT TYPE</th>
                    <th>DUE DATE</th>
                    <th>AMOUNT</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($upcomingPayments as $payment)
                <tr>
                    <td>
                        <strong>{{ $payment->booking->exhibition->name ?? 'N/A' }}</strong>
                        @if($payment->booking->booth)
                            <br><small class="text-muted">Booth: {{ $payment->booking->booth->booth_number ?? 'N/A' }}</small>
                        @endif
                    </td>
                    <td>
                        @php
                            $paymentTypeLabel = '';
                            if ($payment->payment_type === 'initial') {
                                $paymentTypeLabel = 'Initial Payment';
                            } elseif ($payment->payment_type === 'installment') {
                                $partNum = $payment->part_number ?? null;
                                if ($partNum) {
                                    $paymentTypeLabel = 'Part ' . $partNum . ' Payment';
                                } else {
                                    $paymentTypeLabel = 'Installment Payment';
                                }
                            } else {
                                $paymentTypeLabel = ucfirst($payment->payment_type ?? 'Payment');
                            }
                        @endphp
                        <span class="payment-type-badge">{{ $paymentTypeLabel }}</span>
                    </td>
                    <td>
                        @if($payment->due_date)
                            <div class="due-date-cell">
                                <span class="due-date-value">
                                    {{ $payment->due_date->format('M d, Y') }}
                                </span>
                                @if(isset($payment->days_until_due))
                                    @php
                                        $daysUntilDue = $payment->days_until_due;
                                        $badgeClass = 'due-date-normal';
                                        $badgeText = '';
                                        
                                        if ($daysUntilDue < 0) {
                                            $badgeClass = 'due-date-urgent';
                                            $badgeText = abs($daysUntilDue) . ' day(s) overdue';
                                        } elseif ($daysUntilDue <= 7) {
                                            $badgeClass = 'due-date-urgent';
                                            $badgeText = $daysUntilDue . ' day(s) remaining';
                                        } elseif ($daysUntilDue <= 30) {
                                            $badgeClass = 'due-date-soon';
                                            $badgeText = $daysUntilDue . ' day(s) remaining';
                                        } else {
                                            $badgeClass = 'due-date-normal';
                                            $badgeText = $daysUntilDue . ' day(s) remaining';
                                        }
                                    @endphp
                                    <span class="due-date-badge {{ $badgeClass }}">
                                        {{ $badgeText }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </td>
                    <td><strong>₹{{ number_format($payment->amount, 2) }}</strong></td>
                    <td>
                        @php
                            $displayStatus = $payment->status;
                            $statusClass = 'status-pending';
                            
                            if ($payment->status === 'completed') {
                                $displayStatus = 'completed';
                                $statusClass = 'status-paid';
                            } elseif ($payment->status === 'pending' && $payment->payment_proof_file && $payment->approval_status === 'pending') {
                                $displayStatus = 'waiting for approval';
                                $statusClass = 'status-pending';
                            } else {
                                $displayStatus = 'pending';
                                $statusClass = 'status-pending';
                            }
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ ucfirst($displayStatus) }}
                        </span>
                    </td>
                    <td>
                        @php
                            // Show Pay Now only for pending payments WITHOUT payment proof (not waiting for approval)
                            $canPay = $payment->status === 'pending' && !$payment->payment_proof_file;
                        @endphp
                        
                        @if($canPay)
                            <a href="{{ route('payments.pay', $payment->id) }}" class="btn-pay-now">Pay Now</a>
                        @elseif($payment->status === 'completed')
                            <span class="text-muted">Paid</span>
                        @else
                            <span class="text-muted">Waiting for Approval</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<!-- Action Items Checklist -->
<div class="section-card">
    <h5 class="section-title">
        <i class="bi bi-list-check"></i>Action Items Checklist
    </h5>
    
    @php
        $checklistItems = [
            ['name' => 'Upload Company Photo', 'status' => 'pending', 'due' => '2024-08-15'],
            ['name' => 'Submit Certificate of Insurance', 'status' => 'pending', 'due' => '2024-08-20'],
            ['name' => 'Upload Booth Brochure', 'status' => 'pending', 'due' => '2024-08-25'],
            ['name' => 'Upload Food/Beverage Coupons', 'status' => 'completed', 'due' => '2024-08-10'],
            ['name' => 'Submit Exhibitor Kit', 'status' => 'completed', 'due' => '2024-08-05'],
        ];
    @endphp
    
    @foreach($checklistItems as $item)
    <div class="checklist-item">
        <div class="checklist-info">
            <div class="checklist-name">{{ $item['name'] }}</div>
            <div class="checklist-due">Due: {{ $item['due'] }}</div>
        </div>
        <div>
            <span class="checklist-status {{ $item['status'] === 'completed' ? 'status-completed' : 'status-pending' }}">
                {{ ucfirst($item['status']) }}
            </span>
        </div>
    </div>
    @endforeach
</div>
@endsection
