<p>Hello,</p>

<p>An exhibitor has submitted a <strong>wallet refund request</strong> for a special discount amount (no booth cancellation).</p>

<p><strong>Request #{{ $refundRequest->id }}</strong></p>
<ul>
    <li><strong>Exhibitor:</strong> {{ $refundRequest->user->name }} ({{ $refundRequest->user->email }})</li>
    <li><strong>Amount:</strong> ₹{{ number_format($refundRequest->amount, 2) }}</li>
    <li><strong>Submitted at:</strong> {{ $refundRequest->created_at->format('d M Y, h:i A') }}</li>
    @if($refundRequest->reason)
    <li><strong>Reason:</strong> {{ $refundRequest->reason }}</li>
    @endif
</ul>

<p>Please log in to the admin panel and go to <strong>Wallet Refund Requests</strong> to review and approve or reject this request.</p>

<p>Regards,<br>{{ config('app.name') }} Team</p>
