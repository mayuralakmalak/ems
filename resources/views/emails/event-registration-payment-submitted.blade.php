<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Payment</title>
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
            <h1>@if($isAdmin) Registration Payment (Admin) @else Payment Submitted @endif</h1>
        </div>
        <div class="content">
            @if($isAdmin)
                <p>Dear Admin,</p>
                <p>A payment has been submitted for an event registration. Please verify and approve or reject in the admin panel.</p>
            @else
                <p>Dear {{ $registration->full_name }},</p>
                <p>We have received your payment of <strong>₹{{ number_format($payment->amount, 2) }}</strong> for registration <strong>{{ $registration->registration_number }}</strong>. It is pending admin approval.</p>
            @endif
            <div class="info-box">
                <div class="detail-row"><span class="detail-label">Payment Number:</span><span class="detail-value"><strong>{{ $payment->payment_number }}</strong></span></div>
                <div class="detail-row"><span class="detail-label">Registration:</span><span class="detail-value">{{ $registration->registration_number }}</span></div>
                <div class="detail-row"><span class="detail-label">Exhibition:</span><span class="detail-value">{{ $registration->exhibition->name }}</span></div>
                <div class="detail-row"><span class="detail-label">Amount:</span><span class="detail-value">₹{{ number_format($payment->amount, 2) }}</span></div>
                <div class="detail-row"><span class="detail-label">Payment Method:</span><span class="detail-value">{{ strtoupper($payment->payment_method) }}</span></div>
                @if($payment->transaction_id)
                <div class="detail-row"><span class="detail-label">Transaction ID:</span><span class="detail-value">{{ $payment->transaction_id }}</span></div>
                @endif
            </div>
            @if($isAdmin)
                <p><strong>Action:</strong> Approve or reject this payment in the admin panel.</p>
            @else
                <p>You will receive a confirmation email once the payment is approved.</p>
            @endif
        </div>
        <div class="footer">
            <p>This is an automated email from the Exhibition Management System.</p>
            <p>&copy; {{ date('Y') }} Exhibition Management System.</p>
        </div>
    </div>
</body>
</html>
