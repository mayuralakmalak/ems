@extends('layouts.admin')

@section('title', 'Discount Details')
@section('page-title', 'Discount Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-tag me-2"></i>Discount Details</h5>
        <a href="{{ route('admin.discounts.edit', $discount->id) }}" class="btn btn-light">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <strong>Discount Code:</strong>
                <p>{{ $discount->code }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Discount Name:</strong>
                <p>{{ $discount->name }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Discount Percent:</strong>
                <p>{{ $discount->discount_percent }}%</p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Status:</strong>
                <p>
                    <span class="badge bg-{{ $discount->status === 'active' ? 'success' : ($discount->status === 'completed' ? 'info' : 'secondary') }}">
                        {{ ucfirst($discount->status) }}
                    </span>
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Start Date:</strong>
                <p>{{ $discount->start_date->format('d M Y') }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>End Date:</strong>
                <p>{{ $discount->end_date->format('d M Y') }}</p>
            </div>
            @if($discount->description)
            <div class="col-12 mb-3">
                <strong>Description:</strong>
                <p>{{ $discount->description }}</p>
            </div>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>
</div>
@endsection
