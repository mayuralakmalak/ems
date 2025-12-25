<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Due Reminder</title>
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
            background: linear-gradient(90deg, #dc2626, #ef4444);
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
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
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
        .amount-box {
            background-color: #fff7ed;
            border: 2px solid #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .amount-box .amount-label {
            font-size: 14px;
            color: #92400e;
            margin-bottom: 5px;
        }
        .amount-box .amount-value {
            font-size: 32px;
            font-weight: bold;
            color: #b45309;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4f46e5;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        .button:hover {
            background-color: #4338ca;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #92400e;
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
            <h1>Payment Due Reminder</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $payment->user->name }},</p>
            <p>This is a friendly reminder that your <strong>{{ $paymentNumber }}{{ $numberSuffix }} payment</strong> is due tomorrow.</p>

            <div class="warning">
                <strong>⚠️ Important:</strong> Please ensure your payment is completed by the due date to avoid any inconvenience.
            </div>

            <div class="info-box">
                <div class="detail-row">
                    <span class="detail-label">Payment Number:</span>
                    <span class="detail-value"><strong>{{ $payment->payment_number }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Exhibition:</span>
                    <span class="detail-value">{{ $payment->booking->exhibition->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Booking Number:</span>
                    <span class="detail-value">{{ $payment->booking->booking_number ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Type:</span>
                    <span class="detail-value">{{ ucfirst($payment->payment_type) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value"><strong>{{ $payment->due_date->format('d M Y') }}</strong></span>
                </div>
            </div>

            <div class="amount-box">
                <div class="amount-label">Amount Due</div>
                <div class="amount-value">₹{{ number_format($payment->amount, 2) }}</div>
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/ems-laravel/login') }}" class="button">Make Payment Now</a>
            </div>

            <p style="margin-top: 30px;">If you have already made the payment, please ignore this reminder. Thank you for your prompt attention to this matter.</p>

            <p>Best regards,<br>
            {{ config('app.name') }} Team</p>
        </div>

        <div class="footer">
            <p>This is an automated reminder. Please do not reply to this email.</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>

