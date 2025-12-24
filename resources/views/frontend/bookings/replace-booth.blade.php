@extends('layouts.exhibitor')

@section('title', 'Replace Booth - ' . $exhibition->name)
@section('page-title', 'Replace Booth')

@push('styles')
<style>
    .replace-booth-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .info-banner {
        background: #dbeafe;
        border: 1px solid #93c5fd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 25px;
    }
    
    .info-banner h5 {
        color: #1e40af;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }
    
    .info-banner p {
        color: #1e3a8a;
        margin: 5px 0;
        font-size: 0.95rem;
    }
    
    .current-booth-info {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .current-booth-info h5 {
        color: #1e293b;
        margin-bottom: 15px;
        font-size: 1.1rem;
    }
    
    .booth-detail-row {
        display: flex;
        gap: 30px;
        margin-bottom: 10px;
    }
    
    .booth-detail-item {
        flex: 1;
    }
    
    .booth-detail-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .booth-detail-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 1rem;
    }
    
    .floorplan-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .floorplan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .floorplan-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }
    
    .floor-selection {
        margin-top: 10px;
    }
    
    .floor-selection label {
        font-size: 0.9rem;
        color: #64748b;
        margin-right: 10px;
    }
    
    .floor-selection select {
        display: inline-block;
        width: auto;
        min-width: 200px;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.9rem;
    }
    
    .floorplan-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .zoom-btn, .reset-btn {
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .zoom-btn:hover, .reset-btn:hover {
        background: #f8fafc;
        border-color: #6366f1;
    }
    
    .floorplan-canvas {
        position: relative;
        background: #f8fafc;
        border-radius: 12px;
        overflow: auto;
        border: 1px solid #e2e8f0;
        min-height: 500px;
        max-height: 700px;
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
        flex-direction: column;
        text-align: center;
    }
    
    .booth-item:hover {
        transform: scale(1.05);
        z-index: 20;
    }
    
    .booth-available {
        background-color: #28a745;
        border: 2px solid #1e7e34;
    }
    
    .booth-current {
        background-color: #6366f1;
        border: 3px solid #4f46e5;
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.5);
    }
    
    .booth-booked {
        background-color: #dc3545;
        border: 2px solid #b02a37;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .booth-reserved {
        background-color: #ffc107;
        border: 2px solid #d39e00;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .booth-selected {
        border: 4px solid #007bff !important;
        box-shadow: 0 0 15px rgba(0,123,255,0.7) !important;
        background-color: #17a2b8 !important;
        transform: scale(1.1);
    }
    
    .booth-unavailable {
        background-color: #6c757d;
        border: 2px solid #5a6268;
        cursor: not-allowed;
        opacity: 0.5;
    }
    
    .action-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-top: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .selected-booth-info {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .selected-booth-info h6 {
        color: #0369a1;
        margin-bottom: 10px;
    }
    
    .selected-booth-details {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .selected-booth-detail {
        font-size: 0.9rem;
    }
    
    .selected-booth-detail strong {
        color: #1e293b;
    }
    
    .btn-replace {
        background: #6366f1;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-replace:hover {
        background: #4f46e5;
    }
    
    .btn-replace:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
    }
    
    .btn-cancel {
        background: #f3f4f6;
        color: #1e293b;
        padding: 12px 24px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-cancel:hover {
        background: #e5e7eb;
    }
    
    .no-booths-message {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }
    
    .no-booths-message i {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 20px;
    }
    
    .no-booths-message h5 {
        color: #1e293b;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<div class="replace-booth-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Replace Booth</h2>
        <a href="{{ route('bookings.show', $booking->id) }}" class="btn-cancel">
            <i class="bi bi-arrow-left me-2"></i>Back to Booking Details
        </a>
    </div>
    
    <!-- Info Banner -->
    <div class="info-banner">
        <h5><i class="bi bi-info-circle me-2"></i>Booth Replacement Information</h5>
        <p><strong>Current Booth:</strong> {{ $currentBooth->name }} ({{ $currentBooth->category }}, {{ $currentBooth->size_sqft }} sq ft)</p>
        <p><strong>Replacement Criteria:</strong> You can only replace with booths that have the same category (<strong>{{ $currentBooth->category }}</strong>) and same size (<strong>{{ $currentBooth->size_sqft }} sq ft</strong>).</p>
        <p><strong>Note:</strong> All your additional services, items, payments, and badges will be preserved with the new booth.</p>
    </div>
    
    <!-- Current Booth Info -->
    <div class="current-booth-info">
        <h5><i class="bi bi-grid-3x3-gap me-2"></i>Current Booth Details</h5>
        <div class="booth-detail-row">
            <div class="booth-detail-item">
                <div class="booth-detail-label">Booth Number</div>
                <div class="booth-detail-value">{{ $currentBooth->name }}</div>
            </div>
            <div class="booth-detail-item">
                <div class="booth-detail-label">Category</div>
                <div class="booth-detail-value">{{ $currentBooth->category }}</div>
            </div>
            <div class="booth-detail-item">
                <div class="booth-detail-label">Size</div>
                <div class="booth-detail-value">{{ $currentBooth->size_sqft }} sq ft</div>
            </div>
            <div class="booth-detail-item">
                <div class="booth-detail-label">Type</div>
                <div class="booth-detail-value">{{ $currentBooth->booth_type }}</div>
            </div>
        </div>
    </div>
    
    <!-- Floor Plan Section -->
    <div class="floorplan-section">
        <div class="floorplan-header">
            <div>
                <h4 class="floorplan-title">Select Replacement Booth</h4>
                @if(isset($exhibition->floors) && $exhibition->floors && $exhibition->floors->count() > 1)
                <div class="floor-selection">
                    <label for="floorSelect">Select Floor:</label>
                    <select id="floorSelect" class="form-select">
                        @foreach($exhibition->floors as $floor)
                            <option value="{{ $floor->id }}" {{ (isset($selectedFloorId) && $selectedFloorId == $floor->id) ? 'selected' : '' }}>
                                {{ $floor->name }} @if($floor->description) - {{ $floor->description }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="floorplan-controls">
                <button class="zoom-btn" id="zoomIn">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <button class="zoom-btn" id="zoomOut">
                    <i class="bi bi-dash-lg"></i>
                </button>
                <button class="reset-btn" id="resetView">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </button>
            </div>
        </div>
        
        <div class="floorplan-canvas" id="floorplanCanvas">
            @if($availableBooths->isEmpty())
            <div class="no-booths-message">
                <i class="bi bi-inbox"></i>
                <h5>No Replacement Booths Available</h5>
                <p>There are no available booths matching your current booth's category ({{ $currentBooth->category }}) and size ({{ $currentBooth->size_sqft }} sq ft).</p>
                <p>Please check back later or contact support for assistance.</p>
            </div>
            @else
            <div id="boothsContainer" style="position: relative; min-height: 100%; z-index: 2;">
                @foreach($allBooths as $booth)
                @php
                    // Determine booth status
                    $isCurrentBooth = $booth->id == $currentBooth->id;
                    $isReserved = in_array($booth->id, $reservedBoothIds ?? []);
                    $isBooked = in_array($booth->id, $bookedBoothIds ?? []) || $booth->is_booked;
                    $isAvailable = $availableBooths->contains('id', $booth->id);
                    
                    if ($isCurrentBooth) {
                        $status = 'current';
                        $statusClass = 'booth-current';
                    } elseif ($isBooked) {
                        $status = 'booked';
                        $statusClass = 'booth-booked';
                    } elseif ($isReserved) {
                        $status = 'reserved';
                        $statusClass = 'booth-reserved';
                    } elseif ($isAvailable) {
                        $status = 'available';
                        $statusClass = 'booth-available';
                    } else {
                        $status = 'unavailable';
                        $statusClass = 'booth-unavailable';
                    }
                @endphp
                <div class="booth-item {{ $statusClass }}" 
                     data-booth-id="{{ $booth->id }}"
                     data-booth-name="{{ $booth->name }}"
                     data-booth-size="{{ $booth->size_sqft }}"
                     data-booth-category="{{ $booth->category }}"
                     data-booth-status="{{ $status }}"
                     style="left: {{ $booth->position_x ?? ($loop->index % 5) * 120 }}px; 
                            top: {{ $booth->position_y ?? floor($loop->index / 5) * 100 }}px; 
                            width: {{ $booth->width ?? 100 }}px; 
                            height: {{ $booth->height ?? 80 }}px;">
                    <div class="text-center">
                        <div>{{ $booth->name }}</div>
                        <div style="font-size: 10px;">{{ $booth->size_sqft }} sq ft</div>
                        @if($isCurrentBooth)
                        <div style="font-size: 9px; margin-top: 2px;">(Current)</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    
    <!-- Action Section -->
    <div class="action-section">
        <form id="replaceBoothForm" action="{{ route('bookings.replace', $booking->id) }}" method="POST">
            @csrf
            <input type="hidden" name="new_booth_id" id="newBoothId" value="">
            
            <div id="selectedBoothInfo" class="selected-booth-info" style="display: none;">
                <h6><i class="bi bi-check-circle me-2"></i>Selected Booth</h6>
                <div class="selected-booth-details" id="selectedBoothDetails">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            
            <div class="d-flex gap-3">
                <button type="submit" class="btn-replace" id="replaceBtn" disabled>
                    <i class="bi bi-arrow-repeat me-2"></i>Replace Booth
                </button>
                <a href="{{ route('bookings.show', $booking->id) }}" class="btn-cancel">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedBoothId = null;
let zoomLevel = 1;

// Floor selection
const floorSelect = document.getElementById('floorSelect');
if (floorSelect) {
    floorSelect.addEventListener('change', function() {
        const floorId = this.value;
        const url = new URL(window.location.href);
        url.searchParams.set('floor_id', floorId);
        window.location.href = url.toString();
    });
}

// Zoom controls
document.getElementById('zoomIn')?.addEventListener('click', function() {
    zoomLevel = Math.min(zoomLevel + 0.1, 2);
    applyZoom();
});

document.getElementById('zoomOut')?.addEventListener('click', function() {
    zoomLevel = Math.max(zoomLevel - 0.1, 0.5);
    applyZoom();
});

document.getElementById('resetView')?.addEventListener('click', function() {
    zoomLevel = 1;
    applyZoom();
    const canvas = document.getElementById('floorplanCanvas');
    canvas.scrollLeft = 0;
    canvas.scrollTop = 0;
});

function applyZoom() {
    const container = document.getElementById('boothsContainer');
    if (container) {
        container.style.transform = `scale(${zoomLevel})`;
        container.style.transformOrigin = 'top left';
    }
}

// Booth selection
document.querySelectorAll('.booth-item').forEach(booth => {
    booth.addEventListener('click', function() {
        const status = this.getAttribute('data-booth-status');
        const boothId = this.getAttribute('data-booth-id');
        
        // Only allow selection of available booths
        if (status !== 'available') {
            if (status === 'current') {
                alert('This is your current booth. Please select a different booth to replace it.');
            } else {
                alert('This booth is ' + status + ' and cannot be selected.');
            }
            return;
        }
        
        // Remove previous selection
        document.querySelectorAll('.booth-item').forEach(b => {
            b.classList.remove('booth-selected');
        });
        
        // Select this booth
        this.classList.add('booth-selected');
        selectedBoothId = boothId;
        
        // Update form
        document.getElementById('newBoothId').value = boothId;
        document.getElementById('replaceBtn').disabled = false;
        
        // Show selected booth info
        const boothName = this.getAttribute('data-booth-name');
        const boothSize = this.getAttribute('data-booth-size');
        const boothCategory = this.getAttribute('data-booth-category');
        
        document.getElementById('selectedBoothDetails').innerHTML = `
            <div class="selected-booth-detail">
                <strong>Booth Number:</strong> ${boothName}
            </div>
            <div class="selected-booth-detail">
                <strong>Category:</strong> ${boothCategory}
            </div>
            <div class="selected-booth-detail">
                <strong>Size:</strong> ${boothSize} sq ft
            </div>
        `;
        
        document.getElementById('selectedBoothInfo').style.display = 'block';
    });
});

// Form submission confirmation
document.getElementById('replaceBoothForm')?.addEventListener('submit', function(e) {
    if (!selectedBoothId) {
        e.preventDefault();
        alert('Please select a booth to replace with.');
        return false;
    }
    
    return confirm('Are you sure you want to replace your current booth? All additional services, items, payments, and badges will be preserved.');
});
</script>
@endpush

