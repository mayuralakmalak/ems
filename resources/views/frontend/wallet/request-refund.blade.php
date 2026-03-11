@extends('layouts.frontend')

@section('title', 'Request Refund')

@section('content')
<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-2">Request Refund of Special Discount</h2>
            <p class="text-muted">You can request a refund of this special discount amount without cancelling your booth.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Discount Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8">₹{{ number_format($wallet->amount, 2) }}</dd>

                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">
                            Special discount credited to wallet
                        </dd>

                        <dt class="col-sm-4">Date</dt>
                        <dd class="col-sm-8">
                            {{ $wallet->created_at->format('d M Y, h:i A') }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Submit Refund Request</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('wallet.refund.submit', $wallet->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason (optional)</label>
                            <textarea name="reason" id="reason" rows="4" class="form-control" placeholder="Explain why you are requesting refund for this discount...">{{ old('reason') }}</textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('wallet.index') }}" class="btn btn-outline-secondary">Back to Wallet</a>
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

