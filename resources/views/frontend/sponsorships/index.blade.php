@extends('layouts.exhibitor')

@section('title', 'Sponsorship Management Page')
@section('page-title', 'Sponsorship Management Page')

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .nav-tabs {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .nav-tab {
        padding: 12px 24px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        bottom: -2px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .nav-tab:hover {
        color: #6366f1;
    }
    
    .nav-tab.active {
        color: #6366f1;
        border-bottom-color: #6366f1;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 10px;
    }
    
    .section-description {
        color: #64748b;
        font-size: 1rem;
        margin-bottom: 40px;
    }
    
    .sponsorship-tiers {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .sponsorship-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .sponsorship-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .sponsorship-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
    }
    
    .sponsorship-price {
        font-size: 2rem;
        font-weight: 700;
        color: #6366f1;
        margin-bottom: 20px;
    }
    
    .deliverables-section {
        margin-bottom: 20px;
    }
    
    .deliverables-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
    }
    
    .deliverable-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
        color: #64748b;
        font-size: 0.95rem;
    }
    
    .deliverable-item i {
        color: #6366f1;
        margin-top: 3px;
    }
    
    .benefits-section {
        margin-bottom: 25px;
    }
    
    .benefit-badge {
        display: inline-block;
        padding: 6px 12px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        margin-right: 8px;
        margin-bottom: 8px;
    }
    
    .btn-select {
        width: 100%;
        padding: 12px;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-select:hover {
        background: #4f46e5;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h2 class="mb-0">Sponsorship Management Page</h2>
    <div>
        <i class="bi bi-bell me-2" style="font-size: 1.5rem; color: #6366f1;"></i>
        <i class="bi bi-envelope" style="font-size: 1.5rem; color: #6366f1;"></i>
    </div>
</div>

<div class="nav-tabs">
    <a href="{{ route('home') }}" class="nav-tab">
        <i class="bi bi-cube"></i>ExhiBook
    </a>
    <a href="{{ route('sponsorships.index') }}" class="nav-tab active">
        <i class="bi bi-check-circle"></i>Sponsorships
    </a>
    <a href="{{ route('messages.index') }}" class="nav-tab">
        <i class="bi bi-chat"></i>Communication
    </a>
</div>

<div>
    <h3 class="section-title">Sponsorship Opportunities</h3>
    <p class="section-description">Explore our tailored sponsorship packages designed to maximize your brand's visibility and impact.</p>
    
    <div class="sponsorship-tiers">
        @foreach($sponsorships as $sponsorship)
        <div class="sponsorship-card">
            <div class="sponsorship-title">{{ $sponsorship->name }}</div>
            <div class="sponsorship-price">₹{{ number_format($sponsorship->price, 0) }}</div>
            
            <div class="deliverables-section">
                <div class="deliverables-title">Key Deliverables:</div>
                @if(is_array($sponsorship->deliverables))
                    @foreach($sponsorship->deliverables as $deliverable)
                    <div class="deliverable-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ $deliverable }}</span>
                    </div>
                    @endforeach
                @else
                    <div class="deliverable-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ $sponsorship->deliverables }}</span>
                    </div>
                @endif
            </div>
            
            <div class="benefits-section">
                @if($sponsorship->tier === 'Bronze')
                    <span class="benefit-badge">Increased visibility</span>
                    <span class="benefit-badge">Community engagement</span>
                @elseif($sponsorship->tier === 'Silver')
                    <span class="benefit-badge">Enhanced brand recognition</span>
                    <span class="benefit-badge">Direct audience interaction</span>
                @elseif($sponsorship->tier === 'Gold')
                    <span class="benefit-badge">Maximized exposure</span>
                    <span class="benefit-badge">Leadership positioning</span>
                    <span class="benefit-badge">Exclusive networking</span>
                @endif
            </div>
            
            <form action="{{ route('sponsorships.select', $sponsorship->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-select">Select Package</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection

