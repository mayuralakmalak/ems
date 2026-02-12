@extends('layouts.exhibitor')

@section('title', 'Booking Invoice')
@section('page-title', 'Booking Invoice')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/invoice.css') }}">
@endpush

@section('content')
@php
    $user = $booking->user;
    $exhibition = $booking->exhibition;
    $companyLogo = $generalSettings['company_logo'] ?? null;
    $organizerName = $generalSettings['company_name'] ?? config('app.name');
    $organizerAddress = $generalSettings['company_address'] ?? null;
    $organizerGst = $generalSettings['company_gst_number'] ?? null;

    $servicesTotal = $booking->bookingServices->sum('total_price');
    $extrasTotal = collect($booking->included_item_extras ?? [])->sum(function ($row) {
        return (float) ($row['total_price'] ?? 0);
    });
    $boothPortion = max(0, (float) $booking->total_amount - $servicesTotal - $extrasTotal);

    $paidAmount = (float) $booking->paid_amount;
    $totalAmount = (float) $booking->total_amount;
    $balanceAmount = max(0, $totalAmount - $paidAmount);

    $statusBadgeClass = 'unpaid';
    if ($balanceAmount <= 0 && $totalAmount > 0) {
        $statusBadgeClass = 'paid';
    } elseif ($paidAmount > 0 && $balanceAmount > 0) {
        $statusBadgeClass = 'partial';
    }
@endphp

