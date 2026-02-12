@extends('layouts.frontend')

@section('title', 'Book Booth - ' . $exhibition->name)

@push('styles')
<style>
    .booking-container {
        display: flex;
        gap: 20px;
        /* Allow page to grow with content instead of fixing to viewport height */
        min-height: calc(100vh - 150px);
        margin-top: 20px;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden; /* Prevent horizontal page scroll */
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
        min-width: 0; /* Prevents flex item from overflowing */
        max-width: 100%; /* Ensures it doesn't exceed parent */
        overflow: hidden; /* Prevents content from expanding parent */
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
        min-height: 400px;
        max-height: calc(100vh - 300px);
        width: 100%;
        /* Ensure scrolling works properly */
        overflow-x: auto;
        overflow-y: auto;
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
    
    /* Company logo on booked/reserved booths - logo same size as booth box, number above image */
    .booth-item .booth-inner {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: stretch;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    .booth-item .booth-name {
        flex: 0 0 auto;
        font-size: 11px;
        line-height: 1.15;
        text-align: center;
        padding: 1px 2px;
        background: rgba(0,0,0,0.35);
        text-shadow: 0 0 2px rgba(0,0,0,0.8);
    }
    .booth-item .booth-logo {
        flex: 1 1 auto;
        min-height: 0;
        display: block;
        overflow: hidden;
    }
    .booth-item .booth-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    
    .booth-available {
        background-color: #28a745;
        border-color: #1e7e34;
    }
    
    .booth-booked {
        background-color: #fd7e14;
        border-color: #e65100;
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
        background: #fd7e14;
        border-color: #e65100;
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

    /* Exhibition Details Banner Responsive */
    @media (max-width: 768px) {
        .exhibition-details-banner > div {
            flex-direction: column !important;
            text-align: center;
        }
        
        .exhibition-details-banner > div > div:last-child {
            width: 100%;
            justify-content: center;
        }
    }

    /* Included items list inside booth details */
    .included-items-list {
        list-style: disc;
        margin: 4px 0 0 18px;
        padding: 0;
        font-size: 0.85rem;
        color: #475569;
    text-align: left;
}

.included-items-panel {
    margin-top: 15px;
}

.included-items-header {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}

.included-items-subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 10px;
}

.included-items-images {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.included-item-thumb {
    width: 70px;
    height: 70px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
}

.included-item-thumb img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}

.included-item-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 8px;
}

.included-item-main {
    flex: 1;
}

.included-item-label {
    font-size: 0.9rem;
    color: #334155;
}

.included-item-price {
    font-size: 0.8rem;
    color: #64748b;
}

.included-item-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    min-width: 160px;
}

.included-item-qty {
    width: 70px;
    padding: 4px 6px;
    font-size: 0.85rem;
}

    .included-item-line-total {
        font-size: 0.85rem;
        color: #6366f1;
        font-weight: 600;
    }

    /* Floorplan images + stall variations gallery */
    .floorplan-images-section {
        margin-top: 16px;
        background: #f8fafc;
        border-radius: 10px;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
    }

    .floorplan-images-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .floorplan-images-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .floorplan-image-thumb {
        width: 120px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .floorplan-image-thumb img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }

    .floorplan-image-thumb:hover {
        box-shadow: 0 4px 10px rgba(15,23,42,0.15);
        transform: translateY(-1px);
    }

    /* Gallery Navigation Buttons */
    .gallery-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.5);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.5rem;
        z-index: 10;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .gallery-nav-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.8);
        transform: translateY(-50%) scale(1.1);
    }

    .gallery-nav-btn:active {
        transform: translateY(-50%) scale(0.95);
    }

    .gallery-prev-btn {
        left: 20px;
    }

    .gallery-next-btn {
        right: 20px;
    }

    .gallery-nav-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .gallery-nav-btn:disabled:hover {
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.2);
    }
</style>
@endpush

