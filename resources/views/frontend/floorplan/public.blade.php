@extends('layouts.frontend')

@section('title', 'Floorplan - ' . $exhibition->name)

@section('content')
<div class="container my-5">
    <div class="mb-4">
        <a href="{{ route('exhibitions.show', $exhibition->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Exhibition
        </a>
    </div>

    <div class="row g-3">
        <!-- Left Sidebar - Filters -->
        <div class="col-lg-3">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Booth Size</label>
                        <select class="form-select form-select-sm" id="filterSize">
                            <option value="">All Sizes</option>
                            <option value="small">Small (< 15 sq ft)</option>
                            <option value="medium">Medium (15-25 sq ft)</option>
                            <option value="large">Large (> 25 sq ft)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small">Price Range</label>
                        <input type="range" class="form-range" id="priceRange" min="0" max="100000" value="100000">
                        <small class="text-muted">Up to ₹<span id="priceRangeValue">100,000</span></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="filterAvailable" checked>
                            <label class="form-check-label" for="filterAvailable">Available</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="filterBooked">
                            <label class="form-check-label" for="filterBooked">Booked</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Legend</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div style="width: 20px; height: 20px; background-color: #28a745; border: 1px solid #1e7e34; margin-right: 10px;"></div>
                        <small>Available</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div style="width: 20px; height: 20px; background-color: #ffc107; border: 1px solid #d39e00; margin-right: 10px;"></div>
                        <small>Reserved</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div style="width: 20px; height: 20px; background-color: #dc3545; border: 1px solid #b02a37; margin-right: 10px;"></div>
                        <small>Booked</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div style="width: 20px; height: 20px; background-color: #20c997; border: 1px solid #17a2b8; margin-right: 10px;"></div>
                        <small>Merged</small>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body text-center">
                    <p class="text-muted mb-3">Want to book a booth?</p>
                    <a href="{{ route('login') }}" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login to Book
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Center - Floorplan Canvas -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Exhibition Hall Floorplan - {{ $exhibition->name }}</h5>
                </div>
                <div class="card-body p-0" style="position: relative; overflow: auto; height: 600px; background: #f8f9fa;">
                    @php
                        $floorplanImages = is_array($exhibition->floorplan_images ?? null)
                            ? $exhibition->floorplan_images
                            : (array) ($exhibition->floorplan_image ? [$exhibition->floorplan_image] : []);
                        $primaryFloorplanImage = $floorplanImages[0] ?? null;
                    @endphp
                    @if($primaryFloorplanImage)
                    <img src="{{ asset('storage/' . $primaryFloorplanImage) }}" id="floorplanImage" style="position: absolute; top: 0; left: 0; max-width: 100%; height: auto; z-index: 1;">
                    @endif
                    <div id="floorplanCanvas" style="position: relative; min-height: 100%; z-index: 2;">
                        @foreach($exhibition->booths as $booth)
                        @php
                            // Skip merged original booths (they are hidden)
                            if ($booth->parent_booth_id !== null && !$booth->is_split) {
                                continue;
                            }
                            
                            // Determine booth status based on bookings
                            // Priority: booked (approved) > reserved (pending with payment) > merged > available
                            $isReserved = in_array($booth->id, $reservedBoothIds ?? []);
                            $isBooked = in_array($booth->id, $bookedBoothIds ?? []) || $booth->is_booked;
                            $isMerged = $booth->is_merged ?? false;
                            
                            if ($isBooked) {
                                $status = 'booked';
                                $bgColor = '#dc3545';
                                $borderColor = '#b02a37';
                            } elseif ($isReserved) {
                                $status = 'reserved';
                                $bgColor = '#ffc107';
                                $borderColor = '#d39e00';
                            } elseif ($isMerged && $booth->is_available) {
                                $status = 'merged';
                                $bgColor = '#20c997';
                                $borderColor = '#17a2b8';
                            } elseif ($booth->is_available) {
                                $status = 'available';
                                $bgColor = '#28a745';
                                $borderColor = '#1e7e34';
                            } else {
                                $status = 'reserved';
                                $bgColor = '#ffc107';
                                $borderColor = '#d39e00';
                            }
                        @endphp
                        <div class="booth-item" 
                             data-booth-id="{{ $booth->id }}"
                             data-booth-name="{{ $booth->name }}"
                             data-booth-size="{{ $booth->size_sqft }}"
                             data-booth-price="{{ $booth->price }}"
                             data-booth-status="{{ $status }}"
                             style="position: absolute; 
                                    left: {{ $booth->position_x ?? ($loop->index % 5) * 120 }}px; 
                                    top: {{ $booth->position_y ?? floor($loop->index / 5) * 100 }}px; 
                                    width: {{ $booth->width ?? 100 }}px; 
                                    height: {{ $booth->height ?? 80 }}px;
                                    background-color: {{ $bgColor }};
                                    border: 2px solid {{ $borderColor }};
                                    cursor: pointer;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    color: white;
                                    font-weight: bold;
                                    font-size: 12px;
                                    user-select: none;
                                    z-index: 10;
                                    box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            <div class="text-center">
                                <div>{{ $booth->name }}</div>
                                <div style="font-size: 10px;">{{ $booth->size_sqft }} sq ft</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Price range filter
document.getElementById('priceRange').addEventListener('input', function(e) {
    document.getElementById('priceRangeValue').textContent = parseInt(e.target.value).toLocaleString();
});

// Booth hover tooltip
document.querySelectorAll('.booth-item').forEach(booth => {
    booth.addEventListener('mouseenter', function() {
        const name = this.getAttribute('data-booth-name');
        const size = this.getAttribute('data-booth-size');
        const price = this.getAttribute('data-booth-price');
        const status = this.getAttribute('data-booth-status');
        
        this.title = `${name}\nSize: ${size} sq ft\nPrice: ₹${parseFloat(price).toLocaleString()}\nStatus: ${status}`;
    });
});
</script>
@endpush
@endsection

