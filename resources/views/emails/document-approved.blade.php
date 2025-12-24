<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Approved</title>
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
            background: linear-gradient(90deg, #10b981, #059669);
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
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
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
            text-align: right;
        }
        .comments-box {
            background-color: #f8f9fa;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .comments-box .label {
            font-weight: 600;
            color: #059669;
            margin-bottom: 8px;
            display: block;
        }
        .comments-box .content {
            color: #1e293b;
            margin: 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #10b981;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        .button:hover {
            background-color: #059669;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
        .success-icon {
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Document Approved</h1>
        </div>
        
        <div class="content">
            <div class="success-icon">✓</div>
            
            <p>Dear {{ $document->user->name }},</p>
            <p>We are pleased to inform you that your document <strong>"{{ $document->name }}"</strong> has been <strong>approved</strong>.</p>

            <div class="info-box">
                <div class="detail-row">
                    <span class="detail-label">Document Name:</span>
                    <span class="detail-value"><strong>{{ $document->name }}</strong></span>
                </div>
                @if($document->booking && $document->booking->exhibition)
                <div class="detail-row">
                    <span class="detail-label">Exhibition:</span>
                    <span class="detail-value">{{ $document->booking->exhibition->name }}</span>
                </div>
                @endif
                @if($document->booking)
                <div class="detail-row">
                    <span class="detail-label">Booking Number:</span>
                    <span class="detail-value">{{ $document->booking->booking_number ?? 'N/A' }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value"><strong style="color: #10b981;">Approved</strong></span>
                </div>
                @if($document->expiry_date)
                <div class="detail-row">
                    <span class="detail-label">Expiry Date:</span>
                    <span class="detail-value">{{ $document->expiry_date->format('d M Y') }}</span>
                </div>
                @endif
            </div>

            @if($comments)
            <div class="comments-box">
                <span class="label">Verification Comments:</span>
                <div class="content">{{ $comments }}</div>
            </div>
            @endif

            <div style="text-align: center;">
                <a href="{{ url('/ems-laravel/dashboard') }}" class="button">View Dashboard</a>
            </div>

            <p style="margin-top: 30px;">Thank you for submitting your documents. If you have any questions, please contact our support team.</p>

            <p>Best regards,<br>
            {{ config('app.name') }} Team</p>
        </div>

        <div class="footer">
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>

