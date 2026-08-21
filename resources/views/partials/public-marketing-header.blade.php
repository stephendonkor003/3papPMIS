@php
    $isLandingPage = request()->routeIs('landing.index');
    $landingSectionUrl = static fn (string $section): string => $isLandingPage
        ? '#'.$section
        : route('landing.index').'#'.$section;
    $exploreIsActive = request()->routeIs(
        'impact.*',
        'world.indicators.performance',
        'public.procurement.*',
        'discussion.*',
        'news.*'
    );
    $skipTarget = $skipTarget ?? '#main-content';
    $skipLabel = $skipLabel ?? 'Skip to main content';
@endphp

<a class="pmis-skip-link" href="{{ $skipTarget }}">{{ $skipLabel }}</a>

<div class="pmis-utility">
    <div class="pmis-container pmis-utility__inner">
        <span>Enterprise portfolio, programme, project cycle and fund management</span>
        <a href="mailto:info@3pap.com">info@3pap.com</a>
    </div>
</div>

<div class="mobile-nav-overlay" id="navOverlay" onclick="closeMobileNav()"></div>
<nav class="mobile-nav pmis-mobile-nav" id="mobileNav" aria-label="Mobile navigation" aria-hidden="true">
    <div class="mobile-nav-header">
        <img src="{{ asset('assets/images/3pap-logo-white.png') }}" alt="3PAP">
        <button class="mobile-nav-close" type="button" onclick="closeMobileNav()" aria-label="Close menu">&times;</button>
    </div>
    <a href="{{ $landingSectionUrl('platform') }}" onclick="closeMobileNav()">Platform</a>
    <a href="{{ $landingSectionUrl('capabilities') }}" onclick="closeMobileNav()">Capabilities</a>
    <a href="{{ $landingSectionUrl('lifecycle') }}" onclick="closeMobileNav()">How it works</a>
    <a href="{{ route('impact.map') }}" class="{{ request()->routeIs('impact.*') ? 'active' : '' }}" onclick="closeMobileNav()">Portfolio insights</a>
    <a href="{{ route('world.indicators.performance') }}" class="{{ request()->routeIs('world.indicators.performance') ? 'active' : '' }}" onclick="closeMobileNav()">Performance analytics</a>
    <a href="{{ route('public.procurement.index') }}" class="{{ request()->routeIs('public.procurement.*') ? 'active' : '' }}" onclick="closeMobileNav()">Procurement opportunities</a>
    <a href="{{ route('discussion.current') }}" class="{{ request()->routeIs('discussion.*') ? 'active' : '' }}" onclick="closeMobileNav()">Community discussions</a>
    <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'active' : '' }}" onclick="closeMobileNav()">News &amp; updates</a>
    <a href="{{ route('public.grievances.create') }}" class="{{ request()->routeIs('public.grievances.*') ? 'active' : '' }}" @if(request()->routeIs('public.grievances.*')) aria-current="page" @endif onclick="closeMobileNav()">Feedback</a>
    <div class="mobile-nav-actions">
        <a href="{{ route('login') }}" class="pmis-button pmis-button--light">Sign in to PMIS</a>
        <x-language-selector style="landing-mobile" />
    </div>
</nav>

<header class="pmis-header" role="banner">
    <div class="pmis-container pmis-header__inner">
        <a href="{{ route('landing.index') }}" class="pmis-brand" aria-label="3PAP PMIS home">
            <img src="{{ asset('assets/images/3pap-logo-dark.png') }}" alt="3PAP - Portfolio, Program, Project">
            <span><strong>PMIS</strong><small>Portfolio Management Information System</small></span>
        </a>
        <nav class="pmis-nav" aria-label="Main navigation">
            <a href="{{ $landingSectionUrl('platform') }}">Platform</a>
            <a href="{{ $landingSectionUrl('capabilities') }}">Capabilities</a>
            <a href="{{ $landingSectionUrl('lifecycle') }}">How it works</a>
            <div class="pmis-nav__group {{ $exploreIsActive ? 'is-active' : '' }}">
                <button type="button" class="{{ $exploreIsActive ? 'active' : '' }}" aria-expanded="false">Explore <span aria-hidden="true">&#8964;</span></button>
                <div class="pmis-nav__menu">
                    <a href="{{ route('impact.map') }}" class="{{ request()->routeIs('impact.*') ? 'active' : '' }}" @if(request()->routeIs('impact.*')) aria-current="page" @endif>Portfolio insights</a>
                    <a href="{{ route('world.indicators.performance') }}" class="{{ request()->routeIs('world.indicators.performance') ? 'active' : '' }}" @if(request()->routeIs('world.indicators.performance')) aria-current="page" @endif>Performance analytics</a>
                    <a href="{{ route('public.procurement.index') }}" class="{{ request()->routeIs('public.procurement.*') ? 'active' : '' }}" @if(request()->routeIs('public.procurement.*')) aria-current="page" @endif>Procurement opportunities</a>
                    <a href="{{ route('discussion.current') }}" class="{{ request()->routeIs('discussion.*') ? 'active' : '' }}" @if(request()->routeIs('discussion.*')) aria-current="page" @endif>Community discussions</a>
                    <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'active' : '' }}" @if(request()->routeIs('news.*')) aria-current="page" @endif>News &amp; updates</a>
                </div>
            </div>
        </nav>
        <div class="pmis-header__actions">
            <x-language-selector style="landing" />
            <a href="{{ route('login') }}" class="pmis-button pmis-button--primary">Sign in</a>
        </div>
        <button class="pmis-menu-button" id="hamburgerBtn" type="button" onclick="openMobileNav()" aria-label="Open menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
