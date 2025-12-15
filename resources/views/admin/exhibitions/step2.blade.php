@extends('layouts.admin')

@section('title', 'Admin - Exhibition booking step 2')
@section('page-title', 'Admin - Exhibition booking step 2')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Admin - Exhibition booking step 2</h4>
            <span class="text-muted">23 / 36</span>
        </div>
        <div class="text-center mb-4">
            <h5>Step 2 - Booth & Pricing Configuration</h5>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <form id="step2Form" action="{{ route('admin.exhibitions.step2.store', $exhibition->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

              <!-- Booth & Pricing Configuration Section -->
              <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Booth & Pricing Configuration</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary add-size-btn">Add size</button>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <p class="text-muted mb-2">Size (sq ft) with row price, orphan price, category, and multiple items. Use Add size to manage multiple entries.</p>
                        <div id="boothSizesContainer">
                            @forelse($exhibition->boothSizes as $sizeIndex => $boothSize)
                            <div class="border rounded p-3 mb-3 booth-size-card" data-size-index="{{ $sizeIndex }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Size #{{ $loop->iteration }}</h6>
                                    <button type="button" class="btn btn-sm btn-link text-danger remove-size-btn">Remove size</button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Size (sq ft)</label>
                                        <input type="number" step="0.01" name="booth_sizes[{{ $sizeIndex }}][size_sqft]" class="form-control" value="{{ $boothSize->size_sqft }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Row price</label>
                                        <input type="number" step="0.01" name="booth_sizes[{{ $sizeIndex }}][row_price]" class="form-control" value="{{ $boothSize->row_price }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Orphan price</label>
                                        <input type="number" step="0.01" name="booth_sizes[{{ $sizeIndex }}][orphan_price]" class="form-control" value="{{ $boothSize->orphan_price }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Category</label>
                                        <select name="booth_sizes[{{ $sizeIndex }}][category]" class="form-select">
                                            <option value="">Select</option>
                                            <option value="1" @selected($boothSize->category === '1' || $boothSize->category === 'Premium')>Premium</option>
                                            <option value="2" @selected($boothSize->category === '2' || $boothSize->category === 'Standard')>Standard</option>
                                            <option value="3" @selected($boothSize->category === '3' || $boothSize->category === 'Economy')>Economy</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Items for this size</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary add-item-btn" data-size-index="{{ $sizeIndex }}">Add item</button>
                                    </div>
                                    <div class="items-container">
                                        @php $items = $boothSize->items ?? collect(); @endphp
                                        @forelse($items as $itemIndex => $item)
                                        <div class="row g-2 align-items-end item-row" data-item-index="{{ $itemIndex }}">
                                            <div class="col-md-5">
                                                <label class="form-label">Item name</label>
                                                <input type="text" name="booth_sizes[{{ $sizeIndex }}][items][{{ $itemIndex }}][name]" class="form-control" value="{{ $item->item_name }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="booth_sizes[{{ $sizeIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control" value="{{ $item->quantity }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Images</label>
                                                <input type="file" name="booth_sizes[{{ $sizeIndex }}][items][{{ $itemIndex }}][images][]" class="form-control" multiple>
                                                @if(!empty($item->images))
                                                    <small class="text-muted">Existing: {{ collect($item->images)->map(function($img){ return basename($img); })->implode(', ') }}</small>
                                                @endif
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="button" class="btn btn-sm btn-link text-danger remove-item-btn">Remove item</button>
                                            </div>
                                        </div>
                                        @empty
                                        <div class="row g-2 align-items-end item-row" data-item-index="0">
                                            <div class="col-md-5">
                                                <label class="form-label">Item name</label>
                                                <input type="text" name="booth_sizes[{{ $sizeIndex }}][items][0][name]" class="form-control" placeholder="Item name">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="booth_sizes[{{ $sizeIndex }}][items][0][quantity]" class="form-control" placeholder="0">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Images</label>
                                                <input type="file" name="booth_sizes[{{ $sizeIndex }}][items][0][images][]" class="form-control" multiple>
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="button" class="btn btn-sm btn-link text-danger remove-item-btn">Remove item</button>
                                            </div>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="border rounded p-3 mb-3 booth-size-card" data-size-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Size #1</h6>
                                    <button type="button" class="btn btn-sm btn-link text-danger remove-size-btn">Remove size</button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Size (sq ft)</label>
                                        <input type="number" step="0.01" name="booth_sizes[0][size_sqft]" class="form-control" placeholder="e.g. 100">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Row price</label>
                                        <input type="number" step="0.01" name="booth_sizes[0][row_price]" class="form-control" placeholder="e.g. 5000">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Orphan price</label>
                                        <input type="number" step="0.01" name="booth_sizes[0][orphan_price]" class="form-control" placeholder="e.g. 4000">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Category</label>
                                        <select name="booth_sizes[0][category]" class="form-select">
                                            <option value="">Select</option>
                                            <option value="1">Premium</option>
                                            <option value="2">Standard</option>
                                            <option value="3">Economy</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Items for this size</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary add-item-btn" data-size-index="0">Add item</button>
                                    </div>
                                    <div class="items-container">
                                        <div class="row g-2 align-items-end item-row" data-item-index="0">
                                            <div class="col-md-5">
                                                <label class="form-label">Item name</label>
                                                <input type="text" name="booth_sizes[0][items][0][name]" class="form-control" placeholder="Item name">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="booth_sizes[0][items][0][quantity]" class="form-control" placeholder="0">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Images</label>
                                                <input type="file" name="booth_sizes[0][items][0][images][]" class="form-control" multiple>
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="button" class="btn btn-sm btn-link text-danger remove-item-btn">Remove item</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        <div class="d-flex justify-content-end mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary add-size-btn">Add size</button>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Base price per sq ft</label>
                                <input type="number" name="price_per_sqft" class="form-control" step="0.01" placeholder="eg. 100" value="{{ $exhibition->price_per_sqft ?? '' }}">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Side Open Variations (% adjustment)</label>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">1 Side Open</span>
                                            <input type="text" name="side_1_open_percent" class="form-control" placeholder="%" value="{{ $exhibition->side_1_open_percent ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">2 Sides Open</span>
                                            <input type="text" name="side_2_open_percent" class="form-control" placeholder="%" value="{{ $exhibition->side_2_open_percent ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">3 Sides Open</span>
                                            <input type="text" name="side_3_open_percent" class="form-control" placeholder="%" value="{{ $exhibition->side_3_open_percent ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">4 Sides Open</span>
                                            <input type="text" name="side_4_open_percent" class="form-control" placeholder="%" value="{{ $exhibition->side_4_open_percent ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add-on Services Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Add-on Services</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary add-addon-btn">Add service</button>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Add-on services are independent of booth sizes.</p>
                    <div id="addonServicesContainer">
                        @forelse($exhibition->addonServices as $idx => $service)
                        <div class="row g-3 align-items-end addon-row" data-addon-index="{{ $idx }}">
                            <div class="col-md-6">
                                <label class="form-label">Item name</label>
                                <input type="text" name="addon_services[{{ $idx }}][item_name]" class="form-control" value="{{ $service->item_name }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price per quantity</label>
                                <input type="number" step="0.01" name="addon_services[{{ $idx }}][price_per_quantity]" class="form-control" value="{{ $service->price_per_quantity }}">
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-sm btn-link text-danger remove-addon-btn">Remove</button>
                            </div>
                        </div>
                        @empty
                        <div class="row g-3 align-items-end addon-row" data-addon-index="0">
                            <div class="col-md-6">
                                <label class="form-label">Item name</label>
                                <input type="text" name="addon_services[0][item_name]" class="form-control" placeholder="Service name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price per quantity</label>
                                <input type="number" step="0.01" name="addon_services[0][price_per_quantity]" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-sm btn-link text-danger remove-addon-btn">Remove</button>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary add-addon-btn">Add service</button>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Save and Continue to Step 3</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sizesContainer = document.getElementById('boothSizesContainer');
    const addSizeButtons = document.querySelectorAll('.add-size-btn');
    let sizeCounter = sizesContainer ? sizesContainer.querySelectorAll('.booth-size-card').length : 0;
    const addonContainer = document.getElementById('addonServicesContainer');
    const addAddonButtons = document.querySelectorAll('.add-addon-btn');
    let addonCounter = addonContainer ? addonContainer.querySelectorAll('.addon-row').length : 0;

    const itemTemplate = (sizeIndex, itemIndex) => `
        <div class="row g-2 align-items-end item-row" data-item-index="${itemIndex}">
            <div class="col-md-5">
                <label class="form-label">Item name</label>
                <input type="text" name="booth_sizes[${sizeIndex}][items][${itemIndex}][name]" class="form-control" placeholder="Item name">
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="booth_sizes[${sizeIndex}][items][${itemIndex}][quantity]" class="form-control" placeholder="0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Images</label>
                <input type="file" name="booth_sizes[${sizeIndex}][items][${itemIndex}][images][]" class="form-control" multiple>
            </div>
            <div class="col-12 text-end">
                <button type="button" class="btn btn-sm btn-link text-danger remove-item-btn">Remove item</button>
            </div>
        </div>
    `;

    const sizeTemplate = (sizeIndex) => `
        <div class="border rounded p-3 mb-3 booth-size-card" data-size-index="${sizeIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Size #${sizeIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-link text-danger remove-size-btn">Remove size</button>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Size (sq ft)</label>
                    <input type="number" step="0.01" name="booth_sizes[${sizeIndex}][size_sqft]" class="form-control" placeholder="e.g. 100">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Row price</label>
                    <input type="number" step="0.01" name="booth_sizes[${sizeIndex}][row_price]" class="form-control" placeholder="e.g. 5000">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Orphan price</label>
                    <input type="number" step="0.01" name="booth_sizes[${sizeIndex}][orphan_price]" class="form-control" placeholder="e.g. 4000">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="booth_sizes[${sizeIndex}][category]" class="form-select">
                        <option value="">Select</option>
                        <option value="1">Premium</option>
                        <option value="2">Standard</option>
                        <option value="3">Economy</option>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">Items for this size</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary add-item-btn" data-size-index="${sizeIndex}">Add item</button>
                </div>
                <div class="items-container">
                    ${itemTemplate(sizeIndex, 0)}
                </div>
            </div>
        </div>
    `;

    const addSizeCard = () => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = sizeTemplate(sizeCounter).trim();
        sizesContainer.appendChild(wrapper.firstElementChild);
        sizeCounter += 1;
    };

    const addItemRow = (sizeCard) => {
        const itemsContainer = sizeCard.querySelector('.items-container');
        const sizeIndex = sizeCard.getAttribute('data-size-index');
        const nextIndex = itemsContainer.querySelectorAll('.item-row').length;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = itemTemplate(sizeIndex, nextIndex).trim();
        itemsContainer.appendChild(wrapper.firstElementChild);
    };

    if (addSizeButtons && addSizeButtons.length && sizesContainer) {
        addSizeButtons.forEach((btn) => btn.addEventListener('click', () => addSizeCard()));
    }

    if (sizesContainer) {
        sizesContainer.addEventListener('click', (event) => {
            if (event.target.closest('.add-item-btn')) {
                const button = event.target.closest('.add-item-btn');
                const sizeCard = button.closest('.booth-size-card');
                addItemRow(sizeCard);
            }

            if (event.target.closest('.remove-item-btn')) {
                const itemRow = event.target.closest('.item-row');
                const itemsContainer = itemRow.parentElement;
                itemRow.remove();
                if (!itemsContainer.querySelector('.item-row')) {
                    const sizeCard = itemsContainer.closest('.booth-size-card');
                    addItemRow(sizeCard);
                }
            }

            if (event.target.closest('.remove-size-btn')) {
                const sizeCard = event.target.closest('.booth-size-card');
                sizeCard.remove();
                if (!sizesContainer.querySelector('.booth-size-card')) {
                    sizeCounter = 0;
                    addSizeCard();
                }
            }
        });
    }

    const addonTemplate = (idx) => `
        <div class="row g-3 align-items-end addon-row" data-addon-index="${idx}">
            <div class="col-md-6">
                <label class="form-label">Item name</label>
                <input type="text" name="addon_services[${idx}][item_name]" class="form-control" placeholder="Service name">
            </div>
            <div class="col-md-4">
                <label class="form-label">Price per quantity</label>
                <input type="number" step="0.01" name="addon_services[${idx}][price_per_quantity]" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-sm btn-link text-danger remove-addon-btn">Remove</button>
            </div>
        </div>
    `;

    const addAddonRow = () => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = addonTemplate(addonCounter).trim();
        addonContainer.appendChild(wrapper.firstElementChild);
        addonCounter += 1;
    };

    if (addAddonButtons && addAddonButtons.length && addonContainer) {
        addAddonButtons.forEach((btn) => btn.addEventListener('click', () => addAddonRow()));
    }

    if (addonContainer) {
        addonContainer.addEventListener('click', (event) => {
            if (event.target.closest('.remove-addon-btn')) {
                const row = event.target.closest('.addon-row');
                row.remove();
                if (!addonContainer.querySelector('.addon-row')) {
                    addonCounter = 0;
                    addAddonRow();
                }
            }
        });
    }
});
</script>
@endpush
