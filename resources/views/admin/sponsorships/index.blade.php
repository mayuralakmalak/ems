@extends('layouts.admin')

@section('title', 'Sponsorship Management')
@section('page-title', 'Sponsorship Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
    <a href="{{ route('admin.sponsorships.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add
    </a>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Sponsorship Packages</h5>
        <span class="text-muted small">{{ $sponsorships->total() }} total</span>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Filter by Exhibition</label>
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
                <label class="form-label small text-muted">Status</label>
                <select name="is_active" class="form-select" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Exhibition</th>
                        <th>Name</th>
                        <th>Tier</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sponsorships as $sponsorship)
                    <tr>
                        <td>#{{ $sponsorship->id }}</td>
                        <td>{{ $sponsorship->exhibition->name ?? 'N/A' }}</td>
                        <td>
                            <strong>{{ $sponsorship->name }}</strong><br>
                            <small class="text-muted">{{ \Illuminate\Support\Str::limit($sponsorship->description, 60) }}</small>
                        </td>
                        <td>{{ $sponsorship->tier ?? '-' }}</td>
                        <td>₹{{ number_format($sponsorship->price, 2) }}</td>
                        <td>
                            @if($sponsorship->max_available)
                                {{ $sponsorship->current_count }} / {{ $sponsorship->max_available }}
                            @else
                                <span class="text-muted">Unlimited</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $sponsorship->is_active ? 'success' : 'secondary' }}">
                                {{ $sponsorship->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.sponsorships.show', $sponsorship->id) }}" class="btn btn-sm btn-info me-1" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.sponsorships.edit', $sponsorship->id) }}" class="btn btn-sm btn-primary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.sponsorships.toggle-status', $sponsorship->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                    <i class="bi bi-power"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No sponsorship packages found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $sponsorships->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection


