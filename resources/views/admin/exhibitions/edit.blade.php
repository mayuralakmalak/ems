@extends('layouts.admin')

@section('title', 'Edit Exhibition')
@section('page-title', 'Edit Exhibition')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Exhibitions
    </a>
</div>

<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Exhibition Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.exhibitions.update', $exhibition->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-tag text-primary me-1"></i>Exhibition Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $exhibition->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-building text-primary me-1"></i>Venue Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="venue" class="form-control @error('venue') is-invalid @enderror" 
                           value="{{ old('venue', $exhibition->venue) }}" required>
                    @error('venue')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-file-text text-primary me-1"></i>Description
                    </label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $exhibition->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-geo-alt text-primary me-1"></i>City <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" 
                           value="{{ old('city', $exhibition->city) }}" required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-geo text-primary me-1"></i>State / Province
                    </label>
                    <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" 
                           value="{{ old('state', $exhibition->state) }}">
                    @error('state')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-globe text-primary me-1"></i>Country <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" 
                           value="{{ old('country', $exhibition->country) }}" required>
                    @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar3 text-primary me-1"></i>Start Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                           value="{{ old('start_date', $exhibition->start_date->format('Y-m-d')) }}" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar-check text-primary me-1"></i>End Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                           value="{{ old('end_date', $exhibition->end_date->format('Y-m-d')) }}" required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock text-primary me-1"></i>Start Time
                    </label>
                    <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                           value="{{ old('start_time', $exhibition->start_time) }}">
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock-history text-primary me-1"></i>End Time
                    </label>
                    <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                           value="{{ old('end_time', $exhibition->end_time) }}">
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-tag text-primary me-1"></i>Status
                    </label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="draft" {{ old('status', $exhibition->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status', $exhibition->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ old('status', $exhibition->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $exhibition->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Update Exhibition
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

