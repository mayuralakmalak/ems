@extends('layouts.exhibitor')

@section('title', 'Edit Document')
@section('page-title', 'Edit Document')

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
    
    .current-file {
        margin-top: 15px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 6px;
    }
</style>
@endpush

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

        <form method="POST" action="{{ route('documents.update', $document->id) }}" enctype="multipart/form-data" id="uploadForm">
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

            <!-- Document Category -->
            <div class="mb-4">
                <label class="form-label fw-bold mb-3">Document Category <span class="text-danger">*</span></label>
                @if($categories->count() > 0)
                    <div class="category-radio-group">
                        @foreach($categories as $category)
                            <div class="category-radio-item">
                                <input type="radio" name="category" value="{{ $category->slug }}" id="cat_{{ $category->id }}" class="category-radio" {{ old('category', $document->type) === $category->slug ? 'checked' : '' }} required>
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

            <!-- Drag and Drop Upload Zone -->
            <div class="mb-3">
                <label class="form-label fw-bold">Replace Files (optional)</label>
                <div class="upload-zone" id="uploadZone">
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
                <small class="text-muted">You can upload multiple files. Leaving empty keeps current file(s).</small>
                @if($document->file_path)
                    <div class="current-file">
                        <strong>Current file:</strong>
                        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="ms-2">View / Download</a>
                        <div class="text-muted small mt-1">{{ basename($document->file_path) }}</div>
                    </div>
                @endif
                <div class="file-list" id="fileList"></div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Save Changes
            </button>
            <a href="{{ route('documents.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');
const uploadForm = document.getElementById('uploadForm');
const categoryError = document.getElementById('categoryError');

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

let selectedFiles = [];

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFiles(e.target.files);
        updateFileInput();
    }
});

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

// Make removeFile available globally
window.removeFile = removeFile;

// Form validation
uploadForm.addEventListener('submit', (e) => {
    const selectedCategory = document.querySelector('.category-radio:checked');
    if (!selectedCategory) {
        e.preventDefault();
        categoryError.style.display = 'block';
        return false;
    }
    categoryError.style.display = 'none';
    
    // Ensure file input is updated before submit (if files were selected)
    if (selectedFiles.length > 0) {
        updateFileInput();
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
