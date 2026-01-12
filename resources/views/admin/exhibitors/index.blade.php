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
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="payment_status" class="form-select">
                        <option value="">All Payment Status</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" name="booth_area" class="form-control" placeholder="Min Booth Area (sq meter)" value="{{ request('booth_area') }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.exhibitors.index') }}" class="btn btn-secondary">Reset</a>
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
