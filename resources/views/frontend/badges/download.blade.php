@extends('layouts.exhibitor')

@section('title', 'Download Badge')
@section('page-title', 'Download Badge')

@push('styles')
<style>
    .badge-download {
        max-width: 720px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    .badge-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .badge-body {
        display: flex;
        gap: 20px;
        align-items: center;
    }
    .qr-box {
        width: 180px;
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }
    .details {
        flex: 1;
    }
    .details h5 {
        margin-bottom: 8px;
        color: #0f172a;
    }
    .details p {
        margin: 0 0 6px 0;
        color: #475569;
    }
    .badge-footer {
        margin-top: 24px;
        display: flex;
        gap: 12px;
    }
    @media print {
        /* Hide everything by default */
        body * {
            visibility: hidden;
        }

        /* Only show the badge card */
        .badge-download,
        .badge-download * {
            visibility: visible;
        }

        /* Hide action buttons area (Print / Back to Badges) */
        .badge-footer {
            display: none !important;
            visibility: hidden !important;
        }

        /* Position the badge card at the top-left for clean printing */
        .badge-download {
            position: absolute;
            left: 0;
            top: 0;
            margin: 0;
            box-shadow: none;
        }
    }
</style>
@endpush

@section('content')
<div class="badge-download">
    <div class="badge-header">
        <div>
            <h4 class="mb-1">Badge</h4>
            <small class="text-muted">{{ $badge->exhibition->name ?? 'Exhibition' }}</small>
        </div>
        <span class="badge bg-success text-white" style="font-size:0.9rem;">{{ strtoupper($badge->status) }}</span>
    </div>

    <div class="badge-body">
        <div class="qr-box">
            @if($badge->qr_code && Storage::disk('public')->exists($badge->qr_code))
                <img src="{{ asset('storage/' . $badge->qr_code) }}" alt="QR Code" style="max-width: 100%; max-height: 100%;">
            @else
                <span class="text-muted">QR unavailable</span>
            @endif
        </div>
        <div class="details">
            <h5>{{ $badge->name }}</h5>
            <p><strong>Type:</strong> {{ $badge->badge_type }}</p>
            <p><strong>Exhibition:</strong> {{ $badge->exhibition->name ?? 'N/A' }}</p>
            <p><strong>Booking:</strong> {{ $badge->booking->booking_number ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $badge->email ?? '—' }}</p>
            <p><strong>Phone:</strong> {{ $badge->phone ?? '—' }}</p>
        </div>
    </div>

    <div class="badge-footer">
        <button class="btn btn-primary" onclick="window.print();"><i class="bi bi-printer me-1"></i>Print</button>
        <a href="{{ route('badges.index') }}" class="btn btn-outline-secondary">Back to Badges</a>
    </div>
</div>
@endsection
