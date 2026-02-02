@extends('layouts.admin')

@section('title', 'Discount Management')
@section('page-title', 'Discount Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.discounts.import') }}" class="btn btn-success">
            <i class="bi bi-upload me-1"></i>Import Discount
        </a>
        <a href="{{ route('admin.discounts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Add
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-tag me-2"></i>All Discounts</h5>
        <div class="d-flex align-items-center gap-2">
            <form id="bulkDeleteForm" action="{{ route('admin.discounts.bulk-delete') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="discount_ids" id="bulkDeleteIds">
            </form>
            <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                <i class="bi bi-trash me-1"></i>Delete Selected
            </button>
            <span class="text-muted small">{{ $discounts->count() }} total</span>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAll" class="form-check-input" title="Select all"></th>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Code</th>
                        <th>Exhibition</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $discount)
                    <tr>
                        <td><input type="checkbox" class="form-check-input discount-checkbox" value="{{ $discount->id }}" name="discount_ids[]"></td>
                        <td>#{{ $discount->id }}</td>
                        <td>{{ $discount->title }}</td>
                        <td><strong>{{ $discount->code }}</strong></td>
                        <td>{{ $discount->exhibition ? $discount->exhibition->name : 'All Exhibitions' }}</td>
                        <td>{{ $discount->email ?? '—' }}</td>
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
                        <td colspan="10" class="text-center">No discounts found.</td>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.discount-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteIds = document.getElementById('bulkDeleteIds');

    function updateBulkBtn() {
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        bulkDeleteBtn.disabled = checked.length === 0;
        if (checkboxes.length) selectAll.checked = checked.length === checkboxes.length;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
            bulkDeleteBtn.disabled = !selectAll.checked;
        });
    }
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBtn);
    });

    bulkDeleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const checked = Array.from(document.querySelectorAll('.discount-checkbox:checked')).map(cb => cb.value);
        if (checked.length > 0 && confirm('Delete ' + checked.length + ' selected discount(s)?')) {
            bulkDeleteIds.value = JSON.stringify(checked);
            bulkDeleteForm.submit();
        }
    });
});
</script>
@endsection
