@extends('layouts.frontend')

@section('title', $exhibition->name . ' - Details')

@push('styles')
<style>
    /* --- Hero Section --- */
    .exhibition-hero {
        background: radial-gradient(circle at top left, #1e293b 0, #020617 55%);
        border-radius: 20px;
        padding: 26px 30px;
        color: #e5e7eb;
        position: relative;
        overflow: hidden;
        margin-bottom: 26px;
        box-shadow: 0 20px 40px rgba(15,23,42,0.45);
    }
    .exhibition-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top left, rgba(56,189,248,0.3), transparent 55%),
            radial-gradient(circle at bottom right, rgba(129,140,248,0.4), transparent 55%);
        mix-blend-mode: screen;
        pointer-events: none;
    }
    .exhibition-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        gap: 24px;
        align-items: center;
        flex-wrap: wrap;
    }
    .exhibition-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #f9fafb;
    }
    .exhibition-meta {
        font-size: 0.92rem;
        color: #cbd5f5;
    }
    .exhibition-meta i {
        color: #93c5fd;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(34,197,94,0.16);
        color: #bbf7d0;
        border: 1px solid rgba(34,197,94,0.55);
        margin-right: 6px;
    }
    .date-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 500;
        background: rgba(37,99,235,0.2);
        color: #dbeafe;
        margin-top: 6px;
    }

    .hero-actions .btn {
        border-radius: 999px;
        font-weight: 600;
    }
    .hero-actions .btn-primary {
        box-shadow: 0 14px 32px rgba(37,99,235,0.55);
    }
    .hero-actions .btn-outline-light {
        border-width: 1.5px;
    }

    /* --- Section Cards --- */
    .section-card {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 14px 40px rgba(15,23,42,0.06);
        transition: transform 120ms ease, box-shadow 120ms ease, border-color 120ms ease;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }
    .section-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 46px rgba(15,23,42,0.08);
        border-color: #cbd5e1;
    }
    .section-card .card-header {
        background: linear-gradient(90deg, #f8fafc 0%, #eef2ff 100%);
        border-bottom-color: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 12px;
        padding-bottom: 12px;
    }
    .section-card .card-header h5 {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 2px;
    }
    .section-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #e0e7ff;
        color: #3730a3;
        font-weight: 600;
        font-size: 0.78rem;
    }
    .empty-state {
        border: 1px dashed #cbd5e1;
        padding: 14px;
        border-radius: 12px;
        background: #f8fafc;
        color: #64748b;
    }
    .card-cta {
        background: linear-gradient(120deg, #4f46e5, #6366f1);
        color: #fff;
        border-radius: 12px;
        padding: 10px 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 12px 28px rgba(79,70,229,0.25);
    }
    .card-cta:hover { color: #fff; opacity: 0.94; }

    .section-subtitle {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0;
    }

    /* --- Tables & Lists --- */
    .table-sm thead th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom-width: 1px;
        color: #64748b;
        background: #f9fafb;
    }
    .table-sm tbody td {
        font-size: 0.86rem;
        vertical-align: middle;
    }

    .badge-chip {
        display: inline-flex;
        align-items: center;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 0.75rem;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .list-group-item {
        border: none;
        border-bottom: 1px solid #e5e7eb;
    }
    .list-group-item:last-child {
        border-bottom: none;
    }

    @media (max-width: 767.98px) {
        .exhibition-hero {
            padding: 18px 18px;
        }
        .exhibition-title {
            font-size: 1.5rem;
        }
        .hero-actions {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container my-4 my-lg-5">
    <div class="row mb-3 mb-lg-4">
        <div class="col-12 mb-3">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="bi bi-arrow-left me-2"></i>Back to Exhibitions
            </a>
        </div>
        <div class="col-12">
            <div class="exhibition-hero">
                <div class="exhibition-hero-inner">
                    <div>
                        <div class="exhibition-title">{{ $exhibition->name ?? 'Exhibition' }}</div>
                        <div class="exhibition-meta mb-1">
                            <div class="mb-1">
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ $exhibition->venue ?? 'N/A' }}, {{ $exhibition->city ?? '' }}, {{ $exhibition->country ?? '' }}
                            </div>
                            <div>
                                <i class="bi bi-calendar3 me-1"></i>
                                @if($exhibition->start_date && $exhibition->end_date)
                                    {{ $exhibition->start_date->format('d M Y') }} - {{ $exhibition->end_date->format('d M Y') }}
                                @else
                                    Date TBA
                                @endif
                            </div>
                        </div>
                        <div class="mt-1">
                            <span class="status-pill">
                                <span class="me-1" style="font-size:10px;">●</span>{{ ucfirst($exhibition->status ?? 'active') }}
                            </span>
                            @if($exhibition->start_date)
                                <span class="date-pill">
                                    <i class="bi bi-clock-history me-1"></i>
                                    {{ now()->lt($exhibition->start_date) ? 'Upcoming' : 'Ongoing' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="hero-actions text-end">
                        @php
                            $floorplanUrl = null;
                            if (Route::has('floorplan.show.public')) {
                                $floorplanUrl = route('floorplan.show.public', $exhibition->id);
                            } elseif (Route::has('floorplan.show')) {
                                $floorplanUrl = route('floorplan.show', $exhibition->id);
                            }
                        @endphp
                        @if($floorplanUrl)
                            <a href="{{ $floorplanUrl }}" class="btn btn-outline-light btn-sm w-100 mb-2">
                                <i class="bi bi-diagram-3 me-1"></i>View Hall Plan
                            </a>
                        @endif
                        @auth
                        <a href="{{ route('bookings.book', $exhibition->id) }}" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-cart-check me-1"></i>Book Booth
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login to Book
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-8">
            <p class="mb-0 text-slate-700">{{ $exhibition->description ?? 'No description available.' }}</p>
        </div>
        <div class="col-lg-4 d-none">
            <!-- legacy booking info card kept hidden -->
        </div>
    </div>

    {{-- Booth & Pricing Configuration (from admin Step 2) --}}
    <div class="row mt-4">
        @php
            $sizes = $exhibition->boothSizes ?? collect();
        @endphp
        <div class="col-md-7 mb-4">
            <div class="card section-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Booth Sizes & Pricing</h5>
                    <span class="section-chip"><i class="bi bi-list-ol"></i>{{ $sizes->count() }} option{{ $sizes->count() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-body">
                    @if($sizes->isEmpty())
                        <p class="text-muted mb-0">Booth size and pricing information will be available soon.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Size (sq meter)</th>
                                        <th>Raw Price</th>
                                        <th>Orphan Price</th>
                                        <th>Category</th>
                                        <th>Included Items</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sizes as $size)
                                        @php
                                            $items = $size->items ?? collect();
                                        @endphp
                                        <tr>
                                            <td>{{ $size->size_sqft }}</td>
                                            <td>₹{{ number_format($size->row_price ?? 0, 2) }}</td>
                                            <td>₹{{ number_format($size->orphan_price ?? 0, 2) }}</td>
                                            <td>
                                                @switch($size->category)
                                                    @case('1') Premium @break
                                                    @case('2') Standard @break
                                                    @case('3') Economy @break
                                                    @default {{ $size->category ?? '-' }}
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($items->isEmpty())
                                                    <span class="text-muted">-</span>
                                                @else
                                                    <ul class="mb-0 ps-3 small">
                                                        @foreach($items as $item)
                                                            <li>
                                                                {{ $item->quantity ?? 0 }} × {{ $item->item_name }}
                                                                @if(!is_null($item->price) && $item->price > 0)
                                                                    <span class="text-muted">(₹{{ number_format($item->price, 2) }} per extra)</span>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Add-on Services --}}
        @php
            $addonServices = $exhibition->addonServices ?? collect();
        @endphp
        <div class="col-md-5 mb-4">
            <div class="card section-card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-stars me-2"></i>Add-on Services</h5>
                    <span class="section-chip"><i class="bi bi-bag-plus"></i>{{ $addonServices->count() }} item{{ $addonServices->count() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-body">
                    @if($addonServices->isEmpty())
                        <p class="text-muted mb-0">No add-on services have been published for this exhibition yet.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($addonServices as $service)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <div class="fw-semibold">{{ $service->item_name }}</div>
                                        <div class="small text-muted">Price per quantity</div>
                                    </div>
                                    <div class="fw-semibold text-primary">
                                        ₹{{ number_format($service->price_per_quantity ?? 0, 2) }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Hall Plan Background Images & Stall Variations --}}
    <div class="row mt-2">
        @php
            $floorplanImages = is_array($exhibition->floorplan_images ?? null)
                ? $exhibition->floorplan_images
                : (array) ($exhibition->floorplan_image ? [$exhibition->floorplan_image] : []);
        @endphp
        <div class="col-md-7 mb-4">
            <div class="card section-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i>Hall plan</h5>
                    <span class="section-chip"><i class="bi bi-images"></i>{{ count($floorplanImages) }} image{{ count($floorplanImages) === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-body">
                    @if(empty($floorplanImages))
                        <p class="text-muted mb-0">No hall plan images uploaded yet.</p>
                    @else
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($floorplanImages as $imgPath)
                                <div class="border rounded p-2 text-center" style="width: 140px; background-color: #f8f9fa;">
                                    <img src="{{ asset('storage/' . ltrim($imgPath, '/')) }}" alt="Hall Plan"
                                         style="width: 100%; height: 90px; object-fit: cover; border-radius: 4px;">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @php
            $variations = $exhibition->stallVariations ?? collect();
        @endphp
        <div class="col-md-5 mb-4">
            <div class="card section-card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-layout-three-columns me-2"></i>Stall Variations</h5>
                    <span class="section-chip"><i class="bi bi-collection"></i>{{ $variations->count() }} style{{ $variations->count() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-body">
                    @if($variations->isEmpty())
                        <p class="text-muted mb-0">Stall variation visuals will be available soon.</p>
                    @else
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($variations as $variation)
                                @foreach([
                                    'Front view' => $variation->front_view,
                                    'Left side' => $variation->side_view_left,
                                    'Right side' => $variation->side_view_right,
                                    'Back view' => $variation->back_view,
                                ] as $label => $path)
                                    @if($path)
                                        <div class="border rounded p-2 text-center" style="width: 140px; background-color: #f8f9fa;">
                                            <img src="{{ asset('storage/' . ltrim($path, '/')) }}" class="img-fluid mb-1" alt="{{ $label }}">
                                            <small class="text-muted d-block text-truncate">
                                                {{ $variation->stall_type ? $variation->stall_type . ' - ' : '' }}{{ $label }}
                                            </small>
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Schedule, Cut-off Dates, Badge Management, Exhibition Manual --}}
    <div class="row mt-2">
        @php
            $schedules = $exhibition->paymentSchedules ?? collect();
        @endphp
        <div class="col-md-7 mb-4">
            <div class="card section-card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Payment Schedule & Cut-off Dates</h5>
                    <span class="section-chip"><i class="bi bi-calendar-check"></i>{{ $schedules->count() }} part{{ $schedules->count() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-body">
                    @if($schedules->isEmpty() && !$exhibition->addon_services_cutoff_date && !$exhibition->document_upload_deadline)
                        <p class="text-muted mb-0">Payment schedule and cut-off dates will be communicated later.</p>
                    @else
                        @if($schedules->isNotEmpty())
                            <h6 class="fw-semibold mb-2">Payment Schedule</h6>
                            <ul class="list-unstyled small mb-3">
                                @foreach($schedules as $part)
                                    <li class="mb-1">
                                        <strong>Part {{ $part->part_number }}:</strong>
                                        {{ $part->percentage }}% —
                                        Due by {{ \Carbon\Carbon::parse($part->due_date)->format('d M Y') }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <h6 class="fw-semibold mb-2">Cut-off Dates</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>
                                <strong>Add-on services cut-off:</strong>
                                {{ $exhibition->addon_services_cutoff_date ? $exhibition->addon_services_cutoff_date->format('d M Y') : 'Not set' }}
                            </li>
                            <li>
                                <strong>Document upload deadline:</strong>
                                {{ $exhibition->document_upload_deadline ? $exhibition->document_upload_deadline->format('d M Y') : 'Not set' }}
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        @php
            $badgeConfigs = $exhibition->badgeConfigurations->keyBy('badge_type');
        @endphp
        <div class="col-md-5 mb-4">
            <div class="card section-card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Badges for CheckIn</h5>
                    <span class="section-chip"><i class="bi bi-people"></i>{{ $badgeConfigs->count() }} type{{ $badgeConfigs->count() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-body">
                    @if($badgeConfigs->isEmpty())
                        <p class="text-muted mb-0">Badge configuration details will be shared later.</p>
                    @else
                        <ul class="list-unstyled small mb-0">
                            @foreach(['Primary', 'Secondary', 'Additional'] as $type)
                                @php $cfg = $badgeConfigs->get($type); @endphp
                                @if($cfg)
                                    <li class="mb-2">
                                        <strong>{{ $type }} Badge:</strong>
                                        {{ $cfg->quantity }} included,
                                        {{ $cfg->pricing_type ?? 'Free' }}
                                        @if(($cfg->pricing_type ?? null) === 'Paid')
                                            — ₹{{ number_format($cfg->price ?? 0, 2) }} each
                                        @endif
                                        @if($type === 'Additional' && !empty($cfg->access_permissions))
                                            <br><span class="text-muted">Includes: {{ implode(', ', $cfg->access_permissions) }}</span>
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card section-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Exhibition Manual</h5>
                    <span class="section-chip {{ $exhibition->exhibition_manual_pdf ? '' : 'bg-warning text-dark' }}">
                        <i class="bi bi-file-earmark"></i>{{ $exhibition->exhibition_manual_pdf ? 'Available' : 'Pending' }}
                    </span>
                </div>
                <div class="card-body">
                    @if($exhibition->exhibition_manual_pdf)
                        <p class="mb-2 small text-muted">Please download and review the exhibition manual for detailed rules and guidelines.</p>
                        <a href="{{ asset('storage/' . $exhibition->exhibition_manual_pdf) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i>Download Manual
                        </a>
                    @else
                        <p class="text-muted mb-0">Exhibition manual will be uploaded soon.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


