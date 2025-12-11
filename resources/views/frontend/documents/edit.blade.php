@extends('layouts.exhibitor')

@section('title', 'Edit Document')
@section('page-title', 'Edit Document')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-2"></i>Back to Documents
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('documents.update', $document->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-bold">Booking</label>
                <select name="booking_id" class="form-select" disabled>
                    <option value="{{ $document->booking_id }}">
                        {{ $document->booking->booking_number ?? '' }} - {{ $document->booking->exhibition->name ?? '' }}
                    </option>
                </select>
                <small class="text-muted">Booking cannot be changed after upload.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Document Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $document->name) }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Document Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    @foreach(['certificate','proof','catalog','design','other'] as $type)
                        <option value="{{ $type }}" {{ old('type', $document->type) === $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Replace File (optional)</label>
                <input type="file" name="file" class="form-control">
                <small class="text-muted">Max size: 5 MB. Leaving empty keeps current file.</small>
                @if($document->file_path)
                    <div class="mt-2">
                        Current file:
                        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank">View / Download</a>
                        <div class="text-muted small">{{ basename($document->file_path) }}</div>
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Save Changes
            </button>
            <a href="{{ route('documents.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