@section('content')
<!-- Exhibition Details Banner -->
<div class="exhibition-details-banner" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div style="flex: 1; min-width: 300px;">
            <h3 style="margin: 0 0 10px 0; font-size: 1.5rem; font-weight: 700; color: white;">
                <i class="bi bi-calendar-event me-2"></i>{{ $exhibition->name }}
            </h3>
            @if($exhibition->description)
            <p style="margin: 0; font-size: 0.95rem; opacity: 0.9; line-height: 1.5;">
                {{ \Illuminate\Support\Str::limit($exhibition->description, 150) }}
            </p>
            @endif
        </div>
        <div style="display: flex; gap: 30px; flex-wrap: wrap; align-items: center;">
            <div style="text-align: center;">
                <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="bi bi-calendar3 me-1"></i>Date
                </div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    @if($exhibition->start_date && $exhibition->end_date)
                        @if($exhibition->start_date->format('Y-m-d') === $exhibition->end_date->format('Y-m-d'))
                            {{ $exhibition->start_date->format('M d, Y') }}
                        @else
                            {{ $exhibition->start_date->format('M d') }} - {{ $exhibition->end_date->format('M d, Y') }}
                        @endif
                    @elseif($exhibition->start_date)
                        {{ $exhibition->start_date->format('M d, Y') }}
                    @else
                        TBA
                    @endif
                </div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="bi bi-clock me-1"></i>Time
                </div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    @if($exhibition->start_time && $exhibition->end_time)
                        @php
                            $startTime = is_string($exhibition->start_time) ? \Carbon\Carbon::parse($exhibition->start_time) : $exhibition->start_time;
                            $endTime = is_string($exhibition->end_time) ? \Carbon\Carbon::parse($exhibition->end_time) : $exhibition->end_time;
                        @endphp
                        {{ $startTime->format('h:i A') }} - {{ $endTime->format('h:i A') }}
                    @elseif($exhibition->start_time)
                        @php
                            $startTime = is_string($exhibition->start_time) ? \Carbon\Carbon::parse($exhibition->start_time) : $exhibition->start_time;
                        @endphp
                        {{ $startTime->format('h:i A') }}
                    @else
                        TBA
                    @endif
                </div>
            </div>
            @if($exhibition->venue)
            <div style="text-align: center;">
                <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="bi bi-geo-alt me-1"></i>Venue
                </div>
                <div style="font-size: 1.1rem; font-weight: 600;">
                    {{ $exhibition->venue }}
                    @if($exhibition->city)
                        <div style="font-size: 0.9rem; opacity: 0.85; margin-top: 3px;">
                            {{ $exhibition->city }}{{ $exhibition->state ? ', ' . $exhibition->state : '' }}
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="booking-container">
    <!-- Left Panel - Filters -->
    <div class="left-panel">
        <div class="filter-section">
            <h5 class="filter-title">Filters</h5>
            
            <div class="filter-group">
                <label>Category</label>
                <select id="filterCategory" class="form-select">
                    <option value="all">All Categories</option>
                    @if(isset($categories) && $categories->isNotEmpty())
                        @foreach($categories as $category)
                            <option value="{{ $category['value'] }}">{{ $category['label'] }}</option>
                        @endforeach
                    @endif
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
            <div>
                <h4 class="floorplan-title">Exhibition Hall Plan</h4>
                @if(isset($floors) && $floors->count() > 1)
                <div class="floor-selection" style="margin-top: 10px;">
                    <label for="floorSelect" style="font-size: 0.9rem; color: #64748b; margin-right: 10px;">Select Hall:</label>
                    <select id="floorSelect" class="form-select" style="display: inline-block; width: auto; min-width: 200px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
                        @foreach($floors as $floor)
                            <option value="{{ $floor->id }}" {{ (isset($selectedFloorId) && $selectedFloorId == $floor->id) ? 'selected' : '' }}>
                                {{ $floor->name }} @if($floor->description) - {{ $floor->description }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
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
            @php
                // Calculate canvas dimensions (hall + 200px border on each side = 400px total)
                $BORDER_PADDING = 200;
                $hallWidth = 0;
                $hallHeight = 0;
                $canvasWidth = 0;
                $canvasHeight = 0;
                
                if ($selectedFloor && $selectedFloor->width_meters && $selectedFloor->height_meters) {
                    // Convert meters to pixels (1 meter = 50px, same as admin panel)
                    $hallWidth = $selectedFloor->width_meters * 50;
                    $hallHeight = $selectedFloor->height_meters * 50;
                    $canvasWidth = $hallWidth + ($BORDER_PADDING * 2);
                    $canvasHeight = $hallHeight + ($BORDER_PADDING * 2);
                } else {
                    // Fallback: calculate from booths or use default
                    $maxX = 0;
                    $maxY = 0;
                    foreach ($exhibition->booths as $booth) {
                        if ($booth->position_x && $booth->position_y) {
                            $maxX = max($maxX, ($booth->position_x ?? 0) + ($booth->width ?? 100));
                            $maxY = max($maxY, ($booth->position_y ?? 0) + ($booth->height ?? 80));
                        }
                    }
                    // If we have booth positions, use them; otherwise use defaults
                    if ($maxX > 0 && $maxY > 0) {
                        // Booths are positioned with 200px offset, so subtract it to get hall size
                        $hallWidth = max(2000, $maxX - $BORDER_PADDING);
                        $hallHeight = max(800, $maxY - $BORDER_PADDING);
                    } else {
                        $hallWidth = 2000;
                        $hallHeight = 800;
                    }
                    $canvasWidth = $hallWidth + ($BORDER_PADDING * 2);
                    $canvasHeight = $hallHeight + ($BORDER_PADDING * 2);
                }
                
                // Get background image (priority: floor background_image, fallback: exhibition floorplan_images)
                $backgroundImage = null;
                if ($selectedFloor && $selectedFloor->background_image) {
                    $backgroundImage = $selectedFloor->background_image;
                } elseif (is_array($exhibition->floorplan_images ?? null) && !empty($exhibition->floorplan_images)) {
                    $backgroundImage = $exhibition->floorplan_images[0];
                } elseif ($exhibition->floorplan_image) {
                    $backgroundImage = $exhibition->floorplan_image;
                }
            @endphp
            
            @if($exhibition->booths->isEmpty())
            <div style="display: flex; align-items: center; justify-content: center; min-height: 400px; flex-direction: column; gap: 15px; color: #64748b;">
                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.5;"></i>
                <div style="text-align: center;">
                    <h5 style="color: #1e293b; margin-bottom: 8px;">No Booths Available</h5>
                    <p style="margin: 0;">No booths have been configured for this exhibition yet.</p>
                    <p style="margin: 8px 0 0 0; font-size: 0.9rem;">Please contact the administrator to set up the floorplan.</p>
                </div>
            </div>
            @else
            <div id="boothsContainer" style="position: relative; width: {{ $canvasWidth }}px; height: {{ $canvasHeight }}px; z-index: 2; flex-shrink: 0;">
                @if($backgroundImage)
                <!-- Background Image (covers full canvas including 200px border, same as admin panel) -->
                <img src="{{ asset('storage/' . ltrim($backgroundImage, '/')) }}" 
                     id="floorplanBackgroundImage" 
                     style="position: absolute; 
                            top: 0; 
                            left: 0; 
                            width: {{ $canvasWidth }}px; 
                            height: {{ $canvasHeight }}px; 
                            object-fit: fill;
                            z-index: 1;
                            opacity: 0.85;">
                @endif
                @foreach($exhibition->booths as $booth)
                @php
                    // Hide split parent booths (they are replaced by their children)
                    if ($booth->is_split && $booth->parent_booth_id === null) continue;
                    // Hide merged originals (parent_booth_id not null AND not split)
                    if ($booth->parent_booth_id !== null && !$booth->is_split) continue;
                    
                    // Determine booth status
                    // Priority: booked (approved) > reserved (pending with payment) > merged > available
                    $isReserved = isset($reservedBoothIds) && in_array($booth->id, $reservedBoothIds, true);
                    $isBooked = (isset($bookedBoothIds) && in_array($booth->id, $bookedBoothIds, true)) || $booth->is_booked;
                    $isMerged = $booth->is_merged ?? false;
                    
                    if ($isBooked) {
                        $status = 'booked';
                        $statusClass = 'booth-booked';
                    } elseif ($isReserved) {
                        $status = 'reserved';
                        $statusClass = 'booth-reserved';
                    } elseif ($isMerged && $booth->is_available) {
                        $status = 'merged';
                        $statusClass = 'booth-available booth-merged';
                    } elseif ($booth->is_available) {
                        $status = 'available';
                        $statusClass = 'booth-available';
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

                    // Resolve row/orphan prices and size images for this booth from configured sizes
                    // First try to get from the relationship (eager loaded)
                    $sizeConfig = $booth->exhibitionBoothSize;
                    if (!$sizeConfig && $booth->exhibition_booth_size_id) {
                        $sizeConfig = $exhibition->boothSizes->firstWhere('id', $booth->exhibition_booth_size_id);
                    }
                    if (!$sizeConfig && $booth->size_sqft) {
                        $sizeConfig = $exhibition->boothSizes->firstWhere('size_sqft', $booth->size_sqft);
                    }
                    // If still not found, fall back to the first configured size block
                    if (!$sizeConfig) {
                        $sizeConfig = $exhibition->boothSizes->first();
                    }

                    $rowPriceForBooth = (float) ($sizeConfig->row_price ?? 0);
                    $orphanPriceForBooth = (float) ($sizeConfig->orphan_price ?? 0);
                    
                    // Get category from ExhibitionBoothSize, not from Booth directly
                    $boothCategory = $sizeConfig->category ?? $booth->category ?? 'Standard';
                    // Normalize category value (handle both numeric and text)
                    $normalizedCategory = match(trim((string)$boothCategory)) {
                        '1', 'Premium' => 'Premium',
                        '2', 'Standard' => 'Standard',
                        '3', 'Economy' => 'Economy',
                        default => $boothCategory,
                    };

                    // Normalise size-level images for this booth size (used for preview thumbnails)
                    $sizeImages = [];
                    if ($sizeConfig && !empty($sizeConfig->images)) {
                        $imagesValue = $sizeConfig->images;
                        if (is_array($imagesValue)) {
                            $sizeImages = $imagesValue;
                        } elseif (is_string($imagesValue)) {
                            $decoded = json_decode($imagesValue, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $sizeImages = $decoded;
                            } else {
                                $sizeImages = array_filter(array_map('trim', explode(',', $imagesValue)));
                            }
                        } elseif (is_object($imagesValue)) {
                            $sizeImages = array_values((array) $imagesValue);
                        }
                    }
                    
                    // Get Size Type information (length x width)
                    $sizeTypeText = '';
                    if ($sizeConfig && $sizeConfig->sizeType) {
                        $sizeTypeText = $sizeConfig->sizeType->length . ' x ' . $sizeConfig->sizeType->width;
                    }

                    // Company logo URL for booked/reserved booths
                    $boothLogoUrl = null;
                    $boothLogosList = $boothLogos ?? [];
                    if (($status === 'booked' || $status === 'reserved') && isset($boothLogosList[$booth->id]) && $boothLogosList[$booth->id]) {
                        $boothLogoUrl = $boothLogosList[$booth->id];
                    }
                @endphp
                <div class="booth-item {{ $statusClass }}" 
                     data-booth-id="{{ $booth->id }}"
                     data-booth-name="{{ $booth->name }}"
                     data-booth-size="{{ $booth->size_sqft }}"
                     data-booth-size-id="{{ $booth->exhibition_booth_size_id }}"
                     data-booth-size-type="{{ $sizeTypeText }}"
                     data-booth-price="{{ $booth->price }}"
                     data-row-price="{{ $rowPriceForBooth }}"
                     data-orphan-price="{{ $orphanPriceForBooth }}"
                     data-booth-category="{{ $normalizedCategory }}"
                     data-booth-type="{{ $booth->booth_type }}"
                     data-booth-sides="{{ $booth->sides_open ?? 1 }}"
                     data-booth-status="{{ $status }}"
                     data-booth-merged="{{ $booth->is_merged ? 'true' : 'false' }}"
                     data-merged-originals="{{ $mergedNames }}"
                     data-booth-size-images='@json($sizeImages)'
                     style="left: {{ $booth->position_x ?? ($loop->index % 5) * 120 }}px; 
                            top: {{ $booth->position_y ?? floor($loop->index / 5) * 100 }}px; 
                            width: {{ $booth->width ?? 100 }}px; 
                            height: {{ $booth->height ?? 80 }}px;">
                    @if($boothLogoUrl)
                    <div class="booth-inner">
                        <div class="booth-name">
                            {{ $booth->name }}{{ $booth->is_merged ? ' (Merged)' : '' }}
                            @if($mergedNames)
                            <div style="font-size: 9px; opacity: 0.95;">Original: {{ $mergedNames }}</div>
                            @endif
                        </div>
                        <div class="booth-logo">
                            <img src="{{ $boothLogoUrl }}" alt="Company logo">
                        </div>
                    </div>
                    @else
                    <div class="text-center">
                        <div>{{ $booth->name }}{{ $booth->is_merged ? ' (Merged)' : '' }}</div>
                        {{-- <div style="font-size: 10px;">{{ $booth->size_sqft }} </div> --}}
                        @if($mergedNames)
                        <div style="font-size: 9px; color: #0f172a;">Original: {{ $mergedNames }}</div>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
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
        
        {{-- Floorplan images gallery (below canvas, not as background) --}}
        @php
            $allFloorplanImages = collect([]);
            if ($exhibition->floorplan_image) {
                $allFloorplanImages->push($exhibition->floorplan_image);
            }
            if (is_array($exhibition->floorplan_images)) {
                $allFloorplanImages = $allFloorplanImages->merge($exhibition->floorplan_images);
            }
            $allFloorplanImages = $allFloorplanImages->filter()->unique()->values();
        @endphp
        @if($allFloorplanImages->count() > 0)
        <div class="floorplan-images-section">
            <div class="floorplan-images-title">Hall Plan images</div>
            <div class="floorplan-images-grid">
                @foreach($allFloorplanImages as $idx => $fpImage)
                    @php
                        $src = \Illuminate\Support\Str::startsWith($fpImage, ['http://', 'https://'])
                            ? $fpImage
                            : asset('storage/' . ltrim($fpImage, '/'));
                    @endphp
                    <div class="floorplan-image-thumb"
                         onclick="openImageGallery(null, '{{ $src }}', 'Hall Plan image {{ $idx + 1 }}')">
                        <img src="{{ $src }}" alt="Hall Plan image {{ $idx + 1 }}">
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($exhibition->stallVariations && $exhibition->stallVariations->count() > 0)
        @php
            // Collect all stall variation images for gallery
            $allStallVariationImages = [];
            foreach($exhibition->stallVariations as $variation) {
                $views = [
                    'Front view' => $variation->front_view,
                    'Left side' => $variation->side_view_left,
                    'Right side' => $variation->side_view_right,
                    'Back view' => $variation->back_view,
                ];
                foreach($views as $label => $path) {
                    if($path) {
                        $src = \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])
                            ? $path
                            : asset('storage/' . ltrim($path, '/'));
                        $allStallVariationImages[] = [
                            'url' => $src,
                            'title' => $variation->stall_type . ' - ' . $label
                        ];
                    }
                }
            }
        @endphp
        <div class="floorplan-images-section">
            <div class="floorplan-images-title">Stall variations</div>
            <div class="floorplan-images-grid" id="stallVariationsGrid">
                @foreach($exhibition->stallVariations as $variation)
                    @php
                        $views = [
                            'Front view' => $variation->front_view,
                            'Left side' => $variation->side_view_left,
                            'Right side' => $variation->side_view_right,
                            'Back view' => $variation->back_view,
                        ];
                    @endphp
                    @foreach($views as $label => $path)
                        @if($path)
                            @php
                                $src = \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])
                                    ? $path
                                    : asset('storage/' . ltrim($path, '/'));
                            @endphp
                            <div class="floorplan-image-thumb stall-variation-thumb"
                                 data-image-url="{{ $src }}"
                                 data-image-title="{{ $variation->stall_type }} - {{ $label }}">
                                <img src="{{ $src }}" alt="{{ $variation->stall_type }} - {{ $label }}">
                            </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
        <script>
            // Initialize stall variations gallery
            (function() {
                const stallVariationImages = @json($allStallVariationImages);
                const grid = document.getElementById('stallVariationsGrid');
                if (grid && stallVariationImages.length > 0) {
                    const thumbs = grid.querySelectorAll('.stall-variation-thumb');
                    thumbs.forEach(thumb => {
                        thumb.addEventListener('click', function() {
                            const imageUrl = this.getAttribute('data-image-url');
                            const imageTitle = this.getAttribute('data-image-title');
                            openStallVariationsGallery(stallVariationImages, imageUrl, imageTitle);
                        });
                    });
                }
            })();
        </script>
        @endif
        
        <!-- Included Items for Selected Size -->
        <div class="panel-card included-items-panel" id="includedItemsPanel" style="display: none;">
            <div class="included-items-header">Included items for this booth size</div>
            <div class="included-items-subtitle" id="includedItemsSubtitle">
                Select a booth from the floorplan to see what is included for that size.
            </div>
            <div id="includedItemsText"></div>
            <div class="included-items-images" id="includedItemsImages"></div>
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

                <!-- Sides open is set by admin per booth; no user selection -->

                <div class="detail-row" style="margin-top: 12px;">
                    <span class="detail-label">Calculated Price</span>
                    <span class="detail-value price" id="boothCalculatedPrice">₹0</span>
                </div>
            </div>
            <div id="boothSizeImagesPreview" class="included-items-images" style="margin-top: 10px;"></div>
            <div class="action-buttons">
                <button class="btn-action btn-select" id="selectBoothBtn">
                    <i class="bi bi-check-circle me-1"></i>Select Booth
                </button>
            </div>
        </div>

        <!-- Additional Services -->
        @php
            // Exhibition-specific add-on services: price comes from ExhibitionAddonService,
            // but the underlying service is the global Service model (for FK in booking_services).
            $addonServices = $exhibition->addonServices ?? collect();
            $serviceNames = $addonServices->pluck('item_name')->filter()->unique()->values();
            $servicesByName = \App\Models\Service::whereIn('name', $serviceNames)->get()->keyBy('name');

            $activeServices = $addonServices->map(function ($addon) use ($servicesByName) {
                $serviceModel = $servicesByName->get($addon->item_name);
                if (!$serviceModel) {
                    return null;
                }

                return (object) [
                    // IMPORTANT: use Service ID here so booking_services.service_id references services.id
                    'id' => $serviceModel->id,
                    'name' => $serviceModel->name,
                    'description' => $serviceModel->description,
                    'image' => $serviceModel->image,
                    'price' => $addon->price_per_quantity, // exhibition-specific price per quantity
                    'category' => $serviceModel->category ?? null,
                ];
            })->filter();
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
                                <div class="service-price" style="margin-bottom: 3px;">₹{{ number_format($service->price, 2) }}</div>
                                <small class="text-muted" style="font-size: 0.8rem; display:block; margin-bottom:4px;">Price per quantity</small>
                                @if($service->image)
                                <button type="button" class="btn-view-image" onclick="openImageGallery({{ $service->id }}, '{{ asset('storage/' . $service->image) }}', '{{ $service->name }}')" style="padding: 4px 12px; background: #6366f1; color: white; border: none; border-radius: 4px; font-size: 0.75rem; cursor: pointer; margin-top: 5px;">
                                    <i class="bi bi-image me-1"></i>View Image
                                </button>
                                @endif
                            </div>
                            <div style="min-width: 110px; text-align: right;">
                                <label style="font-size: 0.8rem; color:#64748b; display:block;">Quantity</label>
                                <input
                                    type="number"
                                    min="0"
                                    value="0"
                                    class="form-control form-control-sm service-qty-input"
                                    style="width: 90px; display:inline-block;"
                                    data-service-id="{{ $service->id }}"
                                    data-service-price="{{ $service->price }}"
                                    data-service-name="{{ $service->name }}"
                                    oninput="updateServiceSelection()"
                                >
                                <div class="service-line-total" id="serviceTotal-{{ $service->id }}" style="margin-top:4px; font-size:0.8rem; color:#6366f1; font-weight:600;"></div>
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
    </div>
