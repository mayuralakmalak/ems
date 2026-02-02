<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<p>Our exhibitors\' privacy is critically important to us. At Radeecal Communications, we respect your rights to privacy and will comply with obligations under the Data Protection Acts. We have a few fundamental principles related to the management of the personal data of the parties associated with the company, as outlined below. This privacy policy applies to main website of Radeecal as well as the individual event-specific websites operated under Radeecal.</p>
<h2>Personal Information</h2>
<p>We may collect personal identification information from users in a variety of ways, including, when users visit our site, fill out a registration form, and in connection with other activities, services, features or resources we make available on our Site. Users may be asked for, as appropriate, name, email address, mailing address and phone number. Users may, however, visit our site anonymously. We will collect personal identification information from users only if they voluntarily submit such information to us. Users can always choose not to supply personal identification information, except that it may prevent them from engaging in certain site related activities.</p>
<h2>Non Personal Information</h2>
<p>We may collect non-personal identification information about users whenever they interact with our site. Non-personal identification information may include the browser name, the type of computer and technical information about users.</p>
<h2>Usage of collected data</h2>
<ul>
<li><strong>To personalize user experience</strong> – We use the contact details for the employees to get in touch with the exhibitors and deal with them personally.</li>
<li><strong>To improve customer service</strong> – Your information helps us to more effectively respond to your specific customer service requests and support needs.</li>
<li><strong>To send periodic emails</strong> – The email addresses users provide will only be used to send them information and updates pertaining to their registered event and inform them about the upcoming events.</li>
</ul>
<h2>Disclosure of Information</h2>
<p>We will not disclose your Personal Data to third parties unless you have consented to this disclosure or unless required to comply with any applicable law, a summons, a search warrant, a court or regulatory order, or other statutory requirement.</p>
<h2>Payment</h2>
<p>We use CCAvenue Payment Gateway for processing payments. They do not store your card data on their servers. The data is encrypted through the Payment Card Industry Data Security Standard (PCI-DSS) when processing payment. Your purchase transaction data is only used as long as is necessary to complete your purchase transaction. After that is complete, your purchase transaction information is not saved. Our payment gateway adheres to the standards set by PCI-DSS as managed by the PCI Security Standards Council, which is a joint effort of brands like Visa, MasterCard, American Express and Discover. PCI-DSS requirements help ensure the secure handling of credit card information by our store and its service providers.</p>
<h2>Changes to Privacy Policy</h2>
<p>Radeecal has the discretion to update this privacy policy at any time. You acknowledge and agree that it is your responsibility to review this privacy policy periodically and become aware of modifications. Your continued use of the site following the posting of changes to this policy will be deemed as your acceptance of those changes.</p>',
                'show_in_footer' => true,
                'show_in_header' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Terms and Conditions',
                'slug' => 'terms-and-conditions',
                'content' => '<p>Registration through booking form is binding. The Exhibition Terms and Conditions are an integral part of this binding online registration form. The below stated Terms and Conditions apply to all the companies and institutions that book onto any events organized by ALEMAI.</p>
