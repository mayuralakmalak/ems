@extends('layouts.admin')

@section('title', 'Admin - Exhibition booking step 2')
@section('page-title', 'Admin - Exhibition booking step 2')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-floorplan-step2.css') }}">
@endpush

@section('content')
<input type="hidden" id="exhibitionId" value="{{ $exhibition->id }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Admin - Exhibition booking step 2</h4>
            <span class="text-muted">23 / 36</span>
        </div>
        <div class="text-center mb-4">
            <h5>Step 2 - Floor Plan Management</h5>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <form id="step2Form" action="{{ route('admin.exhibitions.step2.store', $exhibition->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Floorplan Management Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Floor Plan Management</h5>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="p-3">
                        <label class="form-label mb-2">Upload Floorplan Background Image (Optional)</label>
                        <input type="file" name="floorplan_image" class="form-control" accept="image/*">
                        @if($exhibition->floorplan_image)
                            <small class="text-muted d-block mt-1">Current: {{ basename($exhibition->floorplan_image) }}</small>
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
                                    <label>Price ($):</label>
                                    <input type="number" id="boothPrice" value="10000" min="0" step="100">
                                </div>
                                <div class="form-group">
                                    <label>Open Sides:</label>
                                    <select id="boothOpenSides">
                                        <option value="1">1 Side</option>
                                        <option value="2">2 Sides</option>
                                        <option value="3">3 Sides</option>
                                        <option value="4">4 Sides</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Category:</label>
                                    <select id="boothCategory">
                                        <option value="Standard">Standard</option>
                                        <option value="Premium">Premium</option>
                                        <option value="VIP">VIP</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Included Items (comma-separated):</label>
                                    <input type="text" id="boothItems" placeholder="Table, 2 Chairs, Power">
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

            <!-- Booth Configuration Section -->
            <div class="row mb-4">
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Booth Configuration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Select Booth category</label>
                                    <select name="booth_category" class="form-select">
                                        <option value="">Select Category</option>
                                        <option value="Premium">Premium</option>
                                        <option value="Standard">Standard</option>
                                        <option value="Economy">Economy</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Size (sq ft)</label>
                                    <input type="number" name="booth_size" class="form-control" step="0.01" placeholder="Size in sq ft">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Category Label</label>
                                    <input type="text" name="booth_category_name" class="form-control" placeholder="Category label (e.g., Hall A)">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Side open pricing % adjustment</label>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">Rear</span>
                                            <input type="number" name="rear_price_per_sqft" class="form-control" step="0.01" placeholder="Price/sq ft" value="{{ $exhibition->raw_price_per_sqft ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">Orphaned</span>
                                            <input type="number" name="orphaned_price_per_sqft" class="form-control" step="0.01" placeholder="Price/sq ft" value="{{ $exhibition->orphand_price_per_sqft ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="free_booth" id="free_booth" value="1">
                                <label class="form-check-label" for="free_booth">Free Booth</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Configuration -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Pricing Configuration</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Base price per sq ft</label>
                                <input type="number" name="price_per_sqft" class="form-control" step="0.01" placeholder="eg. 100" value="{{ $exhibition->price_per_sqft ?? '' }}">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Side Open Variations (% adjustment)</label>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">1 Side Open</span>
                                            <input type="number" name="side_1_open_percent" class="form-control" step="0.01" placeholder="%" value="{{ $exhibition->side_1_open_percent ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">2 Sides Open</span>
                                            <input type="number" name="side_2_open_percent" class="form-control" step="0.01" placeholder="%" value="{{ $exhibition->side_2_open_percent ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">3 Sides Open</span>
                                            <input type="number" name="side_3_open_percent" class="form-control" step="0.01" placeholder="%" value="{{ $exhibition->side_3_open_percent ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">4 Sides Open</span>
                                            <input type="number" name="side_4_open_percent" class="form-control" step="0.01" placeholder="%" value="{{ $exhibition->side_4_open_percent ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category Pricing</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Premium</td>
                                                <td>
                                                    <input type="number" name="premium_price" class="form-control" step="0.01" placeholder="Price" value="{{ $exhibition->premium_price ?? '' }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Standard</td>
                                                <td>
                                                    <input type="number" name="standard_price" class="form-control" step="0.01" placeholder="Price" value="{{ $exhibition->standard_price ?? '' }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Economy</td>
                                                <td>
                                                    <input type="number" name="economy_price" class="form-control" step="0.01" placeholder="Price" value="{{ $exhibition->economy_price ?? '' }}">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Save and Continue to Step 3</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Generate Grid -->
<div id="generateModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Generate Booth Grid</h2>
            <button class="modal-close" id="closeGenerateModal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Rows:</label>
                <input type="number" id="gridRows" value="6" min="1" max="20">
            </div>
            <div class="form-group">
                <label>Columns:</label>
                <input type="number" id="gridCols" value="8" min="1" max="20">
            </div>
            <div class="form-group">
                <label>Booth Width (grid units):</label>
                <input type="number" id="gridBoothWidth" value="2" min="1" max="20">
                <small>Current: <span id="gridBoothWidthPx">100</span> px</small>
            </div>
            <div class="form-group">
                <label>Booth Height (grid units):</label>
                <input type="number" id="gridBoothHeight" value="2" min="1" max="20">
                <small>Current: <span id="gridBoothHeightPx">100</span> px</small>
            </div>
            <div class="form-group">
                <label>Spacing (grid units):</label>
                <input type="number" id="gridSpacing" value="0" min="0" max="10">
                <small>Current: <span id="gridSpacingPx">0</span> px</small>
            </div>
            <div class="form-group">
                <label>Start X (grid units):</label>
                <input type="number" id="gridStartX" value="2" min="0">
                <small>Current: <span id="gridStartXPx">100</span> px</small>
            </div>
            <div class="form-group">
                <label>Start Y (grid units):</label>
                <input type="number" id="gridStartY" value="3" min="0">
                <small>Current: <span id="gridStartYPx">150</span> px</small>
            </div>
            <div class="form-group">
                <label>Booth ID Prefix:</label>
                <input type="text" id="gridPrefix" value="B" placeholder="B">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" id="generateGridBtn" class="btn-primary">Generate</button>
            <button type="button" id="cancelGenerateBtn" class="btn-secondary">Cancel</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-floorplan-step2.js') }}"></script>
@endpush
