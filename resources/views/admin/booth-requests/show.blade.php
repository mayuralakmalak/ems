@extends('layouts.admin')

@section('title', 'Booth Request Details')
@section('page-title', 'Booth Request Details')

@push('styles')
<style>
    .booking-details-container {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
    }
    .section-header {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 14px;
    }
    .detail-section {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 16px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }
    .detail-item {
        margin-bottom: 12px;
    }
    .detail-label {
        font-size: 0.9rem;
        color: #6b7280;
        margin-bottom: 4px;
        font-weight: 600;
    }
    .detail-value {
        font-size: 1rem;
        color: #111827;
        font-weight: 600;
    }
    .status-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .status-confirmed { background: #ecfdf3; color: #15803d; }
    .status-pending { background: #fef3c7; color: #b45309; }
    .summary-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        color: #374151;
    }
    .summary-label { font-weight: 600; }
    .summary-total { font-size: 1.05rem; color: #111827; }
    .summary-balance { color: #ef4444; font-weight: 700; }
    .payment-history-table {
        width: 100%;
        border-collapse: collapse;
    }
    .payment-history-table th, .payment-history-table td {
        padding: 10px;
        border-bottom: 1px solid #e5e7eb;
    }
    .payment-history-table th {
        text-transform: uppercase;
        font-size: 0.85rem;
        color: #6b7280;
    }
    .status-paid { background: #ecfdf3; color: #166534; }
    .status-pending-pay { background: #fef3c7; color: #b45309; }
    .document-status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    .document-status {
        font-weight: 600;
    }
    .status-uploaded { color: #2563eb; }
    .status-pending-doc { color: #d97706; }
    .booth-features {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 6px;
    }
    .booth-features li {
        background: #f9fafb;
        padding: 8px 10px;
        border-radius: 8px;
        color: #4b5563;
        font-size: 0.95rem;
    }
</style>
@endpush

@section('content')
<div class="booking-details-container">
    <h2 class="section-header mb-3">Booth Request</h2>
    <div class="detail-section">
        <div class="row">
            <div class="col-md-4">
                <div class="detail-item">
                    <div class="detail-label">Type</div>
                    <div class="detail-value">{{ ucfirst($boothRequest->request_type) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <div class="detail-label">Exhibition</div>
                    <div class="detail-value">{{ $boothRequest->exhibition->name ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="detail-item">
                    <div class="detail-label">User</div>
                    <div class="detail-value">{{ $boothRequest->user->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Booths</div>
            <div class="detail-value">
                @if(isset($displayBooths) && $displayBooths->isNotEmpty())
                    @foreach($displayBooths as $booth)
                        <div class="mb-2 p-2 border rounded" style="background: #f9fafb;">
                            <div class="fw-bold">{{ $booth->name }}</div>
                            <div class="small text-muted">
                                <span class="me-3"><strong>Size:</strong> {{ $booth->size_sqft ?? 'N/A' }} sq meter</span>
                                <span class="me-3"><strong>Type:</strong> {{ ($booth->booth_type ?? 'N/A') === 'Orphand' ? 'Shell' : ($booth->booth_type ?? 'N/A') }}</span>
                                <span class="me-3"><strong>Category:</strong> {{ $booth->category ?? 'N/A' }}</span>
                                <span><strong>Sides Open:</strong> {{ $booth->sides_open ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach($boothRequest->booths() as $booth)
                        <div class="mb-2 p-2 border rounded" style="background: #f9fafb;">
                            <div class="fw-bold">{{ $booth->name }}</div>
                            <div class="small text-muted">
                                <span class="me-3"><strong>Size:</strong> {{ $booth->size_sqft ?? 'N/A' }} sq meter</span>
                                <span class="me-3"><strong>Type:</strong> {{ ($booth->booth_type ?? 'N/A') === 'Orphand' ? 'Shell' : ($booth->booth_type ?? 'N/A') }}</span>
                                <span class="me-3"><strong>Category:</strong> {{ $booth->category ?? 'N/A' }}</span>
                                <span><strong>Sides Open:</strong> {{ $booth->sides_open ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Description</div>
            <div class="detail-value">{{ $boothRequest->description ?? 'N/A' }}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Requested At</div>
            <div class="detail-value">{{ $boothRequest->created_at?->format('d M Y H:i') ?? 'N/A' }}</div>
        </div>
    </div>

    @if($booking)
    <h2 class="section-header mb-3">Booking Details</h2>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="detail-section">
                <h5 class="section-header">Booking</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Booking ID</div>
                            <div class="detail-value">{{ $booking->booking_number }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Event Name</div>
                            <div class="detail-value">{{ $booking->exhibition->name ?? 'N/A' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Dates</div>
                            <div class="detail-value">
                                @if($booking->exhibition?->start_date && $booking->exhibition?->end_date)
                                    {{ $booking->exhibition->start_date->format('F d') }} - {{ $booking->exhibition->end_date->format('d, Y') }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <span class="status-badge status-confirmed">{{ ucfirst($booking->status ?? 'pending') }}</span>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Approval Status</div>
                            <div class="detail-value">{{ ucfirst($booking->approval_status ?? 'pending') }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Booked At</div>
                            <div class="detail-value">{{ $booking->created_at?->format('d M Y H:i') ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h5 class="section-header">Primary Contact</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="detail-item">
                            <div class="detail-label">Name</div>
                            <div class="detail-value">{{ $booking->user->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-item">
                            <div class="detail-label">Email</div>
                            <div class="detail-value">{{ $booking->user->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-item">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value">{{ $booking->user->phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                @if($booking->contact_emails)
                    <div class="detail-item">
                        <div class="detail-label">Additional Emails</div>
                        <div class="detail-value">
                            {{ is_array($booking->contact_emails) ? implode(', ', $booking->contact_emails) : $booking->contact_emails }}
                        </div>
                    </div>
                @endif
                @if($booking->contact_numbers)
                    <div class="detail-item">
                        <div class="detail-label">Additional Phones</div>
                        <div class="detail-value">
                            {{ is_array($booking->contact_numbers) ? implode(', ', $booking->contact_numbers) : $booking->contact_numbers }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="detail-section">
                <h5 class="section-header">Company Assets</h5>
                <div class="detail-item mb-3">
                    <div class="detail-label">Company Logo</div>
                    <div class="detail-value">
                        @if($booking->logo)
                            <img src="{{ asset('storage/' . $booking->logo) }}" alt="Company Logo" style="max-width: 220px; max-height: 120px; object-fit: contain; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; background: #f8fafc;">
                        @else
                            <span class="text-muted">Not uploaded</span>
                        @endif
                    </div>
                </div>
                @php
                    $brochures = $booking->documents->where('type', 'Promotional Brochure');
                @endphp
                <div class="detail-item">
                    <div class="detail-label">Brochures</div>
                    <div class="detail-value">
                        @if($brochures->count())
                            @foreach($brochures as $brochure)
                                <div class="mb-1">
                                    <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>
                                    <a href="{{ asset('storage/' . $brochure->file_path) }}" target="_blank">{{ $brochure->name ?? 'Brochure' }}</a>
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted">No brochure uploaded</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h5 class="section-header">Booth Details</h5>
                @php
                    $boothsToDisplay = isset($displayBooths) && $displayBooths->isNotEmpty() 
                        ? $displayBooths 
                        : (($booking->booth) ? collect([$booking->booth]) : collect());
                @endphp
                
                @if($boothsToDisplay->count() > 0)
                    @foreach($boothsToDisplay as $booth)
                        <div class="mb-4 p-3 border rounded" style="background: #f9fafb;">
                            <h6 class="fw-bold mb-3">{{ $booth->name }}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <div class="detail-label">Booth Number</div>
                                        <div class="detail-value">{{ $booth->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Category</div>
                                        <div class="detail-value">{{ $booth->category ?? 'N/A' }}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Type</div>
                                        <div class="detail-value">{{ ($booth->booth_type ?? 'N/A') === 'Orphand' ? 'Shell' : ($booth->booth_type ?? 'N/A') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <div class="detail-label">Size</div>
                                        <div class="detail-value">{{ $booth->size_sqft ?? 'N/A' }} sq meter</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Sides Open</div>
                                        <div class="detail-value">{{ $booth->sides_open ?? 'N/A' }}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Price</div>
                                        <div class="detail-value">₹{{ number_format($booth->price ?? 0, 2) }}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Location</div>
                                        <div class="detail-value">{{ $booking->exhibition->venue ?? 'N/A' }}, {{ $booking->exhibition->city ?? '' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">Booth Number</div>
                                <div class="detail-value">{{ $booking->booth->name ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Category</div>
                                <div class="detail-value">{{ $booking->booth->category ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Type</div>
                                <div class="detail-value">{{ (($booking->booth->booth_type ?? 'N/A') === 'Orphand') ? 'Shell' : ($booking->booth->booth_type ?? 'N/A') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">Size</div>
                                <div class="detail-value">{{ $booking->booth->size_sqft ?? 'N/A' }} sq meter</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Sides Open</div>
                                <div class="detail-value">{{ $booking->booth->sides_open ?? 'N/A' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Location</div>
                                <div class="detail-value">{{ $booking->exhibition->venue ?? 'N/A' }}, {{ $booking->exhibition->city ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <ul class="booth-features">
                    <li>High visibility location</li>
                    <li>Dedicated Power Outlet</li>
                    <li>High-speed Internet Access</li>
                </ul>
            </div>

            @if(!empty($booking->included_item_extras))
            <div class="detail-section">
                <h5 class="section-header">Included Item Extras (Paid)</h5>
                <table class="payment-history-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $extrasTotal = 0;
                        @endphp
                        @foreach($booking->included_item_extras as $extra)
                            @php
                                $qty = (int) ($extra['quantity'] ?? 0);
                                $unit = (float) ($extra['unit_price'] ?? 0);
                                $lineTotal = $qty * $unit;
                                $extrasTotal += $lineTotal;
                                $item = isset($extraItemsMap) ? $extraItemsMap->get($extra['item_id'] ?? null) : null;
                            @endphp
                            <tr>
                                <td>{{ $item->item_name ?? ('Item #' . ($extra['item_id'] ?? '-')) }}</td>
                                <td class="text-end">{{ $qty }}</td>
                                <td class="text-end">₹{{ number_format($unit, 2) }}</td>
                                <td class="text-end">₹{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Extras</strong></td>
                            <td class="text-end"><strong>₹{{ number_format($extrasTotal, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif

            @php
                $adminServices = $booking->bookingServices()->with('service')->get();
            @endphp
            @if($adminServices->count())
            <div class="detail-section">
                <h5 class="section-header">Additional Services</h5>
                <table class="payment-history-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $servicesTotal = 0;
                        @endphp
                        @foreach($adminServices as $bs)
                            @php
                                $qty = (int) $bs->quantity;
                                $unit = (float) $bs->unit_price;
                                $lineTotal = $qty * $unit;
                                $servicesTotal += $lineTotal;
                            @endphp
                            <tr>
                                <td>{{ $bs->service->name ?? 'Service #' . $bs->service_id }}</td>
                                <td class="text-end">{{ $qty }}</td>
                                <td class="text-end">₹{{ number_format($unit, 2) }}</td>
                                <td class="text-end">₹{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Services</strong></td>
                            <td class="text-end"><strong>₹{{ number_format($servicesTotal, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif

            <div class="detail-section">
                <h5 class="section-header">Payment History</h5>
                <table class="payment-history-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Gateway Fee (online)</th>
                            <th>Total Charged</th>
                            <th>Platform</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->payments as $payment)
                            @php
                                $gatewayCharge = (float) ($payment->gateway_charge ?? 0);
                                $totalCharged = $payment->amount + $gatewayCharge;
                            @endphp
                            <tr>
                                <td>{{ $payment->payment_number }}</td>
                                <td>{{ $payment->created_at?->format('Y-m-d') }}</td>
                                <td>₹{{ number_format($payment->amount, 2) }}</td>
                                <td>@if($gatewayCharge > 0) ₹{{ number_format($gatewayCharge, 2) }} @else <span class="text-muted">—</span> @endif</td>
                                <td>₹{{ number_format($totalCharged, 2) }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>
                                    <span class="status-badge {{ $payment->status === 'completed' ? 'status-paid' : 'status-pending-pay' }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No payments yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="detail-section">
                <h5 class="section-header">Document Status</h5>
                @php
                    $requiredDocs = [
                        'Exhibitor Agreement' => $booking->documents->where('type', 'Exhibitor Agreement')->first(),
                        'Company Registration' => $booking->documents->where('type', 'Company Registration')->first(),
                        'Product Catalog' => $booking->documents->where('type', 'Product Catalog')->first(),
                        'Insurance Certificate' => $booking->documents->where('type', 'Insurance Certificate')->first(),
                    ];
                @endphp
                @foreach($requiredDocs as $docName => $document)
                    <div class="document-status-item">
                        <div class="document-name">
                            <i class="bi bi-circle-fill" style="color: {{ $document && $document->status === 'approved' ? '#1e40af' : '#f59e0b' }}; font-size: 0.5rem;"></i>
                            {{ $docName }}
                        </div>
                        <div class="document-status {{ $document && $document->status === 'approved' ? 'status-uploaded' : 'status-pending-doc' }}">
                            {{ $document && $document->status === 'approved' ? 'Uploaded' : 'Pending' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card">
                <h5 class="section-header">Booking Summary</h5>
                @php
                    $servicesTotal = $booking->bookingServices->sum(fn($bs) => $bs->quantity * $bs->unit_price);
                    
                    // Calculate booth total from selected_booth_ids
                    $boothEntries = collect($booking->selected_booth_ids ?? []);
                    if ($boothEntries->isEmpty() && $booking->booth_id) {
                        $boothEntries = collect([[
                            'id' => $booking->booth_id,
                            'price' => $booking->booth->price ?? 0,
                        ]]);
                    }
                    $boothTotal = $boothEntries->sum(function($entry) {
                        if (is_array($entry)) {
                            return (float) ($entry['price'] ?? 0);
                        }
                        return 0;
                    });
                    if ($boothTotal == 0 && $booking->booth) {
                        $boothTotal = $booking->booth->price ?? 0;
                    }
                    
                    // Calculate extras total
                    $extrasTotal = 0;
                    $extrasRaw = $booking->included_item_extras ?? [];
                    if (is_array($extrasRaw)) {
                        foreach ($extrasRaw as $extra) {
                            $lineTotal = $extra['total_price'] ?? (
                                (isset($extra['quantity'], $extra['unit_price']))
                                    ? ((float) $extra['quantity'] * (float) $extra['unit_price'])
                                    : 0
                            );
                            $extrasTotal += $lineTotal;
                        }
                    }
                    
                    // Calculate base total before discount (booth + services + extras)
                    $baseTotal = $boothTotal + $servicesTotal + $extrasTotal;
                    
                    // Calculate discount from discount_percent (applied to base total)
                    $discount = 0;
                    if ($booking->discount_percent > 0 && $baseTotal > 0) {
                        $discount = ($baseTotal * $booking->discount_percent) / 100;
                    }
                    
                    $taxes = ($booking->total_amount - $servicesTotal) * 0.1;
                    $baseTotalAmount = $booking->total_amount;
                    $paidAmount = $booking->paid_amount;
                    $balanceDue = $baseTotalAmount - $paidAmount;
                    $gatewayFee = round(($baseTotalAmount * 2.5) / 100, 2);
                    $totalAmountInclGateway = $baseTotalAmount + $gatewayFee;
                @endphp
                <div class="summary-item">
                    <span class="summary-label">Booth/Fee</span>
                    <span class="summary-value">₹{{ number_format($boothTotal, 2) }}</span>
                </div>
                @if($servicesTotal > 0)
                <div class="summary-item">
                    <span class="summary-label">Service Charges</span>
                    <span class="summary-value">₹{{ number_format($servicesTotal, 2) }}</span>
                </div>
                @endif
                <div class="summary-item">
                    <span class="summary-label">Taxes</span>
                    <span class="summary-value">₹{{ number_format($taxes, 2) }}</span>
                </div>
                @if($discount > 0)
                <div class="summary-item">
                    <span class="summary-label">Special Discount ({{ number_format($booking->discount_percent, 2) }}%)</span>
                    <span class="summary-value" style="color: #10b981;">-₹{{ number_format($discount, 2) }}</span>
                </div>
                @endif
                <div class="summary-item">
                    <span class="summary-label">Booking total (before gateway)</span>
                    <span class="summary-value">₹{{ number_format($baseTotalAmount, 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Payment gateway fee (2.5% for online)</span>
                    <span class="summary-value">₹{{ number_format($gatewayFee, 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label summary-total">Total Amount (incl. gateway)</span>
                    <span class="summary-value summary-total">₹{{ number_format($totalAmountInclGateway, 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Amount Paid</span>
                    <span class="summary-value">₹{{ number_format($paidAmount, 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Balance Due</span>
                    <span class="summary-value summary-balance">₹{{ number_format($balanceDue, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">No related booking found for this request.</div>
    @endif
</div>
@endsection

