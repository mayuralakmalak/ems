@extends('layouts.admin')

@section('title', 'Admin RoleManagement 3')
@section('page-title', 'Admin RoleManagement 3')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Admin RoleManagement 3</h4>
            <span class="text-muted">27 / 36</span>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5>Admin Panel</h5>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-primary me-3">Dashboard</a>
                <a href="{{ route('admin.roles.index') }}" class="text-primary me-3">Roles</a>
                <a href="{{ route('admin.exhibitions.index') }}" class="text-primary me-3">Exhibitions</a>
                <a href="{{ route('admin.payments.index') }}" class="text-primary">Payments</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Role Permissions</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.roles.update-permissions', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="form-label fw-bold">Select Role:</label>
                <select name="role_id" class="form-select" disabled>
                    <option value="{{ $role->id }}" selected>{{ $role->name }}</option>
                </select>
                <input type="hidden" name="role_id" value="{{ $role->id }}">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Module Permissions</label>
                <p class="text-muted mb-2">
                    Configure what this role can do in each module. 
                    Actions are: <strong>Create</strong>, <strong>View</strong>, <strong>Delete</strong>, <strong>Modify</strong>, <strong>Download</strong>.
                </p>
                <p class="text-muted small mb-3">
                    Currently used modules:
                    {{ implode(', ', array_values($modules)) }}.
                </p>
                <div class="border rounded p-3" style="max-height: 500px; overflow-y: auto;">
                    <div class="form-check mb-3 pb-2 border-bottom">
                        <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                        <label class="form-check-label fw-bold" for="selectAllPermissions">
                            Select All
                        </label>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    @foreach($actions as $action)
                                        <th class="text-center">
                                            <div>{{ $action }}</div>
                                            <div class="form-check d-flex justify-content-center mt-1">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input select-action-checkbox"
                                                    data-action="{{ strtolower($action) }}"
                                                    id="select_{{ strtolower($action) }}"
                                                >
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modules as $moduleKey => $moduleLabel)
                                    <tr>
                                        <td class="fw-semibold">{{ $moduleLabel }}</td>
                                        @foreach($actions as $action)
                                            @php
                                                $permission = $permissionsByModule[$moduleKey][$action] ?? null;
                                            @endphp
                                            <td class="text-center">
                                                @if($permission)
                                                    <input
                                                        class="form-check-input permission-checkbox"
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $permission->id }}"
                                                        id="permission_{{ $permission->id }}"
                                                        data-action="{{ strtolower($action) }}"
                                                        {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                                    >
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <small class="text-muted">
                    Select the specific actions you want this role to be able to perform in each module.
                    For example, if only <strong>Download</strong> is checked for a module, users with this role
                    should only be allowed to download in that module and not create, edit, delete, or modify.
                </small>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Save Permissions</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllPermissions');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const actionSelectCheckboxes = document.querySelectorAll('.select-action-checkbox');
    
    // Function to update select all checkbox state
    function updateSelectAllState() {
        const allChecked = Array.from(permissionCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(permissionCheckboxes).some(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = someChecked && !allChecked;

        // Update per-action select all states
        actionSelectCheckboxes.forEach(actionCheckbox => {
            const action = actionCheckbox.dataset.action;
            const related = Array.from(permissionCheckboxes).filter(cb => cb.dataset.action === action);

            if (related.length === 0) {
                actionCheckbox.checked = false;
                actionCheckbox.indeterminate = false;
                return;
            }

            const actionAllChecked = related.every(cb => cb.checked);
            const actionSomeChecked = related.some(cb => cb.checked);

            actionCheckbox.checked = actionAllChecked;
            actionCheckbox.indeterminate = actionSomeChecked && !actionAllChecked;
        });
    }
    
    // Select All checkbox click handler
    selectAllCheckbox.addEventListener('change', function() {
        permissionCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectAllState();
    });
    
    // Per-action select all handlers
    actionSelectCheckboxes.forEach(actionCheckbox => {
        actionCheckbox.addEventListener('change', function() {
            const action = this.dataset.action;
            permissionCheckboxes.forEach(checkbox => {
                if (checkbox.dataset.action === action) {
                    checkbox.checked = this.checked;
                }
            });
            updateSelectAllState();
        });
    });

    // Individual checkbox change handler
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAllState);
    });
    
    // Initial state update
    updateSelectAllState();
});
</script>
@endpush
@endsection
