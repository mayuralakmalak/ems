@extends('layouts.admin')

@section('title', 'Discount Management')
@section('page-title', 'Discount Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
    <a href="{{ route('admin.discounts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-tag me-2"></i>All Discounts</h5>
        <span class="text-muted small">{{ $discounts->count() }} total</span>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $discount)
                    <tr>
                        <td>#{{ $discount->id }}</td>
                        <td>{{ $discount->title }}</td>
                        <td><strong>{{ $discount->code }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $discount->type === 'percentage' ? 'info' : 'primary' }}">
                                {{ ucfirst($discount->type) }}
                            </span>
                        </td>
                        <td>
                            {{ $discount->type === 'percentage' ? $discount->amount . '%' : number_format($discount->amount, 2) }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $discount->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($discount->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.discounts.show', $discount->id) }}" class="btn btn-sm btn-info me-1" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.discounts.edit', $discount->id) }}" class="btn btn-sm btn-primary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.discounts.destroy', $discount->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No discounts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $discounts->links() }}
        </div>
    </div>
</div>
@endsection
