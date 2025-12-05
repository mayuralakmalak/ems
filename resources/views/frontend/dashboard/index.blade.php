@extends('layouts.exhibitor')

@section('title', 'Exhibitor Dashboard')

@push('styles')
<style>
    .stat-card {
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('page-title', 'Dashboard Overview')

@section('content')
<div class="row mb-4">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-1">Welcome, {{ $user->name }}! 👋</h3>
            <p class="text-muted mb-0">Manage your exhibition bookings, payments, documents and badges</p>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="stat-card bg-white p-3 h-100">
                <h6 class="text-muted mb-1">Total Bookings</h6>
                <h2 class="mb-0">{{ $bookings->count() }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-white p-3 h-100">
                <h6 class="text-muted mb-1">Confirmed Bookings</h6>
                <h2 class="mb-0">{{ $bookings->where('status', 'confirmed')->count() }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-white p-3 h-100">
                <h6 class="text-muted mb-1">Total Payments</h6>
                <h2 class="mb-0">₹{{ number_format($payments->sum('amount'), 0) }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('wallet.index') }}" class="text-decoration-none">
                <div class="stat-card bg-white p-3 h-100" style="border: 2px solid #667eea; cursor: pointer;">
                    <h6 class="text-muted mb-1">Wallet Balance</h6>
                    <h2 class="mb-0 text-primary">₹{{ number_format($walletBalance, 0) }}</h2>
                    <small class="text-muted">Click to view details</small>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Your Bookings</h5>
                </div>
                <div class="card-body">
                    @if($bookings->isEmpty())
                        <p class="text-muted mb-0">You do not have any bookings yet. Browse exhibitions and book your first booth.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Exhibition</th>
                                        <th>Booth</th>
                                        <th>Status</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $booking)
                                    <tr>
                                        <td>
                                            <strong>{{ $booking->exhibition->name ?? '-' }}</strong><br>
                                            <small class="text-muted">{{ $booking->exhibition->start_date->format('d M Y') }} - {{ $booking->exhibition->end_date->format('d M Y') }}</small>
                                        </td>
                                        <td>{{ $booking->booth->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td>₹{{ number_format($booking->total_amount, 0) }}</td>
                                        <td>
                                            <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                                View Booking
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Recent Payments</h5>
                </div>
                <div class="card-body">
                    @if($payments->isEmpty())
                        <p class="text-muted mb-0">No payments found yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Payment #</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_number }}</td>
                                        <td>{{ ucfirst($payment->payment_type) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
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

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Recent Documents</h5>
                </div>
                <div class="card-body">
                    @if($documents->isEmpty())
                        <p class="text-muted mb-0">No documents uploaded yet.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($documents as $document)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $document->name }}</strong><br>
                                    <small class="text-muted">{{ ucfirst($document->status) }}</small>
                                </div>
                                <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    View
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Your Badges</h5>
                </div>
                <div class="card-body">
                    @if($badges->isEmpty())
                        <p class="text-muted mb-0">No badges generated yet.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($badges as $badge)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $badge->name }}</strong><br>
                                    <small class="text-muted">{{ $badge->badge_type }} Badge</small>
                                </div>
                                <span class="badge bg-success">{{ ucfirst($badge->status) }}</span>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


