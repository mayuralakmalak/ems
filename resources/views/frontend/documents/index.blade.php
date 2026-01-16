@extends('layouts.exhibitor')

@section('title', 'Document Management')
@section('page-title', 'Document Management')

@push('styles')
<style>
    .category-radio-group {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    
    .category-radio-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .category-radio-item input[type="radio"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .category-radio-item label {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
        color: #1e293b;
    }
    
    .upload-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .upload-zone:hover {
        border-color: #6366f1;
        background: #f0f4ff;
    }
    
    .upload-zone.dragover {
        border-color: #6366f1;
        background: #eef2ff;
    }
    
    .upload-icon {
        font-size: 48px;
        color: #6366f1;
        margin-bottom: 15px;
    }
    
    .upload-text {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 10px;
    }
    
    .upload-zone input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }
    
    .file-requirements {
        margin-top: 15px;
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .file-list {
        margin-top: 20px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .file-item {
        position: relative;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .file-item:hover {
        border-color: #6366f1;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
    }
    
    .file-item-preview {
        width: 60px;
        height: 60px;
        margin: 0 auto 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border-radius: 6px;
        font-size: 32px;
    }
    
    .file-item-preview img {
        max-width: 100%;
        max-height: 100%;
        border-radius: 4px;
    }
    
    .file-item-name {
        font-size: 0.85rem;
        color: #1e293b;
        font-weight: 500;
        margin-bottom: 5px;
        word-break: break-word;
    }
    
    .file-item-size {
        color: #64748b;
        font-size: 0.75rem;
        margin-bottom: 8px;
    }
    
    .file-item-remove {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #ef4444;
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .file-item-remove:hover {
        background: #dc2626;
        transform: scale(1.1);
    }
    
    .upload-progress {
        margin-top: 15px;
        display: none;
    }
    
    .progress-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: #6366f1;
        transition: width 0.3s ease;
    }
    
    .filter-bar {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .filter-select {
        padding: 10px 15px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
    }
    
    .documents-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .documents-table table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .documents-table thead {
        background: #f8fafc;
    }
    
    .documents-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .documents-table td {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .documents-table tbody tr:hover {
        background: #f8fafc;
    }
    
    .documents-table tbody tr:last-child td {
        border-bottom: none;
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
    
    .action-icons {
        display: flex;
        gap: 10px;
    }
    
    .action-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .action-icon.view {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .action-icon.download {
        background: #d1fae5;
        color: #065f46;
    }
    
    .action-icon.edit {
        background: #fef3c7;
        color: #92400e;
    }
    
    .action-icon.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .action-icon:hover {
        transform: scale(1.1);
    }
    
    .rejection-message {
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 12px;
        margin-top: 10px;
        font-size: 0.85rem;
    }
    
    .rejection-reason {
        color: #991b1b;
        margin-bottom: 8px;
    }
    
    .btn-reupload {
        padding: 6px 12px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        cursor: pointer;
    }
    /* Reduce padding on edit buttons to match other buttons */
    .btn-primary[title="Edit"],
    .btn-primary[title*="Edit"] {
        padding: 0.25rem 0.5rem !important;
    }
</style>
@endpush

@section('content')
<!-- Header with Manage Button -->


<!-- Upload Section -->
<div class="upload-section">
    <h5 class="mb-3">Upload</h5>
    
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

    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" id="uploadForm">
        @csrf
        

        <!-- Booking Selection -->
        <div class="mb-4">
            <label class="form-label fw-bold">Booking <span class="text-danger">*</span></label>
            <select name="booking_id" id="bookingSelect" class="form-select @error('booking_id') is-invalid @enderror" required onchange="loadRequiredDocuments(this.value)">
                <option value="">Select Booking</option>
                @foreach($bookings as $booking)
                    <option value="{{ $booking->id }}" data-exhibition-id="{{ $booking->exhibition_id }}" {{ old('booking_id') == $booking->id ? 'selected' : '' }}>
                        {{ $booking->booking_number }} - {{ $booking->exhibition->name ?? '' }}
                    </option>
                @endforeach
            </select>
            @error('booking_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Required Documents Section -->
        <div id="requiredDocumentsSection" class="mb-4" style="display: none;">
            <label class="form-label fw-bold mb-3">Required Documents</label>
            <div id="requiredDocumentsList" class="border rounded p-3" style="background-color: #f8f9fa;">
                <!-- Required documents will be loaded here via JavaScript -->
            </div>
        </div>

        <!-- Document Category (only show if no required document selected) -->
        <div class="mb-4" id="categorySection">
            <label class="form-label fw-bold mb-3">Document Category <span class="text-danger">*</span></label>
            @if($categories->count() > 0)
                <div class="category-radio-group">
                    @foreach($categories as $category)
                        <div class="category-radio-item">
                            <input type="radio" name="category" value="{{ $category->slug }}" id="cat_{{ $category->id }}" class="category-radio">
                            <label for="cat_{{ $category->id }}">{{ $category->name }}</label>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning">
                    No active document categories found. <a href="{{ route('document-categories.create') }}">Create one</a> to continue.
                </div>
            @endif
            <div class="text-danger small mt-2" id="categoryError" style="display: none;">Please select a category.</div>
        </div>

        <!-- Drag and Drop Upload Zone (only show if no required documents or category selected) -->
        <div class="upload-zone" id="uploadZone" style="display: none;">
            <div class="upload-icon">
                <i class="bi bi-cloud-upload"></i>
            </div>
            <div class="upload-text">
                Drag and drop your files here
            </div>
            <input type="file" name="files[]" id="fileInput" multiple>
            <div class="file-requirements">
                File type requirements: PDF, DOCX, DOC, JPG, JPEG, PNG<br>
                Maximum file size: 5 MB
            </div>
        </div>

        <!-- File List -->
        <div class="file-list" id="fileList"></div>

        <!-- Upload Progress -->
        <div class="upload-progress" id="uploadProgress">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%"></div>
            </div>
            <div class="text-center mt-2 small text-muted" id="progressText"></div>
        </div>

        <button type="submit" class="btn btn-primary mt-3" id="uploadBtn">
            <i class="bi bi-upload me-2"></i>Upload Documents
        </button>
    </form>
</div>

<!-- My Documents -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>My Documents</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <form method="GET" action="{{ route('documents.index') }}" class="filter-bar">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending verification</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <select name="type" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="certificate" {{ request('type') === 'certificate' ? 'selected' : '' }}>Certificate</option>
                    <option value="proof" {{ request('type') === 'proof' ? 'selected' : '' }}>Proof</option>
                    <option value="catalog" {{ request('type') === 'catalog' ? 'selected' : '' }}>Catalog</option>
                    <option value="design" {{ request('type') === 'design' ? 'selected' : '' }}>Booth Design</option>
                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @if(request('status') || request('type'))
                <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
                @endif
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Document Name</th>
                        <th>Type</th>
                        <th>Upload Date</th>
                        <th>Status</th>
                        <th>Expiry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                    <tr>
                        <td><strong>{{ $document->name }}</strong>
                            @if($document->requiredDocument)
                                <br><small class="text-muted">(Required Document)</small>
                            @endif
                        </td>
                        <td>
                            @if($document->requiredDocument)
                                {{ $document->requiredDocument->document_name }}
                            @else
                                {{ ucfirst($document->type) }}
                            @endif
                        </td>
                        <td>{{ $document->created_at->format('Y-m-d') }}</td>
                        <td>
                            <span class="status-badge {{ $document->status === 'approved' ? 'status-approved' : ($document->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
                                {{ $document->status === 'approved' ? 'Approved' : ($document->status === 'rejected' ? 'Rejected' : 'Pending') }}
                            </span>
                            @if($document->status === 'rejected' && $document->rejection_reason)
                            <div class="rejection-message mt-2">
                                <div class="rejection-reason"><strong>Reason:</strong> {{ $document->rejection_reason }}</div>
                                <a href="{{ route('documents.edit', $document->id) }}" class="btn-reupload">Reupload</a>
                            </div>
                            @endif
                        </td>
                        <td>{{ $document->expiry_date ? $document->expiry_date->format('Y-m-d') : 'N/A' }}</td>
                        <td>
                            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-info me-1" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ asset('storage/' . $document->file_path) }}" download class="btn btn-sm btn-secondary me-1" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                            @if($document->canBeEdited())
                                <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="btn btn-sm btn-primary me-1" title="Cannot edit approved document" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">
                                    <i class="bi bi-pencil"></i>
                                </span>
                                <span class="btn btn-sm btn-danger" title="Cannot delete approved document" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">
                                    <i class="bi bi-trash"></i>
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No documents found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Upload functionality
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');
const uploadForm = document.getElementById('uploadForm');
const categoryError = document.getElementById('categoryError');
const uploadBtn = document.getElementById('uploadBtn');

// Drag and drop handlers
uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFiles(files);
        updateFileInput();
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFiles(e.target.files);
        updateFileInput();
    }
});

// Make removeFile available globally
window.removeFile = removeFile;

let selectedFiles = [];

function handleFiles(files) {
    Array.from(files).forEach((file) => {
        // Check if file already exists
        if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
            selectedFiles.push(file);
        }
    });
    renderFileList();
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFileInput();
    renderFileList();
}

function updateFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });
    fileInput.files = dataTransfer.files;
}

function getFileIcon(file) {
    const extension = file.name.split('.').pop().toLowerCase();
    const iconMap = {
        'pdf': '📄',
        'doc': '📝',
        'docx': '📝',
        'jpg': '🖼️',
        'jpeg': '🖼️',
        'png': '🖼️',
        'gif': '🖼️',
    };
    return iconMap[extension] || '📎';
}

function renderFileList() {
    fileList.innerHTML = '';
    
    if (selectedFiles.length === 0) {
        return;
    }
    
    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.setAttribute('data-index', index);
        
        const isImage = file.type.startsWith('image/');
        let previewContent = '';
        
        if (isImage) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = fileItem.querySelector('.file-item-preview');
                if (preview) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
            };
            reader.readAsDataURL(file);
            previewContent = '<div style="font-size: 32px;">🖼️</div>';
        } else {
            previewContent = `<div style="font-size: 32px;">${getFileIcon(file)}</div>`;
        }
        
        fileItem.innerHTML = `
            <button type="button" class="file-item-remove" onclick="removeFile(${index})" title="Remove">
                <i class="bi bi-x"></i>
            </button>
            <div class="file-item-preview">
                ${previewContent}
            </div>
            <div class="file-item-name" title="${file.name}">${file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name}</div>
            <div class="file-item-size">${(file.size / 1024).toFixed(2)} KB</div>
        `;
        fileList.appendChild(fileItem);
    });
}