<h2>Terms of Reference</h2>
<p>In this, the term \'exhibitor\' shall include all employees, staff and agents of any company, partnership firm or individual to whom space has been allocated for the purpose of participation. The term \'organizers\' shall mean ALEMAI, an Ahmedabad based industrial events company.</p>
<h2>Floor Plan</h2>
<ul><li>The Organizers reserve the right to alter the layout of the exhibition at any time and in any respect. We will always endeavor to contact affected Exhibitors should this be required.</li><li>Exhibition displays and furniture must stay within the allocated floor space at all times.</li></ul>
<h2>Allotment</h2>
<ul><li>Allotment of booth(s) will be made at the time of the registration according to the preference of the exhibitor. In case a particular booth is preferred by two or more companies, the booth will be allotted to the first one to register and make the payment.</li><li>The booth(s) allotted will be used solely by the participants for display of goods noted in their application form. Sub-letting of booth(s) or displaying goods not covered by the original application will not be allowed.</li></ul>
<h2>Stand Alteration</h2>
<ul><li>No alteration to the size or position of an exhibitor\'s stand is permitted without the prior written approval of organizers.</li><li>The organizers reserve the right to modify the layout of booth sites and pathways.</li><li>The organizers reserve the right to require exhibitors to make such alterations to their booths, as to the setting of exhibits as they reasonably feel necessary to maintain an acceptable standard of presentation or to avoid interference with the displays of other exhibitors.</li><li>Conversion of an allocated shell scheme site, to free design is not permitted. While reasonable fixings may be made to the flush plywood walls of the shell scheme, no alterations to the fascia structure or the format is permitted. Any attempt to do this will involve the reinstallation of the original structure at the expense of the exhibitor or his agent.</li><li>Booths may not overhang the allotted area, nor are any obstructions permitted on gangways, fire points, extinguishers, or emergency exits. Exhibitors are particularly requested to avoid a design that blocks other exhibitor booths.</li></ul>
<h2>Booth Interiors</h2>
<p>While the exhibitors are free to decorate their booths to the best of their ability for projecting the right image for their products and company, they should not cause any permanent damage to the walls, panels and floors through use of nails, paintings or any other such activity.</p>
<h2>Unoccupied space</h2>
<p>Every exhibitor shall occupy the full stand area booked by him. Should an exhibitor fail to take up the stand allocated to him, the organizers reserve the right to deal with the unoccupied booth as they think fit.</p>
<h2>No sub-letting</h2>
<p>The exhibitors cannot assign, sublet or grant licenses in respect of the whole or any part of the booth. Cards, advertisement or printed matter of persons or firms who are not bonafide exhibitors will not be exhibited or distributed from any booth except that an exhibitor may distribute cards, advertisements or printed matter in respect of companies or firms which are subsidiaries of exhibitor or the exhibitor\'s ultimate holding company. Any other such activities would attract penalty as decided by the organizers.</p>
<h2>Payment details</h2>
<p>ALEMAI has a clearly stated payment policy wherein 50% advance payment has to be made at the time of booking and the rest 50% before 60 days from the exhibition date. All payments must be done in favour of ALEMAI (payable at Ahmedabad) only. Rs. 500/- per square meter will be charged extra for the delay in payment as the penalty after due date.</p>
<h2>International Payment Terms</h2>
<ul><li>For international exhibitors, ALEMAI requires 100% advance payment at the time of booking to confirm participation. All payments must be made in favour of ALEMAI (payable at Ahmedabad) through approved international banking channels.</li><li>Please note that bookings will only be processed upon receipt of full payment. Any delay in payment may result in the forfeiture of space allocation without prior notice.</li><li>Bank transfer charges or international transaction fees, if any, must be borne by the exhibitor.</li></ul>
<h2>Inappropriate Behavior</h2>
<p>The organizers reserve the right to take away the possession of the stall from the exhibitors in case of any misbehavior or use of inappropriate language by any registered members of the stall with either the visitors or the organizers.</p>
<h2>Default on Payments</h2>
<p>The organizers reserve the right to cancel any reservation of space in the event of an exhibitor not having paid the dues of rental charges as stipulated on the rate card.</p>
<h2>Promotional Activity</h2>
<p>The exhibitors are not allowed to indulge in any extra promotional activity like putting up unaccounted banners, standees or any other marketing tool. The exhibitors can carry out only those activities that they have registered and paid for. Any such activity would lead to a legal action and even losing stall possession.</p>
<h2>Cancellation charges</h2>
<p>Advance payment made till cancellation date towards participation charges will be considered as a forfeiture amount, if cancellation is done 60 days before the exhibition date. In case the cancellation is done within the 60 days, the advance payment of 50% will not be refunded. If the exhibitor wishes to register for the next series of the event, the entire amount will be transferred to the account of that next event.</p>
<h2>Insurance</h2>
<p>Insurance of the exhibits and the property of the booth will be responsibility of the individual exhibitors. The organizers shall not be responsible in any way for personal injury to the exhibitor or his staff, agents, invitees or licensees, however caused during the event.</p>
<h2>Consequential Loss</h2>
<p>In case of the show being cancelled or suspended in whole or in part for causes not in the producer\'s control, the producer\'s do not accept any consequential liability in any such eventuality.</p>
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
<p>Any dispute between the organizers and an exhibitor is subject to the jurisdiction of the courts in Ahmedabad only.</p>',
                'show_in_footer' => true,
                'show_in_header' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Refund and Cancellation Policy',
                'slug' => 'refund-and-cancellation-policy',
                'content' => '<p>Radeecal Communications believes in organizing events and providing services with the customers at the center of the process. We understand that various unforeseen circumstances could arise for the exhibitor where they need to cancel the registration at any particular event and thus we have a clear and concise cancellation policy.</p>
