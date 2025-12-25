<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EMS Payment Receipt - {{ $payment->payment_number }}</title>
    <style>
        @page { margin: 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            background: #f6f8fb;
            color: #0f172a;
        }
        .page {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(90deg, #4f46e5, #6366f1);
            color: #fff;
            padding: 24px 28px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
        }
        .header .title {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }
        .header .meta {
            font-size: 13px;
            line-height: 1.5;
            text-align: right;
        }
        .section {
            padding: 16px 22px 8px;
        }
        .section h3 {
            margin: 0 0 8px;
            font-size: 15px;
            color: #0f172a;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 14px;
        }
        .item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 10px;
        }
        .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .value {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 12px;
        }
        th, td {
            padding: 8px 6px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        th {
            background: #f8fafc;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #475569;
        }
        .totals {
            margin-top: 10px;
            width: 100%;
        }
        .totals tr td {
            border: none;
            padding: 4px 2px;
            font-size: 13px;
        }
        .totals .label {
            text-transform: none;
            letter-spacing: 0;
            color: #475569;
            font-size: 13px;
        }
        .totals .amount {
            text-align: right;
            font-weight: 600;
            color: #0f172a;
        }
        .totals .grand {
            font-size: 15px;
            font-weight: 700;
            color: #4f46e5;
        }
        .note {
            padding: 0 22px 14px;
            font-size: 11px;
            color: #475569;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-info { background: #dbeafe; color: #1d4ed8; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
@php
    $booking = $payment->booking;
    $exhibition = $booking->exhibition ?? null;
    
    // Calculate booth total from selected_booth_ids (works for both merged and non-merged booths)
    // For merged booths: selected_booth_ids contains the original booths that were merged
    // For non-merged: selected_booth_ids contains the selected booths
    // We always sum the prices from selected_booth_ids to get the correct booth rental total
    $boothEntries = collect($booking->selected_booth_ids ?? []);
    if ($boothEntries->isEmpty() && $booking->booth_id) {
        $boothEntries = collect([['id' => $booking->booth_id]]);
    }
    $boothIds = $boothEntries->map(fn($entry) => is_array($entry) ? ($entry['id'] ?? null) : $entry)
        ->filter()
        ->values();
    $booths = \App\Models\Booth::whereIn('id', $boothIds)->get()->keyBy('id');
    $boothDisplay = $boothEntries->map(function($entry) use ($booths) {
        $isArray = is_array($entry);
        $id = $isArray ? ($entry['id'] ?? null) : $entry;
        $model = $id ? ($booths[$id] ?? null) : null;
        return [
            'name' => $isArray ? ($entry['name'] ?? $model?->name) : ($model?->name),
            'size_sqft' => $isArray ? ($entry['size_sqft'] ?? $model?->size_sqft) : ($model?->size_sqft),
            'type' => $isArray ? ($entry['type'] ?? null) : ($model?->booth_type),
            'sides' => $isArray ? ($entry['sides'] ?? null) : ($model?->sides_open),
            'price' => $isArray ? ($entry['price'] ?? $model?->price ?? 0) : ($model?->price ?? 0),
        ];
    })->filter(fn($b) => $b['name'] || $b['price']);
    
    // Calculate booth total by summing prices from selected_booth_ids
    // This works correctly for both merged and non-merged booths
    $boothTotal = $boothDisplay->sum(fn($b) => $b['price'] ?? 0);
    $services = $booking->bookingServices()->with('service')->get();
    $servicesTotal = $services->sum('total_price');
    $gateway = $payment->gateway_charge ?? 0;
    $paidAmount = $payment->amount + $gateway;
    $grandTotal = $boothTotal + $servicesTotal + $gateway;

    $statusBadge = match($payment->status) {
        'completed' => 'badge-success',
        'pending' => 'badge-warning',
        'failed' => 'badge-danger',
        default => 'badge-info'
    };
@endphp
@php
    $generalSettings = \App\Models\Setting::getByGroup('general');
    $companyLogo = $generalSettings['company_logo'] ?? null;
    $logoPath = null;
    if ($companyLogo && \Storage::disk('public')->exists($companyLogo)) {
        $logoPath = storage_path('app/public/' . $companyLogo);
    }
@endphp
<div class="page">
    <div class="header">
        <div style="line-height:1.3;">
            @if($logoPath && file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="Company Logo" style="max-height: 50px; max-width: 180px; object-fit: contain; margin-bottom: 8px;">
            @else
                <p style="margin:0; font-size:24px; font-weight:800; opacity:0.96;">EMS</p>
            @endif
            <p style="margin:2px 0 0; font-size:12px; font-weight:600; opacity:0.9;">Payment Receipt</p>
        </div>
        <div class="meta">
            <div>Receipt #: <strong>{{ $payment->payment_number }}</strong></div>
            <div>Date: {{ $payment->created_at->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    <div class="section">
        <h3>Payment Summary</h3>
        <div class="grid">
            <div class="item">
                <div class="label">Exhibitor</div>
                <div class="value">{{ $payment->user->name }}</div>
                <div style="font-size:12px; color:#475569;">{{ $payment->user->email }}</div>
            </div>
            <div class="item">
                <div class="label">Booking #</div>
                <div class="value">{{ $booking->booking_number ?? 'N/A' }}</div>
            </div>
            <div class="item">
                <div class="label">Payment Method</div>
                <div class="value">{{ strtoupper($payment->payment_method) }}</div>
            </div>
            <div class="item">
                <div class="label">Status</div>
                <div class="value">
                    <span class="badge {{ $statusBadge }}">{{ strtoupper($payment->status) }}</span>
                </div>
            </div>
            <div class="item">
                <div class="label">Exhibition</div>
                <div class="value">{{ $exhibition->name ?? 'N/A' }}</div>
                @if($exhibition && $exhibition->start_date && $exhibition->end_date)
                <div style="font-size:12px; color:#475569;">
                    {{ $exhibition->start_date->format('d M Y') }} - {{ $exhibition->end_date->format('d M Y') }}
                </div>
                @endif
            </div>
            <div class="item">
                <div class="label">Transaction ID</div>
                <div class="value">{{ $payment->transaction_id ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Booth Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Booth</th>
                    <th>Size (sq ft)</th>
                    <th>Sides Open</th>
                    <th>Type</th>
                    <th>Price (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($boothDisplay as $booth)
                <tr>
                    <td>{{ $booth['name'] ?? '—' }}</td>
                    <td>{{ $booth['size_sqft'] ?? '—' }}</td>
                    <td>{{ $booth['sides'] ?? '—' }}</td>
                    <td>{{ $booth['type'] ?? '—' }}</td>
                    <td>{{ number_format($booth['price'] ?? 0, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5">No booth information available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Additional Services</h3>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Unit Price (₹)</th>
                    <th>Qty</th>
                    <th>Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $svc)
                <tr>
                    <td>{{ $svc->service->name ?? 'Service' }}</td>
                    <td>{{ number_format($svc->unit_price, 2) }}</td>
                    <td>{{ $svc->quantity }}</td>
                    <td>{{ number_format($svc->total_price, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4">No additional services selected.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Totals</h3>
        <table class="totals">
            <tr>
                <td class="label">Booth Total</td>
                <td class="amount">₹{{ number_format($boothTotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Services Total</td>
                <td class="amount">₹{{ number_format($servicesTotal, 2) }}</td>
            </tr>
            @if($gateway > 0)
            <tr>
                <td class="label">Gateway Charge</td>
                <td class="amount">₹{{ number_format($gateway, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="label grand">Total Paid</td>
                <td class="amount grand">₹{{ number_format($paidAmount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Grand Total (booking)</td>
                <td class="amount">₹{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="note">
        <p>This is a computer-generated receipt. No signature required.</p>
        <p>Generated on {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</div>
</body>
</html>
