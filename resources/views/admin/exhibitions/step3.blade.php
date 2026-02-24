@extends('layouts.admin')

@section('title', 'Admin - Exhibition booking step 3')
@section('page-title', 'Admin - Exhibition booking step 3')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-floorplan-step2.css') }}">
@endpush

@section('content')
<input type="hidden" id="exhibitionId" value="{{ $exhibition->id }}">
<input type="hidden" id="currentFloorId" value="{{ $selectedFloorId ?? '' }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row mb-4">
    <div class="col-12">
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            @if(isset($exhibition) && $exhibition->id)
                <a href="{{ route('admin.exhibitions.edit', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 1: Exhibition Details</a>
                <a href="{{ route('admin.exhibitions.step2', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 2: Hall Plan & Pricing</a>
                <span class="text-primary fw-bold" style="padding: 8px 16px;color: white; border-radius: 4px;">Step 3: Payment Schedule</span>
                <a href="{{ route('admin.exhibitions.step4', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 4: Badge & Manual</a>
            @else
                <small class="text-muted" style="padding: 8px 16px;">Step 1: Exhibition Details</small>
                <small class="text-muted" style="padding: 8px 16px;">Step 2: Hall Plan & Pricing</small>
                <small class="text-primary fw-bold" style="padding: 8px 16px;color: white; border-radius: 4px;">Step 3: Payment Schedule</small>
                <small class="text-muted" style="padding: 8px 16px;">Step 4: Badge & Manual</small>
            @endif
        </div>
    </div>
</div>

