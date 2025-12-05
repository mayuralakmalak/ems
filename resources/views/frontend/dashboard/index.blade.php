@extends('layouts.exhibitor')

@section('title', 'Exhibitor Dashboard')

@push('styles')
<style>
    .dashboard-header {
        background: #f8fafc;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    
    .dashboard-header h5 {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 5px;
    }
    
    .dashboard-header p {
        color: #64748b;
        margin: 0;
        font-size: 0.95rem;
    }
    
    .welcome-section {
        margin-bottom: 30px;
    }
    
    .welcome-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
    }
    
    .welcome-subtitle {
        color: #64748b;
        font-size: 1rem;
    }
    
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

@section('page-title', 'Dashboard Overview')

@section('content')
<div class="dashboard-header">
    <h5>Dashboard Overview</h5>
    <p>Welcome, {{ $user->name }}</p>
</div>

<div class="welcome-section">
    <h1 class="welcome-title">Welcome, {{ $user->name }}! 👋</h1>
    <p class="welcome-subtitle">Manage your exhibition bookings, payments, documents and badges</p>
</div>

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
                        <div class="quick-action-label">Book New Stall</div>
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
                    <th>DUE DATE</th>
                    <th>AMOUNT</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($upcomingPayments as $payment)
                <tr>
                    <td>{{ $payment->booking->exhibition->name ?? 'N/A' }}</td>
                    <td>{{ $payment->due_date ? $payment->due_date->format('Y-m-d') : 'N/A' }}</td>
                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                    <td>
                        <span class="status-badge {{ $payment->status === 'completed' ? 'status-paid' : 'status-pending' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td>
                        @if($payment->status !== 'completed')
                            <a href="{{ route('payments.create', $payment->booking_id) }}" class="btn-pay-now">Pay Now</a>
                        @else
                            <span class="text-muted">Paid</span>
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
