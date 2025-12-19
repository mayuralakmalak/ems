@extends('layouts.admin')

@section('title', 'Admin-Document Management')
@section('page-title', 'Admin-Document Management')

@push('styles')
<style>
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .summary-label {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 10px;
    }
    
    .summary-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .alert-notice {
        background: #fef3c7;
        border: 1px solid #fbbf24;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .filter-bar {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    
    .filter-select {
        padding: 10px 15px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
    }
    
    .btn-filter {
        padding: 10px 20px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: white;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-filter:hover {
        border-color: #6366f1;
        background: #f8fafc;
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
        cursor: pointer;
    }
    
    .documents-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .document-link {
        color: #6366f1;
        text-decoration: none;
        font-weight: 500;
    }
    
    .document-link:hover {
        text-decoration: underline;
    }
    
    .panel-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
        transition: opacity 0.3s ease;
    }
    
    .panel-backdrop.show {
        display: block;
    }
    
    .right-panel {
        position: fixed;
        right: 0;
        top: 0;
        width: 450px;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 10px rgba(0,0,0,0.1);
        padding: 25px;
        overflow-y: auto;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }
    
    .right-panel.open {
        transform: translateX(0);
    }
    
    .document-preview {
        width: 100%;
        height: 400px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    
    .detail-item {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .detail-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .status-badge {
        padding: 4px 12px;
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
    
    .btn-approve {
        background: #6366f1;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        width: 100%;
        margin-bottom: 10px;
    }
    
    .btn-reject {
        background: #ef4444;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        width: 100%;
    }
</style>
@endpush

@section('content')
@if($docsPendingVerification > 0)
<div class="alert-notice">
    <div>
        <strong>Notice Required:</strong> Documents Pending Verification. There are {{ $docsPendingVerification }} exhibitors with documents pending your review.
    </div>
    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
</div>
@endif

<!-- Summary Cards -->
<div class="card mb-4">
    <div class="card-body">
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-label">Total Exhibitors</div>
                <div class="summary-value">{{ $totalExhibitors }}</div>
                <small class="text-muted">Registered in the system</small>
            </div>
            <div class="summary-card">
                <div class="summary-label">Docs Pending Verification</div>
                <div class="summary-value">{{ $docsPendingVerification }}</div>
                <small class="text-muted">Awaiting your review</small>
            </div>
            <div class="summary-card">
                <div class="summary-label">Docs Expiring Soon</div>
                <div class="summary-value">{{ $docsExpiringSoon }}</div>
                <small class="text-muted">Within the next 3 months</small>
            </div>
            <div class="summary-card">
                <div class="summary-label">Missing Docs / Failed Uploads</div>
                <div class="summary-value">{{ $missingDocs }}</div>
                <small class="text-muted">Requires immediate attention</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="documentsFilterForm" method="GET" action="{{ route('documents.index') }}">
            <select class="filter-select" name="type">
                <option value="">Filter by Type</option>
                <option value="Certification" {{ request('type')==='Certification' ? 'selected' : '' }}>Certification</option>
                <option value="Proof of Address" {{ request('type')==='Proof of Address' ? 'selected' : '' }}>Proof of Address</option>
                <option value="Product Catalog" {{ request('type')==='Product Catalog' ? 'selected' : '' }}>Product Catalog</option>
                <option value="Company Registration" {{ request('type')==='Company Registration' ? 'selected' : '' }}>Company Registration</option>
            </select>
            
            <select class="filter-select" name="status">
                <option value="">Filter by Status</option>
                <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status')==='approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status')==='rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

            <select class="filter-select" name="user_id">
                <option value="">Filter by User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id')==$user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>

            <select class="filter-select" name="exhibition_id">
                <option value="">Filter by Exhibition</option>
                @foreach($exhibitions as $exhibition)
                    <option value="{{ $exhibition->id }}" {{ request('exhibition_id')==$exhibition->id ? 'selected' : '' }}>
                        {{ $exhibition->name }}
                    </option>
                @endforeach
            </select>

            <input type="text" class="filter-select" name="search" placeholder="Search..." value="{{ request('search') }}">

            <button type="submit" class="btn-filter">
                Apply Filters
            </button>

            <a href="{{ route('documents.index') }}" class="btn-filter">
                Reset
            </a>

            <button type="button" class="btn-filter" id="bulkApproveBtn" disabled>
                Bulk Approval (<span id="selectedCount">0</span>)
            </button>
        </form>
    </div>
</div>

<!-- Documents Table -->
<div class="card" id="documentsContainer">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Documents</h5>
        <a href="{{ route('admin.document-categories.index') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-folder me-2"></i>Document Categories
        </a>
    </div>
    <div class="card-body p-0" id="documentsTableContainer">
        @include('admin.documents.partials.table')
    </div>
    @if($documents->hasPages())
    <div class="card-footer" id="documentsPaginationContainer">
        @include('admin.documents.partials.pagination')
    </div>
    @endif
</div>

<!-- Backdrop -->
<div class="panel-backdrop" id="panelBackdrop" onclick="closeDocumentPanel()"></div>

<!-- Right Panel - Document Details -->
<div class="right-panel" id="documentDetailsPanel">
    <div id="documentDetailsContent">
        <!-- Content will be loaded via AJAX -->
    </div>
</div>

@push('scripts')
<script>
let selectedDocuments = [];

// Set up event delegation for document panel forms (works for dynamically loaded content)
// Attach to document body so it works for dynamically loaded content
document.addEventListener('submit', function(e) {
    const form = e.target;
    // Only handle forms inside the document panel
    const documentPanel = document.getElementById('documentDetailsPanel');
    if (!documentPanel || !documentPanel.contains(form)) {
        return;
    }
    
    if (form && form.id === 'approveDocumentForm') {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Approving...';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => Promise.reject(data));
            }
            return response.json();
        })
        .then(data => {
            if (data.success !== false) {
                alert(data.message || 'Document approved successfully.');
                closeDocumentPanel();
                location.reload();
            } else {
                alert(data.message || 'Failed to approve document. Please try again.');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const message = error.message || 'An error occurred. Please try again.';
            alert(message);
            button.disabled = false;
            button.innerHTML = originalText;
        });
        
        return false;
    }
    
    // Reject form - event delegation
    if (form && form.id === 'rejectDocumentForm') {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        const rejectionReason = form.querySelector('textarea[name="rejection_reason"]').value;
        
        if (!rejectionReason || rejectionReason.trim() === '') {
            alert('Please provide a rejection reason.');
            return false;
        }
        
        if (!confirm('Are you sure you want to reject this document?')) {
            return false;
        }
        
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Rejecting...';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => Promise.reject(data));
            }
            return response.json();
        })
        .then(data => {
            if (data.success !== false) {
                alert(data.message || 'Document rejected successfully.');
                closeDocumentPanel();
                location.reload();
            } else {
                alert(data.message || 'Failed to reject document. Please try again.');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const message = error.message || 'An error occurred. Please try again.';
            alert(message);
            button.disabled = false;
            button.innerHTML = originalText;
        });
        
        return false;
    }
});

