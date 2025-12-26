<footer id="contact">
    <div class="footer-grid-overlay"></div>
    <div class="container position-relative">
        @php
            $generalSettings = \App\Models\Setting::getByGroup('general');
            $companyName = $generalSettings['company_name'] ?? 'EMS';
        @endphp
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="footer-logo">
                    @if(file_exists(public_path('images/footer-logo.png')))
                    <img src="{{ asset('images/footer-logo.png') }}" alt="{{ $companyName }}">
                    @else
                    <div class="footer-logo-text">{{ $companyName }}</div>
                    <div class="footer-logo-tagline">Exhibition Management System</div>
                    @endif
                </div>
                <p class="small pe-lg-5 mb-4" style="color: rgba(255, 255, 255, 0.7) !important;">
                    {{ $generalSettings['company_description'] ?? 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.' }}
                </p>
                <div class="footer-social">
                    @if(isset($generalSettings['facebook_url']) && $generalSettings['facebook_url'])
                        <a href="{{ $generalSettings['facebook_url'] }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f"></i></a>
                    @endif
                    @if(isset($generalSettings['instagram_url']) && $generalSettings['instagram_url'])
                        <a href="{{ $generalSettings['instagram_url'] }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-instagram"></i></a>
                    @endif
                    @if(isset($generalSettings['twitter_url']) && $generalSettings['twitter_url'])
                        <a href="{{ $generalSettings['twitter_url'] }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-x-twitter"></i></a>
                    @endif
                    @if(isset($generalSettings['youtube_url']) && $generalSettings['youtube_url'])
                        <a href="{{ $generalSettings['youtube_url'] }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-youtube"></i></a>
                    @endif
                </div>
            </div>
            
            <div class="col-lg-2 col-6 mb-4">
                <div class="footer-heading">THE COMPANY</div>
                <ul class="footer-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                    @auth
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Login</a></li>
                        <li><a href="{{ route('register') }}">Register</a></li>
                    @endauth
                </ul>
            </div>
            
            <div class="col-lg-2 col-6 mb-4">
                <div class="footer-heading">QUICK LINKS</div>
                <ul class="footer-list">
                    <li><a href="#">Faqs</a></li>
                    <li><a href="#">Help</a></li>
                    <li><a href="#">Message us</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="footer-heading">CONTACT US</div>
                <div class="contact-row">
                    <div class="contact-icon"><i class="icofont-location-pin"></i></div>
                    <div>
                        <div class="small" style="color: rgba(255, 255, 255, 0.5) !important;">{{ $generalSettings['company_address'] ?? '123, Lorem Ipsum dollar summit.' }}</div>
                    </div>
                </div>
                <div class="contact-row">
                    <div class="contact-icon"><i class="icofont-ui-cell-phone"></i></div>
                    <div>
                        <div class="small" style="color: rgba(255, 255, 255, 0.5) !important;">{{ $generalSettings['company_phone'] ?? '9016912113' }}</div>
                    </div>
                </div>
                <div class="contact-row">
                    <div class="contact-icon"><i class="icofont-envelope-open"></i></div>
                    <div>
                        <div class="small" style="color: rgba(255, 255, 255, 0.5) !important;">{{ $generalSettings['company_email'] ?? 'example@example.com' }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="copyright-area">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    {{ date('Y') }} All Rights Reserved.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    Design and Developed by: Alakmalak Technologies.
                </div>
            </div>
        </div>
    </div>
</footer>

