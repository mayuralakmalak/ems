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

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Booking Details</h5>
                    <small class="text-muted">Provide company and contact information.</small>
                </div>
                <div class="card-body">
                    <form id="bookingForm" action="{{ route('bookings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="exhibition_id" value="{{ $exhibition->id }}">
                        @foreach($boothIds as $boothId)
                            <input type="hidden" name="booth_ids[]" value="{{ $boothId }}">
                        @endforeach

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
                            <label class="form-label">Promotional Brochures (Max 3)</label>
                            <input type="file" class="form-control" name="brochures[]" accept="application/pdf" multiple>
                            <small class="text-muted">PDF files, max 5MB each</small>
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
                <div class="summary-row">
                    <span class="summary-label">Exhibition</span>
                    <span class="summary-value">{{ $exhibition->name }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Selected Booths</span>
                    <span class="summary-value">{{ implode(', ', $booths->pluck('name')->toArray()) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Total Amount</span>
                    <span class="summary-value">₹{{ number_format($totalAmount, 2) }}</span>
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
</script>
@endpush
@endsection
