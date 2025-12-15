@extends('layouts.admin')

@section('title', 'Admin - Exhibition booking step 3')
@section('page-title', 'Admin - Exhibition booking step 3')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-floorplan-step2.css') }}">
@endpush

@section('content')
<input type="hidden" id="exhibitionId" value="{{ $exhibition->id }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Admin - Exhibition booking step 3</h4>
            <span class="text-muted">24 / 36</span>
        </div>
        <div class="text-center mb-4">
            <h5>Step 3</h5>
        </div>
    </div>
</div>

<form action="{{ route('admin.exhibitions.step3.store', $exhibition->id) }}" method="POST" id="paymentScheduleForm" enctype="multipart/form-data">
    @csrf

    <!-- Floorplan Management Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Floor Plan Management</h5>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="p-3">
                <label class="form-label mb-2">Upload Floorplan Background Images (Optional)</label>
                <input type="file" name="floorplan_images[]" class="form-control" accept="image/*" multiple id="floorplanImagesInput">
                <div id="floorplanNewPreview" class="d-flex flex-wrap gap-3 mt-2"></div>
                @php
                    $floorplanImages = is_array($exhibition->floorplan_images ?? null)
                        ? $exhibition->floorplan_images
                        : (array) ($exhibition->floorplan_image ? [$exhibition->floorplan_image] : []);
                @endphp
                @if(!empty($floorplanImages))
                    <div class="mt-2">
                        <span class="text-muted d-block mb-1">Existing images:</span>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($floorplanImages as $imgPath)
                                <div class="border rounded p-2 text-center" style="width: 120px;">
                                    <img
                                        src="{{ asset('storage/' . ltrim($imgPath, '/')) }}"
                                        alt="Floorplan"
                                        style="width: 100%; height: 80px; object-fit: cover; border-radius: 4px;"
                                    >
                                    <div class="form-check mt-1">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="remove_floorplan_images[]"
                                            value="{{ $imgPath }}"
                                            id="remove_floorplan_{{ md5($imgPath) }}"
                                        >
                                        <label class="form-check-label small" for="remove_floorplan_{{ md5($imgPath) }}">
                                            Remove
                                        </label>
                                    </div>
                                    <input type="hidden" name="existing_floorplan_images[]" value="{{ $imgPath }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="admin-container" style="height: 80vh;">
                <div class="admin-main">
                    <!-- Left Sidebar - Tools & Properties -->
                    <aside class="admin-sidebar">
                    <!-- Mode Selection -->
                    <div class="tool-section">
                        <h2>Tools</h2>
                        <div class="tool-buttons">
                            <button type="button" id="selectTool" class="tool-btn active" data-tool="select">
                                <span>🔍</span> Select
                            </button>
                            <button type="button" id="hallTool" class="tool-btn" data-tool="hall">
                                <span>🏢</span> Edit Hall
                            </button>
                            <button type="button" id="boothTool" class="tool-btn" data-tool="booth">
                                <span>📦</span> Add Booth
                            </button>
                            <button type="button" id="deleteTool" class="tool-btn" data-tool="delete">
                                <span>🗑️</span> Delete
                            </button>
                        </div>
                    </div>

                    <!-- Hall Properties -->
                    <div id="hallProperties" class="properties-section hidden">
                        <h2>Hall Properties</h2>
                        <div class="form-group">
                            <label>Hall Width (grid units):</label>
                            <input type="number" id="hallWidthGrid" value="24" min="10" max="60">
                            <small>Current: <span id="hallWidthPx">1200</span> px</small>
                        </div>
                        <div class="form-group">
                            <label>Hall Height (grid units):</label>
                            <input type="number" id="hallHeightGrid" value="16" min="10" max="40">
                            <small>Current: <span id="hallHeightPx">800</span> px</small>
                        </div>
                        <div class="form-group">
                            <label>Grid Size (px):</label>
                            <input type="number" id="gridSizeHall" value="50" min="10" max="100">
                        </div>
                        <button type="button" id="updateHall" class="btn-primary">Update Hall</button>
                    </div>

                    <!-- Booth Properties -->
                    <div id="boothProperties" class="properties-section hidden">
                        <h2>Booth Properties</h2>
                        <div class="form-group">
                            <label>Booth ID:</label>
                            <input type="text" id="boothId" placeholder="B001">
                        </div>
                        <div class="form-group">
                            <label>Width (px):</label>
                            <input type="number" id="boothWidth" value="100" min="50" max="500">
                        </div>
                        <div class="form-group">
                            <label>Height (px):</label>
                            <input type="number" id="boothHeight" value="80" min="50" max="500">
                        </div>
                        <div class="form-group">
                            <label>X Position:</label>
                            <input type="number" id="boothX" value="0" min="0">
                        </div>
                        <div class="form-group">
                            <label>Y Position:</label>
                            <input type="number" id="boothY" value="0" min="0">
                        </div>
                        <div class="form-group">
                            <label>Status:</label>
                            <select id="boothStatus">
                                <option value="available">Available</option>
                                <option value="reserved">Reserved</option>
                                <option value="booked">Booked</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Size Category:</label>
                            <select id="boothSize">
                                <option value="small">Small</option>
                                <option value="medium">Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Area (sq ft):</label>
                            <input type="number" id="boothArea" value="100" min="10" max="1000">
                        </div>
                        <div class="form-group">
                            <label>Size (sq ft):</label>
                            <select id="boothSizeSqft">
                                <option value="">Select size</option>
                                @foreach(($exhibition->boothSizes ?? collect()) as $size)
                                    <option value="{{ $size->id }}" data-size="{{ $size->size_sqft }}">{{ $size->size_sqft }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="button-group">
                        <button type="button" id="saveBooth" class="btn-primary">Save Booth</button>
                        <button type="button" id="deleteBooth" class="btn-danger">Delete Booth</button>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="tool-section">
                        <h2>Quick Actions</h2>
                        <div class="quick-actions">
                            <button type="button" id="snapToGrid" class="btn-secondary">Snap to Grid</button>
                            <button type="button" id="alignBooths" class="btn-secondary">Align Selected</button>
                            <button type="button" id="duplicateBooth" class="btn-secondary">Duplicate Booth</button>
                            <button type="button" id="mergeBooths" class="btn-secondary">Merge Selected (0/2)</button>
                            <button type="button" id="generateBooths" class="btn-secondary">Generate Grid</button>
                            <button type="button" id="resetFloorplan" class="btn-danger">Reset Floorplan</button>
                        </div>
                    </div>

                    <!-- Grid Settings -->
                    <div class="tool-section">
                        <h2>Grid Settings</h2>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="showGrid" checked> Show Grid
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Grid Size (px):</label>
                            <input type="number" id="gridSize" value="50" min="10" max="100">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="snapEnabled" checked> Enable Snap
                            </label>
                        </div>
                    </div>

                    <!-- Booth List -->
                    <div class="tool-section">
                        <h2>Booths List</h2>
                        <div id="boothsList" class="booths-list">
                            <p class="empty-message">No booths added</p>
                        </div>
                    </div>
                </aside>

                <!-- Center - Canvas Area -->
                <div class="canvas-area">
                    <div class="canvas-header">
                        <div class="canvas-info">
                            <span>Mode: <strong id="currentMode">Select</strong></span>
                            <span>Booths: <strong id="boothCount">0</strong></span>
                            <span>Selected: <strong id="selectedCount">0</strong></span>
                        </div>
                        <div class="canvas-controls">
                            <button type="button" id="zoomIn" class="control-btn">+</button>
                            <button type="button" id="zoomOut" class="control-btn">−</button>
                            <button type="button" id="resetView" class="control-btn">Reset</button>
                            <button type="button" id="fitToScreen" class="control-btn">Fit</button>
                        </div>
                    </div>
                    <div class="canvas-wrapper" id="canvasWrapper">
                        <svg id="adminSvg" class="admin-svg" viewBox="0 0 1200 800">
                            <defs>
                                <pattern id="gridPattern" width="50" height="50" patternUnits="userSpaceOnUse">
                                    <path d="M 50 0 L 0 0 0 50" fill="none" stroke="#e0e0e0" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <!-- Grid background -->
                            <rect id="gridBg" width="100%" height="100%" fill="url(#gridPattern)"/>
                            <!-- Hall outline -->
                            <g id="adminHallGroup"></g>
                            <!-- Booths -->
                            <g id="adminBoothsGroup"></g>
                            <!-- Selection box -->
                            <rect id="selectionBox" class="selection-box hidden" stroke="#2196f3" stroke-width="2" 
                                  stroke-dasharray="5,5" fill="rgba(33, 150, 243, 0.1)"/>
                        </svg>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Grid Modal -->
    <div id="generateModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Generate Booth Grid</h2>
                <button type="button" id="closeGenerateModal" class="modal-close" aria-label="Close">×</button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Rows</label>
                        <input type="number" id="gridRows" class="form-control" value="3" min="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Columns</label>
                        <input type="number" id="gridCols" class="form-control" value="3" min="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Booth Width (grid units)</label>
                        <input type="number" id="gridBoothWidth" class="form-control" value="2" min="1">
                        <small class="text-muted">Width px: <span id="gridBoothWidthPx">100</span></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Booth Height (grid units)</label>
                        <input type="number" id="gridBoothHeight" class="form-control" value="2" min="1">
                        <small class="text-muted">Height px: <span id="gridBoothHeightPx">100</span></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Spacing (grid units)</label>
                        <input type="number" id="gridSpacing" class="form-control" value="0" min="0">
                        <small class="text-muted">Spacing px: <span id="gridSpacingPx">0</span></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start X (grid units)</label>
                        <input type="number" id="gridStartX" class="form-control" value="2" min="0">
                        <small class="text-muted">Start X px: <span id="gridStartXPx">100</span></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Y (grid units)</label>
                        <input type="number" id="gridStartY" class="form-control" value="3" min="0">
                        <small class="text-muted">Start Y px: <span id="gridStartYPx">150</span></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Booth ID Prefix</label>
                        <input type="text" id="gridPrefix" class="form-control" value="B">
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end">
                <button type="button" id="cancelGenerateBtn" class="btn btn-secondary me-2">Cancel</button>
                <button type="button" id="generateGridBtn" class="btn btn-primary">Generate</button>
            </div>
        </div>
    </div>
    
    <!-- Payment Schedule Setup -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Payment Schedule Setup</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Number of payment ports (fixed, cannot change after creation)</p>
            
            <input type="hidden" name="payment_parts" id="payment_parts" value="3">
            
            <div id="paymentPartsContainer">
                @php
                    $schedules = $exhibition->paymentSchedules ?? collect();
                    $defaultParts = $schedules->count() > 0 ? $schedules : collect([
                        (object)['part_number' => 1, 'percentage' => null, 'due_date' => null],
                        (object)['part_number' => 2, 'percentage' => null, 'due_date' => null],
                        (object)['part_number' => 3, 'percentage' => null, 'due_date' => null],
                    ]);
                @endphp
                
                @foreach($defaultParts as $index => $part)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Part {{ $part->part_number ?? ($index + 1) }}: Percentage</label>
                        <input type="number" name="parts[{{ $index }}][percentage]" class="form-control" step="0.01" min="0" max="100" 
                               value="{{ $part->percentage ?? '' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Part {{ $part->part_number ?? ($index + 1) }}: Due Date</label>
                        <input type="date" name="parts[{{ $index }}][due_date]" class="form-control" 
                               value="{{ $part->due_date ? \Carbon\Carbon::parse($part->due_date)->format('Y-m-d') : '' }}" required>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>


    <!-- Cut-off Dates -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Cut-off Dates</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Add-on services cut-off date:</label>
                    <div class="input-group">
                        <input type="date" name="addon_services_cutoff_date" class="form-control" 
                               value="{{ $exhibition->addon_services_cutoff_date ? $exhibition->addon_services_cutoff_date->format('Y-m-d') : '' }}">
                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Document upload deadlines:</label>
                    <div class="input-group">
                        <input type="date" name="document_upload_deadline" class="form-control" 
                               value="{{ $exhibition->document_upload_deadline ? $exhibition->document_upload_deadline->format('Y-m-d') : '' }}">
                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('admin.exhibitions.step2', $exhibition->id) }}" class="btn btn-secondary me-2">Back</a>
        <button type="submit" class="btn btn-primary">Save and Continue to Step 4</button>
    </div>
</form>

@push('scripts')
<script src="{{ asset('js/admin-floorplan-step2.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('floorplanImagesInput');
    const previewContainer = document.getElementById('floorplanNewPreview');

    if (!input || !previewContainer) return;

    input.addEventListener('change', function (event) {
        const files = Array.from(event.target.files || []);
        previewContainer.innerHTML = '';

        if (!files.length) {
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
    });
});
</script>
@endpush
@endsection
