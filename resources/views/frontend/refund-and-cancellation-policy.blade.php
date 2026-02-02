@extends('layouts.frontend')

@section('title', 'Refund and Cancellation Policy - ' . config('app.name', 'EMS'))

@push('styles')
<style>
    .policy-page { max-width: 900px; margin: 0 auto; padding: 50px 20px 80px; }
    .policy-page h1 { font-size: 2rem; font-weight: 700; color: #1a1a40; margin-bottom: 8px; }
    .policy-page .subtitle { color: #6c757d; margin-bottom: 40px; }
    .policy-page h2 { font-size: 1.25rem; font-weight: 700; color: #333; margin-top: 32px; margin-bottom: 12px; }
    .policy-page p, .policy-page li { color: #444; line-height: 1.7; margin-bottom: 12px; }
    .policy-page ul { padding-left: 24px; margin-bottom: 16px; }
    .policy-page ul li { margin-bottom: 8px; }
    .policy-page a { color: var(--primary-purple, #8C52FF); text-decoration: none; }
    .policy-page a:hover { text-decoration: underline; }
    .policy-page table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 0.95rem; }
    .policy-page table th, .policy-page table td { border: 1px solid #ddd; padding: 12px 16px; text-align: left; }
    .policy-page table th { background: #f5f5f5; font-weight: 600; color: #333; }
    .policy-page table tr:nth-child(even) { background: #fafafa; }
    .policy-page .table-note { font-size: 0.9rem; color: #555; margin-top: 8px; font-style: italic; }
</style>
@endpush

@section('content')
<div class="policy-page">
    <h1>Refund and Cancellation Policy</h1>
    <p class="subtitle">Last updated: {{ date('F j, Y') }}</p>

    <p>Radeecal Communications believes in organizing events and providing services with the customers at the center of the process. We understand that various unforeseen circumstances could arise for the exhibitor where they need to cancel the registration at any particular event and thus we have a clear and concise cancellation policy.</p>

    <h2>Registration Cancellation Policy</h2>
    <p>Should your circumstances change and you are unable to attend an Event, you must contact Radeecal office by no later than 60 days prior to the commencement of the Event. Advance Payment made till cancellation date towards participants charges will be considered as a forfeiture amount, if cancellation is done within 60 days of exhibition date. Should you cancel less than 60 days prior to the commencement of the event; a cancellation fee will be applicable.</p>

    <table>
        <thead>
            <tr>
                <th>TIME PERIOD</th>
                <th>CANCELLATION FEE</th>
                <th>REFUND AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>From time of Registration until 60 days before event</td>
                <td>–</td>
                <td>Payment made till date</td>
            </tr>
            <tr>
                <td>From 60 days before event until the event day</td>
                <td>Advance booking amount of 50%</td>
                <td>Payment made till date – Cancellation fee of 50%</td>
            </tr>
        </tbody>
    </table>
    <p class="table-note">* In case the company wishes to book in the next series of the event, the full amount will be transferred to the next event, without any cancellation charges.</p>

    <h2>Cancellation and Refund Process</h2>
    <p>In case of a cancellation, the exhibitor should contact the Radeecal office through an email to the following address:</p>
    <p><strong>Email ID:</strong> <a href="mailto:events@radeecal.in">events@radeecal.in</a></p>
    <p>The date of email will be considered as the cancellation date, and accordingly the cancellation fee will be charged.</p>
    <p>Radeecal will refund the amount payable, after taking into consideration the relevant cancellation policy, within 30 business days of receiving a refund request. Credit card surcharges are non-refundable. Refunds will only be processed to the credit card or bank account of the individual, organisation or institution from which the payment was received. Should payment have been via cheque you will be contacted to confirm your current mailing address, and a cheque will be mailed to you.</p>

    <h2>Event Cancellation or Postponement</h2>
    <p>Should an Event be cancelled or postponed due to unforeseen circumstances, Radeecal will endeavor to process a full refund within 90 days of such circumstances becoming known.</p>

    <p><strong>REFUND AND CANCELLATION POLICY*</strong></p>
</div>
@endsection