</div>

<!-- Image Gallery Modal -->
<div class="modal fade" id="imageGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="background: rgba(0,0,0,0.95); border: none;">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div style="flex: 1;">
                    <h5 class="modal-title text-white mb-0" id="galleryModalTitle">Image Gallery</h5>
                    <small class="text-white-50" id="galleryImageTitle" style="display: none; font-size: 0.85rem;"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" style="position: relative; padding: 20px;">
                <!-- Previous Button -->
                <button type="button" class="gallery-nav-btn gallery-prev-btn" id="galleryPrevBtn" style="display: none;">
                    <i class="bi bi-chevron-left"></i>
                </button>
                
                <!-- Image Container -->
                <div id="galleryImageContainer" style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
                    <img id="galleryImage" src="" alt="" style="max-width: 100%; max-height: 75vh; border-radius: 8px; object-fit: contain;">
                </div>
                
                <!-- Next Button -->
                <button type="button" class="gallery-nav-btn gallery-next-btn" id="galleryNextBtn" style="display: none;">
                    <i class="bi bi-chevron-right"></i>
                </button>
                
                <!-- Image Counter -->
                <div id="galleryCounter" style="display: none; color: white; margin-top: 15px; font-size: 0.9rem;">
                    <span id="currentImageIndex">1</span> / <span id="totalImages">1</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedBooths = [];
