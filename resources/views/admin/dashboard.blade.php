@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="mb-1">Welcome back, {{ auth()->user()->name }}! 👋</h3>
                <p class="text-muted mb-0">Here's what's happening with your exhibitions today.</p>
            </div>
            <div>
                <span class="badge bg-success px-3 py-2">
                    <i class="bi bi-check-circle me-2"></i>System Active
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-2">Applications</h6>
                    <h2 class="mb-0 text-primary">{{ $totalApplications ?? 0 }}</h2>
                    <small class="text-muted">Total Applications</small>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                    <i class="bi bi-file-text text-primary" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-2">Total Listings</h6>
                    <h2 class="mb-0 text-success">{{ $totalListings ?? 0 }}</h2>
                    <small class="text-muted">Total Listings</small>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                    <i class="bi bi-list-ul text-success" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-2">Total Earnings</h6>
                    <h2 class="mb-0 text-warning">₹{{ number_format(($totalEarnings ?? 0) / 1000000, 1) }}M</h2>
                    <small class="text-muted">Total Earnings</small>
                </div>
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                    <i class="bi bi-cash-coin text-warning" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-2">Pending Approvals</h6>
                    <h2 class="mb-0 text-info">{{ $pendingApprovals ?? 0 }}</h2>
                    <small class="text-muted">Pending Approvals</small>
                </div>
                <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                    <i class="bi bi-clock-history text-info" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Performance -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Overview</h5>
                <small class="text-muted">Monthly Revenue to 2024 (in USD)</small>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Booking Trends</h5>
                <small class="text-muted">Daily Bookings (in units)</small>
            </div>
            <div class="card-body">
                <canvas id="bookingChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Activities & Tasks -->
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                @forelse($recentActivities ?? [] as $activity)
                <div class="d-flex align-items-start mb-3">
                    <div class="bg-light p-2 rounded-circle me-3">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong>{{ $activity['user'] }}</strong> {{ $activity['action'] }}
                        <div class="text-muted small">{{ $activity['time'] }}</div>
                    </div>
                </div>
                @empty
                <p class="text-muted">No recent activities</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Pending Approvals</h5>
            </div>
            <div class="card-body">
                @forelse($pendingApprovalsList ?? [] as $approval)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-start">
                        <div class="bg-light p-2 rounded-circle me-3">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <strong>{{ $approval->user->name ?? 'Unknown' }}</strong>
                            <div class="text-muted small">{{ $approval->exhibition->name ?? 'Booking' }} - {{ $approval->booking_number }}</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.bookings.show', $approval->id) }}" class="btn btn-sm btn-primary">Review</a>
                </div>
                @empty
                <p class="text-muted">No pending approvals</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Revenue',
            data: [100000, 150000, 200000, 250000, 300000, 350000, 400000, 380000, 360000, 340000, 320000, 300000],
            backgroundColor: '#6366f1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 500000,
                ticks: {
                    callback: function(value) {
                        return (value / 1000) + 'k';
                    }
                }
            }
        }
    }
});

// Booking Trends Chart
const bookingCtx = document.getElementById('bookingChart');
new Chart(bookingCtx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Bookings',
            data: [100, 200, 300, 400, 350, 250, 150],
            borderColor: '#1e293b',
            backgroundColor: 'rgba(30, 41, 59, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 500
            }
        }
    }
});
</script>
@endpush


@push('styles')
<style>
.hover-lift {
    transition: all 0.3s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}
</style>
@endpush
@endsection


