@extends('layouts.frontend')

@section('title', 'Book Booth - ' . $exhibition->name)

@push('styles')
<style>
    .booking-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 150px);
        margin-top: 20px;
    }
    
    /* Left Panel - Filters */
    .left-panel {
        width: 300px;
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow-y: auto;
    }
    
    .filter-section {
        margin-bottom: 25px;
    }
    
    .filter-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .filter-group {
        margin-bottom: 15px;
    }
    
    .filter-group label {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 8px;
        display: block;
    }
    
    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    
    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .checkbox-item label {
        font-size: 0.9rem;
        color: #334155;
        cursor: pointer;
        margin: 0;
    }
    
    .price-range {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .price-range input {
        flex: 1;
    }
    
    /* Center Panel - Floorplan */
    .center-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .floorplan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .floorplan-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .floorplan-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .zoom-controls {
        display: flex;
        gap: 5px;
        align-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 5px;
    }
    
    .zoom-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .zoom-btn:hover {
        background: #f8fafc;
    }
    
    .reset-btn {
        padding: 8px 16px;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .reset-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    
    .floorplan-canvas {
        flex: 1;
        position: relative;
        background: #f8fafc;
        border-radius: 8px;
        overflow: auto;
        border: 1px solid #e2e8f0;
        min-height: 320px;
    }
    
    .floorplan-image {
        position: absolute;
        top: 0;
        left: 0;
        max-width: 100%;
        height: auto;
        z-index: 1;
        opacity: 0.3;
    }
    
    .booth-item {
        position: absolute;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 12px;
        user-select: none;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        border: 2px solid;
    }
    
    .booth-item:hover {
        transform: scale(1.05);
        z-index: 20;
    }
    
    .booth-available {
        background-color: #28a745;
        border-color: #1e7e34;
    }
    
    .booth-booked {
        background-color: #dc3545;
        border-color: #b02a37;
        cursor: not-allowed;
    }
    
    .booth-reserved {
        background-color: #ffc107;
        border-color: #d39e00;
    }
    
    .booth-merged {
        background-color: #17a2b8 !important;
        border-color: #138496 !important;
        border-width: 3px !important;
    }
    
    .booth-selected {
        border: 3px solid #007bff !important;
        box-shadow: 0 0 15px rgba(0,123,255,0.6);
        z-index: 30;
    }
    
    .legend {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 2px solid;
    }
    
    .legend-color.available {
        background: #28a745;
        border-color: #1e7e34;
    }
    
    .legend-color.reserved {
        background: #ffc107;
        border-color: #d39e00;
    }
    
    .legend-color.booked {
        background: #dc3545;
        border-color: #b02a37;
    }
    .legend-color.merged {
        background: #17a2b8;
        border-color: #138496;
    }
    
    .legend-color.selected {
        background: #007bff;
        border-color: #0056b3;
    }
    
    /* Right Panel - Booth Details & Selected */
    .right-panel {
        width: 350px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        overflow-y: auto;
    }
    
    .panel-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

.selection-group {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
}

.selection-group h6 {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 10px;
}

.radio-inline,
.checkbox-inline {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.radio-inline label,
.checkbox-inline label {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    cursor: pointer;
    background: #f8fafc;
    font-size: 0.9rem;
    color: #334155;
}

.radio-inline input,
.checkbox-inline input {
    width: 16px;
    height: 16px;
}
    
    .panel-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .booth-details {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .detail-row:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        font-size: 0.9rem;
        color: #64748b;
    }
    
    .detail-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .detail-value.price {
        color: #6366f1;
        font-size: 1.1rem;
    }
    
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-action {
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .btn-select {
        background: #6366f1;
        color: white;
    }
    
    .btn-select:hover:not(:disabled) {
        background: #4f46e5;
    }
    
    .btn-merge {
        background: #8b5cf6;
        color: white;
    }
    
    .btn-merge:hover:not(:disabled) {
        background: #7c3aed;
    }
    
    .btn-split {
        background: #f59e0b;
        color: white;
    }
    
    .btn-split:hover:not(:disabled) {
        background: #d97706;
    }
    
    .selected-booths {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .selected-booth-item {
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .selected-booth-info {
        flex: 1;
    }
    
    .selected-booth-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
        margin-bottom: 4px;
    }
    
    .selected-booth-price {
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .remove-booth {
        width: 28px;
        height: 28px;
        border: none;
        background: #fee2e2;
        color: #991b1b;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .remove-booth:hover {
        background: #fecaca;
    }
    
    .selected-total {
        padding: 15px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        margin-top: 10px;
    }
    
    .total-label {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .total-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #6366f1;
    }
    
    .proceed-btn {
        width: 100%;
        padding: 15px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 15px;
    }
    
    .proceed-btn:hover:not(:disabled) {
        background: #4f46e5;
        transform: translateY(-2px);
    }
    
    .proceed-btn:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
        transform: none;
    }

    /* Services */
    .services-card {
        margin-top: 0;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .services-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .service-item {
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: white;
    }

    .service-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .service-price {
        font-size: 0.85rem;
        color: #475569;
    }

    .service-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #64748b;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
</style>
@endpush

@section('content')
<div class="booking-container">
    <!-- Left Panel - Filters -->
    <div class="left-panel">
        <div class="filter-section">
            <h5 class="filter-title">Filters</h5>
            
            <div class="filter-group">
                <label>Booth Size</label>
                <select id="filterSize" class="form-select">
                    <option value="all">All Sizes</option>
                    <option value="small">Small (< 200 sq ft)</option>
                    <option value="medium">Medium (200-500 sq ft)</option>
                    <option value="large">Large (> 500 sq ft)</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Price Range</label>
                <div class="price-range">
                    <input type="range" id="priceRange" min="0" max="100000" value="50000" step="1000">
                    <span id="priceRangeValue">Up to ₹50,000</span>
                </div>
            </div>
            
            <div class="filter-group">
                <label>Status</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="statusAvailable" checked>
                        <label for="statusAvailable">Available</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="statusReserved">
                        <label for="statusReserved">Reserved</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="statusBooked">
                        <label for="statusBooked">Booked</label>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- Center Panel - Floorplan -->
    <div class="center-panel">
        <div class="floorplan-header">
            <h4 class="floorplan-title">Exhibition Hall Floorplan</h4>
            <div class="floorplan-controls">
                <div class="zoom-controls">
                    <button class="zoom-btn" id="zoomIn">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button class="zoom-btn" id="zoomOut">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
                <button class="reset-btn" id="resetView">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </button>
            </div>
        </div>
        
        <div class="floorplan-canvas" id="floorplanCanvas">
            @if($exhibition->floorplan_image)
            <img src="{{ asset('storage/' . $exhibition->floorplan_image) }}" 
                 id="floorplanImage" 
                 class="floorplan-image" 
                 alt="Floorplan">
            @endif
            <div id="boothsContainer" style="position: relative; min-height: 100%; z-index: 2;">
                @foreach($exhibition->booths as $booth)
                @php
                    // Hide split parent booths (they are replaced by their children)
                    if ($booth->is_split && $booth->parent_booth_id === null) continue;
                    // Hide merged originals (parent_booth_id not null AND not split)
                    if ($booth->parent_booth_id !== null && !$booth->is_split) continue;
                    
                    // Determine booth status
                    // Priority: booked > available > reserved
                    $status = 'available';
                    $statusClass = 'booth-available';
                    
                    if ($booth->is_booked) {
                        // Booked booths (after admin approval)
                        $status = 'booked';
                        $statusClass = 'booth-booked';
                    } elseif ($booth->is_available) {
                        // Available booths (including merged booths that are available)
                        $status = 'available';
                        $statusClass = 'booth-available';
                        if ($booth->is_merged) {
                            $statusClass .= ' booth-merged';
                        }
                    } else {
                        // Not available and not booked = reserved
                        $status = 'reserved';
                        $statusClass = 'booth-reserved';
                    }

                    $mergedNames = '';
                    if ($booth->is_merged && $booth->merged_booths) {
                        $originalBooths = \App\Models\Booth::whereIn('id', $booth->merged_booths)->pluck('name')->toArray();
                        $mergedNames = implode(', ', $originalBooths);
                    }
                @endphp
                <div class="booth-item {{ $statusClass }}" 
                     data-booth-id="{{ $booth->id }}"
                     data-booth-name="{{ $booth->name }}"
                     data-booth-size="{{ $booth->size_sqft }}"
                     data-booth-price="{{ $booth->price }}"
                     data-booth-category="{{ $booth->category }}"
                     data-booth-type="{{ $booth->booth_type }}"
                     data-booth-sides="{{ $booth->sides_open }}"
                     data-booth-status="{{ $status }}"
                     data-booth-merged="{{ $booth->is_merged ? 'true' : 'false' }}"
                     data-merged-originals="{{ $mergedNames }}"
                     style="left: {{ $booth->position_x ?? ($loop->index % 5) * 120 }}px; 
                            top: {{ $booth->position_y ?? floor($loop->index / 5) * 100 }}px; 
                            width: {{ $booth->width ?? 100 }}px; 
                            height: {{ $booth->height ?? 80 }}px;">
                    <div class="text-center">
                        <div>{{ $booth->name }}{{ $booth->is_merged ? ' (Merged)' : '' }}</div>
                        <div style="font-size: 10px;">{{ $booth->size_sqft }} sq ft</div>
                        @if($mergedNames)
                        <div style="font-size: 9px; color: #0f172a;">Original: {{ $mergedNames }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <div class="legend">
        <div class="legend-item">
            <div class="legend-color available"></div>
            <span>Available</span>
        </div>
            <div class="legend-item">
                <div class="legend-color reserved"></div>
                <span>Reserved</span>
            </div>
            <div class="legend-item">
                <div class="legend-color booked"></div>
                <span>Booked</span>
            </div>
        <div class="legend-item">
            <div class="legend-color selected"></div>
            <span>Selected</span>
        </div>
        <div class="legend-item">
            <div class="legend-color merged"></div>
            <span>Merged</span>
        </div>
        </div>
    </div>
    
    <!-- Right Panel - Booth Details & Selected Booths -->
    <div class="right-panel">
        <!-- Booth Details -->
        <div class="panel-card" id="boothDetailsPanel" style="display: none;">
            <h5 class="panel-title">Booth Details</h5>
            <div class="booth-details" id="boothDetails">
                <!-- Will be populated by JavaScript -->
            </div>
            <div class="selection-group" id="boothSelectionControls" style="display: none;">
                <h6>Choose Booth Type</h6>
                <div class="radio-inline" id="boothTypeOptions">
                    <!-- Radios injected via JS -->
                </div>

                <h6 style="margin-top: 12px;">Choose Open Sides</h6>
                <div class="checkbox-inline" id="boothSideOptions">
                    <!-- Checkboxes injected via JS -->
                </div>

                <div class="detail-row" style="margin-top: 12px;">
                    <span class="detail-label">Calculated Price</span>
                    <span class="detail-value price" id="boothCalculatedPrice">₹0</span>
                </div>
            </div>
            <div class="action-buttons">
                <button class="btn-action btn-select" id="selectBoothBtn">
                    <i class="bi bi-check-circle me-1"></i>Select Booth
                </button>
                <button class="btn-action btn-merge" id="mergeBoothBtn" disabled>
                    <i class="bi bi-arrow-left-right me-1"></i>Request Merge
                </button>
                <button class="btn-action btn-split" id="splitBoothBtn" disabled>
                    <i class="bi bi-scissors me-1"></i>Request Split
                </button>
            </div>
        </div>

        <!-- Selected Booths -->
        <div class="panel-card">
            <h5 class="panel-title">Selected Booths</h5>
            <div id="selectedBoothsList" class="selected-booths">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>No booths selected</p>
                </div>
            </div>
            <div id="selectedTotal" class="selected-total" style="display: none;">
                <div class="total-label">Total Amount</div>
                <div class="total-value" id="totalAmount">₹0</div>
            </div>
            <button class="proceed-btn" id="proceedToBookBtn" disabled>
                <i class="bi bi-cart-check me-2"></i>Proceed to Booking Form
            </button>
        </div>

        <!-- Additional Services -->
        @php
            $activeServices = $exhibition->services ? $exhibition->services->where('is_active', true) : collect();
        @endphp
        @if($activeServices->count() > 0)
        <div class="panel-card services-card" id="servicesCard">
            <h5 class="panel-title">Additional Services</h5>
            <div class="services-list">
                @foreach($activeServices->groupBy('category') as $category => $categoryServices)
                    @if($category)
                    <div style="margin-bottom: 15px;">
                        <h6 style="font-size: 0.9rem; color: #64748b; margin-bottom: 10px; font-weight: 600;">{{ $category }}</h6>
                    </div>
                    @endif
                    @foreach($categoryServices as $service)
                    <div class="service-item">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 10px;">
                            <div style="flex: 1;">
                                <div class="service-name" style="margin-bottom: 5px;">{{ $service->name }}</div>
                                @if($service->description)
                                <div style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 5px;">{{ $service->description }}</div>
                                @endif
                                <div class="service-price" style="margin-bottom: 5px;">₹{{ number_format($service->price, 2) }}</div>
                                @if($service->image)
                                <button type="button" class="btn-view-image" onclick="openImageGallery({{ $service->id }}, '{{ asset('storage/' . $service->image) }}', '{{ $service->name }}')" style="padding: 4px 12px; background: #6366f1; color: white; border: none; border-radius: 4px; font-size: 0.75rem; cursor: pointer; margin-top: 5px;">
                                    <i class="bi bi-image me-1"></i>View Image
                                </button>
                                @endif
                            </div>
                            <div>
                                <input type="checkbox" class="service-checkbox" data-service-id="{{ $service->id }}" data-service-price="{{ $service->price }}" data-service-name="{{ $service->name }}" onchange="updateServiceSelection()">
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>
            <div id="servicesTotal" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.9rem; color: #64748b;">Services Total:</span>
                    <span style="font-weight: 600; color: #6366f1;" id="servicesTotalAmount">₹0</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Image Gallery Modal -->
<div class="modal fade" id="imageGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="galleryModalTitle">Service Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="galleryImage" src="" alt="" style="max-width: 100%; max-height: 70vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedBooths = [];
let selectedServices = [];
let currentZoom = 1;
let selectedBoothId = null;
let contactCount = 0;
let boothSelections = {};

const pricingConfig = {
    basePricePerSqft: {{ $exhibition->price_per_sqft ?? 0 }},
    rawPricePerSqft: {{ $exhibition->raw_price_per_sqft ?? 0 }},
    orphanPricePerSqft: {{ $exhibition->orphand_price_per_sqft ?? 0 }},
    sidePercents: {
        1: {{ $exhibition->side_1_open_percent ?? 0 }},
        2: {{ $exhibition->side_2_open_percent ?? 0 }},
        3: {{ $exhibition->side_3_open_percent ?? 0 }},
        4: {{ $exhibition->side_4_open_percent ?? 0 }},
    },
    premiumPrice: {{ $exhibition->premium_price ?? 0 }},
    economyPrice: {{ $exhibition->economy_price ?? 0 }},
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupBoothSelection();
    setupFilters();
    setupZoom();
    setupMergeSplit();
    setupPriceRange();
    toggleServicesCard();
    
    // Load floorplan positions from JSON if needed
    loadFloorplanPositionsFromJson();
    
    // Pre-select booths from query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const boothIds = urlParams.get('booths');
    if (boothIds) {
        const ids = boothIds.split(',');
        ids.forEach(boothId => {
            const booth = document.querySelector(`[data-booth-id="${boothId.trim()}"]`);
            if (booth) {
                toggleBoothSelection(boothId.trim());
                showBoothDetails(boothId.trim());
            }
        });
    }
});

// Load floorplan positions from JSON file as fallback
async function loadFloorplanPositionsFromJson() {
    const exhibitionId = {{ $exhibition->id }};
    const booths = document.querySelectorAll('.booth-item');
    
    // Check if any booths are missing positions
    let needsPositions = false;
    booths.forEach(booth => {
        const style = booth.getAttribute('style') || '';
        // Check if position is using fallback (loop index calculation)
        if (style.includes('left:') && (style.match(/left:\s*(\d+)/)?.[1] || '0') < 100) {
            // Might be using fallback, check if it's a calculated position
            const leftMatch = style.match(/left:\s*(\d+)/);
            const topMatch = style.match(/top:\s*(\d+)/);
            if (leftMatch && topMatch) {
                // If positions seem like fallback values, try to load from JSON
                needsPositions = true;
            }
        }
    });
    
    // Only load if we have booths and they might need positions
    if (booths.length > 0) {
        try {
            const response = await fetch(`{{ route('floorplan.config.load', $exhibition->id) }}`, {
                headers: { 'Accept': 'application/json' }
            });
            const config = await response.json();
            
            if (config.booths && Array.isArray(config.booths)) {
                // Update booth positions from JSON (match by booth name)
                config.booths.forEach(boothData => {
                    const boothName = boothData.id;
                    if (!boothName) return;
                    
                    // Find booth by name
                    const booth = Array.from(booths).find(b => {
                        return b.getAttribute('data-booth-name') === boothName;
                    });
                    
                    if (booth) {
                        const currentStyle = booth.getAttribute('style') || '';
                        let newStyle = currentStyle;
                        
                        // Update position if provided in JSON
                        if (boothData.x !== undefined) {
                            newStyle = newStyle.replace(/left:\s*[^;]+/, `left: ${boothData.x}px`);
                            if (!newStyle.includes('left:')) {
                                newStyle += ` left: ${boothData.x}px;`;
                            }
                        }
                        if (boothData.y !== undefined) {
                            newStyle = newStyle.replace(/top:\s*[^;]+/, `top: ${boothData.y}px`);
                            if (!newStyle.includes('top:')) {
                                newStyle += ` top: ${boothData.y}px;`;
                            }
                        }
                        if (boothData.width !== undefined) {
                            newStyle = newStyle.replace(/width:\s*[^;]+/, `width: ${boothData.width}px`);
                            if (!newStyle.includes('width:')) {
                                newStyle += ` width: ${boothData.width}px;`;
                            }
                        }
                        if (boothData.height !== undefined) {
                            newStyle = newStyle.replace(/height:\s*[^;]+/, `height: ${boothData.height}px`);
                            if (!newStyle.includes('height:')) {
                                newStyle += ` height: ${boothData.height}px;`;
                            }
                        }
                        
                        booth.setAttribute('style', newStyle);
                    }
                });
            }
        } catch (error) {
            console.warn('Failed to load floorplan positions from JSON:', error);
            // Continue without JSON positions - use database positions
        }
    }
}

// Booth Selection
function setupBoothSelection() {
    document.querySelectorAll('.booth-item').forEach(booth => {
        booth.addEventListener('click', function() {
            const boothId = this.getAttribute('data-booth-id');
            const status = this.getAttribute('data-booth-status');
            
            if (status === 'booked') {
                alert('This booth is already booked');
                return;
            }

            ensureBoothSelection(boothId);
            // Single click toggles selection (supports multi-select)
            toggleBoothSelection(boothId);
            showBoothDetails(boothId);
        });
    });
}

function ensureBoothSelection(boothId) {
    if (!boothSelections[boothId]) {
        const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
        const defaultSides = parseInt(booth?.getAttribute('data-booth-sides')) || 1;
        boothSelections[boothId] = { type: 'Raw', sides: defaultSides };
        applyBoothSelection(boothId);
    }
}

function renderBoothSelectionControls(boothId) {
    const selection = boothSelections[boothId];
    const typeContainer = document.getElementById('boothTypeOptions');
    const sideContainer = document.getElementById('boothSideOptions');
    const controls = document.getElementById('boothSelectionControls');

    if (!typeContainer || !sideContainer || !controls) return;

    typeContainer.innerHTML = `
        <label><input type="radio" name="boothType-${boothId}" value="Raw"> Raw</label>
        <label><input type="radio" name="boothType-${boothId}" value="Orphand"> Orphand</label>
    `;

    sideContainer.innerHTML = [1,2,3,4].map(side => `
        <label>
            <input type="checkbox" class="side-choice" data-booth="${boothId}" value="${side}"> ${side} Side${side > 1 ? 's' : ''}
        </label>
    `).join('');

    controls.style.display = 'block';

    // Set defaults
    const typeInputs = document.querySelectorAll(`input[name="boothType-${boothId}"]`);
    typeInputs.forEach(input => {
        input.checked = input.value === selection.type;
        input.addEventListener('change', () => {
            boothSelections[boothId].type = input.value;
            syncBoothSelectionDisplay(boothId);
        });
    });

    const sideInputs = document.querySelectorAll(`.side-choice[data-booth="${boothId}"]`);
    sideInputs.forEach(input => {
        input.checked = parseInt(input.value) === selection.sides;
        input.addEventListener('change', () => {
            sideInputs.forEach(cb => {
                if (cb !== input) cb.checked = false;
            });
            boothSelections[boothId].sides = parseInt(input.value);
            syncBoothSelectionDisplay(boothId);
        });
    });

    syncBoothSelectionDisplay(boothId);
}

function computeBoothPrice(booth, selection) {
    if (!booth) return 0;
    
    const size = parseFloat(booth.getAttribute('data-booth-size')) || 0;
    const category = booth.getAttribute('data-booth-category') || 'Standard';
    
    // Base price from exhibition (not multiplied by size)
    const basePrice = pricingConfig.basePricePerSqft;
    
    // Add Raw/Orphand price per sqft * size to base price
    const rawOrphandPricePerSqft = selection.type === 'Raw' ? pricingConfig.rawPricePerSqft : pricingConfig.orphanPricePerSqft;
    const rawOrphandAddition = rawOrphandPricePerSqft * size;
    
    // Start with base price + Raw/Orphand addition
    let price = basePrice + rawOrphandAddition;
    
    // Add side percentage amount (not multiplied, but added)
    const sidePercent = pricingConfig.sidePercents[selection.sides] || 0;
    price = price + sidePercent;

    // Add/subtract category premium/economy price
    if (category === 'Premium') {
        price += pricingConfig.premiumPrice;
    } else if (category === 'Economy') {
        price -= pricingConfig.economyPrice;
    }

    return Math.max(0, price);
}

function applyBoothSelection(boothId) {
    const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
    if (!booth) return;
    const selection = boothSelections[boothId];
    const price = computeBoothPrice(booth, selection);
    booth.setAttribute('data-booth-price', price.toFixed(2));
    updateBoothPriceDisplay(price, selection, booth);
    if (selectedBooths.includes(boothId)) {
        updateSelectedBoothsList();
    }
}

function syncBoothSelectionDisplay(boothId) {
    applyBoothSelection(boothId);
    const selection = boothSelections[boothId];
    const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
    const price = computeBoothPrice(booth, selection);
    updateBoothPriceDisplay(price, selection, booth);
    
    // Update selected booths list if this booth is selected
    if (selectedBooths.includes(boothId)) {
        updateSelectedBoothsList();
    }
}

function updateBoothPriceDisplay(price = 0, selection = null, booth = null) {
    const priceEl = document.getElementById('boothDetailPrice');
    const calcPriceEl = document.getElementById('boothCalculatedPrice');
    if (priceEl) priceEl.textContent = `₹${Number(price).toLocaleString()}`;
    if (calcPriceEl) calcPriceEl.textContent = `₹${Number(price).toLocaleString()}`;

    // Update base price display
    if (booth) {
        const basePrice = pricingConfig.basePricePerSqft;
        const basePriceEl = document.getElementById('boothBasePrice');
        if (basePriceEl) basePriceEl.textContent = `₹${Number(basePrice).toLocaleString()}`;
    }

    if (selection) {
        const typeEl = document.getElementById('boothDetailType');
        const sideEl = document.getElementById('boothDetailSides');
        if (typeEl) typeEl.textContent = selection.type;
        if (sideEl) sideEl.textContent = selection.sides;
    }
}

function showBoothDetails(boothId) {
    selectedBoothId = boothId;
    const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
    if (!booth) return;
    
    ensureBoothSelection(boothId);
    const selection = boothSelections[boothId];
    const panel = document.getElementById('boothDetailsPanel');
    const details = document.getElementById('boothDetails');
    
    const status = booth.getAttribute('data-booth-status');
    const isMerged = booth.getAttribute('data-booth-merged') === 'true';
    const originals = booth.getAttribute('data-merged-originals');
    const included = getBoothIncluded(booth.getAttribute('data-booth-size'));
    
    let statusText = status.toUpperCase();
    if (isMerged && status === 'available') {
        statusText = 'AVAILABLE (MERGED)';
    }
    
    details.innerHTML = `
        <div class="detail-row">
            <span class="detail-label">Booth ID</span>
            <span class="detail-value">${booth.getAttribute('data-booth-name')}${isMerged ? ' (Merged)' : ''}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value">${statusText}</span>
        </div>
        ${originals ? `
        <div class="detail-row">
            <span class="detail-label">Original Booths</span>
            <span class="detail-value">${originals}</span>
        </div>
        ` : ''}
        <div class="detail-row">
            <span class="detail-label">Size</span>
            <span class="detail-value">${booth.getAttribute('data-booth-size')} sq ft</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Base Price</span>
            <span class="detail-value" id="boothBasePrice">₹0</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Final Price</span>
            <span class="detail-value price" id="boothDetailPrice">₹0</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Open Sides</span>
            <span class="detail-value" id="boothDetailSides">${selection.sides}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Category</span>
            <span class="detail-value">${booth.getAttribute('data-booth-category') || 'Standard'}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Type</span>
            <span class="detail-value" id="boothDetailType">${selection.type}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Included</span>
            <span class="detail-value">${included}</span>
        </div>
    `;
    
    renderBoothSelectionControls(boothId);
    
    // Calculate and display initial price
    syncBoothSelectionDisplay(boothId);

    panel.style.display = 'block';
    
    // Update button states
    const isSelected = selectedBooths.includes(boothId);
    document.getElementById('selectBoothBtn').textContent = isSelected ? 'Deselect Booth' : 'Select Booth';
    document.getElementById('mergeBoothBtn').disabled = selectedBooths.length < 2;
    document.getElementById('splitBoothBtn').disabled = selectedBooths.length !== 1;
}

function getBoothIncluded(boothSize) {
    // Get included items from stall schemes based on booth size
    const stallSchemes = @json($exhibition->stallSchemes ?? []);
    if (!stallSchemes || stallSchemes.length === 0) {
        return 'Table, 2 Chairs, Power Outlet';
    }
    
    // Convert sqft to sqm (approximate: 1 sqm ≈ 10.764 sqft)
    const sizeSqm = parseFloat(boothSize) / 10.764;
    
    // Find matching stall scheme (closest match)
    let matchedScheme = null;
    let minDiff = Infinity;
    
    stallSchemes.forEach(scheme => {
        const diff = Math.abs(scheme.size_sqm - sizeSqm);
        if (diff < minDiff) {
            minDiff = diff;
            matchedScheme = scheme;
        }
    });
    
    if (matchedScheme && matchedScheme.items && matchedScheme.items.length > 0) {
        return matchedScheme.items.map(item => 
            `${item.quantity || 1} ${item.name || 'Item'}`
        ).join(', ');
    }
    
    return 'Table, 2 Chairs, Power Outlet';
}

function toggleBoothSelection(boothId) {
    const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
    if (!booth) return;
    
    const status = booth.getAttribute('data-booth-status');
    if (status === 'booked') {
        alert('This booth is already booked');
        return;
    }
    
    const index = selectedBooths.indexOf(boothId);
    
    if (index > -1) {
        selectedBooths.splice(index, 1);
        booth.classList.remove('booth-selected');
    } else {
        ensureBoothSelection(boothId);
        selectedBooths.push(boothId);
        booth.classList.add('booth-selected');
    }
    
    updateSelectedBoothsList();
    updateProceedButton();
}

function updateSelectedBoothsList() {
    const list = document.getElementById('selectedBoothsList');
    const totalDiv = document.getElementById('selectedTotal');
    let total = 0;
    
    if (selectedBooths.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>No booths selected</p>
            </div>
        `;
        totalDiv.style.display = 'none';
        updateTotalAmount();
        return;
    }
    
    let html = '';
    selectedBooths.forEach(boothId => {
        const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
        if (booth) {
            const price = parseFloat(booth.getAttribute('data-booth-price'));
            total += price;
            html += `
                <div class="selected-booth-item">
                    <div class="selected-booth-info">
                        <div class="selected-booth-name">${booth.getAttribute('data-booth-name')}</div>
                        <div class="selected-booth-price">₹${price.toLocaleString()}</div>
                    </div>
                    <button type="button" class="remove-booth" onclick="removeBooth('${boothId}')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        }
    });
    
    list.innerHTML = html;
    document.getElementById('totalAmount').textContent = `₹${total.toLocaleString()}`;
    totalDiv.style.display = 'block';
    updateTotalAmount();
    toggleServicesCard();
}

function updateServiceSelection() {
    selectedServices = [];
    let servicesTotal = 0;
    const checkboxes = document.querySelectorAll('.service-checkbox:checked');
    
    checkboxes.forEach(checkbox => {
        const serviceId = checkbox.getAttribute('data-service-id');
        const servicePrice = parseFloat(checkbox.getAttribute('data-service-price'));
        const serviceName = checkbox.getAttribute('data-service-name');
        selectedServices.push({
            id: serviceId,
            price: servicePrice,
            name: serviceName
        });
        servicesTotal += servicePrice;
    });
    
    const servicesTotalDiv = document.getElementById('servicesTotal');
    const servicesTotalAmount = document.getElementById('servicesTotalAmount');
    
    if (selectedServices.length > 0) {
        servicesTotalDiv.style.display = 'block';
        servicesTotalAmount.textContent = `₹${servicesTotal.toLocaleString()}`;
    } else {
        servicesTotalDiv.style.display = 'none';
    }
    
    updateTotalAmount();
}

function updateTotalAmount() {
    let boothTotal = 0;
    selectedBooths.forEach(boothId => {
        const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
        if (booth) {
            boothTotal += parseFloat(booth.getAttribute('data-booth-price'));
        }
    });
    
    let servicesTotal = 0;
    selectedServices.forEach(service => {
        servicesTotal += service.price;
    });
    
    const grandTotal = boothTotal + servicesTotal;
    document.getElementById('totalAmount').textContent = `₹${grandTotal.toLocaleString()}`;
}

function toggleServicesCard() {
    const servicesCard = document.getElementById('servicesCard');
    if (!servicesCard) return;
    // Always show services card if it exists (services are available)
    servicesCard.style.display = 'block';
}

function openImageGallery(serviceId, imageUrl, serviceName) {
    document.getElementById('galleryModalTitle').textContent = serviceName;
    document.getElementById('galleryImage').src = imageUrl;
    document.getElementById('galleryImage').alt = serviceName;
    const modal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
    modal.show();
}

function removeBooth(boothId) {
    toggleBoothSelection(boothId);
}

// Select Booth Button
document.getElementById('selectBoothBtn').addEventListener('click', function() {
    if (selectedBoothId) {
        toggleBoothSelection(selectedBoothId);
        showBoothDetails(selectedBoothId);
    }
});

// Filters
function setupFilters() {
    document.getElementById('filterSize').addEventListener('change', applyFilters);
    document.getElementById('statusAvailable').addEventListener('change', applyFilters);
    document.getElementById('statusReserved').addEventListener('change', applyFilters);
    document.getElementById('statusBooked').addEventListener('change', applyFilters);
}

function applyFilters() {
    const sizeFilter = document.getElementById('filterSize').value;
    const priceMax = document.getElementById('priceRange').value;
    const showAvailable = document.getElementById('statusAvailable').checked;
    const showReserved = document.getElementById('statusReserved').checked;
    const showBooked = document.getElementById('statusBooked').checked;
    
    document.querySelectorAll('.booth-item').forEach(booth => {
        const size = parseFloat(booth.getAttribute('data-booth-size'));
        const price = parseFloat(booth.getAttribute('data-booth-price'));
        const status = booth.getAttribute('data-booth-status');
        
        let show = true;
        
        // Size filter
        if (sizeFilter !== 'all') {
            if (sizeFilter === 'small' && size >= 200) show = false;
            if (sizeFilter === 'medium' && (size < 200 || size > 500)) show = false;
            if (sizeFilter === 'large' && size <= 500) show = false;
        }
        
        // Price filter
        if (price > priceMax) show = false;
        
        // Status filter
        if (status === 'available' && !showAvailable) show = false;
        if (status === 'reserved' && !showReserved) show = false;
        if (status === 'booked' && !showBooked) show = false;
        
        booth.style.display = show ? 'flex' : 'none';
    });
}

// Price Range
function setupPriceRange() {
    const range = document.getElementById('priceRange');
    const value = document.getElementById('priceRangeValue');
    
    range.addEventListener('input', function() {
        value.textContent = `Up to ₹${parseInt(this.value).toLocaleString()}`;
        applyFilters();
    });
}

// Zoom
function setupZoom() {
    document.getElementById('zoomIn').addEventListener('click', () => {
        currentZoom = Math.min(currentZoom + 0.1, 2);
        applyZoom();
    });
    
    document.getElementById('zoomOut').addEventListener('click', () => {
        currentZoom = Math.max(currentZoom - 0.1, 0.5);
        applyZoom();
    });
    
    document.getElementById('resetView').addEventListener('click', () => {
        currentZoom = 1;
        applyZoom();
        selectedBooths = [];
        document.querySelectorAll('.booth-item').forEach(booth => {
            booth.classList.remove('booth-selected');
        });
        updateSelectedBoothsList();
        updateProceedButton();
    });
}

function applyZoom() {
    const container = document.getElementById('boothsContainer');
    container.style.transform = `scale(${currentZoom})`;
    container.style.transformOrigin = 'top left';
}

// Merge & Split
function setupMergeSplit() {
    document.getElementById('mergeBoothBtn').addEventListener('click', function() {
        if (selectedBooths.length < 2) {
            alert('Please select at least 2 booths to merge');
            return;
        }
        requestMerge();
    });
    
    document.getElementById('splitBoothBtn').addEventListener('click', function() {
        if (selectedBooths.length !== 1) {
            alert('Please select exactly 1 booth to split');
            return;
        }
        requestSplit();
    });
}

function requestMerge() {
    const newName = prompt('Enter name for merged booth (e.g., D1D2):');
    if (!newName) return;
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    selectedBooths.forEach(boothId => {
        formData.append('booth_ids[]', boothId);
    });
    formData.append('new_name', newName);
    
    fetch('{{ route("floorplan.merge-request", $exhibition->id) }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message with merged booth details
            const message = `Booths merged successfully!\n\nMerged Booth: ${data.merged_booth_name}\nSize: ${data.merged_booth_size} sq ft\nPrice: ₹${parseFloat(data.merged_booth_price).toLocaleString()}\n\nThe merged booth is now available for booking.`;
            alert(message);
            
            // Clear selection and reload to show merged booth
            selectedBooths = [];
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                location.reload();
            }
        } else {
            alert(data.message || 'Merge failed');
        }
    })
    .catch(error => {
        alert('Error merging booths');
        console.error(error);
    });
}

function requestSplit() {
    const splitCount = prompt('Split into how many booths? (2-4):');
    if (!splitCount || splitCount < 2 || splitCount > 4) return;
    
    const names = [];
    for (let i = 0; i < splitCount; i++) {
        const name = prompt(`Enter name for booth ${i + 1}:`);
        if (!name) return;
        names.push(name);
    }
    
    const boothId = selectedBooths[0];
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('split_count', parseInt(splitCount));
    names.forEach(name => {
        formData.append('new_names[]', name);
    });
    
    fetch(`{{ url('/exhibitions/' . $exhibition->id . '/booths') }}/${boothId}/split-request`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Booth split successfully! New booths are now available for booking.');
            selectedBooths = [];
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                location.reload();
            }
        } else {
            alert(data.message || 'Split request failed');
        }
    })
    .catch(error => {
        alert('Error submitting split request');
        console.error(error);
    });
}

