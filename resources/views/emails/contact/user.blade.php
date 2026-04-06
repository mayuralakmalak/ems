<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We have received your message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 640px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(90deg, #4f46e5, #6366f1);
            color: #ffffff;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 20px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .content {
            margin: 10px 0 20px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin: 20px 0 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
        }
        .detail-value {
            color: #1e293b;
            text-align: right;
        }
        .message-box {
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 14px 16px;
            font-size: 14px;
            white-space: pre-line;
            color: #111827;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            @php
                $generalSettings = \App\Models\Setting::getByGroup('general');
                $companyLogo = $generalSettings['company_logo'] ?? null;
                $companyName = $generalSettings['company_name'] ?? config('app.name', 'EMS');
            @endphp
            @if($companyLogo && \Storage::disk('public')->exists($companyLogo))
                <div style="margin-bottom: 10px;">
                    <img src="{{ \Storage::url($companyLogo) }}" alt="{{ $companyName }}" style="max-height: 50px; max-width: 180px; object-fit: contain;">
                </div>
            @endif
            <h1>We have received your message</h1>
        </div>

        <div class="content">
            <p>Hi {{ $data['name'] }},</p>

            <p>Thank you for contacting us. We have received your enquiry with the details below:</p>

            <div class="section-title">Your enquiry</div>

            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value">{{ $data['name'] }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $data['email'] }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Subject</span>
                <span class="detail-value">{{ $data['subject'] }}</span>
            </div>

            <div class="section-title">Message</div>

            <div class="message-box">
                {{ $data['message'] }}
            </div>

            <p style="margin-top: 20px;">
                Our team will review your request and get back to you as soon as possible.
            </p>
        </div>

        <div class="footer">
            <p>Best regards,<br>{{ $companyName }} Team</p>
        </div>
    </div>
</body>
</html>

