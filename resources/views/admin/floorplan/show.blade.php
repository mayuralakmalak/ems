@extends('layouts.admin')

@section('title', 'Admin Panel: Hall Plan')
@section('page-title', 'Admin Panel: Hall Plan')

@push('styles')
<style>
    .floorplan-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 150px);
    }
    
    .left-sidebar {
        width: 250px;
        overflow-y: auto;
    }
    
    .center-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .right-sidebar {
        width: 350px;
        overflow-y: auto;
    }
    
    .section-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .section-description {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }
    
    .stall-button {
        padding: 15px 20px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 10px;
        text-align: center;
    }
    
    .stall-button:hover {
        border-color: #6366f1;
        transform: translateY(-2px);
    }
    
    .stall-button.selected {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .floorplan-canvas {
        flex: 1;
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        overflow: auto;
        border: 1px solid #e2e8f0;
        position: relative;
    }
    
    .floorplan-grid {
        position: relative;
        min-height: 100%;
        background: white;
        border: 1px solid #e2e8f0;
    }
    
    .stall-item {
        position: absolute;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 0.9rem;
        color: #1e293b;
    }
    
    .stall-item:hover {
        border-color: #6366f1;
        transform: scale(1.05);
        z-index: 10;
    }
    
    .stall-item.selected {
        border-color: #6366f1;
        background: #f0f9ff;
        box-shadow: 0 0 10px rgba(99, 102, 241, 0.3);
    }
    
    .stall-available {
        background: #d1fae5;
        border-color: #10b981;
    }
    
    .stall-booked {
        background: #fff3e0;
        border-color: #fd7e14;
    }
    
    .stall-reserved {
        background: #fef3c7;
        border-color: #f59e0b;
    }
    
    .btn-action {
        width: 100%;
        padding: 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-action:hover {
        border-color: #6366f1;
        background: #f8fafc;
    }
    
    .btn-action.primary {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }
    
    .btn-action.primary:hover {
        background: #4f46e5;
    }
    
    .stall-details-form {
        margin-top: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .form-control {
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.9rem;
        width: 100%;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 15px;
        opacity: 0.3;
    }
</style>
@endpush

@section('content')
<div class="floorplan-container">
    <!-- Left Sidebar -->
    <div class="left-sidebar">
        <div class="section-card">
            <h5 class="section-title">Navigation</h5>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-grid me-2"></i>Dashboard
                </a>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-file-text me-2"></i>My Bookings
                </a>
                <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-calendar me-2"></i>Book New Booth
                </a>
            </div>
        </div>
    </div>
    
    <!-- Center Content -->
    <div class="center-content">
        <div class="section-card">
            <h4 class="mb-3">Interactive Hall Plan</h4>
            <p class="text-muted mb-3">Click on a stall to view/edit details</p>
            
            <!-- Stall Quick Access Buttons -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach($exhibition->booths->take(8) as $booth)
                <button class="stall-button {{ $loop->first ? 'selected' : '' }}" 
                        data-booth-id="{{ $booth->id }}"
                        style="background-color: {{ $booth->is_booked ? '#fee2e2' : ($booth->is_available ? '#d1fae5' : '#fef3c7') }};">
                    {{ $booth->name }}
                </button>
                @endforeach
            </div>
            
            <!-- Hall Plan Canvas -->
                <div class="floorplan-canvas">
                    <div class="floorplan-grid" id="floorplanGrid" style="position: relative; min-height: 600px; width: 100%;">
                    @php
                        $floorplanImages = is_array($exhibition->floorplan_images ?? null)
                            ? $exhibition->floorplan_images
                            : (array) ($exhibition->floorplan_image ? [$exhibition->floorplan_image] : []);
                        $primaryFloorplanImage = $floorplanImages[0] ?? null;
                    @endphp
                    @if($primaryFloorplanImage)
                    <img src="{{ asset('storage/' . $primaryFloorplanImage) }}" 
                         style="position: absolute; top: 0; left: 0; max-width: 100%; height: auto; z-index: 1; opacity: 0.3;">
                    @endif
                    
                    @if($exhibition->booths->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-grid-3x3-gap"></i>
                        <p>Interactive Hall Plan Coming Soon</p>
                    </div>
                    @else
                        @foreach($exhibition->booths as $booth)
                        <div class="stall-item {{ $booth->is_booked ? 'stall-booked' : ($booth->is_available ? 'stall-available' : 'stall-reserved') }}"
                             data-booth-id="{{ $booth->id }}"
                             data-booth-name="{{ $booth->name }}"
                             style="left: {{ $booth->position_x ?? ($loop->index % 5) * 120 }}px; 
                                    top: {{ $booth->position_y ?? floor($loop->index / 5) * 100 }}px; 
                                    width: {{ $booth->width ?? 100 }}px; 
                                    height: {{ $booth->height ?? 80 }}px;"
                             onclick="selectStall({{ $booth->id }})">
                            {{ $booth->name }}
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Sidebar -->
    <div class="right-sidebar">
        <!-- Stall Details & Actions -->
        <div class="section-card">
            <h5 class="section-title">Stall Details & Actions</h5>
            <p class="section-description">Manage selected stall properties</p>
            <p class="text-muted small">Select a stall on the hall plan to edit its details.</p>
            
            <div id="stallDetailsForm" class="stall-details-form" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Stall Name</label>
                    <input type="text" class="form-control" id="stallName" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" class="form-control" id="stallCategory" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Size (sq meter)</label>
                    <input type="text" class="form-control" id="stallSize" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Price</label>
                    <input type="text" class="form-control" id="stallPrice" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" id="stallStatus" readonly>
                </div>
            </div>
        </div>
        
        <!-- Hall Plan Management -->
        <div class="section-card">
            <h5 class="section-title">Hall Plan Management</h5>
            <p class="section-description">Combine, split, or add new stalls.</p>
            
            <button class="btn-action" id="combineStallsBtn" onclick="showCombineModal()">
                <i class="bi bi-arrow-left-right"></i>Combine Stalls
            </button>
            <button class="btn-action" id="splitStallsBtn" onclick="showSplitModal()">
                <i class="bi bi-scissors"></i>Split Stalls
            </button>
            <button class="btn-action primary" id="addNewStallBtn" onclick="showAddStallModal()">
                <i class="bi bi-plus-circle"></i>Add New Stall Area
            </button>
        </div>
        
        <!-- Upload Stall Visual Variations -->
        <div class="section-card">
            <h5 class="section-title">Upload Stall Visual Variations</h5>
            <p class="section-description">Provide multiple visual options for each stall type, enhancing exhibitor choice.</p>
            
            <button class="btn-action" onclick="showUploadVariationsModal()">
                <i class="bi bi-upload"></i>Upload Variations
            </button>
        </div>
    </div>
</div>

<!-- Combine Stalls Modal -->
<div class="modal fade" id="combineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Combine Stalls</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="combineForm">
                <div class="modal-body">
                    <p class="text-muted">Select multiple stalls to combine them into one.</p>
                    <div class="mb-3">
                        <label class="form-label">New Stall Name</label>
                        <input type="text" class="form-control" name="new_name" required>
                    </div>
                    <div id="selectedStallsList"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Combine Stalls</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Split Stall Modal -->
<div class="modal fade" id="splitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Split Stall</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="splitForm">
                <div class="modal-body">
                    <p class="text-muted">Split the selected stall into multiple smaller stalls.</p>
                    <div class="mb-3">
                        <label class="form-label">Split Into</label>
                        <select class="form-select" name="split_count" required>
                            <option value="2">2 Stalls</option>
                            <option value="3">3 Stalls</option>
                            <option value="4">4 Stalls</option>
                        </select>
                    </div>
                    <div id="splitNamesContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Split Stall</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add New Stall Modal -->
<div class="modal fade" id="addStallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Stall Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.booths.store', $exhibition->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Stall Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" name="category" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Size (sq meter)</label>
                        <input type="number" class="form-control" name="size_sqft" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sides Open</label>
                        <input type="number" class="form-control" name="sides_open" min="1" max="4" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Stall</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedStallId = null;
let selectedStalls = [];

function selectStall(boothId) {
    selectedStallId = boothId;
    selectedStalls = [boothId];
    
    // Update UI
    document.querySelectorAll('.stall-item').forEach(item => {
        item.classList.remove('selected');
    });
    document.querySelectorAll('.stall-button').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    document.querySelector(`[data-booth-id="${boothId}"]`).classList.add('selected');
    document.querySelector(`.stall-button[data-booth-id="${boothId}"]`)?.classList.add('selected');
    
    // Load stall details
    loadStallDetails(boothId);
}

function loadStallDetails(boothId) {
    // Fetch booth details via AJAX
    fetch(`/ems-laravel/public/admin/exhibitions/{{ $exhibition->id }}/booths/${boothId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('stallName').value = data.name || '';
            document.getElementById('stallCategory').value = data.category || '';
            document.getElementById('stallSize').value = data.size_sqft || '';
            document.getElementById('stallPrice').value = '₹' + (parseFloat(data.price || 0)).toLocaleString();
            document.getElementById('stallStatus').value = data.is_booked ? 'Booked' : (data.is_available ? 'Available' : 'Reserved');
            document.getElementById('stallDetailsForm').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading stall details:', error);
            // Fallback: try to get data from DOM
            const boothElement = document.querySelector(`[data-booth-id="${boothId}"]`);
            if (boothElement) {
                document.getElementById('stallName').value = boothElement.getAttribute('data-booth-name') || '';
                document.getElementById('stallDetailsForm').style.display = 'block';
            }
        });
}

function showCombineModal() {
    if (selectedStalls.length < 2) {
        alert('Please select at least 2 stalls to combine');
        return;
    }
    new bootstrap.Modal(document.getElementById('combineModal')).show();
}

function showSplitModal() {
    if (!selectedStallId) {
        alert('Please select a stall to split');
        return;
    }
    new bootstrap.Modal(document.getElementById('splitModal')).show();
}

function showAddStallModal() {
    new bootstrap.Modal(document.getElementById('addStallModal')).show();
}

function showUploadVariationsModal() {
    alert('Upload variations feature coming soon');
}

// Combine form submission
document.getElementById('combineForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('/ems-laravel/public/admin/exhibitions/{{ $exhibition->id }}/booths/merge', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            booth_ids: selectedStalls,
            new_name: formData.get('new_name')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error combining stalls');
        }
    });
});

// Split form submission
document.getElementById('splitForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const splitCount = parseInt(formData.get('split_count'));
    const newNames = formData.getAll('new_names[]');
    
    fetch(`/ems-laravel/public/admin/exhibitions/{{ $exhibition->id }}/booths/${selectedStallId}/split`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            split_count: splitCount,
            new_names: newNames
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error splitting stall');
        }
    });
});

// Update split names container
document.querySelector('#splitForm select[name="split_count"]')?.addEventListener('change', function() {
    const count = parseInt(this.value);
    const container = document.getElementById('splitNamesContainer');
    let html = '';
    for (let i = 0; i < count; i++) {
        html += `<div class="mb-3">
            <label class="form-label">Stall ${i + 1} Name</label>
            <input type="text" class="form-control" name="new_names[]" required>
        </div>`;
    }
    container.innerHTML = html;
});
</script>
@endpush
@endsection
