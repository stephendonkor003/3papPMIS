@extends('layouts.public')

@section('title', 'Opportunities | 3PAP PMIS')
@section('description', 'View current public opportunities connected to 3PAP PMIS. There are no open calls or online submissions at this time.')

@push('styles')
    <style>
        .pmis-opportunities {
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
            --pmis-warning: #c66a00;
            background: #fff;
            color: var(--pmis-text);
            overflow: hidden;
        }

        .pmis-opportunities,
        .pmis-opportunities * { box-sizing: border-box; }
        .pmis-opportunities a { text-underline-offset: .2em; }
        .pmis-opportunities a:focus-visible { outline: 3px solid var(--pmis-cyan-400); outline-offset: 3px; }
        .pmis-opportunities__wrap { width: min(1120px, calc(100% - 2rem)); margin-inline: auto; }

        .pmis-opportunities__hero {
            position: relative;
            isolation: isolate;
            padding: clamp(4.5rem, 9vw, 7.5rem) 0 clamp(4rem, 8vw, 6.5rem);
            background: radial-gradient(circle at 88% 16%, rgba(47,208,237,.2), transparent 24rem),
                linear-gradient(132deg, var(--pmis-navy-950), var(--pmis-navy-900) 58%, var(--pmis-navy-800));
            color: #fff;
        }

        .pmis-opportunities__hero::before {
            position: absolute;
            inset: 0;
            z-index: -1;
            content: '';
            background: linear-gradient(115deg, transparent 50%, rgba(255,255,255,.04) 50%);
        }

        .pmis-opportunities__hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(290px, .75fr);
            gap: clamp(2.25rem, 7vw, 6.5rem);
            align-items: center;
        }

        .pmis-opportunities__eyebrow,
        .pmis-opportunities__kicker {
            margin: 0 0 1rem;
            color: var(--pmis-blue-700);
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .15em;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .pmis-opportunities__hero .pmis-opportunities__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            color: #8ce7ff;
        }

        .pmis-opportunities__hero .pmis-opportunities__eyebrow::before {
            width: 2rem;
            height: 2px;
            content: '';
            background: var(--pmis-cyan-400);
        }

        .pmis-opportunities h1 {
            margin: 0;
            color: #fff;
            font-size: clamp(2.55rem, 6vw, 4.8rem);
            font-weight: 800;
            letter-spacing: -.055em;
            line-height: 1;
        }

        .pmis-opportunities__lead {
            max-width: 680px;
            margin: 1.45rem 0 0;
            color: #d8e4ef;
            font-size: clamp(1rem, 2vw, 1.18rem);
            line-height: 1.75;
        }

        .pmis-opportunities__status-card {
            padding: 1.45rem;
            border: 1px solid rgba(255,255,255,.17);
            border-radius: 1.1rem;
            background: rgba(255,255,255,.09);
            box-shadow: 0 28px 65px rgba(0,0,0,.18);
            backdrop-filter: blur(12px);
        }

        .pmis-opportunities__status-pill {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .45rem .68rem;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            color: #fff;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .pmis-opportunities__status-dot {
            width: .55rem;
            height: .55rem;
            border-radius: 50%;
            background: #f6b84b;
            box-shadow: 0 0 0 5px rgba(246,184,75,.14);
        }

        .pmis-opportunities__status-card h2 {
            margin: 1.3rem 0 .65rem;
            color: #fff;
            font-size: 1.4rem;
            line-height: 1.3;
        }

        .pmis-opportunities__status-card > p {
            margin: 0;
            color: #c4d7ec;
            font-size: .9rem;
            line-height: 1.65;
        }

        .pmis-opportunities__status-list {
            display: grid;
            gap: .7rem;
            margin: 1.3rem 0 0;
            padding: 1.15rem 0 0;
            border-top: 1px solid rgba(255,255,255,.13);
            list-style: none;
        }

        .pmis-opportunities__status-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            color: #bac8d6;
            font-size: .82rem;
        }

        .pmis-opportunities__status-list strong { color: #fff; font-size: .8rem; }
        .pmis-opportunities__section { padding: clamp(4rem, 8vw, 6.5rem) 0; }
        .pmis-opportunities__section--soft { border-block: 1px solid var(--pmis-line); background: var(--pmis-surface-soft); }

        .pmis-opportunities__alert {
            display: flex;
            gap: .9rem;
            align-items: start;
            margin-bottom: 1.5rem;
            padding: 1rem 1.1rem;
            border: 1px solid #f0d6a8;
            border-radius: .8rem;
            background: #fff9ee;
            color: #71420a;
        }

        .pmis-opportunities__alert svg { width: 1.3rem; height: 1.3rem; flex: 0 0 auto; margin-top: .1rem; }
        .pmis-opportunities__alert strong { display: block; }
        .pmis-opportunities__alert span { display: block; margin-top: .2rem; font-size: .86rem; line-height: 1.5; }

        .pmis-opportunities__empty {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1.35rem;
            align-items: center;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            border: 1px solid var(--pmis-line);
            border-radius: 1.1rem;
            background: #fff;
            box-shadow: 0 18px 48px rgba(6,21,47,.07);
        }

        .pmis-opportunities__empty-icon {
            display: grid;
            width: 4rem;
            height: 4rem;
            place-items: center;
            border-radius: 1rem;
            background: var(--pmis-blue-50);
            color: var(--pmis-blue-700);
        }

        .pmis-opportunities__empty-icon svg { width: 1.8rem; height: 1.8rem; }
        .pmis-opportunities__empty h2 { margin: 0; color: var(--pmis-ink); font-size: clamp(1.35rem, 3vw, 1.8rem); }
        .pmis-opportunities__empty p { max-width: 720px; margin: .55rem 0 0; color: var(--pmis-muted); line-height: 1.65; }

        .pmis-opportunities__heading { max-width: 720px; margin-bottom: 2rem; }
        .pmis-opportunities__heading h2,
        .pmis-opportunities__contact h2 { margin: 0; color: var(--pmis-ink); font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 800; letter-spacing: -.04em; line-height: 1.08; }
        .pmis-opportunities__heading p { margin: .85rem 0 0; color: var(--pmis-muted); line-height: 1.7; }
        .pmis-opportunities__steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }

        .pmis-opportunities__step {
            position: relative;
            padding: 1.6rem;
            border: 1px solid var(--pmis-line);
            border-radius: 1rem;
            background: #fff;
        }

        .pmis-opportunities__step-number {
            display: grid;
            width: 2.25rem;
            height: 2.25rem;
            place-items: center;
            margin-bottom: 2rem;
            border-radius: .65rem;
            background: var(--pmis-blue-50);
            color: var(--pmis-blue-700);
            font-size: .76rem;
            font-weight: 800;
        }

        .pmis-opportunities__step h3 { margin: 0; color: var(--pmis-ink); font-size: 1.05rem; }
        .pmis-opportunities__step p { margin: .65rem 0 0; color: var(--pmis-muted); font-size: .9rem; line-height: 1.65; }
        .pmis-opportunities__details { display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr); gap: clamp(2rem, 7vw, 6rem); align-items: start; }
        .pmis-opportunities__details h2 { margin: 0; color: var(--pmis-ink); font-size: clamp(1.75rem, 4vw, 2.7rem); font-weight: 800; letter-spacing: -.035em; line-height: 1.1; }
        .pmis-opportunities__details > div > p { margin: .9rem 0 0; color: var(--pmis-muted); line-height: 1.7; }
        .pmis-opportunities__checklist { display: grid; gap: .75rem; margin: 0; padding: 0; list-style: none; }

        .pmis-opportunities__checklist li {
            display: grid;
            grid-template-columns: 2rem 1fr;
            gap: .8rem;
            align-items: start;
            padding-bottom: .75rem;
            border-bottom: 1px solid var(--pmis-line);
            color: var(--pmis-text);
            line-height: 1.55;
        }

        .pmis-opportunities__checklist li:last-child { padding-bottom: 0; border-bottom: 0; }
        .pmis-opportunities__checklist span { display: grid; width: 2rem; height: 2rem; place-items: center; border-radius: .55rem; background: var(--pmis-blue-50); color: var(--pmis-blue-700); font-size: .72rem; font-weight: 800; }

        .pmis-opportunities__contact {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 2rem;
            align-items: center;
            padding: clamp(2rem, 5vw, 3.5rem);
            border: 1px solid var(--pmis-line);
            border-radius: 1.2rem;
            background: linear-gradient(125deg, var(--pmis-blue-50), #fff 72%);
        }

        .pmis-opportunities__contact p { max-width: 650px; margin: .9rem 0 0; color: var(--pmis-muted); line-height: 1.7; }
        .pmis-opportunities__actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: .7rem; }

        .pmis-opportunities__button {
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

        .pmis-opportunities__button:hover { border-color: var(--pmis-blue-600); transform: translateY(-2px); color: var(--pmis-blue-700); text-decoration: none; }
        .pmis-opportunities__button--primary { border-color: var(--pmis-blue-600); background: var(--pmis-blue-600); color: #fff; }
        .pmis-opportunities__button--primary:hover { background: var(--pmis-blue-500); color: #fff; }

        @media (max-width: 900px) {
            .pmis-opportunities__hero-grid, .pmis-opportunities__details, .pmis-opportunities__contact { grid-template-columns: 1fr; }
            .pmis-opportunities__status-card { max-width: 620px; }
            .pmis-opportunities__steps { grid-template-columns: 1fr; }
            .pmis-opportunities__step-number { margin-bottom: 1.2rem; }
            .pmis-opportunities__actions { justify-content: flex-start; }
        }

        @media (max-width: 620px) {
            .pmis-opportunities__wrap { width: min(100% - 1.25rem, 1120px); }
            .pmis-opportunities__hero { padding-top: 3.75rem; }
            .pmis-opportunities__empty { grid-template-columns: 1fr; }
            .pmis-opportunities__actions, .pmis-opportunities__button { width: 100%; }
        }

        @media (prefers-reduced-motion: reduce) {
            .pmis-opportunities__button { transition: none; }
        }
    </style>
@endpush

@section('content')
    @php($submissionsOpen = (bool) ($callForProposalSubmissionsOpen ?? false))

    <div class="pmis-opportunities">
        <section class="pmis-opportunities__hero" aria-labelledby="opportunities-title">
            <div class="pmis-opportunities__wrap pmis-opportunities__hero-grid">
                <div>
                    <p class="pmis-opportunities__eyebrow">3PAP PMIS public notices</p>
                    <h1 id="opportunities-title">Opportunities</h1>
                    <p class="pmis-opportunities__lead">
                        A clear public notice point for future PMIS-related opportunities. Scope, eligibility,
                        timelines and the official response process will be published together when a call is available.
                    </p>
                </div>

                <aside class="pmis-opportunities__status-card" aria-label="Opportunity status">
                    <span class="pmis-opportunities__status-pill">
                        <span class="pmis-opportunities__status-dot" aria-hidden="true"></span>
                        {{ $submissionsOpen ? 'Notice in preparation' : 'No open calls' }}
                    </span>
                    <h2>Online submissions are not available</h2>
                    <p>There is no active application form on this page. Please rely only on notices published through official 3PAP channels.</p>
                    <ul class="pmis-opportunities__status-list">
                        <li><span>Current status</span><strong>{{ $submissionsOpen ? 'Publication pending' : 'Closed' }}</strong></li>
                        <li><span>Submission form</span><strong>Disabled</strong></li>
                        <li><span>Future updates</span><strong>Published here</strong></li>
                    </ul>
                </aside>
            </div>
        </section>

        <section class="pmis-opportunities__section pmis-opportunities__section--soft" aria-labelledby="current-status-title">
            <div class="pmis-opportunities__wrap">
                @if ($errors->any())
                    <div class="pmis-opportunities__alert" role="alert">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle><path d="M12 8v5M12 16.5h.01"></path>
                        </svg>
                        <div>
                            <strong>Submissions are unavailable.</strong>
                            <span>No application has been accepted. There is currently no open online call.</span>
                        </div>
                    </div>
                @endif

                <div class="pmis-opportunities__empty" role="status">
                    <div class="pmis-opportunities__empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 7.5h16M7 4h10a2 2 0 0 1 2 2v14H5V6a2 2 0 0 1 2-2Z"></path>
                            <path d="M9 12h6M9 16h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 id="current-status-title">There are no open calls at this time</h2>
                        <p>When a verified opportunity is released, this page will show the complete notice and the approved response channel. No documents or expressions of interest are being accepted here now.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="pmis-opportunities__section" aria-labelledby="process-title">
            <div class="pmis-opportunities__wrap">
                <div class="pmis-opportunities__heading">
                    <p class="pmis-opportunities__kicker">A transparent process</p>
                    <h2 id="process-title">What happens when an opportunity opens</h2>
                    <p>Every public call will provide enough context for organisations to make an informed decision before responding.</p>
                </div>
                <div class="pmis-opportunities__steps">
                    <article class="pmis-opportunities__step">
                        <span class="pmis-opportunities__step-number" aria-hidden="true">01</span>
                        <h3>Notice published</h3>
                        <p>The purpose, scope, intended outcomes, eligibility and responsible contact are published in one place.</p>
                    </article>
                    <article class="pmis-opportunities__step">
                        <span class="pmis-opportunities__step-number" aria-hidden="true">02</span>
                        <h3>Response window confirmed</h3>
                        <p>Opening and closing times, required information, clarification arrangements and the official channel are stated clearly.</p>
                    </article>
                    <article class="pmis-opportunities__step">
                        <span class="pmis-opportunities__step-number" aria-hidden="true">03</span>
                        <h3>Updates remain traceable</h3>
                        <p>Material changes and outcome communications follow the governance process described in the notice.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="pmis-opportunities__section pmis-opportunities__section--soft" aria-labelledby="notice-content-title">
            <div class="pmis-opportunities__wrap pmis-opportunities__details">
                <div>
                    <p class="pmis-opportunities__kicker">Complete by design</p>
                    <h2 id="notice-content-title">What an official notice will contain</h2>
                    <p>Do not act on incomplete or unofficial requests. A published opportunity should make the decision path and accountability clear.</p>
                </div>
                <ul class="pmis-opportunities__checklist">
                    <li><span aria-hidden="true">01</span><strong>Objective, scope and expected outcomes</strong></li>
                    <li><span aria-hidden="true">02</span><strong>Eligibility and evaluation requirements</strong></li>
                    <li><span aria-hidden="true">03</span><strong>Key dates and clarification process</strong></li>
                    <li><span aria-hidden="true">04</span><strong>Required response information and formats</strong></li>
                    <li><span aria-hidden="true">05</span><strong>Approved submission channel and contact point</strong></li>
                </ul>
            </div>
        </section>

        <section class="pmis-opportunities__section" aria-labelledby="stay-informed-title">
            <div class="pmis-opportunities__wrap">
                <div class="pmis-opportunities__contact">
                    <div>
                        <p class="pmis-opportunities__kicker">Stay informed</p>
                        <h2 id="stay-informed-title">Explore 3PAP while you wait</h2>
                        <p>Learn how PMIS connects strategy, funding, delivery, evidence and decisions—or review upcoming public sessions.</p>
                    </div>
                    <div class="pmis-opportunities__actions">
                        <a class="pmis-opportunities__button" href="{{ route('events') }}">Events &amp; webinars</a>
                        <a class="pmis-opportunities__button" href="{{ route('applicants.faq') }}">PMIS FAQs</a>
                        <a class="pmis-opportunities__button pmis-opportunities__button--primary" href="https://3pap.africa">
                            Visit 3pap.africa <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
