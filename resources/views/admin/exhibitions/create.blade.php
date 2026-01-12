@extends('layouts.admin')

@section('title', 'Create Exhibition - Step 1')
@section('page-title', 'Create New Exhibition - Step 1 of 4')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 25%"></div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            <small class="text-primary fw-bold">Step 1: General Details</small>
            <small class="text-muted">Step 2: Hall Plan & Pricing</small>
            <small class="text-muted">Step 3: Payment Schedule</small>
            <small class="text-muted">Step 4: Badge & Manual</small>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Exhibition General Information</h5>
            <small class="text-white-50">Provide basic details about your exhibition event</small>
        </div>
        <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to list
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.exhibitions.store') }}" method="POST" id="exhibitionForm">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-tag text-primary me-1"></i>Exhibition Name <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Exhibition Name"
                        value="{{ old('name') }}"
                        required
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar-event text-primary me-1"></i>Start Date <span class="text-danger">*</span>
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        class="form-control"
                        value="{{ old('start_date') }}"
                        required
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar-check text-primary me-1"></i>End Date <span class="text-danger">*</span>
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        class="form-control"
                        value="{{ old('end_date') }}"
                        required
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock text-primary me-1"></i>Start Time
                    </label>
                    <input
                        type="time"
                        name="start_time"
                        class="form-control"
                        value="{{ old('start_time') }}"
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock-history text-primary me-1"></i>End Time
                    </label>
                    <input
                        type="time"
                        name="end_time"
                        class="form-control"
                        value="{{ old('end_time') }}"
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-building text-primary me-1"></i>Venue <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="venue"
                        class="form-control"
                        placeholder="Venue"
                        value="{{ old('venue') }}"
                        required
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-geo-alt text-primary me-1"></i>Location
                    </label>
                    <input
                        type="text"
                        name="location"
                        class="form-control"
                        placeholder="Location"
                        value="{{ old('location') }}"
                    >
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-file-text text-primary me-1"></i>Description
                    </label>
                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="Rich text editor placeholder..."
                    >{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Save and Continue to Step 2 <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

