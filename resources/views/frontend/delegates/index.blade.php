@extends('layouts.exhibitor')

@section('title', 'Delegates - ' . $booking->booking_number)
@section('page-title', 'Delegates for ' . $booking->exhibition->name)

@push('styles')
<style>
    .delegate-summary-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .delegate-summary-item {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.95rem;
    }
    .delegate-summary-item:last-child {
        border-bottom: none;
    }
    .delegates-table th {
        background: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="delegate-summary-card">
            <h5 class="mb-3">Delegate Summary</h5>
            <div class="delegate-summary-item">
                <span>Free delegates allowed</span>
                <strong>{{ $freeLimit }}</strong>
            </div>
            <div class="delegate-summary-item">
                <span>Free delegates used</span>
                <strong>{{ $freeUsed }}</strong>
            </div>
            <div class="delegate-summary-item">
                <span>Paid delegates</span>
                <strong>{{ $paidUsed }}</strong>
            </div>
            <div class="delegate-summary-item">
                <span>Additional delegate fee</span>
                <strong>₹{{ number_format($paidFee, 2) }}</strong>
            </div>
            <div class="delegate-summary-item">
                <span>Total delegates</span>
                <strong>{{ $totalDelegates }}</strong>
            </div>
            <p class="small text-muted mt-3">
                First <strong>{{ $freeLimit }}</strong> delegates are free as per exhibition settings.
                Any additional delegates will be charged at the configured fee.
            </p>
        </div>

        <div class="delegate-summary-card">
            <h6 class="mb-3">Add Delegate</h6>
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('bookings.delegates.store', $booking->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control form-control-sm" value="{{ old('first_name') }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control form-control-sm" value="{{ old('last_name') }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone') }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">ID Proof (PDF / JPG / PNG, max 2MB) <span class="text-danger">*</span></label>
                    <input type="file" name="id_proof" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Company</label>
                    <input type="text" name="company" class="form-control form-control-sm" value="{{ old('company') }}">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Designation</label>
                    <input type="text" name="designation" class="form-control form-control-sm" value="{{ old('designation') }}">
                </div>
                <div class="mb-2">
                    <label class="form-label small">City</label>
                    <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city') }}">
                </div>
                <div class="mb-2">
                    <label class="form-label small">State</label>
                    <input type="text" name="state" class="form-control form-control-sm" value="{{ old('state') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Country</label>
                    <input type="text" name="country" class="form-control form-control-sm" value="{{ old('country') }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    Add Delegate
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Delegates List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm delegates-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Fee</th>
                                <th>Payment Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($delegates as $delegate)
                                <tr>
                                    <td>{{ $delegate->full_name }}</td>
                                    <td>{{ $delegate->email }}</td>
                                    <td>{{ $delegate->phone }}</td>
                                    <td>
                                        @if($delegate->fee_amount <= 0)
                                            <span class="badge bg-success">Free</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Paid</span>
                                        @endif
                                    </td>
                                    <td>₹{{ number_format($delegate->fee_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $delegate->payment_status === 'paid' ? 'success' : ($delegate->payment_status === 'partial' ? 'info' : 'secondary') }}">
                                            {{ ucfirst($delegate->payment_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $delegate->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No delegates added yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

