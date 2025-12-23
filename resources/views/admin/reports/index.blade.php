@extends('layouts.admin')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1"><i class="bi bi-graph-up-arrow me-2"></i>Reports & Analytics</h2>
            <p class="text-muted mb-0">View booking, financial and service usage reports</p>
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="me-2 mb-0">Filter by Exhibition:</label>
            <select name="exhibition_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Exhibitions</option>
                @foreach($exhibitions as $ex)
                    <option value="{{ $ex->id }}" {{ $selectedExhibitionId == $ex->id ? 'selected' : '' }}>
                        {{ $ex->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="stat-card primary bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Total Bookings</h6>
            <h2 class="mb-0 text-primary">{{ $bookingCount }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Confirmed Bookings</h6>
            <h2 class="mb-0 text-success">{{ $confirmedBookings }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Cancelled Bookings</h6>
            <h2 class="mb-0 text-warning">{{ $cancelledBookings }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Total Completed Revenue</h6>
            <h2 class="mb-0 text-info">₹{{ number_format($financialTotal, 0) }}</h2>
        </div>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="stat-card primary bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Booths (Booked / Total)</h6>
            <h4 class="mb-0 text-primary">{{ $bookedBooths }} / {{ $totalBooths }}</h4>
            <small class="text-muted">Space Utilization: {{ $spaceUtilization }}%</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Additional Services Booked</h6>
            <h4 class="mb-0 text-info">{{ $totalAdditionalServices }}</h4>
            <small class="text-muted">Revenue: ₹{{ number_format($additionalServicesRevenue, 0) }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Extra Items (Included)</h6>
            <h4 class="mb-0 text-warning">{{ $extraItemsCount }}</h4>
            <small class="text-muted">Value: ₹{{ number_format($extraItemsRevenue, 0) }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success bg-white p-3 h-100">
            <h6 class="text-muted mb-1">Badges Generated</h6>
            <h4 class="mb-0 text-success">{{ $totalBadges }}</h4>
            <small class="text-muted">Avg / Booking: {{ $avgBadgesPerBooking }}</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Booking Report</h5>
            </div>
            <div class="card-body">
                @if($bookings->isEmpty())
                    <p class="text-muted mb-0">No bookings found for the selected filter.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Booking #</th>
                                    <th>Exhibitor</th>
                                    <th>Booth</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookings as $booking)
                                <tr>
                                    <td>{{ $booking->booking_number }}</td>
                                    <td>{{ $booking->user->name ?? '-' }}</td>
                                    <td>{{ $booking->booth->name ?? '-' }}</td>
                                    <td>{{ ucfirst($booking->status) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Financial Report</h5>
            </div>
            <div class="card-body">
                @if($payments->isEmpty())
                    <p class="text-muted mb-0">No payments found for the selected filter.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Payment #</th>
                                    <th>Exhibitor</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_number }}</td>
                                    <td>{{ $payment->user->name ?? '-' }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>{{ ucfirst($payment->status) }}</td>
                                    <td>₹{{ number_format($payment->amount, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bag-check me-2"></i>Service Usage Report</h5>
            </div>
            <div class="card-body">
                @if($serviceUsage->isEmpty())
                    <p class="text-muted mb-0">No service usage data yet.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($serviceUsage as $row)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $row->name }}</span>
                            <span class="badge bg-primary">{{ $row->usage_count }} times</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i>Document Verification</h5>
            </div>
            <div class="card-body">
                @if($totalDocuments === 0)
                    <p class="text-muted mb-0">No documents uploaded for the selected filter.</p>
                @else
                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="mb-1"><strong>Total Documents</strong></p>
                            <p class="mb-0">{{ $totalDocuments }}</p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1"><strong>Verification Ratio</strong></p>
                            <p class="mb-0 text-success">{{ $documentVerificationRatio }}% approved</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <span class="badge bg-success">Approved: {{ $approvedDocuments }}</span>
                        <span class="badge bg-danger">Rejected: {{ $rejectedDocuments }}</span>
                        <span class="badge bg-secondary">Pending: {{ $pendingDocuments }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Most Booked Booth Sizes (sqft)</h5>
            </div>
            <div class="card-body">
                @if($popularSizes->isEmpty())
                    <p class="text-muted mb-0">No booking data available for booth sizes.</p>
                @else
                    <canvas id="boothSizesChart" height="220"></canvas>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Most Booked Booth Categories</h5>
            </div>
            <div class="card-body">
                @if($popularCategories->isEmpty())
                    <p class="text-muted mb-0">No booking data available for booth categories.</p>
                @else
                    <canvas id="boothCategoriesChart" height="220"></canvas>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sizeLabels = @json($popularSizes->pluck('size_sqft')->map(function ($v) {
            return $v . ' sqft';
        }));
        const sizeData = @json($popularSizes->pluck('bookings_count'));

        const categoryLabels = @json($popularCategories->pluck('category')->map(function ($v) {
            return $v ?: 'Uncategorized';
        }));
        const categoryData = @json($popularCategories->pluck('bookings_count'));

        if (document.getElementById('boothSizesChart') && sizeLabels.length > 0) {
            new Chart(document.getElementById('boothSizesChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: sizeLabels,
                    datasets: [{
                        label: 'Bookings',
                        data: sizeData,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }

        if (document.getElementById('boothCategoriesChart') && categoryLabels.length > 0) {
            new Chart(document.getElementById('boothCategoriesChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryData,
                        backgroundColor: [
                            '#6366f1', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444',
                            '#06b6d4', '#0f172a', '#14b8a6', '#e11d48', '#3b82f6'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

