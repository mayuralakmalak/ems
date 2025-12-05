@extends('layouts.exhibitor')

@section('title', 'Document Management')
@section('page-title', 'Document Management')

@push('styles')
<style>
    .upload-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 60px 40px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .upload-zone:hover {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .upload-zone.dragover {
        border-color: #6366f1;
        background: #e0f2fe;
    }
    
    .upload-icon {
        font-size: 4rem;
        color: #94a3b8;
        margin-bottom: 20px;
    }
    
    .upload-text {
        color: #64748b;
        font-size: 1rem;
        margin-bottom: 10px;
    }
    
    .upload-hint {
        color: #94a3b8;
        font-size: 0.9rem;
    }
    
    .upload-progress {
        margin-top: 20px;
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
    
    .category-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    
    .category-tab {
        padding: 10px 20px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        color: #64748b;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .category-tab:hover {
        border-color: #6366f1;
        color: #6366f1;
    }
    
    .category-tab.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
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
</style>
@endpush

@section('content')
<!-- Upload Section -->
<div class="upload-section">
    <h5 class="mb-3">Upload</h5>
    
    <div class="upload-zone" id="uploadZone">
        <i class="bi bi-cloud-upload upload-icon"></i>
        <div class="upload-text">Drag and drop your files here, or browse</div>
        <input type="file" id="fileInput" multiple accept=".pdf,.docx" style="display: none;">
    </div>
    
    <div class="mt-3">
        <small class="text-muted">File type requirements: PDF, DOCX.</small><br>
        <small class="text-muted">Maximum 500kb.</small>
    </div>
    
    <div class="upload-progress" id="uploadProgress">
        <div class="d-flex justify-content-between mb-2">
            <span id="uploadFileName">Uploading Document_A.pdf</span>
            <span id="uploadPercent">75%</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill" style="width: 75%;"></div>
        </div>
    </div>
</div>

<!-- Document Categories -->
<div class="mb-4">
    <h5 class="mb-3">Document Categories</h5>
    <div class="category-tabs">
        <button class="category-tab active" data-category="all">Certificates</button>
        <button class="category-tab" data-category="registration">Company registration documents</button>
        <button class="category-tab" data-category="design">Booth design files</button>
        <button class="category-tab" data-category="catalog">Catalogs</button>
        <button class="category-tab" data-category="other">Other required documents</button>
    </div>
</div>

<!-- My Documents -->
<div class="documents-table">
    <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0">My Documents</h5>
        <div class="filter-bar">
            <select class="filter-select" id="filterStatus">
                <option value="all">Filter by Status</option>
                <option value="pending">Pending verification</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <select class="filter-select" id="sortBy">
                <option value="date">Sort by: Upload Date</option>
                <option value="name">Sort by: Name</option>
                <option value="status">Sort by: Status</option>
            </select>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>DOCUMENT NAME</th>
                <th>DOCUMENT TYPE</th>
                <th>UPLOAD DATE</th>
                <th>STATUS</th>
                <th>EXPIRY DATE</th>
                <th>VERSION</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $document)
            <tr>
                <td>
                    <strong>{{ $document->name }}</strong>
                </td>
                <td>{{ $document->type }}</td>
                <td>{{ $document->created_at->format('Y-m-d') }}</td>
                <td>
                    <span class="status-badge {{ $document->status === 'approved' ? 'status-approved' : ($document->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
                        {{ $document->status === 'approved' ? 'Approved' : ($document->status === 'rejected' ? 'Rejected' : 'Pending verification') }}
                    </span>
                    @if($document->status === 'rejected' && $document->rejection_reason)
                    <div class="rejection-message">
                        <div class="rejection-reason">Reason: {{ $document->rejection_reason }}</div>
                        <a href="{{ route('documents.edit', $document->id) }}" class="btn-reupload">Reupload</a>
                    </div>
                    @endif
                </td>
                <td>{{ $document->expiry_date ? $document->expiry_date->format('Y-m-d') : 'N/A' }}</td>
                <td>{{ $document->version ?? '1.0' }}</td>
                <td>
                    <div class="action-icons">
                        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="action-icon view" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ asset('storage/' . $document->file_path) }}" download class="action-icon download" title="Download">
                            <i class="bi bi-download"></i>
                        </a>
                        <a href="{{ route('documents.edit', $document->id) }}" class="action-icon edit" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-icon delete" title="Delete" style="border: none; background: none; padding: 0;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">No documents found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
// Upload zone
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const uploadProgress = document.getElementById('uploadProgress');

uploadZone.addEventListener('click', () => fileInput.click());
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
    if (e.dataTransfer.files.length > 0) {
        handleFiles(e.dataTransfer.files);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFiles(e.target.files);
    }
});

function handleFiles(files) {
    Array.from(files).forEach(file => {
        if (file.size > 500 * 1024) {
            alert(`${file.name} is larger than 500KB`);
            return;
        }
        
        if (!file.name.match(/\.(pdf|docx)$/i)) {
            alert(`${file.name} is not a PDF or DOCX file`);
            return;
        }
        
        // Simulate upload progress
        uploadProgress.style.display = 'block';
        document.getElementById('uploadFileName').textContent = `Uploading ${file.name}`;
        
        let progress = 0;
        const interval = setInterval(() => {
            progress += 10;
            document.getElementById('uploadPercent').textContent = progress + '%';
            document.getElementById('progressFill').style.width = progress + '%';
            
            if (progress >= 100) {
                clearInterval(interval);
                setTimeout(() => {
                    uploadProgress.style.display = 'none';
                    location.reload();
                }, 500);
            }
        }, 200);
        
        // Actually upload the file
        const formData = new FormData();
        formData.append('file', file);
        formData.append('name', file.name);
        formData.append('type', 'Document');
        formData.append('booking_id', '{{ $documents->first()->booking_id ?? 1 }}');
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('/ems-laravel/public/documents', {
            method: 'POST',
            body: formData
        });
    });
}

// Category tabs
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        // Filter documents by category
        const category = this.getAttribute('data-category');
        // Implement filtering logic
    });
});
</script>
@endpush
@endsection
