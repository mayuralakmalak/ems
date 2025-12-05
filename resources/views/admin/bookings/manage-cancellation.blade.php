@extends('layouts.admin')

@section('title', 'Manage Cancellation')
@section('page-title', 'Manage Cancellation - ' . $booking->booking_number)

@push('styles')
<style>
    .cancellation-form {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        font-weight: 500;
        color: #334155;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        width: 100%;
    }
    
    .btn-submit {
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 500;
        border: none;
        cursor: pointer;
    }
    
    .btn-approve {
        background: #6366f1;
        color: white;
    }
    
    .btn-reject {
        background: #ef4444;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="cancellation-form">
    <h3 class="mb-4">Manage Cancellation - {{ $booking->booking_number }}</h3>
    
    <form method="POST" action="{{ route('admin.bookings.approve-cancellation', $booking->id) }}">
        @csrf
        
        <div class="form-group">
            <label class="form-label">Refund Type</label>
            <select name="cancellation_type" class="form-select" required>
                <option value="refund">Full Refund (Bank Transfer)</option>
                <option value="wallet_credit">Credit to Wallet</option>
            </select>
        </div>
        
        <div class="form-group" id="accountDetailsGroup">
            <label class="form-label">Account Details</label>
            <textarea name="account_details" class="form-control" rows="4" placeholder="Enter bank account details for refund..."></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Admin Notes</label>
            <textarea name="admin_notes" class="form-control" rows="4" placeholder="Enter internal notes..."></textarea>
        </div>
        
        <div class="d-flex gap-3">
            <button type="submit" class="btn-submit btn-approve">Approve Cancellation</button>
            <a href="{{ route('admin.bookings.cancellations') }}" class="btn-submit" style="background: #f3f4f6; color: #1e293b; text-decoration: none; display: inline-block;">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelector('select[name="cancellation_type"]').addEventListener('change', function() {
    const accountGroup = document.getElementById('accountDetailsGroup');
    if (this.value === 'refund') {
        accountGroup.style.display = 'block';
        accountGroup.querySelector('textarea').required = true;
    } else {
        accountGroup.style.display = 'none';
        accountGroup.querySelector('textarea').required = false;
    }
});
</script>
@endpush
@endsection

