<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
        @php
            $generalSettings = \App\Models\Setting::getByGroup('general');
            $companyLogo = $generalSettings['company_logo'] ?? null;
            $companyName = $generalSettings['company_name'] ?? 'EMS';
        @endphp
        <a href="{{ route('home') }}" class="navbar-brand">
            @if($companyLogo && \Storage::disk('public')->exists($companyLogo))
                <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $companyName }}" width="201" height="65">
            @else
                <span>{{ $companyName }}</span>
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="mainNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('exhibitions.*') || request()->routeIs('exhibitions.list') ? 'active' : '' }}" href="{{ route('exhibitions.list') }}">Exhibitions</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('#about') ? 'active' : '' }}" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('#contact') ? 'active' : '' }}" href="#contact">Contact Us</a></li>
                @php
                    $cmsHeaderPages = \App\Models\CmsPage::active()->showInHeader()->orderBy('title')->get();
                @endphp
                @foreach($cmsHeaderPages as $cmsPage)
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('cms-page') && request()->route('slug') == $cmsPage->slug ? 'active' : '' }}" href="{{ route('cms-page', $cmsPage->slug) }}">{{ $cmsPage->title }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="d-none d-lg-block">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-custom bg-gradient-purple">DASHBOARD</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-custom bg-gradient-purple">LOGIN / REGISTER</a>
            @endauth
        </div>
    </div>
</nav>

