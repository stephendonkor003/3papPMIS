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
            <strong>Portfolio Management Information System</strong>
            <p>One governed platform for portfolios, programmes, projects, funds, procurement, people, compliance, and performance.</p>
        </div>
        <div>
            <h2>Platform</h2>
            <a href="{{ $landingSectionUrl('capabilities') }}">Capabilities</a>
            <a href="{{ $landingSectionUrl('lifecycle') }}">Delivery lifecycle</a>
            <a href="{{ route('impact.map') }}">Portfolio insights</a>
            <a href="{{ route('world.indicators.performance') }}">Performance analytics</a>
        </div>
        <div>
            <h2>Public access</h2>
            <a href="{{ route('public.procurement.index') }}">Procurement</a>
            <a href="{{ route('discussion.current') }}">Community discussions</a>
            <a href="{{ route('news.index') }}">News &amp; updates</a>
            <a href="{{ route('events') }}">Events</a>
            <a href="{{ route('gallery') }}">Gallery</a>
            <a href="{{ route('careers.index') }}">Careers</a>
        </div>
        <div>
            <h2>Contact</h2>
            <a href="mailto:info@3pap.com">info@3pap.com</a>
            <a href="https://3pap.africa" target="_blank" rel="noopener noreferrer">3pap.africa</a>
            <a href="{{ route('public.grievances.create') }}">Feedback &amp; grievances</a>
        </div>
    </div>
    <div class="pmis-container pmis-footer__bottom">
        <span>&copy; {{ date('Y') }} 3PAP. All rights reserved.</span>
        <span>3PAP PMIS &middot; Portfolio Management Information System</span>
    </div>
</footer>
