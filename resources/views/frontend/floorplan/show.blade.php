@extends('layouts.exhibitor')

@section('title', 'Floorplan - ' . $exhibition->name)
@section('page-title', 'Floorplan - ' . $exhibition->name)

@section('content')
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
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                <button class="btn btn-sm btn-primary w-100 mb-2" id="requestMergeBtn" disabled>
                    <i class="bi bi-arrow-left-right me-1"></i>Request Merge
                </button>
                <button class="btn btn-sm btn-warning w-100 mb-2" id="requestSplitBtn" disabled>
                    <i class="bi bi-scissors me-1"></i>Request Split
                </button>
                <button class="btn btn-sm btn-secondary w-100" id="resetBtn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset View
                </button>
            </div>
        </div>
    </div>
    
    <!-- Center - Floorplan Canvas -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Exhibition Hall Floorplan</h6>
            </div>
            <div class="card-body p-0" style="position: relative; overflow: auto; height: 600px; background: #f8f9fa;">
                @if($exhibition->floorplan_image)
                <img src="{{ asset('storage/' . $exhibition->floorplan_image) }}" id="floorplanImage" style="position: absolute; top: 0; left: 0; max-width: 100%; height: auto; z-index: 1;">
                @endif
                <div id="floorplanCanvas" style="position: relative; min-height: 100%; z-index: 2;">
                    @foreach($exhibition->booths as $booth)
                    <div class="booth-item" 
                         data-booth-id="{{ $booth->id }}"
                         data-booth-name="{{ $booth->name }}"
                         data-booth-size="{{ $booth->size_sqft }}"
                         data-booth-price="{{ $booth->price }}"
                         data-booth-status="{{ $booth->is_booked ? 'booked' : ($booth->is_available ? 'available' : 'reserved') }}"
                         style="position: absolute; 
                                left: {{ $booth->position_x ?? ($loop->index % 5) * 120 }}px; 
                                top: {{ $booth->position_y ?? floor($loop->index / 5) * 100 }}px; 
                                width: {{ $booth->width ?? 100 }}px; 
                                height: {{ $booth->height ?? 80 }}px;
                                background-color: {{ $booth->is_booked ? '#dc3545' : ($booth->is_available ? '#28a745' : '#ffc107') }};
                                border: 2px solid {{ $booth->is_booked ? '#b02a37' : ($booth->is_available ? '#1e7e34' : '#d39e00') }};
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
    
    <!-- Right Sidebar - Selected Booths -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-check2-square me-2"></i>Selected Booths</h6>
            </div>
            <div class="card-body">
                <div id="selectedBooths">
                    <p class="text-muted text-center mb-0">No booths selected</p>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary w-100" id="proceedToBookBtn" disabled>
                        <i class="bi bi-cart-check me-2"></i>Request Booking
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
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
                <div class="d-flex align-items-center">
                    <div style="width: 20px; height: 20px; background-color: #007bff; border: 1px solid #0056b3; margin-right: 10px;"></div>
                    <small>Selected</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Merge Request Modal -->
