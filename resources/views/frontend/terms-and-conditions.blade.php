@extends('layouts.frontend')

@section('title', 'Terms and Conditions - ' . config('app.name', 'EMS'))

@push('styles')
<style>
    .terms-page { max-width: 900px; margin: 0 auto; padding: 50px 20px 80px; }
    .terms-page h1 { font-size: 2rem; font-weight: 700; color: #1a1a40; margin-bottom: 8px; }
    .terms-page .subtitle { color: #6c757d; margin-bottom: 40px; }
    .terms-page h2 { font-size: 1.25rem; font-weight: 700; color: #333; margin-top: 32px; margin-bottom: 12px; }
    .terms-page p, .terms-page li { color: #444; line-height: 1.7; margin-bottom: 12px; }
    .terms-page ul { padding-left: 24px; margin-bottom: 16px; }
    .terms-page ul li { margin-bottom: 8px; }
    .terms-page a { color: var(--primary-purple, #8C52FF); text-decoration: none; }
    .terms-page a:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="terms-page">
    <h1>Terms and Conditions</h1>
    <p class="subtitle">Last updated: {{ date('F j, Y') }}</p>

    <p>Registration through booking form is binding. The Exhibition Terms and Conditions are an integral part of this binding online registration form. The below stated Terms and Conditions apply to all the companies and institutions that book onto any events organized by ALEMAI.</p>

    <h2>Terms of Reference</h2>
    <p>In this, the term 'exhibitor' shall include all employees, staff and agents of any company, partnership firm or individual to whom space has been allocated for the purpose of participation. The term 'organizers' shall mean ALEMAI, an Ahmedabad based industrial events company.</p>

    <h2>Floor Plan</h2>
    <ul>
        <li>The Organizers reserve the right to alter the layout of the exhibition at any time and in any respect. We will always endeavor to contact affected Exhibitors should this be required.</li>
        <li>Exhibition displays and furniture must stay within the allocated floor space at all times.</li>
    </ul>

    <h2>Allotment</h2>
    <ul>
        <li>Allotment of booth(s) will be made at the time of the registration according to the preference of the exhibitor. In case a particular booth is preferred by two or more companies, the booth will be allotted to the first one to register and make the payment.</li>
        <li>The booth(s) allotted will be used solely by the participants for display of goods noted in their application form. Sub-letting of booth(s) or displaying goods not covered by the original application will not be allowed.</li>
    </ul>

    <h2>Stand Alteration</h2>
    <ul>
        <li>No alteration to the size or position of an exhibitor's stand is permitted without the prior written approval of organizers.</li>
        <li>The organizers reserve the right to modify the layout of booth sites and pathways.</li>
        <li>The organizers reserve the right to require exhibitors to make such alterations to their booths, as to the setting of exhibits as they reasonably feel necessary to maintain an acceptable standard of presentation or to avoid interference with the displays of other exhibitors.</li>
        <li>Conversion of an allocated shell scheme site, to free design is not permitted. While reasonable fixings may be made to the flush plywood walls of the shell scheme, no alterations to the fascia structure or the format is permitted. Any attempt to do this will involve the reinstallation of the original structure at the expense of the exhibitor or his agent.</li>
        <li>Booths may not overhang the allotted area, nor are any obstructions permitted on gangways, fire points, extinguishers, or emergency exits. Exhibitors are particularly requested to avoid a design that blocks other exhibitor booths.</li>
    </ul>

    <h2>Booth Interiors</h2>
    <p>While the exhibitors are free to decorate their booths to the best of their ability for projecting the right image for their products and company, they should not cause any permanent damage to the walls, panels and floors through use of nails, paintings or any other such activity.</p>

    <h2>Unoccupied space</h2>
    <p>Every exhibitor shall occupy the full stand area booked by him. Should an exhibitor fail to take up the stand allocated to him, the organizers reserve the right to deal with the unoccupied booth as they think fit.</p>

    <h2>No sub-letting</h2>
    <p>The exhibitors cannot assign, sublet or grant licenses in respect of the whole or any part of the booth. Cards, advertisement or printed matter of persons or firms who are not bonafide exhibitors will not be exhibited or distributed from any booth except that an exhibitor may distribute cards, advertisements or printed matter in respect of companies or firms which are subsidiaries of exhibitor or the exhibitor's ultimate holding company. Any other such activities would attract penalty as decided by the organizers.</p>

    <h2>Payment details</h2>
    <p>ALEMAI has a clearly stated payment policy wherein 50% advance payment has to be made at the time of booking and the rest 50% before 60 days from the exhibition date. All payments must be done in favour of ALEMAI (payable at Ahmedabad) only. Rs. 500/- per square meter will be charged extra for the delay in payment as the penalty after due date.</p>

    <h2>International Payment Terms</h2>
    <ul>
        <li>For international exhibitors, ALEMAI requires 100% advance payment at the time of booking to confirm participation. All payments must be made in favour of ALEMAI (payable at Ahmedabad) through approved international banking channels.</li>
        <li>Please note that bookings will only be processed upon receipt of full payment. Any delay in payment may result in the forfeiture of space allocation without prior notice.</li>
        <li>Bank transfer charges or international transaction fees, if any, must be borne by the exhibitor.</li>
    </ul>

    <h2>Inappropriate Behavior</h2>
    <p>The organizers reserve the right to take away the possession of the stall from the exhibitors in case of any misbehavior or use of inappropriate language by any registered members of the stall with either the visitors or the organizers.</p>

    <h2>Default on Payments</h2>
    <p>The organizers reserve the right to cancel any reservation of space in the event of an exhibitor not having paid the dues of rental charges as stipulated on the rate card.</p>

    <h2>Promotional Activity</h2>
    <p>The exhibitors are not allowed to indulge in any extra promotional activity like putting up unaccounted banners, standees or any other marketing tool. The exhibitors can carry out only those activities that they have registered and paid for. Any such activity would lead to a legal action and even losing stall possession.</p>

    <h2>Cancellation charges</h2>
    <p>Advance payment made till cancellation date towards participation charges will be considered as a forfeiture amount, if cancellation is done 60 days before the exhibition date. In case the cancellation is done within the 60 days, the advance payment of 50% will not be refunded. If the exhibitor wishes to register for the next series of the event, the entire amount will be transferred in the account of that next event.</p>

    <h2>Insurance</h2>
    <p>Insurance of the exhibits and the property of the booth will be responsibility of the individual exhibitors. The organizers shall not be responsible in any way for personal injury to the exhibitor or his staff, agents, invitees or licensees, however caused during the event.</p>

    <h2>Consequential Loss</h2>
    <p>In case of the show being cancelled or suspended in whole or in part for causes not in the producer's control, the producer's do not accept any consequential liability in any such eventuality.</p>

    <h2>Security</h2>
    <p>Although a twenty-four-hour security service will be in operation throughout the event, exhibitors should take all possible precautions to minimize loss or damage to the equipment outside of show open hours.</p>

    <h2>Failure of Service</h2>
    <p>The organizers will use their best endeavors to ensure supply of services of the official contractors, but as the supply of such services are not within the contractors, neither they nor the organizers shall incur any liability to the exhibitor for any loss or damage, if any such service shall wholly or partially fail or cease to be available. Nor shall the exhibitor be entitled to any allowance in respect of rental due or paid under the contract.</p>

    <h2>Liability</h2>
    <p>The company, its directors, officials, employees and office holders or its agents, will not be liable in any way for any injury, theft, damage, loss or harm of any kind whatsoever or which occurs as a result of any reason whatsoever that may be caused to person or property while they are present on the area of the exhibition grounds. And no compensation will be provided for damages due to theft, lighting fire, explosion or rainwater. The company will not be liable for any losses whatsoever that may be caused to the exhibitors in the event that water or electricity is cut off or in case of any natural calamity.</p>

    <h2>Electrical Installation</h2>
    <p>All on-site electrical installation must be carried out by the officially appointed electrical contractor before the connection to the mains supply.</p>

    <h2>Government Taxes</h2>
    <p>Government tax(es) will be charged extra, if applicable.</p>

    <h2>Rights</h2>
    <p>The organizers reserve all the rights in connection with the event.</p>

    <h2>Jurisdiction</h2>
    <p>Any dispute between the organizers and an exhibitor is subject to the jurisdiction of the courts in Ahmedabad only.</p>
</div>
@endsection
