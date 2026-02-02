@extends('layouts.frontend')

@section('title', 'Privacy Policy - ' . config('app.name', 'EMS'))

@push('styles')
<style>
    .privacy-page { max-width: 900px; margin: 0 auto; padding: 50px 20px 80px; }
    .privacy-page h1 { font-size: 2rem; font-weight: 700; color: #1a1a40; margin-bottom: 8px; }
    .privacy-page .subtitle { color: #6c757d; margin-bottom: 40px; }
    .privacy-page h2 { font-size: 1.25rem; font-weight: 700; color: #333; margin-top: 32px; margin-bottom: 12px; }
    .privacy-page p, .privacy-page li { color: #444; line-height: 1.7; margin-bottom: 12px; }
    .privacy-page ul { padding-left: 24px; margin-bottom: 16px; }
    .privacy-page ul li { margin-bottom: 8px; }
    .privacy-page a { color: var(--primary-purple, #8C52FF); text-decoration: none; }
    .privacy-page a:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="privacy-page">
    <h1>Privacy Policy</h1>
    <p class="subtitle">Last updated: {{ date('F j, Y') }}</p>

    <p>Our exhibitors' privacy is critically important to us. At Radeecal Communications, we respect your rights to privacy and will comply with obligations under the Data Protection Acts. We have a few fundamental principles related to the management of the personal data of the parties associated with the company, as outlined below. This privacy policy applies to main website of Radeecal as well as the individual event-specific websites operated under Radeecal.</p>

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
    <p>Radeecal has the discretion to update this privacy policy at any time. You acknowledge and agree that it is your responsibility to review this privacy policy periodically and become aware of modifications. Your continued use of the site following the posting of changes to this policy will be deemed as your acceptance of those changes.</p>
</div>
@endsection
