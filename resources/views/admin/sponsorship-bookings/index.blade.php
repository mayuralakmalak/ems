@extends('layouts.admin')

@section('title', 'Sponsorship Bookings')
@section('page-title', 'Sponsorship Bookings Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Sponsorship Bookings</h5>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label small">Exhibition</label>
                <select name="exhibition_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Exhibitions</option>
                    @foreach($exhibitions as $exhibition)
                        <option value="{{ $exhibition->id }}" {{ request('exhibition_id') == $exhibition->id ? 'selected' : '' }}>
                            {{ $exhibition->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Payment Status</label>
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Payment Statuses</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Approval Status</label>
                <select name="approval_status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Approval Statuses</option>
                    <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Sponsorship</th>
                        <th>Exhibition</th>
                        <th>Exhibitor</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Approval</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td><strong>{{ $booking->booking_number }}</strong></td>
                        <td>{{ $booking->sponsorship->name }}</td>
                        <td>{{ $booking->exhibition->name }}</td>
                        <td>
                            {{ $booking->user->name }}<br>
                            <small class="text-muted">{{ $booking->user->email }}</small>
                        </td>
                        <td>₹{{ number_format($booking->amount, 2) }}</td>
                        <td>₹{{ number_format($booking->paid_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'partial' ? 'warning' : 'info') }}">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $booking->approval_status === 'approved' ? 'success' : ($booking->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($booking->approval_status) }}
                            </span>
                        </td>
                        <td>{{ $booking->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.sponsorship-bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">No sponsorship bookings found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection

