<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(90deg, #4f46e5, #6366f1);
            color: #fff;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 20px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin: 20px 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #4f46e5;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
        }
        .detail-value {
            color: #1e293b;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.9rem;
            color: #64748b;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #475569;
        }
        .total-row {
            font-weight: 600;
            background-color: #f8f9fa;
        }
        .grand-total {
            font-size: 1.1rem;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            @php
                $generalSettings = \App\Models\Setting::getByGroup('general');
                $companyLogo = $generalSettings['company_logo'] ?? null;
            @endphp
            @if($companyLogo && \Storage::disk('public')->exists($companyLogo))
                <div style="margin-bottom: 15px;">
                    <img src="{{ \Storage::url($companyLogo) }}" alt="Company Logo" style="max-height: 60px; max-width: 200px; object-fit: contain;">
                </div>
            @endif
            <h1>Payment Receipt</h1>
        </div>
        
        <div class="content">
            @if($isAdmin)
                <p>Dear Admin,</p>
                <p>A payment has been received for the following booking:</p>
            @else
                <p>Dear {{ $payment->user->name }},</p>
                <p>Thank you for your payment! Please find your payment receipt below.</p>
            @endif

            <div class="info-box">
                <div class="detail-row">
                    <span class="detail-label">Payment Number:</span>
                    <span class="detail-value"><strong>{{ $payment->payment_number }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Booking Number:</span>
                    <span class="detail-value">{{ $payment->booking->booking_number ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Exhibition:</span>
                    <span class="detail-value">{{ $payment->booking->exhibition->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Exhibitor:</span>
                    <span class="detail-value">{{ $payment->user->name }} ({{ $payment->user->email }})</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ strtoupper($payment->payment_method) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Type:</span>
                    <span class="detail-value">{{ ucfirst($payment->payment_type) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value"><strong>₹{{ number_format($payment->amount, 2) }}</strong></span>
                </div>
                @if($payment->gateway_charge > 0)
                <div class="detail-row">
                    <span class="detail-label">Gateway Charge:</span>
                    <span class="detail-value">₹{{ number_format($payment->gateway_charge, 2) }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Total Paid:</span>
                    <span class="detail-value"><strong>₹{{ number_format($payment->amount + ($payment->gateway_charge ?? 0), 2) }}</strong></span>
                </div>
                @if($payment->transaction_id)
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $payment->transaction_id }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : $payment->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ $payment->status }}">
                            {{ strtoupper($payment->status) }}
                        </span>
                    </span>
                </div>
            </div>

            @php
                $booking = $payment->booking;
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
                $boothTotal = $boothDisplay->sum(fn($b) => $b['price'] ?? 0);
                $services = $booking->bookingServices()->with('service')->get();
                $servicesTotal = $services->sum('total_price');
                $gateway = $payment->gateway_charge ?? 0;
            @endphp

            @if($boothDisplay->isNotEmpty())
            <h3 style="color: #1e293b; margin-top: 25px;">Booth Details</h3>
            <table>
                <thead>
                    <tr>
                        <th>Booth</th>
                        <th>Size (sq ft)</th>
                        <th>Type</th>
                        <th>Price (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($boothDisplay as $booth)
                    <tr>
                        <td>{{ $booth['name'] ?? '—' }}</td>
                        <td>{{ $booth['size_sqft'] ?? '—' }}</td>
                        <td>{{ $booth['type'] ?? '—' }}</td>
                        <td>₹{{ number_format($booth['price'] ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($services->isNotEmpty())
            <h3 style="color: #1e293b; margin-top: 25px;">Additional Services</h3>
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Quantity</th>
                        <th>Unit Price (₹)</th>
                        <th>Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $service)
                    <tr>
                        <td>{{ $service->service->name ?? 'Service' }}</td>
                        <td>{{ $service->quantity }}</td>
                        <td>₹{{ number_format($service->unit_price, 2) }}</td>
                        <td>₹{{ number_format($service->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <h3 style="color: #1e293b; margin-top: 25px;">Payment Summary</h3>
            <table>
                <tr>
                    <td>Booth Total</td>
                    <td style="text-align: right;">₹{{ number_format($boothTotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Services Total</td>
                    <td style="text-align: right;">₹{{ number_format($servicesTotal, 2) }}</td>
                </tr>
                @if($gateway > 0)
                <tr>
                    <td>Gateway Charge</td>
                    <td style="text-align: right;">₹{{ number_format($gateway, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row grand-total">
                    <td><strong>Total Paid</strong></td>
                    <td style="text-align: right;"><strong>₹{{ number_format($payment->amount + $gateway, 2) }}</strong></td>
                </tr>
            </table>

            @if(!$isAdmin)
                <p style="margin-top: 25px;">
                    <strong>Note:</strong> This payment receipt is for your records. Please keep this email for your reference.
                </p>
            @endif
        </div>

        <div class="footer">
            <p>This is an automated email from the Exhibition Management System.</p>
            <p>Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Exhibition Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
