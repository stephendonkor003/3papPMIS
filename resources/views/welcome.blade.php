@php($documentDirection = config('localization.supported.'.app()->getLocale().'.direction', 'ltr'))
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $documentDirection }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('landing.home.meta.title') }}</title>
    <meta name="description" content="{{ __('landing.home.meta.description') }}">
    <meta name="keywords" content="{{ __('landing.home.meta.keywords') }}">
    <meta name="author" content="3PAP">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#06152F">
    <link rel="canonical" href="{{ route('landing.index') }}">
    <link rel="icon" href="{{ asset('assets/images/3pap-icon.svg') }}" type="image/svg+xml">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ __('landing.home.meta.title') }}">
    <meta property="og:description" content="{{ __('landing.home.meta.social_description') }}">
    <meta property="og:image" content="{{ asset('assets/images/3pap-og-image.svg') }}">
    <meta property="og:url" content="{{ route('landing.index') }}">
    <meta property="og:site_name" content="3PAP PMIS">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="3PAP PMIS">
    <meta name="twitter:description" content="{{ __('landing.home.meta.twitter_description') }}">
    <meta name="twitter:image" content="{{ asset('assets/images/3pap-og-image.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/3pap-public.css') }}">
    @if ($documentDirection === 'rtl')
        <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}">
    @endif
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "3PAP PMIS",
        "alternateName": "Portfolio Management Information System",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "url": "{{ route('landing.index') }}",
        "publisher": { "@@type": "Organization", "name": "3PAP", "url": "https://3pap.africa" },
        "description": @json(__('landing.home.meta.schema_description'))
    }
    </script>
