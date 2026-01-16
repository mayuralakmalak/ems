@extends('layouts.admin')

@section('title', 'Size Types')
@section('page-title', 'Size Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
    <a href="{{ route('admin.size-types.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Size Type List</h5>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">{{ $sizeTypes->count() }} total</span>
            <form id="bulkDeleteForm" action="{{ route('admin.size-types.bulk-delete') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="size_type_ids" id="bulkDeleteIds">
            </form>
            <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                <i class="bi bi-trash me-1"></i>Delete Selected
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th width="80">ID</th>
                        <th>Length</th>
                        <th>Width</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sizeTypes as $sizeType)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input size-type-checkbox" value="{{ $sizeType->id }}" name="size_type_ids[]">
                            </td>
                            <td>{{ $sizeType->id }}</td>
                            <td>{{ $sizeType->length }}</td>
                            <td>{{ $sizeType->width }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.size-types.edit', $sizeType) }}" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.size-types.destroy', $sizeType) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this size type?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No size types found. Add your first size type.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.size-type-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteIds = document.getElementById('bulkDeleteIds');

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        bulkDeleteBtn.disabled = !selectAll.checked;
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checked = document.querySelectorAll('.size-type-checkbox:checked');
            bulkDeleteBtn.disabled = checked.length === 0;
            selectAll.checked = checked.length === checkboxes.length;
        });
    });

    bulkDeleteBtn.addEventListener('click', function() {
        const checked = Array.from(document.querySelectorAll('.size-type-checkbox:checked')).map(cb => cb.value);
        if (checked.length > 0) {
            if (confirm('Are you sure you want to delete ' + checked.length + ' selected size type(s)?')) {
                bulkDeleteIds.value = JSON.stringify(checked);
                bulkDeleteForm.submit();
            }
        }
    });
});
</script>
@endsection
