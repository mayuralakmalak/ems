@extends('layouts.exhibitor')

@section('title', 'Sponsorship Details')
@section('page-title', 'Sponsorship Details')

@push('styles')
<style>
    .sponsorship-detail-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    
    .sponsorship-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .sponsorship-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
    }
    
    .sponsorship-tier {
        display: inline-block;
        padding: 6px 16px;
        background: #6366f1;
        color: white;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .sponsorship-price {
        font-size: 2.5rem;
        font-weight: 700;
        color: #6366f1;
    }
    
    .deliverables-list {
        list-style: none;
        padding: 0;
    }
    
    .deliverables-list li {
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .deliverables-list li:last-child {
        border-bottom: none;
    }
    
    .deliverables-list li i {
        color: #10b981;
        font-size: 1.2rem;
    }
</style>
@endpush

@section('content')
<div class="sponsorship-detail-card">
    <div class="sponsorship-header">
        <div>
            <h1 class="sponsorship-title">{{ $sponsorship->name }}</h1>
            @if($sponsorship->tier)
                <span class="sponsorship-tier">{{ $sponsorship->tier }} Tier</span>
            @endif
            <div class="sponsorship-price">₹{{ number_format($sponsorship->price, 0) }}</div>
        </div>
    </div>
    
    @if($sponsorship->description)
    <div class="mb-4">
        <h3 class="mb-3">Description</h3>
        <p class="text-muted">{{ $sponsorship->description }}</p>
    </div>
    @endif
    
    <div class="mb-4">
        <h3 class="mb-3">Key Deliverables</h3>
        <ul class="deliverables-list">
            @if(is_array($sponsorship->deliverables))
                @foreach($sponsorship->deliverables as $deliverable)
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ $deliverable }}</span>
                </li>
                @endforeach
            @else
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ $sponsorship->deliverables }}</span>
                </li>
            @endif
        </ul>
    </div>
    
    <div class="d-flex gap-3 mt-4">
        <a href="{{ route('sponsorships.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
        <a href="{{ route('sponsorships.book', $sponsorship->id) }}" class="btn btn-primary">
            <i class="bi bi-check-circle me-2"></i>Book This Package
        </a>
    </div>
</div>
@endsection

