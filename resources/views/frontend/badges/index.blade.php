@extends('layouts.exhibitor')

@section('title', 'Badge Management')
@section('page-title', 'Badge Management')

@push('styles')
<style>
    .badge-management-container {
        display: flex;
        gap: 20px;
    }
    
    .left-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .right-panel {
        width: 400px;
    }
    
    .section-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
    }
    
    .form-label {
        font-weight: 500;
        color: #334155;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .form-control, .form-select {
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 1rem;
        width: 100%;
    }
    
    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 15px;
    }
    
    .radio-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .radio-option:hover {
        border-color: #6366f1;
        background: #f8fafc;
    }
    
    .radio-option.selected {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .radio-option input[type="radio"] {
        margin: 0;
    }
    
    .badge-assignment-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    
    .badge-assignment-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .badge-assignment-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .badge-assignment-table tr:last-child td {
        border-bottom: none;
    }
    
    .action-icons {
        display: flex;
        gap: 8px;
    }
    
    .action-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .action-icon.download {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .action-icon.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .btn-add-badge {
        width: 100%;
        padding: 12px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        margin-top: 15px;
        cursor: pointer;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: 0.4s;
        border-radius: 24px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background-color: #10b981;
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    
    .btn-generate {
        width: 100%;
        padding: 12px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        margin-top: 15px;
        cursor: pointer;
    }
    
    .download-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-download {
        width: 100%;
        padding: 12px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .badge-preview {
        width: 100%;
        height: 400px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        position: relative;
    }
    
    .badge-preview-content {
        text-align: center;
    }
    
    .badge-id {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
    }
    
    .qr-code-placeholder {
        width: 200px;
        height: 200px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    
    .detail-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    
    .detail-item {
        margin-bottom: 12px;
    }
    
    .detail-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .tab {
        padding: 10px 20px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        bottom: -2px;
    }
    
    .tab.active {
        color: #6366f1;
        border-bottom-color: #6366f1;
    }
</style>
@endpush

@section('content')
<div class="badge-management-container">
    <!-- Left Panel -->
    <div class="left-panel">
        <!-- Badge Generation -->
        <div class="section-card">
            <h5 class="section-title">Badge Generation</h5>
            
            <div class="form-group">
                <label class="form-label">Select Booking</label>
                <select class="form-select" id="bookingSelect">
                    <option value="">Select Booking</option>
                    @foreach(\App\Models\Booking::where('user_id', auth()->id())->where('status', 'confirmed')->with('exhibition')->get() as $booking)
                    <option value="{{ $booking->id }}">
                        {{ $booking->booking_number }} - {{ $booking->exhibition->name ?? 'N/A' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mt-2">
                <div id="badgeLimitsBoxIndex" class="alert alert-info d-none">
                    <strong>Badge limits for this booking:</strong>
                    <ul id="badgeLimitsListIndex" class="mb-0 mt-2"></ul>
                </div>
                <small id="badgeLimitsEmptyIndex" class="text-muted d-none">
                    No badge configuration found for this exhibition. Please contact the organizer if you believe this is an error.
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Badge Type</label>
                <div class="radio-group">
                    <label class="radio-option selected">
                        <input type="radio" name="badge_type" value="Staff Management" checked>
                        <span>Staff Management (Staff)</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="badge_type" value="Exhibitors">
                        <span>Exhibitors (Exhibitors)</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="badge_type" value="General Staff">
                        <span>General Staff (Staff)</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Badge Assignment -->
        <div class="section-card">
            <h5 class="section-title">Badge Assignment</h5>
            
            <table class="badge-assignment-table">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Role</th>
                        <th>Valid Date(s)</th>
                        <th>Check-in Option</th>
                        <th>Badge Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($badges as $badge)
                    <tr>
                        <td>{{ $badge->name }}</td>
                        <td>{{ $badge->badge_type }}</td>
                        <td>
                            @php
                                $datesToShow = [];
                                if (is_array($badge->valid_for_dates) && count($badge->valid_for_dates) > 0) {
                                    foreach ($badge->valid_for_dates as $d) {
                                        if ($d) {
                                            try {
                                                $datesToShow[] = \Carbon\Carbon::parse($d)->format('d M Y');
                                            } catch (\Exception $e) {
                                                $datesToShow[] = $d;
                                            }
                                        }
                                    }
                                } elseif ($badge->valid_for_date) {
                                    try {
                                        $datesToShow[] = $badge->valid_for_date->format('d M Y');
                                    } catch (\Exception $e) {
                                        $datesToShow[] = (string) $badge->valid_for_date;
                                    }
                                }
                            @endphp
                            {{ count($datesToShow) ? implode(', ', $datesToShow) : 'N/A' }}
                        </td>
                        <td>
                            <input type="checkbox" checked disabled>
                        </td>
                        <td>
                            <input type="checkbox" checked disabled>
                        </td>
                        <td>
                            <div class="action-icons">
                                <a href="{{ route('badges.download', $badge->id) }}" class="action-icon download" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <form action="{{ route('badges.destroy', $badge->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this badge?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-icon delete" title="Delete" style="border: none; background: none; padding: 0;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">No badges assigned yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <a href="{{ route('badges.create') }}" class="btn-add-badge">
                <i class="bi bi-plus-circle me-2"></i>Add Badge
            </a>
        </div>
        
        <!-- Additional Badges -->
        <div class="section-card">
            <h5 class="section-title">Additional Badges</h5>
            <p class="text-muted small mb-3">Order additional badges if necessary.</p>
            <input type="number" class="form-control" value="0" min="0">
        </div>
        
        <!-- Generate HBL -->
        <div class="section-card">
            <h5 class="section-title">Generate HBL</h5>
            <div class="d-flex justify-content-between align-items-center">
                <span>Enable HBL Generation</span>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <button class="btn-generate">
                <i class="bi bi-printer me-2"></i>Generate & Print
            </button>
        </div>
        
        <!-- Download Options -->
        <div class="section-card">
            <h5 class="section-title">Download Options</h5>
            <div class="download-buttons">
                <button class="btn-download">
                    <i class="bi bi-download me-2"></i>Download Selected Badges
                </button>
                <button class="btn-download">
                    <i class="bi bi-file-pdf me-2"></i>Download All Badges (PDF)
                </button>
                <button class="btn-download">
                    <i class="bi bi-printer me-2"></i>Print Options
                </button>
            </div>
        </div>
        
        <!-- What is HBL -->
        <div class="section-card">
            <h5 class="section-title">What is HBL</h5>
            <p class="text-muted small mb-2">Quick How-to check-in/check-out (tracking) with badged staff.</p>
            <ul class="text-muted small" style="padding-left: 20px;">
                <li>QR code scanner</li>
                <li>(Registration) app</li>
                <li>POS (point-of-sale) system</li>
                <li>Handheld tools</li>
            </ul>
        </div>
    </div>
    
    <!-- Right Panel -->
    <div class="right-panel">
        <div class="section-card">
            <div class="tabs">
                <button class="tab active">Badge Generation</button>
                <button class="tab">Download & Print</button>
            </div>
            
            <button class="btn-download mb-3">
                <i class="bi bi-eye me-2"></i>Event Badge Preview
            </button>
            
            <div class="badge-preview">
                <div class="badge-preview-content">
                    @if($badges->count() > 0)
                        @php $selectedBadge = $badges->first(); @endphp
                        <div class="badge-id">{{ $selectedBadge->name ?? 'ASDFGH1234-FG-ASDF' }}</div>
                        @if($selectedBadge->qr_code)
                        <img src="{{ asset('storage/' . $selectedBadge->qr_code) }}" alt="QR Code" style="max-width: 200px;">
                        @else
                        <div class="qr-code-placeholder">
                            <i class="bi bi-qr-code" style="font-size: 4rem; color: #cbd5e1;"></i>
                        </div>
                        @endif
                        <p class="text-muted small">Scan the QR code to access details.</p>
                    @else
                        <div class="badge-preview-content">
                            <i class="bi bi-person-badge" style="font-size: 4rem; color: #cbd5e1;"></i>
                            <p class="text-muted mt-3">No badge selected</p>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($badges->count() > 0)
            <div class="d-flex gap-2">
                <button class="btn-download" style="flex: 1;">
                    <i class="bi bi-gear me-2"></i>Generate Badge
                </button>
                <a href="{{ route('badges.download', $badges->first()->id) }}" class="btn-download" style="flex: 1; text-decoration: none; display: inline-block;">
                    <i class="bi bi-download me-2"></i>Download Badge
                </a>
            </div>
            @endif
            
            <!-- Staff Details -->
            @if($badges->count() > 0)
            @php $selectedBadge = $badges->first(); @endphp
            <div class="detail-section">
                <h6 class="mb-3">Staff Details</h6>
                <div class="detail-item">
                    <div class="detail-label">Name</div>
                    <div class="detail-value">{{ $selectedBadge->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Role</div>
                    <div class="detail-value">{{ $selectedBadge->badge_type }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Department</div>
                    <div class="detail-value">Operations</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">{{ $selectedBadge->email ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value">{{ $selectedBadge->phone ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="badge bg-{{ $selectedBadge->status === 'approved' ? 'success' : 'warning' }}">
                            {{ ucfirst($selectedBadge->status) }}
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Valid Date(s)</div>
                    <div class="detail-value">
                        @php
                            $datesToShow = [];
                            if (is_array($selectedBadge->valid_for_dates) && count($selectedBadge->valid_for_dates) > 0) {
                                foreach ($selectedBadge->valid_for_dates as $d) {
                                    if ($d) {
                                        try {
                                            $datesToShow[] = \Carbon\Carbon::parse($d)->format('d M Y');
                                        } catch (\Exception $e) {
                                            $datesToShow[] = $d;
                                        }
                                    }
                                }
                            } elseif ($selectedBadge->valid_for_date) {
                                try {
                                    $datesToShow[] = $selectedBadge->valid_for_date->format('d M Y');
                                } catch (\Exception $e) {
                                    $datesToShow[] = (string) $selectedBadge->valid_for_date;
                                }
                            }
                        @endphp
                        {{ count($datesToShow) ? implode(', ', $datesToShow) : 'N/A' }}
                    </div>
                </div>
            </div>
            
            <!-- Event Details -->
            <div class="detail-section">
                <h6 class="mb-3">Event Details</h6>
                <div class="detail-item">
                    <div class="detail-label">Event Name</div>
                    <div class="detail-value">{{ $selectedBadge->exhibition->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">
                        {{ $selectedBadge->exhibition->start_date->format('F d') }} - {{ $selectedBadge->exhibition->end_date->format('d, Y') }}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Location</div>
                    <div class="detail-value">{{ $selectedBadge->exhibition->venue ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Description</div>
                    <div class="detail-value text-muted small">
                        {{ $selectedBadge->exhibition->description ?? 'The premier event for technology innovators, industry leaders, and enthusiasts worldwide.' }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Booking selection -> show badge limits for that exhibition/booking
document.addEventListener('DOMContentLoaded', function () {
    const bookingSelect = document.getElementById('bookingSelect');
    const badgeLimitsBox = document.getElementById('badgeLimitsBoxIndex');
    const badgeLimitsList = document.getElementById('badgeLimitsListIndex');
    const badgeLimitsEmpty = document.getElementById('badgeLimitsEmptyIndex');

    if (!bookingSelect) {
        return;
    }

    const bookingLimitsUrlTemplate = "{{ route('badges.booking-limits', ['bookingId' => ':bookingId']) }}";

    function clearBadgeLimits() {
        if (!badgeLimitsBox || !badgeLimitsList || !badgeLimitsEmpty) {
            return;
        }
        badgeLimitsList.innerHTML = '';
        badgeLimitsBox.classList.add('d-none');
        badgeLimitsEmpty.classList.add('d-none');
    }

    bookingSelect.addEventListener('change', function () {
        const bookingId = this.value;

        if (!bookingId) {
            clearBadgeLimits();
            return;
        }

        const url = bookingLimitsUrlTemplate.replace(':bookingId', bookingId);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (!badgeLimitsBox || !badgeLimitsList || !badgeLimitsEmpty) {
                    return;
                }

                if (!data.success || !data.data || data.data.length === 0) {
                    badgeLimitsList.innerHTML = '';
                    badgeLimitsBox.classList.add('d-none');
                    badgeLimitsEmpty.classList.remove('d-none');
                    return;
                }

                badgeLimitsList.innerHTML = '';

                // Group by booth size if size-specific data
                const groupedBySize = {};
                data.data.forEach(item => {
                    const sizeKey = item.booth_size_id ? `size_${item.booth_size_id}` : 'no_size';
                    if (!groupedBySize[sizeKey]) {
                        groupedBySize[sizeKey] = {
                            sizeLabel: item.booth_size_label || 'General',
                            items: []
                        };
                    }
                    groupedBySize[sizeKey].items.push(item);
                });

                // Display grouped by size
                Object.keys(groupedBySize).forEach(sizeKey => {
                    const group = groupedBySize[sizeKey];
                    const sizeHeader = document.createElement('li');
                    sizeHeader.innerHTML = `<strong>${group.sizeLabel}:</strong>`;
                    sizeHeader.style.marginTop = sizeKey !== Object.keys(groupedBySize)[0] ? '10px' : '0';
                    badgeLimitsList.appendChild(sizeHeader);
                    
                    group.items.forEach(item => {
                        const li = document.createElement('li');
                        li.style.marginLeft = '20px';
                        const price = item.price || 0;
                        const priceText = price > 0
                            ? ` | Additional Price: ₹${price.toFixed(2)} each beyond free quota`
                            : '';
                        li.textContent = `${item.badge_type}: Allowed ${item.allowed}, Used ${item.used}, Remaining ${item.remaining}${priceText}`;
                        badgeLimitsList.appendChild(li);
                    });
                });

                badgeLimitsEmpty.classList.add('d-none');
                badgeLimitsBox.classList.remove('d-none');
            })
            .catch(() => {
                clearBadgeLimits();
            });
    });
});

// Radio button selection
document.querySelectorAll('.radio-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.radio-option').forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input[type="radio"]').checked = true;
    });
});

// Tab switching
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
@endpush
@endsection
