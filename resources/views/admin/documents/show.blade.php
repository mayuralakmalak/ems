<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1">{{ $document->name }}</h5>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">
            @if($document->requiredDocument)
                Required Document: {{ $document->requiredDocument->document_name }}
            @else
                Document Type: {{ $document->type }}
            @endif
        </p>
    </div>
    <button class="btn btn-sm btn-outline-secondary" onclick="closeDocumentPanel()" style="border-radius: 50%; width: 32px; height: 32px; padding: 0;">
        <i class="bi bi-x"></i>
    </button>
</div>

<!-- Document Preview -->
<div class="document-preview" style="position: relative;">
    @if($document->file_path)
        @php
            $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
            $isPdf = strtolower($extension) === 'pdf';
        @endphp
        @if($isImage)
            <img src="{{ asset('storage/' . $document->file_path) }}" alt="{{ $document->name }}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px;">
        @elseif($isPdf)
            <iframe src="{{ asset('storage/' . $document->file_path) }}#toolbar=0" style="width: 100%; height: 100%; border: none; border-radius: 8px;"></iframe>
        @else
            <div class="text-center" style="padding: 40px;">
                <i class="bi bi-file-earmark-text" style="font-size: 4rem; color: #6366f1;"></i>
                <p class="text-muted mt-3">{{ strtoupper($extension) }} File</p>
                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                    <i class="bi bi-download me-1"></i> Open File
                </a>
            </div>
        @endif
    @else
        <div class="text-center" style="padding: 40px;">
            <i class="bi bi-file-earmark" style="font-size: 4rem; color: #cbd5e1;"></i>
            <p class="text-muted mt-2">No file available</p>
        </div>
    @endif
</div>

@if($document->file_path)
<div class="mb-3 text-center">
    <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-download me-1"></i> Download Document
    </a>
</div>
@endif

<!-- Document Information -->
<div class="detail-item">
    <div class="detail-label">Document Name</div>
    <div class="detail-value">{{ $document->name }}</div>
</div>

@if($document->requiredDocument)
<div class="detail-item">
    <div class="detail-label">Required Document Type</div>
    <div class="detail-value">{{ $document->requiredDocument->document_name }} ({{ strtoupper($document->requiredDocument->document_type) }})</div>
</div>
@else
<div class="detail-item">
    <div class="detail-label">Document Type</div>
    <div class="detail-value">{{ $document->type }}</div>
</div>
@endif

<div class="detail-item">
    <div class="detail-label">Status</div>
    <div>
        <span class="status-badge {{ $document->status === 'approved' ? 'status-approved' : ($document->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
            {{ ucfirst($document->status) }}
        </span>
    </div>
</div>

<div class="detail-item">
    <div class="detail-label">Uploaded Date</div>
    <div class="detail-value">{{ $document->created_at->format('Y-m-d h:i A') }}</div>
</div>

@if($document->expiry_date)
<div class="detail-item">
    <div class="detail-label">Expiry Date</div>
    <div class="detail-value">{{ $document->expiry_date->format('Y-m-d') }}</div>
</div>
@endif

@if($document->file_size)
<div class="detail-item">
    <div class="detail-label">File Size</div>
    <div class="detail-value">{{ number_format($document->file_size / 1024, 2) }} KB</div>
</div>
@endif

<!-- User Information -->
<div class="detail-item" style="border-top: 2px solid #e2e8f0; margin-top: 20px; padding-top: 20px;">
    <div class="detail-label" style="font-weight: 600; color: #1e293b;">User Information</div>
</div>

<div class="detail-item">
    <div class="detail-label">Name</div>
    <div class="detail-value">{{ $document->user->name ?? 'N/A' }}</div>
</div>

<div class="detail-item">
    <div class="detail-label">Company Name</div>
    <div class="detail-value">{{ $document->user->company_name ?? 'N/A' }}</div>
</div>

