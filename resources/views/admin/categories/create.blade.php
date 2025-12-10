@extends('layouts.admin')

@section('title', 'Add Category')
@section('page-title', 'Add Category')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Categories
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create Category</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                    <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
