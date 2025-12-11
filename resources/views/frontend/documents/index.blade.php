@extends('layouts.exhibitor')

@section('title', 'Document Management')
@section('page-title', 'Document Management')

@push('styles')
<style>
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
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>My Documents</h5>
        <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Upload Document
        </a>
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
                        <td><strong>{{ $document->name }}</strong></td>
                        <td>{{ ucfirst($document->type) }}</td>
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