let selectedServices = [];
let includedItemsSelection = {};
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

// Booth size configurations with included items (from DB)
const boothSizes = @json($exhibition->boothSizes->load('items'));
const storageBaseUrl = "{{ asset('storage') }}/";

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initializeBoothPrices();
    setupBoothSelection();
    setupFilters();
    setupZoom();
    setupPriceRange();
    toggleServicesCard();
    setupFloorSelection();
    setupGalleryNavigation();
    
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

// Floor Selection Handler
function setupFloorSelection() {
    const floorSelect = document.getElementById('floorSelect');
    if (floorSelect) {
        floorSelect.addEventListener('change', function() {
            const selectedFloorId = this.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('floor_id', selectedFloorId);
            // Remove booth selections when changing floors
            currentUrl.searchParams.delete('booths');
            // Reload page with new floor
            window.location.href = currentUrl.toString();
        });
    }
}

// Booth Selection
function setupBoothSelection() {
    document.querySelectorAll('.booth-item').forEach(booth => {
        booth.addEventListener('click', function() {
            const boothId = this.getAttribute('data-booth-id');
            const status = this.getAttribute('data-booth-status');
            
            // Prevent selection of booked or reserved booths
            if (status === 'booked' || status === 'reserved') {
                alert('This booth is already ' + status + ' and cannot be selected');
                return;
            }

            ensureBoothSelection(boothId);
            // Single click toggles selection (supports multi-select)
            toggleBoothSelection(boothId);
            if (selectedBooths.length > 0) {
                showBoothDetails(boothId);
            }
        });
    });
}

function ensureBoothSelection(boothId) {
    if (!boothSelections[boothId]) {
        const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
        const defaultSides = parseInt(booth?.getAttribute('data-booth-sides')) || 1;
        const defaultType = booth?.getAttribute('data-booth-type') || 'Raw';
        boothSelections[boothId] = { type: defaultType, sides: defaultSides };
        applyBoothSelection(boothId);
    }
}

