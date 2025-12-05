@extends('layouts.admin')

@section('title', 'Analytics Dashboard')
@section('page-title', 'Analytics & Reports')

@section('content')
<form method="GET" class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Exhibition</label>
                <select name="exhibition_id" class="form-select">
                    <option value="">All Exhibitions</option>
                    @foreach($exhibitions as $exhibition)
                    <option value="{{ $exhibition->id }}" {{ $exhibitionId == $exhibition->id ? 'selected' : '' }}>{{ $exhibition->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ route('admin.analytics.export', request()->all()) }}" class="btn btn-success">Export Report</a>
            </div>
        </div>
    </div>
</form>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card primary">
            <h6 class="text-muted mb-2">Total Bookings</h6>
            <h2 class="mb-0">{{ $totalBookings }}</h2>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card success">
            <h6 class="text-muted mb-2">Total Revenue</h6>
            <h2 class="mb-0">₹{{ number_format($totalRevenue, 0) }}</h2>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card info">
            <h6 class="text-muted mb-2">Avg Space Utilization</h6>
            <h2 class="mb-0">{{ $avgSpaceUtil }}%</h2>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card warning">
            <h6 class="text-muted mb-2">Avg Rating</h6>
            <h2 class="mb-0">{{ $avgRating }}</h2>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Booking Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="bookingTrendsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Chart</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Exhibitor Demographics</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>State</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exhibitorDemographics as $demo)
                            <tr>
                                <td>{{ $demo->state ?? 'Unknown' }}</td>
                                <td>{{ $demo->count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Service Usage</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Usage Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceUsage as $service)
                            <tr>
                                <td>{{ $service->name }}</td>
                                <td>{{ $service->booking_services_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Space Utilization by Venue</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Exhibition</th>
                                <th>Utilization %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($spaceUtilization as $util)
                            <tr>
                                <td>{{ $util['name'] }}</td>
                                <td>{{ $util['utilization'] }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Geographic Distribution</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>State</th>
                                <th>Bookings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($geographicDistribution as $state => $count)
                            <tr>
                                <td>{{ $state }}</td>
                                <td>{{ $count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const bookingData = @json($bookingTrends);
const revenueData = @json($revenueChart);

new Chart(document.getElementById('bookingTrendsChart'), {
    type: 'line',
    data: {
        labels: bookingData.map(d => d.month),
        datasets: [{
            label: 'Bookings',
            data: bookingData.map(d => d.count),
            borderColor: 'rgb(99, 102, 241)',
            tension: 0.1
        }]
    }
});

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: revenueData.map(d => d.month),
        datasets: [{
            label: 'Revenue',
            data: revenueData.map(d => d.revenue),
            backgroundColor: 'rgb(16, 185, 129)'
        }]
    }
});
</script>
@endsection
