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
    $skipLabel = $skipLabel ?? __('public.skip_to_main');
@endphp

<a class="pmis-skip-link" href="{{ $skipTarget }}">{{ $skipLabel }}</a>

<div class="pmis-utility">
    <div class="pmis-container pmis-utility__inner">
        <span>{{ __('public.utility_tagline') }}</span>
        <a href="mailto:info@3pap.com">info@3pap.com</a>
    </div>
</div>

<div class="mobile-nav-overlay" id="navOverlay" onclick="closeMobileNav()"></div>
<nav class="mobile-nav pmis-mobile-nav" id="mobileNav" aria-label="{{ __('public.navigation.mobile_label') }}" aria-hidden="true">
    <div class="mobile-nav-header">
        <img src="{{ asset('assets/images/3pap-logo-white.png') }}" alt="3PAP">
        <button class="mobile-nav-close" type="button" onclick="closeMobileNav()" aria-label="{{ __('public.navigation.close_menu') }}">&times;</button>
    </div>
    <a href="{{ $landingSectionUrl('platform') }}" onclick="closeMobileNav()">{{ __('public.navigation.platform') }}</a>
    <a href="{{ $landingSectionUrl('capabilities') }}" onclick="closeMobileNav()">{{ __('public.navigation.capabilities') }}</a>
    <a href="{{ $landingSectionUrl('lifecycle') }}" onclick="closeMobileNav()">{{ __('public.navigation.lifecycle') }}</a>
    <a href="{{ route('impact.map') }}" class="{{ request()->routeIs('impact.*') ? 'active' : '' }}" onclick="closeMobileNav()">{{ __('public.navigation.portfolio_insights') }}</a>
    <a href="{{ route('world.indicators.performance') }}" class="{{ request()->routeIs('world.indicators.performance') ? 'active' : '' }}" onclick="closeMobileNav()">{{ __('public.navigation.performance_analytics') }}</a>
    <a href="{{ route('public.procurement.index') }}" class="{{ request()->routeIs('public.procurement.*') ? 'active' : '' }}" onclick="closeMobileNav()">{{ __('public.navigation.procurement') }}</a>
    <a href="{{ route('discussion.current') }}" class="{{ request()->routeIs('discussion.*') ? 'active' : '' }}" onclick="closeMobileNav()">{{ __('public.navigation.discussions') }}</a>
    <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'active' : '' }}" onclick="closeMobileNav()">{{ __('public.navigation.news') }}</a>
    <a href="{{ route('public.grievances.create') }}" class="{{ request()->routeIs('public.grievances.*') ? 'active' : '' }}" @if(request()->routeIs('public.grievances.*')) aria-current="page" @endif onclick="closeMobileNav()">{{ __('public.navigation.feedback') }}</a>
    <div class="mobile-nav-actions">
        <a href="{{ route('login') }}" class="pmis-button pmis-button--light">{{ __('public.navigation.sign_in_pmis') }}</a>
        <x-language-selector style="landing-mobile" />
    </div>
</nav>

<header class="pmis-header" role="banner">
    <div class="pmis-container pmis-header__inner">
        <a href="{{ route('landing.index') }}" class="pmis-brand" aria-label="{{ __('public.brand_home_label') }}">
            <img src="{{ asset('assets/images/3pap-logo-dark.png') }}" alt="{{ __('public.brand_alt') }}">
            <span><strong>PMIS</strong><small>{{ __('public.brand_subtitle') }}</small></span>
        </a>
        <nav class="pmis-nav" aria-label="{{ __('public.navigation.main_label') }}">
            <a href="{{ $landingSectionUrl('platform') }}">{{ __('public.navigation.platform') }}</a>
            <a href="{{ $landingSectionUrl('capabilities') }}">{{ __('public.navigation.capabilities') }}</a>
            <a href="{{ $landingSectionUrl('lifecycle') }}">{{ __('public.navigation.lifecycle') }}</a>
            <div class="pmis-nav__group {{ $exploreIsActive ? 'is-active' : '' }}">
                <button type="button" class="{{ $exploreIsActive ? 'active' : '' }}" aria-expanded="false">{{ __('public.navigation.explore') }} <span aria-hidden="true">&#8964;</span></button>
                <div class="pmis-nav__menu">
                    <a href="{{ route('impact.map') }}" class="{{ request()->routeIs('impact.*') ? 'active' : '' }}" @if(request()->routeIs('impact.*')) aria-current="page" @endif>{{ __('public.navigation.portfolio_insights') }}</a>
                    <a href="{{ route('world.indicators.performance') }}" class="{{ request()->routeIs('world.indicators.performance') ? 'active' : '' }}" @if(request()->routeIs('world.indicators.performance')) aria-current="page" @endif>{{ __('public.navigation.performance_analytics') }}</a>
                    <a href="{{ route('public.procurement.index') }}" class="{{ request()->routeIs('public.procurement.*') ? 'active' : '' }}" @if(request()->routeIs('public.procurement.*')) aria-current="page" @endif>{{ __('public.navigation.procurement') }}</a>
                    <a href="{{ route('discussion.current') }}" class="{{ request()->routeIs('discussion.*') ? 'active' : '' }}" @if(request()->routeIs('discussion.*')) aria-current="page" @endif>{{ __('public.navigation.discussions') }}</a>
                    <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'active' : '' }}" @if(request()->routeIs('news.*')) aria-current="page" @endif>{{ __('public.navigation.news') }}</a>
                </div>
            </div>
        </nav>
        <div class="pmis-header__actions">
            <x-language-selector style="landing" />
            <a href="{{ route('login') }}" class="pmis-button pmis-button--primary">{{ __('public.navigation.sign_in') }}</a>
        </div>
        <button class="pmis-menu-button" id="hamburgerBtn" type="button" onclick="openMobileNav()" aria-label="{{ __('public.navigation.open_menu') }}" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
