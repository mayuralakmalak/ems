@extends('layouts.frontend')

@section('title', 'Exhibitions - ' . config('app.name', 'EMS'))

@section('content')
<div class="container py-5">
    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <h1 class="h3 fw-bold mb-2 text-slate-800">All Exhibitions</h1>
            <p class="text-muted mb-0">Browse all active exhibitions and view their details.</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($exhibitions as $exhibition)
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-img-top" style="height: 180px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display:flex; align-items:center; justify-content:center; color:#fff;">
                    @if($exhibition->floorplan_image)
                        <img src="{{ asset('storage/' . $exhibition->floorplan_image) }}" alt="{{ $exhibition->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="bi bi-calendar-event" style="font-size: 2.5rem;"></i>
                    @endif
                </div>
                <div class="card-body">
                    <h5 class="card-title fw-semibold">{{ $exhibition->name }}</h5>
                    <p class="text-muted mb-1">{{ optional($exhibition->start_date)->format('d M Y') }} - {{ optional($exhibition->end_date)->format('d M Y') }}</p>
                    <p class="text-muted small mb-3">{{ $exhibition->venue }}</p>
                    <a href="{{ route('exhibitions.show', $exhibition->id) }}" class="btn btn-primary w-100">View Details</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">No exhibitions are currently available.</div>
        </div>
        @endforelse
    </div>

    @if($exhibitions->hasPages())
    <div class="mt-4">
        {{ $exhibitions->links() }}
    </div>
    @endif
</div>
@endsection