// Store bookings data for JavaScript
@php
$bookingsData = $bookings->map(function($booking) {
    return [
        'id' => $booking->id,
        'exhibition_id' => $booking->exhibition_id,
        'required_documents' => ($booking->exhibition && $booking->exhibition->requiredDocuments ? $booking->exhibition->requiredDocuments->map(function($doc) {
            return [
                'id' => $doc->id,
                'document_name' => $doc->document_name,
                'document_type' => $doc->document_type,
            ];
        })->values()->toArray() : []),
        'uploaded_documents' => ($booking->documents ? $booking->documents->where('required_document_id', '!=', null)->map(function($doc) {
            return [
                'id' => $doc->id,
                'required_document_id' => $doc->required_document_id,
                'status' => $doc->status,
                'file_path' => $doc->file_path,
            ];
        })->values()->toArray() : []),
    ];
})->values()->toArray();
@endphp
const bookingsData = @json($bookingsData);

function loadRequiredDocuments(bookingId) {
    const booking = bookingsData.find(b => parseInt(b.id) === parseInt(bookingId));
    const requiredDocsSection = document.getElementById('requiredDocumentsSection');
    const requiredDocsList = document.getElementById('requiredDocumentsList');
    const categorySection = document.getElementById('categorySection');
    
    const uploadZone = document.getElementById('uploadZone');
    
    if (!booking || !booking.required_documents || booking.required_documents.length === 0) {
        requiredDocsSection.style.display = 'none';
        categorySection.style.display = 'block';
        uploadZone.style.display = 'block';
        uploadZone.querySelector('input').required = true;
        // Make category required
        document.querySelectorAll('.category-radio').forEach(radio => {
            radio.required = true;
        });
        return;
    }
    
    // Show required documents section
    requiredDocsSection.style.display = 'block';
    categorySection.style.display = 'none';
    uploadZone.style.display = 'none';
    uploadZone.querySelector('input').required = false;
    // Make category not required
    document.querySelectorAll('.category-radio').forEach(radio => {
        radio.required = false;
        radio.checked = false;
    });
    
    // Build required documents list
    let html = '';
    booking.required_documents.forEach(reqDoc => {
        const uploadedDoc = booking.uploaded_documents.find(ud => parseInt(ud.required_document_id) === parseInt(reqDoc.id));
        const isApproved = uploadedDoc && uploadedDoc.status === 'approved';
        const hasUpload = uploadedDoc && uploadedDoc.status !== null;
        
        html += `
            <div class="mb-3 p-3 border rounded bg-white required-doc-item" data-required-doc-id="${reqDoc.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${reqDoc.document_name}</strong>
                        <small class="text-muted d-block">Type: ${reqDoc.document_type === 'both' ? 'Image or PDF' : reqDoc.document_type.toUpperCase()}</small>
                    </div>
                    <div>
                        ${hasUpload ? `
                            <span class="badge ${isApproved ? 'bg-success' : (uploadedDoc.status === 'rejected' ? 'bg-danger' : 'bg-warning')}">
                                ${isApproved ? 'Approved' : (uploadedDoc.status === 'rejected' ? 'Rejected' : 'Pending')}
                            </span>
                            ${!isApproved ? `
                                <input type="file" id="fileInput_${reqDoc.id}" accept="${reqDoc.document_type === 'image' ? 'image/*' : (reqDoc.document_type === 'pdf' ? '.pdf' : 'image/*,.pdf')}" style="display: none;" onchange="uploadRequiredDocument(${reqDoc.id}, this.files[0])">
                                <button type="button" class="btn btn-sm btn-primary ms-2" onclick="document.getElementById('fileInput_${reqDoc.id}').click()">
                                    ${hasUpload ? 'Change' : 'Upload'}
                                </button>
                            ` : '<span class="text-muted ms-2">(Cannot change after approval)</span>'}
                        ` : `
                            <input type="file" id="fileInput_${reqDoc.id}" accept="${reqDoc.document_type === 'image' ? 'image/*' : (reqDoc.document_type === 'pdf' ? '.pdf' : 'image/*,.pdf')}" style="display: none;" onchange="uploadRequiredDocument(${reqDoc.id}, this.files[0])">
                            <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('fileInput_${reqDoc.id}').click()">
                                Upload
                            </button>
                        `}
                    </div>
                </div>
                ${hasUpload && uploadedDoc.file_path ? `
                    <div class="mt-2">
                        <a href="{{ asset('storage/') }}/${uploadedDoc.file_path}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> View Current Document
                        </a>
                    </div>
                ` : ''}
            </div>
        `;
    });
    
    requiredDocsList.innerHTML = html || '<p class="text-muted mb-0">No required documents for this booking.</p>';
}

