@php
    $waNumber = config('app.whatsapp_number');
@endphp
@if(!empty($waNumber))
@php
    $waNumber = preg_replace('/[^0-9]/', '', $waNumber);
    $waUrl = 'https://wa.me/' . $waNumber;
    $waText = rawurlencode('Hello, I would like to know more about your services.');
@endphp
<!-- WhatsApp Floating Button -->
<a href="{{ $waUrl }}?text={{ $waText }}"
   class="whatsapp-float"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
@endif