<div class="modal fade" id="mergeRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Booth Merge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="mergeRequestForm" action="{{ route('floorplan.merge-request', $exhibition->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Your merge request will be sent to admin for approval.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Booth Name *</label>
                        <input type="text" class="form-control" name="new_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Selected Booths</label>
                        <div id="mergeBoothsList"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Split Request Modal -->
<div class="modal fade" id="splitRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Booth Split</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="splitRequestForm" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Your split request will be sent to admin for approval.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Split Into *</label>
                        <select class="form-select" name="split_count" id="splitCount" required>
                            <option value="2">2 Booths</option>
                            <option value="3">3 Booths</option>
                            <option value="4">4 Booths</option>
                        </select>
                    </div>
                    <div id="splitNamesContainer"></div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedBooths = [];

// Booth selection
document.querySelectorAll('.booth-item').forEach(booth => {
    booth.addEventListener('click', function(e) {
        if (e.ctrlKey || e.metaKey) {
            toggleBoothSelection(this);
        } else {
            clearSelection();
            toggleBoothSelection(this);
        }
    });
});

function toggleBoothSelection(booth) {
    const boothId = booth.getAttribute('data-booth-id');
    const status = booth.getAttribute('data-booth-status');
    
    if (status === 'booked') {
        alert('This booth is already booked');
        return;
    }
    
    const index = selectedBooths.indexOf(boothId);
    
    if (index > -1) {
        selectedBooths.splice(index, 1);
        booth.style.border = status === 'available' ? '2px solid #1e7e34' : '2px solid #d39e00';
    } else {
        selectedBooths.push(boothId);
        booth.style.border = '3px solid #007bff';
    }
    
    updateSelectedBoothsDisplay();
    updateActionButtons();
}

function clearSelection() {
    selectedBooths = [];
    document.querySelectorAll('.booth-item').forEach(booth => {
        const status = booth.getAttribute('data-booth-status');
        booth.style.border = status === 'booked' ? '2px solid #b02a37' : 
                            (status === 'available' ? '2px solid #1e7e34' : '2px solid #d39e00');
    });
    updateSelectedBoothsDisplay();
    updateActionButtons();
}

function updateSelectedBoothsDisplay() {
    const container = document.getElementById('selectedBooths');
    if (selectedBooths.length === 0) {
        container.innerHTML = '<p class="text-muted text-center mb-0">No booths selected</p>';
    } else {
        let html = '<ul class="list-unstyled mb-0">';
        selectedBooths.forEach(boothId => {
            const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
            if (booth) {
                html += `<li class="mb-2">
                    <strong>${booth.getAttribute('data-booth-name')}</strong><br>
                    <small class="text-muted">${booth.getAttribute('data-booth-size')} sq ft - ₹${parseFloat(booth.getAttribute('data-booth-price')).toLocaleString()}</small>
                </li>`;
            }
        });
        html += '</ul>';
        container.innerHTML = html;
    }
}

function updateActionButtons() {
    const mergeBtn = document.getElementById('requestMergeBtn');
    const splitBtn = document.getElementById('requestSplitBtn');
    const proceedBtn = document.getElementById('proceedToBookBtn');
    
    mergeBtn.disabled = selectedBooths.length < 2;
    splitBtn.disabled = selectedBooths.length !== 1;
    proceedBtn.disabled = selectedBooths.length === 0;
}

// Merge request
document.getElementById('requestMergeBtn').addEventListener('click', function() {
    if (selectedBooths.length < 2) return;
    
    // Add hidden inputs for booth_ids
    const form = document.getElementById('mergeRequestForm');
    form.querySelectorAll('input[name="booth_ids[]"]').forEach(input => input.remove());
    selectedBooths.forEach(boothId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'booth_ids[]';
        input.value = boothId;
        form.appendChild(input);
    });
    
    let html = '<ul class="list-unstyled">';
    selectedBooths.forEach(boothId => {
        const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
        if (booth) {
            html += `<li>${booth.getAttribute('data-booth-name')}</li>`;
        }
    });
    html += '</ul>';
    document.getElementById('mergeBoothsList').innerHTML = html;
    
    new bootstrap.Modal(document.getElementById('mergeRequestModal')).show();
});

// Split request
document.getElementById('requestSplitBtn').addEventListener('click', function() {
    if (selectedBooths.length !== 1) return;
    
    const boothId = selectedBooths[0];
    const form = document.getElementById('splitRequestForm');
    form.action = `/exhibitions/{{ $exhibition->id }}/booths/${boothId}/split-request`;
    
    const splitCount = document.getElementById('splitCount');
    const container = document.getElementById('splitNamesContainer');
    
    function updateSplitNames() {
        const count = parseInt(splitCount.value);
        let html = '';
        for (let i = 0; i < count; i++) {
            html += `<div class="mb-3">
                <label class="form-label">Booth ${i + 1} Name *</label>
                <input type="text" class="form-control" name="new_names[]" required>
            </div>`;
        }
        container.innerHTML = html;
    }
    
    splitCount.addEventListener('change', updateSplitNames);
    updateSplitNames();
    
    new bootstrap.Modal(document.getElementById('splitRequestModal')).show();
});

// Reset view
document.getElementById('resetBtn').addEventListener('click', function() {
    clearSelection();
});

// Price range filter
document.getElementById('priceRange').addEventListener('input', function(e) {
    document.getElementById('priceRangeValue').textContent = parseInt(e.target.value).toLocaleString();
});

// Proceed to book
document.getElementById('proceedToBookBtn').addEventListener('click', function() {
    if (selectedBooths.length > 0) {
        window.location.href = `{{ route('bookings.create', $exhibition->id) }}?booths=${selectedBooths.join(',')}`;
    }
});
</script>
@endpush
@endsection

