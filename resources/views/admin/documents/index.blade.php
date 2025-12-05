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
    
    .right-panel {
        position: fixed;
        right: 0;
        top: 0;
        width: 400px;
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

<!-- Filters -->
<div class="filter-bar">
    <select class="filter-select" id="filterType">
        <option value="">Filter by Type</option>
        <option value="Certification">Certification</option>
        <option value="Proof of Address">Proof of Address</option>
        <option value="Product Catalog">Product Catalog</option>
        <option value="Company Registration">Company Registration</option>
    </select>
    
    <select class="filter-select" id="filterStatus">
        <option value="">Filter by Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
    </select>
    
    <button class="btn-filter" id="bulkApproveBtn" disabled>
        Bulk Approval (<span id="selectedCount">0</span>)
    </button>
    
    <button class="btn-filter">
        <i class="bi bi-download me-2"></i>Export Report
    </button>
    
    <button class="btn-filter">
        <i class="bi bi-code-slash me-2"></i>API Integration
    </button>
</div>

<!-- Documents Table -->
<div class="documents-table">
    <table>
        <thead>
            <tr>
                <th width="50">
                    <input type="checkbox" id="selectAll">
                </th>
                <th>Exhibitor</th>
                <th>Document</th>
                <th>Type</th>
                <th>Uploaded</th>
                <th width="50"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $document)
            <tr onclick="showDocumentDetails({{ $document->id }})">
                <td>
                    <input type="checkbox" class="document-checkbox" value="{{ $document->id }}" onclick="event.stopPropagation()">
                </td>
                <td>{{ $document->user->company_name ?? $document->user->name }}</td>
                <td>
                    <a href="#" class="document-link" onclick="event.stopPropagation(); showDocumentDetails({{ $document->id }}); return false;">
                        {{ $document->name }}
                    </a>
                </td>
                <td>{{ $document->type }}</td>
                <td>{{ $document->created_at->format('Y-m-d h:i A') }}</td>
                <td>
                    <i class="bi bi-chevron-right"></i>
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

@if($documents->hasPages())
<div class="mt-4">
    {{ $documents->links() }}
</div>
@endif

<!-- Right Panel - Document Details -->
<div class="right-panel" id="documentDetailsPanel">
    <div id="documentDetailsContent">
        <!-- Content will be loaded via AJAX -->
    </div>
</div>

@push('scripts')
<script>
let selectedDocuments = [];

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
    fetch(`/ems-laravel/public/admin/documents/${documentId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('documentDetailsContent').innerHTML = html;
            document.getElementById('documentDetailsPanel').classList.add('open');
        });
}

function closeDocumentPanel() {
    document.getElementById('documentDetailsPanel').classList.remove('open');
}

// Bulk approve
document.getElementById('bulkApproveBtn')?.addEventListener('click', function() {
    if (selectedDocuments.length === 0) return;
    
    if (confirm(`Approve ${selectedDocuments.length} selected documents?`)) {
        fetch('/ems-laravel/public/admin/documents/bulk-approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
</script>
@endpush
@endsection

