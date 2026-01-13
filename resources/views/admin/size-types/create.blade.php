@extends('layouts.admin')

@section('title', 'Add Size Type')
@section('page-title', 'Add Size Type')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.size-types.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Size Types
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create Size Type</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.size-types.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Length *</label>
                <input type="number" name="length" class="form-control" value="{{ old('length') }}" step="0.01" min="0" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Width *</label>
                <input type="number" name="width" class="form-control" value="{{ old('width') }}" step="0.01" min="0" required>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.size-types.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
