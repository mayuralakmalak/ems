@extends('layouts.exhibitor')

@section('title', 'Required Documents - ' . $booking->booking_number)
@section('page-title', 'Required Documents')

@push('styles')
<style>
    .required-doc-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .required-doc-card:hover {
        border-color: #6366f1;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.1);
    }
    
    .doc-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .doc-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .doc-type {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 5px;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .upload-section {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
    }
    
    .file-input-wrapper {
        position: relative;
        display: inline-block;
    }
    
    .file-input-wrapper input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .btn-upload {
        padding: 10px 20px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-upload:hover {
        background: #4f46e5;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    
    .btn-upload:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
        transform: none;
    }
    
    .current-document {
        margin-top: 15px;
        padding: 12px;
        background: #f8fafc;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .current-document-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .rejection-reason {
        margin-top: 10px;
        padding: 10px;
        background: #fee2e2;
        border-left: 3px solid #ef4444;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #991b1b;
    }
    
    .booking-info {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
</style>
@endpush

@section('content')
<div class="booking-info">
    <h5 class="mb-2">Booking: {{ $booking->booking_number }}</h5>
    <p class="text-muted mb-0">Exhibition: {{ $booking->exhibition->name ?? 'N/A' }}</p>
    <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-secondary mt-2">
        <i class="bi bi-arrow-left me-1"></i> Back to Booking Details
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($booking->exhibition->requiredDocuments && $booking->exhibition->requiredDocuments->count() > 0)
    @foreach($booking->exhibition->requiredDocuments as $requiredDoc)
        @php
            // Get the latest document for this required document (most recent upload)
            $uploadedDoc = $booking->documents
                ->where('required_document_id', $requiredDoc->id)
                ->sortByDesc('created_at')
                ->first();
            $isApproved = $uploadedDoc && $uploadedDoc->status === 'approved';
            $isRejected = $uploadedDoc && $uploadedDoc->status === 'rejected';
            $hasUpload = $uploadedDoc !== null;
        @endphp
        
        <div class="required-doc-card">
            <div class="doc-header">
                <div>
                    <div class="doc-name">{{ $requiredDoc->document_name }}</div>
                    <div class="doc-type">
                        Type: {{ $requiredDoc->document_type === 'both' ? 'Image or PDF' : strtoupper($requiredDoc->document_type) }}
                    </div>
                </div>
                <div>
                    @if($hasUpload)
                        <span class="status-badge {{ $isApproved ? 'status-approved' : ($isRejected ? 'status-rejected' : 'status-pending') }}">
                            {{ $isApproved ? 'Approved' : ($isRejected ? 'Rejected' : 'Pending Verification') }}
                        </span>
                    @else
                        <span class="status-badge status-pending">Not Uploaded</span>
                    @endif
                </div>
            </div>
            
            @if($isRejected && $uploadedDoc->rejection_reason)
                <div class="rejection-reason">
                    <strong>Rejection Reason:</strong> {{ $uploadedDoc->rejection_reason }}
                </div>
            @endif
            
            <div class="upload-section">
                @if($hasUpload && $uploadedDoc->file_path)
                    <div class="current-document">
                        <div class="current-document-info">
                            <i class="bi bi-file-earmark-text" style="font-size: 1.5rem; color: #6366f1;"></i>
                            <div>
                                <strong>Current Document</strong>
                                <div class="text-muted small">{{ basename($uploadedDoc->file_path) }}</div>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $uploadedDoc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i> View
                        </a>
                    </div>
                @endif
                
                @if(!$isApproved)
                    <div class="file-input-wrapper">
                        <input type="file" 
                               id="fileInput_{{ $requiredDoc->id }}" 
                               accept="{{ $requiredDoc->document_type === 'image' ? 'image/*' : ($requiredDoc->document_type === 'pdf' ? '.pdf' : 'image/*,.pdf') }}"
                               onchange="uploadRequiredDocument({{ $requiredDoc->id }}, this.files[0])">
                        <button type="button" class="btn-upload" onclick="document.getElementById('fileInput_{{ $requiredDoc->id }}').click()">
                            <i class="bi bi-upload me-1"></i>
                            {{ $hasUpload ? 'Change Document' : 'Upload Document' }}
                        </button>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        This document has been approved. You cannot change it. Please contact admin if you need to update it.
                    </div>
                @endif
            </div>
        </div>
    @endforeach
@else
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        No required documents are set for this exhibition.
    </div>
@endif

@push('scripts')
<script>
function uploadRequiredDocument(requiredDocId, file) {
    if (!file) return;
    
    const bookingId = {{ $booking->id }};
    const requiredDoc = @json($booking->exhibition->requiredDocuments->keyBy('id'));
    const doc = requiredDoc[requiredDocId];
    
    if (!doc) {
        alert('Invalid document selected.');
        return;
    }
    
    // Validate file type
    const extension = file.name.split('.').pop().toLowerCase();
    const docType = doc.document_type;
    
    if (docType === 'image' && !['jpg', 'jpeg', 'png'].includes(extension)) {
        alert('This document requires an image file (JPG, JPEG, PNG).');
        document.getElementById('fileInput_' + requiredDocId).value = '';
        return;
    }
    if (docType === 'pdf' && extension !== 'pdf') {
        alert('This document requires a PDF file.');
        document.getElementById('fileInput_' + requiredDocId).value = '';
        return;
    }
    if (docType === 'both' && !['jpg', 'jpeg', 'png', 'pdf'].includes(extension)) {
        alert('This document requires an image (JPG, JPEG, PNG) or PDF file.');
        document.getElementById('fileInput_' + requiredDocId).value = '';
        return;
    }
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5 MB.');
        document.getElementById('fileInput_' + requiredDocId).value = '';
        return;
    }
    
    // Create form data
    const formData = new FormData();
    formData.append('booking_id', bookingId);
    formData.append('required_document_id', requiredDocId);
    formData.append('files[]', file);
    formData.append('_token', '{{ csrf_token() }}');
    
    // Show loading
    const button = document.querySelector(`#fileInput_${requiredDocId}`).nextElementSibling;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
    
    // Upload
    fetch('{{ route("documents.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        return response.json().then(data => Promise.reject(data));
    })
    .then(data => {
        if (data.success !== false) {
            alert('Document uploaded successfully! It will be sent for verification.');
            location.reload();
        } else {
            alert(data.message || 'Failed to upload document. Please try again.');
            button.disabled = false;
            button.innerHTML = originalText;
            document.getElementById('fileInput_' + requiredDocId).value = '';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const message = error.message || 'An error occurred. Please try again.';
        alert(message);
        button.disabled = false;
        button.innerHTML = originalText;
        document.getElementById('fileInput_' + requiredDocId).value = '';
    });
}
</script>
@endpush
@endsection
