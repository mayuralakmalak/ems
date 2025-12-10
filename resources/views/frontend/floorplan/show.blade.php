@extends('layouts.exhibitor')

@section('title', 'Floorplan - ' . $exhibition->name)
@section('page-title', 'Floorplan - ' . $exhibition->name)

@push('styles')
<style>
    .floorplan-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 200px);
    }
    
    .left-panel {
        width: 350px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        overflow-y: auto;
    }
    
    .right-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .panel-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .panel-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
    
    .panel-title i {
        margin-right: 10px;
        color: #6366f1;
    }
    
    .booking-item {
        padding: 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 15px;
        background: #f8fafc;
    }
    
    .booking-item:last-child {
        margin-bottom: 0;
    }
    
    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .booking-booth {
        font-weight: 600;
        color: #1e293b;
        font-size: 1rem;
    }
    
    .booking-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-confirmed {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .booking-details {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 8px;
    }
    
    .booking-amount {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .payment-item {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .payment-item:last-child {
        border-bottom: none;
    }
    
    .payment-info {
        flex: 1;
    }
    
    .payment-number {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }
    
    .payment-date {
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .payment-amount {
        font-weight: 600;
        color: #6366f1;
    }
    
    .floorplan-canvas {
        flex: 1;
        position: relative;
        background: #f8fafc;
        border-radius: 12px;
        overflow: auto;
        border: 1px solid #e2e8f0;
    }
    
    .floorplan-image {
        position: absolute;
        top: 0;
        left: 0;
        max-width: 100%;
        height: auto;
        z-index: 1;
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
    }
    
    .booth-item:hover {
        transform: scale(1.05);
        z-index: 20;
    }
    
    .booth-available {
        background-color: #28a745;
        border: 2px solid #1e7e34;
    }
    
    .booth-booked {
        background-color: #dc3545;
        border: 2px solid #b02a37;
    }
    
    .booth-reserved {
        background-color: #ffc107;
        border: 2px solid #d39e00;
    }
    
    .booth-selected {
        border: 3px solid #007bff !important;
        box-shadow: 0 0 10px rgba(0,123,255,0.5);
    }
    
    .floorplan-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background: white;
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .filter-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .filter-btn {
        padding: 8px 16px;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .filter-btn:hover {
        background: #f8fafc;
        border-color: #6366f1;
    }
    
    .filter-btn.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    
    .btn-action {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .selected-booth-info {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }
    
    .selected-booth-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 1.1rem;
        margin-bottom: 8px;
    }
    
    .selected-booth-details {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 5px;
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
<div class="floorplan-container">
    <!-- Left Panel: Booking Summary & Payments -->
    <div class="left-panel">
        <!-- Booking Summary -->
        <div class="panel-card">
            <h5 class="panel-title">
                <i class="bi bi-calendar-check"></i>Booking Summary
            </h5>
            @if($bookings->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>No bookings yet</p>
                </div>
            @else
                @foreach($bookings as $booking)
                <div class="booking-item">
                    <div class="booking-header">
                        <div class="booking-booth">{{ $booking->booth->name ?? 'N/A' }}</div>
                        <span class="booking-status {{ $booking->status === 'confirmed' ? 'status-confirmed' : 'status-pending' }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                    <div class="booking-details">
                        Booking #{{ $booking->booking_number }}
                    </div>
                    <div class="booking-details">
                        {{ $booking->exhibition->name }}
                    </div>
                    <div class="booking-amount">
                        ₹{{ number_format($booking->total_amount, 0) }}
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        
        <!-- Payment & Invoices -->
        <div class="panel-card">
            <h5 class="panel-title">
                <i class="bi bi-receipt"></i>Payment & Invoices
            </h5>
            @if($payments->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>No payments yet</p>
                </div>
            @else
                @foreach($payments as $payment)
                <div class="payment-item">
                    <div class="payment-info">
                        <div class="payment-number">{{ $payment->payment_number }}</div>
                        <div class="payment-date">{{ $payment->created_at->format('d M Y') }}</div>
                    </div>
                    <div class="payment-amount">₹{{ number_format($payment->amount, 0) }}</div>
                </div>
                @endforeach
            @endif
        </div>
        
        <!-- Selected Booth Info -->
        <div class="panel-card" id="selectedBoothPanel" style="display: none;">
            <h5 class="panel-title">
                <i class="bi bi-info-circle"></i>Selected Booth
            </h5>
            <div class="selected-booth-info" id="selectedBoothInfo">
                <!-- Will be populated by JavaScript -->
            </div>
            <div class="mt-3">
                <button class="btn btn-primary w-100" id="proceedToBookBtn" disabled>
                    <i class="bi bi-cart-check me-2"></i>Proceed to Book
                </button>
            </div>
        </div>
    </div>
    
    <!-- Right Panel: Interactive Floorplan -->
    <div class="right-panel">
        <div class="floorplan-controls">
            <div class="filter-group">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="available">Available</button>
                <button class="filter-btn" data-filter="booked">Booked</button>
                <button class="filter-btn" data-filter="reserved">Reserved</button>
            </div>
            <div class="action-buttons">
                <button class="btn-action btn btn-sm btn-primary" id="requestMergeBtn" disabled>
                    <i class="bi bi-arrow-left-right me-1"></i>Request Merge
                </button>
                <button class="btn-action btn btn-sm btn-warning" id="requestSplitBtn" disabled>
                    <i class="bi bi-scissors me-1"></i>Request Split
                </button>
                <button class="btn-action btn btn-sm btn-secondary" id="resetBtn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </button>
            </div>
        </div>
        
        <div class="floorplan-canvas" id="floorplanCanvas">
            @if($exhibition->floorplan_image)
            <img src="{{ asset('storage/' . $exhibition->floorplan_image) }}" id="floorplanImage" class="floorplan-image" alt="Floorplan">
            @endif
            <div id="boothsContainer" style="position: relative; min-height: 100%; z-index: 2;">
                @foreach($exhibition->booths as $booth)
                <div class="booth-item {{ $booth->is_booked ? 'booth-booked' : ($booth->is_available ? 'booth-available' : 'booth-reserved') }}" 
                     data-booth-id="{{ $booth->id }}"
                     data-booth-name="{{ $booth->name }}"
                     data-booth-size="{{ $booth->size_sqft }}"
                     data-booth-price="{{ $booth->price }}"
                     data-booth-category="{{ $booth->category }}"
                     data-booth-type="{{ $booth->booth_type }}"
                     data-booth-sides="{{ $booth->sides_open }}"
                     data-booth-status="{{ $booth->is_booked ? 'booked' : ($booth->is_available ? 'available' : 'reserved') }}"
                     style="left: {{ $booth->position_x ?? ($loop->index % 5) * 120 }}px; 
                            top: {{ $booth->position_y ?? floor($loop->index / 5) * 100 }}px; 
                            width: {{ $booth->width ?? 100 }}px; 
                            height: {{ $booth->height ?? 80 }}px;">
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
        booth.classList.remove('booth-selected');
    } else {
        selectedBooths.push(boothId);
        booth.classList.add('booth-selected');
    }
    
    updateSelectedBoothInfo();
    updateActionButtons();
}

function clearSelection() {
    selectedBooths = [];
    document.querySelectorAll('.booth-item').forEach(booth => {
        booth.classList.remove('booth-selected');
    });
    updateSelectedBoothInfo();
    updateActionButtons();
}

function updateSelectedBoothInfo() {
    const panel = document.getElementById('selectedBoothPanel');
    const info = document.getElementById('selectedBoothInfo');
    
    if (selectedBooths.length === 0) {
        panel.style.display = 'none';
        return;
    }
    
    panel.style.display = 'block';
    
    if (selectedBooths.length === 1) {
        const booth = document.querySelector(`[data-booth-id="${selectedBooths[0]}"]`);
        if (booth) {
            info.innerHTML = `
                <div class="selected-booth-name">${booth.getAttribute('data-booth-name')}</div>
                <div class="selected-booth-details">Size: ${booth.getAttribute('data-booth-size')} sq ft</div>
                <div class="selected-booth-details">Category: ${booth.getAttribute('data-booth-category')}</div>
                <div class="selected-booth-details">Type: ${booth.getAttribute('data-booth-type')}</div>
                <div class="selected-booth-details">Sides Open: ${booth.getAttribute('data-booth-sides')}</div>
                <div class="selected-booth-details">Price: ₹${parseFloat(booth.getAttribute('data-booth-price')).toLocaleString()}</div>
            `;
        }
    } else {
        let totalPrice = 0;
        let html = '<div class="selected-booth-name">Multiple Booths Selected</div>';
        selectedBooths.forEach(boothId => {
            const booth = document.querySelector(`[data-booth-id="${boothId}"]`);
            if (booth) {
                totalPrice += parseFloat(booth.getAttribute('data-booth-price'));
                html += `<div class="selected-booth-details">${booth.getAttribute('data-booth-name')} - ₹${parseFloat(booth.getAttribute('data-booth-price')).toLocaleString()}</div>`;
            }
        });
        html += `<div class="selected-booth-details mt-2"><strong>Total: ₹${totalPrice.toLocaleString()}</strong></div>`;
        info.innerHTML = html;
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

// Filter buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.getAttribute('data-filter');
        document.querySelectorAll('.booth-item').forEach(booth => {
            const status = booth.getAttribute('data-booth-status');
            if (filter === 'all' || status === filter) {
                booth.style.display = 'flex';
            } else {
                booth.style.display = 'none';
            }
        });
    });
});

// Merge request
document.getElementById('requestMergeBtn').addEventListener('click', function() {
    if (selectedBooths.length < 2) return;
    
    const form = document.getElementById('mergeRequestForm');
    form.querySelectorAll('input[name="booth_ids[]"]').forEach(input => input.remove());
    selectedBooths.forEach(boothId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'booth_ids[]';
        input.value = boothId;
        form.appendChild(input);
    });
    
    new bootstrap.Modal(document.getElementById('mergeRequestModal')).show();
});

// Split request
document.getElementById('requestSplitBtn').addEventListener('click', function() {
    if (selectedBooths.length !== 1) return;
    
    const boothId = selectedBooths[0];
    const form = document.getElementById('splitRequestForm');
    form.action = `/ems-laravel/public/exhibitions/{{ $exhibition->id }}/booths/${boothId}/split-request`;
    
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
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('.filter-btn[data-filter="all"]').classList.add('active');
    document.querySelectorAll('.booth-item').forEach(booth => {
        booth.style.display = 'flex';
    });
});

// Proceed to book
document.getElementById('proceedToBookBtn').addEventListener('click', function() {
    if (selectedBooths.length > 0) {
        window.location.href = `{{ route('bookings.book', $exhibition->id) }}?booths=${selectedBooths.join(',')}`;
    }
});
</script>
@endpush
@endsection
