@extends('layouts.admin')

@section('title', 'CMS Pages')
@section('page-title', 'CMS Pages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
    <a href="{{ route('admin.cms-pages.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add Page
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">CMS Pages List</h5>
        <form id="bulkDeleteForm" action="{{ route('admin.cms-pages.bulk-delete') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="page_ids" id="bulkDeleteIds">
        </form>
        <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
            <i class="bi bi-trash me-1"></i>Delete Selected
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAll" class="form-check-input" title="Select all"></th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Footer</th>
                        <th>Header</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        <tr>
                            <td><input type="checkbox" class="form-check-input page-checkbox" value="{{ $page->id }}" name="page_ids[]"></td>
                            <td>{{ $page->title }}</td>
                            <td><code>{{ $page->slug }}</code></td>
                            <td>
                                @if($page->show_in_footer)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                @if($page->show_in_header)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                @if($page->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.cms-pages.edit', $page) }}" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.cms-pages.destroy', $page) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this page?')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No CMS pages found. Add your first page.</td>
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
    const checkboxes = document.querySelectorAll('.page-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteIds = document.getElementById('bulkDeleteIds');

    function updateBulkBtn() {
        const checked = document.querySelectorAll('.page-checkbox:checked');
        bulkDeleteBtn.disabled = checked.length === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkBtn();
        });
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkBtn);
    });

    bulkDeleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const checked = Array.from(document.querySelectorAll('.page-checkbox:checked')).map(cb => cb.value);
        if (checked.length > 0) {
            if (confirm('Are you sure you want to delete ' + checked.length + ' selected page(s)?')) {
                bulkDeleteIds.value = JSON.stringify(checked);
                bulkDeleteForm.submit();
            }
        }
    });
});
</script>
@endsection
