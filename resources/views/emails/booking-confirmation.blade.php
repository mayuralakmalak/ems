<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
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
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-confirmed {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.9rem;
            color: #64748b;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>@if($isAdmin) New Booking Request @else Booking Confirmation @endif</h1>
        </div>
        
        <div class="content">
            @if($isAdmin)
                <p>Dear Admin,</p>
                <p>A new booking request has been submitted and requires your review.</p>
            @else
                <p>Dear {{ $booking->user->name }},</p>
                <p>Thank you for your booking! We have received your booking request and it is currently pending admin approval.</p>
            @endif

            <div class="info-box">
                <div class="detail-row">
                    <span class="detail-label">Booking Number:</span>
                    <span class="detail-value"><strong>{{ $booking->booking_number }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Exhibition:</span>
                    <span class="detail-value">{{ $booking->exhibition->name ?? 'N/A' }}</span>
                </div>
                @if($booking->exhibition)
                <div class="detail-row">
                    <span class="detail-label">Exhibition Dates:</span>
                    <span class="detail-value">
                        @if($booking->exhibition->start_date && $booking->exhibition->end_date)
                            {{ $booking->exhibition->start_date->format('d M Y') }} - {{ $booking->exhibition->end_date->format('d M Y') }}
                        @else
                            N/A
                        @endif
                    </span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Exhibitor:</span>
                    <span class="detail-value">{{ $booking->user->name }} ({{ $booking->user->email }})</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value"><strong>₹{{ number_format($booking->total_amount, 2) }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Paid Amount:</span>
                    <span class="detail-value">₹{{ number_format($booking->paid_amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ $booking->status }}">
                            {{ strtoupper($booking->status) }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Approval Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ $booking->approval_status ?? 'pending' }}">
                            {{ strtoupper($booking->approval_status ?? 'PENDING') }}
                        </span>
                    </span>
                </div>
            </div>

            @php
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

            @php
                $services = $booking->bookingServices()->with('service')->get();
            @endphp

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

            @if(!$isAdmin)
                <p style="margin-top: 25px;">
                    <strong>Next Steps:</strong>
                </p>
                <ul>
                    <li>Please complete the payment to submit your booking request for admin approval.</li>
                    <li>Once payment is received and approved, your booking will be confirmed.</li>
                    <li>You will receive email notifications for any updates on your booking status.</li>
                </ul>
            @else
                <p style="margin-top: 25px;">
                    <strong>Action Required:</strong> Please review this booking request in the admin panel and approve or reject it accordingly.
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
