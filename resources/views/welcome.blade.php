<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>3PAP PMIS | Portfolio Management Information System</title>
    <meta name="description" content="3PAP PMIS connects portfolios, programmes, projects, funds, procurement, people, and performance in one governed source of operational information.">
    <meta name="keywords" content="3PAP, PMIS, portfolio management information system, programme management, project cycle management, fund management, procurement, performance management">
    <meta name="author" content="3PAP">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#06152F">
    <link rel="canonical" href="{{ route('landing.index') }}">
    <link rel="icon" href="{{ asset('assets/images/3pap-icon.svg') }}" type="image/svg+xml">
    <meta property="og:type" content="website">
    <meta property="og:title" content="3PAP PMIS | Portfolio Management Information System">
    <meta property="og:description" content="Turn institutional strategy and resources into visible, governed, measurable delivery.">
    <meta property="og:image" content="{{ asset('assets/images/3pap-og-image.svg') }}">
    <meta property="og:url" content="{{ route('landing.index') }}">
    <meta property="og:site_name" content="3PAP PMIS">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="3PAP PMIS">
    <meta name="twitter:description" content="Portfolio management information for accountable institutional delivery.">
    <meta name="twitter:image" content="{{ asset('assets/images/3pap-og-image.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
    @if (app()->getLocale() === 'ar')
        <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/3pap-public.css') }}">
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
        "description": "A governed platform for portfolio, programme, project cycle, fund, procurement, people, compliance, and performance management."
    }
    </script>
