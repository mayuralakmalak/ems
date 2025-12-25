<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Possession Letter - {{ $booking->booking_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4f46e5;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-top: 5px;
        }
        .content {
            margin: 30px 0;
        }
        .date-section {
            text-align: right;
            margin-bottom: 30px;
        }
        .date-section p {
            margin: 5px 0;
        }
        .address-section {
            margin-bottom: 30px;
        }
        .address-section strong {
            display: block;
            margin-bottom: 10px;
            color: #1e293b;
        }
        .subject {
            font-size: 16px;
            font-weight: bold;
            margin: 30px 0 20px 0;
            color: #1e293b;
        }
        .body-text {
            text-align: justify;
            margin-bottom: 20px;
            line-height: 1.8;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            background-color: #f8fafc;
        }
        .details-table th,
        .details-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }
        .details-table th {
            background-color: #4f46e5;
            color: white;
            font-weight: bold;
            width: 35%;
        }
        .details-table td {
            background-color: white;
        }
        .booth-list {
            margin: 10px 0;
            padding-left: 20px;
        }
        .booth-list li {
            margin: 5px 0;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }
        .signature-section {
            margin-top: 60px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 250px;
            margin-top: 60px;
        }
        .signature-label {
            margin-top: 5px;
            font-weight: bold;
        }
        .terms-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f1f5f9;
            border-left: 4px solid #4f46e5;
        }
        .terms-section h4 {
            margin-top: 0;
            color: #1e293b;
        }
        .terms-section ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .terms-section li {
            margin: 8px 0;
        }
        .amount-highlight {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="header">
        @php
            $generalSettings = \App\Models\Setting::getByGroup('general');
            $companyLogo = $generalSettings['company_logo'] ?? null;
            $logoPath = null;
            if ($companyLogo && \Storage::disk('public')->exists($companyLogo)) {
                $logoPath = storage_path('app/public/' . $companyLogo);
            }
        @endphp
        @if($logoPath && file_exists($logoPath))
            <div style="margin-bottom: 15px;">
                <img src="{{ $logoPath }}" alt="Company Logo" style="max-height: 60px; max-width: 200px; object-fit: contain;">
            </div>
        @endif
        <h1>POSSESSION LETTER</h1>
        <div class="subtitle">{{ $generalSettings['company_name'] ?? 'Exhibition Management System' }}</div>
    </div>

    <div class="date-section">
        <p><strong>Date:</strong> {{ now()->format('d F Y') }}</p>
        <p><strong>Letter No:</strong> PL/{{ $booking->booking_number }}/{{ now()->format('Y') }}</p>
    </div>

    <div class="address-section">
        <strong>To,</strong>
        <p style="margin: 0;">
            {{ $booking->user->name }}<br>
            @if($booking->user->company_name)
                {{ $booking->user->company_name }}<br>
            @endif
            @if($booking->user->address)
                {{ $booking->user->address }}<br>
            @endif
            @if($booking->user->city)
                {{ $booking->user->city }},
            @endif
            @if($booking->user->state)
                {{ $booking->user->state }}
            @endif
            @if($booking->user->pincode)
                - {{ $booking->user->pincode }}
            @endif
            @if($booking->user->country)
                <br>{{ $booking->user->country }}
            @endif
        </p>
    </div>

    <div class="subject">
        Subject: Possession Letter for Booth(s) - {{ $booking->exhibition->name ?? 'Exhibition' }}
    </div>

    <div class="body-text">
        <p>Dear {{ $booking->user->name }},</p>
        
        <p>This is to confirm that you have been granted possession of the following booth(s) for the exhibition as per the details mentioned below:</p>
    </div>

    <table class="details-table">
        <tr>
            <th>Booking Number:</th>
            <td><strong>{{ $booking->booking_number }}</strong></td>
        </tr>
        <tr>
            <th>Exhibition Name:</th>
            <td>{{ $booking->exhibition->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Exhibition Dates:</th>
            <td>
                @if($booking->exhibition && $booking->exhibition->start_date && $booking->exhibition->end_date)
                    {{ $booking->exhibition->start_date->format('d M Y') }} to {{ $booking->exhibition->end_date->format('d M Y') }}
                @else
                    N/A
                @endif
            </td>
        </tr>
        <tr>
            <th>Exhibition Venue:</th>
            <td>{{ $booking->exhibition->venue ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Exhibition City:</th>
            <td>{{ $booking->exhibition->city ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Booth(s) Allocated:</th>
            <td>
                @php
                    $boothNames = [];
                    if ($booking->booth) {
                        $boothNames[] = $booking->booth->name . ($booking->booth->size_sqft ? ' (' . $booking->booth->size_sqft . ' sq. ft.)' : '');
                    }
                    $selectedBoothIds = $booking->selected_booth_ids;
                    if ($selectedBoothIds && is_array($selectedBoothIds) && !empty($selectedBoothIds)) {
                        // Convert to array to avoid indirect modification issues
                        $selectedBoothIdsArray = array_values($selectedBoothIds);
                        // Get first item to check format
                        $firstItem = !empty($selectedBoothIdsArray) ? $selectedBoothIdsArray[0] : null;
                        if (is_array($firstItem) && isset($firstItem['name'])) {
                            // Array of objects format: [{'id': 1, 'name': 'B001'}, ...]
                            foreach ($selectedBoothIdsArray as $boothData) {
                                if (isset($boothData['name'])) {
                                    $name = $boothData['name'];
                                    if (isset($boothData['size_sqft'])) {
                                        $name .= ' (' . $boothData['size_sqft'] . ' sq. ft.)';
                                    }
                                    if (!in_array($name, $boothNames)) {
                                        $boothNames[] = $name;
                                    }
                                }
                            }
                        }
                    }
                @endphp
                @if(count($boothNames) > 0)
                    @if(count($boothNames) == 1)
                        <strong>{{ $boothNames[0] }}</strong>
                    @else
                        <ul class="booth-list">
                            @foreach($boothNames as $boothName)
                                <li><strong>{{ $boothName }}</strong></li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    N/A
                @endif
            </td>
        </tr>
        <tr>
            <th>Total Amount:</th>
            <td class="amount-highlight">₹{{ number_format($booking->total_amount, 2) }}</td>
        </tr>
        <tr>
            <th>Amount Paid:</th>
            <td class="amount-highlight">₹{{ number_format($booking->paid_amount, 2) }}</td>
        </tr>
        <tr>
            <th>Payment Status:</th>
            <td><strong style="color: #059669;">Fully Paid</strong></td>
        </tr>
    </table>

    <div class="body-text">
        <p>You are hereby granted possession of the above-mentioned booth(s) for the duration of the exhibition. Please note the following terms and conditions:</p>
    </div>

    <div class="terms-section">
        <h4>Terms and Conditions:</h4>
        <ul>
            <li>You are required to maintain the booth in good condition and comply with all exhibition rules and regulations.</li>
            <li>The booth must be set up as per the guidelines provided by the exhibition management.</li>
            <li>Any damage to the booth or exhibition property will be charged to your account.</li>
            <li>You must vacate the booth on or before the exhibition end date.</li>
            <li>All outstanding dues, if any, must be cleared before taking possession.</li>
            <li>This possession letter is valid only for the specified exhibition and dates.</li>
            <li>Any violation of exhibition rules may result in immediate termination of possession.</li>
        </ul>
    </div>

    <div class="body-text">
        <p>Please acknowledge receipt of this possession letter and ensure compliance with all terms and conditions mentioned above.</p>
        
        <p>We look forward to your participation in the exhibition and wish you a successful event.</p>
    </div>

    <div class="footer">
        <p><strong>For Exhibition Management System</strong></p>
        
        <div class="signature-section">
            <div class="signature-line"></div>
            <div class="signature-label">Authorized Signatory</div>
        </div>
    </div>

    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b; text-align: center;">
        <p>This is a system-generated document. For any queries, please contact the exhibition management team.</p>
        <p>Generated on: {{ now()->format('d M Y, h:i A') }}</p>
    </div>
</body>
</html>

