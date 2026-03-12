@if($isAdmin)
<p>Hello,</p>
<p>A wallet refund request has been <strong>approved</strong> and the amount has been debited from the exhibitor's wallet.</p>
@else
<p>Dear {{ $refundRequest->user->name }},</p>
<p>Your request to refund the special discount amount from your wallet has been <strong>approved</strong>.</p>
@endif

<p><strong>Request #{{ $refundRequest->id }}</strong></p>
<ul>
    <li><strong>Amount refunded:</strong> ₹{{ number_format($refundRequest->amount, 2) }}</li>
    <li><strong>Processed at:</strong> {{ optional($refundRequest->processed_at)->format('d M Y, h:i A') }}</li>
    @if($refundRequest->processor)
    <li><strong>Processed by:</strong> {{ $refundRequest->processor->name }}</li>
    @endif
</ul>

@if($isAdmin)
<p><strong>Exhibitor:</strong> {{ $refundRequest->user->name }} ({{ $refundRequest->user->email }})</p>
@else
<p>The amount has been debited from your wallet. Our team will process the actual refund to you as per the agreed method. If you have any questions, contact the exhibition support team.</p>
@endif

<p>Regards,<br>{{ config('app.name') }} Team</p>