function renderBoothSelectionControls(boothId) {
    const selection = boothSelections[boothId];
    const typeContainer = document.getElementById('boothTypeOptions');
    const controls = document.getElementById('boothSelectionControls');

    if (!typeContainer || !controls) return;

    typeContainer.innerHTML = `
        <label><input type="radio" name="boothType-${boothId}" value="Raw"> Raw</label>
        <label><input type="radio" name="boothType-${boothId}" value="Orphand">Shell</label>
    `;

    controls.style.display = 'block';

    const typeInputs = document.querySelectorAll(`input[name="boothType-${boothId}"]`);
    typeInputs.forEach(input => {
        input.checked = input.value === selection.type;
        input.addEventListener('change', () => {
            boothSelections[boothId].type = input.value;
            syncBoothSelectionDisplay(boothId);
        });
    });

    // Sides open is fixed per booth (set by admin); selection.sides is set from data-booth-sides in ensureBoothSelection
    syncBoothSelectionDisplay(boothId);
}

function computeBoothPrice(booth, selection) {
    if (!booth) return 0;

    const rowPrice = parseFloat(booth.getAttribute('data-row-price')) || 0;
    const orphanPrice = parseFloat(booth.getAttribute('data-orphan-price')) || 0;
    const boothPrice = selection.type === 'Orphand' ? orphanPrice : rowPrice;

    // Side-open surcharge is a percentage of booth price
    const sidePercent = pricingConfig.sidePercents[selection.sides] || 0;
    const extra = boothPrice * (sidePercent / 100);

    return Math.max(0, boothPrice + extra);
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
        const basePrice = getBoothBasePrice(booth, selection || { type: booth.getAttribute('data-booth-type') || 'Raw' });
        const basePriceEl = document.getElementById('boothBasePrice');
        if (basePriceEl) basePriceEl.textContent = `₹${Number(basePrice).toLocaleString()}`;
    }

    if (selection) {
        const typeEl = document.getElementById('boothDetailType');
        const sideEl = document.getElementById('boothDetailSides');
        if (typeEl) typeEl.textContent = selection.type === 'Orphand' ? 'Shell' : selection.type;
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
            <span class="detail-value">
                ${booth.getAttribute('data-booth-size')} sq meter
                ${booth.getAttribute('data-booth-size-type') ? ' (' + booth.getAttribute('data-booth-size-type') + ')' : ''}
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Category</span>
            <span class="detail-value">${booth.getAttribute('data-booth-category') || 'Standard'}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Type</span>
            <span class="detail-value" id="boothDetailType">${selection.type === 'Orphand' ? 'Shell' : selection.type}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Sides Open</span>
            <span class="detail-value" id="boothDetailSides">${selection.sides}</span>
        </div>
    `;
    
    renderBoothSelectionControls(boothId);
    // Only show included items if booth is selected or if there are selected booths
    if (selectedBooths.length > 0) {
        renderIncludedItemsSection(booth);
    } else {
        renderIncludedItemsSection(null);
    }
    renderBoothSizeImagesPreview(booth);
    
    // Calculate and display initial price
    syncBoothSelectionDisplay(boothId);

    panel.style.display = 'block';
    
    // Update button states
    const isSelected = selectedBooths.includes(boothId);
    document.getElementById('selectBoothBtn').textContent = isSelected ? 'Deselect Booth' : 'Select Booth';
}

function hideBoothDetails() {
    selectedBoothId = null;
    const panel = document.getElementById('boothDetailsPanel');
    const details = document.getElementById('boothDetails');
    const controls = document.getElementById('boothSelectionControls');
    const imagesPreview = document.getElementById('boothSizeImagesPreview');
    if (panel) panel.style.display = 'none';
    if (details) details.innerHTML = '';
    if (controls) controls.style.display = 'none';
    if (imagesPreview) imagesPreview.innerHTML = '';
}

function renderBoothSizeImagesPreview(booth) {
    const container = document.getElementById('boothSizeImagesPreview');
    if (!container) return;

    if (!booth) {
        container.innerHTML = '';
        return;
    }

    const raw = booth.getAttribute('data-booth-size-images');
    if (!raw) {
        container.innerHTML = '';
        return;
    }

    let paths = [];
    try {
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) {
            paths = parsed;
        }
    } catch (e) {
        // ignore parse errors
    }

    if (!paths.length) {
        container.innerHTML = '';
        return;
    }

    // Prepare all images for gallery
    const allImages = paths.map((p) => {
        if (!p) return null;
        const normalized = String(p).replace(/^\/+/, '');
        return normalized.startsWith('http')
            ? normalized
            : storageBaseUrl + normalized;
    }).filter(Boolean);
    
    const thumbs = paths.map((p, idx) => {
        if (!p) return '';
        const normalized = String(p).replace(/^\/+/, '');
        const src = normalized.startsWith('http')
            ? normalized
            : storageBaseUrl + normalized;
        const label = `Booth size image ${idx + 1}`;
        // Store images as JSON string in data attribute (will be HTML-encoded by browser)
        const imagesJson = JSON.stringify(allImages);
        return `
            <div class="included-item-thumb" data-gallery-images="${imagesJson.replace(/"/g, '&quot;')}" onclick="openBoothVisualsGallery(this, '${src.replace(/'/g, "\\'")}', '${label.replace(/'/g, "\\'")}')">
                <img src="${src}" alt="${label}">
            </div>
        `;
    }).join('');

    if (!thumbs) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <div class="included-items-header" style="margin-bottom: 6px;">Booth visuals</div>
        <div class="included-items-images">
            ${thumbs}
        </div>
    `;
}

function getBoothIncluded(boothSizeSqft, boothSizeId) {
    // Prefer matching by explicit booth size ID, then by closest size_sqft
    const sizes = boothSizes || [];
    if (!sizes.length) {
        return {
            items: ['Table', '2 Chairs', 'Power Outlet'],
            images: [],
        };
    }

    let matched = null;

    // 1) Try exact match by exhibition_booth_size_id
    if (boothSizeId) {
        matched = sizes.find(s => String(s.id) === String(boothSizeId));
    }

    // 2) Fallback: closest match by size_sqft
    if (!matched && boothSizeSqft) {
        const size = parseFloat(boothSizeSqft);
        if (!isNaN(size)) {
            let minDiff = Infinity;
            sizes.forEach(s => {
                const sSize = parseFloat(s.size_sqft || 0);
                const diff = Math.abs(sSize - size);
                if (diff < minDiff) {
                    minDiff = diff;
                    matched = s;
                }
            });
        }
    }

    if (matched) {
        const items = (matched.items || []).map((item) => ({
            key: item.id,
            label: `${item.quantity || 1} ${item.item_name || 'Item'}`,
            includedQuantity: item.quantity || 0,
            unitPrice: parseFloat(item.price || 0),
            images: Array.isArray(item.images) ? item.images : [],
        }));

        // Normalise size-level images array from the booth size record
        let sizeImages = [];
        if (Array.isArray(matched.images)) {
            sizeImages = matched.images;
        } else if (matched.images) {
            // In case it's stored as an object or comma-separated string
            if (typeof matched.images === 'string') {
                try {
                    const parsed = JSON.parse(matched.images);
                    sizeImages = Array.isArray(parsed) ? parsed : Object.values(parsed || {});
                } catch (e) {
                    sizeImages = matched.images.split(',').map(s => s.trim()).filter(Boolean);
                }
            } else if (typeof matched.images === 'object') {
                sizeImages = Object.values(matched.images || {});
            }
        }

        return { items, images: sizeImages };
    }

    return {
        items: ['Table', '2 Chairs', 'Power Outlet'],
        images: [],
    };
}

function renderIncludedItemsSection(booth) {
    const panel = document.getElementById('includedItemsPanel');
    const textContainer = document.getElementById('includedItemsText');
    const subtitle = document.getElementById('includedItemsSubtitle');
    const imagesContainer = document.getElementById('includedItemsImages');

    if (!panel || !textContainer || !subtitle || !imagesContainer) return;

    // If no booths are selected, hide the panel
    if (selectedBooths.length === 0) {
        panel.style.display = 'none';
        textContainer.innerHTML = '';
        imagesContainer.innerHTML = '';
        return;
    }

    // If multiple booths are selected, collect items from all selected booths
    let allItems = [];
    let allSizeImages = [];
    
    // Collect items from all selected booths
    selectedBooths.forEach(boothId => {
        const selectedBooth = document.querySelector(`[data-booth-id="${boothId}"]`);
        if (selectedBooth) {
            const included = getBoothIncluded(
                selectedBooth.getAttribute('data-booth-size'),
                selectedBooth.getAttribute('data-booth-size-id')
            );
            
            if (included && included.items) {
                allItems = allItems.concat(included.items);
            }
            
            if (included && included.images) {
                allSizeImages = allSizeImages.concat(included.images);
            }
        }
    });
    
    // Remove duplicate items (by key) and merge quantities if same item exists
    const itemsMap = new Map();
    allItems.forEach(item => {
        const existing = itemsMap.get(item.key);
        if (existing) {
            // If same item exists, keep the one with higher quantity or price
            // Or you can sum quantities if needed - for now, keep first occurrence
            // You can modify this logic based on requirements
        } else {
            itemsMap.set(item.key, item);
        }
    });
    allItems = Array.from(itemsMap.values());
    
    // Remove duplicate images
    allSizeImages = [...new Set(allSizeImages)];

    const items = allItems;
    const sizeImages = allSizeImages;

    // If there are neither items nor images, hide the panel
    if (!items.length && !sizeImages.length) {
        panel.style.display = 'none';
        textContainer.innerHTML = '';
        imagesContainer.innerHTML = '';
        return;
    }

    // Build list with quantity input and View image button
    const itemsHtml = items.map(item => {
        const inputId = `included-item-qty-${item.key}`;
        const currentQty = includedItemsSelection[item.key]?.quantity || 0;
        const unitPrice = item.unitPrice || 0;
        const lineTotal = currentQty * unitPrice;
        const hasImages = item.images && item.images.length > 0;
        let imgSrc = '';
        if (hasImages) {
            const first = String(item.images[0]).replace(/^\/+/, '');
            imgSrc = first.startsWith('http') ? first : storageBaseUrl + first;
        }

        return `
            <li>
                <div class="included-item-row">
                    <div class="included-item-main">
                        <div class="included-item-label">${item.label}</div>
                        ${unitPrice > 0 ? `<div class="included-item-price">₹${unitPrice.toLocaleString()} per extra</div>` : ''}
                    </div>
                    <div class="included-item-actions">
                        <input
                            type="number"
                            min="0"
                            value="${currentQty}"
                            class="included-item-qty"
                            id="${inputId}"
                            data-extra-key="${item.key}"
                            data-unit-price="${unitPrice}"
                        />
                        ${hasImages ? `
                            <button
                                type="button"
                                class="btn-view-image"
                                style="padding: 4px 12px; background: #6366f1; color: white; border: none; border-radius: 4px; font-size: 0.75rem; cursor: pointer;"
                                onclick="openImageGallery(null, '${imgSrc}', '${item.label.replace(/'/g, "\\'")}')"
                            >
                                <i class="bi bi-image me-1"></i>View Image
                            </button>
                        ` : ''}
                        <div class="included-item-line-total" id="${inputId}-total">
                            ${lineTotal > 0 ? `₹${lineTotal.toLocaleString()}` : ''}
                        </div>
                    </div>
                </div>
            </li>
        `;
    }).join('');

    if (itemsHtml) {
        textContainer.innerHTML = `<ul class="included-items-list">${itemsHtml}</ul>`;
    } else {
        textContainer.innerHTML = '';
    }

    // Render size-level images (booth size preview thumbnails)
    if (sizeImages.length) {
        // Prepare all images for gallery
        const allImages = sizeImages.map((rawPath) => {
            if (!rawPath) return null;
            const normalized = String(rawPath).replace(/^\/+/, '');
            return normalized.startsWith('http')
                ? normalized
                : storageBaseUrl + normalized;
        }).filter(Boolean);
        
        const thumbs = sizeImages.map((rawPath, idx) => {
            if (!rawPath) return '';
            const normalized = String(rawPath).replace(/^\/+/, '');
            const src = normalized.startsWith('http')
                ? normalized
                : storageBaseUrl + normalized;
            const label = `Size image ${idx + 1}`;
            // Store images as JSON string in data attribute (will be HTML-encoded by browser)
            const imagesJson = JSON.stringify(allImages);
            return `
                <div class="included-item-thumb" data-gallery-images="${imagesJson.replace(/"/g, '&quot;')}" onclick="openBoothVisualsGallery(this, '${src.replace(/'/g, "\\'")}', '${label.replace(/'/g, "\\'")}')">
                    <img src="${src}" alt="${label}">
                </div>
            `;
        }).join('');
        imagesContainer.innerHTML = thumbs;
    } else {
        imagesContainer.innerHTML = '';
    }

    // Update subtitle based on number of selected booths
    if (selectedBooths.length > 1) {
        subtitle.textContent = 'These items and booth previews are associated with all selected booth sizes:';
    } else {
        subtitle.textContent = 'These items and booth previews are associated with the selected booth size:';
    }

    // Attach listeners for quantity changes
    textContainer.querySelectorAll('.included-item-qty').forEach(input => {
        input.addEventListener('input', handleIncludedItemQtyChange);
    });

    panel.style.display = 'block';
}

