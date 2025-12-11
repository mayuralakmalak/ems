@extends('layouts.exhibitor')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@push('styles')
<style>
    .profile-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .profile-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 700;
        margin: 0 auto 15px;
        border: 4px solid white;
    }
    
    .profile-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        font-weight: 500;
        color: #334155;
        margin-bottom: 8px;
        display: block;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    
    .btn-save {
        background: #6366f1;
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-save:hover {
        background: #4f46e5;
        transform: translateY(-2px);
    }
    
    .photo-preview {
        width: 150px;
        height: 150px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid #e2e8f0;
        margin-top: 10px;
    }
</style>
@endpush

@section('content')
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <h3>{{ auth()->user()->name }}</h3>
        <p class="mb-0">{{ auth()->user()->email }}</p>
    </div>
    
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        
        <!-- Personal Information -->
        <div class="profile-card">
            <h5 class="section-title">Personal Information</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}">
                        @error('phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewPhoto(this)">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="Profile Photo" class="photo-preview" id="photoPreview">
                        @else
                            <img src="" alt="Profile Photo" class="photo-preview" id="photoPreview" style="display: none;">
                        @endif
                        @error('photo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Company Information -->
        <div class="profile-card">
            <h5 class="section-title">Company Information</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', auth()->user()->company_name) }}">
                        @error('company_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" value="{{ old('website', auth()->user()->website) }}">
                        @error('website')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Company Description</label>
                <textarea name="company_description" class="form-control" rows="4">{{ old('company_description', auth()->user()->company_description) }}</textarea>
                @error('company_description')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>
        
        <!-- Address Information -->
        <div class="profile-card">
            <h5 class="section-title">Address Information</h5>
            
            <div class="form-group">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', auth()->user()->address) }}">
                @error('address')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', auth()->user()->city) }}">
                        @error('city')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', auth()->user()->state) }}">
                        @error('state')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', auth()->user()->country) }}">
                        @error('country')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', auth()->user()->pincode) }}">
                        @error('pincode')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tax Information -->
        <div class="profile-card">
            <h5 class="section-title">Tax Information</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">GST Number</label>
                        <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', auth()->user()->gst_number) }}">
                        @error('gst_number')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">PAN Number</label>
                        <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', auth()->user()->pan_number) }}">
                        @error('pan_number')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-end">
            <button type="submit" class="btn-save">
                <i class="bi bi-check-circle me-2"></i>Save Changes
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photoPreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
