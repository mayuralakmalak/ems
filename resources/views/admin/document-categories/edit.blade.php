@extends('layouts.admin')

@section('title', 'Edit Document Category')
@section('page-title', 'Edit Document Category')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('admin.document-categories.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-2"></i>Back to Categories
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.document-categories.update', $category->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">This will be displayed to users when selecting document categories.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="active" {{ old('status', $category->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $category->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Only active categories will be shown in document upload forms.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Order</label>
                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $category->order) }}" min="0">
                @error('order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Lower numbers appear first. Default is 0.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Slug</label>
                <input type="text" class="form-control" value="{{ $category->slug }}" disabled>
                <small class="text-muted">Slug is automatically generated from the name and cannot be changed.</small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Update Category
            </button>
            <a href="{{ route('admin.document-categories.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
