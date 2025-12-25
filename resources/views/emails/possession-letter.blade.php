<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Possession Letter</title>
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
        .success-badge {
            display: inline-block;
            padding: 8px 16px;
            background-color: #d1fae5;
            color: #065f46;
            border-radius: 20px;
            font-weight: 600;
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        .button:hover {
            background-color: #4338ca;
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
            <h1>Possession Letter Generated</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $booking->user->name }},</p>
            
            <p>We are pleased to inform you that your possession letter has been generated for the following booking:</p>

            <div class="success-badge">
                ✓ All Payments Completed
            </div>

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
                    <span class="detail-label">Booth(s):</span>
                    <span class="detail-value">
                        @if($booking->booth)
                            {{ $booking->booth->name }}
                        @else
                            N/A
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value"><strong>₹{{ number_format($booking->total_amount, 2) }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount Paid:</span>
                    <span class="detail-value"><strong style="color: #059669;">₹{{ number_format($booking->paid_amount, 2) }}</strong></span>
                </div>
            </div>

            <p><strong>Your possession letter has been attached to this email.</strong></p>

            <p>You can also download the possession letter from your booking details page in the system.</p>

            <div style="text-align: center;">
                <a href="{{ route('bookings.show', $booking->id) }}" class="button">View Booking Details</a>
            </div>

            <p style="margin-top: 25px;">
                <strong>Important Notes:</strong>
            </p>
            <ul>
                <li>Please keep this possession letter safe as it serves as proof of your booth allocation.</li>
                <li>Ensure compliance with all exhibition rules and regulations.</li>
                <li>Contact the exhibition management team if you have any questions or concerns.</li>
            </ul>

            <p>We look forward to your participation in the exhibition and wish you a successful event.</p>
        </div>

        <div class="footer">
            <p>This is an automated email from the Exhibition Management System.</p>
            <p>Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Exhibition Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

