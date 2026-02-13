@extends('layouts.admin')

@section('title', 'Admin - Role Management 4')
@section('page-title', 'Admin - Role Management 4')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Admin - Role Management 4</h4>
            <span class="text-muted">28 / 36</span>
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
                <a href="{{ route('admin.exhibitions.management') }}" class="text-primary me-3">Exhibitions</a>
                <a href="{{ route('admin.payments.index') }}" class="text-primary">Payments</a>
            </div>
        </div>
    </div>
</div>

<!-- Exhibition Management Overview -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Exhibition Management</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">A section for managing exhibitions, including creation, editing, and viewing details.</p>
    </div>
</div>

<!-- Create New Exhibition Section -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Create New Exhibition</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.exhibitions.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Exhibition Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           placeholder="Exhibition Name" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Date</label>
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                               placeholder="mm/dd/yyyy" required>
                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    </div>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" 
                           placeholder="Location">
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Exhibition</button>
        </form>
    </div>
</div>

<!-- Existing Exhibitions Section -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Existing Exhibitions</h5>
    </div>
    <div class="card-body">
        @forelse($exhibitions as $exhibition)
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <div>
                <strong>{{ $exhibition->name }}</strong>
                <br>
                <small class="text-muted">
                    ({{ $exhibition->start_date->format('Y-m-d') }}, {{ $exhibition->venue }})
                </small>
            </div>
            <div>
                @can('Exhibition Management - Modify')
                    <a href="{{ route('admin.exhibitions.edit', $exhibition->id) }}" class="btn btn-primary btn-sm me-2">
                        Edit
                    </a>
                @endcan
                @can('Exhibition Management - View')
                    <a href="{{ route('admin.exhibitions.show', $exhibition->id) }}" class="btn btn-secondary btn-sm">
                        View Details
                    </a>
                @endcan
            </div>
        </div>
        @empty
        <p class="text-muted mb-0">No exhibitions found. Create your first exhibition above.</p>
        @endforelse
    </div>
</div>
@endsection