<div class="detail-item">
    <div class="detail-label">Email</div>
    <div class="detail-value">{{ $document->user->email ?? 'N/A' }}</div>
</div>

@if($document->user->phone)
<div class="detail-item">
    <div class="detail-label">Phone</div>
    <div class="detail-value">{{ $document->user->phone }}</div>
</div>
@endif

<!-- Exhibition Information -->
@if($document->booking && $document->booking->exhibition)
<div class="detail-item" style="border-top: 2px solid #e2e8f0; margin-top: 20px; padding-top: 20px;">
    <div class="detail-label" style="font-weight: 600; color: #1e293b;">Exhibition Information</div>
</div>

<div class="detail-item">
    <div class="detail-label">Exhibition Name</div>
    <div class="detail-value">{{ $document->booking->exhibition->name ?? 'N/A' }}</div>
</div>

<div class="detail-item">
    <div class="detail-label">Booking Number</div>
    <div class="detail-value">{{ $document->booking->booking_number ?? 'N/A' }}</div>
</div>

@if($document->booking->exhibition->venue)
<div class="detail-item">
    <div class="detail-label">Venue</div>
    <div class="detail-value">{{ $document->booking->exhibition->venue }}</div>
</div>
@endif

@if($document->booking->exhibition->start_date)
<div class="detail-item">
    <div class="detail-label">Exhibition Dates</div>
    <div class="detail-value">
        {{ $document->booking->exhibition->start_date->format('M d, Y') }} - 
        {{ $document->booking->exhibition->end_date->format('M d, Y') }}
    </div>
</div>
@endif
@endif

<!-- Rejection Reason (if rejected) -->
@if($document->status === 'rejected' && $document->rejection_reason)
<div class="alert alert-danger mt-3">
    <strong>Rejection Reason:</strong>
    <div class="mt-2">{{ $document->rejection_reason }}</div>
</div>
@endif

<!-- Verification Actions -->
<div class="mt-4" style="border-top: 2px solid #e2e8f0; padding-top: 20px;">
    <h6 class="mb-3" style="font-weight: 600;">Verification Actions</h6>

    @if($document->status === 'approved')
        <div class="alert alert-success mb-0">
            <i class="bi bi-check-circle me-2"></i>This document has been approved.
            @if($document->updated_at)
                <div class="mt-2 small">Approved on: {{ $document->updated_at->format('Y-m-d h:i A') }}</div>
            @endif
        </div>
    @elseif($document->status === 'rejected')
        <div class="alert alert-danger mb-0">
            <i class="bi bi-x-circle me-2"></i>This document has been rejected.
            @if($document->rejection_reason)
                <div class="mt-2"><strong>Reason:</strong> {{ $document->rejection_reason }}</div>
            @endif
            @if($document->updated_at)
                <div class="mt-2 small">Rejected on: {{ $document->updated_at->format('Y-m-d h:i A') }}</div>
            @endif
        </div>
    @else
        <form id="approveDocumentForm" method="POST" action="{{ url('/admin/documents/' . $document->id . '/approve') }}" class="mb-3">
            @csrf
            <div class="mb-3">
                <label class="form-label" style="font-weight: 500;">Verification Comments (Optional)</label>
                <textarea name="verification_comments" class="form-control" rows="3" placeholder="Enter any verification comments..."></textarea>
            </div>
            <button type="submit" class="btn-approve">
                <i class="bi bi-check-circle me-2"></i>Approve Document
            </button>
        </form>
        
        <form id="rejectDocumentForm" method="POST" action="{{ url('/admin/documents/' . $document->id . '/reject') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" style="font-weight: 500;">Rejection Reason <span class="text-danger">*</span></label>
                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Enter rejection reason..." required></textarea>
                <small class="text-muted">This reason will be shown to the exhibitor.</small>
            </div>
            <button type="submit" class="btn-reject">
                <i class="bi bi-x-circle me-2"></i>Reject Document
            </button>
        </form>
    @endif
</div>

