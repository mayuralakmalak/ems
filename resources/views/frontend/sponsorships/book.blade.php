@extends('layouts.exhibitor')

@section('title', 'Book Sponsorship')
@section('page-title', 'Book Sponsorship - ' . $sponsorship->name)

@push('styles')
<style>
    .form-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .summary-card {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .summary-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .summary-item:last-child {
        border-bottom: none;
        font-weight: 700;
        font-size: 1.1rem;
        color: #6366f1;
        margin-top: 10px;
        padding-top: 15px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="form-section">
            <h3 class="mb-4">Booking Information</h3>
            
            <form action="{{ route('sponsorships.store', $sponsorship->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                @if($booking)
                <div class="mb-3">
                    <label class="form-label">Link to Booth Booking (Optional)</label>
                    <select name="booking_id" class="form-select">
                        <option value="">No Link</option>
                        <option value="{{ $booking->id }}" selected>Booking #{{ $booking->booking_number }}</option>
                    </select>
                </div>
                @endif
                
                <div class="mb-3">
                    <label class="form-label">Contact Emails <span class="text-danger">*</span></label>
                    <small class="text-muted d-block mb-2">Enter at least 1 email address (one per line, up to 5)</small>
                    <textarea name="contact_emails_text" id="contact_emails_text" rows="3" class="form-control @error('contact_emails') is-invalid @enderror" placeholder="email1@example.com&#10;email2@example.com" required>{{ old('contact_emails_text') }}</textarea>
                    <input type="hidden" name="contact_emails" id="contact_emails" value="">
                    @error('contact_emails')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Contact Numbers <span class="text-danger">*</span></label>
                    <small class="text-muted d-block mb-2">Enter at least 1 phone number (one per line, up to 5)</small>
                    <textarea name="contact_numbers_text" id="contact_numbers_text" rows="3" class="form-control @error('contact_numbers') is-invalid @enderror" placeholder="+91 1234567890&#10;+91 9876543210" required>{{ old('contact_numbers_text') }}</textarea>
                    <input type="hidden" name="contact_numbers" id="contact_numbers" value="">
                    @error('contact_numbers')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Logo (Optional)</label>
                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    @error('logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Additional Notes (Optional)</label>
                    <textarea name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="Any special requirements or notes...">{{ old('notes') }}</textarea>
                    @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Proceed to Payment
                    </button>
                    <a href="{{ route('sponsorships.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="summary-card">
            <div class="summary-title">Booking Summary</div>
            <div class="summary-item">
                <span>Package:</span>
                <span>{{ $sponsorship->name }}</span>
            </div>
            <div class="summary-item">
                <span>Exhibition:</span>
                <span>{{ $sponsorship->exhibition->name }}</span>
            </div>
            @if($sponsorship->tier)
            <div class="summary-item">
                <span>Tier:</span>
                <span>{{ $sponsorship->tier }}</span>
            </div>
            @endif
            <div class="summary-item">
                <span>Total Amount:</span>
                <span>₹{{ number_format($sponsorship->price, 2) }}</span>
            </div>
        </div>
        
        <div class="form-section">
            <h5 class="mb-3">Deliverables Included</h5>
            <ul class="list-unstyled">
                @if(is_array($sponsorship->deliverables))
                    @foreach($sponsorship->deliverables as $deliverable)
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ $deliverable }}
                    </li>
                    @endforeach
                @else
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ $sponsorship->deliverables }}
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>

@endsection