</head>
<body class="public-mobile-nav pmis-home pmis-marketing-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    @include('partials.public-marketing-header')
    <main id="main-content">
        <section class="pmis-hero" id="platform">
            <div class="pmis-hero__orb pmis-hero__orb--one" aria-hidden="true"></div>
            <div class="pmis-hero__orb pmis-hero__orb--two" aria-hidden="true"></div>
            <div class="pmis-container pmis-hero__grid">
                <div class="pmis-hero__copy">
                    <p class="pmis-eyebrow">Enterprise execution, connected</p>
                    <h1>Portfolio Management <span>Information System.</span></h1>
                    <p class="pmis-hero__lead">3PAP PMIS connects strategy, portfolios, programmes, projects, funds, procurement, people, and performance&mdash;giving every decision a clear owner, workflow, and evidence trail.</p>
                    <div class="pmis-hero__actions">
                        <a href="{{ route('login') }}" class="pmis-button pmis-button--primary pmis-button--large">Access PMIS <span aria-hidden="true">&rarr;</span></a>
                        <a href="#capabilities" class="pmis-button pmis-button--secondary pmis-button--large">Explore the platform</a>
                    </div>
                    <ul class="pmis-proof-list" aria-label="Platform benefits">
                        <li>Configurable governance</li><li>Enterprise integration</li><li>Decision-ready reporting</li>
                    </ul>
                </div>
                <div class="pmis-command" aria-label="Portfolio command centre preview">
                    <div class="pmis-command__topbar">
                        <div><span class="pmis-command__label">Portfolio command centre</span><strong>Executive overview</strong></div>
                        <span class="pmis-live"><i></i> Live view</span>
                    </div>
                    <div class="pmis-command__health">
                        <div><span>Portfolio health</span><strong>On track</strong></div>
                        <div class="pmis-score"><span>92</span><small>%</small></div>
                    </div>
                    <div class="pmis-command__stats">
                        <div><span>Active programmes</span><strong>24</strong><small>6 portfolios</small></div>
                        <div><span>Decision queue</span><strong>08</strong><small>3 priority</small></div>
                        <div><span>Evidence coverage</span><strong>94%</strong><small>Current cycle</small></div>
                    </div>
                    <div class="pmis-command__chart">
                        <div class="pmis-chart__head"><span>Delivery performance</span><small>12 months</small></div>
                        <div class="pmis-chart__bars" aria-hidden="true">
                            <i style="--bar:42%"></i><i style="--bar:58%"></i><i style="--bar:51%"></i><i style="--bar:68%"></i><i style="--bar:64%"></i><i style="--bar:77%"></i><i style="--bar:72%"></i><i style="--bar:84%"></i><i style="--bar:82%"></i><i style="--bar:91%"></i><i style="--bar:87%"></i><i style="--bar:94%"></i>
                        </div>
                    </div>
                    <div class="pmis-command__action">
                        <span class="pmis-check" aria-hidden="true">&#10003;</span>
                        <div><strong>Approval completed</strong><small>Funding release &middot; Phase 2</small></div>
                        <time>Today</time>
                    </div>
                </div>
            </div>
        </section>
        <section class="pmis-audience" aria-label="Who PMIS is for">
            <div class="pmis-container pmis-audience__inner">
                <span>Built for complex, accountable delivery</span>
                <ul><li>Public institutions</li><li>Development organisations</li><li>Enterprise PMOs</li><li>Finance &amp; delivery teams</li></ul>
            </div>
        </section>
        <section class="pmis-section pmis-principles">
            <div class="pmis-container">
                <div class="pmis-section__intro">
                    <div><p class="pmis-eyebrow">A connected operating environment</p><h2>Replace fragmented oversight with one governed source of truth.</h2></div>
                    <p>Keep strategy, funding, responsibilities, workflows, evidence, performance, and decisions connected without losing the controls each team needs.</p>
                </div>
                <div class="pmis-number-grid">
                    <article><span>01</span><h3>See the whole enterprise</h3><p>Move from isolated updates to current portfolio, programme, financial, resource, and outcome visibility.</p></article>
                    <article><span>02</span><h3>Govern work by design</h3><p>Embed roles, approvals, stage gates, thresholds, evidence, risk, and escalation in the work itself.</p></article>
                    <article><span>03</span><h3>Report with confidence</h3><p>Build management and executive insight from traceable operational data instead of repeated manual collection.</p></article>
                </div>
            </div>
        </section>
        <section class="pmis-section pmis-capabilities" id="capabilities">
            <div class="pmis-container">
                <div class="pmis-section__intro pmis-section__intro--light">
                    <div><p class="pmis-eyebrow">One platform, connected capabilities</p><h2>Everything required to move from intent to accountable results.</h2></div>
                    <p>PMIS gives every team a focused workspace while maintaining a continuous institutional record.</p>
                </div>
                <div class="pmis-capability-grid">
                    <article><span>01</span><div class="pmis-capability-icon">P</div><h3>Portfolio management</h3><p>Align investments, priorities, risks, and outcomes across the complete enterprise portfolio.</p></article>
                    <article><span>02</span><div class="pmis-capability-icon">Pg</div><h3>Programme management</h3><p>Coordinate related projects, dependencies, benefits, milestones, and executive reporting.</p></article>
                    <article><span>03</span><div class="pmis-capability-icon">Pc</div><h3>Project cycle management</h3><p>Standardise stage gates, approvals, delivery evidence, issues, and closure from one workspace.</p></article>
                    <article><span>04</span><div class="pmis-capability-icon">F</div><h3>Fund management</h3><p>Connect budgets, allocations, commitments, disbursements, and partner reporting.</p></article>
                    <article><span>05</span><div class="pmis-capability-icon">Pr</div><h3>Procurement management</h3><p>Control requisitions, sourcing, evaluations, approvals, contracts, and supplier performance.</p></article>
                    <article><span>06</span><div class="pmis-capability-icon">M&amp;E</div><h3>Performance &amp; assurance</h3><p>Track results, risks, compliance, evidence, learning, and accountability throughout delivery.</p></article>
                </div>
            </div>
        </section>
        <section class="pmis-section pmis-lifecycle" id="lifecycle">
            <div class="pmis-container">
                <div class="pmis-section__intro">
                    <div><p class="pmis-eyebrow">Govern the complete lifecycle</p><h2>A continuous line from strategic priority to verified outcome.</h2></div>
                    <p>Every handoff carries its owners, funding, approvals, risks, evidence, and reporting history forward.</p>
                </div>
                <ol class="pmis-lifecycle__steps">
                    <li><span>01</span><h3>Prioritise</h3><p>Assess demand and align investment to strategy.</p></li>
                    <li><span>02</span><h3>Plan</h3><p>Structure funding, outcomes, resources, and controls.</p></li>
                    <li><span>03</span><h3>Execute</h3><p>Coordinate work, procurement, partners, and decisions.</p></li>
                    <li><span>04</span><h3>Assure</h3><p>Monitor performance, risk, compliance, and evidence.</p></li>
                    <li><span>05</span><h3>Learn</h3><p>Evaluate outcomes and improve future investment.</p></li>
                </ol>
            </div>
        </section>
        <section class="pmis-section pmis-portals">
            <div class="pmis-container">
                <div class="pmis-section__intro">
                    <div><p class="pmis-eyebrow">Open information and participation</p><h2>Connect delivery teams, partners, and the public.</h2></div>
                    <p>Purpose-built public entry points make institutional information easier to discover and act on.</p>
                </div>
                <div class="pmis-portal-grid">
                    <a href="{{ route('impact.map') }}"><span>Portfolio insights</span><strong>Explore delivery and funding data</strong><i>&rarr;</i></a>
                    <a href="{{ route('public.procurement.index') }}"><span>Procurement</span><strong>View published opportunities</strong><i>&rarr;</i></a>
                    <a href="{{ route('discussion.current') }}"><span>Engagement</span><strong>Join current discussions</strong><i>&rarr;</i></a>
                    <a href="{{ route('public.grievances.create') }}"><span>Accountability</span><strong>Submit feedback or a grievance</strong><i>&rarr;</i></a>
                </div>
            </div>
        </section>
        <section class="pmis-cta">
            <div class="pmis-container pmis-cta__inner">
                <div><p class="pmis-eyebrow">Ready for governed execution?</p><h2>Turn institutional strategy into visible delivery.</h2><p>Access your secure PMIS workspace or learn more about the enterprise 3PAP platform.</p></div>
                <div class="pmis-cta__actions">
                    <a href="{{ route('login') }}" class="pmis-button pmis-button--light pmis-button--large">Sign in to PMIS</a>
                    <a href="https://3pap.africa" target="_blank" rel="noopener noreferrer" class="pmis-button pmis-button--outline-light pmis-button--large">Visit 3PAP Africa</a>
                </div>
            </div>
        </section>
    </main>
    @include('partials.public-marketing-footer')
    @include('partials.public-marketing-scripts')
</body>
</html>
