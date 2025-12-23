@extends('layouts.frontend')

@section('title', 'Booking Details')

@push('styles')
<style>
    .stepper {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .step-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #475569;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .step-pill.active {
        background: #6366f1;
        color: #fff;
        box-shadow: 0 8px 20px rgba(99,102,241,0.25);
    }
    .step-pill .badge {
        background: rgba(255,255,255,0.2);
        color: inherit;
    }
    .summary-card {
        background: #fff;
        border-radius: 12px;
        padding: 18px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 16px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-label { color: #64748b; }
    .summary-value { font-weight: 600; color: #0f172a; }

    .exhibition-hero {
        background: linear-gradient(120deg, #4f46e5, #6366f1, #8b5cf6);
        border-radius: 16px;
        padding: 22px 24px;
        color: #fff;
        box-shadow: 0 12px 30px rgba(79,70,229,0.18);
        margin-bottom: 20px;
    }
    .exhibition-hero .metric-pill {
        background: rgba(255,255,255,0.12);
        color: #fff;
        padding: 8px 12px;
        border-radius: 999px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .exhibition-hero .metric-pill i {
        font-size: 1.1rem;
    }
    .summary-total-chip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 14px;
    }
    .soft-alert {
        background: #eef2ff;
        border: 1px solid #e0e7ff;
        color: #312e81;
        border-radius: 12px;
        padding: 12px 14px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 14px;
    }
    .soft-alert i {
        font-size: 1.1rem;
        margin-top: 2px;
    }
    .pill-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        background: #f1f5f9;
        border-radius: 10px;
        font-weight: 600;
        color: #334155;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="stepper">
        <span class="step-pill"><span class="badge bg-light text-dark">1</span> Select Booth</span>
        <i class="bi bi-arrow-right text-secondary"></i>
        <span class="step-pill active"><span class="badge bg-light text-dark">2</span> Booking Details</span>
        <i class="bi bi-arrow-right text-secondary"></i>
        <span class="step-pill"><span class="badge bg-light text-dark">3</span> Payment</span>
    </div>

    <div class="exhibition-hero">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
            <div>
                <div class="text-uppercase fw-semibold small text-white-50 mb-1">Booking Details</div>
                <h3 class="mb-1">{{ $exhibition->name }}</h3>
                <p class="mb-0 text-white-75">Review your company info, confirm selections, and continue to payment.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="metric-pill"><i class="bi bi-grid-3x3-gap"></i>{{ count($booths) }} booth{{ count($booths) > 1 ? 's' : '' }}</span>
                <span class="metric-pill"><i class="bi bi-cash-coin"></i>₹{{ number_format($totalAmount ?? 0, 2) }}</span>
                @if($exhibition->exhibition_manual_pdf)
                    <a href="{{ asset('storage/' . $exhibition->exhibition_manual_pdf) }}" target="_blank" class="metric-pill text-decoration-none">
                        <i class="bi bi-file-earmark-arrow-down"></i>Manual
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Booking Details</h5>
                    <small class="text-muted">Provide company and contact information.</small>
                </div>
                <div class="card-body">
                    <div class="soft-alert">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <strong>Tip:</strong> Accurate details speed up approval. You can update uploads later from your dashboard if needed.
                        </div>
                    </div>
                    <form id="bookingForm" action="{{ route('bookings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="exhibition_id" value="{{ $exhibition->id }}">
                        @foreach($boothIds as $boothId)
                            <input type="hidden" name="booth_ids[]" value="{{ $boothId }}">
                        @endforeach
                        @if($merge && count($boothIds) > 1)
                            <input type="hidden" name="merge_booths" value="1">
                        @endif
                        @if(!empty($boothSelections))
                            @foreach($boothSelections as $selection)
                                <input type="hidden" name="booth_selections[{{ $selection['id'] }}][type]" value="{{ $selection['type'] }}">
                                <input type="hidden" name="booth_selections[{{ $selection['id'] }}][sides]" value="{{ $selection['sides'] }}">
                            @endforeach
                        @endif
                        @if(!empty($selectedServices ?? []))
                            @foreach($selectedServices as $index => $service)
                                <input type="hidden" name="services[{{ $index }}][service_id]" value="{{ $service['id'] }}">
                                <input type="hidden" name="services[{{ $index }}][quantity]" value="{{ $service['quantity'] }}">
                                <input type="hidden" name="services[{{ $index }}][unit_price]" value="{{ $service['unit_price'] }}">
                                <input type="hidden" name="services[{{ $index }}][name]" value="{{ $service['name'] }}">
                            @endforeach
                        @endif
                        @if(!empty($includedExtras ?? []))
                            @foreach($includedExtras as $index => $extra)
                                <input type="hidden" name="included_item_extras[{{ $index }}][item_id]" value="{{ $extra['item_id'] }}">
                                <input type="hidden" name="included_item_extras[{{ $index }}][quantity]" value="{{ $extra['quantity'] }}">
                                <input type="hidden" name="included_item_extras[{{ $index }}][unit_price]" value="{{ $extra['unit_price'] }}">
                            @endforeach
                        @endif

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>Your booking will be submitted and payment is required on the next step.
                        </div>

                        <h6 class="mt-3 mb-2">Company Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name *</label>
                                <input type="text" class="form-control" name="company_name" value="{{ auth()->user()->company_name ?? '' }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Website</label>
                                <input type="url" class="form-control" name="company_website" value="{{ auth()->user()->website ?? '' }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address *</label>
                                <input type="text" class="form-control" name="company_address" value="{{ auth()->user()->address ?? '' }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" class="form-control" name="company_city" value="{{ auth()->user()->city ?? '' }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">State *</label>
                                <input type="text" class="form-control" name="company_state" value="{{ auth()->user()->state ?? '' }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Country *</label>
                                <input type="text" class="form-control" name="company_country" value="{{ auth()->user()->country ?? '' }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Zip Code *</label>
                                <input type="text" class="form-control" name="company_pincode" value="{{ auth()->user()->pincode ?? '' }}" required>
                            </div>
                        </div>

                        <h6 class="mt-3 mb-2">Contact Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Primary Email *</label>
                                <input type="email" class="form-control" name="contact_emails[]" value="{{ auth()->user()->email ?? '' }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Primary Phone *</label>
                                <input type="tel" class="form-control" name="contact_numbers[]" value="{{ auth()->user()->phone ?? '' }}" required>
                            </div>
                        </div>

                        <div id="additionalContacts"></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="addContactBtn">
                            <i class="bi bi-plus-circle me-1"></i>Add Additional Contact (Max 5)
                        </button>

                        <h6 class="mt-3 mb-2">Uploads</h6>
                        <div class="mb-3">
                            <label class="form-label">Company Logo</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                            <small class="text-muted">PNG, JPG, max 5MB</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Promotional Brochures (Max 5)</label>
                            <input type="file" class="form-control" name="brochures[]" accept="application/pdf" multiple>
                            <small class="text-muted">PDF files, max 5MB each, up to 5 files</small>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the Terms & Conditions *
                            </label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('bookings.book', $exhibition->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Booth Selection
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Continue to Payment <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="summary-card">
                <h6 class="mb-3">Summary</h6>
                <div class="summary-total-chip">
                    <div class="text-muted small mb-1">Current Total</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-bold" style="font-size: 1.4rem; color:#4f46e5;">₹{{ number_format($totalAmount ?? ($boothTotal + $servicesTotal + $extrasTotal), 2) }}</span>
                        <span class="badge bg-light text-dark">Step 2 of 3</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="pill-chip"><i class="bi bi-grid-3x3-gap"></i>{{ count($booths) }} booth{{ count($booths) > 1 ? 's' : '' }}</span>
                        <span class="pill-chip"><i class="bi bi-clipboard-check"></i>{{ !empty($selectedServices) ? 'Services added' : 'No extra services' }}</span>
                        <span class="pill-chip"><i class="bi bi-shield-lock"></i>Secure review</span>
                    </div>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Exhibition</span>
                    <span class="summary-value">{{ $exhibition->name }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Selected Booths</span>
                    <span class="summary-value">
                        @if(!empty($boothSelections))
                            @foreach($boothSelections as $selection)
                                <div style="font-size: 0.95rem; margin-bottom:4px;">
                                    {{ $selection['name'] }} — {{ $selection['type'] }} / {{ $selection['sides'] }} sides
                                    <span style="color:#6366f1; font-weight:600;">₹{{ number_format($selection['price'], 2) }}</span>
                                </div>
                            @endforeach
                        @else
                            {{ implode(', ', $booths->pluck('name')->toArray()) }}
                        @endif
                    </span>
                </div>
                @php
                    $boothTotal = $boothTotal ?? (!empty($boothSelections)
                        ? collect($boothSelections)->sum('price')
                        : $booths->sum('price'));
                    $extrasTotal = $extrasTotal ?? 0;
                    $servicesTotal = $servicesTotal ?? 0;
                    $grandTotal = $totalAmount ?? ($boothTotal + $servicesTotal + $extrasTotal);
                    // Normalize for view
                    $selectedServices = $selectedServices ?? [];
                @endphp
                @if(!empty($selectedServices))
                <div class="summary-row">
                    <span class="summary-label">Additional Services</span>
                    <span class="summary-value">
                        @foreach($selectedServices as $service)
                            <div style="font-size: 0.85rem; margin-bottom: 3px;">
                                {{ $service['name'] ?: 'Additional service' }} (x{{ $service['quantity'] }}) - ₹{{ number_format($service['total_price'], 2) }}
                            </div>
                        @endforeach
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Services Total</span>
                    <span class="summary-value">₹{{ number_format($servicesTotal, 2) }}</span>
                </div>
                @endif
                @if(!empty($includedExtras ?? []))
                <div class="summary-row">
                    <span class="summary-label">Included Item Extras</span>
                    <span class="summary-value">
                        @foreach($includedExtras as $extra)
                            <div style="font-size: 0.85rem; margin-bottom: 3px;">
                                {{ $extra['name'] }} (x{{ $extra['quantity'] }}) - ₹{{ number_format($extra['total_price'], 2) }}
                            </div>
                        @endforeach
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Extras Total</span>
                    <span class="summary-value">₹{{ number_format($extrasTotal, 2) }}</span>
                </div>
                @endif
                <div class="summary-row" style="border-top: 2px solid #e2e8f0; padding-top: 10px; margin-top: 10px;">
                    <span class="summary-label" style="font-weight: 700;">Total Amount</span>
                    <span class="summary-value" style="font-weight: 700; color: #6366f1; font-size: 1.2rem;">₹{{ number_format($grandTotal, 2) }}</span>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    <h6 class="mb-0">Exhibition Manual</h6>
                </div>
                <div class="card-body">
                    @if($exhibition->exhibition_manual_pdf)
                        <p class="text-muted small mb-3">Download and review the exhibition manual before submitting your booking.</p>
                        <a href="{{ asset('storage/' . $exhibition->exhibition_manual_pdf) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i>Download Manual
                        </a>
                    @else
                        <p class="text-muted mb-0">Exhibition manual will be uploaded soon.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Service Image Modal -->
        <div class="modal fade" id="serviceImageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="serviceModalTitle">Service Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="serviceModalImage" src="" alt="" style="max-width: 100%; max-height: 70vh; border-radius: 8px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let contactCount = 0;
document.getElementById('addContactBtn').addEventListener('click', function() {
    if (contactCount >= 4) {
        alert('Maximum 5 contacts allowed');
        return;
    }
    const container = document.getElementById('additionalContacts');
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

function openServiceImage(imageUrl, serviceName) {
    document.getElementById('serviceModalTitle').textContent = serviceName;
    document.getElementById('serviceModalImage').src = imageUrl;
    document.getElementById('serviceModalImage').alt = serviceName;
    const modal = new bootstrap.Modal(document.getElementById('serviceImageModal'));
    modal.show();
}
</script>
@endpush
@endsection
