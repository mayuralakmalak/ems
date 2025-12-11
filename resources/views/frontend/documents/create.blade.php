@extends('layouts.exhibitor')

@section('title', 'Upload Document')
@section('page-title', 'Upload New Document')

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
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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

            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Booking <span class="text-danger">*</span></label>
                    <select name="booking_id" class="form-select @error('booking_id') is-invalid @enderror" required>
                        <option value="">Select Booking</option>
                        @foreach($bookings as $booking)
                            <option value="{{ $booking->id }}" {{ old('booking_id') == $booking->id ? 'selected' : '' }}>
                                {{ $booking->booking_number }} - {{ $booking->exhibition->name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('booking_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Document Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">Select Document Type</option>
                        @foreach(['certificate' => 'Certificate', 'proof' => 'Proof', 'catalog' => 'Catalog', 'design' => 'Booth Design', 'other' => 'Other'] as $val => $label)
                            <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Files <span class="text-danger">*</span></label>
                    <input type="file" name="files[]" class="form-control @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror" multiple required>
                    <small class="text-muted">You can select multiple files. Max size per file: 5 MB. Allowed formats: PDF, DOC, DOCX, JPG, JPEG, PNG</small>
                    @error('files')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('files.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="fileList" class="mt-2"></div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload me-2"></i>Upload Document
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelector('input[name="files[]"]').addEventListener('change', function(e) {
        const fileList = document.getElementById('fileList');
        fileList.innerHTML = '';
        
        if (e.target.files.length > 0) {
            const list = document.createElement('ul');
            list.className = 'list-group';
            
            Array.from(e.target.files).forEach((file, index) => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                listItem.innerHTML = `
                    <span>${file.name}</span>
                    <small class="text-muted">${(file.size / 1024).toFixed(2)} KB</small>
                `;
                list.appendChild(listItem);
            });
            
            fileList.appendChild(list);
        }
    });
</script>
@endpush
@endsection


