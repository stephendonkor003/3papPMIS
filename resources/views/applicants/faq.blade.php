@extends('layouts.public')

@section('title', 'PMIS FAQs | 3PAP')
@section('description', 'Answers to common questions about the 3PAP Portfolio Management Information System, its capabilities, governance and implementation.')

@push('styles')
    <style>
        .pmis-faq {
            --pmis-navy-950: #06152f;
            --pmis-navy-900: #0a2450;
            --pmis-navy-800: #0a3479;
            --pmis-blue-700: #0756c7;
            --pmis-blue-600: #0a6ed1;
            --pmis-blue-500: #1687ea;
            --pmis-blue-100: #dceeff;
            --pmis-blue-50: #eff8ff;
            --pmis-cyan-400: #2fd0ed;
            --pmis-ink: #14213d;
            --pmis-text: #283b58;
            --pmis-muted: #60728b;
            --pmis-line: #dbe4ee;
            --pmis-surface-soft: #f4f7fa;
            background: #fff;
            color: var(--pmis-text);
            overflow: hidden;
        }

        .pmis-faq,
        .pmis-faq * { box-sizing: border-box; }
        .pmis-faq a { text-underline-offset: .2em; }
        .pmis-faq a:focus-visible,
        .pmis-faq summary:focus-visible { outline: 3px solid var(--pmis-cyan-400); outline-offset: 3px; }
        .pmis-faq__wrap { width: min(1080px, calc(100% - 2rem)); margin-inline: auto; }

        .pmis-faq__hero {
            position: relative;
            isolation: isolate;
            padding: clamp(4.5rem, 9vw, 7.5rem) 0 clamp(4rem, 8vw, 6.5rem);
            background: radial-gradient(circle at 84% 12%, rgba(47,208,237,.21), transparent 24rem),
                linear-gradient(135deg, var(--pmis-navy-950), var(--pmis-navy-900) 54%, var(--pmis-navy-800));
            color: #fff;
        }

        .pmis-faq__hero::after {
            position: absolute;
            right: -12vw;
            bottom: -18rem;
            z-index: -1;
            width: 38rem;
            height: 38rem;
            border: 5rem solid rgba(255,255,255,.035);
            border-radius: 50%;
            content: '';
        }

        .pmis-faq__hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(270px, .75fr);
            gap: clamp(2.25rem, 7vw, 6rem);
            align-items: center;
        }

        .pmis-faq__eyebrow,
        .pmis-faq__kicker {
            margin: 0 0 1rem;
            color: var(--pmis-blue-700);
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .15em;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .pmis-faq__hero .pmis-faq__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            color: #8ce7ff;
        }

        .pmis-faq__hero .pmis-faq__eyebrow::before {
            width: 2rem;
            height: 2px;
            content: '';
            background: var(--pmis-cyan-400);
        }

        .pmis-faq h1 {
            max-width: 740px;
            margin: 0;
            color: #fff;
            font-size: clamp(2.55rem, 6vw, 4.8rem);
            font-weight: 800;
            letter-spacing: -.055em;
            line-height: 1;
        }

        .pmis-faq__lead {
            max-width: 660px;
            margin: 1.45rem 0 0;
            color: #d8e4ef;
            font-size: clamp(1rem, 2vw, 1.18rem);
            line-height: 1.75;
        }

        .pmis-faq__snapshot {
            padding: 1.35rem;
            border: 1px solid rgba(255,255,255,.17);
            border-radius: 1.1rem;
            background: rgba(255,255,255,.09);
            box-shadow: 0 28px 65px rgba(0,0,0,.18);
            backdrop-filter: blur(12px);
        }

        .pmis-faq__snapshot-label {
            margin: 0 0 1rem;
            color: #b9c8d7;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .pmis-faq__snapshot h2 { margin: 0; color: #fff; font-size: 1.25rem; line-height: 1.35; }
        .pmis-faq__snapshot-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; margin-top: 1.2rem; }

        .pmis-faq__snapshot-item {
            min-height: 84px;
            padding: .85rem;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: .7rem;
            background: rgba(6,21,47,.24);
        }

        .pmis-faq__snapshot-item span { display: block; color: #8ce7ff; font-size: .67rem; font-weight: 800; letter-spacing: .1em; }
        .pmis-faq__snapshot-item strong { display: block; margin-top: .45rem; color: #fff; font-size: .84rem; line-height: 1.35; }
        .pmis-faq__section { padding: clamp(4rem, 8vw, 6.5rem) 0; }
        .pmis-faq__section--soft { border-block: 1px solid var(--pmis-line); background: var(--pmis-surface-soft); }
        .pmis-faq__intro { max-width: 720px; margin-bottom: 2.1rem; }
        .pmis-faq__intro h2,
        .pmis-faq__contact h2 { margin: 0; color: var(--pmis-ink); font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 800; letter-spacing: -.04em; line-height: 1.08; }
        .pmis-faq__intro p { margin: .85rem 0 0; color: var(--pmis-muted); line-height: 1.7; }
        .pmis-faq__list { display: grid; gap: .75rem; }

        .pmis-faq__item {
            border: 1px solid var(--pmis-line);
            border-radius: .9rem;
            background: #fff;
            box-shadow: 0 8px 25px rgba(6,21,47,.035);
            overflow: hidden;
        }

        .pmis-faq__item[open] { border-color: #c4d7ec; box-shadow: 0 16px 38px rgba(6,21,47,.07); }
        .pmis-faq__item summary { display: grid; grid-template-columns: auto 1fr auto; gap: 1rem; align-items: center; padding: 1.15rem 1.25rem; color: var(--pmis-ink); cursor: pointer; font-weight: 750; line-height: 1.45; list-style: none; }
        .pmis-faq__item summary::-webkit-details-marker { display: none; }

        .pmis-faq__number {
            display: grid;
            width: 2.15rem;
            height: 2.15rem;
            place-items: center;
            border-radius: .6rem;
            background: var(--pmis-blue-50);
            color: var(--pmis-blue-700);
            font-size: .72rem;
            font-weight: 800;
        }

        .pmis-faq__toggle {
            position: relative;
            width: 1.3rem;
            height: 1.3rem;
            color: var(--pmis-blue-600);
        }

        .pmis-faq__toggle::before,
        .pmis-faq__toggle::after { position: absolute; top: 50%; left: 50%; width: .85rem; height: 2px; border-radius: 2px; background: currentColor; content: ''; transform: translate(-50%, -50%); }
        .pmis-faq__toggle::after { transform: translate(-50%, -50%) rotate(90deg); transition: transform .2s ease; }
        .pmis-faq__item[open] .pmis-faq__toggle::after { transform: translate(-50%, -50%) rotate(0deg); }
        .pmis-faq__answer { padding: 0 1.25rem 1.3rem 4.4rem; color: var(--pmis-muted); font-size: .94rem; line-height: 1.75; }
        .pmis-faq__answer p { margin: 0; }
        .pmis-faq__answer p + p { margin-top: .75rem; }
        .pmis-faq__answer a { color: var(--pmis-blue-700); font-weight: 750; }

        .pmis-faq__principles {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .pmis-faq__principle {
            padding: 1.5rem;
            border: 1px solid var(--pmis-line);
            border-radius: 1rem;
            background: #fff;
        }

        .pmis-faq__principle svg { width: 1.6rem; height: 1.6rem; color: var(--pmis-blue-600); }
        .pmis-faq__principle h3 { margin: 1.25rem 0 0; color: var(--pmis-ink); font-size: 1.05rem; }
        .pmis-faq__principle p { margin: .65rem 0 0; color: var(--pmis-muted); font-size: .9rem; line-height: 1.65; }

        .pmis-faq__contact {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 2rem;
            align-items: center;
            padding: clamp(2rem, 5vw, 3.5rem);
            border: 1px solid var(--pmis-line);
            border-radius: 1.2rem;
            background: linear-gradient(125deg, var(--pmis-blue-50), #fff 72%);
        }

        .pmis-faq__contact p { max-width: 650px; margin: .9rem 0 0; color: var(--pmis-muted); line-height: 1.7; }
        .pmis-faq__actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: .7rem; }

        .pmis-faq__button {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .75rem 1.1rem;
            border: 1px solid var(--pmis-line);
            border-radius: .7rem;
            background: #fff;
            color: var(--pmis-blue-700);
            font-size: .9rem;
            font-weight: 800;
            text-decoration: none;
            transition: transform .2s ease, border-color .2s ease, background-color .2s ease;
        }

        .pmis-faq__button:hover { border-color: var(--pmis-blue-600); transform: translateY(-2px); color: var(--pmis-blue-700); text-decoration: none; }
        .pmis-faq__button--primary { border-color: var(--pmis-blue-600); background: var(--pmis-blue-600); color: #fff; }
        .pmis-faq__button--primary:hover { background: var(--pmis-blue-500); color: #fff; }

        @media (max-width: 880px) {
            .pmis-faq__hero-grid, .pmis-faq__contact { grid-template-columns: 1fr; }
            .pmis-faq__snapshot { max-width: 620px; }
            .pmis-faq__principles { grid-template-columns: 1fr; }
            .pmis-faq__actions { justify-content: flex-start; }
        }

        @media (max-width: 620px) {
            .pmis-faq__wrap { width: min(100% - 1.25rem, 1080px); }
            .pmis-faq__hero { padding-top: 3.75rem; }
            .pmis-faq__snapshot-grid { grid-template-columns: 1fr; }
            .pmis-faq__item summary { grid-template-columns: auto 1fr auto; gap: .7rem; padding-inline: .85rem; }
            .pmis-faq__answer { padding: 0 .9rem 1.15rem; }
            .pmis-faq__actions, .pmis-faq__button { width: 100%; }
        }

        @media (prefers-reduced-motion: reduce) {
            .pmis-faq__toggle::after, .pmis-faq__button { transition: none; }
        }
    </style>
@endpush

@section('content')
    <div class="pmis-faq">
        <section class="pmis-faq__hero" aria-labelledby="faq-title">
            <div class="pmis-faq__wrap pmis-faq__hero-grid">
                <div>
                    <p class="pmis-faq__eyebrow">3PAP PMIS platform guide</p>
                    <h1 id="faq-title">Frequently asked questions</h1>
                    <p class="pmis-faq__lead">
                        Clear answers about the Portfolio Management Information System, the work it connects
                        and how it supports governed, evidence-led delivery.
                    </p>
                </div>

                <aside class="pmis-faq__snapshot" aria-label="PMIS at a glance">
                    <p class="pmis-faq__snapshot-label">PMIS at a glance</p>
                    <h2>Enterprise execution, connected</h2>
                    <div class="pmis-faq__snapshot-grid">
                        <div class="pmis-faq__snapshot-item"><span>01</span><strong>Strategy &amp; portfolios</strong></div>
                        <div class="pmis-faq__snapshot-item"><span>02</span><strong>Programmes &amp; projects</strong></div>
                        <div class="pmis-faq__snapshot-item"><span>03</span><strong>Funds &amp; procurement</strong></div>
                        <div class="pmis-faq__snapshot-item"><span>04</span><strong>Evidence &amp; performance</strong></div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="pmis-faq__section" aria-labelledby="faq-list-title">
            <div class="pmis-faq__wrap">
                <div class="pmis-faq__intro">
                    <p class="pmis-faq__kicker">The essentials</p>
                    <h2 id="faq-list-title">Understanding PMIS</h2>
                    <p>Select a question to learn how the platform brings oversight, delivery and accountability into one governed operating environment.</p>
                </div>

                <div class="pmis-faq__list">
                    <details class="pmis-faq__item" open>
                        <summary>
                            <span class="pmis-faq__number" aria-hidden="true">01</span>
                            <span>What is 3PAP PMIS?</span>
                            <span class="pmis-faq__toggle" aria-hidden="true"></span>
                        </summary>
                        <div class="pmis-faq__answer">
                            <p>PMIS is the 3PAP-powered Portfolio Management Information System presented on this site. It connects portfolios, programmes, project cycles, funds, procurement, people, compliance and performance so leadership and delivery teams can work from one governed source of operational information.</p>
                        </div>
                    </details>

                    <details class="pmis-faq__item">
                        <summary>
                            <span class="pmis-faq__number" aria-hidden="true">02</span>
                            <span>Which organisations and teams is it designed for?</span>
                            <span class="pmis-faq__toggle" aria-hidden="true"></span>
                        </summary>
                        <div class="pmis-faq__answer">
                            <p>PMIS supports public institutions, development organisations, donor-funded programmes, enterprise portfolio offices, finance and governance teams, and other organisations responsible for complex delivery and accountability. Workspaces and controls can be configured around each institution's operating model.</p>
                        </div>
                    </details>

                    <details class="pmis-faq__item">
                        <summary>
                            <span class="pmis-faq__number" aria-hidden="true">03</span>
                            <span>What parts of the delivery lifecycle does PMIS cover?</span>
                            <span class="pmis-faq__toggle" aria-hidden="true"></span>
                        </summary>
                        <div class="pmis-faq__answer">
                            <p>PMIS maintains a continuous line from strategic priority to verified outcome: prioritise demand, plan funding and controls, execute work, assure performance and risk, then evaluate results and apply learning. Owners, approvals, evidence and reporting history remain connected as work moves between stages.</p>
                        </div>
                    </details>

                    <details class="pmis-faq__item">
                        <summary>
                            <span class="pmis-faq__number" aria-hidden="true">04</span>
                            <span>How does PMIS strengthen governance and accountability?</span>
                            <span class="pmis-faq__toggle" aria-hidden="true"></span>
                        </summary>
                        <div class="pmis-faq__answer">
                            <p>Institutions can embed roles, approvals, stage gates, thresholds, evidence requirements, risk controls and escalation paths in the work itself. Role-based access and traceable decisions help every action retain a clear owner and evidence trail.</p>
                        </div>
                    </details>

                    <details class="pmis-faq__item">
                        <summary>
                            <span class="pmis-faq__number" aria-hidden="true">05</span>
                            <span>What reporting does the platform support?</span>
                            <span class="pmis-faq__toggle" aria-hidden="true"></span>
                        </summary>
                        <div class="pmis-faq__answer">
                            <p>PMIS turns current operational records into portfolio, programme, financial, resource, risk, outcome and executive views. Reporting is built from traceable data, reducing repeated manual collection and making it easier to move from an indicator to its supporting workflow and evidence.</p>
                        </div>
                    </details>

                    <details class="pmis-faq__item">
                        <summary>
                            <span class="pmis-faq__number" aria-hidden="true">06</span>
                            <span>Can PMIS integrate with existing enterprise systems?</span>
                            <span class="pmis-faq__toggle" aria-hidden="true"></span>
                        </summary>
                        <div class="pmis-faq__answer">
                            <p>Yes. PMIS can operate as a governed delivery layer across finance, human resources, identity, procurement, collaboration and reporting environments. Integration is planned around clear systems of record, data ownership, governed interfaces and role controls.</p>
                        </div>
                    </details>

                    <details class="pmis-faq__item">
                        <summary>
                            <span class="pmis-faq__number" aria-hidden="true">07</span>
                            <span>How are access, implementation and support handled?</span>
                            <span class="pmis-faq__toggle" aria-hidden="true"></span>
                        </summary>
                        <div class="pmis-faq__answer">
                            <p>Access is role-based and managed within the institution's approved governance model. Implementation typically covers configuration, data and integration planning, workflow setup, user enablement and adoption support. Existing users should contact their PMIS administrator for account access; organisations considering PMIS can contact the 3PAP team.</p>
                        </div>
                    </details>

                    <details class="pmis-faq__item">
                        <summary>
                            <span class="pmis-faq__number" aria-hidden="true">08</span>
                            <span>How can we request a demonstration or find public opportunities?</span>
                            <span class="pmis-faq__toggle" aria-hidden="true"></span>
                        </summary>
                        <div class="pmis-faq__answer">
                            <p>Email <a href="mailto:info@3pap.com">info@3pap.com</a> with your organisation, priority use case and preferred timeframe to request a relevant product session. Public opportunities are listed on the <a href="{{ route('applicants.create') }}">Opportunities page</a>; there are currently no open calls or online submissions.</p>
                        </div>
                    </details>
                </div>
            </div>
        </section>

        <section class="pmis-faq__section pmis-faq__section--soft" aria-labelledby="principles-title">
            <div class="pmis-faq__wrap">
                <div class="pmis-faq__intro">
                    <p class="pmis-faq__kicker">Platform principles</p>
                    <h2 id="principles-title">Built for accountable delivery</h2>
                </div>
                <div class="pmis-faq__principles">
                    <article class="pmis-faq__principle">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="m8.5 12 2.2 2.2 4.8-5"></path></svg>
                        <h3>Govern work by design</h3>
                        <p>Make responsibilities, approvals, controls and escalation part of day-to-day execution.</p>
                    </article>
                    <article class="pmis-faq__principle">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"></path></svg>
                        <h3>See the whole enterprise</h3>
                        <p>Move beyond isolated updates to connected portfolio, financial, resource and outcome visibility.</p>
                    </article>
                    <article class="pmis-faq__principle">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4zM8 15l2.5-3 2 2 3.5-5"></path></svg>
                        <h3>Report with confidence</h3>
                        <p>Build management insight from current, traceable operational data and supporting evidence.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="pmis-faq__section" aria-labelledby="faq-contact-title">
            <div class="pmis-faq__wrap">
                <div class="pmis-faq__contact">
                    <div>
                        <p class="pmis-faq__kicker">Need a more specific answer?</p>
                        <h2 id="faq-contact-title">Start a conversation with 3PAP</h2>
                        <p>Share your organisation, delivery context and priority use case so the team can respond with relevant guidance.</p>
                    </div>
                    <div class="pmis-faq__actions">
                        <a class="pmis-faq__button" href="{{ route('events') }}">Events &amp; webinars</a>
                        <a class="pmis-faq__button pmis-faq__button--primary" href="mailto:info@3pap.com?subject=3PAP%20PMIS%20enquiry">
                            Contact 3PAP <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
