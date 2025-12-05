@extends('layouts.admin')

@section('title', 'Interactive Floorplan')
@section('page-title', 'Interactive Floorplan - ' . $exhibition->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.exhibitions.show', $exhibition->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Exhibition
    </a>
</div>

<div class="row g-3">
    <!-- Left Sidebar - Filters & Controls -->
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
                        <input class="form-check-input" type="checkbox" id="filterReserved">
                        <label class="form-check-label" for="filterReserved">Reserved</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="filterBooked">
                        <label class="form-check-label" for="filterBooked">Booked</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small">Open Sides</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="filterSide1">
                        <label class="form-check-label" for="filterSide1">1 Side</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="filterSide2">
                        <label class="form-check-label" for="filterSide2">2 Sides</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="filterSide3">
                        <label class="form-check-label" for="filterSide3">3 Sides</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="filterSide4">
                        <label class="form-check-label" for="filterSide4">4 Sides</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
                <button class="btn btn-sm btn-primary w-100 mb-2" id="mergeBtn" disabled>
                    <i class="bi bi-arrow-left-right me-1"></i>Merge Selected
                </button>
                <button class="btn btn-sm btn-warning w-100 mb-2" id="splitBtn" disabled>
                    <i class="bi bi-scissors me-1"></i>Split Booth
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
                <div>
                    <button class="btn btn-sm btn-light" id="zoomInBtn">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <button class="btn btn-sm btn-light" id="zoomOutBtn">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                </div>
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
                         data-booth-sides="{{ $booth->sides_open }}"
                         style="position: absolute; 
                                left: {{ $booth->position_x ?? ($loop->index % 5) * 120 }}px; 
                                top: {{ $booth->position_y ?? floor($loop->index / 5) * 100 }}px; 
                                width: {{ $booth->width ?? 100 }}px; 
                                height: {{ $booth->height ?? 80 }}px;
                                background-color: {{ $booth->is_booked ? '#dc3545' : ($booth->is_available ? '#28a745' : '#ffc107') }};
                                border: 2px solid {{ $booth->is_booked ? '#b02a37' : ($booth->is_available ? '#1e7e34' : '#d39e00') }};
                                cursor: move;
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
                        <i class="bi bi-cart-check me-2"></i>Proceed to Book
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

<!-- Merge Modal -->
<div class="modal fade" id="mergeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Merge Booths</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="mergeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Booth Name *</label>
                        <input type="text" class="form-control" name="new_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Selected Booths</label>
                        <div id="mergeBoothsList"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Merge Booths</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Split Modal -->
<div class="modal fade" id="splitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Split Booth</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="splitForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Split Into *</label>
                        <select class="form-select" name="split_count" id="splitCount" required>
                            <option value="2">2 Booths</option>
                            <option value="3">3 Booths</option>
                            <option value="4">4 Booths</option>
                        </select>
                    </div>
                    <div id="splitNamesContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Split Booth</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
<script>
let selectedBooths = [];
let zoomLevel = 1;
let isDragging = false;

// Initialize interact.js for drag and drop
interact('.booth-item').draggable({
    onstart: function(event) {
        event.target.style.opacity = '0.6';
    },
    onmove: function(event) {
        const target = event.target;
        const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
        
        target.style.transform = `translate(${x}px, ${y}px)`;
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    },
    onend: function(event) {
        event.target.style.opacity = '1';
        const target = event.target;
        const boothId = target.getAttribute('data-booth-id');
        const currentX = parseFloat(target.style.left) || 0;
        const currentY = parseFloat(target.style.top) || 0;
        const x = currentX + (parseFloat(target.getAttribute('data-x')) || 0);
        const y = currentY + (parseFloat(target.getAttribute('data-y')) || 0);
        
        // Save position to server
        fetch(`/admin/exhibitions/{{ $exhibition->id }}/booths/${boothId}/position`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                position_x: x,
                position_y: y,
                width: parseFloat(target.style.width) || 100,
                height: parseFloat(target.style.height) || 80
            })
        });
        
        // Reset transform
        target.style.left = x + 'px';
        target.style.top = y + 'px';
        target.style.transform = 'translate(0px, 0px)';
        target.removeAttribute('data-x');
        target.removeAttribute('data-y');
    }
});

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
    const index = selectedBooths.indexOf(boothId);
    
    if (index > -1) {
        selectedBooths.splice(index, 1);
        booth.style.border = booth.getAttribute('data-booth-status') === 'booked' ? '2px solid #b02a37' : 
                            (booth.getAttribute('data-booth-status') === 'available' ? '2px solid #1e7e34' : '2px solid #d39e00');
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
    const mergeBtn = document.getElementById('mergeBtn');
    const splitBtn = document.getElementById('splitBtn');
    const proceedBtn = document.getElementById('proceedToBookBtn');
    
    mergeBtn.disabled = selectedBooths.length < 2;
    splitBtn.disabled = selectedBooths.length !== 1;
    proceedBtn.disabled = selectedBooths.length === 0;
}

// Merge functionality
document.getElementById('mergeBtn').addEventListener('click', function() {
    if (selectedBooths.length < 2) return;
    
    let html = '<ul class="list-unstyled">';
    selectedBooths.forEach(boothId => {
        const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
        if (booth) {
            html += `<li>${booth.getAttribute('data-booth-name')}</li>`;
        }
    });
    html += '</ul>';
    document.getElementById('mergeBoothsList').innerHTML = html;
    
    new bootstrap.Modal(document.getElementById('mergeModal')).show();
});

document.getElementById('mergeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('{{ route("admin.floorplan.merge", $exhibition->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            booth_ids: selectedBooths,
            new_name: formData.get('new_name')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error merging booths');
        }
    });
});

// Split functionality
document.getElementById('splitBtn').addEventListener('click', function() {
    if (selectedBooths.length !== 1) return;
    
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
    
    new bootstrap.Modal(document.getElementById('splitModal')).show();
});

document.getElementById('splitForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const newNames = formData.getAll('new_names[]');
    
    fetch(`/admin/exhibitions/{{ $exhibition->id }}/booths/${selectedBooths[0]}/split`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            split_count: parseInt(formData.get('split_count')),
            new_names: newNames
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error splitting booth');
        }
    });
});

// Reset view
document.getElementById('resetBtn').addEventListener('click', function() {
    clearSelection();
    zoomLevel = 1;
    document.getElementById('floorplanCanvas').style.transform = 'scale(1)';
});

// Price range filter
document.getElementById('priceRange').addEventListener('input', function(e) {
    document.getElementById('priceRangeValue').textContent = parseInt(e.target.value).toLocaleString();
    applyFilters();
});

// Apply filters
function applyFilters() {
    // Filter logic here
}

// Proceed to book
document.getElementById('proceedToBookBtn').addEventListener('click', function() {
    if (selectedBooths.length > 0) {
        // Admin can view bookings but not create them from floorplan
        alert('Please use the booking management section to create bookings.');
    }
});
</script>
@endpush
@endsection

