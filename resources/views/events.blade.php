@extends('layouts.public')

@section('title', 'Events & Webinars | 3PAP PMIS')
@section('description', 'Discover 3PAP PMIS events, product demonstrations and practitioner sessions for connected, accountable portfolio delivery.')

@push('styles')
    <style>
        .pmis-events {
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
            --pmis-surface: #ffffff;
            --pmis-surface-soft: #f4f7fa;
            --pmis-success: #138a5b;
            background: var(--pmis-surface);
            color: var(--pmis-text);
            overflow: hidden;
        }

        .pmis-events,
        .pmis-events * { box-sizing: border-box; }
        .pmis-events a { text-underline-offset: .2em; }
        .pmis-events a:focus-visible { outline: 3px solid var(--pmis-cyan-400); outline-offset: 3px; }
        .pmis-events__wrap { width: min(1160px, calc(100% - 2rem)); margin-inline: auto; }

        .pmis-events__hero {
            position: relative;
            isolation: isolate;
            padding: clamp(4.5rem, 9vw, 7.5rem) 0 clamp(4rem, 8vw, 6.5rem);
            background: radial-gradient(circle at 82% 18%, rgba(47, 208, 237, .22), transparent 26rem),
                linear-gradient(135deg, var(--pmis-navy-950), var(--pmis-navy-900) 52%, var(--pmis-navy-800));
            color: #fff;
        }

        .pmis-events__hero::after {
            position: absolute;
            inset: 0;
            z-index: -1;
            content: '';
            opacity: .2;
            background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px);
            background-size: 54px 54px;
            mask-image: linear-gradient(to right, transparent 5%, #000 65%);
        }

        .pmis-events__hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(270px, .7fr);
            gap: clamp(2rem, 6vw, 5.5rem);
            align-items: center;
        }

        .pmis-events__eyebrow,
        .pmis-events__section-kicker {
            margin: 0 0 1rem;
            color: var(--pmis-blue-700);
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .15em;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .pmis-events__hero .pmis-events__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            color: #8ce7ff;
        }

        .pmis-events__hero .pmis-events__eyebrow::before {
            width: 2rem;
            height: 2px;
            content: '';
            background: var(--pmis-cyan-400);
        }

        .pmis-events h1 {
            max-width: 760px;
            margin: 0;
            color: #fff;
            font-size: clamp(2.55rem, 6vw, 5rem);
            font-weight: 800;
            letter-spacing: -.055em;
            line-height: .98;
        }

        .pmis-events__lead {
            max-width: 680px;
            margin: 1.5rem 0 0;
            color: #d8e4ef;
            font-size: clamp(1rem, 2vw, 1.22rem);
            line-height: 1.75;
        }

        .pmis-events__actions { display: flex; flex-wrap: wrap; gap: .8rem; margin-top: 2rem; }

        .pmis-events__button {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            padding: .75rem 1.15rem;
            border: 1px solid transparent;
            border-radius: .7rem;
            font-size: .93rem;
            font-weight: 750;
            line-height: 1.2;
            text-decoration: none;
            transition: transform .2s ease, background-color .2s ease, border-color .2s ease;
        }

        .pmis-events__button:hover { transform: translateY(-2px); text-decoration: none; }
        .pmis-events__button--primary { background: var(--pmis-blue-600); color: #fff; box-shadow: 0 14px 30px rgba(10, 110, 209, .28); }
        .pmis-events__button--primary:hover { background: var(--pmis-blue-500); color: #fff; }
        .pmis-events__button--quiet { border-color: rgba(255,255,255,.3); background: rgba(255,255,255,.07); color: #fff; }
        .pmis-events__button--quiet:hover { border-color: rgba(255,255,255,.65); background: rgba(255,255,255,.13); color: #fff; }

        .pmis-events__brief {
            position: relative;
            padding: 1.35rem;
            border: 1px solid rgba(255,255,255,.17);
            border-radius: 1.1rem;
            background: rgba(255,255,255,.09);
            box-shadow: 0 26px 65px rgba(0,0,0,.18);
            backdrop-filter: blur(12px);
        }

        .pmis-events__brief-label {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin: 0 0 1.2rem;
            color: #b9c8d7;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .pmis-events__pulse { width: .6rem; height: .6rem; border-radius: 50%; background: var(--pmis-cyan-400); box-shadow: 0 0 0 5px rgba(47, 208, 237, .14); }
        .pmis-events__brief h2 { margin: 0; color: #fff; font-size: 1.25rem; line-height: 1.35; }
        .pmis-events__brief-list { display: grid; gap: .8rem; margin: 1.2rem 0 0; padding: 0; list-style: none; }

        .pmis-events__brief-list li {
            display: grid;
            grid-template-columns: 1.7rem 1fr;
            gap: .7rem;
            align-items: start;
            color: #dbe4ee;
            font-size: .9rem;
            line-height: 1.55;
        }

        .pmis-events__brief-list span { display: grid; width: 1.7rem; height: 1.7rem; place-items: center; border-radius: .45rem; background: rgba(47,208,237,.13); color: #8ce7ff; font-size: .72rem; font-weight: 800; }
        .pmis-events__section { padding: clamp(4rem, 8vw, 6.5rem) 0; }
        .pmis-events__section--soft { border-block: 1px solid var(--pmis-line); background: var(--pmis-surface-soft); }

        .pmis-events__section-heading {
            display: flex;
            max-width: 900px;
            align-items: end;
            justify-content: space-between;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .pmis-events__section-heading h2,
        .pmis-events__cta h2 { margin: 0; color: var(--pmis-ink); font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 800; letter-spacing: -.035em; line-height: 1.08; }
        .pmis-events__section-heading p { max-width: 640px; margin: .8rem 0 0; color: var(--pmis-muted); line-height: 1.7; }

        .pmis-events__notice {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1.25rem;
            align-items: center;
            padding: clamp(1.35rem, 3vw, 2rem);
            border: 1px solid var(--pmis-line);
            border-left: 4px solid var(--pmis-blue-600);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 15px 42px rgba(6, 21, 47, .07);
        }

        .pmis-events__notice-icon { display: grid; width: 3.25rem; height: 3.25rem; place-items: center; border-radius: .85rem; background: var(--pmis-blue-50); color: var(--pmis-blue-700); }
        .pmis-events__notice-icon svg { width: 1.45rem; height: 1.45rem; }
        .pmis-events__notice h3 { margin: 0; color: var(--pmis-ink); font-size: 1.05rem; }
        .pmis-events__notice p { margin: .35rem 0 0; color: var(--pmis-muted); font-size: .92rem; line-height: 1.6; }
        .pmis-events__text-link { color: var(--pmis-blue-700); font-size: .9rem; font-weight: 800; white-space: nowrap; }
        .pmis-events__formats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }

        .pmis-events__format {
            position: relative;
            min-height: 250px;
            padding: 1.6rem;
            border: 1px solid var(--pmis-line);
            border-radius: 1rem;
            background: #fff;
            overflow: hidden;
        }

        .pmis-events__format::after { position: absolute; right: -2rem; bottom: -2rem; width: 7rem; height: 7rem; border: 1.25rem solid var(--pmis-blue-50); border-radius: 50%; content: ''; }
        .pmis-events__format-number { display: block; margin-bottom: 2.4rem; color: var(--pmis-blue-600); font-size: .78rem; font-weight: 800; letter-spacing: .13em; }
        .pmis-events__format h3 { margin: 0; color: var(--pmis-ink); font-size: 1.15rem; }
        .pmis-events__format p { position: relative; z-index: 1; margin: .7rem 0 0; color: var(--pmis-muted); font-size: .92rem; line-height: 1.65; }
        .pmis-events__lifecycle { display: grid; grid-template-columns: repeat(5, 1fr); gap: .7rem; margin-top: 2.25rem; }
        .pmis-events__stage { position: relative; padding: 1.15rem .95rem; border-radius: .8rem; background: var(--pmis-blue-50); }
        .pmis-events__stage strong { display: block; color: var(--pmis-navy-900); font-size: .94rem; }
        .pmis-events__stage span { display: block; margin-top: .3rem; color: var(--pmis-muted); font-size: .76rem; line-height: 1.45; }

        .pmis-events__cta {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 2rem;
            align-items: center;
            padding: clamp(2rem, 5vw, 3.5rem);
            border-radius: 1.25rem;
            background: linear-gradient(125deg, var(--pmis-blue-50), #fff 72%);
            box-shadow: inset 0 0 0 1px var(--pmis-line);
        }

        .pmis-events__cta p { max-width: 670px; margin: .9rem 0 0; color: var(--pmis-muted); line-height: 1.7; }
        .pmis-events__cta .pmis-events__button--primary { white-space: nowrap; }

        @media (max-width: 900px) {
            .pmis-events__hero-grid, .pmis-events__cta { grid-template-columns: 1fr; }
            .pmis-events__brief { max-width: 620px; }
            .pmis-events__formats { grid-template-columns: 1fr; }
            .pmis-events__format { min-height: 0; }
            .pmis-events__format-number { margin-bottom: 1.4rem; }
            .pmis-events__lifecycle { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 680px) {
            .pmis-events__wrap { width: min(100% - 1.25rem, 1160px); }
            .pmis-events__hero { padding-top: 3.75rem; }
            .pmis-events__section-heading { display: block; }
            .pmis-events__notice { grid-template-columns: auto 1fr; }
            .pmis-events__notice .pmis-events__text-link { grid-column: 2; justify-self: start; }
            .pmis-events__lifecycle { grid-template-columns: 1fr; }
            .pmis-events__actions, .pmis-events__actions .pmis-events__button, .pmis-events__cta .pmis-events__button { width: 100%; }
        }

        @media (prefers-reduced-motion: reduce) {
            .pmis-events__button { transition: none; }
        }
    </style>
@endpush

@section('content')
    <div class="pmis-events">
        <section class="pmis-events__hero" aria-labelledby="events-title">
            <div class="pmis-events__wrap pmis-events__hero-grid">
                <div>
                    <p class="pmis-events__eyebrow">3PAP PMIS knowledge exchange</p>
                    <h1 id="events-title">Events &amp; Webinars</h1>
                    <p class="pmis-events__lead">
                        Practical conversations for leaders and delivery teams turning strategy into visible,
                        governed execution across portfolios, programmes, projects and funds.
                    </p>
                    <div class="pmis-events__actions">
                        <a class="pmis-events__button pmis-events__button--primary" href="mailto:info@3pap.com?subject=3PAP%20PMIS%20event%20enquiry">
                            Ask about upcoming sessions <span aria-hidden="true">&rarr;</span>
                        </a>
                        <a class="pmis-events__button pmis-events__button--quiet" href="{{ route('applicants.faq') }}">Explore PMIS FAQs</a>
                    </div>
                </div>

                <aside class="pmis-events__brief" aria-label="Session focus">
                    <p class="pmis-events__brief-label"><span class="pmis-events__pulse" aria-hidden="true"></span> Session focus</p>
                    <h2>One governed view of enterprise delivery</h2>
                    <ul class="pmis-events__brief-list">
                        <li><span aria-hidden="true">01</span>Portfolio and programme visibility for decision-makers</li>
                        <li><span aria-hidden="true">02</span>Connected funding, procurement, risk and performance workflows</li>
                        <li><span aria-hidden="true">03</span>Traceable evidence and decision-ready reporting</li>
                    </ul>
                </aside>
            </div>
        </section>

        <section class="pmis-events__section pmis-events__section--soft" aria-labelledby="schedule-title">
            <div class="pmis-events__wrap">
                <div class="pmis-events__section-heading">
                    <div>
                        <p class="pmis-events__section-kicker">Schedule</p>
                        <h2 id="schedule-title">Upcoming public sessions</h2>
                        <p>Confirmed sessions will be published here with the date, format, audience and registration details.</p>
                    </div>
                </div>

                <div class="pmis-events__notice" role="status">
                    <div class="pmis-events__notice-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                            <path d="M16 3v4M8 3v4M3 10h18"></path>
                        </svg>
                    </div>
                    <div>
                        <h3>No public sessions are currently scheduled</h3>
                        <p>For a tailored product conversation, contact the 3PAP team with your organisation and priority use case.</p>
                    </div>
                    <a class="pmis-events__text-link" href="https://3pap.africa">Visit 3pap.africa <span aria-hidden="true">&rarr;</span></a>
                </div>
            </div>
        </section>

        <section class="pmis-events__section" aria-labelledby="formats-title">
            <div class="pmis-events__wrap">
                <div class="pmis-events__section-heading">
                    <div>
                        <p class="pmis-events__section-kicker">What to expect</p>
                        <h2 id="formats-title">Sessions designed around real delivery work</h2>
                        <p>Each format connects platform capability to the governance, coordination and reporting questions institutions face every day.</p>
                    </div>
                </div>

                <div class="pmis-events__formats">
                    <article class="pmis-events__format">
                        <span class="pmis-events__format-number" aria-hidden="true">01 / DEMONSTRATE</span>
                        <h3>Platform demonstrations</h3>
                        <p>See how PMIS connects portfolios, programmes, projects, funds, procurement, people and performance in one operating environment.</p>
                    </article>
                    <article class="pmis-events__format">
                        <span class="pmis-events__format-number" aria-hidden="true">02 / IMPLEMENT</span>
                        <h3>Implementation briefings</h3>
                        <p>Explore configurable governance, role-based workflows, integration considerations and a practical path from setup to adoption.</p>
                    </article>
                    <article class="pmis-events__format">
                        <span class="pmis-events__format-number" aria-hidden="true">03 / EXCHANGE</span>
                        <h3>Practitioner exchanges</h3>
                        <p>Share approaches to portfolio oversight, financial governance, delivery assurance, evidence and executive decision support.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="pmis-events__section pmis-events__section--soft" aria-labelledby="lifecycle-title">
            <div class="pmis-events__wrap">
                <div class="pmis-events__section-heading">
                    <div>
                        <p class="pmis-events__section-kicker">Connected lifecycle</p>
                        <h2 id="lifecycle-title">From strategic priority to verified outcome</h2>
                        <p>Our event programme follows the complete delivery lifecycle so context, ownership and evidence remain connected at every stage.</p>
                    </div>
                </div>
                <div class="pmis-events__lifecycle" aria-label="PMIS delivery lifecycle">
                    <div class="pmis-events__stage"><strong>Prioritise</strong><span>Align demand and investment to strategy.</span></div>
                    <div class="pmis-events__stage"><strong>Plan</strong><span>Structure outcomes, resources and controls.</span></div>
                    <div class="pmis-events__stage"><strong>Execute</strong><span>Coordinate work, partners and decisions.</span></div>
                    <div class="pmis-events__stage"><strong>Assure</strong><span>Monitor performance, risk and evidence.</span></div>
                    <div class="pmis-events__stage"><strong>Learn</strong><span>Evaluate results and improve investment.</span></div>
                </div>
            </div>
        </section>

        <section class="pmis-events__section" aria-labelledby="event-contact-title">
            <div class="pmis-events__wrap">
                <div class="pmis-events__cta">
                    <div>
                        <p class="pmis-events__section-kicker">Start a conversation</p>
                        <h2 id="event-contact-title">Need a session tailored to your institution?</h2>
                        <p>Tell us about your delivery environment, the teams involved and the decisions PMIS needs to support.</p>
                    </div>
                    <a class="pmis-events__button pmis-events__button--primary" href="mailto:info@3pap.com?subject=Tailored%203PAP%20PMIS%20session">
                        Contact the 3PAP team <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
