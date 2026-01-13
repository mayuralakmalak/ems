@extends('layouts.admin')

@section('title', 'Edit Size Type')
@section('page-title', 'Edit Size Type')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.size-types.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Size Types
    </a>
    <form action="{{ route('admin.size-types.destroy', $sizeType) }}" method="POST" onsubmit="return confirm('Delete this size type?')" class="mb-0">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-trash me-2"></i>Delete
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Update Size Type</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.size-types.update', $sizeType) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Length *</label>
                <input type="number" name="length" class="form-control" value="{{ old('length', $sizeType->length) }}" step="0.01" min="0" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Width *</label>
                <input type="number" name="width" class="form-control" value="{{ old('width', $sizeType->width) }}" step="0.01" min="0" required>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.size-types.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
