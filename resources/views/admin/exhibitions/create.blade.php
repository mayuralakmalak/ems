@extends('layouts.admin')

@section('title', 'Create Exhibition - Step 1')
@section('page-title', 'Create New Exhibition - Step 1 of 4')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 25%"></div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            <small class="text-primary fw-bold" style="padding: 8px 16px;color: white; border-radius: 4px;">Step 1: Exhibition Details</small>
            <small class="text-muted" style="padding: 8px 16px;">Step 2: Hall Plan & Pricing</small>
            <small class="text-muted" style="padding: 8px 16px;">Step 3: Payment Schedule</small>
            <small class="text-muted" style="padding: 8px 16px;">Step 4: Badge & Manual</small>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Exhibition General Information</h5>
            <small class="text-white-50">Provide basic details about your exhibition event</small>
        </div>
        <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to list
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.exhibitions.store') }}" method="POST" id="exhibitionForm">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-tag text-primary me-1"></i>Exhibition Name <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Exhibition Name"
                        value="{{ old('name') }}"
                        required
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar-event text-primary me-1"></i>Start Date <span class="text-danger">*</span>
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        class="form-control"
                        value="{{ old('start_date') }}"
                        required
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar-check text-primary me-1"></i>End Date <span class="text-danger">*</span>
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        class="form-control"
                        value="{{ old('end_date') }}"
                        required
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock text-primary me-1"></i>Start Time
                    </label>
                    <input
                        type="time"
                        name="start_time"
                        class="form-control"
                        value="{{ old('start_time') }}"
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock-history text-primary me-1"></i>End Time
                    </label>
                    <input
                        type="time"
                        name="end_time"
                        class="form-control"
                        value="{{ old('end_time') }}"
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-building text-primary me-1"></i>Venue <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="venue"
                        class="form-control"
                        placeholder="Venue"
                        value="{{ old('venue') }}"
                        required
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-geo-alt text-primary me-1"></i>City <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="city"
                        class="form-control"
                        placeholder="City"
                        value="{{ old('city') }}"
                        required
                    >
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-geo text-primary me-1"></i>State / Province
                    </label>
                    <select name="state" id="state" class="form-control" data-value-field="name">
                        <option value="">Select State</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-globe text-primary me-1"></i>Country <span class="text-danger">*</span>
                    </label>
                    <select name="country" id="country" class="form-control" required>
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}" data-id="{{ $country->id }}" {{ old('country') == $country->name ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-file-text text-primary me-1"></i>Description
                    </label>
                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="Rich text editor placeholder..."
                    >{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                <a href="{{ route('admin.exhibitions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Save and Continue to Step 2 <i class="bi bi-arrow-right ms-2"></i>
                </button>
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
                
                // If end date is already selected and is before the new min date, clear it
                if (endDateInput.value && new Date(endDateInput.value) <= startDate) {
                    endDateInput.value = '';
                }
            } else {
                endDateInput.removeAttribute('min');
            }
        });
    }
    
    // Make country and state dropdowns searchable
    makeSelectSearchable('country', 'Type country name...');
    
    // Initialize country-state functionality
    if (typeof applyCountryState === 'function') {
        applyCountryState();
        
        // Make state dropdown searchable after states are loaded
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
        
        // Initialize state searchable after initial load
        setTimeout(function() {
            const stateSelect = document.getElementById('state');
            if (stateSelect) {
                makeSelectSearchable('state', 'Type state name...');
            }
        }, 500);
    }
});
</script>
@endpush
@endsection

