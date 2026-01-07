@extends('layouts.exhibitor')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

@push('styles')
<style>
    .booking-details-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .section-header {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .detail-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .detail-item {
        margin-bottom: 15px;
    }
    
    .detail-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-weight: 500;
        color: #1e293b;
        font-size: 1rem;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .status-confirmed {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .booth-icon {
        font-size: 2rem;
        color: #6366f1;
        margin-right: 15px;
    }
    
    .booth-features {
        list-style: none;
        padding: 0;
        margin-top: 15px;
    }
    
    .booth-features li {
        padding: 8px 0;
        color: #64748b;
        font-size: 0.95rem;
        position: relative;
        padding-left: 25px;
    }
    
    .booth-features li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #10b981;
        font-weight: bold;
    }
    
    .payment-history-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .payment-history-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .payment-history-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
    }
    
    .payment-history-table tr:last-child td {
        border-bottom: none;
    }
    
    .status-paid {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .document-status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .document-status-item:last-child {
        border-bottom: none;
    }
    
    .document-name {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        color: #1e293b;
    }
    
    .document-status {
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-uploaded {
        color: #1e40af;
    }
    
    .status-pending-doc {
        color: #f59e0b;
    }
    
    .status-rejected {
        color: #991b1b;
    }
    
    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        position: sticky;
        top: 20px;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .summary-item:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        color: #64748b;
        font-size: 0.95rem;
    }
    
    .summary-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .summary-total {
        font-size: 1.2rem;
        font-weight: 700;
    }
    
    .summary-balance {
        color: #ef4444;
        font-weight: 700;
    }
    
    .due-date-note {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 10px;
    }
    
    .action-buttons {
        margin-top: 25px;
    }
    
    .btn-action {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        font-weight: 500;
        margin-bottom: 10px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-cancel {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .btn-cancel:hover {
        background: #fecaca;
    }
    
    .btn-modify {
        background: #6366f1;
        color: white;
    }
    
    .btn-modify:hover {
        background: #4f46e5;
    }
    
    .btn-download {
        background: #f3f4f6;
        color: #1e293b;
    }
    
    .btn-download:hover {
        background: #e5e7eb;
    }
    
    .contact-emails, .contact-numbers {
        margin-top: 10px;
    }
    
    .contact-item {
        padding: 8px 0;
        color: #64748b;
        font-size: 0.95rem;
    }
</style>
@endpush

@section('content')
<div class="booking-details-container">
    <h2 class="section-header mb-4">Booking Details</h2>
    
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Booking Details -->
            <div class="detail-section">
                <h5 class="section-header">Booking Details</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Booking ID</div>
                            <div class="detail-value">{{ $booking->booking_number }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Event Name</div>
                            <div class="detail-value">{{ $booking->exhibition->name }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value">
                                {{ $booking->exhibition->start_date->format('F d') }} - {{ $booking->exhibition->end_date->format('d, Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Time</div>
                            <div class="detail-value">
                                {{ $booking->exhibition->start_time ?? '9:00 AM' }} - {{ $booking->exhibition->end_time ?? '5:00 PM' }} Daily
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Duration</div>
                            <div class="detail-value">
                                {{ $booking->exhibition->start_date->diffInDays($booking->exhibition->end_date) + 1 }} Days
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div>
                                <span class="status-badge status-confirmed">Confirmed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Primary Contact Person -->
            <div class="detail-section">
                <h5 class="section-header">Primary Contact Person</h5>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="detail-item">
                            <div class="detail-label">Name</div>
                            <div class="detail-value">{{ auth()->user()->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-item">
                            <div class="detail-label">Email</div>
                            <div class="detail-value">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-item">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value">{{ auth()->user()->phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                
                @if($booking->contact_emails && count($booking->contact_emails) > 0)
                <div class="contact-emails">
                    <div class="detail-label">Additional Emails (up to 5)</div>
                    @foreach($booking->contact_emails as $email)
                    <div class="contact-item">{{ $email }}</div>
                    @endforeach
                </div>
                @endif
                
                @if($booking->contact_numbers && count($booking->contact_numbers) > 0)
                <div class="contact-numbers">
                    <div class="detail-label">Additional Phone Numbers (up to 5)</div>
                    @foreach($booking->contact_numbers as $number)
                    <div class="contact-item">{{ $number }}</div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Company Assets -->
            <div class="detail-section">
                <h5 class="section-header">Company Assets</h5>

                <!-- Company Logo Section -->
                <div class="detail-item mb-4">
                    <div class="detail-label">Company Logo</div>
                    <div class="detail-value">
                        @if($booking->logo)
                            <img src="{{ asset('storage/' . $booking->logo) }}" alt="Company Logo" style="max-width: 220px; max-height: 120px; object-fit: contain; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; background: #f8fafc;">
                        @else
                            <img src="https://via.placeholder.com/220x120/e2e8f0/64748b?text=No+Logo" alt="Default Logo" style="max-width: 220px; max-height: 120px; object-fit: contain; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; background: #f8fafc;">
                        @endif
                    </div>
                </div>

                @php
                    $brochures = $booking->documents->where('type', 'Promotional Brochure');
                @endphp

                <!-- Existing brochures with one-click remove -->
                <div class="detail-item">
                    <div class="detail-label">Brochures</div>
                    <div class="detail-value">
                        @if($brochures->count() > 0)
                            @foreach($brochures as $brochure)
                            <div class="d-flex align-items-center justify-content-between contact-item">
                                <div>
                                    <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>
                                    <a href="{{ asset('storage/' . $brochure->file_path) }}" target="_blank" class="text-decoration-none">
                                        {{ $brochure->name ?? 'Brochure' }}
                                    </a>
                                    @if($brochure->file_size)
                                        <small class="text-muted ms-2">({{ number_format($brochure->file_size / 1024, 0) }} KB)</small>
                                    @endif
                                </div>
                                <form action="{{ route('bookings.update', $booking->id) }}" method="POST" class="ms-3">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="remove_brochure_ids[]" value="{{ $brochure->id }}">
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Remove this brochure?');"
                                            title="Remove brochure">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        @else
                            <div class="contact-item text-muted">
                                <i class="bi bi-file-earmark-pdf me-2"></i>
                                No brochure uploaded
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Edit Assets (Logo + Brochures) -->
                <div class="mt-3">
                    <h6 class="detail-label mb-2">Update Company Assets</h6>
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('bookings.update', $booking->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="detail-label d-block">Change Company Logo</label>
                                <input type="file"
                                       name="logo"
                                       class="form-control"
                                       accept="image/png,image/jpeg,image/jpg">
                                <small class="text-muted d-block mt-1">PNG, JPG, max 5MB</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="detail-label d-block">Add Promotional Brochures</label>
                                <input type="file"
                                       name="brochures[]"
                                       class="form-control"
                                       accept="application/pdf"
                                       multiple>
                                <small class="text-muted d-block mt-1">
                                    PDF only, max 5MB each, up to 5 brochures total per booking.
                                </small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-upload me-1"></i>Save Assets
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Booth Details -->
            <div class="detail-section">
                <h5 class="section-header">
                    <i class="bi bi-grid-3x3-gap booth-icon"></i>Booth Details
                </h5>
                
                @php
                    // Get all booths from selected_booth_ids (for multiple booth bookings)
                    $boothEntries = collect($booking->selected_booth_ids ?? []);
                    if ($boothEntries->isEmpty() && $booking->booth_id) {
                        // Fallback to primary booth if no selected_booth_ids
                        $boothEntries = collect([[
                            'id' => $booking->booth_id,
                            'name' => $booking->booth->name ?? 'N/A',
                            'category' => $booking->booth->category ?? 'N/A',
                            'booth_type' => $booking->booth->booth_type ?? 'N/A',
                            'size_sqft' => $booking->booth->size_sqft ?? 0,
                            'price' => $booking->booth->price ?? 0,
                            'type' => $booking->booth->booth_type ?? 'Raw',
                            'sides' => $booking->booth->sides_open ?? 1,
                        ]]);
                    }
                    
                    // Load booth models for additional details if needed
                    $boothIds = $boothEntries->map(function($entry) {
                        return is_array($entry) ? ($entry['id'] ?? null) : $entry;
                    })->filter()->values();
                    $booths = \App\Models\Booth::whereIn('id', $boothIds)->get()->keyBy('id');
                    
                    // Build display array with all booth details
                    $boothDisplay = $boothEntries->map(function($entry) use ($booths) {
                        $isArray = is_array($entry);
                        $id = $isArray ? ($entry['id'] ?? null) : $entry;
                        $model = $id ? ($booths[$id] ?? null) : null;
                        return [
                            'id' => $id,
                            'name' => $isArray ? ($entry['name'] ?? $model?->name ?? 'N/A') : ($model?->name ?? 'N/A'),
                            'category' => $isArray ? ($entry['category'] ?? $model?->category ?? 'N/A') : ($model?->category ?? 'N/A'),
                            'type' => $isArray ? ($entry['type'] ?? $entry['booth_type'] ?? $model?->booth_type ?? 'N/A') : ($model?->booth_type ?? 'N/A'),
                            'sides' => $isArray ? ($entry['sides'] ?? $model?->sides_open ?? 1) : ($model?->sides_open ?? 1),
                            'size_sqft' => $isArray ? ($entry['size_sqft'] ?? $model?->size_sqft ?? 0) : ($model?->size_sqft ?? 0),
                            'price' => $isArray ? ($entry['price'] ?? $model?->price ?? 0) : ($model?->price ?? 0),
                        ];
                    })->filter(fn($b) => $b['id'] && $b['name'] !== 'N/A');
                @endphp
                
                @if($boothDisplay->count() > 1)
                    <!-- Multiple Booths Table -->
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Booth Number</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Sides Open</th>
                                    <th>Size (sq ft)</th>
                                    <th class="text-end">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boothDisplay as $booth)
                                <tr>
                                    <td><strong>{{ $booth['name'] }}</strong></td>
                                    <td>{{ $booth['category'] }}</td>
                                    <td>{{ $booth['type'] }}</td>
                                    <td>{{ $booth['sides'] }} Side{{ $booth['sides'] > 1 ? 's' : '' }}</td>
                                    <td>{{ number_format($booth['size_sqft'], 0) }}</td>
                                    <td class="text-end"><strong>₹{{ number_format($booth['price'], 2) }}</strong></td>
                                </tr>
                                @endforeach
                                <tr class="table-info">
                                    <td colspan="5" class="text-end"><strong>Total Booth Price:</strong></td>
                                    <td class="text-end"><strong>₹{{ number_format($boothDisplay->sum('price'), 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Single Booth Display -->
                    @php $singleBooth = $boothDisplay->first(); @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">Booth Number</div>
                                <div class="detail-value">{{ $singleBooth['name'] ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Booth Category</div>
                                <div class="detail-value">{{ $singleBooth['category'] ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Booth Type</div>
                                <div class="detail-value">{{ $singleBooth['type'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">Sides Open</div>
                                <div class="detail-value">{{ $singleBooth['sides'] ?? 1 }} Side{{ ($singleBooth['sides'] ?? 1) > 1 ? 's' : '' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Size</div>
                                <div class="detail-value">{{ number_format($singleBooth['size_sqft'] ?? 0, 0) }} sq ft</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Price</div>
                                <div class="detail-value"><strong>₹{{ number_format($singleBooth['price'] ?? 0, 2) }}</strong></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Location</div>
                                <div class="detail-value">
                                    {{ $booking->exhibition->venue }}, {{ $booking->exhibition->city }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <ul class="booth-features">
                    <li>High visibility location</li>
                    <li>Larger Footprint (double by)</li>
                    <li>Dedicated Power Outlet</li>
                    <li>Basic Furniture Package</li>
                    <li>High-speed Internet Access</li>
                </ul>
                
                @if($booking->status !== 'cancelled' && $booking->booth)
                <div class="mt-3">
                    <a href="{{ route('bookings.replace-booth', $booking->id) }}" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat me-2"></i>Replace Booth
                    </a>
                    <small class="text-muted d-block mt-2">
                        @if($boothDisplay->count() > 1)
                            You can replace booths with other booths of the same category and size.
                        @else
                            You can replace this booth with another booth of the same category and size ({{ $singleBooth['size_sqft'] ?? 0 }} sq ft).
                        @endif
                    </small>
                </div>
                @endif
            </div>
            
            <!-- Additional Services Request -->
            @if($booking->status === 'confirmed' && $booking->approval_status === 'approved')
            <div class="detail-section">
                <h5 class="section-header">
                    <i class="bi bi-plus-circle booth-icon"></i>Request Additional Services
                </h5>
                
                @php
                    // Get exhibition additional services
                    $addonServices = $booking->exhibition->addonServices ?? collect();
                    $serviceNames = $addonServices->pluck('item_name')->filter()->unique()->values();
                    $servicesByName = \App\Models\Service::whereIn('name', $serviceNames)->get()->keyBy('name');
                    
                    $activeServices = $addonServices->map(function ($addon) use ($servicesByName) {
                        $serviceModel = $servicesByName->get($addon->item_name);
                        if (!$serviceModel) {
                            return null;
                        }
                        
                        return (object) [
                            'id' => $serviceModel->id,
                            'name' => $serviceModel->name,
                            'description' => $serviceModel->description,
                            'image' => $serviceModel->image,
                            'price' => $addon->price_per_quantity,
                            'category' => $serviceModel->category ?? null,
                        ];
                    })->filter();
                    
                    // Get existing service IDs that are already in booking
                    $existingServiceIds = $booking->bookingServices->pluck('service_id')->toArray();
                    
                    // Get pending request service IDs
                    $pendingRequestServiceIds = $booking->additionalServiceRequests()
                        ->where('status', 'pending')
                        ->pluck('service_id')
                        ->toArray();
                @endphp
                
                @if($activeServices->count() > 0)
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <div class="row g-3">
                        @foreach($activeServices as $service)
                            @php
                                $isAlreadyBooked = in_array($service->id, $existingServiceIds);
                                $hasPendingRequest = in_array($service->id, $pendingRequestServiceIds);
                                $existingService = $booking->bookingServices->firstWhere('service_id', $service->id);
                                $existingQty = $existingService?->quantity ?? 0;
                            @endphp
                            
                            <div class="col-md-6">
                                <div class="card" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1" style="font-weight: 600; color: #1e293b;">{{ $service->name }}</h6>
                                            @if($service->description)
                                                <small class="text-muted" style="font-size: 0.85rem;">{{ $service->description }}</small>
                                            @endif
                                        </div>
                                        @if($service->image)
                                            <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" style="max-width: 60px; max-height: 60px; border-radius: 4px;">
                                        @endif
                                    </div>
                                    
                                    <div class="mb-2">
                                        <strong style="color: #6366f1;">₹{{ number_format($service->price, 2) }}</strong>
                                        <small class="text-muted">per quantity</small>
                                    </div>
                                    
                                    @if($hasPendingRequest)
                                        <div class="alert alert-warning mb-0" style="padding: 8px; font-size: 0.85rem;">
                                            <i class="bi bi-clock me-1"></i>Request pending approval
                                        </div>
                                    @else
                                        @if($isAlreadyBooked && $existingQty > 0)
                                            <div class="alert alert-info mb-2" style="padding: 8px; font-size: 0.8rem;">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Already included in booking (current quantity: {{ $existingQty }}). You can request additional quantity below.
                                            </div>
                                        @endif
                                        <form action="{{ route('additional-service-requests.store', $booking->id) }}" method="POST" class="d-flex gap-2 align-items-end">
                                            @csrf
                                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                                            <div style="flex: 1;">
                                                <label class="detail-label" style="font-size: 0.8rem;">Additional Quantity</label>
                                                <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm" required>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-plus-circle me-1"></i>Request
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No additional services available for this exhibition.</p>
                @endif
                
                <!-- Show existing additional service requests -->
                @php
                    $additionalRequests = $booking->additionalServiceRequests()->with('service')->latest()->get();
                @endphp
                
                @if($additionalRequests->count() > 0)
                    <div class="mt-4">
                        <h6 class="section-header" style="font-size: 1rem; margin-bottom: 15px;">Additional Service Requests</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" style="font-size: 0.9rem;">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($additionalRequests as $req)
                                        <tr>
                                            <td>{{ $req->service->name }}</td>
                                            <td>{{ $req->quantity }}</td>
                                            <td>₹{{ number_format($req->unit_price, 2) }}</td>
                                            <td>₹{{ number_format($req->total_price, 2) }}</td>
                                            <td>
                                                @if($req->status === 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($req->status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                    @if($req->rejection_reason)
                                                        <br><small class="text-danger">{{ $req->rejection_reason }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
            @endif
            
            <!-- Payment History -->
            <div class="detail-section">
                <h5 class="section-header">Payment History</h5>
                
                <table class="payment-history-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Platform</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</td>
                            <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                            <td>₹{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                            <td>
                                @php
                                    $displayStatus = $payment->status;
                                    $statusClass = 'status-pending';
                                    
                                    if ($payment->status === 'completed') {
                                        $displayStatus = 'completed';
                                        $statusClass = 'status-paid';
                                    } elseif ($payment->status === 'pending' && $payment->payment_proof_file && $payment->approval_status === 'pending') {
                                        $displayStatus = 'waiting for approval';
                                        $statusClass = 'status-pending';
                                    } else {
                                        $displayStatus = 'pending';
                                        $statusClass = 'status-pending';
                                    }
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ ucfirst($displayStatus) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No payments yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Possession Letter Section -->
            @if($booking->possession_letter_issued)
            <div class="detail-section" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-left: 4px solid #059669;">
                <h5 class="section-header" style="color: #065f46;">
                    <i class="bi bi-file-earmark-check me-2"></i>Possession Letter
                </h5>
                
                <div class="alert alert-success" style="background: rgba(255, 255, 255, 0.8); border: none;">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Your possession letter has been generated!</strong>
                    <p class="mb-0 mt-2" style="font-size: 0.9rem;">
                        The possession letter has been sent to your email address. You can also download it using the button below.
                    </p>
                </div>
                
                <div class="action-buttons">
                    <a href="{{ route('bookings.download-possession-letter', $booking->id) }}" class="btn-action btn-download" style="background: #059669; color: white;">
                        <i class="bi bi-download me-2"></i>Download Possession Letter (PDF)
                    </a>
                </div>
            </div>
            @elseif($booking->isFullyPaid() && $booking->areAllPaymentsCompleted() && $booking->approval_status === 'approved' && $booking->status === 'confirmed')
            <div class="detail-section" style="background: #fef3c7; border-left: 4px solid #f59e0b;">
                <h5 class="section-header" style="color: #92400e;">
                    <i class="bi bi-file-earmark-text me-2"></i>Possession Letter
                </h5>
                
                <div class="alert alert-info" style="background: rgba(255, 255, 255, 0.8); border: none;">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>All payments completed!</strong>
                    <p class="mb-0 mt-2" style="font-size: 0.9rem;">
                        Your possession letter will be generated by the admin shortly. You will receive an email notification once it's ready.
                    </p>
                </div>
            </div>
            @endif
            
            <!-- Document Status -->
            <div class="detail-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="section-header mb-0">Document Status</h5>
                    @if($booking->exhibition && $booking->exhibition->requiredDocuments && $booking->exhibition->requiredDocuments->count() > 0)
                        <a href="{{ route('bookings.required-documents', $booking->id) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-file-earmark-text me-1"></i>Manage Documents
                        </a>
                    @endif
                </div>
                
                @if($booking->exhibition && $booking->exhibition->requiredDocuments && $booking->exhibition->requiredDocuments->count() > 0)
                    @php
                        $requiredDocsList = $booking->exhibition->requiredDocuments;
                    @endphp
                    
                    @foreach($requiredDocsList as $requiredDoc)
                        @php
                            // Get the latest document for this required document (most recent upload)
                            $document = $booking->documents
                                ->where('required_document_id', $requiredDoc->id)
                                ->sortByDesc('created_at')
                                ->first();
                            $status = $document ? $document->status : 'pending';
                        @endphp
                        <div class="document-status-item">
                            <div class="document-name">
                                <i class="bi bi-circle-fill" style="color: {{ $status === 'approved' ? '#10b981' : ($status === 'rejected' ? '#ef4444' : '#f59e0b') }}; font-size: 0.5rem;"></i>
                                {{ $requiredDoc->document_name }}
                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                    Type: {{ $requiredDoc->document_type === 'both' ? 'Image or PDF' : strtoupper($requiredDoc->document_type) }}
                                </small>
                            </div>
                            <div class="document-status {{ $status === 'approved' ? 'status-uploaded' : ($status === 'rejected' ? 'status-rejected' : 'status-pending-doc') }}">
                                @if($status === 'approved')
                                    Approved
                                @elseif($status === 'rejected')
                                    Rejected
                                @elseif($status === 'pending' && $document)
                                    Pending Verification
                                @else
                                    Not Uploaded
                                @endif
                            </div>
                        </div>
                        @if($document && $status === 'rejected' && $document->rejection_reason)
                            <div class="text-danger small ms-3 mb-2" style="font-size: 0.75rem;">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Reason: {{ $document->rejection_reason }}
                            </div>
                        @endif
                    @endforeach
                @else
                    @php
                        // Fallback to old document types if no required documents
                        $requiredDocs = [
                            'Exhibitor Agreement' => $booking->documents->where('type', 'Exhibitor Agreement')->first(),
                            'Company Registration' => $booking->documents->where('type', 'Company Registration')->first(),
                            'Product Catalog' => $booking->documents->where('type', 'Product Catalog')->first(),
                            'Insurance Certificate' => $booking->documents->where('type', 'Insurance Certificate')->first(),
                        ];
                    @endphp
                    
                    @foreach($requiredDocs as $docName => $document)
                    <div class="document-status-item">
                        <div class="document-name">
                            <i class="bi bi-circle-fill" style="color: {{ $document && $document->status === 'approved' ? '#1e40af' : '#f59e0b' }}; font-size: 0.5rem;"></i>
                            {{ $docName }}
                        </div>
                        <div class="document-status {{ $document && $document->status === 'approved' ? 'status-uploaded' : 'status-pending-doc' }}">
                            {{ $document && $document->status === 'approved' ? 'Uploaded' : 'Pending' }}
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="summary-card">
                <h5 class="section-header">Booking Summary</h5>
                
                @php
                    $servicesTotal = $booking->bookingServices->sum(function($bs) {
                        return $bs->quantity * $bs->unit_price;
                    });
                    
                    // Calculate booth total from selected_booth_ids (for multiple booths)
                    $boothEntries = collect($booking->selected_booth_ids ?? []);
                    if ($boothEntries->isEmpty() && $booking->booth_id) {
                        // Fallback to primary booth if no selected_booth_ids
                        $boothEntries = collect([[
                            'id' => $booking->booth_id,
                            'price' => $booking->booth->price ?? 0,
                        ]]);
                    }
                    $boothTotal = $boothEntries->sum(function($entry) {
                        if (is_array($entry)) {
                            return (float) ($entry['price'] ?? 0);
                        }
                        return 0;
                    });
                    // If still 0, fallback to primary booth price
                    if ($boothTotal == 0 && $booking->booth) {
                        $boothTotal = $booking->booth->price ?? 0;
                    }
                    
                    // Calculate extras total
                    $extrasTotal = 0;
                    $extrasRaw = $booking->included_item_extras ?? [];
                    if (is_array($extrasRaw)) {
                        foreach ($extrasRaw as $extra) {
                            $lineTotal = $extra['total_price'] ?? (
                                (isset($extra['quantity'], $extra['unit_price']))
                                    ? ((float) $extra['quantity'] * (float) $extra['unit_price'])
                                    : 0
                            );
                            $extrasTotal += $lineTotal;
                        }
                    }
                    
                    // Calculate base total before discount (booth + services + extras)
                    $baseTotal = $boothTotal + $servicesTotal + $extrasTotal;
                    
                    // Calculate discount from discount_percent (applied to base total)
                    $discount = 0;
                    if ($booking->discount_percent > 0 && $baseTotal > 0) {
                        $discount = ($baseTotal * $booking->discount_percent) / 100;
                    }
                    
                    $taxes = ($booking->total_amount - $servicesTotal) * 0.1; // 10% tax
                    $totalAmount = $booking->total_amount;
                    $paidAmount = $booking->paid_amount;
                    $balanceDue = $totalAmount - $paidAmount;
                @endphp
                
                <div class="summary-item">
                    <span class="summary-label">Booth/Fee</span>
                    <span class="summary-value">₹{{ number_format($boothTotal, 2) }}</span>
                </div>
                @if($servicesTotal > 0)
                <div class="summary-item">
                    <span class="summary-label">Service Charges</span>
                    <span class="summary-value">₹{{ number_format($servicesTotal, 2) }}</span>
                </div>
                @endif
                <div class="summary-item">
                    <span class="summary-label">Taxes</span>
                    <span class="summary-value">₹{{ number_format($taxes, 2) }}</span>
                </div>
                @if($discount > 0)
                <div class="summary-item">
                    <span class="summary-label">Special Discount ({{ number_format($booking->discount_percent, 2) }}%)</span>
                    <span class="summary-value" style="color: #10b981;">-₹{{ number_format($discount, 2) }}</span>
                </div>
                @endif
                <div class="summary-item">
                    <span class="summary-label summary-total">Total Amount</span>
                    <span class="summary-value summary-total">₹{{ number_format($totalAmount, 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Amount Paid</span>
                    <span class="summary-value">₹{{ number_format($paidAmount, 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Balance Due</span>
                    <span class="summary-value summary-balance">₹{{ number_format($balanceDue, 2) }}</span>
                </div>
                
                <div class="due-date-note">
                    Due by {{ $booking->exhibition->start_date->subDays(30)->format('F d, Y') }}
                </div>
                
                <div class="action-buttons">
                    @if($booking->status !== 'cancelled')
                    <a href="{{ route('bookings.cancel.show', $booking->id) }}" class="btn-action btn-cancel">
                        Cancel Booking
                    </a>
                    <a href="{{ route('bookings.edit', $booking->id) }}" class="btn-action btn-modify">
                        Request Modification
                    </a>
                    @endif
                    <button class="btn-action btn-download" onclick="window.print()">
                        Download Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