</head>
<body class="public-mobile-nav pmis-home pmis-marketing-page" dir="{{ $documentDirection }}">
    @include('partials.public-marketing-header')
    <main id="main-content">
        <section class="pmis-hero" id="platform">
            <div class="pmis-hero__orb pmis-hero__orb--one" aria-hidden="true"></div>
            <div class="pmis-hero__orb pmis-hero__orb--two" aria-hidden="true"></div>
            <div class="pmis-container pmis-hero__grid">
                <div class="pmis-hero__copy">
                    <p class="pmis-eyebrow">{{ __('landing.home.hero.eyebrow') }}</p>
                    <h1>{{ __('landing.home.hero.title_prefix') }} <span>{{ __('landing.home.hero.title_emphasis') }}</span></h1>
                    <p class="pmis-hero__lead">{{ __('landing.home.hero.lead') }}</p>
                    <div class="pmis-hero__actions">
                        <a href="{{ route('login') }}" class="pmis-button pmis-button--primary pmis-button--large">{{ __('landing.home.hero.primary_cta') }} <span aria-hidden="true">&rarr;</span></a>
                        <a href="#capabilities" class="pmis-button pmis-button--secondary pmis-button--large">{{ __('landing.home.hero.secondary_cta') }}</a>
                    </div>
                    <ul class="pmis-proof-list" aria-label="{{ __('landing.home.hero.benefits_label') }}">
                        @foreach (trans('landing.home.hero.benefits') as $benefit)
                            <li>{{ $benefit }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="pmis-command" aria-label="{{ __('landing.home.command.aria_label') }}">
                    <div class="pmis-command__topbar">
                        <div><span class="pmis-command__label">{{ __('landing.home.command.centre') }}</span><strong>{{ __('landing.home.command.overview') }}</strong></div>
                        <span class="pmis-live"><i></i> {{ __('landing.home.command.live') }}</span>
                    </div>
                    <div class="pmis-command__health">
                        <div><span>{{ __('landing.home.command.health') }}</span><strong>{{ __('landing.home.command.on_track') }}</strong></div>
                        <div class="pmis-score"><span>92</span><small>%</small></div>
                    </div>
                    <div class="pmis-command__stats">
                        <div><span>{{ __('landing.home.command.active_programmes') }}</span><strong>24</strong><small>{{ __('landing.home.command.portfolio_count') }}</small></div>
                        <div><span>{{ __('landing.home.command.decision_queue') }}</span><strong>08</strong><small>{{ __('landing.home.command.priority_count') }}</small></div>
                        <div><span>{{ __('landing.home.command.evidence_coverage') }}</span><strong>94%</strong><small>{{ __('landing.home.command.current_cycle') }}</small></div>
                    </div>
                    <div class="pmis-command__chart">
                        <div class="pmis-chart__head"><span>{{ __('landing.home.command.delivery_performance') }}</span><small>{{ __('landing.home.command.period') }}</small></div>
                        <div class="pmis-chart__bars" aria-hidden="true">
                            <i style="--bar:42%"></i><i style="--bar:58%"></i><i style="--bar:51%"></i><i style="--bar:68%"></i><i style="--bar:64%"></i><i style="--bar:77%"></i><i style="--bar:72%"></i><i style="--bar:84%"></i><i style="--bar:82%"></i><i style="--bar:91%"></i><i style="--bar:87%"></i><i style="--bar:94%"></i>
                        </div>
                    </div>
                    <div class="pmis-command__action">
                        <span class="pmis-check" aria-hidden="true">&#10003;</span>
                        <div><strong>{{ __('landing.home.command.approval_completed') }}</strong><small>{{ __('landing.home.command.funding_release') }}</small></div>
                        <time>{{ __('landing.home.command.today') }}</time>
                    </div>
                </div>
            </div>
        </section>
        <section class="pmis-audience" aria-label="{{ __('landing.home.audience.aria_label') }}">
            <div class="pmis-container pmis-audience__inner">
                <span>{{ __('landing.home.audience.lead') }}</span>
                <ul>
                    @foreach (trans('landing.home.audience.items') as $audience)
                        <li>{{ $audience }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
        <section class="pmis-section pmis-principles">
            <div class="pmis-container">
                <div class="pmis-section__intro">
                    <div><p class="pmis-eyebrow">{{ __('landing.home.principles.eyebrow') }}</p><h2>{{ __('landing.home.principles.title') }}</h2></div>
                    <p>{{ __('landing.home.principles.intro') }}</p>
                </div>
                <div class="pmis-number-grid">
                    @foreach (trans('landing.home.principles.items') as $principle)
                        <article><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $principle['title'] }}</h3><p>{{ $principle['description'] }}</p></article>
                    @endforeach
                </div>
            </div>
        </section>
        <section class="pmis-section pmis-capabilities" id="capabilities">
            <div class="pmis-container">
                <div class="pmis-section__intro pmis-section__intro--light">
                    <div><p class="pmis-eyebrow">{{ __('landing.home.capabilities.eyebrow') }}</p><h2>{{ __('landing.home.capabilities.title') }}</h2></div>
                    <p>{{ __('landing.home.capabilities.intro') }}</p>
                </div>
                <div class="pmis-capability-grid">
                    @php($capabilityIcons = ['P', 'Pg', 'Pc', 'F', 'Pr', 'M&E'])
                    @foreach (trans('landing.home.capabilities.items') as $index => $capability)
                        <article><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div class="pmis-capability-icon">{{ $capabilityIcons[$index] }}</div><h3>{{ $capability['title'] }}</h3><p>{{ $capability['description'] }}</p></article>
                    @endforeach
                </div>
            </div>
        </section>
        <section class="pmis-section pmis-lifecycle" id="lifecycle">
            <div class="pmis-container">
                <div class="pmis-section__intro">
                    <div><p class="pmis-eyebrow">{{ __('landing.home.lifecycle.eyebrow') }}</p><h2>{{ __('landing.home.lifecycle.title') }}</h2></div>
                    <p>{{ __('landing.home.lifecycle.intro') }}</p>
                </div>
                <ol class="pmis-lifecycle__steps">
                    @foreach (trans('landing.home.lifecycle.items') as $step)
                        <li><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['description'] }}</p></li>
                    @endforeach
                </ol>
            </div>
        </section>
        <section class="pmis-section pmis-portals">
            <div class="pmis-container">
                <div class="pmis-section__intro">
                    <div><p class="pmis-eyebrow">{{ __('landing.home.portals.eyebrow') }}</p><h2>{{ __('landing.home.portals.title') }}</h2></div>
                    <p>{{ __('landing.home.portals.intro') }}</p>
                </div>
                <div class="pmis-portal-grid">
                    @php($portalRoutes = [route('impact.map'), route('public.procurement.index'), route('discussion.current'), route('public.grievances.create')])
                    @foreach (trans('landing.home.portals.items') as $index => $portal)
                        <a href="{{ $portalRoutes[$index] }}"><span>{{ $portal['label'] }}</span><strong>{{ $portal['action'] }}</strong><i>&rarr;</i></a>
                    @endforeach
                </div>
            </div>
        </section>
        <section class="pmis-cta">
            <div class="pmis-container pmis-cta__inner">
                <div><p class="pmis-eyebrow">{{ __('landing.home.cta.eyebrow') }}</p><h2>{{ __('landing.home.cta.title') }}</h2><p>{{ __('landing.home.cta.description') }}</p></div>
                <div class="pmis-cta__actions">
                    <a href="{{ route('login') }}" class="pmis-button pmis-button--light pmis-button--large">{{ __('landing.home.cta.sign_in') }}</a>
                    <a href="https://3pap.africa" target="_blank" rel="noopener noreferrer" class="pmis-button pmis-button--outline-light pmis-button--large">{{ __('landing.home.cta.visit') }}</a>
                </div>
            </div>
        </section>
    </main>
    @include('partials.public-marketing-footer')
    @include('partials.public-marketing-scripts')
</body>
</html>
