@extends('layouts.frontend')

@section('title', 'Rules for Exhibitors - ' . config('app.name', 'EMS'))

@push('styles')
<style>
    .rules-page { max-width: 900px; margin: 0 auto; padding: 50px 20px 80px; }
    .rules-page h1 { font-size: 2rem; font-weight: 700; color: #1a1a40; margin-bottom: 8px; }
    .rules-page .subtitle { color: #6c757d; margin-bottom: 40px; }
    .rules-page h2 { font-size: 1.25rem; font-weight: 700; color: #333; margin-top: 32px; margin-bottom: 12px; }
    .rules-page p, .rules-page li { color: #444; line-height: 1.7; margin-bottom: 12px; }
    .rules-page ul { padding-left: 24px; margin-bottom: 16px; }
    .rules-page ul li { margin-bottom: 8px; }
    .rules-page a { color: var(--primary-purple, #8C52FF); text-decoration: none; }
    .rules-page a:hover { text-decoration: underline; }
    .rules-page table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 0.95rem; }
    .rules-page table th, .rules-page table td { border: 1px solid #ddd; padding: 12px 16px; text-align: left; }
    .rules-page table th { background: #f5f5f5; font-weight: 600; color: #333; }
    .rules-page table tr:nth-child(even) { background: #fafafa; }
</style>
@endpush

@section('content')
<div class="rules-page">
    <h1>Rules for Exhibitors</h1>
    <p class="subtitle">Last updated: {{ date('F j, Y') }}</p>

    <h2>Stall Allocation</h2>
    <p>Stalls will be allocated strictly on a first-come, first-served basis. Booking will only be confirmed upon receipt of an official email confirmation and mandatory payment of 10% of the total stall cost within 24 hours of the booking. Failure to comply may result in the cancellation of your booking.</p>

    <h2>Exclusivity of Participation</h2>
    <p>Each stall is reserved for a single exhibiting company only. Co-exhibitors and stall sharing with other companies are strictly prohibited and will not be permitted under any circumstances.</p>

    <h2>Pavilion Participation & Sponsorship Requirement</h2>
    <p>If an exhibitor opts not to participate within the designated pavilion and wishes to exhibit in another area reserved for sponsors, it is mandatory to select a sponsorship package. Exhibitors without sponsorship will not be allowed to participate in other-pavilion areas.</p>

    <h2>Additional Charges for Premium Booths</h2>
    <ul>
        <li>2-Side open: 10% extra</li>
        <li>3-Side open: 15% extra</li>
        <li>4-Side open: 20% extra</li>
    </ul>

    <h2>Payment Policy</h2>
    <ul>
        <li>10% booking amount (immediate transfer).</li>
        <li>40% advance within 7 days after booking space.</li>
        <li>Balance 50% before July 31, 2025.</li>
    </ul>

    <h2>Cancellation Policy</h2>
    <ul>
        <li>50% of total participation charges on cancellation before 31st July 2025.</li>
        <li>No cancellation will be accepted after 31st July 2025.</li>
    </ul>
</div>
@endsection
