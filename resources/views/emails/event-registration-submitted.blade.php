<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .email-container { background-color: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(90deg, #4f46e5, #6366f1); color: #fff; padding: 20px; border-radius: 8px 8px 0 0; margin: -30px -30px 20px -30px; }
        .header h1 { margin: 0; font-size: 24px; }
        .info-box { background-color: #f8f9fa; border-left: 4px solid #4f46e5; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .detail-label { font-weight: 600; color: #64748b; }
        .detail-value { color: #1e293b; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 0.9rem; color: #64748b; text-align: center; }
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
            <h1>@if($isAdmin) New {{ ucfirst($registration->type) }} Registration @else Registration Submitted @endif</h1>
        </div>
        <div class="content">
            @if($isAdmin)
                <p>Dear Admin,</p>
                <p>A new event registration has been submitted and requires your approval.</p>
            @else
                <p>Dear {{ $registration->full_name }},</p>
                <p>Thank you for registering. We have received your {{ $registration->type }} registration for <strong>{{ $registration->exhibition->name }}</strong>. It is pending admin approval.</p>
            @endif
            <div class="info-box">
                <div class="detail-row"><span class="detail-label">Registration Number:</span><span class="detail-value"><strong>{{ $registration->registration_number }}</strong></span></div>
                <div class="detail-row"><span class="detail-label">Type:</span><span class="detail-value">{{ ucfirst($registration->type) }}</span></div>
                <div class="detail-row"><span class="detail-label">Exhibition:</span><span class="detail-value">{{ $registration->exhibition->name }}</span></div>
                <div class="detail-row"><span class="detail-label">Name:</span><span class="detail-value">{{ $registration->full_name }}</span></div>
                <div class="detail-row"><span class="detail-label">Email:</span><span class="detail-value">{{ $registration->email }}</span></div>
                <div class="detail-row"><span class="detail-label">Phone:</span><span class="detail-value">{{ $registration->phone }}</span></div>
                <div class="detail-row"><span class="detail-label">Fee Amount:</span><span class="detail-value">₹{{ number_format($registration->fee_amount, 2) }}</span></div>
                @if($registration->fee_tier)
                <div class="detail-row"><span class="detail-label">Fee Tier:</span><span class="detail-value">{{ ucfirst(str_replace('_', ' ', $registration->fee_tier)) }}</span></div>
                @endif
            </div>
            @if(!$isAdmin && $registration->fee_amount > 0)
                <p>Please complete payment using the link sent in the confirmation email. After payment and admin approval, your registration will be confirmed.</p>
            @endif
            @if($isAdmin)
                <p><strong>Action:</strong> Review and approve or reject this registration in the admin panel.</p>
            @endif
        </div>
        <div class="footer">
            <p>This is an automated email from the Exhibition Management System.</p>
            <p>&copy; {{ date('Y') }} Exhibition Management System.</p>
        </div>
    </div>
</body>
</html>
