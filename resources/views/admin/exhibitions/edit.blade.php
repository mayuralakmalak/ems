@extends('layouts.admin')

@section('title', 'Edit Exhibition')
@section('page-title', 'Edit Exhibition')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 25%"></div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            <a href="{{ route('admin.exhibitions.edit', $exhibition->id) }}" class="text-primary fw-bold text-decoration-none" style="padding: 8px 16px;color: white; border-radius: 4px;">Step 1: Exhibition Details</a>
            <a href="{{ route('admin.exhibitions.step2', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 2: Hall Plan & Pricing</a>
            <a href="{{ route('admin.exhibitions.step3', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 3: Payment Schedule</a>
            <a href="{{ route('admin.exhibitions.step4', $exhibition->id) }}" class="text-muted text-decoration-none" style="padding: 8px 16px;">Step 4: Badge & Manual</a>
        </div>
    </div>
</div>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Exhibitions
    </a>
</div>

<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Exhibition Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.exhibitions.update', $exhibition->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_step2" value="1">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-tag text-primary me-1"></i>Exhibition Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $exhibition->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-building text-primary me-1"></i>Venue Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="venue" class="form-control @error('venue') is-invalid @enderror" 
                           value="{{ old('venue', $exhibition->venue) }}" required>
                    @error('venue')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-file-text text-primary me-1"></i>Description
                    </label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $exhibition->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-geo-alt text-primary me-1"></i>City <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" 
                           value="{{ old('city', $exhibition->city) }}" required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-geo text-primary me-1"></i>State / Province
                    </label>
                    <select name="state" id="state" class="form-control @error('state') is-invalid @enderror" data-value-field="name">
                        <option value="">Select State</option>
                        @foreach($states as $state)
                            <option value="{{ $state->name }}" {{ old('state', $exhibition->state) == $state->name ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('state')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-globe text-primary me-1"></i>Country <span class="text-danger">*</span>
                    </label>
                    <select name="country" id="country" class="form-control @error('country') is-invalid @enderror" required>
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}" data-id="{{ $country->id }}" {{ old('country', $exhibition->country) == $country->name ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar3 text-primary me-1"></i>Start Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                           value="{{ old('start_date', $exhibition->start_date->format('Y-m-d')) }}" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar-check text-primary me-1"></i>End Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                           value="{{ old('end_date', $exhibition->end_date->format('Y-m-d')) }}" required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock text-primary me-1"></i>Start Time
                    </label>
                    <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                           value="{{ old('start_time', $exhibition->start_time) }}">
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock-history text-primary me-1"></i>End Time
                    </label>
                    <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                           value="{{ old('end_time', $exhibition->end_time) }}">
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-tag text-primary me-1"></i>Status
                    </label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="draft" {{ old('status', $exhibition->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status', $exhibition->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ old('status', $exhibition->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $exhibition->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.exhibitions.step2', $exhibition->id) }}" class="btn btn-outline-primary">
                        Go to Step 2
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update & Continue
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/country-state.js') }}"></script>
<script>
// Make select dropdowns searchable
function makeSelectSearchable(selectId, searchPlaceholder) {
    const select = document.getElementById(selectId);
    if (!select) return;
    
    if (select.hasAttribute('data-searchable-initialized')) {
        return;
    }
    select.setAttribute('data-searchable-initialized', 'true');
    
    let originalOptions = Array.from(select.options).map(opt => ({
        value: opt.value,
        text: opt.textContent,
        selected: opt.selected,
        dataId: opt.getAttribute('data-id')
    }));
    
    const seen = new Set();
    originalOptions = originalOptions.filter(opt => {
        if (opt.value === '') return true;
        if (seen.has(opt.value)) return false;
        seen.add(opt.value);
        return true;
    });
    
    select.addEventListener('mousedown', function(e) {
        e.preventDefault();
        showSearch();
    });
    
    select.addEventListener('focus', function(e) {
        e.preventDefault();
        showSearch();
    });
    
    select.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            showSearch();
        }
    });
    
    function showSearch() {
        const existingOverlay = document.getElementById(selectId + '_search_overlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }
        
        const overlay = document.createElement('div');
        overlay.id = selectId + '_search_overlay';
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;';
        
        const container = document.createElement('div');
        container.style.cssText = 'background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 400px; max-height: 80vh; display: flex; flex-direction: column;';
        
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = searchPlaceholder || 'Type to search...';
        searchInput.style.cssText = 'padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px;';
        
        const results = document.createElement('div');
        results.id = selectId + '_results';
        results.style.cssText = 'max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px;';
        
        container.appendChild(searchInput);
        container.appendChild(results);
        overlay.appendChild(container);
        document.body.appendChild(overlay);
        
        setTimeout(() => searchInput.focus(), 100);
        
        function displayOptions(filterTerm = '') {
            results.innerHTML = '';
            const term = filterTerm.toLowerCase().trim();
            const displayedValues = new Set();
            
            originalOptions.forEach(function(opt) {
                if (opt.value === '') {
                    if (!filterTerm) {
                        const item = document.createElement('div');
                        item.style.cssText = 'padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;';
                        item.textContent = opt.text;
                        item.addEventListener('click', function() {
                            select.value = '';
                            overlay.remove();
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                        results.appendChild(item);
                    }
                    return;
                }
                
                if (displayedValues.has(opt.value)) return;
                
                if (!term || opt.text.toLowerCase().includes(term)) {
                    displayedValues.add(opt.value);
                    const item = document.createElement('div');
                    item.style.cssText = 'padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;';
                    item.textContent = opt.text;
                    if (opt.value === select.value) {
                        item.style.backgroundColor = '#e3f2fd';
                    }
                    item.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#f5f5f5';
                    });
                    item.addEventListener('mouseleave', function() {
                        if (opt.value !== select.value) {
                            this.style.backgroundColor = '';
                        }
                    });
                    item.addEventListener('click', function() {
                        select.value = opt.value;
                        overlay.remove();
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    results.appendChild(item);
                }
            });
        }
        
        displayOptions();
        
        searchInput.addEventListener('input', function() {
            displayOptions(this.value);
        });
        
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.remove();
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                overlay.remove();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    
    if (startDateInput && endDateInput) {
        // Set initial min date if start date is already selected
        if (startDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const minEndDate = new Date(startDate);
            minEndDate.setDate(minEndDate.getDate() + 1);
            endDateInput.setAttribute('min', minEndDate.toISOString().split('T')[0]);
        }
        
        // Update min date when start date changes
        startDateInput.addEventListener('change', function() {
            if (this.value) {
                const startDate = new Date(this.value);
                const minEndDate = new Date(startDate);
                minEndDate.setDate(minEndDate.getDate() + 1);
                endDateInput.setAttribute('min', minEndDate.toISOString().split('T')[0]);
                
                // If end date is already selected and is before or equal to start date, clear it
                if (endDateInput.value && new Date(endDateInput.value) <= startDate) {
                    endDateInput.value = '';
                }
            } else {
                endDateInput.removeAttribute('min');
            }
        });
    }
    
    // Set old state value for country-state.js
    const stateSelect = document.getElementById('state');
    if (stateSelect && stateSelect.value) {
        stateSelect.setAttribute('data-old-value', stateSelect.value);
    }
    
    // Make country and state dropdowns searchable
    makeSelectSearchable('country', 'Type country name...');
    makeSelectSearchable('state', 'Type state name...');
    
    // Initialize country-state functionality
    if (typeof applyCountryState === 'function') {
        applyCountryState();
        
        // Make state dropdown searchable after states are reloaded
        const countrySelect = document.getElementById('country');
        if (countrySelect) {
            countrySelect.addEventListener('change', function() {
                setTimeout(function() {
                    const stateSelect = document.getElementById('state');
                    if (stateSelect) {
                        const oldOverlay = document.getElementById('state_search_overlay');
                        if (oldOverlay) {
                            oldOverlay.remove();
                        }
                        stateSelect.removeAttribute('data-searchable-initialized');
                        makeSelectSearchable('state', 'Type state name...');
                    }
                }, 400);
            });
        }
    }
});
</script>
@endpush
@endsection
