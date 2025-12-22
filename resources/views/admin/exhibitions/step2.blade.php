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

              <!-- Floor Management Section -->
              <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-building me-2"></i>Floor Management</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addFloorBtn">
                        <i class="bi bi-plus-circle me-1"></i>Add Floor
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Manage multiple floors for this exhibition. Each floor can have its own floor plan and booth configuration.</p>
                    <div id="floorsContainer">
                        @forelse($exhibition->floors as $floorIndex => $floor)
                        <div class="border rounded p-3 mb-3 floor-item" data-floor-id="{{ $floor->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">
                                    <i class="bi bi-layers me-2"></i>{{ $floor->name }}
                                    @if(!$floor->is_active)
                                        <span class="badge bg-secondary ms-2">Inactive</span>
                                    @endif
                                </h6>
                                <button type="button" class="btn btn-sm btn-link text-danger remove-floor-btn" data-floor-id="{{ $floor->id }}">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Floor Name <span class="text-danger">*</span></label>
                                    <input type="text" name="floors[{{ $floorIndex }}][name]" class="form-control floor-name-input" 
                                           value="{{ $floor->name }}" required>
                                    <input type="hidden" name="floors[{{ $floorIndex }}][id]" value="{{ $floor->id }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Floor Number <span class="text-danger">*</span></label>
                                    <input type="number" name="floors[{{ $floorIndex }}][floor_number]" class="form-control" 
                                           value="{{ $floor->floor_number }}" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="floors[{{ $floorIndex }}][description]" class="form-control" 
                                           value="{{ $floor->description }}" placeholder="Optional description">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Active</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="floors[{{ $floorIndex }}][is_active]" 
                                               value="1" {{ $floor->is_active ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="border rounded p-3 mb-3 floor-item" data-floor-id="new-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="bi bi-layers me-2"></i>Floor #1</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger remove-floor-btn">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Floor Name <span class="text-danger">*</span></label>
                                    <input type="text" name="floors[0][name]" class="form-control floor-name-input" 
                                           value="Ground Floor" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Floor Number <span class="text-danger">*</span></label>
                                    <input type="number" name="floors[0][floor_number]" class="form-control" 
                                           value="0" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="floors[0][description]" class="form-control" 
                                           placeholder="Optional description">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Active</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="floors[0][is_active]" 
                                               value="1" checked>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addFloorBtnBottom">
                            <i class="bi bi-plus-circle me-1"></i>Add Floor
                        </button>
                    </div>
                </div>
              </div>

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
                                <div class="row g-3 mt-2">
                                    <div class="col-12">
                                        <label class="form-label">Size Images</label>
                                        <input type="file" name="booth_sizes[{{ $sizeIndex }}][images][]" class="form-control size-images-input" multiple accept="image/*" data-size-index="{{ $sizeIndex }}">
                                        <div class="size-images-preview mt-2" data-size-index="{{ $sizeIndex }}"></div>
                                        @if(!empty($boothSize->images))
                                            <div class="mt-1 small">
                                                <span class="text-muted d-block mb-1">Existing images:</span>
                                                <div class="d-flex flex-wrap gap-2 size-existing-images-container">
                                                    @foreach((array) $boothSize->images as $imgPath)
                                                        <div class="size-image-wrapper d-flex align-items-center gap-2 mb-1">
                                                            <img
                                                                src="{{ asset('storage/' . ltrim($imgPath, '/')) }}"
                                                                alt="{{ basename($imgPath) }}"
                                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;"
                                                            >
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-link text-danger p-0 size-image-remove-btn"
                                                                data-size-index="{{ $sizeIndex }}"
                                                                data-image-path="{{ $imgPath }}"
                                                            >
                                                                Remove
                                                            </button>
                                                            <input
                                                                type="hidden"
                                                                name="booth_sizes[{{ $sizeIndex }}][existing_images][]"
                                                                value="{{ $imgPath }}"
                                                            >
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
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
                                            <div class="col-md-4">
                                                <label class="form-label">Item name</label>
                                                <input type="text" name="booth_sizes[{{ $sizeIndex }}][items][{{ $itemIndex }}][name]" class="form-control" value="{{ $item->item_name }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="booth_sizes[{{ $sizeIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control" value="{{ $item->quantity }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Price (per extra unit)</label>
                                                <input type="number" step="0.01" name="booth_sizes[{{ $sizeIndex }}][items][{{ $itemIndex }}][price]" class="form-control" value="{{ $item->price }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Images</label>
                                                <input type="file" name="booth_sizes[{{ $sizeIndex }}][items][{{ $itemIndex }}][images][]" class="form-control" multiple>
                                                @if(!empty($item->images))
                                                    <div class="mt-1 small">
                                                        <span class="text-muted d-block mb-1">Existing images:</span>
                                                        @foreach((array) $item->images as $imgPath)
                                                            <div class="form-check">
                                                                <input
                                                                    class="form-check-input"
                                                                    type="checkbox"
                                                                    name="booth_sizes[{{ $sizeIndex }}][items][{{ $itemIndex }}][remove_existing_images][]"
                                                                    value="{{ $imgPath }}"
                                                                    id="remove_img_{{ $sizeIndex }}_{{ $itemIndex }}_{{ md5($imgPath) }}"
                                                                >
                                                                <label class="form-check-label" for="remove_img_{{ $sizeIndex }}_{{ $itemIndex }}_{{ md5($imgPath) }}">
                                                                    <span class="d-inline-flex align-items-center gap-2">
                                                                        <img
                                                                            src="{{ asset('storage/' . ltrim($imgPath, '/')) }}"
                                                                            alt="{{ basename($imgPath) }}"
                                                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;"
                                                                        >
                                                                        {{-- <span>{{ basename($imgPath) }}</span> --}}
                                                                        <span class="text-danger ms-1">(Remove)</span>
                                                                    </span>
                                                                </label>
                                                                <input
                                                                    type="hidden"
                                                                    name="booth_sizes[{{ $sizeIndex }}][items][{{ $itemIndex }}][existing_images][]"
                                                                    value="{{ $imgPath }}"
                                                                >
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="button" class="btn btn-sm btn-link text-danger remove-item-btn">Remove item</button>
                                            </div>
                                        </div>
                                        @empty
                                        <div class="row g-2 align-items-end item-row" data-item-index="0">
                                            <div class="col-md-4">
                                                <label class="form-label">Item name</label>
                                                <input type="text" name="booth_sizes[{{ $sizeIndex }}][items][0][name]" class="form-control" placeholder="Item name">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="booth_sizes[{{ $sizeIndex }}][items][0][quantity]" class="form-control" placeholder="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Price (per extra unit)</label>
                                                <input type="number" step="0.01" name="booth_sizes[{{ $sizeIndex }}][items][0][price]" class="form-control" placeholder="0.00">
                                            </div>
                                            <div class="col-md-3">
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
                                <div class="row g-3 mt-2">
                                    <div class="col-12">
                                        <label class="form-label">Size Images</label>
                                        <input type="file" name="booth_sizes[0][images][]" class="form-control size-images-input" multiple accept="image/*" data-size-index="0">
                                        <div class="size-images-preview mt-2" data-size-index="0"></div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">Items for this size</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary add-item-btn" data-size-index="0">Add item</button>
                                    </div>
                                    <div class="items-container">
                                        <div class="row g-2 align-items-end item-row" data-item-index="0">
                                            <div class="col-md-4">
                                                <label class="form-label">Item name</label>
                                                <input type="text" name="booth_sizes[0][items][0][name]" class="form-control" placeholder="Item name">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="booth_sizes[0][items][0][quantity]" class="form-control" placeholder="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Price (per extra unit)</label>
                                                <input type="number" step="0.01" name="booth_sizes[0][items][0][price]" class="form-control" placeholder="0.00">
                                            </div>
                                            <div class="col-md-3">
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
                            <div class="col-md-5">
                                <label class="form-label">Service</label>
                                <select name="addon_services[{{ $idx }}][service_id]" class="form-select">
                                    <option value="">Select service</option>
                                    @foreach($services as $configuredService)
                                        <option value="{{ $configuredService->id }}" {{ $configuredService->name === $service->item_name ? 'selected' : '' }}>
                                            {{ $configuredService->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Price per quantity</label>
                                <input type="number" step="0.01" name="addon_services[{{ $idx }}][price_per_quantity]" class="form-control" value="{{ $service->price_per_quantity }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cut-off date</label>
                                <input type="date" name="addon_services[{{ $idx }}][cutoff_date]" class="form-control"
                                       value="{{ $service->cutoff_date ? $service->cutoff_date->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-link text-danger remove-addon-btn">Remove</button>
                            </div>
                        </div>
                        @empty
                        <div class="row g-3 align-items-end addon-row" data-addon-index="0">
                            <div class="col-md-5">
                                <label class="form-label">Service</label>
                                <select name="addon_services[0][service_id]" class="form-select">
                                    <option value="">Select service</option>
                                    @foreach($services as $configuredService)
                                        <option value="{{ $configuredService->id }}">{{ $configuredService->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Price per quantity</label>
                                <input type="number" step="0.01" name="addon_services[0][price_per_quantity]" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cut-off date</label>
                                <input type="date" name="addon_services[0][cutoff_date]" class="form-control">
                            </div>
                            <div class="col-md-1 text-end">
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
    // Floor Management
    const floorsContainer = document.getElementById('floorsContainer');
    const addFloorButtons = document.querySelectorAll('#addFloorBtn, #addFloorBtnBottom');
    let floorCounter = floorsContainer ? floorsContainer.querySelectorAll('.floor-item').length : 0;

    const floorTemplate = (floorIndex) => `
        <div class="border rounded p-3 mb-3 floor-item" data-floor-id="new-${floorIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bi bi-layers me-2"></i>Floor #${floorIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-link text-danger remove-floor-btn">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Floor Name <span class="text-danger">*</span></label>
                    <input type="text" name="floors[${floorIndex}][name]" class="form-control floor-name-input" 
                           value="Floor ${floorIndex + 1}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Floor Number <span class="text-danger">*</span></label>
                    <input type="number" name="floors[${floorIndex}][floor_number]" class="form-control" 
                           value="${floorIndex}" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Description</label>
                    <input type="text" name="floors[${floorIndex}][description]" class="form-control" 
                           placeholder="Optional description">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Active</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="floors[${floorIndex}][is_active]" 
                               value="1" checked>
                    </div>
                </div>
            </div>
        </div>
    `;

    const addFloor = () => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = floorTemplate(floorCounter).trim();
        floorsContainer.appendChild(wrapper.firstElementChild);
        floorCounter += 1;
    };

    if (addFloorButtons && addFloorButtons.length && floorsContainer) {
        addFloorButtons.forEach((btn) => btn.addEventListener('click', () => addFloor()));
    }

    if (floorsContainer) {
        floorsContainer.addEventListener('click', (event) => {
            if (event.target.closest('.remove-floor-btn')) {
                const floorItem = event.target.closest('.floor-item');
                const floorId = floorItem.getAttribute('data-floor-id');
                
                // Don't allow removing if it's the only floor
                if (floorsContainer.querySelectorAll('.floor-item').length <= 1) {
                    alert('You must have at least one floor.');
                    return;
                }
                
                floorItem.remove();
            }
        });
    }

    // Booth Sizes Management
    const sizesContainer = document.getElementById('boothSizesContainer');
    const addSizeButtons = document.querySelectorAll('.add-size-btn');
    let sizeCounter = sizesContainer ? sizesContainer.querySelectorAll('.booth-size-card').length : 0;
    const addonContainer = document.getElementById('addonServicesContainer');
    const addAddonButtons = document.querySelectorAll('.add-addon-btn');
    let addonCounter = addonContainer ? addonContainer.querySelectorAll('.addon-row').length : 0;

    const itemTemplate = (sizeIndex, itemIndex) => `
        <div class="row g-2 align-items-end item-row" data-item-index="${itemIndex}">
            <div class="col-md-4">
                <label class="form-label">Item name</label>
                <input type="text" name="booth_sizes[${sizeIndex}][items][${itemIndex}][name]" class="form-control" placeholder="Item name">
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity</label>
                <input type="number" name="booth_sizes[${sizeIndex}][items][${itemIndex}][quantity]" class="form-control" placeholder="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">Price (per extra unit)</label>
                <input type="number" step="0.01" name="booth_sizes[${sizeIndex}][items][${itemIndex}][price]" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-3">
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
            <div class="row g-3 mt-2">
                <div class="col-12">
                    <label class="form-label">Size Images</label>
                    <input type="file" name="booth_sizes[${sizeIndex}][images][]" class="form-control size-images-input" multiple accept="image/*" data-size-index="${sizeIndex}">
                    <div class="size-images-preview mt-2" data-size-index="${sizeIndex}"></div>
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

    // Function to handle image preview
    const handleImagePreview = (input) => {
        const sizeIndex = input.getAttribute('data-size-index');
        const previewContainer = document.querySelector(`.size-images-preview[data-size-index="${sizeIndex}"]`);
        
        if (!previewContainer) return;
        
        // Clear previous previews
        previewContainer.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            const previewTitle = document.createElement('div');
            previewTitle.className = 'text-muted small mb-2';
            previewTitle.textContent = `Selected images (${input.files.length}):`;
            previewContainer.appendChild(previewTitle);
            
            const previewWrapper = document.createElement('div');
            previewWrapper.className = 'd-flex flex-wrap gap-2';
            
            Array.from(input.files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const imgWrapper = document.createElement('div');
                        imgWrapper.className = 'position-relative d-inline-block';
                        imgWrapper.innerHTML = `
                            <img
                                src="${e.target.result}"
                                alt="${file.name}"
                                style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;"
                                class="img-thumbnail"
                            >
                            <span class="badge bg-secondary position-absolute bottom-0 start-0 m-1" style="font-size: 0.7rem;">${file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name}</span>
                        `;
                        previewWrapper.appendChild(imgWrapper);
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            previewContainer.appendChild(previewWrapper);
        }
    };

    const addSizeCard = () => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = sizeTemplate(sizeCounter).trim();
        const newCard = wrapper.firstElementChild;
        sizesContainer.appendChild(newCard);
        
        // Attach preview handler to newly added size image input
        const newInput = newCard.querySelector('.size-images-input');
        if (newInput) {
            newInput.addEventListener('change', function() {
                handleImagePreview(this);
            });
        }
        
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

    // Add event listeners for image preview on existing inputs
    document.querySelectorAll('.size-images-input').forEach(input => {
        input.addEventListener('change', function() {
            handleImagePreview(this);
        });
    });

    if (addSizeButtons && addSizeButtons.length && sizesContainer) {
        addSizeButtons.forEach((btn) => btn.addEventListener('click', () => addSizeCard()));
    }

    if (sizesContainer) {
        sizesContainer.addEventListener('click', (event) => {
            if (event.target.closest('.add-item-btn')) {
                const button = event.target.closest('.add-item-btn');
                const sizeCard = button.closest('.booth-size-card');
                addItemRow(sizeCard);
                return;
            }

            if (event.target.closest('.remove-item-btn')) {
                const itemRow = event.target.closest('.item-row');
                const itemsContainer = itemRow.parentElement;
                itemRow.remove();
                if (!itemsContainer.querySelector('.item-row')) {
                    const sizeCard = itemsContainer.closest('.booth-size-card');
                    addItemRow(sizeCard);
                }
                return;
            }

            if (event.target.closest('.remove-size-btn')) {
                const sizeCard = event.target.closest('.booth-size-card');
                sizeCard.remove();
                if (!sizesContainer.querySelector('.booth-size-card')) {
                    sizeCounter = 0;
                    addSizeCard();
                }
                return;
            }

            // Handle existing size image remove (clicking the Remove button under existing images)
            const removeBtn = event.target.closest('.size-image-remove-btn');
            if (removeBtn) {
                const sizeIndex = removeBtn.getAttribute('data-size-index');
                const imagePath = removeBtn.getAttribute('data-image-path');
                const wrapper = removeBtn.closest('.size-image-wrapper');
                const form = document.getElementById('step2Form');

                if (form && sizeIndex !== null && imagePath) {
                    // Add a hidden field to indicate this image should be removed
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = `booth_sizes[${sizeIndex}][remove_existing_images][]`;
                    hidden.value = imagePath;
                    form.appendChild(hidden);
                }

                // Immediately hide the image row from UI so the user sees it's removed
                if (wrapper) {
                    wrapper.remove();
                }
            }
        });
    }

    const addonTemplate = (idx) => `
        <div class="row g-3 align-items-end addon-row" data-addon-index="${idx}">
            <div class="col-md-5">
                <label class="form-label">Service</label>
                <select name="addon_services[${idx}][service_id]" class="form-select">
                    <option value="">Select service</option>
                    @foreach($services as $configuredService)
                        <option value="{{ $configuredService->id }}">{{ $configuredService->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Price per quantity</label>
                <input type="number" step="0.01" name="addon_services[${idx}][price_per_quantity]" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cut-off date</label>
                <input type="date" name="addon_services[${idx}][cutoff_date]" class="form-control">
            </div>
            <div class="col-md-1 text-end">
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
