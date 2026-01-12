@extends('layouts.admin')

@section('title', 'Edit Booth')
@section('page-title', 'Edit Booth - ' . $booth->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.booths.index', $exhibition->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Booths
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Booth</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.booths.update', [$exhibition->id, $booth->id]) }}" method="POST" id="boothForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Booth Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ $booth->name }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Size (sq meter) *</label>
                    <input type="number" name="size_sqft" class="form-control" step="0.01" min="0" value="{{ $booth->size_sqft }}" required id="size_sqft">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category *</label>
                    @php
                        $activeCategoryTitles = $categories->pluck('title')->toArray();
                        $availableCategories = $activeCategoryTitles;

                        if (!in_array($booth->category, $availableCategories, true) && !empty($booth->category)) {
                            $availableCategories[] = $booth->category;
                        }

                        if (empty($availableCategories)) {
                            $availableCategories = ['Premium', 'Standard', 'Economy'];
                        }

                        $availableCategories = array_values(array_unique($availableCategories));
                        $selectedCategory = old('category', $booth->category);
                    @endphp
                    <select name="category" class="form-select" id="category" required>
                        @foreach($availableCategories as $category)
                            <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>
                                {{ $category }}@if(!in_array($category, $activeCategoryTitles, true) && $category === $booth->category) (inactive) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Booth Type *</label>
                    <select name="booth_type" class="form-select" id="booth_type" required>
                        <option value="Raw" {{ $booth->booth_type == 'Raw' ? 'selected' : '' }}>Raw</option>
                        <option value="Orphand" {{ $booth->booth_type == 'Orphand' ? 'selected' : '' }}>Orphand</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sides Open *</label>
                    <select name="sides_open" class="form-select" id="sides_open" required>
                        <option value="1" {{ $booth->sides_open == 1 ? 'selected' : '' }}>1 Side</option>
                        <option value="2" {{ $booth->sides_open == 2 ? 'selected' : '' }}>2 Sides</option>
                        <option value="3" {{ $booth->sides_open == 3 ? 'selected' : '' }}>3 Sides</option>
                        <option value="4" {{ $booth->sides_open == 4 ? 'selected' : '' }}>4 Sides</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Calculated Price</label>
                    <input type="text" id="calculated_price" class="form-control" readonly value="₹{{ number_format($booth->price, 2) }}">
                    <small class="text-muted">Price is calculated automatically</small>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_free" value="1" id="is_free" {{ $booth->is_free ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_free">
                            Free Booth (Price will be ₹0)
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.booths.index', $exhibition->id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Booth</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const rawPrice = {{ $exhibition->raw_price_per_sqft ?? 0 }};
    const orphandPrice = {{ $exhibition->orphand_price_per_sqft ?? 0 }};
    const side1Percent = {{ $exhibition->side_1_open_percent ?? 0 }};
    const side2Percent = {{ $exhibition->side_2_open_percent ?? 0 }};
    const side3Percent = {{ $exhibition->side_3_open_percent ?? 0 }};
    const side4Percent = {{ $exhibition->side_4_open_percent ?? 0 }};
    const premiumPrice = {{ $exhibition->premium_price ?? 0 }};
    const economyPrice = {{ $exhibition->economy_price ?? 0 }};

    function calculatePrice() {
        if ($('#is_free').is(':checked')) {
            $('#calculated_price').val('₹0.00');
            return;
        }

        const size = parseFloat($('#size_sqft').val()) || 0;
        const boothType = $('#booth_type').val();
        const sidesOpen = parseInt($('#sides_open').val()) || 1;
        const category = $('#category').val();

        const basePrice = boothType === 'Raw' ? rawPrice : orphandPrice;
        const sidePercent = sidesOpen === 1 ? side1Percent : (sidesOpen === 2 ? side2Percent : (sidesOpen === 3 ? side3Percent : side4Percent));
        const sideMultiplier = 1 + (sidePercent / 100);

        let price = basePrice * size * sideMultiplier;

        if (category === 'Premium') {
            price += premiumPrice;
        } else if (category === 'Economy') {
            price -= economyPrice;
        }

        $('#calculated_price').val('₹' + Math.max(0, price.toFixed(2)));
    }

    $('#size_sqft, #booth_type, #sides_open, #category').on('change input', calculatePrice);
    $('#is_free').on('change', calculatePrice);
    
    calculatePrice();
});
</script>
@endpush
@endsection

