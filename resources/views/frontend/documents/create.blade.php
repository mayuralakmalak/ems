@extends('layouts.frontend')

@section('title', 'Upload Document')

@section('content')
<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left me-2"></i>Back to Documents
            </a>
            <h1 class="mb-1">Upload New Document</h1>
            <p class="text-muted mb-0">Attach documents to your bookings (max 5 MB per file)</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Booking <span class="text-danger">*</span></label>
                    <select name="booking_id" class="form-select" required>
                        <option value="">Select Booking</option>
                        @foreach($bookings as $booking)
                            <option value="{{ $booking->id }}">
                                {{ $booking->booking_number }} - {{ $booking->exhibition->name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Document Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Document Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="certificate">Certificate</option>
                        <option value="proof">Proof</option>
                        <option value="catalog">Catalog</option>
                        <option value="design">Booth Design</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" required>
                    <small class="text-muted">Max size: 5 MB</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload me-2"></i>Upload Document
                </button>
            </form>
        </div>
    </div>
</div>
@endsection


