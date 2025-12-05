@extends('layouts.admin')

@section('title', 'Create Exhibition - Step 2')
@section('page-title', 'Create Exhibition - Step 2: Floor Plan & Pricing')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Step 2: Floor Plan Management & Pricing Configuration</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.exhibitions.step2.store', $exhibition->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="form-label">Upload Floorplan Image</label>
                <input type="file" name="floorplan_image" class="form-control" accept="image/*">
            </div>

            <h6 class="mb-3">Booth Type Pricing (per sq ft)</h6>
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Raw Price/sq ft <span class="text-danger">*</span></label>
                    <input type="number" name="raw_price_per_sqft" class="form-control" step="0.01" value="{{ $exhibition->raw_price_per_sqft }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Orphand Price/sq ft <span class="text-danger">*</span></label>
                    <input type="number" name="orphand_price_per_sqft" class="form-control" step="0.01" value="{{ $exhibition->orphand_price_per_sqft }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Standard Price/sq ft <span class="text-danger">*</span></label>
                    <input type="number" name="price_per_sqft" class="form-control" step="0.01" value="{{ $exhibition->price_per_sqft }}" required>
                </div>
            </div>

            <h6 class="mb-3">Side Open Variations (% adjustment)</h6>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <label class="form-label">1 Side Open %</label>
                    <input type="number" name="side_1_open_percent" class="form-control" step="0.01" value="{{ $exhibition->side_1_open_percent }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">2 Sides Open %</label>
                    <input type="number" name="side_2_open_percent" class="form-control" step="0.01" value="{{ $exhibition->side_2_open_percent }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">3 Sides Open %</label>
                    <input type="number" name="side_3_open_percent" class="form-control" step="0.01" value="{{ $exhibition->side_3_open_percent }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">4 Sides Open %</label>
                    <input type="number" name="side_4_open_percent" class="form-control" step="0.01" value="{{ $exhibition->side_4_open_percent }}">
                </div>
            </div>

            <h6 class="mb-3">Booth Category Pricing</h6>
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Premium Price</label>
                    <input type="number" name="premium_price" class="form-control" step="0.01" value="{{ $exhibition->premium_price }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Standard Price</label>
                    <input type="number" name="standard_price" class="form-control" step="0.01" value="{{ $exhibition->standard_price }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Economy Price</label>
                    <input type="number" name="economy_price" class="form-control" step="0.01" value="{{ $exhibition->economy_price }}">
                </div>
            </div>

            <h6 class="mb-3">Cut-off Dates</h6>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Add-on Services Cut-off Date</label>
                    <input type="date" name="addon_services_cutoff_date" class="form-control" value="{{ $exhibition->addon_services_cutoff_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Document Upload Deadline</label>
                    <input type="date" name="document_upload_deadline" class="form-control" value="{{ $exhibition->document_upload_deadline?->format('Y-m-d') }}">
                </div>
            </div>

            <hr class="my-4">

            <h6 class="mb-3">Booth Configuration</h6>
            <div class="mb-3">
                <button type="button" class="btn btn-sm btn-success" id="addBoothBtn">
                    <i class="bi bi-plus-circle me-1"></i>Add Booth
                </button>
            </div>

            <div id="boothsContainer" class="mb-4">
                @if($exhibition->booths->count() > 0)
                    @foreach($exhibition->booths as $booth)
                    <div class="card mb-3 booth-item" data-booth-id="{{ $booth->id }}">
                        <div class="card-body">
                            <div class="row g-3">
                                <input type="hidden" name="booths[{{ $loop->index }}][id]" value="{{ $booth->id }}">
                                <div class="col-md-2">
                                    <label class="form-label">Booth Name *</label>
                                    <input type="text" name="booths[{{ $loop->index }}][name]" class="form-control" value="{{ $booth->name }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Size (sq ft) *</label>
                                    <input type="number" name="booths[{{ $loop->index }}][size_sqft]" class="form-control booth-size" step="0.01" value="{{ $booth->size_sqft }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Category *</label>
                                    <select name="booths[{{ $loop->index }}][category]" class="form-select">
                                        <option value="Premium" {{ $booth->category == 'Premium' ? 'selected' : '' }}>Premium</option>
                                        <option value="Standard" {{ $booth->category == 'Standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="Economy" {{ $booth->category == 'Economy' ? 'selected' : '' }}>Economy</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Booth Type *</label>
                                    <select name="booths[{{ $loop->index }}][booth_type]" class="form-select">
                                        <option value="Raw" {{ $booth->booth_type == 'Raw' ? 'selected' : '' }}>Raw</option>
                                        <option value="Orphand" {{ $booth->booth_type == 'Orphand' ? 'selected' : '' }}>Orphand</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Sides Open *</label>
                                    <select name="booths[{{ $loop->index }}][sides_open]" class="form-select">
                                        <option value="1" {{ $booth->sides_open == 1 ? 'selected' : '' }}>1 Side</option>
                                        <option value="2" {{ $booth->sides_open == 2 ? 'selected' : '' }}>2 Sides</option>
                                        <option value="3" {{ $booth->sides_open == 3 ? 'selected' : '' }}>3 Sides</option>
                                        <option value="4" {{ $booth->sides_open == 4 ? 'selected' : '' }}>4 Sides</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Price</label>
                                    <input type="number" name="booths[{{ $loop->index }}][price]" class="form-control booth-price" step="0.01" value="{{ $booth->price }}" readonly>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="booths[{{ $loop->index }}][is_free]" value="1" {{ $booth->is_free ? 'checked' : '' }} id="free_{{ $loop->index }}">
                                        <label class="form-check-label" for="free_{{ $loop->index }}">Free Booth</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-sm btn-danger remove-booth" {{ $booth->is_booked ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save and Continue to Step 3</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let boothIndex = {{ $exhibition->booths->count() }};
    
    $('#addBoothBtn').on('click', function() {
        const boothHtml = `
            <div class="card mb-3 booth-item">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Booth Name *</label>
                            <input type="text" name="booths[${boothIndex}][name]" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Size (sq ft) *</label>
                            <input type="number" name="booths[${boothIndex}][size_sqft]" class="form-control booth-size" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Category *</label>
                            <select name="booths[${boothIndex}][category]" class="form-select">
                                <option value="Premium">Premium</option>
                                <option value="Standard" selected>Standard</option>
                                <option value="Economy">Economy</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Booth Type *</label>
                            <select name="booths[${boothIndex}][booth_type]" class="form-select">
                                <option value="Raw" selected>Raw</option>
                                <option value="Orphand">Orphand</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sides Open *</label>
                            <select name="booths[${boothIndex}][sides_open]" class="form-select">
                                <option value="1" selected>1 Side</option>
                                <option value="2">2 Sides</option>
                                <option value="3">3 Sides</option>
                                <option value="4">4 Sides</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Price</label>
                            <input type="number" name="booths[${boothIndex}][price]" class="form-control booth-price" step="0.01" readonly>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="booths[${boothIndex}][is_free]" value="1" id="free_${boothIndex}">
                                <label class="form-check-label" for="free_${boothIndex}">Free Booth</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-danger remove-booth">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#boothsContainer').append(boothHtml);
        boothIndex++;
        calculateBoothPrice($('#boothsContainer .booth-item').last());
    });

    $(document).on('click', '.remove-booth', function() {
        const boothItem = $(this).closest('.booth-item');
        const boothId = boothItem.data('booth-id');
        
        if (boothId) {
            // Mark for deletion
            boothItem.append(`<input type="hidden" name="delete_booths[]" value="${boothId}">`);
            boothItem.hide();
        } else {
            // Remove new booth
            boothItem.remove();
        }
    });

    $(document).on('change', '.booth-size, .booth-item select', function() {
        calculateBoothPrice($(this).closest('.booth-item'));
    });

    $(document).on('change', '.booth-item input[type="checkbox"]', function() {
        const boothItem = $(this).closest('.booth-item');
        if ($(this).is(':checked')) {
            boothItem.find('.booth-price').val(0);
        } else {
            calculateBoothPrice(boothItem);
        }
    });

    function calculateBoothPrice(boothItem) {
        const isFree = boothItem.find('input[type="checkbox"]').is(':checked');
        if (isFree) {
            boothItem.find('.booth-price').val(0);
            return;
        }

        const rawPrice = parseFloat($('input[name="raw_price_per_sqft"]').val()) || 0;
        const orphandPrice = parseFloat($('input[name="orphand_price_per_sqft"]').val()) || 0;
        const size = parseFloat(boothItem.find('.booth-size').val()) || 0;
        const boothType = boothItem.find('select[name*="[booth_type]"]').val();
        const sidesOpen = parseInt(boothItem.find('select[name*="[sides_open]"]').val()) || 1;
        const category = boothItem.find('select[name*="[category]"]').val();
        
        const basePrice = boothType === 'Raw' ? rawPrice : orphandPrice;
        const sidePercent = parseFloat($(`input[name="side_${sidesOpen}_open_percent"]`).val()) || 0;
        const sideMultiplier = 1 + (sidePercent / 100);
        
        let price = basePrice * size * sideMultiplier;
        
        // Add category premium
        if (category === 'Premium') {
            const premiumPrice = parseFloat($('input[name="premium_price"]').val()) || 0;
            price += premiumPrice;
        } else if (category === 'Economy') {
            const economyPrice = parseFloat($('input[name="economy_price"]').val()) || 0;
            price -= economyPrice;
        }
        
        boothItem.find('.booth-price').val(Math.max(0, price.toFixed(2)));
    }

    // Calculate prices for existing booths on page load
    $('.booth-item').each(function() {
        calculateBoothPrice($(this));
    });
});
</script>
@endpush
@endsection

