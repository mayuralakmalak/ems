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
                    <h6 class="mb-0"><i class="bi bi-building me-2"></i>Hall Management</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addFloorBtn">
                        <i class="bi bi-plus-circle me-1"></i>Add Hall
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Manage multiple halls for this exhibition. Each hall can have its own hall plan and booth configuration.</p>
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
                                <div class="col-md-3">
                                    <label class="form-label">Hall Name <span class="text-danger">*</span></label>
                                    <input type="text" name="floors[{{ $floorIndex }}][name]" class="form-control floor-name-input" 
                                           value="{{ $floor->name }}" required>
                                    <input type="hidden" name="floors[{{ $floorIndex }}][id]" value="{{ $floor->id }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Hall Number <span class="text-danger">*</span></label>
                                    <input type="number" name="floors[{{ $floorIndex }}][floor_number]" class="form-control" 
                                           value="{{ $floor->floor_number }}" min="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="floors[{{ $floorIndex }}][description]" class="form-control" 
                                           value="{{ $floor->description }}" placeholder="Optional description">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Width (meters)</label>
                                    <input type="number" step="0.01" name="floors[{{ $floorIndex }}][width_meters]" class="form-control" 
                                           value="{{ $floor->width_meters }}" placeholder="e.g. 50.00" min="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Height (meters)</label>
                                    <input type="number" step="0.01" name="floors[{{ $floorIndex }}][height_meters]" class="form-control" 
                                           value="{{ $floor->height_meters }}" placeholder="e.g. 30.00" min="0">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Active</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="floors[{{ $floorIndex }}][is_active]" 
                                               value="1" {{ $floor->is_active ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label">Hall Background Image</label>
                                    <input type="file" name="floors[{{ $floorIndex }}][background_image]" 
                                           class="form-control floor-background-image-input" 
                                           accept="image/*" 
                                           data-floor-index="{{ $floorIndex }}">
                                    <div class="floor-background-preview mt-2" data-floor-index="{{ $floorIndex }}">
                                        @if($floor->background_image)
                                            <div class="existing-image-preview">
                                                <small class="text-muted d-block mb-1">Current image:</small>
                                                <img src="{{ asset('storage/' . ltrim($floor->background_image, '/')) }}" 
                                                     alt="Floor background" 
                                                     style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="floors[{{ $floorIndex }}][remove_background_image]" 
                                                           value="1" 
                                                           id="remove_bg_{{ $floorIndex }}">
                                                    <label class="form-check-label" for="remove_bg_{{ $floorIndex }}">
                                                        Remove background image
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="border rounded p-3 mb-3 floor-item" data-floor-id="new-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="bi bi-layers me-2"></i>Hall #1</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger remove-floor-btn">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Hall Name <span class="text-danger">*</span></label>
                                    <input type="text" name="floors[0][name]" class="form-control floor-name-input" 
                                           value="Ground Floor" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Hall Number <span class="text-danger">*</span></label>
                                    <input type="number" name="floors[0][floor_number]" class="form-control" 
                                           value="0" min="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="floors[0][description]" class="form-control" 
                                           placeholder="Optional description">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Width (meters)</label>
                                    <input type="number" step="0.01" name="floors[0][width_meters]" class="form-control" 
                                           placeholder="e.g. 50.00" min="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Height (meters)</label>
                                    <input type="number" step="0.01" name="floors[0][height_meters]" class="form-control" 
                                           placeholder="e.g. 30.00" min="0">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Active</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="floors[0][is_active]" 
                                               value="1" checked>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label">Hall Background Image</label>
                                    <input type="file" name="floors[0][background_image]" 
                                           class="form-control floor-background-image-input" 
                                           accept="image/*" 
                                           data-floor-index="0">
                                    <div class="floor-background-preview mt-2" data-floor-index="0"></div>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addFloorBtnBottom">
                            <i class="bi bi-plus-circle me-1"></i>Add Hall
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
                    <!-- Base prices per meter (raw & shell) -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Base Price Raw (per meter)</label>
                            <input type="number"
                                   name="raw_price_per_sqft"
                                   class="form-control"
                                   step="0.01"
                                   placeholder="e.g. 100"
                                   value="{{ $exhibition->raw_price_per_sqft ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Base Price Shell (per meter)</label>
                            <input type="number"
                                   name="orphand_price_per_sqft"
                                   class="form-control"
                                   step="0.01"
                                   placeholder="e.g. 120"
                                   value="{{ $exhibition->orphand_price_per_sqft ?? '' }}">
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-muted mb-2">Size (sq meter) with Raw Price, Shell Price, category, and multiple items. Use Add size to manage multiple entries.</p>
                        <div id="boothSizesContainer">
                            @forelse($exhibition->boothSizes as $sizeIndex => $boothSize)
                            <div class="border rounded p-3 mb-3 booth-size-card" data-size-index="{{ $sizeIndex }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Size #{{ $loop->iteration }}</h6>
                                    <button type="button" class="btn btn-sm btn-link text-danger remove-size-btn">Remove size</button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Size (sq meter)</label>
                                        <input type="number" step="0.01" name="booth_sizes[{{ $sizeIndex }}][size_sqft]" class="form-control size-input" data-size-index="{{ $sizeIndex }}" value="{{ $boothSize->size_sqft }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Raw Price</label>
                                        <input type="number" step="0.01" name="booth_sizes[{{ $sizeIndex }}][row_price]" class="form-control raw-price-input" data-size-index="{{ $sizeIndex }}" value="{{ $boothSize->row_price }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Shell Price</label>
                                        <input type="number" step="0.01" name="booth_sizes[{{ $sizeIndex }}][orphan_price]" class="form-control shell-price-input" data-size-index="{{ $sizeIndex }}" value="{{ $boothSize->orphan_price }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Category</label>
                                        <select name="booth_sizes[{{ $sizeIndex }}][category]" class="form-select category-select" data-size-index="{{ $sizeIndex }}">
                                            <option value="">Select</option>
                                            <option value="1" @selected($boothSize->category === '1' || $boothSize->category === 'Premium')>Premium</option>
                                            <option value="2" @selected($boothSize->category === '2' || $boothSize->category === 'Standard' || empty($boothSize->category))>Standard</option>
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
                                        <label class="form-label">Size (sq meter)</label>
                                        <input type="number" step="0.01" name="booth_sizes[0][size_sqft]" class="form-control size-input" data-size-index="0" placeholder="e.g. 100">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Raw Price</label>
                                        <input type="number" step="0.01" name="booth_sizes[0][row_price]" class="form-control raw-price-input" data-size-index="0" placeholder="e.g. 5000">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Shell Price</label>
                                        <input type="number" step="0.01" name="booth_sizes[0][orphan_price]" class="form-control shell-price-input" data-size-index="0" placeholder="e.g. 4000">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Category</label>
                                        <select name="booth_sizes[0][category]" class="form-select category-select" data-size-index="0">
                                            <option value="">Select</option>
                                            <option value="1">Premium</option>
                                            <option value="2" selected>Standard</option>
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
                                <label class="form-label">Base price per sq meter</label>
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
                <h6 class="mb-0"><i class="bi bi-layers me-2"></i>Hall #${floorIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-link text-danger remove-floor-btn">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Hall Name <span class="text-danger">*</span></label>
                    <input type="text" name="floors[${floorIndex}][name]" class="form-control floor-name-input" 
                           value="Hall ${floorIndex + 1}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hall Number <span class="text-danger">*</span></label>
                    <input type="number" name="floors[${floorIndex}][floor_number]" class="form-control" 
                           value="${floorIndex}" min="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="floors[${floorIndex}][description]" class="form-control" 
                           placeholder="Optional description">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Width (meters)</label>
                    <input type="number" step="0.01" name="floors[${floorIndex}][width_meters]" class="form-control" 
                           placeholder="e.g. 50.00" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Height (meters)</label>
                    <input type="number" step="0.01" name="floors[${floorIndex}][height_meters]" class="form-control" 
                           placeholder="e.g. 30.00" min="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Active</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="floors[${floorIndex}][is_active]" 
                               value="1" checked>
                    </div>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-12">
                    <label class="form-label">Hall Background Image</label>
                    <input type="file" name="floors[${floorIndex}][background_image]" 
                           class="form-control floor-background-image-input" 
                           accept="image/*" 
                           data-floor-index="${floorIndex}">
                    <div class="floor-background-preview mt-2" data-floor-index="${floorIndex}"></div>
                </div>
            </div>
        </div>
    `;

    // Handle floor background image preview
    const handleFloorBackgroundPreview = (input) => {
        const floorIndex = input.getAttribute('data-floor-index');
        const previewContainer = document.querySelector(`.floor-background-preview[data-floor-index="${floorIndex}"]`);
        
        if (!previewContainer) return;
        
        // Clear previous new image preview (keep existing image preview if it exists)
        const newImagePreview = previewContainer.querySelector('.new-image-preview');
        if (newImagePreview) {
            newImagePreview.remove();
        }
        
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const previewWrapper = document.createElement('div');
                    previewWrapper.className = 'new-image-preview';
                    previewWrapper.innerHTML = `
                        <small class="text-muted d-block mb-1">New image preview:</small>
                        <img src="${e.target.result}" 
                             alt="Preview" 
                             style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;">
                    `;
                    previewContainer.appendChild(previewWrapper);
                };
                reader.readAsDataURL(file);
            }
        }
    };

    // Attach preview handlers to existing floor background image inputs
    document.querySelectorAll('.floor-background-image-input').forEach(input => {
        input.addEventListener('change', function() {
            handleFloorBackgroundPreview(this);
        });
    });

    // Handle remove background image checkbox
    document.querySelectorAll('input[name*="[remove_background_image]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const floorItem = this.closest('.floor-item');
            if (floorItem) {
                const existingPreview = floorItem.querySelector('.existing-image-preview');
                if (existingPreview) {
                    existingPreview.style.display = this.checked ? 'none' : 'block';
                }
            }
        });
    });

    const addFloor = () => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = floorTemplate(floorCounter).trim();
        const newFloorItem = wrapper.firstElementChild;
        floorsContainer.appendChild(newFloorItem);
        
        // Attach preview handler to newly added floor background image input
        const newBackgroundInput = newFloorItem.querySelector('.floor-background-image-input');
        if (newBackgroundInput) {
            newBackgroundInput.addEventListener('change', function() {
                handleFloorBackgroundPreview(this);
            });
        }
        
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
                    <label class="form-label">Size (sq meter)</label>
                    <input type="number" step="0.01" name="booth_sizes[${sizeIndex}][size_sqft]" class="form-control size-input" data-size-index="${sizeIndex}" placeholder="e.g. 100">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Raw Price</label>
                    <input type="number" step="0.01" name="booth_sizes[${sizeIndex}][row_price]" class="form-control raw-price-input" data-size-index="${sizeIndex}" placeholder="e.g. 5000">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shell Price</label>
                    <input type="number" step="0.01" name="booth_sizes[${sizeIndex}][orphan_price]" class="form-control shell-price-input" data-size-index="${sizeIndex}" placeholder="e.g. 4000">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="booth_sizes[${sizeIndex}][category]" class="form-select category-select" data-size-index="${sizeIndex}">
                        <option value="">Select</option>
                        <option value="1">Premium</option>
                        <option value="2" selected>Standard</option>
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

    // Auto-calculation functions for size prices
    const getBasePrices = () => {
        const baseRawPrice = parseFloat(document.querySelector('input[name="raw_price_per_sqft"]')?.value) || 0;
        const baseShellPrice = parseFloat(document.querySelector('input[name="orphand_price_per_sqft"]')?.value) || 0;
        return { baseRawPrice, baseShellPrice };
    };

    const calculateSizePrices = (sizeIndex) => {
        const { baseRawPrice, baseShellPrice } = getBasePrices();
        
        // Check if base prices exist
        if (baseRawPrice === 0 && baseShellPrice === 0) {
            return; // Don't calculate if base prices don't exist
        }

        const sizeCard = document.querySelector(`.booth-size-card[data-size-index="${sizeIndex}"]`);
        if (!sizeCard) return;

        const categorySelect = sizeCard.querySelector('.category-select');
        const sizeInput = sizeCard.querySelector('.size-input');
        const rawPriceInput = sizeCard.querySelector('.raw-price-input');
        const shellPriceInput = sizeCard.querySelector('.shell-price-input');

        if (!categorySelect || !sizeInput || !rawPriceInput || !shellPriceInput) return;

        const category = categorySelect.value;
        const size = parseFloat(sizeInput.value) || 0;

        // Only auto-calculate if category is "Standard" (value "2")
        if (category === '2' && size > 0) {
            const calculatedRawPrice = baseRawPrice * size;
            const calculatedShellPrice = baseShellPrice * size;
            
            rawPriceInput.value = calculatedRawPrice.toFixed(2);
            shellPriceInput.value = calculatedShellPrice.toFixed(2);
        }
    };

    const handleCategoryChange = (sizeIndex) => {
        const sizeCard = document.querySelector(`.booth-size-card[data-size-index="${sizeIndex}"]`);
        if (!sizeCard) return;

        const categorySelect = sizeCard.querySelector('.category-select');
        if (!categorySelect) return;

        const category = categorySelect.value;

        // If changed to Standard, recalculate prices
        if (category === '2') {
            calculateSizePrices(sizeIndex);
        }
        // If changed to Premium/Economy, prices remain editable (no action needed)
    };

    const setupSizeEventListeners = (sizeCard) => {
        const sizeIndex = sizeCard.getAttribute('data-size-index');
        if (!sizeIndex) return;

        const sizeInput = sizeCard.querySelector('.size-input');
        const categorySelect = sizeCard.querySelector('.category-select');

        if (sizeInput) {
            sizeInput.addEventListener('input', () => {
                const category = categorySelect?.value;
                if (category === '2') {
                    calculateSizePrices(sizeIndex);
                }
            });
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', () => {
                handleCategoryChange(sizeIndex);
            });
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
        
        // Setup auto-calculation event listeners
        setupSizeEventListeners(newCard);
        
        // Auto-calculate for new size if base prices exist and category is Standard
        const categorySelect = newCard.querySelector('.category-select');
        if (categorySelect && categorySelect.value === '2') {
            calculateSizePrices(sizeCounter);
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

    // Setup auto-calculation event listeners for existing size cards
    document.querySelectorAll('.booth-size-card').forEach(sizeCard => {
        setupSizeEventListeners(sizeCard);
    });

    // Recalculate all Standard category sizes when base prices change
    const baseRawPriceInput = document.querySelector('input[name="raw_price_per_sqft"]');
    const baseShellPriceInput = document.querySelector('input[name="orphand_price_per_sqft"]');
    
    if (baseRawPriceInput) {
        baseRawPriceInput.addEventListener('input', () => {
            document.querySelectorAll('.booth-size-card').forEach(sizeCard => {
                const sizeIndex = sizeCard.getAttribute('data-size-index');
                const categorySelect = sizeCard.querySelector('.category-select');
                if (categorySelect && categorySelect.value === '2' && sizeIndex !== null) {
                    calculateSizePrices(sizeIndex);
                }
            });
        });
    }
    
    if (baseShellPriceInput) {
        baseShellPriceInput.addEventListener('input', () => {
            document.querySelectorAll('.booth-size-card').forEach(sizeCard => {
                const sizeIndex = sizeCard.getAttribute('data-size-index');
                const categorySelect = sizeCard.querySelector('.category-select');
                if (categorySelect && categorySelect.value === '2' && sizeIndex !== null) {
                    calculateSizePrices(sizeIndex);
                }
            });
        });
    }

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
