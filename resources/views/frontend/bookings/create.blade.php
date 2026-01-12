@extends('layouts.exhibitor')

@section('title', 'Book Booth')
@section('page-title', 'Book Booth - ' . $exhibition->name)

@push('styles')
<style>
    .form-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .section-description {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 25px;
    }
    
    .form-label {
        font-weight: 500;
        color: #334155;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .form-control, .form-select {
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }
    
    .upload-area {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .upload-area:hover {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .upload-area.dragover {
        border-color: #6366f1;
        background: #e0f2fe;
    }
    
    .upload-icon {
        font-size: 3rem;
        color: #94a3b8;
        margin-bottom: 15px;
    }
    
    .upload-text {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 5px;
    }
    
    .upload-hint {
        color: #94a3b8;
        font-size: 0.85rem;
    }
    
    .file-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px;
        background: #f8fafc;
        border-radius: 8px;
        margin-top: 10px;
    }
    
    .file-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .file-icon {
        font-size: 1.5rem;
        color: #6366f1;
    }
    
    .file-details {
        flex: 1;
    }
    
    .file-name {
        font-weight: 500;
        color: #1e293b;
        font-size: 0.95rem;
    }
    
    .file-size {
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .remove-file {
        background: #fee2e2;
        color: #991b1b;
        border: none;
        border-radius: 6px;
        padding: 6px 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .remove-file:hover {
        background: #fecaca;
    }
    
    .terms-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 25px;
    }
    
    .terms-checkbox input[type="checkbox"] {
        margin-top: 3px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .terms-checkbox label {
        flex: 1;
        color: #334155;
        font-size: 0.95rem;
        cursor: pointer;
    }
    
    .terms-checkbox a {
        color: #6366f1;
        text-decoration: none;
    }
    
    .terms-checkbox a:hover {
        text-decoration: underline;
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        padding-top: 25px;
        border-top: 1px solid #e2e8f0;
    }
    
    .btn-back {
        padding: 12px 30px;
        background: white;
        border: 2px solid #e2e8f0;
        color: #64748b;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    
    .btn-continue {
        padding: 12px 30px;
        background: #6366f1;
        border: none;
        color: white;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-continue:hover {
        background: #4f46e5;
    }
    
    .btn-continue:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('bookings.store') }}" method="POST" id="bookingForm" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="exhibition_id" value="{{ $exhibition->id }}">
    
    @if(request()->has('booths'))
        @foreach(explode(',', request()->get('booths')) as $boothId)
            <input type="hidden" name="booth_ids[]" value="{{ $boothId }}">
        @endforeach
    @endif
    
    <!-- Booth Selection -->
    <div class="form-section">
        <h3 class="section-title">Select Booth</h3>
        <p class="section-description">Choose the booth(s) you want to book for this exhibition.</p>
        
        @php
            $availableBooths = $exhibition->booths->where('is_available', true);
        @endphp
        
        @if($availableBooths->count() > 0)
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="booth_selection" class="form-label">Available Booths *</label>
                    <select class="form-select" id="booth_selection" name="booth_ids[]" multiple required size="5" style="min-height: 150px;">
                        @foreach($availableBooths as $booth)
                            <option value="{{ $booth->id }}" 
                                @if(request()->has('booths') && in_array($booth->id, explode(',', request()->get('booths')))) selected @endif>
                                {{ $booth->name }} - {{ $booth->category ?? 'Standard' }} ({{ $booth->size_sqft ?? 'N/A' }} sq meter) - ₹{{ number_format($booth->price ?? 0, 0) }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">Hold Ctrl/Cmd (Windows/Mac) to select multiple booths. You can also select booths from the hall plan.</small>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>No booths are currently available for this exhibition. Please contact the administrator.
            </div>
        @endif
    </div>
    
    <!-- Company Information -->
    <div class="form-section">
        <h3 class="section-title">Company Information</h3>
        <p class="section-description">Provide your company details for the booking.</p>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="company_name" class="form-label">Company Name *</label>
                <input type="text" class="form-control" id="company_name" name="company_name" 
                       value="{{ auth()->user()->company_name ?? old('company_name') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="company_website" class="form-label">Company Website</label>
                <input type="url" class="form-control" id="company_website" name="company_website" 
                       value="{{ auth()->user()->website ?? old('company_website') }}">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="company_address" class="form-label">Company Address *</label>
                <input type="text" class="form-control" id="company_address" name="company_address" 
                       value="{{ auth()->user()->address ?? old('company_address') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="company_city" class="form-label">City *</label>
                <input type="text" class="form-control" id="company_city" name="company_city" 
                       value="{{ auth()->user()->city ?? old('company_city') }}" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="company_state" class="form-label">State *</label>
                <input type="text" class="form-control" id="company_state" name="company_state" 
                       value="{{ auth()->user()->state ?? old('company_state') }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="company_country" class="form-label">Country *</label>
                <input type="text" class="form-control" id="company_country" name="company_country" 
                       value="{{ auth()->user()->country ?? old('company_country') }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="company_pincode" class="form-label">Zip Code *</label>
                <input type="text" class="form-control" id="company_pincode" name="company_pincode" 
                       value="{{ auth()->user()->pincode ?? old('company_pincode') }}" required>
            </div>
        </div>
    </div>
    
    <!-- Primary Contact Person -->
    <div class="form-section">
        <h3 class="section-title">Primary Contact Person</h3>
        <p class="section-description">Main contact for booking coordination and communication.</p>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="contact_name" class="form-label">Full Name *</label>
                <input type="text" class="form-control" id="contact_name" name="contact_name" 
                       value="{{ auth()->user()->name ?? old('contact_name') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="contact_designation" class="form-label">Designation</label>
                <input type="text" class="form-control" id="contact_designation" name="contact_designation" 
                       value="{{ old('contact_designation') }}">
            </div>
        </div>
        
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_emails[]" 
                               value="{{ auth()->user()->email ?? old('contact_emails.0') }}" required>
                        <small class="text-muted">You can add more contact emails below if needed.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contact_phone" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="contact_phone" name="contact_numbers[]" 
                               value="{{ auth()->user()->phone ?? old('contact_numbers.0') }}" required>
                        <small class="text-muted">You can add more contact numbers below if needed.</small>
                    </div>
                </div>
                <div class="row" id="additionalContacts">
                    <!-- Additional contact emails and numbers will be added here via JavaScript -->
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addContactBtn">
                        <i class="bi bi-plus-circle me-1"></i>Add Additional Contact
                    </button>
                </div>
    </div>
    
    <!-- Additional Requirements -->
    <div class="form-section">
        <h3 class="section-title">Additional Requirements</h3>
        <p class="section-description">Any special requirements or requests for your booking.</p>
        
        <div class="mb-3">
            <label for="additional_requirements" class="form-label">Special Requirements</label>
            <textarea class="form-control" id="additional_requirements" name="additional_requirements" 
                      rows="4" placeholder="Enter any special requirements or requests...">{{ old('additional_requirements') }}</textarea>
        </div>
    </div>
    
    <!-- Company Logo Upload -->
    <div class="form-section">
        <h3 class="section-title">Company Logo</h3>
        <p class="section-description">Upload your company logo (PNG, JPG, max 5MB).</p>
        
        <div class="upload-area" id="logoUploadArea">
            <i class="bi bi-cloud-upload upload-icon"></i>
            <div class="upload-text">Drag & drop your logo here</div>
            <div class="upload-hint">or click to browse</div>
            <input type="file" id="logoFile" name="logo" accept="image/png,image/jpeg,image/jpg" style="display: none;">
        </div>
        <div id="logoPreview"></div>
    </div>
    
    <!-- Promotional Brochures Upload -->
    <div class="form-section">
        <h3 class="section-title">Promotional Brochures</h3>
        <p class="section-description">Upload promotional brochures (PDF, max 5 files, 5MB each).</p>
        
        <div class="upload-area" id="brochureUploadArea">
            <i class="bi bi-file-earmark-pdf upload-icon"></i>
            <div class="upload-text">Drag & drop brochures here</div>
            <div class="upload-hint">or click to browse (max 5 files)</div>
            <input type="file" id="brochureFiles" name="brochures[]" accept="application/pdf" multiple style="display: none;">
        </div>
        <div id="brochurePreview"></div>
    </div>
    
    <!-- Terms & Conditions -->
    <div class="terms-checkbox">
        <input type="checkbox" id="terms" name="terms" required>
        <label for="terms">
            I agree to the <a href="#" target="_blank">Terms & Conditions</a> and <a href="#" target="_blank">Privacy Policy</a> *
        </label>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <a href="{{ route('exhibitions.show', $exhibition->id) }}" class="btn btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button type="submit" class="btn btn-continue" id="submitBtn">
            Continue to Payment <i class="bi bi-arrow-right ms-2"></i>
        </button>
    </div>
</form>

@push('scripts')
<script>
// Logo upload
const logoUploadArea = document.getElementById('logoUploadArea');
const logoFile = document.getElementById('logoFile');
const logoPreview = document.getElementById('logoPreview');

logoUploadArea.addEventListener('click', () => logoFile.click());
logoUploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    logoUploadArea.classList.add('dragover');
});
logoUploadArea.addEventListener('dragleave', () => {
    logoUploadArea.classList.remove('dragover');
});
logoUploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    logoUploadArea.classList.remove('dragover');
    if (e.dataTransfer.files.length > 0) {
        logoFile.files = e.dataTransfer.files;
        handleLogoFile(e.dataTransfer.files[0]);
    }
});

