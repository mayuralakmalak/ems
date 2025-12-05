<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5>{{ $document->name }}</h5>
        <p class="text-muted mb-0">Exhibitor: {{ $document->user->company_name ?? $document->user->name }}</p>
    </div>
    <button class="btn btn-sm btn-outline-secondary" onclick="closeDocumentPanel()">
        <i class="bi bi-x"></i>
    </button>
</div>

<div class="document-preview">
    @if($document->file_path)
    <iframe src="{{ asset('storage/' . $document->file_path) }}" style="width: 100%; height: 100%; border: none;"></iframe>
    @else
    <i class="bi bi-file-earmark" style="font-size: 4rem; color: #cbd5e1;"></i>
    @endif
</div>

<div class="detail-item">
    <div class="detail-label">Type</div>
    <div class="detail-value">{{ $document->type }}</div>
</div>

<div class="detail-item">
    <div class="detail-label">Uploaded</div>
    <div class="detail-value">{{ $document->created_at->format('Y-m-d h:i A') }}</div>
</div>

<div class="detail-item">
    <div class="detail-label">Expiry</div>
    <div class="detail-value">{{ $document->expiry_date ? $document->expiry_date->format('Y-m-d') : 'N/A' }}</div>
</div>

<div class="detail-item">
    <div class="detail-label">Status</div>
    <div>
        <span class="status-badge {{ $document->status === 'approved' ? 'status-approved' : ($document->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
            {{ ucfirst($document->status) }}
        </span>
    </div>
</div>

<div class="detail-item">
    <div class="detail-label">Automatic reminder</div>
    <div class="detail-value">Yes</div>
</div>

<div class="detail-item">
    <div class="detail-label">Compliance Tags</div>
    <div class="detail-value">None</div>
</div>

<div class="mt-4">
    <h6 class="mb-3">Verification History</h6>
    
    <form method="POST" action="{{ route('admin.documents.approve', $document->id) }}" class="mb-3">
        @csrf
        <div class="mb-3">
            <label class="form-label">Manual Verification</label>
            <textarea name="verification_comments" class="form-control" rows="3" placeholder="Enter verification comments..."></textarea>
        </div>
        <button type="submit" class="btn-approve">Approve Document</button>
    </form>
    
    <form method="POST" action="{{ route('admin.documents.reject', $document->id) }}">
        @csrf
        <div class="mb-3">
            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Enter rejection reason..." required></textarea>
        </div>
        <button type="submit" class="btn-reject">Reject Document</button>
    </form>
</div>