<form action="{{ route('admin.exhibitions.step3.store', $exhibition->id) }}" method="POST" id="paymentScheduleForm" enctype="multipart/form-data">
    @csrf

    <!-- Floor Selection Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-layers me-2"></i>Select Hall for Hall Plan Management</h5>
        </div>
        <div class="card-body">
            @php
                $floors = $exhibition->floors ?? collect();
                $selectedFloorId = request()->get('floor_id', $floors->first()?->id);
            @endphp
            
            @if($floors->count() > 0)
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Hall:</label>
                    <select id="floorSelector" class="form-select" style="max-width: 400px;">
                        @foreach($floors as $floor)
                            <option value="{{ $floor->id }}" {{ $selectedFloorId == $floor->id ? 'selected' : '' }}>
                                {{ $floor->name }} (Hall #{{ $floor->floor_number }})
                                @if(!$floor->is_active)
                                    - Inactive
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">Select a hall to manage its hall plan and booths. Each hall has its own independent hall plan.</small>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No halls configured. Please go back to <a href="{{ route('admin.exhibitions.step2', $exhibition->id) }}">Step 2</a> to add halls first.
                </div>
            @endif
        </div>
    </div>

    <!-- Floorplan Management Section -->
    <div class="card mb-4" id="floorplanCard">
        <div class="card-header">
            <h5 class="mb-0">
                <span id="floorplanTitle">Hall Plan Management</span>
                <span id="selectedFloorName" class="badge bg-primary ms-2"></span>
            </h5>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="p-3" id="floorplanImagesSection">
                <label class="form-label mb-2">Upload Hall Plan Background Images for <span id="currentFloorName">Selected Hall</span> (Optional)</label>
                <input type="file" name="floorplan_images[]" class="form-control" accept="image/*" multiple id="floorplanImagesInput">
                <input type="hidden" id="currentFloorIdForm" name="current_floor_id" value="{{ $selectedFloorId }}">
                <div id="floorplanNewPreview" class="d-flex flex-wrap gap-3 mt-2"></div>
                <div id="existingFloorplanImages" class="mt-2">
                    <!-- Existing images will be loaded dynamically based on selected floor -->
                </div>
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
                            <input type="number" id="hallWidthGrid" name="hallWidthGrid" value="24">
                            <small>Current: <span id="hallWidthPx">2000</span> px</small>
                        </div>
                        <div class="form-group">
                            <label>Hall Height (grid units):</label>
                            <input type="number" id="hallHeightGrid" name="hallHeightGrid" value="16">
                            <small>Current: <span id="hallHeightPx">800</span> px</small>
                        </div>
                        <div class="form-group">
                            <label>Grid Size (px):</label>
                            <input type="number" id="gridSizeHall" name="gridSizeHall" value="50">
                        </div>
                        <button type="button" id="updateHall" class="btn-primary">Update Hall</button>
                    </div>

                    <!-- Booth Properties -->
                    <div id="boothProperties" class="properties-section hidden">
                        <h2>Booth Properties</h2>
                        <div class="form-group">
                            <label>Booth ID:</label>
                            <input type="text" id="boothId" name="boothId" placeholder="B001">
                        </div>
                        <div class="form-group">
                            <label>Width (px):</label>
                            <input type="number" id="boothWidth" name="boothWidth" value="100">
                        </div>
                        <div class="form-group">
                            <label>Height (px):</label>
                            <input type="number" id="boothHeight" name="boothHeight" value="80">
                        </div>
                        <div class="form-group">
                            <label>X Position:</label>
                            <input type="number" id="boothX" name="boothX" value="0">
                        </div>
                        <div class="form-group">
                            <label>Y Position:</label>
                            <input type="number" id="boothY" name="boothY" value="0">
                        </div>
                        <div class="form-group">
                            <label>Status:</label>
                            <select id="boothStatus" name="boothStatus">
                                <option value="available">Available</option>
                                <option value="reserved">Reserved</option>
                                <option value="booked">Booked</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Size Category:</label>
                            <select id="boothSize" name="boothSize">
                                <option value="small">Small</option>
                                <option value="medium">Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Area (sq meter):</label>
                            <input type="number" id="boothArea" name="boothArea" value="100">
                        </div>
                        <div class="form-group">
                            <label>Sides Open:</label>
                            <select id="boothSidesOpen" name="boothSidesOpen">
                                <option value="1">1 Side</option>
                                <option value="2">2 Sides</option>
                                <option value="3">3 Sides</option>
                                <option value="4">4 Sides</option>
                            </select>
                            <small class="text-muted d-block mt-1">Price on frontend is calculated based on this. Default: 1 Side.</small>
                        </div>
                        <div class="form-group">
                            <label>Size (sq meter):</label>
                            <select id="boothSizeSqft" name="boothSizeSqft">
                                <option value="">Select size</option>
                                @foreach(($exhibition->boothSizes ?? collect()) as $size)
                                    @php
                                        $categoryLabel = 'Standard';
                                        if($size->category == '1' || $size->category == 'Premium') {
                                            $categoryLabel = 'Premium';
                                        } elseif($size->category == '3' || $size->category == 'Economy') {
                                            $categoryLabel = 'Economy';
                                        }
                                    @endphp
                                    <option value="{{ $size->id }}" 
                                            data-size="{{ $size->size_sqft }}"
                                            data-category="{{ $size->category }}">
                                        {{ $size->size_sqft }} sq meter - {{ $categoryLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Revenue %:</label>
                            <input type="number" id="boothRevenuePercentage" name="boothRevenuePercentage" min="0" max="100" step="0.01" value="0" placeholder="0">
                            <small class="text-muted d-block mt-1">Revenue share for this booth (sum of all booths = Total Revenue Coverage).</small>
                        </div>
                        <!-- <div class="form-group">
                            <label>Discount (optional):</label>
                            <select id="boothDiscount">
                                <option value="">No Discount</option>
                                @foreach(($activeDiscounts ?? collect()) as $discount)
                                    <option value="{{ $discount->id }}"
                                            data-type="{{ $discount->type }}"
                                            data-amount="{{ $discount->amount }}">
                                        {{ $discount->title }} ({{ $discount->type === 'percentage' ? $discount->amount . '%' : '₹' . number_format($discount->amount, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div> -->
                        <!-- <div class="form-group">
                            <label>Special Price For User (optional):</label>
                            <select id="boothDiscountUser">
                                <option value="">All Users</option>
                                @foreach(($exhibitors ?? collect()) as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">If selected, the discount will apply only when this exhibitor books the booth.</small>
                        </div> -->
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
                            <button type="button" id="resetFloorplan" class="btn-danger">Reset Hall Plan</button>
                        </div>
                    </div>

                    <!-- Grid Settings -->
                    <div class="tool-section">
                        <h2>Grid Settings</h2>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="showGrid" name="showGrid" checked> Show Grid
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Grid Size (px):</label>
                            <input type="number" id="gridSize" name="gridSize" value="50">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="snapEnabled" name="snapEnabled" checked> Enable Snap
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
                <div class="canvas-area" style="overflow: scroll">
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
                    <div class="canvas-wrapper" id="canvasWrapper" style="overflow: auto; min-width: 100%; min-height: 100%;">
                        <div class="admin-svg-background">
                        <!-- SVG viewBox will be set dynamically based on floor dimensions -->
                            <svg id="adminSvg" class="admin-svg" style="display: block;">
                            <defs>
                                <pattern id="gridPattern" width="50" height="50" patternUnits="userSpaceOnUse">
                                    <path d="M 50 0 L 0 0 0 50" fill="none" stroke="#e0e0e0" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <!-- Background image will be inserted here dynamically -->
                            <!-- Semi-transparent white overlay to show grid over background -->
                            <rect id="gridBgOverlay" width="100%" height="100%" fill="rgba(250, 250, 250, 0.3)"/>
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
                        <label class="form-label">No of Rows</label>
                        <input type="number" id="gridRows" name="gridRows" class="form-control" value="3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No of Columns</label>
                        <input type="number" id="gridCols" name="gridCols" class="form-control" value="3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Booth Rows (grid units)</label>
                        <input type="number" id="gridBoothWidth" name="gridBoothWidth" class="form-control" value="2">
                        <small class="text-muted">Width px: <span id="gridBoothWidthPx">100</span></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Booth Columns (grid units)</label>
                        <input type="number" id="gridBoothHeight" name="gridBoothHeight" class="form-control" value="2">
                        <small class="text-muted">Height px: <span id="gridBoothHeightPx">100</span></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Spacing Between Booths (grid units)</label>
                        <input type="number" id="gridSpacing" name="gridSpacing" class="form-control" value="0">
                        <small class="text-muted">Spacing px: <span id="gridSpacingPx">0</span></small>
                    </div>
                    <div class="col-md-6" style="display: none;">
                        <label class="form-label">Start X (grid units)</label>
                        <input type="number" id="gridStartX" name="gridStartX" class="form-control" value="2">
                        <small class="text-muted">Start X px: <span id="gridStartXPx">100</span></small>
                    </div>
                    <div class="col-md-6" style="display: none;">
                        <label class="form-label">Start Y (grid units)</label>
                        <input type="number" id="gridStartY" name="gridStartY" class="form-control" value="3">
                        <small class="text-muted">Start Y px: <span id="gridStartYPx">150</span></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Booth ID Prefix</label>
                        <input type="text" id="gridPrefix" name="gridPrefix" class="form-control" value="B">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Booth Size Category</label>
                        <select id="gridBoothSizeCategory" name="gridBoothSizeCategory" class="form-select">
                            <option value="">Select Category (Optional)</option>
                            @foreach(($exhibition->boothSizes ?? collect()) as $size)
                                <option value="{{ $size->id }}" 
                                        data-category="{{ $size->category }}"
                                        data-size-sqft="{{ $size->size_sqft }}">
                                    {{ $size->size_sqft }} sq meter - 
                                    @if($size->category == '1' || $size->category == 'Premium')
                                        Premium
                                    @elseif($size->category == '2' || $size->category == 'Standard')
                                        Standard
                                    @elseif($size->category == '3' || $size->category == 'Economy')
                                        Economy
                                    @else
                                        {{ $size->category }}
                                    @endif
                                    @if($size->sizeType)
                                        ({{ $size->sizeType->length }}x{{ $size->sizeType->width }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select a category to apply to all generated booths</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end">
                <button type="button" id="cancelGenerateBtn" class="btn btn-secondary me-2">Cancel</button>
                <button type="button" id="generateGridBtn" class="btn btn-primary">Generate</button>
            </div>
        </div>
    </div>
    
    <!-- Hall Capacity Summary -->
    <div class="card mb-4" id="hallCapacitySummaryCard">
        <div class="card-header">
            <h6 class="mb-0">Hall Capacity Summary</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <span class="text-muted">Total Hall Area:</span> <strong id="summaryTotalHallArea">0</strong> sq meter
            </div>
            <div class="mb-3">
                <span class="text-muted">Passage/Common Area (30%):</span> <strong id="summaryPassageArea">0</strong> sq meter
            </div>
            <div class="mb-3">
                <span class="text-muted">Usable Stall Area (70%):</span> <strong id="summaryUsableArea">0</strong> sq meter
            </div>
            <div class="mb-3">
                <label class="form-label mb-1">Usable vs Passage area</label>
                <div class="progress" style="height: 1.5rem;">
                    <div class="progress-bar bg-success" id="summaryProgressUsable" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">70% Usable</div>
                    <div class="progress-bar bg-secondary" id="summaryProgressPassage" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">30% Passage</div>
                </div>
            </div>
            <div class="mb-3">
                <span class="text-muted">Maximum Possible Stalls:</span> <strong id="summaryMaxStalls">—</strong>
            </div>
            <div class="mb-3">
                <span class="text-muted">Total Created Stall Area:</span> <strong id="summaryTotalStallArea">0</strong> sq meter
            </div>
            <div class="mb-3">
                <span class="text-muted">Booked Stall Area:</span> <strong id="summaryBookedStallArea">0</strong> sq meter
            </div>
            <div class="mb-0">
                <label class="form-label mb-1">Occupancy: <span id="summaryOccupancyPct">0</span>%</label>
                <div class="progress" style="height: 1.5rem;">
                    <div class="progress-bar bg-primary" id="summaryProgressOccupancy" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
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

    <!-- Discount Configuration -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Discount Configuration</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Payment Discount Percentage (%):</label>
                    <input type="number" name="full_payment_discount_percent" class="form-control" step="0.01" min="0" max="100" 
                           value="{{ $exhibition->full_payment_discount_percent ?? '' }}" placeholder="e.g., 10">
                    <small class="text-muted d-block mt-1">Enter discount percentage for full payment (e.g., 10 for 10% discount)</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Member Discount Percentage (%):</label>
                    <input type="number" name="member_discount_percent" class="form-control" step="0.01" min="0" max="100" 
                           value="{{ $exhibition->member_discount_percent ?? '' }}" placeholder="e.g., 5">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Maximum Discount Apply Percentage (%):</label>
                    <input type="number" name="maximum_discount_apply_percent" class="form-control" step="0.01" min="0" max="100" 
                           value="{{ $exhibition->maximum_discount_apply_percent ?? '' }}" placeholder="e.g., 25">
                </div>
            </div>
        </div>
    </div>

    <!-- Registration / Visitor Fees -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-currency-rupee me-2"></i>Registration & Visitor Fees</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-4">Set fee amounts (in ₹) and cut-off dates for visitor tiers, delegate, member, and VIP registration. Leave empty or 0 if not applicable.</p>

            <!-- Visitor Fee (3 tiers) -->
            <h6 class="text-dark border-bottom pb-2 mb-3">Visitor Fee</h6>
            <div class="row mb-4">
                <div class="col-12 col-lg-4 mb-3">
                    <label class="form-label">Early Bird</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" name="visitor_early_bird_end_date" class="form-control"
                                   value="{{ $exhibition->visitor_early_bird_end_date ? $exhibition->visitor_early_bird_end_date->format('Y-m-d') : '' }}" placeholder="End date">
                            <small class="text-muted">End date</small>
                        </div>
                        <div class="col-6">
                            <input type="number" name="visitor_early_bird_fee" class="form-control" step="0.01" min="0" placeholder="0.00"
                                   value="{{ $exhibition->visitor_early_bird_fee !== null ? $exhibition->visitor_early_bird_fee : '' }}">
                            <small class="text-muted">Fee (₹)</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 mb-3">
                    <label class="form-label">Standard Fee</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" name="visitor_standard_end_date" class="form-control"
                                   value="{{ $exhibition->visitor_standard_end_date ? $exhibition->visitor_standard_end_date->format('Y-m-d') : '' }}" placeholder="End date">
                            <small class="text-muted">End date</small>
                        </div>
                        <div class="col-6">
                            <input type="number" name="visitor_standard_fee" class="form-control" step="0.01" min="0" placeholder="0.00"
                                   value="{{ $exhibition->visitor_standard_fee !== null ? $exhibition->visitor_standard_fee : '' }}">
                            <small class="text-muted">Fee (₹)</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 mb-3">
                    <label class="form-label">Last Minute Registration</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" name="visitor_last_minute_end_date" class="form-control"
                                   value="{{ $exhibition->visitor_last_minute_end_date ? $exhibition->visitor_last_minute_end_date->format('Y-m-d') : '' }}" placeholder="End date">
                            <small class="text-muted">End date</small>
                        </div>
                        <div class="col-6">
                            <input type="number" name="visitor_last_minute_fee" class="form-control" step="0.01" min="0" placeholder="0.00"
                                   value="{{ $exhibition->visitor_last_minute_fee !== null ? $exhibition->visitor_last_minute_fee : '' }}">
                            <small class="text-muted">Fee (₹)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Fee -->
            <h6 class="text-dark border-bottom pb-2 mb-3">Member Fee</h6>
            <div class="row mb-4">
                <div class="col-md-6 col-lg-4 mb-3">
                    <label class="form-label">Member Registration Fee (₹)</label>
                    <input type="number" name="member_fee" class="form-control" step="0.01" min="0" placeholder="0.00"
                           value="{{ $exhibition->member_fee !== null ? $exhibition->member_fee : '' }}">
                    <small class="text-muted d-block mt-1">Registration fee for members</small>
                </div>
            </div>

            <!-- Delegate Fee: free count + additional fee -->
            <h6 class="text-dark border-bottom pb-2 mb-3">Delegate Fee</h6>
            <p class="text-muted small mb-2">Up to a certain number of delegates are free; additional delegates are charged per person.</p>
            <div class="row mb-4">
                <div class="col-md-6 col-lg-4 mb-3">
                    <label class="form-label">Free delegates (count)</label>
                    <input type="number" name="delegate_free_count" class="form-control" min="0" step="1" placeholder="e.g. 2"
                           value="{{ $exhibition->delegate_free_count !== null ? $exhibition->delegate_free_count : '2' }}">
                    <small class="text-muted d-block mt-1">Number of delegates included free (e.g. 2)</small>
                </div>
                <div class="col-md-6 col-lg-4 mb-3">
                    <label class="form-label">Additional delegate fee (₹ per person)</label>
                    <input type="number" name="delegate_additional_fee" class="form-control" step="0.01" min="0" placeholder="0.00"
                           value="{{ $exhibition->delegate_additional_fee !== null ? $exhibition->delegate_additional_fee : '' }}">
                    <small class="text-muted d-block mt-1">Fee for each delegate beyond the free count</small>
                </div>
            </div>

            <!-- VIP Registration Fee -->
            <h6 class="text-dark border-bottom pb-2 mb-3">VIP Registration Fee</h6>
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-3">
                    <label class="form-label">VIP Registration Fee (₹)</label>
                    <input type="number" name="vip_registration_fee" class="form-control" step="0.01" min="0" placeholder="0.00"
                           value="{{ $exhibition->vip_registration_fee !== null ? $exhibition->vip_registration_fee : '' }}">
                    <small class="text-muted d-block mt-1">Registration fee for VIP attendees</small>
                </div>
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
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Document upload deadlines:</label>
                    <div class="input-group">
                        <input type="date" name="document_upload_deadline" class="form-control" 
                               value="{{ $exhibition->document_upload_deadline ? $exhibition->document_upload_deadline->format('Y-m-d') : '' }}">
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
// Pass booth sizes to JavaScript
const boothSizesData = @json($exhibition->boothSizes ?? []);

function updateGridAreaSummary() {
    var editor = window.floorplanEditor;
    if (!editor || !editor.hallConfig || !editor.booths) return;

    var gridSize = editor.gridConfig && editor.gridConfig.size ? editor.gridConfig.size : 50;
    var pxToSqM = 1 / (gridSize * gridSize);

    var totalHallAreaPx = editor.hallConfig.width * editor.hallConfig.height;
    var totalHallArea = totalHallAreaPx * pxToSqM;
    var passageArea = totalHallArea * 0.30;
    var usableArea = totalHallArea * 0.70;
    var usableAreaPx = totalHallAreaPx * 0.70;

    var totalStallAreaPx = 0;
    var bookedStallAreaPx = 0;
    var minStallAreaPx = null;
    editor.booths.forEach(function(b) {
        var areaPx = (b.width || 0) * (b.height || 0);
        totalStallAreaPx += areaPx;
        if ((b.status || '').toLowerCase() === 'booked') {
            bookedStallAreaPx += areaPx;
        }
        if (areaPx > 0) {
            minStallAreaPx = minStallAreaPx === null ? areaPx : Math.min(minStallAreaPx, areaPx);
        }
    });

    var totalStallArea = totalStallAreaPx * pxToSqM;
    var bookedStallArea = bookedStallAreaPx * pxToSqM;

    var maxPossibleStalls = 0;
    if (minStallAreaPx && minStallAreaPx > 0 && usableAreaPx > 0) {
        maxPossibleStalls = Math.floor(usableAreaPx / minStallAreaPx);
    }

    var occupancyPct = totalStallAreaPx > 0 ? (bookedStallAreaPx / totalStallAreaPx) * 100 : 0;
    occupancyPct = Math.min(100, Math.round(occupancyPct * 10) / 10);

    function setEl(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text;
    }
    function setProgress(id, pct, aria) {
        var el = document.getElementById(id);
        if (el) {
            el.style.width = (pct || 0) + '%';
            if (aria !== undefined) el.setAttribute('aria-valuenow', aria);
        }
    }

    setEl('summaryTotalHallArea', totalHallArea.toFixed(2));
    setEl('summaryPassageArea', passageArea.toFixed(2));
    setEl('summaryUsableArea', usableArea.toFixed(2));
    setEl('summaryMaxStalls', minStallAreaPx ? maxPossibleStalls : '—');
    setEl('summaryTotalStallArea', totalStallArea.toFixed(2));
    setEl('summaryBookedStallArea', bookedStallArea.toFixed(2));
    setEl('summaryOccupancyPct', occupancyPct.toFixed(1));

    setProgress('summaryProgressUsable', 70, 70);
    setProgress('summaryProgressPassage', 30, 30);
    setProgress('summaryProgressOccupancy', occupancyPct, occupancyPct);
}
window.updateGridAreaSummary = updateGridAreaSummary;

document.addEventListener('DOMContentLoaded', function () {
    const exhibitionId = {{ $exhibition->id }};
    const floorSelector = document.getElementById('floorSelector');
    const currentFloorIdInput = document.getElementById('currentFloorId');
    const currentFloorNameSpan = document.getElementById('currentFloorName');
    const selectedFloorNameBadge = document.getElementById('selectedFloorName');
    const existingImagesContainer = document.getElementById('existingFloorplanImages');
    const floorplanCard = document.getElementById('floorplanCard');
    
    // Floor data cache
    const floorsData = @json($exhibition->floors ?? []);
    
    // Initialize floor selector
    if (floorSelector && floorsData.length > 0) {
        const initialFloorId = floorSelector.value;
        
        // Wait for floorplan editor to be initialized before loading floor data
        if (window.floorplanEditor) {
            loadFloorData(initialFloorId);
        } else {
            // Wait a bit for the editor to initialize
            setTimeout(() => {
                loadFloorData(initialFloorId);
            }, 100);
        }
        
        floorSelector.addEventListener('change', function() {
            const selectedFloorId = this.value;
            loadFloorData(selectedFloorId);
        });
    }
    
    function loadFloorData(floorId) {
        const floor = floorsData.find(f => f.id == floorId);
        if (!floor) return;
        
        // Update UI
        const currentFloorIdEl = document.getElementById('currentFloorId');
        if (currentFloorIdEl) currentFloorIdEl.value = floorId;
        if (currentFloorIdInput) currentFloorIdInput.value = floorId;
        if (currentFloorNameSpan) currentFloorNameSpan.textContent = floor.name;
        if (selectedFloorNameBadge) selectedFloorNameBadge.textContent = floor.name;
        
        // Update floorplan editor's currentFloorId
        if (window.floorplanEditor) {
            window.floorplanEditor.currentFloorId = floorId;
            
            // Update hall dimensions from floor meters (1 meter = 50px)
            // If floor dimensions are provided, calculate dynamic dimensions
            if (floor.width_meters && floor.height_meters) {
                if (typeof window.floorplanEditor.updateHallDimensionsFromFloor === 'function') {
                    window.floorplanEditor.updateHallDimensionsFromFloor(
                        parseFloat(floor.width_meters),
                        parseFloat(floor.height_meters)
                    );
                }
            }
        }
        
        // Load background image for this floor (after dimensions are set)
        if (window.floorplanEditor && typeof window.floorplanEditor.loadBackgroundImage === 'function') {
            // Small delay to ensure dimensions are set first
            setTimeout(() => {
                window.floorplanEditor.loadBackgroundImage(floor.background_image || null);
            }, 100);
        }
        
        // Load existing images for this floor
        loadFloorImages(floorId, floor);
        
        // Update floorplan editor to load this floor's config (after dimensions are set)
        loadFloorplanConfig(floorId);
    }
    
    function loadFloorImages(floorId, floor) {
        if (!existingImagesContainer) return;
        
        const floorplanImages = floor.floorplan_images ?? [];
        const floorplanImage = floor.floorplan_image;
        
        let allImages = [];
        if (Array.isArray(floorplanImages) && floorplanImages.length > 0) {
            allImages = floorplanImages;
        } else if (floorplanImage) {
            allImages = [floorplanImage];
        }
        
        if (allImages.length > 0) {
            let html = '<div class="mt-2"><span class="text-muted d-block mb-1">Existing images:</span><div class="d-flex flex-wrap gap-3">';
            allImages.forEach((imgPath, index) => {
                html += `
                    <div class="border rounded p-2 text-center" style="width: 120px;">
                        <img src="{{ asset('storage/') }}/${imgPath.replace(/^\/+/, '')}" alt="Hall Plan" 
                             style="width: 100%; height: 80px; object-fit: cover; border-radius: 4px;">
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" 
                                   name="remove_floorplan_images[${floorId}][]" 
                                   value="${imgPath}" 
                                   id="remove_floorplan_${floorId}_${index}">
                            <label class="form-check-label small" for="remove_floorplan_${floorId}_${index}">
                                Remove
                            </label>
                        </div>
                        <input type="hidden" name="existing_floorplan_images[${floorId}][]" value="${imgPath}">
                    </div>
                `;
            });
            html += '</div></div>';
            existingImagesContainer.innerHTML = html;
        } else {
            existingImagesContainer.innerHTML = '<p class="text-muted small mt-2">No existing images for this floor.</p>';
        }
    }
    
    function loadFloorplanConfig(floorId) {
        // Load floorplan config via AJAX
        if (window.floorplanEditor && typeof window.floorplanEditor.loadConfiguration === 'function') {
            // Update currentFloorId
            window.floorplanEditor.currentFloorId = floorId;
            // Load the config without confirmation dialog
            window.floorplanEditor.loadConfiguration(floorId, false);
        } else {
            // Fallback: fetch and manually set config
            fetch(`/admin/exhibitions/${exhibitionId}/floorplan/config/${floorId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (window.floorplanEditor) {
                    window.floorplanEditor.currentFloorId = floorId;
                    // Manually set the config if there's a method for it
                    if (data.hall && window.floorplanEditor.hallConfig) {
                        window.floorplanEditor.hallConfig = data.hall;
                    }
                    if (data.grid && window.floorplanEditor.gridConfig) {
                        window.floorplanEditor.gridConfig = data.grid;
                    }
                    if (data.booths && Array.isArray(data.booths)) {
                        window.floorplanEditor.booths = data.booths;
                    }
                    // Trigger redraw
                    if (window.floorplanEditor.updateGrid) window.floorplanEditor.updateGrid();
                    if (window.floorplanEditor.drawHall) window.floorplanEditor.drawHall();
                    if (window.floorplanEditor.renderBooths) window.floorplanEditor.renderBooths();
                }
            })
            .catch(error => {
                console.error('Error loading floorplan config:', error);
            });
        }
    }
    
    // Image preview handler
    const input = document.getElementById('floorplanImagesInput');
    const previewContainer = document.getElementById('floorplanNewPreview');

    if (input && previewContainer) {
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
    }
});
</script>
@endpush
@endsection