logoFile.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleLogoFile(e.target.files[0]);
    }
});

function handleLogoFile(file) {
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB');
        return;
    }
    
    if (!file.type.match('image.*')) {
        alert('Please upload an image file');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        logoPreview.innerHTML = `
            <div class="file-preview">
                <div class="file-info">
                    <i class="bi bi-file-image file-icon"></i>
                    <div class="file-details">
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${(file.size / 1024).toFixed(2)} KB</div>
                    </div>
                </div>
                <button type="button" class="remove-file" onclick="removeLogo()">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    };
    reader.readAsDataURL(file);
}

function removeLogo() {
    logoFile.value = '';
    logoPreview.innerHTML = '';
}

// Brochure upload
const brochureUploadArea = document.getElementById('brochureUploadArea');
const brochureFiles = document.getElementById('brochureFiles');
const brochurePreview = document.getElementById('brochurePreview');
let brochureFilesList = [];

brochureUploadArea.addEventListener('click', () => brochureFiles.click());
brochureUploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    brochureUploadArea.classList.add('dragover');
});
brochureUploadArea.addEventListener('dragleave', () => {
    brochureUploadArea.classList.remove('dragover');
});
brochureUploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    brochureUploadArea.classList.remove('dragover');
    if (e.dataTransfer.files.length > 0) {
        handleBrochureFiles(Array.from(e.dataTransfer.files));
    }
});

brochureFiles.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleBrochureFiles(Array.from(e.target.files));
    }
});

function handleBrochureFiles(files) {
    files.forEach(file => {
        if (brochureFilesList.length >= 3) {
            alert('Maximum 3 files allowed');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            alert(`${file.name} is larger than 5MB`);
            return;
        }
        
        if (file.type !== 'application/pdf') {
            alert(`${file.name} is not a PDF file`);
            return;
        }
        
        brochureFilesList.push(file);
        addBrochurePreview(file);
    });
    
    updateBrochureInput();
}

function addBrochurePreview(file) {
    const div = document.createElement('div');
    div.className = 'file-preview';
    div.innerHTML = `
        <div class="file-info">
            <i class="bi bi-file-earmark-pdf file-icon"></i>
            <div class="file-details">
                <div class="file-name">${file.name}</div>
                <div class="file-size">${(file.size / 1024).toFixed(2)} KB</div>
            </div>
        </div>
        <button type="button" class="remove-file" onclick="removeBrochure('${file.name}')">
            <i class="bi bi-trash"></i>
        </button>
    `;
    div.dataset.fileName = file.name;
    brochurePreview.appendChild(div);
}

function removeBrochure(fileName) {
    brochureFilesList = brochureFilesList.filter(f => f.name !== fileName);
    document.querySelector(`[data-file-name="${fileName}"]`).remove();
    updateBrochureInput();
}

function updateBrochureInput() {
    const dt = new DataTransfer();
    brochureFilesList.forEach(file => dt.items.add(file));
    brochureFiles.files = dt.files;
}

// Add additional contacts
let contactCount = 0;
document.getElementById('addContactBtn').addEventListener('click', function() {
    if (contactCount >= 4) {
        alert('Maximum 5 contacts allowed');
        return;
    }
    
    const container = document.getElementById('additionalContacts');
    const row = document.createElement('div');
    row.className = 'row mb-2';
    row.innerHTML = `
        <div class="col-md-6 mb-2">
            <input type="email" class="form-control" name="contact_emails[]" placeholder="Additional Email (Optional)">
        </div>
        <div class="col-md-5 mb-2">
            <input type="tel" class="form-control" name="contact_numbers[]" placeholder="Additional Phone (Optional)">
        </div>
        <div class="col-md-1 mb-2">
            <button type="button" class="btn btn-sm btn-danger w-100" onclick="this.closest('.row').remove(); contactCount--;">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    contactCount++;
});

// Auto-select first booth if coming from query parameter
@if(request()->has('booths'))
    document.addEventListener('DOMContentLoaded', function() {
        const boothIds = '{{ request()->get('booths') }}'.split(',');
        const boothSelection = document.getElementById('booth_selection');
        if (boothSelection) {
            boothIds.forEach(boothId => {
                const option = boothSelection.querySelector(`option[value="${boothId.trim()}"]`);
                if (option) {
                    option.selected = true;
                }
            });
        }
    });
@endif

// Form validation
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const terms = document.getElementById('terms');
    if (!terms.checked) {
        e.preventDefault();
        alert('Please accept the Terms & Conditions');
        return false;
    }
    
    // Check if at least one booth is selected
    const boothSelection = document.getElementById('booth_selection');
    if (!boothSelection || boothSelection.selectedOptions.length === 0) {
        e.preventDefault();
        alert('Please select at least one booth to book');
        return false;
    }
});
</script>
@endpush
@endsection