// Select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.document-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
        if (this.checked) {
            if (!selectedDocuments.includes(parseInt(cb.value))) {
                selectedDocuments.push(parseInt(cb.value));
            }
        } else {
            selectedDocuments = [];
        }
    });
    updateBulkApproveButton();
});

// Individual checkbox
document.querySelectorAll('.document-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        const id = parseInt(this.value);
        if (this.checked) {
            if (!selectedDocuments.includes(id)) {
                selectedDocuments.push(id);
            }
        } else {
            selectedDocuments = selectedDocuments.filter(x => x !== id);
        }
        updateBulkApproveButton();
    });
});

function updateBulkApproveButton() {
    const btn = document.getElementById('bulkApproveBtn');
    const count = document.getElementById('selectedCount');
    count.textContent = selectedDocuments.length;
    btn.disabled = selectedDocuments.length === 0;
}

function showDocumentDetails(documentId) {
    const panel = document.getElementById('documentDetailsPanel');
    const backdrop = document.getElementById('panelBackdrop');
    const content = document.getElementById('documentDetailsContent');
    
    // Show loading
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading document details...</p></div>';
    panel.classList.add('open');
    backdrop.classList.add('show');
    
    fetch(`{{ url('/admin/documents') }}/${documentId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load document details');
        }
        return response.json();
    })
    .then(data => {
        content.innerHTML = data.html;
        // Event delegation is already set up, no need to rebind
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load document details. Please try again.</div>';
    });
}

function closeDocumentPanel() {
    document.getElementById('documentDetailsPanel').classList.remove('open');
    document.getElementById('panelBackdrop').classList.remove('show');
}

// Bulk approve
document.getElementById('bulkApproveBtn')?.addEventListener('click', function() {
    if (selectedDocuments.length === 0) return;
    
    if (confirm(`Approve ${selectedDocuments.length} selected documents?`)) {
        fetch('{{ url("/admin/documents/bulk-approve") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                document_ids: selectedDocuments
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
});

// AJAX filter submit (no page reload)
const filterForm = document.getElementById('documentsFilterForm');
if (filterForm) {
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const params = new URLSearchParams(new FormData(filterForm));
        const url = `${filterForm.action}?${params.toString()}`;
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                document.getElementById('documentsTableContainer').innerHTML = data.html;
            }
            if (data.pagination) {
                const paginationContainer = document.getElementById('documentsPaginationContainer');
                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination;
                } else if (data.pagination.trim()) {
                    const card = document.getElementById('documentsContainer');
                    let footer = card.querySelector('.card-footer');
                    if (!footer) {
                        footer = document.createElement('div');
                        footer.className = 'card-footer';
                        footer.id = 'documentsPaginationContainer';
                        card.appendChild(footer);
                    }
                    footer.innerHTML = data.pagination;
                }
            }
            
            // Re-bind all event listeners
            selectedDocuments = [];
            updateBulkApproveButton();
            
            // Re-bind select all
            const selectAll = document.getElementById('selectAll');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.document-checkbox');
                    checkboxes.forEach(cb => {
                        cb.checked = this.checked;
                        if (this.checked) {
                            if (!selectedDocuments.includes(parseInt(cb.value))) {
                                selectedDocuments.push(parseInt(cb.value));
                            }
                        } else {
                            selectedDocuments = [];
                        }
                    });
                    updateBulkApproveButton();
                });
            }
            
            // Re-bind individual checkboxes
            document.querySelectorAll('.document-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const id = parseInt(this.value);
                    if (this.checked) {
                        if (!selectedDocuments.includes(id)) {
                            selectedDocuments.push(id);
                        }
                    } else {
                        selectedDocuments = selectedDocuments.filter(x => x !== id);
                    }
                    updateBulkApproveButton();
                });
            });
        })
        .catch(err => {
            console.error('Filter error:', err);
            window.location = url; // fallback full reload on error
        });
    });
}
</script>
@endpush
@endsection