function uploadRequiredDocument(requiredDocId, file) {
    if (!file) return;
    
    const bookingId = document.getElementById('bookingSelect').value;
    if (!bookingId) {
        alert('Please select a booking first.');
        return;
    }
    
    // Validate file type
    const requiredDoc = bookingsData.find(b => parseInt(b.id) === parseInt(bookingId))
        ?.required_documents.find(d => parseInt(d.id) === parseInt(requiredDocId));
    
    if (requiredDoc) {
        const extension = file.name.split('.').pop().toLowerCase();
        const docType = requiredDoc.document_type;
        
        if (docType === 'image' && !['jpg', 'jpeg', 'png'].includes(extension)) {
            alert('This document requires an image file (JPG, JPEG, PNG).');
            return;
        }
        if (docType === 'pdf' && extension !== 'pdf') {
            alert('This document requires a PDF file.');
            return;
        }
        if (docType === 'both' && !['jpg', 'jpeg', 'png', 'pdf'].includes(extension)) {
            alert('This document requires an image (JPG, JPEG, PNG) or PDF file.');
            return;
        }
    }
    
    // Create form data
    const formData = new FormData();
    formData.append('booking_id', bookingId);
    formData.append('required_document_id', requiredDocId);
    formData.append('files[]', file);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
    if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page.');
        return;
    }
    formData.append('_token', csrfToken);
    
    // Show loading
    const button = document.querySelector(`#fileInput_${requiredDocId}`).nextElementSibling;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';
    
    // Upload
    fetch('{{ route("documents.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json().catch(() => ({ success: false, message: 'Upload failed' })))
    .then(data => {
        if (data.success !== false) {
            alert('Document uploaded successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to upload document. Please try again.');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function selectRequiredDocument(requiredDocId) {
    // This function is kept for backward compatibility but uploadRequiredDocument is used instead
    document.getElementById(`fileInput_${requiredDocId}`).click();
}

// Form validation
uploadForm.addEventListener('submit', (e) => {
    const requiredDocsSection = document.getElementById('requiredDocumentsSection');
    const categorySection = document.getElementById('categorySection');
    
    // If required documents section is visible, don't submit this form
    if (requiredDocsSection && requiredDocsSection.style.display !== 'none') {
        e.preventDefault();
        alert('Please use the Upload button next to each required document to upload files.');
        return false;
    }
    
    const selectedCategory = document.querySelector('.category-radio:checked');
    if (!selectedCategory) {
        e.preventDefault();
        categoryError.style.display = 'block';
        return false;
    }
    categoryError.style.display = 'none';
    
    if (selectedFiles.length === 0) {
        e.preventDefault();
        alert('Please select at least one file to upload.');
        return false;
    }
    
    // Ensure file input is updated before submit
    updateFileInput();
});

// Load required documents on page load if booking is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    const bookingSelect = document.getElementById('bookingSelect');
    if (bookingSelect && bookingSelect.value) {
        loadRequiredDocuments(bookingSelect.value);
    }
});

// Category radio change
document.querySelectorAll('.category-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        categoryError.style.display = 'none';
    });
});
</script>
@endpush
@endsection
