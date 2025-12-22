@extends('layouts.admin')

@section('title', 'Edit Sponsorship')
@section('page-title', 'Edit Sponsorship Package')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Sponsorship</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.sponsorships.update', $sponsorship->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Exhibition <span class="text-danger">*</span></label>
                    <select name="exhibition_id" class="form-select @error('exhibition_id') is-invalid @enderror" required>
                        <option value="">Select Exhibition</option>
                        @foreach($exhibitions as $exhibition)
                            <option value="{{ $exhibition->id }}" {{ old('exhibition_id', $sponsorship->exhibition_id) == $exhibition->id ? 'selected' : '' }}>
                                {{ $exhibition->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('exhibition_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $sponsorship->name) }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tier</label>
                    <input type="text" name="tier" class="form-control @error('tier') is-invalid @enderror" value="{{ old('tier', $sponsorship->tier) }}" placeholder="e.g. Bronze, Silver, Gold">
                    @error('tier')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $sponsorship->price) }}" required>
                    @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Max Available</label>
                    <input type="number" name="max_available" min="1" class="form-control @error('max_available') is-invalid @enderror" value="{{ old('max_available', $sponsorship->max_available) }}" placeholder="Leave blank for unlimited">
                    @error('max_available')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if($sponsorship->image)
                        <small class="text-muted d-block mt-1">Current: <a href="{{ asset('storage/' . $sponsorship->image) }}" target="_blank">View Image</a></small>
                    @endif
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Short description of the sponsorship package">{{ old('description', $sponsorship->description) }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Deliverables <span class="text-danger">*</span></label>
                    <small class="text-muted d-block mb-2">Enter one deliverable per line (e.g. Logo on website, Social media mentions)</small>
                    <textarea name="deliverables[]" rows="4" class="form-control @error('deliverables') is-invalid @enderror" placeholder="Enter deliverables, one per line">{{ old('deliverables.0', is_array($sponsorship->deliverables) ? implode("\n", $sponsorship->deliverables) : $sponsorship->deliverables) }}</textarea>
                    @error('deliverables')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $sponsorship->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Update Sponsorship
                </button>
                <a href="{{ route('admin.sponsorships.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
