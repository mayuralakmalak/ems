@extends('layouts.admin')

@section('title', 'Exhibitor Profile')
@section('page-title', 'Exhibitor Profile')

@section('content')
<ul class="nav nav-tabs mb-4" id="exhibitorTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button">Contact</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="booth-tab" data-bs-toggle="tab" data-bs-target="#booth" type="button">Booth</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button">Bookings</button>
    </li>
</ul>

<div class="tab-content" id="exhibitorTabContent">
    <div class="tab-pane fade show active" id="contact" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Contact Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.exhibitors.update-contact', $exhibitor->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $exhibitor->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ $exhibitor->email }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ $exhibitor->phone }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ $exhibitor->company_name }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Industry</label>
                            <input type="text" name="industry" class="form-control" value="{{ $exhibitor->industry }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ $exhibitor->city }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ $exhibitor->state }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ $exhibitor->country }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ $exhibitor->address }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Contact</button>
                </form>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="booth" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Booth Assignment</h5>
            </div>
            <div class="card-body">
                @if($bookings->count() > 0)
                @foreach($bookings as $booking)
                <form action="{{ route('admin.exhibitors.update-booth', $exhibitor->id) }}" method="POST" class="mb-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exhibition <span class="text-danger">*</span></label>
                            <select name="exhibition_id" class="form-select" required>
                                @foreach($exhibitions as $exhibition)
                                <option value="{{ $exhibition->id }}" {{ $booking->exhibition_id == $exhibition->id ? 'selected' : '' }}>{{ $exhibition->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Booth <span class="text-danger">*</span></label>
                            <select name="booth_id" class="form-select" required>
                                @foreach($booths as $booth)
                                <option value="{{ $booth->id }}" {{ $booking->booth_id == $booth->id ? 'selected' : '' }}>{{ $booth->name }} ({{ $booth->size_sqft }} sqft)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" step="0.01" min="0" class="form-control" value="{{ $booking->total_amount }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount</label>
                            <select name="discount_id" class="form-select">
                                <option value="">No Discount</option>
                                @foreach($discounts as $discount)
                                <option value="{{ $discount->id }}">{{ $discount->code }} ({{ $discount->discount_percent }}%)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Booth Assignment</button>
                </form>
                @endforeach
                @else
                <p class="text-muted">No bookings found for this exhibitor.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="bookings" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Booking History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking #</th>
                                <th>Exhibition</th>
                                <th>Booth</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_number }}</td>
                                <td>{{ $booking->exhibition->name ?? '-' }}</td>
                                <td>{{ $booking->booth->name ?? '-' }}</td>
                                <td>₹{{ number_format($booking->total_amount, 0) }}</td>
                                <td>
                                    <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