// Proceed to Book
function updateProceedButton() {
    const btn = document.getElementById('proceedToBookBtn');
    btn.disabled = selectedBooths.length === 0;
}

document.getElementById('proceedToBookBtn').addEventListener('click', function() {
    if (selectedBooths.length === 0) return;
    
    const detailsUrl = "{{ route('bookings.details', $exhibition->id) }}";
    const params = new URLSearchParams();
    params.set('booths', selectedBooths.join(','));
    const meta = {};
    selectedBooths.forEach(id => {
        if (boothSelections[id]) {
            meta[id] = {
                type: boothSelections[id].type,
                sides: boothSelections[id].sides
            };
        }
    });
    if (Object.keys(meta).length > 0) {
        params.set('booth_meta', JSON.stringify(meta));
    }
    if (selectedServices.length > 0) {
        params.set('services', selectedServices.map(s => s.id).join(','));
    }
    window.location.href = `${detailsUrl}?${params.toString()}`;
});

// Add Additional Contacts
const addContactBtn = document.getElementById('addContactBtn');
if (addContactBtn) {
    addContactBtn.addEventListener('click', function() {
        if (contactCount >= 4) {
            alert('Maximum 5 contacts allowed');
            return;
        }
        
        const container = document.getElementById('additionalContacts');
        if (!container) return;
        
        const row = document.createElement('div');
        row.className = 'row mb-2';
        row.innerHTML = `
            <div class="col-md-6 mb-2">
                <input type="email" class="form-control" name="contact_emails[]" placeholder="Additional Email">
            </div>
            <div class="col-md-5 mb-2">
                <input type="tel" class="form-control" name="contact_numbers[]" placeholder="Additional Phone">
            </div>
            <div class="col-md-1 mb-2">
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="this.closest('.row').remove(); contactCount--;">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        contactCount++;
    });
}
</script>
@endpush
@endsection
