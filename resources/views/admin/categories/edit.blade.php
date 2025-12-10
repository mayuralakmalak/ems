@extends('layouts.admin')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Categories
    </a>
    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')" class="mb-0">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-trash me-2"></i>Delete
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Update Category</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $category->title) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                    <option value="1" {{ old('status', $category->status ? '1' : '0') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $category->status ? '1' : '0') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
