@php
    $isLandingPage = request()->routeIs('landing.index');
    $landingSectionUrl = static fn (string $section): string => $isLandingPage
        ? '#'.$section
        : route('landing.index').'#'.$section;
@endphp

<footer class="pmis-footer" id="contact" role="contentinfo">
    <div class="pmis-container pmis-footer__grid">
        <div class="pmis-footer__brand">
            <img src="{{ asset('assets/images/3pap-logo-white.png') }}" alt="3PAP">
            <strong>{{ __('public.brand_subtitle') }}</strong>
            <p>{{ __('public.footer.description') }}</p>
        </div>
        <div>
            <h2>{{ __('public.footer.platform') }}</h2>
            <a href="{{ $landingSectionUrl('capabilities') }}">{{ __('public.navigation.capabilities') }}</a>
            <a href="{{ $landingSectionUrl('lifecycle') }}">{{ __('public.footer.delivery_lifecycle') }}</a>
            <a href="{{ route('impact.map') }}">{{ __('public.navigation.portfolio_insights') }}</a>
            <a href="{{ route('world.indicators.performance') }}">{{ __('public.navigation.performance_analytics') }}</a>
        </div>
        <div>
            <h2>{{ __('public.footer.public_access') }}</h2>
            <a href="{{ route('public.procurement.index') }}">{{ __('public.footer.procurement') }}</a>
            <a href="{{ route('discussion.current') }}">{{ __('public.footer.discussions') }}</a>
            <a href="{{ route('news.index') }}">{{ __('public.footer.news') }}</a>
            <a href="{{ route('events') }}">{{ __('public.footer.events') }}</a>
            <a href="{{ route('gallery') }}">{{ __('public.footer.gallery') }}</a>
            <a href="{{ route('careers.index') }}">{{ __('public.footer.careers') }}</a>
        </div>
        <div>
            <h2>{{ __('public.footer.contact') }}</h2>
            <a href="mailto:info@3pap.africa">info@3pap.africa</a>
            <a href="https://3pap.africa" target="_blank" rel="noopener noreferrer">3pap.africa</a>
            <a href="{{ route('public.grievances.create') }}">{{ __('public.footer.feedback') }}</a>
        </div>
    </div>
    <div class="pmis-container pmis-footer__bottom">
        <span>{{ __('public.footer.rights_reserved', ['year' => date('Y')]) }}</span>
        <span>{{ __('public.footer.product_name') }}</span>
    </div>
</footer>