<h2>Registration Cancellation Policy</h2>
<p>Should your circumstances change and you are unable to attend an Event, you must contact Radeecal office by no later than 60 days prior to the commencement of the Event. Advance Payment made till cancellation date towards participants charges will be considered as a forfeiture amount, if cancellation is done within 60 days of exhibition date. Should you cancel less than 60 days prior to the commencement of the event; a cancellation fee will be applicable.</p>
<table><thead><tr><th>TIME PERIOD</th><th>CANCELLATION FEE</th><th>REFUND AMOUNT</th></tr></thead><tbody><tr><td>From time of Registration until 60 days before event</td><td>–</td><td>Payment made till date</td></tr><tr><td>From 60 days before event until the event day</td><td>Advance booking amount of 50%</td><td>Payment made till date – Cancellation fee of 50%</td></tr></tbody></table>
<p><em>* In case the company wishes to book in the next series of the event, the full amount will be transferred to the next event, without any cancellation charges.</em></p>
<h2>Cancellation and Refund Process</h2>
<p>In case of a cancellation, the exhibitor should contact the Radeecal office through an email to the following address:</p>
<p><strong>Email ID:</strong> <a href="mailto:events@radeecal.in">events@radeecal.in</a></p>
<p>The date of email will be considered as the cancellation date, and accordingly the cancellation fee will be charged.</p>
<p>Radeecal will refund the amount payable, after taking into consideration the relevant cancellation policy, within 30 business days of receiving a refund request. Credit card surcharges are non-refundable. Refunds will only be processed to the credit card or bank account of the individual, organisation or institution from which the payment was received. Should payment have been via cheque you will be contacted to confirm your current mailing address, and a cheque will be mailed to you.</p>
<h2>Event Cancellation or Postponement</h2>
<p>Should an Event be cancelled or postponed due to unforeseen circumstances, Radeecal will endeavor to process a full refund within 90 days of such circumstances becoming known.</p>
<p><strong>REFUND AND CANCELLATION POLICY*</strong></p>',
                'show_in_footer' => true,
                'show_in_header' => false,
                'is_active' => true,
            ],
            [
                'title' => 'Rules for Exhibitors',
                'slug' => 'rules-for-exhibitors',
                'content' => '<h2>Stall Allocation</h2>
<p>Stalls will be allocated strictly on a first-come, first-served basis. Booking will only be confirmed upon receipt of an official email confirmation and mandatory payment of 10% of the total stall cost within 24 hours of the booking. Failure to comply may result in the cancellation of your booking.</p>
<h2>Exclusivity of Participation</h2>
<p>Each stall is reserved for a single exhibiting company only. Co-exhibitors and stall sharing with other companies are strictly prohibited and will not be permitted under any circumstances.</p>
<h2>Pavilion Participation & Sponsorship Requirement</h2>
<p>If an exhibitor opts not to participate within the designated pavilion and wishes to exhibit in another area reserved for sponsors, it is mandatory to select a sponsorship package. Exhibitors without sponsorship will not be allowed to participate in other-pavilion areas.</p>
<h2>Additional Charges for Premium Booths</h2>
<ul><li>2-Side open: 10% extra</li><li>3-Side open: 15% extra</li><li>4-Side open: 20% extra</li></ul>
<h2>Payment Policy</h2>
<ul><li>10% booking amount (immediate transfer).</li><li>40% advance within 7 days after booking space.</li><li>Balance 50% before July 31, 2025.</li></ul>
<h2>Cancellation Policy</h2>
<ul><li>50% of total participation charges on cancellation before 31st July 2025.</li><li>No cancellation will be accepted after 31st July 2025.</li></ul>',
                'show_in_footer' => true,
                'show_in_header' => false,
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            CmsPage::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
