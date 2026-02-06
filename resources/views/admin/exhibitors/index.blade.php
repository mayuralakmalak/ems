@extends('layouts.admin')

@section('title', 'Exhibitor Management')
@section('page-title', 'Exhibitor Management')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Exhibitors</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Search (Name / Email / Company)</label>
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All Payment Status</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Min Booth Area (sq meter)</label>
                    <input type="number" name="booth_area" class="form-control" placeholder="Min Booth Area (sq meter)" value="{{ request('booth_area') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Exhibition</label>
                    <select name="exhibition_id" class="form-select">
                        <option value="">All Exhibitions</option>
                        @foreach($exhibitions as $exhibition)
                            <option value="{{ $exhibition->id }}" {{ (string) $exhibition->id === request('exhibition_id') ? 'selected' : '' }}>
                                {{ $exhibition->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-start gap-2 mt-2">
                    <a href="{{ route('admin.exhibitors.index') }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                        Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                        Filter
                    </button>
                    <button type="submit" name="export" value="1" class="btn btn-success btn-sm text-nowrap">
                        Export
                    </button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Bookings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exhibitors as $exhibitor)
                    <tr>
                        <td><strong>{{ $exhibitor->name }}</strong></td>
                        <td>{{ $exhibitor->company_name ?? '-' }}</td>
                        <td>{{ $exhibitor->email }}</td>
                        <td>{{ $exhibitor->bookings->count() }}</td>
                        <td>
                            <a href="{{ route('admin.exhibitors.show', $exhibitor->id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View Profile
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No exhibitors found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $exhibitors->links() }}
        </div>
    </div>
</div>
@endsection
