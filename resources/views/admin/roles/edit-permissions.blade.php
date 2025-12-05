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
                <label class="form-label fw-bold">Permissions:</label>
                <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                    @foreach($permissions as $permission)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="permissions[]" 
                               value="{{ $permission->id }}" id="permission_{{ $permission->id }}"
                               {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                            {{ $permission->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Save Permissions</button>
            </div>
        </form>
    </div>
</div>
@endsection
