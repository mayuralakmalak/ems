@extends('layouts.admin')

@section('title', 'Admin - Role Management 2')
@section('page-title', 'Admin - Role Management 2')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Admin - Role Management 2</h4>
            <span class="text-muted">26 / 36</span>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5>Admin Role</h5>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-primary me-3">Dashboard</a>
                <a href="{{ route('admin.roles.index') }}" class="text-primary me-3">Roles</a>
                <a href="{{ route('admin.exhibitions.index') }}" class="text-primary me-3">Exhibitions</a>
                <a href="{{ route('admin.payments.index') }}" class="text-primary">Payments</a>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Role Management</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">A screen for creating and managing user roles and their associated permissions.</p>
    </div>
</div>

<!-- Existing Roles Section -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Existing Roles</h5>
    </div>
    <div class="card-body">
        @forelse($roles as $role)
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <div>
                <h6 class="mb-0">{{ $role->name }}</h6>
            </div>
            <div>
                <a href="{{ route('admin.roles.edit-permissions', $role->id) }}" class="btn btn-primary btn-sm">
                    Edit Permissions
                </a>
            </div>
        </div>
        @empty
        <p class="text-muted mb-0">No roles found. Create your first role below.</p>
        @endforelse
    </div>
</div>

<!-- Create New Role Section -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create New Role</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Role Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                       placeholder="Enter role name" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Create Role</button>
        </form>
    </div>
</div>
@endsection
