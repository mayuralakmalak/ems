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

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save and Continue to Step 3</button>
            </div>
        </form>
    </div>
</div>
@endsection

