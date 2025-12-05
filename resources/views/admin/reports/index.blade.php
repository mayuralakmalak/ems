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

<div class="row mt-4">
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
</div>
@endsection


