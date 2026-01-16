@extends('layouts.admin')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
    <div></div>
</div>

<!-- Create New Role Section -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create New Role</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           placeholder="Enter role name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Create Role</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Existing Roles Section -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Existing Roles</h5>
        <form id="bulkDeleteForm" action="{{ route('admin.roles.bulk-delete') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="role_ids" id="bulkDeleteIds">
        </form>
        <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
            <i class="bi bi-trash me-1"></i>Delete Selected
        </button>
    </div>
    <div class="card-body">
        @forelse($roles as $role)
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <div class="d-flex align-items-center">
                <input type="checkbox" class="form-check-input me-3 role-checkbox" value="{{ $role->id }}" name="role_ids[]">
                <div>
                    <h6 class="mb-0">{{ $role->name }}</h6>
                    <small class="text-muted">
                        Status: 
                        <span class="badge {{ $role->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($role->status) }}
                        </span>
                        | {{ $role->permissions->count() }} permission(s)
                    </small>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary btn-sm me-1" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <a href="{{ route('admin.roles.edit-permissions', $role->id) }}" class="btn btn-secondary btn-sm me-1" title="Permissions">
                    <i class="bi bi-gear"></i>
                </a>
                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline delete-role-form" data-role-name="{{ $role->name }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this role?');" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-0">No roles found. Create your first role above.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.role-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteIds = document.getElementById('bulkDeleteIds');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checked = document.querySelectorAll('.role-checkbox:checked');
            bulkDeleteBtn.disabled = checked.length === 0;
        });
    });

    bulkDeleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const checked = Array.from(document.querySelectorAll('.role-checkbox:checked')).map(cb => cb.value);
        if (checked.length > 0) {
            if (confirm('Are you sure you want to delete ' + checked.length + ' selected role(s)?')) {
                bulkDeleteIds.value = JSON.stringify(checked);
                bulkDeleteForm.submit();
            }
        } else {
            alert('Please select at least one role to delete.');
        }
    });
});
</script>
@endsection
