@extends('layouts.admin')

@section('title', 'Admin Exhibition booking step 4')
@section('page-title', 'Admin Exhibition booking step 4')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Admin Exhibition booking step 4</h4>
            <span class="text-muted">25 / 36</span>
        </div>
        <div class="text-center mb-4">
            <h5>Step 4</h5>
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
                $badgeConfigs = $exhibition->badgeConfigurations->keyBy('badge_type');
                $additionalConfig = $badgeConfigs->get('Additional');
                $additionalItems = $additionalConfig->access_permissions ?? ['Lunch', 'Entry Only', 'Snacks'];
            @endphp
            
            <!-- Primary Badge -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Primary Badge</label>
                </div>
                <div class="col-md-2">
                    <input type="number" name="badge_configurations[Primary][quantity]" class="form-control" 
                           placeholder="Free quantity" min="0" 
                           value="{{ $badgeConfigs->get('Primary')->quantity ?? 0 }}">
                </div>
                <div class="col-md-4 offset-md-1">
                    <input type="number" name="badge_configurations[Primary][price]" class="form-control" 
                           placeholder="Price per additional Primary badge" step="0.01" min="0"
                           value="{{ $badgeConfigs->get('Primary')->price ?? 0 }}">
                    <input type="hidden" name="badge_configurations[Primary][badge_type]" value="Primary">
                </div>
            </div>

            <!-- Secondary Badge -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Secondary Badge</label>
                </div>
                <div class="col-md-2">
                    <input type="number" name="badge_configurations[Secondary][quantity]" class="form-control" 
                           placeholder="Free quantity" min="0"
                           value="{{ $badgeConfigs->get('Secondary')->quantity ?? 0 }}">
                </div>
                <div class="col-md-4 offset-md-1">
                    <input type="number" name="badge_configurations[Secondary][price]" class="form-control" 
                           placeholder="Price per additional Secondary badge" step="0.01" min="0"
                           value="{{ $badgeConfigs->get('Secondary')->price ?? 0 }}">
                    <input type="hidden" name="badge_configurations[Secondary][badge_type]" value="Secondary">
                </div>
            </div>

            <!-- Additional Badge Settings (applies when free quota is exceeded) -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Additional Badge Settings</label>
                </div>
                <div class="col-md-9">
                    <div class="mb-2">
                        <label class="form-label d-block">Need Admin approval for additional (paid) badges?</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="badge_configurations[Additional][needs_admin_approval]" 
                                   id="approval_yes" value="1"
                                   {{ ($additionalConfig->needs_admin_approval ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="badge_configurations[Additional][needs_admin_approval]" 
                                   id="approval_no" value="0"
                                   {{ !($additionalConfig->needs_admin_approval ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="approval_no">No</label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            When enabled, any badge created beyond the free quantity (and charged) will stay in pending status until approved by admin.
                        </small>
                    </div>

                    <div class="mt-3">
                        <label class="form-label d-block">Items to be included in Additional Badge</label>
                        <div id="additionalItemsContainer">
                            @foreach($additionalItems as $index => $item)
                                <div class="input-group mb-2 additional-item-row">
                                    <input type="text" name="badge_configurations[Additional][access_permissions][]" 
                                           class="form-control" placeholder="Item name"
                                           value="{{ $item }}">
                                    <button type="button" class="btn btn-outline-danger remove-additional-item">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addAdditionalItemBtn">
                            <i class="bi bi-plus-circle me-1"></i>Add Item
                        </button>
                        <small class="text-muted d-block mt-1">
                            These items describe what is bundled with an additional (paid) badge, e.g. Lunch, Entry Only, Snacks.
                        </small>
                    </div>
                </div>
            </div>
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

    <!-- Stall Variation Management -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Stall Variation Management</h6>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Upload visual designs for all stall types</p>
            
            <div class="mb-3">
                <label class="form-label">Upload Stall Variations</label>
                <div class="border border-2 border-dashed rounded p-4 text-center" style="background-color: #f8f9fa;">
                    <input type="file" name="stall_variations[]" id="stall_variations" class="d-none" accept="image/*" multiple onchange="updateVariationFiles(this)">
                    <button type="button" class="btn btn-primary mb-2" onclick="document.getElementById('stall_variations').click()">
                        Choose files to Upload
                    </button>
                    <p class="mb-0 text-muted">
                        <i class="bi bi-cloud-upload"></i> or drag and drop them here
                    </p>
                    <small id="variation_files" class="text-muted"></small>
                    <div id="stallVariationsPreview" class="d-flex flex-wrap gap-3 mt-3"></div>
                </div>
            </div>

            <!-- Existing Stall Variation Thumbnails -->
            @php
                $variation = $exhibition->stallVariations->first();
            @endphp
            @if($variation && ($variation->front_view || $variation->side_view_left || $variation->side_view_right))
                <div class="mb-3">
                    <label class="form-label">Existing Stall Variation Images</label>
                    <div class="d-flex flex-wrap gap-3">
                        @if($variation->front_view)
                            <div class="border rounded p-2 text-center" style="width: 140px; background-color: #f8f9fa;">
                                <img src="{{ asset('storage/' . $variation->front_view) }}" class="img-fluid mb-1" alt="Front View">
                                <small class="text-muted d-block text-truncate">Front View</small>
                            </div>
                        @endif
                        @if($variation->side_view_left)
                            <div class="border rounded p-2 text-center" style="width: 140px; background-color: #f8f9fa;">
                                <img src="{{ asset('storage/' . $variation->side_view_left) }}" class="img-fluid mb-1" alt="Left View">
                                <small class="text-muted d-block text-truncate">Side View (Left)</small>
                            </div>
                        @endif
                        @if($variation->side_view_right)
                            <div class="border rounded p-2 text-center" style="width: 140px; background-color: #f8f9fa;">
                                <img src="{{ asset('storage/' . $variation->side_view_right) }}" class="img-fluid mb-1" alt="Right View">
                                <small class="text-muted d-block text-truncate">Side View (Right)</small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            <button type="button" class="btn btn-secondary" onclick="previewVariations()">Preview in Viewer</button>
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

// Dynamic additional badge items
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('additionalItemsContainer');
    const addBtn = document.getElementById('addAdditionalItemBtn');

    if (!container || !addBtn) {
        return;
    }

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'input-group mb-2 additional-item-row';
        row.innerHTML = `
            <input type="text" name="badge_configurations[Additional][access_permissions][]" 
                   class="form-control" placeholder="Item name">
            <button type="button" class="btn btn-outline-danger remove-additional-item">
                <i class="bi bi-x"></i>
            </button>
        `;
        container.appendChild(row);
    });

    container.addEventListener('click', function (e) {
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