<div class="invoice-wrapper">
    <div class="invoice-actions">
        <button type="button" class="btn-print" onclick="window.print()">
            <i class="bi bi-printer"></i>
            <span>Print / Save as PDF</span>
        </button>
    </div>

    <div class="invoice-card">
        <div class="invoice-header">
            <div class="invoice-logo">
                @if($companyLogo && \Storage::disk('public')->exists($companyLogo))
                    <img src="{{ \Storage::url($companyLogo) }}" alt="Organizer Logo">
                @else
                    <h4 style="margin: 0; color: #111827;">{{ $organizerName }}</h4>
                @endif
                @if($organizerAddress)
                    <div style="font-size: 0.8rem; color: #6b7280; margin-top: 6px;">
                        {!! nl2br(e($organizerAddress)) !!}
                    </div>
                @endif
                @if($organizerGst)
                    <div style="font-size: 0.8rem; color: #6b7280; margin-top: 4px;">
                        GSTIN: {{ $organizerGst }}
                    </div>
                @endif
            </div>
            <div class="invoice-meta">
                <h4>Invoice</h4>
                <div>
                    <div><span class="info-label">Invoice No:</span> <span class="info-value">{{ $booking->booking_number }}</span></div>
                    <div><span class="info-label">Invoice Date:</span> <span class="info-value">{{ $booking->created_at->format('d M Y') }}</span></div>
                    <div><span class="info-label">Booking Status:</span> <span class="info-value">{{ ucfirst($booking->status ?? 'pending') }}</span></div>
                    <div style="margin-top: 6px;">
                        <span class="badge-status {{ $statusBadgeClass }}">
                            @if($statusBadgeClass === 'paid')
                                Fully Paid
                            @elseif($statusBadgeClass === 'partial')
                                Partially Paid
                            @else
                                Payment Pending
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="invoice-section">
            <div class="info-grid">
                <div>
                    <div class="invoice-section-title">Invoice To</div>
                    <div class="info-label">{{ $user->company_name ?: $user->name }}</div>
                    <div class="info-value">
                        @if($user->address)
                            {{ $user->address }}<br>
                        @endif
                        {{ $user->city }}@if($user->state), {{ $user->state }}@endif<br>
                        @if($user->country){{ $user->country }}@endif @if($user->pincode)- {{ $user->pincode }}@endif
                    </div>
                    <div class="info-value" style="margin-top: 6px;">
                        @if($user->email)Email: {{ $user->email }}<br>@endif
                        @if($user->phone ?? $user->mobile_number)
                            Phone: {{ $user->phone ?? $user->mobile_number }}
                        @endif
                    </div>
                    @if($user->gst_number)
                        <div class="info-value" style="margin-top: 6px;">
                            GSTIN: {{ $user->gst_number }}
                        </div>
                    @endif
                </div>
                <div>
                    <div class="invoice-section-title">Exhibition Details</div>
                    <div class="info-label">{{ $exhibition->name ?? 'Exhibition' }}</div>
                    <div class="info-value">
                        @if($exhibition->venue)
                            {{ $exhibition->venue }}<br>
                        @endif
                        @if($exhibition->city){{ $exhibition->city }}@endif
                        @if($exhibition->state), {{ $exhibition->state }}@endif
                        @if($exhibition->country), {{ $exhibition->country }}@endif
                    </div>
                    <div class="info-value" style="margin-top: 6px;">
                        @if($exhibition->start_date && $exhibition->end_date)
                            {{ $exhibition->start_date->format('d M Y') }} – {{ $exhibition->end_date->format('d M Y') }}
                        @endif
                        @if($exhibition->start_time && $exhibition->end_time)
                            <br>{{ $exhibition->start_time }} – {{ $exhibition->end_time }}
                        @endif
                    </div>
                    <div class="info-value" style="margin-top: 6px;">
                        Booth:
                        @php
                            $boothEntries = collect($booking->selected_booth_ids ?? []);
                            if ($boothEntries->isEmpty() && $booking->booth) {
                                $boothEntries = collect([['id' => $booking->booth->id, 'name' => $booking->booth->name]]);
                            }
                            $boothNames = $boothEntries->map(function($entry) {
                                if (is_array($entry)) {
                                    return $entry['name'] ?? ('Booth #' . ($entry['id'] ?? 'N/A'));
                                }
                                return 'Booth #' . $entry;
                            })->filter()->values();
                        @endphp
                        @if($boothNames->isNotEmpty())
                            {{ $boothNames->implode(', ') }}
                        @else
                            {{ $booking->booth->name ?? 'N/A' }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="invoice-section">
            <div class="invoice-section-title">Booking Summary</div>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Booth Booking</strong><br>
                            <span style="font-size: 0.82rem; color: #6b7280;">
                                Exhibition: {{ $exhibition->name ?? 'N/A' }}<br>
                                Booking #{{ $booking->booking_number }}
                            </span>
                        </td>
                        <td class="text-right">{{ number_format($boothPortion, 2) }}</td>
                    </tr>
                    @if($servicesTotal > 0)
                        <tr>
                            <td>
                                <strong>Additional Services</strong>
                                @if($booking->bookingServices->isNotEmpty())
                                    <div style="font-size: 0.82rem; color: #6b7280; margin-top: 4px;">
                                        @foreach($booking->bookingServices as $service)
                                            <div>
                                                {{ $service->service->name ?? 'Service' }} &times; {{ $service->quantity }}
                                                @if($service->unit_price)
                                                    (₹{{ number_format($service->unit_price, 2) }} each)
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($servicesTotal, 2) }}</td>
                        </tr>
                    @endif
                    @if($extrasTotal > 0)
                        <tr>
                            <td>
                                <strong>Included Items & Extras</strong>
                                <div style="font-size: 0.82rem; color: #6b7280; margin-top: 4px;">
                                    Additional booth items and inclusions as per configuration.
                                </div>
                            </td>
                            <td class="text-right">{{ number_format($extrasTotal, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="invoice-section" style="margin-top: 8px;">
            <div class="summary-box">
                <div class="summary-row label">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($totalAmount, 2) }}</span>
                </div>
                @if($booking->discount_percent > 0)
                    <div class="summary-row label">
                        <span>Discount Applied</span>
                        <span>{{ number_format($booking->discount_percent, 2) }}%</span>
                    </div>
                @endif
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span>₹{{ number_format($totalAmount, 2) }}</span>
                </div>
                <div class="summary-row label" style="margin-top: 4px;">
                    <span>Amount Paid</span>
                    <span>₹{{ number_format($paidAmount, 2) }}</span>
                </div>
                <div class="summary-row balance">
                    <span>Balance Due</span>
                    <span>₹{{ number_format($balanceAmount, 2) }}</span>
                </div>
            </div>
        </div>

        @if($booking->payments->isNotEmpty())
            <div class="invoice-section">
                <div class="invoice-section-title">Payment Schedule</div>
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Payment #</th>
                            <th>Type</th>
                            <th class="text-right">Amount (₹)</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->payments->sortBy('due_date') as $payment)
                            <tr>
                                <td>{{ $payment->payment_number }}</td>
                                <td>{{ ucfirst($payment->payment_type ?? '—') }}</td>
                                <td class="text-right">{{ number_format($payment->amount ?? 0, 2) }}</td>
                                <td>
                                    @if($payment->due_date)
                                        {{ $payment->due_date->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if(($payment->status ?? '') === 'completed' && ($payment->approval_status ?? '') === 'approved')
                                        <span style="color:#15803d;font-weight:600;">Paid</span>
                                    @elseif(($payment->status ?? '') === 'completed')
                                        <span style="color:#0f766e;font-weight:600;">Completed (Pending Approval)</span>
                                    @else
                                        <span style="color:#b91c1c;font-weight:600;">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="invoice-footer">
            This invoice is generated electronically from the Exhibition Management System.
            Please retain it for your records.
        </div>
    </div>
</div>
@endsection

