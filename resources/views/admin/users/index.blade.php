@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'Manage Users & Roles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
    @can('User Management - Create')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add
    </a>
    @endcan
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>User List</h5>
        <form id="bulkDeleteForm" action="{{ route('admin.users.bulk-delete') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="user_ids" id="bulkDeleteIds">
        </form>
        @can('User Management - Delete')
        <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
            <i class="bi bi-trash me-1"></i>Delete Selected
        </button>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Role(s)</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            @if(! $user->hasRole('Admin'))
                            <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}" name="user_ids[]">
                            @endif
                        </td>
                        <td>
                            <strong>{{ $user->name }}</strong><br>
                            <small class="text-muted">{{ $user->phone }}</small>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->company_name }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $user->city }} @if($user->city && $user->country),@endif {{ $user->country }}
                            </small>
                        </td>
                        <td>
                            @can('User Management - Modify')
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @if(! $user->hasRole('Admin'))
                                @can('User Management - Delete')
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No users found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
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
            const checked = document.querySelectorAll('.user-checkbox:checked');
            bulkDeleteBtn.disabled = checked.length === 0;
            selectAll.checked = checked.length === checkboxes.length;
        });
    });

    bulkDeleteBtn.addEventListener('click', function() {
        const checked = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
        if (checked.length > 0) {
            if (confirm('Are you sure you want to delete ' + checked.length + ' selected user(s)?')) {
                bulkDeleteIds.value = JSON.stringify(checked);
                bulkDeleteForm.submit();
            }
        }
    });
});
</script>
@endsection


