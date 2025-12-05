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

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-2">Total Exhibitions</h6>
                    <h2 class="mb-0 text-primary">{{ \App\Models\Exhibition::count() }}</h2>
                    <small class="text-muted">Active events</small>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                    <i class="bi bi-calendar-event text-primary" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-2">Exhibitor Accounts</h6>
                    <h2 class="mb-0 text-success">{{ \App\Models\User::role('Exhibitor')->count() }}</h2>
                    <small class="text-muted">Registered users</small>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                    <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-2">Total Revenue</h6>
                    <h2 class="mb-0 text-warning">₹{{ number_format(\App\Models\Payment::where('status', 'completed')->sum('amount'), 0) }}</h2>
                    <small class="text-muted">All time earnings</small>
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
                    <h6 class="text-muted mb-2">Active Bookings</h6>
                    <h2 class="mb-0 text-info">{{ \App\Models\Booking::where('status', 'confirmed')->count() }}</h2>
                    <small class="text-muted">Confirmed bookings</small>
                </div>
                <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                    <i class="bi bi-check-square text-info" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Get started quickly with these essential actions</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('admin.exhibitions.create') }}" class="card text-decoration-none text-dark h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body text-center p-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                                    <i class="bi bi-plus-circle-fill text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <h6 class="fw-bold">Create New Exhibition</h6>
                                <small class="text-muted">Set up a new event</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('admin.users.index') }}" class="card text-decoration-none text-dark h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body text-center p-4">
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                                    <i class="bi bi-people-fill text-success" style="font-size: 2rem;"></i>
                                </div>
                                <h6 class="fw-bold">Manage Exhibitors</h6>
                                <small class="text-muted">View all users</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('admin.financial.index') }}" class="card text-decoration-none text-dark h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body text-center p-4">
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                                    <i class="bi bi-cash-stack text-warning" style="font-size: 2rem;"></i>
                                </div>
                                <h6 class="fw-bold">Financial Reports</h6>
                                <small class="text-muted">View payments & revenue</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Recent Exhibitions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Exhibition Name</th>
                                <th>Venue</th>
                                <th>Start Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\Exhibition::latest()->take(5)->get() as $exhibition)
                            <tr>
                                <td><strong>{{ $exhibition->name }}</strong></td>
                                <td>{{ $exhibition->venue }}, {{ $exhibition->city }}</td>
                                <td>{{ $exhibition->start_date->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $exhibition->status === 'active' ? 'success' : ($exhibition->status === 'completed' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($exhibition->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.exhibitions.show', $exhibition->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No exhibitions yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Status</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-shield-check text-success me-2"></i>Authentication</span>
                        <span class="badge bg-success">Active</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-key text-success me-2"></i>Role & Permission</span>
                        <span class="badge bg-success">Ready</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-person-circle text-info me-2"></i>Admin Account</span>
                        <span class="badge bg-info">{{ Str::limit(auth()->user()->email, 15) }}</span>
                    </li>
                    <li class="d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-database text-primary me-2"></i>Database</span>
                        <span class="badge bg-primary">Connected</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Total Booths</small>
                        <strong>{{ \App\Models\Booth::count() }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: 75%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Available Booths</small>
                        <strong>{{ \App\Models\Booth::where('is_available', true)->count() }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ (\App\Models\Booth::where('is_available', true)->count() / max(\App\Models\Booth::count(), 1)) * 100 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Booked Booths</small>
                        <strong>{{ \App\Models\Booth::where('is_booked', true)->count() }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: {{ (\App\Models\Booth::where('is_booked', true)->count() / max(\App\Models\Booth::count(), 1)) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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


