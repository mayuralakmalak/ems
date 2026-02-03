<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking & Payment Summary</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 640px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .email-container { background-color: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(90deg, #4f46e5, #6366f1); color: #fff; padding: 20px; border-radius: 8px 8px 0 0; margin: -30px -30px 20px -30px; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { margin: 20px 0; }
        .section-title { font-size: 16px; font-weight: 700; color: #1e293b; margin: 20px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #e2e8f0; }
        .booking-block { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .booking-block h3 { margin: 0 0 12px; font-size: 15px; color: #475569; }
        .detail-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #64748b; }
        .detail-value { color: #1e293b; text-align: right; }
        .summary-box { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 2px solid #0ea5e9; border-radius: 8px; padding: 20px; margin: 24px 0; }
        .summary-box .row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 15px; }
        .summary-box .total { font-weight: 700; font-size: 18px; border-top: 2px solid #0ea5e9; margin-top: 8px; padding-top: 12px; }
        .badge-paid { background: #10b981; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        .badge-pending { background: #f59e0b; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; }
        table.payments { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 8px; }
        table.payments th, table.payments td { padding: 8px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        table.payments th { background: #f1f5f9; color: #475569; font-weight: 600; }
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
                <div style="margin-bottom: 12px;">
                    <img src="{{ \Storage::url($companyLogo) }}" alt="Logo" style="max-height: 50px; max-width: 180px; object-fit: contain;">
                </div>
            @endif
            <h1>Booking & Payment Summary</h1>
        </div>

        <div class="content">
            <p>Dear {{ $user->name }},</p>
            <p>Please find below a summary of your booking(s) and payment status{{ $exhibition ? ' for ' . $exhibition->name : '' }}.</p>

            @php
                $grandTotal = 0;
                $grandPaid = 0;
                $grandPending = 0;
            @endphp

            <div class="section-title">Your Bookings</div>

            @foreach($bookings as $booking)
                @php
                    $payments = $booking->payments;
                    $bookingTotal = (float) $booking->total_amount;
                    $bookingPaid = (float) $booking->paid_amount;
                    $bookingPending = $bookingTotal - $bookingPaid;
                    $grandTotal += $bookingTotal;
                    $grandPaid += $bookingPaid;
                    $grandPending += $bookingPending;
                @endphp
                <div class="booking-block">
                    <h3>{{ $booking->exhibition->name ?? 'Exhibition' }} — {{ $booking->booking_number ?? 'N/A' }}</h3>
                    <div class="detail-row">
                        <span class="detail-label">Booking Number</span>
                        <span class="detail-value">{{ $booking->booking_number ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">{{ ucfirst($booking->status ?? 'N/A') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Total</span>
                        <span class="detail-value">₹{{ number_format($bookingTotal, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Paid</span>
                        <span class="detail-value">₹{{ number_format($bookingPaid, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Pending</span>
                        <span class="detail-value">₹{{ number_format($bookingPending, 2) }}</span>
                    </div>
                    @if($payments->isNotEmpty())
                        <table class="payments">
                            <thead>
                                <tr>
                                    <th>Payment #</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments->sortBy('due_date') as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_number }}</td>
                                        <td>{{ ucfirst($payment->payment_type ?? '—') }}</td>
                                        <td>₹{{ number_format($payment->amount ?? 0, 2) }}</td>
                                        <td>{{ $payment->due_date ? $payment->due_date->format('d M Y') : '—' }}</td>
                                        <td>
                                            @if(($payment->status ?? '') === 'completed' && ($payment->approval_status ?? '') === 'approved')
                                                <span class="badge-paid">Paid</span>
                                            @else
                                                <span class="badge-pending">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endforeach

            <div class="section-title">Overall Summary</div>
            <div class="summary-box">
                <div class="row">
                    <span class="detail-label">Payment Total (all bookings)</span>
                    <span class="detail-value"><strong>₹{{ number_format($grandTotal, 2) }}</strong></span>
                </div>
                <div class="row">
                    <span class="detail-label">Paid</span>
                    <span class="detail-value" style="color: #059669;">₹{{ number_format($grandPaid, 2) }}</span>
                </div>
                <div class="row">
                    <span class="detail-label">Pending</span>
                    <span class="detail-value" style="color: #b45309;">₹{{ number_format($grandPending, 2) }}</span>
                </div>
                <div class="row total">
                    <span>Balance Due</span>
                    <span>₹{{ number_format($grandPending, 2) }}</span>
                </div>
            </div>

            <p>If you have any questions, please contact our support team.</p>
            <p>Best regards,<br>{{ config('app.name') }} Team</p>
        </div>

        <div class="footer">
            <p>This is an automated summary. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
