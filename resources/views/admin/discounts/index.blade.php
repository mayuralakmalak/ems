@extends('layouts.admin')

@section('title', 'Discount Management')
@section('page-title', 'Discount Management')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-tag me-2"></i>All Discounts</h5>
        <a href="{{ route('admin.discounts.create') }}" class="btn btn-light">
            <i class="bi bi-plus-circle me-2"></i>Add New
        </a>
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
                        <th>Code</th>
                        <th>Name</th>
                        <th>Discount %</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $discount)
                    <tr>
                        <td><strong>{{ $discount->code }}</strong></td>
                        <td>{{ $discount->name }}</td>
                        <td>{{ $discount->discount_percent }}%</td>
                        <td>
                            <span class="badge bg-{{ $discount->status === 'active' ? 'success' : ($discount->status === 'completed' ? 'info' : 'secondary') }}">
                                {{ ucfirst($discount->status) }}
                            </span>
                        </td>
                        <td>{{ $discount->start_date->format('d M Y') }}</td>
                        <td>{{ $discount->end_date->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.discounts.show', $discount->id) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.discounts.edit', $discount->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.discounts.destroy', $discount->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
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
