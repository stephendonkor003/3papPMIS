@extends('layouts.public')

@section('title', 'Media & Insights | 3PAP PMIS')
@section('description', 'Explore 3PAP PMIS platform updates, portfolio insights, events, and published media.')

@push('styles')
<style>
    .media-hero {
        position: relative;
        overflow: hidden;
        padding: clamp(5rem, 10vw, 8rem) 1.5rem;
        border-bottom: 5px solid #1687EA;
        background: linear-gradient(128deg, #06152F 0%, #0A2450 58%, #0A6ED1 100%);
        color: #fff;
    }
    .media-hero::after {
        content: '';
        position: absolute;
        width: 30rem;
        height: 30rem;
        right: -10rem;
        top: -15rem;
        border: 1px solid rgba(103,217,255,.25);
        border-radius: 50%;
        box-shadow: 0 0 0 4rem rgba(103,217,255,.04), 0 0 0 8rem rgba(103,217,255,.03);
    }
    .media-wrap { width: min(1160px, 100%); margin: 0 auto; }
    .media-hero__inner { position: relative; z-index: 1; }
    .media-eyebrow { display: flex; align-items: center; gap: .7rem; margin: 0 0 1rem; color: #73D9FF; font-size: .74rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
    .media-eyebrow::before { content: ''; width: 2rem; height: 3px; background: #2FD0ED; }
    .media-hero h1 { max-width: 780px; margin: 0; color: #fff; font-size: clamp(2.8rem, 6vw, 5.4rem); line-height: 1; letter-spacing: -.05em; }
    .media-hero p:last-child { max-width: 660px; margin: 1.5rem 0 0; color: #B9C8D7; font-size: 1.03rem; line-height: 1.75; }
    .media-main { padding: clamp(4.5rem, 8vw, 7rem) 1.5rem; background: #F4F7FA; }
    .media-intro { display: grid; grid-template-columns: 1.2fr .8fr; gap: 3rem; align-items: end; margin-bottom: 2.8rem; }
    .media-intro h2 { margin: 0; color: #06152F; font-size: clamp(2rem, 4vw, 3.5rem); line-height: 1.08; letter-spacing: -.04em; }
    .media-intro p { margin: 0; color: #60728B; line-height: 1.75; }
    .media-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border-top: 1px solid #DBE4EE; border-left: 1px solid #DBE4EE; }
    .media-card { position: relative; display: flex; flex-direction: column; min-height: 250px; padding: 2rem; border-right: 1px solid #DBE4EE; border-bottom: 1px solid #DBE4EE; background: #fff; color: #14213D; text-decoration: none; transition: background .2s, transform .2s; }
    .media-card:hover { background: #EFF8FF; transform: translateY(-2px); color: #14213D; }
    .media-card span { color: #0A6ED1; font-size: .7rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .media-card h2 { max-width: 430px; margin: auto 0 .6rem; font-size: 1.5rem; line-height: 1.25; }
    .media-card p { margin: 0; color: #60728B; font-size: .88rem; }
    .media-card i { position: absolute; right: 2rem; top: 2rem; color: #0A6ED1; font-size: 1.3rem; font-style: normal; }
    .media-note { display: flex; align-items: center; gap: 1rem; margin-top: 2rem; padding: 1.2rem 1.35rem; border-left: 4px solid #0A6ED1; background: #fff; color: #41546E; font-size: .88rem; }
    .media-note strong { color: #06152F; }
    @media (max-width: 760px) { .media-intro, .media-grid { grid-template-columns: 1fr; } .media-card { min-height: 220px; } }
</style>
@endpush

@section('content')
<section class="media-hero">
    <div class="media-wrap media-hero__inner">
        <p class="media-eyebrow">3PAP PMIS resources</p>
        <h1>Media, insights, and delivery stories.</h1>
        <p>Follow platform developments, portfolio performance, institutional events, and practical learning from governed delivery.</p>
    </div>
</section>

<section class="media-main">
    <div class="media-wrap">
        <div class="media-intro">
            <h2>Explore current public information.</h2>
            <p>Published resources are organised around accountable portfolio delivery and the information stakeholders need to act.</p>
        </div>
        <div class="media-grid">
            <a class="media-card" href="{{ route('news.index') }}"><span>Newsroom</span><i>&rarr;</i><h2>Announcements and platform updates</h2><p>Read the latest approved communications.</p></a>
            <a class="media-card" href="{{ route('events') }}"><span>Events</span><i>&rarr;</i><h2>Briefings, webinars, and demonstrations</h2><p>Discover opportunities to learn about PMIS capabilities.</p></a>
            <a class="media-card" href="{{ route('impact.map') }}"><span>Portfolio insights</span><i>&rarr;</i><h2>Delivery and funding analytics</h2><p>Explore current portfolio information by project, partner, and geography.</p></a>
            <a class="media-card" href="{{ route('world.indicators.performance') }}"><span>Performance</span><i>&rarr;</i><h2>Indicators and decision-ready reporting</h2><p>Review performance evidence through the public analytics workspace.</p></a>
        </div>
        <div class="media-note"><strong>Photo library:</strong> Curated 3PAP PMIS media collections will appear here when approved for public release.</div>
    </div>
</section>
@endsection
