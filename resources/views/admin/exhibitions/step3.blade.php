@extends('layouts.admin')

@section('title', 'Create Exhibition - Step 3')
@section('page-title', 'Create Exhibition - Step 3: Payment Schedule')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Step 3: Payment Schedule Setup</h5>
        <small class="text-muted">Number of payment parts is fixed and cannot be changed after creation</small>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.exhibitions.step3.store', $exhibition->id) }}" method="POST" id="paymentScheduleForm">
            @csrf
            <div class="mb-3">
                <label class="form-label">Number of Payment Parts <span class="text-danger">*</span></label>
                <input type="number" name="payment_parts" id="payment_parts" class="form-control" min="1" max="10" value="3" required>
            </div>

            <div id="paymentPartsContainer">
                <!-- Payment parts will be added here dynamically -->
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.exhibitions.step2', $exhibition->id) }}" class="btn btn-secondary">Back</a>
                <button type="submit" class="btn btn-primary">Save and Continue to Step 4</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    function generatePaymentParts() {
        const parts = parseInt($('#payment_parts').val()) || 3;
        let html = '';
        
        for (let i = 1; i <= parts; i++) {
            html += `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Part ${i}: Percentage <span class="text-danger">*</span></label>
                        <input type="number" name="parts[${i-1}][percentage]" class="form-control" step="0.01" min="0" max="100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Part ${i}: Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="parts[${i-1}][due_date]" class="form-control" required>
                    </div>
                </div>
            `;
        }
        
        $('#paymentPartsContainer').html(html);
    }
    
    $('#payment_parts').on('change', generatePaymentParts);
    generatePaymentParts();
});
</script>
@endpush
@endsection

