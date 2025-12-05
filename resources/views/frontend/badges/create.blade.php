@extends('layouts.exhibitor')

@section('title', 'Create Badge')
@section('page-title', 'Create New Badge')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Create New Badge</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('badges.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Booking *</label>
                <select name="booking_id" class="form-select" required>
                    <option value="">Select Booking</option>
                    @foreach($bookings as $booking)
                    <option value="{{ $booking->id }}">{{ $booking->booking_number }} - {{ $booking->exhibition->name ?? 'N/A' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Badge Type *</label>
                <select name="badge_type" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="Primary">Primary</option>
                    <option value="Secondary">Secondary</option>
                    <option value="Additional">Additional</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>

            <div class="mb-3">
                <label class="form-label">Valid For Date</label>
                <input type="date" name="valid_for_date" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Access Permissions</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="access_permissions[]" value="Entry Only" id="entry">
                    <label class="form-check-label" for="entry">Entry Only</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="access_permissions[]" value="Lunch" id="lunch">
                    <label class="form-check-label" for="lunch">Lunch</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="access_permissions[]" value="Snacks" id="snacks">
                    <label class="form-check-label" for="snacks">Snacks</label>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('badges.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Badge</button>
            </div>
        </form>
    </div>
</div>
@endsection

