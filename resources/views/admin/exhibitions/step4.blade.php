@extends('layouts.admin')

@section('title', 'Admin Exhibition booking step 4')
@section('page-title', 'Admin Exhibition booking step 4')

@push('styles')
<style>
    .badge-size-section {
        background-color: #f8f9fa !important;
    }
    .form-control-sm {
        font-size: 0.8rem;
        padding: 0.2rem 0.4rem;
        height: calc(1.4em + 0.4rem + 2px);
    }
    .input-group-sm > .form-control {
        font-size: 0.8rem;
        padding: 0.2rem 0.4rem;
    }
    .input-group-sm > .btn {
        font-size: 0.8rem;
        padding: 0.2rem 0.4rem;
    }
    .form-label.small {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }
    .badge-size-section input[type="number"] {
        max-width: 100px;
    }
    .additional-item-row {
        width: fit-content !important;
        display: inline-flex !important;
    }
    .additional-item-row input[type="text"] {
        width: 120px !important;
        max-width: 120px !important;
        flex: 0 0 120px !important;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            @if(isset($exhibition) && $exhibition->id)
                <a href="{{ route('admin.exhibitions.edit', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 1: Exhibition Details</a>
                <a href="{{ route('admin.exhibitions.step2', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 2: Hall Plan & Pricing</a>
                <a href="{{ route('admin.exhibitions.step3', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 3: Payment Schedule</a>
                <span class="text-primary fw-bold" style="padding: 8px 16px;color: white; border-radius: 4px;">Step 4: Badge & Manual</span>
            @else
                <small class="text-muted" style="padding: 8px 16px;">Step 1: Exhibition Details</small>
                <small class="text-muted" style="padding: 8px 16px;">Step 2: Hall Plan & Pricing</small>
                <small class="text-muted" style="padding: 8px 16px;">Step 3: Payment Schedule</small>
                <small class="text-primary fw-bold" style="padding: 8px 16px;color: white; border-radius: 4px;">Step 4: Badge & Manual</small>
            @endif
        </div>
    </div>
</div>

<form action="{{ route('admin.exhibitions.step4.store', $exhibition->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <!-- Badge Management -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Badge Management</h6>
        </div>
        <div class="card-body">
            @php
                $boothSizes = $exhibition->boothSizes ?? collect();
            @endphp
            
            @if($boothSizes->count() > 0)
                @foreach($boothSizes as $sizeIndex => $boothSize)
                    @php
                        $sizeId = $boothSize->id;
                        $sizeSqft = $boothSize->size_sqft ?? 0;
                        $sizeType = $boothSize->sizeType;
                        $sizeTypeLabel = $sizeType ? ($sizeType->length . ' x ' . $sizeType->width) : '';
                        
                        // Get badge configs for this size
                        $sizeBadgeConfigs = $exhibition->badgeConfigurations->where('exhibition_booth_size_id', $sizeId)->keyBy('badge_type');
                        $primaryConfig = $sizeBadgeConfigs->get('Primary');
                        $secondaryConfig = $sizeBadgeConfigs->get('Secondary');
                        $additionalConfig = $sizeBadgeConfigs->get('Additional');
                        $additionalItems = $additionalConfig->access_permissions ?? ['Lunch', 'Entry Only', 'Snacks'];
                    @endphp
                    
                    <div class="badge-size-section mb-4 p-3 border rounded" style="background-color: #f8f9fa;">
                        <h6 class="mb-3 fw-bold">
                            Size {{ $sizeSqft }} sq meter
                            @if($sizeTypeLabel)
                                <span class="text-muted fw-normal">({{ $sizeTypeLabel }})</span>
                            @endif
                        </h6>
                        
                        <!-- Primary & Secondary Badge - One Line -->
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-2">
                                <label class="form-label mb-0 small">Primary Badge</label>
                            </div>
                            <div class="col-md-1">
                                <input type="number" name="badge_configurations[{{ $sizeId }}][Primary][quantity]" 
                                       class="form-control form-control-sm" placeholder="Qty" min="0" 
                                       value="{{ $primaryConfig->quantity ?? 0 }}" style="width: 70px;">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="badge_configurations[{{ $sizeId }}][Primary][price]" 
                                       class="form-control form-control-sm" placeholder="Price" step="0.01" min="0"
                                       value="{{ $primaryConfig->price ?? 0 }}" style="width: 90px;">
                            </div>
                            <input type="hidden" name="badge_configurations[{{ $sizeId }}][Primary][badge_type]" value="Primary">
                            <input type="hidden" name="badge_configurations[{{ $sizeId }}][Primary][exhibition_booth_size_id]" value="{{ $sizeId }}">
                            
                            <div class="col-md-2">
                                <label class="form-label mb-0 small">Secondary Badge</label>
                            </div>
                            <div class="col-md-1">
                                <input type="number" name="badge_configurations[{{ $sizeId }}][Secondary][quantity]" 
                                       class="form-control form-control-sm" placeholder="Qty" min="0"
                                       value="{{ $secondaryConfig->quantity ?? 0 }}" style="width: 70px;">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="badge_configurations[{{ $sizeId }}][Secondary][price]" 
                                       class="form-control form-control-sm" placeholder="Price" step="0.01" min="0"
                                       value="{{ $secondaryConfig->price ?? 0 }}" style="width: 90px;">
                            </div>
                            <input type="hidden" name="badge_configurations[{{ $sizeId }}][Secondary][badge_type]" value="Secondary">
                            <input type="hidden" name="badge_configurations[{{ $sizeId }}][Secondary][exhibition_booth_size_id]" value="{{ $sizeId }}">
                        </div>

                        <!-- Additional Badge Settings - Compact -->
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <label class="form-label mb-0 small">Additional Badge</label>
                            </div>
                            <div class="col-md-10">
                                <div class="mb-2">
                                    <label class="form-label d-block small mb-1">Need Admin approval?</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" 
                                               name="badge_configurations[{{ $sizeId }}][Additional][needs_admin_approval]" 
                                               id="approval_yes_{{ $sizeId }}" value="1"
                                               {{ ($additionalConfig->needs_admin_approval ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="approval_yes_{{ $sizeId }}">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" 
                                               name="badge_configurations[{{ $sizeId }}][Additional][needs_admin_approval]" 
                                               id="approval_no_{{ $sizeId }}" value="0"
                                               {{ !($additionalConfig->needs_admin_approval ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="approval_no_{{ $sizeId }}">No</label>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                        When enabled, any badge created beyond the free quantity (and charged) will stay in pending status until approved by admin.
                                    </small>
                                </div>

                                <div>
                                    <label class="form-label d-block small mb-1">Items included</label>
                                    <div id="additionalItemsContainer_{{ $sizeId }}" class="mb-2">
                                        @foreach($additionalItems as $itemIndex => $item)
                                            <div class="input-group input-group-sm mb-1 additional-item-row" style="width: fit-content;">
                                                <input type="text" 
                                                       name="badge_configurations[{{ $sizeId }}][Additional][access_permissions][]" 
                                                       class="form-control form-control-sm" placeholder="Item name"
                                                       value="{{ $item }}" style="width: 120px; max-width: 120px;">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-additional-item" style="padding: 0.2rem 0.4rem;">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-sm" 
                                            onclick="addAdditionalItem({{ $sizeId }})" style="padding: 0.2rem 0.4rem; font-size: 0.75rem;">
                                        <i class="bi bi-plus-circle me-1"></i>Add Item
                                    </button>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                        These items describe what is bundled with an additional (paid) badge, e.g. Lunch, Entry Only, Snacks.
                                    </small>
                                </div>
                                <input type="hidden" name="badge_configurations[{{ $sizeId }}][Additional][badge_type]" value="Additional">
                                <input type="hidden" name="badge_configurations[{{ $sizeId }}][Additional][exhibition_booth_size_id]" value="{{ $sizeId }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>Please add booth sizes in Step 2 first to configure badges.
                </div>
            @endif
        </div>
    </div>

    <!-- Exhibition Manual -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Exhibition Manual</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">PDF Upload Section</label>
                <div class="border border-2 border-dashed rounded p-4 text-center" style="background-color: #f8f9fa;">
                    <input type="file" name="exhibition_manual_pdf" id="manual_pdf" class="d-none" accept=".pdf" onchange="updateFileName(this)">
                    <button type="button" class="btn btn-primary mb-2" onclick="document.getElementById('manual_pdf').click()">
                        Choose files to Upload
                    </button>
                    <p class="mb-0 text-muted">
                        <i class="bi bi-cloud-upload"></i> or drag and drop them here
                    </p>
                    <small id="file_name" class="text-muted"></small>
                    @if($exhibition->exhibition_manual_pdf)
                        <p class="mt-2 mb-0">
                            <small>Current: <a href="{{ asset('storage/' . $exhibition->exhibition_manual_pdf) }}" target="_blank">{{ basename($exhibition->exhibition_manual_pdf) }}</a></small>
                        </p>
                    @endif
                </div>
            </div>
            <button type="button" class="btn btn-secondary" onclick="previewPDF()">Preview</button>
        </div>
    </div>

    <!-- Required Documents -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Required Documents</h6>
            <button type="button" class="btn btn-sm btn-primary" onclick="addRequiredDocument()">
                <i class="bi bi-plus-circle"></i> Add Document
            </button>
        </div>
        <div class="card-body">
            <div id="requiredDocumentsContainer">
                @php
                    $requiredDocs = $exhibition->requiredDocuments ?? collect();
                @endphp
                @if($requiredDocs->count() > 0)
                    @foreach($requiredDocs as $index => $doc)
                        <div class="required-document-item mb-3 p-3 border rounded" data-index="{{ $index }}">
                            <div class="row">
                                <div class="col-md-5">
                                    <label class="form-label">Document Name</label>
                                    <input type="text" name="required_documents[{{ $index }}][document_name]" 
                                           class="form-control" value="{{ $doc->document_name }}" required>
                                    <input type="hidden" name="required_documents[{{ $index }}][id]" value="{{ $doc->id }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Document Type</label>
                                    <select name="required_documents[{{ $index }}][document_type]" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="image" {{ $doc->document_type === 'image' ? 'selected' : '' }}>Image</option>
                                        <option value="pdf" {{ $doc->document_type === 'pdf' ? 'selected' : '' }}>PDF</option>
                                        <option value="both" {{ $doc->document_type === 'both' ? 'selected' : '' }}>Both (Image/PDF)</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeRequiredDocument(this)">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div id="emptyRequiredDocuments" class="text-muted text-center py-3" style="{{ $requiredDocs->count() > 0 ? 'display:none;' : '' }}">
                No required documents added yet. Click "Add Document" to add one.
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='{{ route('admin.exhibitions.index') }}'">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

@push('scripts')
<script>
let requiredDocumentIndex = {{ $requiredDocs->count() ?? 0 }};

function addRequiredDocument() {
    const container = document.getElementById('requiredDocumentsContainer');
    const emptyMessage = document.getElementById('emptyRequiredDocuments');
    
    const item = document.createElement('div');
    item.className = 'required-document-item mb-3 p-3 border rounded';
    item.setAttribute('data-index', requiredDocumentIndex);
    item.innerHTML = `
        <div class="row">
            <div class="col-md-5">
                <label class="form-label">Document Name</label>
                <input type="text" name="required_documents[${requiredDocumentIndex}][document_name]" 
                       class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Document Type</label>
                <select name="required_documents[${requiredDocumentIndex}][document_type]" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="image">Image</option>
                    <option value="pdf">PDF</option>
                    <option value="both">Both (Image/PDF)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeRequiredDocument(this)">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(item);
    requiredDocumentIndex++;
    
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
}

function removeRequiredDocument(button) {
    const item = button.closest('.required-document-item');
    item.remove();
    
    const container = document.getElementById('requiredDocumentsContainer');
    const emptyMessage = document.getElementById('emptyRequiredDocuments');
    
    if (container.children.length === 0 && emptyMessage) {
        emptyMessage.style.display = 'block';
    }
}

function updateFileName(input) {
    const fileName = input.files[0]?.name || '';
    document.getElementById('file_name').textContent = fileName;
}

function updateVariationFiles(input) {
    const files = Array.from(input.files || []);
    const fileCount = files.length;
    const infoEl = document.getElementById('variation_files');
    const previewContainer = document.getElementById('stallVariationsPreview');

    if (infoEl) {
        infoEl.textContent = fileCount > 0 ? `${fileCount} file(s) selected` : '';
    }

    if (!previewContainer) {
        return;
    }

    // Clear existing previews
    previewContainer.innerHTML = '';

    if (!fileCount) {
        return;
    }

    files.forEach((file) => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            const wrapper = document.createElement('div');
            wrapper.className = 'border rounded p-2 text-center';
            wrapper.style.width = '120px';
            wrapper.innerHTML = `
                <img src="${e.target.result}" alt="${file.name}" style="width: 100%; height: 80px; object-fit: cover; border-radius: 4px;">
                <div class="small mt-1 text-truncate" title="${file.name}">${file.name}</div>
            `;
            previewContainer.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
    });
}

function previewPDF() {
    const fileInput = document.getElementById('manual_pdf');
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const url = URL.createObjectURL(file);
        window.open(url, '_blank');
    } else {
        alert('Please select a PDF file first');
    }
}

function previewVariations() {
    const fileInput = document.getElementById('stall_variations');
    if (fileInput.files.length > 0) {
        // Open preview modal or new window
        alert('Preview functionality will open in a viewer');
    } else {
        alert('Please select variation images first');
    }
}

// Dynamic additional badge items per size
function addAdditionalItem(sizeId) {
    const container = document.getElementById('additionalItemsContainer_' + sizeId);
    if (!container) {
        return;
    }

    const row = document.createElement('div');
    row.className = 'input-group input-group-sm mb-1 additional-item-row';
    row.style.width = 'fit-content';
    row.innerHTML = `
        <input type="text" name="badge_configurations[${sizeId}][Additional][access_permissions][]" 
               class="form-control form-control-sm" placeholder="Item name" style="width: 120px; max-width: 120px;">
        <button type="button" class="btn btn-outline-danger btn-sm remove-additional-item" style="padding: 0.2rem 0.4rem;">
            <i class="bi bi-x"></i>
        </button>
    `;
    container.appendChild(row);
}

document.addEventListener('DOMContentLoaded', function () {
    // Handle remove buttons for all size sections
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-additional-item');
        if (!btn) return;
        const row = btn.closest('.additional-item-row');
        if (row) {
            row.remove();
        }
    });
});
</script>
@endpush
@endsection
