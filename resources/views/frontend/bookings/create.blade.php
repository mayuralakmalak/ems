@extends('layouts.exhibitor')

@section('title', 'Book Booth')
@section('page-title', 'Book Booth - ' . $exhibition->name)

@section('content')
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>{{ $exhibition->name }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Venue:</strong> {{ $exhibition->venue }}, {{ $exhibition->city }}</p>
                <p><strong>Dates:</strong> {{ $exhibition->start_date->format('d M Y') }} - {{ $exhibition->end_date->format('d M Y') }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Base Price:</strong> ₹{{ number_format($exhibition->price_per_sqft ?? 0, 0) }}/sq ft</p>
                <p><strong>Status:</strong> <span class="badge bg-success">{{ ucfirst($exhibition->status) }}</span></p>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
    @csrf
    <input type="hidden" name="exhibition_id" value="{{ $exhibition->id }}">
    
    <!-- Floor Plan Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Select Booths</h5>
        </div>
        <div class="card-body">
            @if($exhibition->floorplan_image)
            <div class="mb-3">
                <img src="{{ asset('storage/' . $exhibition->floorplan_image) }}" alt="Floor Plan" class="img-fluid border rounded" style="max-height: 500px;">
            </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" title="Select All">
                            </th>
                            <th>Booth</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Size (sq ft)</th>
                            <th>Sides Open</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exhibition->booths->where('is_available', true) as $booth)
                        <tr>
                            <td>
                                <input type="checkbox" name="booth_ids[]" value="{{ $booth->id }}" class="booth-checkbox" data-price="{{ $booth->price }}" data-name="{{ $booth->name }}">
                            </td>
                            <td><strong>{{ $booth->name }}</strong></td>
                            <td>{{ $booth->category }}</td>
                            <td>{{ $booth->booth_type }}</td>
                            <td>{{ $booth->size_sqft }}</td>
                            <td>{{ $booth->sides_open }}</td>
                            <td>₹{{ number_format($booth->price, 0) }}</td>
                            <td>
                                @if($booth->is_free)
                                    <span class="badge bg-info">Free</span>
                                @else
                                    <span class="badge bg-success">Available</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="merge_booths" id="mergeBooths" value="1">
                    <label class="form-check-label" for="mergeBooths">
                        Merge selected booths (e.g., D1 + D2 = D1D2)
                    </label>
                </div>
            </div>
            
            <div class="mt-3">
                <strong>Selected Booths:</strong> <span id="selectedBooths">None</span><br>
                <strong>Total Amount:</strong> ₹<span id="totalAmount">0</span>
            </div>
        </div>
    </div>

    <!-- Additional Services -->
    @if($exhibition->services->where('is_active', true)->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Additional Services</h5>
            <small class="text-muted">Select additional services (before {{ $exhibition->addon_services_cutoff_date ? $exhibition->addon_services_cutoff_date->format('d M Y') : 'cutoff date' }})</small>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($exhibition->services->where('is_active', true) as $service)
                <div class="col-md-4 mb-3">
                    <div class="card border">
                        <div class="card-body">
                            @if($service->image)
                            <img src="{{ asset('storage/' . $service->image) }}" class="img-fluid mb-2" style="max-height: 100px;">
                            @endif
                            <h6>{{ $service->name }}</h6>
                            <p class="text-muted small mb-2">{{ $service->description }}</p>
                            <p class="mb-2"><strong>Price:</strong> ₹{{ number_format($service->price, 0) }}</p>
                            <div class="input-group">
                                <input type="number" name="services[{{ $service->id }}][quantity]" class="form-control service-quantity" min="0" value="0" data-price="{{ $service->price }}" data-service-id="{{ $service->id }}">
                                <input type="hidden" name="services[{{ $service->id }}][service_id]" value="{{ $service->id }}">
                                <span class="input-group-text">Qty</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-3">
                <strong>Services Total:</strong> ₹<span id="servicesTotal">0</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Contact Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Contact Information</h5>
            <small class="text-muted">Add up to 5 emails and 5 contact numbers</small>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Email Addresses (up to 5)</label>
                    <div id="emailContainer">
                        <div class="input-group mb-2">
                            <input type="email" name="contact_emails[]" class="form-control" placeholder="Email address">
                            <button type="button" class="btn btn-outline-danger remove-email" style="display: none;"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addEmail" {{ count(old('contact_emails', [])) >= 5 ? 'disabled' : '' }}>
                        <i class="bi bi-plus"></i> Add Email
                    </button>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Numbers (up to 5)</label>
                    <div id="phoneContainer">
                        <div class="input-group mb-2">
                            <input type="text" name="contact_numbers[]" class="form-control" placeholder="Contact number">
                            <button type="button" class="btn btn-outline-danger remove-phone" style="display: none;"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addPhone" {{ count(old('contact_numbers', [])) >= 5 ? 'disabled' : '' }}>
                        <i class="bi bi-plus"></i> Add Number
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Booking Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Booths Total:</strong> ₹<span id="boothsTotal">0</span></p>
                    <p><strong>Services Total:</strong> ₹<span id="servicesTotalSummary">0</span></p>
                </div>
                <div class="col-md-6">
                    <h4><strong>Grand Total:</strong> ₹<span id="grandTotal">0</span></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('exhibitions.show', $exhibition->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="bi bi-check-circle me-2"></i>Proceed to Payment
        </button>
    </div>
</form>

@push('scripts')
<script>
$(document).ready(function() {
    let selectedBooths = [];
    let totalAmount = 0;
    let servicesTotal = 0;

    // Select All checkbox
    $('#selectAll').on('change', function() {
        $('.booth-checkbox').prop('checked', this.checked);
        updateBoothSelection();
    });

    // Individual booth selection
    $('.booth-checkbox').on('change', function() {
        updateBoothSelection();
        $('#selectAll').prop('checked', $('.booth-checkbox:checked').length === $('.booth-checkbox').length);
    });

    function updateBoothSelection() {
        selectedBooths = [];
        totalAmount = 0;
        
        $('.booth-checkbox:checked').each(function() {
            selectedBooths.push($(this).data('name'));
            totalAmount += parseFloat($(this).data('price'));
        });

        $('#selectedBooths').text(selectedBooths.length > 0 ? selectedBooths.join(', ') : 'None');
        $('#boothsTotal').text(totalAmount.toLocaleString('en-IN'));
        updateGrandTotal();
        updateSubmitButton();
    }

    // Service quantity change
    $('.service-quantity').on('change', function() {
        calculateServicesTotal();
    });

    function calculateServicesTotal() {
        servicesTotal = 0;
        $('.service-quantity').each(function() {
            const quantity = parseInt($(this).val()) || 0;
            const price = parseFloat($(this).data('price')) || 0;
            servicesTotal += quantity * price;
        });
        $('#servicesTotal').text(servicesTotal.toLocaleString('en-IN'));
        $('#servicesTotalSummary').text(servicesTotal.toLocaleString('en-IN'));
        updateGrandTotal();
    }

    function updateGrandTotal() {
        const grandTotal = totalAmount + servicesTotal;
        $('#grandTotal').text(grandTotal.toLocaleString('en-IN'));
    }

    function updateSubmitButton() {
        $('#submitBtn').prop('disabled', selectedBooths.length === 0);
    }

    // Add/Remove Email
    $('#addEmail').on('click', function() {
        const count = $('#emailContainer .input-group').length;
        if (count < 5) {
            const newEmail = `
                <div class="input-group mb-2">
                    <input type="email" name="contact_emails[]" class="form-control" placeholder="Email address">
                    <button type="button" class="btn btn-outline-danger remove-email"><i class="bi bi-trash"></i></button>
                </div>
            `;
            $('#emailContainer').append(newEmail);
            if (count + 1 >= 5) $(this).prop('disabled', true);
            $('.remove-email').show();
        }
    });

    $(document).on('click', '.remove-email', function() {
        if ($('#emailContainer .input-group').length > 1) {
            $(this).closest('.input-group').remove();
            if ($('#emailContainer .input-group').length < 5) $('#addEmail').prop('disabled', false);
            if ($('#emailContainer .input-group').length === 1) $('.remove-email').hide();
        }
    });

    // Add/Remove Phone
    $('#addPhone').on('click', function() {
        const count = $('#phoneContainer .input-group').length;
        if (count < 5) {
            const newPhone = `
                <div class="input-group mb-2">
                    <input type="text" name="contact_numbers[]" class="form-control" placeholder="Contact number">
                    <button type="button" class="btn btn-outline-danger remove-phone"><i class="bi bi-trash"></i></button>
                </div>
            `;
            $('#phoneContainer').append(newPhone);
            if (count + 1 >= 5) $(this).prop('disabled', true);
            $('.remove-phone').show();
        }
    });

    $(document).on('click', '.remove-phone', function() {
        if ($('#phoneContainer .input-group').length > 1) {
            $(this).closest('.input-group').remove();
            if ($('#phoneContainer .input-group').length < 5) $('#addPhone').prop('disabled', false);
            if ($('#phoneContainer .input-group').length === 1) $('.remove-phone').hide();
        }
    });
});
</script>
@endpush
@endsection

