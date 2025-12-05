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
            <h5>Step 2</h5>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left side - Floorplan Editor Area (Empty for now) -->
    <div class="col-md-6 mb-4">
        <div class="card" style="min-height: 500px;">
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="text-center text-muted">
                    <p>Floorplan editor area</p>
                    <small>Interactive editor for drawing booth boundaries will be implemented here</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Right side - Configuration Form -->
    <div class="col-md-6">
        <form action="{{ route('admin.exhibitions.step2.store', $exhibition->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Floorplan Management -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Floorplan Management</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Upload Floorplan Image</label>
                        <input type="file" name="floorplan_image" class="form-control" accept="image/*">
                        @if($exhibition->floorplan_image)
                            <small class="text-muted">Current: {{ basename($exhibition->floorplan_image) }}</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Intensive editor for drawing booth boundaries, assigning numbers, setting sizes, merging/splitting stalls</label>
                        <textarea name="floorplan_editor_data" class="form-control" rows="8" placeholder="Interactive editor placeholder - This will be implemented with a canvas-based drawing tool"></textarea>
                    </div>
                </div>
            </div>

            <!-- Booth Configuration -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Booth Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Booth category</label>
                            <select name="booth_category" class="form-select">
                                <option value="">Select Category</option>
                                <option value="Premium">Premium</option>
                                <option value="Standard">Standard</option>
                                <option value="Economy">Economy</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Size</label>
                            <input type="number" name="booth_size" class="form-control" step="0.01" placeholder="Size in sq ft">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input type="text" name="booth_category_name" class="form-control" placeholder="Category">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Side open pricing % adjustment</label>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text">Rear</span>
                                    <input type="number" name="rear_price_per_sqft" class="form-control" step="0.01" placeholder="Price/sq ft" value="{{ $exhibition->raw_price_per_sqft ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text">Orphaned</span>
                                    <input type="number" name="orphaned_price_per_sqft" class="form-control" step="0.01" placeholder="Price/sq ft" value="{{ $exhibition->orphand_price_per_sqft ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="free_booth" id="free_booth" value="1">
                        <label class="form-check-label" for="free_booth">Free Booth</label>
                    </div>
                </div>
            </div>

            <!-- Pricing Configuration -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Pricing Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Base price per sq ft</label>
                        <input type="number" name="price_per_sqft" class="form-control" step="0.01" placeholder="eg. 100" value="{{ $exhibition->price_per_sqft ?? '' }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Side Open Variations (% adjustment)</label>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text">1 Side Open</span>
                                    <input type="number" name="side_1_open_percent" class="form-control" step="0.01" placeholder="%" value="{{ $exhibition->side_1_open_percent ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text">2 Sides Open</span>
                                    <input type="number" name="side_2_open_percent" class="form-control" step="0.01" placeholder="%" value="{{ $exhibition->side_2_open_percent ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text">3 Sides Open</span>
                                    <input type="number" name="side_3_open_percent" class="form-control" step="0.01" placeholder="%" value="{{ $exhibition->side_3_open_percent ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text">4 Sides Open</span>
                                    <input type="number" name="side_4_open_percent" class="form-control" step="0.01" placeholder="%" value="{{ $exhibition->side_4_open_percent ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category Pricing</label>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Premium</td>
                                        <td>
                                            <input type="number" name="premium_price" class="form-control" step="0.01" placeholder="Price" value="{{ $exhibition->premium_price ?? '' }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Standard</td>
                                        <td>
                                            <input type="number" name="standard_price" class="form-control" step="0.01" placeholder="Price" value="{{ $exhibition->standard_price ?? '' }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Economy</td>
                                        <td>
                                            <input type="number" name="economy_price" class="form-control" step="0.01" placeholder="Price" value="{{ $exhibition->economy_price ?? '' }}">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
