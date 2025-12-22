@php
    $isWallet = $booking->cancellation_type === 'wallet_credit';
@endphp

<p>Dear {{ $booking->user->name }},</p>

<p>Your cancellation request for booking <strong>#{{ $booking->booking_number }}</strong> ({{ $booking->exhibition->name ?? '' }}) has been processed by our team.</p>

@if($booking->cancellation_reason)
    <p><strong>Your reason:</strong> {{ $booking->cancellation_reason }}</p>
@endif

<p>
    <strong>Cancellation type:</strong>
    {{ $isWallet ? 'Wallet Credit' : 'Refund to Bank Account' }}<br>
    <strong>Amount {{ $isWallet ? 'credited to wallet' : 'to be refunded' }}:</strong>
    ₹{{ number_format($booking->cancellation_amount ?? 0, 2) }}
</p>

@if($isWallet)
    <p>
        This wallet balance can be used only for booking another stall within the platform.
    </p>
@endif

<p>
    If you have any questions, please contact the exhibition support team.
</p>

<p>Regards,<br>
{{ config('app.name') }} Team</p>


