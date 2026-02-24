@extends('layouts.frontend')

@section('title', $typeLabel . ' Registration')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-gradient-purple text-white py-4">
                    <h1 class="h4 mb-0">{{ $typeLabel }} Registration</h1>
                    <p class="mb-0 opacity-90 small">Register for an upcoming exhibition. Admin approval required.</p>
                </div>
                <div class="card-body p-4 p-lg-5">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
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

                    <form action="{{ route('register.store', $type) }}" method="POST" enctype="multipart/form-data" id="registrationForm">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-600">Select Exhibition <span class="text-danger">*</span></label>
                            <select name="exhibition_id" id="exhibition_id" class="form-select form-select-lg" required>
                                <option value="">-- Choose upcoming exhibition --</option>
                                @foreach($exhibitions as $ex)
                                    <option value="{{ $ex->id }}" data-name="{{ $ex->name }}">
                                        {{ $ex->name }} ({{ $ex->start_date->format('d M Y') }} - {{ $ex->end_date->format('d M Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @if($exhibitions->isEmpty())
                                <small class="text-muted">No upcoming exhibitions available at the moment.</small>
                            @endif
                        </div>

                        <div id="feeDisplay" class="alert alert-info mb-4" style="display: none;">
                            <strong>Registration fee:</strong> <span id="feeAmount">—</span>
                            <span id="feeTierLabel" class="ms-2"></span>
                            <p class="mb-0 mt-2 small">Payment options (NEFT, RTGS, Online, etc.) will be shown on the next step after you submit this form. Admin approval is required for all payments.</p>
                        </div>

                        <h5 class="mb-3 mt-4">Personal Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required maxlength="20">
                            </div>
                            <div class="col-12">
                                <label class="form-label">ID Proof (PDF / JPG / PNG, max 2MB) <span class="text-danger">*</span></label>
                                <input type="file" name="id_proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Additional Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company / Organization</label>
                                <input type="text" name="company" class="form-control" value="{{ old('company') }}" maxlength="200">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" value="{{ old('designation') }}" maxlength="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city') }}" maxlength="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" value="{{ old('state') }}" maxlength="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country') }}" maxlength="100">
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-top">
                            <button type="submit" class="btn btn-custom bg-gradient-purple px-4">
                                Submit Registration
                            </button>
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const exhibitionId = document.getElementById('exhibition_id');
    const feeDisplay = document.getElementById('feeDisplay');
    const feeAmount = document.getElementById('feeAmount');
    const feeTierLabel = document.getElementById('feeTierLabel');

    exhibitionId.addEventListener('change', function() {
        const id = this.value;
        if (!id) {
            feeDisplay.style.display = 'none';
            return;
        }
        fetch('{{ url("/register/fee") }}?' + new URLSearchParams({
            exhibition_id: id,
            type: '{{ $type }}',
            _token: '{{ csrf_token() }}'
        }), {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            feeDisplay.style.display = 'block';
            feeAmount.textContent = '₹' + parseFloat(data.fee).toFixed(2);
            if (data.tier_label) {
                feeTierLabel.textContent = '(' + data.tier_label + ')';
                feeTierLabel.style.display = 'inline';
            } else {
                feeTierLabel.style.display = 'none';
            }
        })
        .catch(() => {
            feeDisplay.style.display = 'block';
            feeAmount.textContent = '—';
            feeTierLabel.style.display = 'none';
        });
    });
});
</script>
@endpush
@endsection