function handleIncludedItemQtyChange(e) {
    const input = e.target;
    const key = input.getAttribute('data-extra-key');
    const unitPrice = parseFloat(input.getAttribute('data-unit-price')) || 0;
    let qty = parseInt(input.value, 10);
    if (isNaN(qty) || qty < 0) {
        qty = 0;
        input.value = '0';
    }

    if (!includedItemsSelection[key]) {
        includedItemsSelection[key] = { quantity: 0, unitPrice };
    }
    includedItemsSelection[key].quantity = qty;
    includedItemsSelection[key].unitPrice = unitPrice;

    const lineTotalEl = document.getElementById(`${input.id}-total`);
    if (lineTotalEl) {
        const lineTotal = qty * unitPrice;
        lineTotalEl.textContent = lineTotal > 0 ? `₹${lineTotal.toLocaleString()}` : '';
    }

    updateTotalAmount();
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
        hideBoothDetails();
        list.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>No booths selected</p>
            </div>
        `;
        if (totalDiv) totalDiv.style.display = 'none';
        updateTotalAmount();
        // Hide included items section when no booths are selected
        renderIncludedItemsSection(null);
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
    
    // Update included items section to show items from all selected booths
    const firstBooth = selectedBooths.length > 0 ? document.querySelector(`[data-booth-id="${selectedBooths[0]}"]`) : null;
    renderIncludedItemsSection(firstBooth);
}

function updateServiceSelection() {
    selectedServices = [];
    let servicesTotal = 0;
    const inputs = document.querySelectorAll('.service-qty-input');

    inputs.forEach(input => {
        const serviceId = input.getAttribute('data-service-id');
        const servicePrice = parseFloat(input.getAttribute('data-service-price')) || 0;
        const serviceName = input.getAttribute('data-service-name');
        let quantity = parseInt(input.value, 10);
        if (isNaN(quantity) || quantity < 0) {
            quantity = 0;
            input.value = '0';
        }

        const lineEl = document.getElementById(`serviceTotal-${serviceId}`);

        if (quantity > 0 && servicePrice > 0) {
            const lineTotal = servicePrice * quantity;
            selectedServices.push({
                id: serviceId,
                price: servicePrice,
                name: serviceName,
                quantity: quantity,
                lineTotal: lineTotal,
            });
            servicesTotal += lineTotal;
            if (lineEl) {
                lineEl.textContent = `₹${lineTotal.toLocaleString()}`;
            }
        } else if (lineEl) {
            lineEl.textContent = '';
        }
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
        const qty = service.quantity || 1;
        const unit = service.price || 0;
        const line = service.lineTotal != null ? service.lineTotal : qty * unit;
        servicesTotal += line;
    });

    // Included items extras total
    let includedTotal = 0;
    Object.values(includedItemsSelection).forEach(sel => {
        if (!sel) return;
        const qty = sel.quantity || 0;
        const unit = sel.unitPrice || 0;
        if (qty > 0 && unit > 0) {
            includedTotal += qty * unit;
        }
    });

    const grandTotal = boothTotal + servicesTotal + includedTotal;
    document.getElementById('totalAmount').textContent = `₹${grandTotal.toLocaleString()}`;
}

function toggleServicesCard() {
    const servicesCard = document.getElementById('servicesCard');
    if (!servicesCard) return;
    // Always show services card if it exists (services are available)
    servicesCard.style.display = 'block';
}

// Gallery state
let galleryImages = [];
let currentGalleryIndex = 0;

function openBoothVisualsGallery(element, imageUrl, imageTitle) {
    // Get all images from data attribute
    const imagesJson = element.getAttribute('data-gallery-images');
    if (!imagesJson) {
        // Fallback to single image
        openImageGallery(null, imageUrl, imageTitle);
        return;
    }
    
    try {
        const allImages = JSON.parse(imagesJson);
        if (Array.isArray(allImages) && allImages.length > 0) {
            galleryImages = allImages;
            currentGalleryIndex = allImages.findIndex(img => img === imageUrl || img.url === imageUrl);
            if (currentGalleryIndex === -1) currentGalleryIndex = 0;
            
            // Show navigation and counter
            document.getElementById('galleryPrevBtn').style.display = galleryImages.length > 1 ? 'flex' : 'none';
            document.getElementById('galleryNextBtn').style.display = galleryImages.length > 1 ? 'flex' : 'none';
            document.getElementById('galleryCounter').style.display = galleryImages.length > 1 ? 'block' : 'none';
            
            updateGalleryImage();
            document.getElementById('galleryModalTitle').textContent = 'Booth Visuals';
            
            const modal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
            modal.show();
        } else {
            openImageGallery(null, imageUrl, imageTitle);
        }
    } catch (e) {
        console.error('Error parsing gallery images:', e);
        openImageGallery(null, imageUrl, imageTitle);
    }
}

function openStallVariationsGallery(allImages, imageUrl, imageTitle) {
    if (Array.isArray(allImages) && allImages.length > 0) {
        galleryImages = allImages;
        currentGalleryIndex = allImages.findIndex(img => img.url === imageUrl || (typeof img === 'string' && img === imageUrl));
        if (currentGalleryIndex === -1) currentGalleryIndex = 0;
        
        // Show navigation and counter
        document.getElementById('galleryPrevBtn').style.display = galleryImages.length > 1 ? 'flex' : 'none';
        document.getElementById('galleryNextBtn').style.display = galleryImages.length > 1 ? 'flex' : 'none';
        document.getElementById('galleryCounter').style.display = galleryImages.length > 1 ? 'block' : 'none';
        
        updateGalleryImage();
        document.getElementById('galleryModalTitle').textContent = 'Stall Variations';
        
        const modal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
        modal.show();
    } else {
        openImageGallery(null, imageUrl, imageTitle);
    }
}

function openImageGallery(serviceId, imageUrl, serviceName) {
    // Single image (for services, etc.)
    galleryImages = [{ url: imageUrl, title: serviceName }];
    currentGalleryIndex = 0;
    
    // Hide navigation for single image
    document.getElementById('galleryPrevBtn').style.display = 'none';
    document.getElementById('galleryNextBtn').style.display = 'none';
    document.getElementById('galleryCounter').style.display = 'none';
    
    document.getElementById('galleryImage').src = imageUrl;
    document.getElementById('galleryImage').alt = serviceName;
    document.getElementById('galleryModalTitle').textContent = serviceName;
    
    const modal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
    modal.show();
}

function updateGalleryImage() {
    if (galleryImages.length === 0) return;
    
    const currentImage = galleryImages[currentGalleryIndex];
    const imageUrl = typeof currentImage === 'string' ? currentImage : currentImage.url;
    const imageTitle = typeof currentImage === 'string' ? `Image ${currentGalleryIndex + 1}` : (currentImage.title || `Image ${currentGalleryIndex + 1}`);
    
    const imgElement = document.getElementById('galleryImage');
    imgElement.src = imageUrl;
    imgElement.alt = imageTitle;
    
    // Update image title subtitle if available
    const imageTitleEl = document.getElementById('galleryImageTitle');
    if (imageTitleEl) {
        if (typeof currentImage === 'object' && currentImage.title) {
            imageTitleEl.textContent = currentImage.title;
            imageTitleEl.style.display = 'block';
        } else {
            imageTitleEl.style.display = 'none';
        }
    }
    
    // Add loading state
    imgElement.style.opacity = '0.5';
    imgElement.onload = function() {
        this.style.opacity = '1';
    };
    imgElement.onerror = function() {
        this.style.opacity = '1';
        console.error('Failed to load image:', imageUrl);
    };
    
    document.getElementById('currentImageIndex').textContent = currentGalleryIndex + 1;
    document.getElementById('totalImages').textContent = galleryImages.length;
    
    // Update button states
    const prevBtn = document.getElementById('galleryPrevBtn');
    const nextBtn = document.getElementById('galleryNextBtn');
    if (prevBtn) prevBtn.disabled = currentGalleryIndex === 0;
    if (nextBtn) nextBtn.disabled = currentGalleryIndex === galleryImages.length - 1;
}

function galleryNext() {
    if (currentGalleryIndex < galleryImages.length - 1) {
        currentGalleryIndex++;
        updateGalleryImage();
    }
}

function galleryPrev() {
    if (currentGalleryIndex > 0) {
        currentGalleryIndex--;
        updateGalleryImage();
    }
}

// Setup gallery navigation
function setupGalleryNavigation() {
    const prevBtn = document.getElementById('galleryPrevBtn');
    const nextBtn = document.getElementById('galleryNextBtn');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', galleryPrev);
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', galleryNext);
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('imageGalleryModal');
        if (modal && modal.classList.contains('show')) {
            if (e.key === 'ArrowLeft') {
                galleryPrev();
            } else if (e.key === 'ArrowRight') {
                galleryNext();
            } else if (e.key === 'Escape') {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            }
        }
    });
}

function removeBooth(boothId) {
    toggleBoothSelection(boothId);
}

// Select Booth Button
document.getElementById('selectBoothBtn').addEventListener('click', function() {
    if (selectedBoothId) {
        toggleBoothSelection(selectedBoothId);
        if (selectedBooths.length > 0) {
            const toShow = selectedBooths.includes(selectedBoothId) ? selectedBoothId : selectedBooths[0];
            showBoothDetails(toShow);
        }
    }
});

// Filters
function setupFilters() {
    const categoryFilter = document.getElementById('filterCategory');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', applyFilters);
    }
    document.getElementById('statusAvailable').addEventListener('change', applyFilters);
    document.getElementById('statusReserved').addEventListener('change', applyFilters);
    document.getElementById('statusBooked').addEventListener('change', applyFilters);
}

function applyFilters() {
    const categoryFilter = document.getElementById('filterCategory')?.value || 'all';
    const priceRangeEl = document.getElementById('priceRange');
    const priceMax = priceRangeEl ? parseFloat(priceRangeEl.value) || Infinity : Infinity;
    const showAvailable = document.getElementById('statusAvailable')?.checked ?? true;
    const showReserved = document.getElementById('statusReserved')?.checked ?? true;
    const showBooked = document.getElementById('statusBooked')?.checked ?? true;
    
    document.querySelectorAll('.booth-item').forEach(booth => {
        const category = booth.getAttribute('data-booth-category') || '';
        let price = parseFloat(booth.getAttribute('data-booth-price'));
        const rowPrice = parseFloat(booth.getAttribute('data-row-price')) || 0;
        const orphanPrice = parseFloat(booth.getAttribute('data-orphan-price')) || 0;
        const status = booth.getAttribute('data-booth-status');
        
        let show = true;
        
        // Normalize category values for comparison
        const normalizeCategory = (cat) => {
            if (!cat) return '';
            const normalized = String(cat).trim();
            // Map numeric values to text
            if (normalized === '1') return 'Premium';
            if (normalized === '2') return 'Standard';
            if (normalized === '3') return 'Economy';
            return normalized;
        };
        
        // Category filter (from Category dropdown)
        if (categoryFilter !== 'all') {
            const boothCategory = normalizeCategory(category);
            const filterCategory = normalizeCategory(categoryFilter);
            
            if (boothCategory !== filterCategory) {
                show = false;
            }
        }
        
        // Derive a usable booth price for filtering
        if (isNaN(price) || price <= 0) {
            price = Math.max(rowPrice, orphanPrice);
        }
        
        // Price filter (only when we have a finite max)
        if (Number.isFinite(priceMax) && !isNaN(price) && price > priceMax) {
            show = false;
        }
        
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
    
    if (!range || !value) return;

    // Initialize slider range based on actual booth prices so that
    // the initial filters never hide all booths just because prices
    // are higher than a hard-coded default.
    let maxPrice = 0;
    document.querySelectorAll('.booth-item').forEach(booth => {
        let price = parseFloat(booth.getAttribute('data-booth-price'));
        const rowPrice = parseFloat(booth.getAttribute('data-row-price')) || 0;
        const orphanPrice = parseFloat(booth.getAttribute('data-orphan-price')) || 0;

        if (isNaN(price) || price <= 0) {
            price = Math.max(rowPrice, orphanPrice);
        }

        if (!isNaN(price) && price > maxPrice) {
            maxPrice = price;
        }
    });

    if (maxPrice > 0) {
        const roundedMax = Math.ceil(maxPrice / 1000) * 1000;
        range.max = roundedMax;
        range.value = roundedMax;
        value.textContent = `Up to ₹${roundedMax.toLocaleString()}`;
    } else {
        // Fallback when we cannot determine prices
        value.textContent = 'All prices';
    }
    
    range.addEventListener('input', function() {
        const val = parseInt(this.value, 10) || 0;
        value.textContent = `Up to ₹${val.toLocaleString()}`;
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

// Helpers
function getBoothBasePrice(booth, selection) {
    if (!booth) return 0;
    const rowPrice = parseFloat(booth.getAttribute('data-row-price')) || 0;
    const orphanPrice = parseFloat(booth.getAttribute('data-orphan-price')) || 0;
    return selection.type === 'Orphand' ? orphanPrice : rowPrice;
}

function initializeBoothPrices() {
    document.querySelectorAll('.booth-item').forEach(booth => {
        const boothId = booth.getAttribute('data-booth-id');
        ensureBoothSelection(boothId);
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
        // Preserve legacy list of IDs for backward compatibility
        params.set('services', selectedServices.map(s => s.id).join(','));
        // New payload with full data for accurate pricing on details & server
        const servicesPayload = selectedServices.map(s => ({
            id: s.id,
            quantity: s.quantity || 1,
            unit_price: s.price || 0,
            name: s.name || '',
        }));
        params.set('services_payload', JSON.stringify(servicesPayload));
    }
    // Included item extras (only those with quantity > 0)
    const extrasPayload = [];
    Object.entries(includedItemsSelection).forEach(([key, sel]) => {
        if (!sel) return;
        const qty = sel.quantity || 0;
        const unit = sel.unitPrice || 0;
        if (qty > 0 && unit > 0) {
            extrasPayload.push({
                item_id: parseInt(key, 10),
                quantity: qty,
                unit_price: unit,
            });
        }
    });
    if (extrasPayload.length > 0) {
        params.set('included_items', JSON.stringify(extrasPayload));
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
