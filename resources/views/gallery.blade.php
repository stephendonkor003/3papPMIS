@extends('layouts.public')

@section('title', __('gallery.meta.title'))
@section('description', __('gallery.meta.description'))

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
        inset-inline-end: -10rem;
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
    .media-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border-top: 1px solid #DBE4EE; border-inline-start: 1px solid #DBE4EE; }
    .media-card { position: relative; display: flex; flex-direction: column; min-height: 250px; padding: 2rem; border-inline-end: 1px solid #DBE4EE; border-bottom: 1px solid #DBE4EE; background: #fff; color: #14213D; text-decoration: none; transition: background .2s, transform .2s; }
    .media-card:hover { background: #EFF8FF; transform: translateY(-2px); color: #14213D; }
    .media-card span { color: #0A6ED1; font-size: .7rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .media-card h2 { max-width: 430px; margin: auto 0 .6rem; font-size: 1.5rem; line-height: 1.25; }
    .media-card p { margin: 0; color: #60728B; font-size: .88rem; }
    .media-card i { position: absolute; inset-inline-end: 2rem; top: 2rem; color: #0A6ED1; font-size: 1.3rem; font-style: normal; }
    .media-note { display: flex; align-items: center; gap: 1rem; margin-top: 2rem; padding: 1.2rem 1.35rem; border-inline-start: 4px solid #0A6ED1; background: #fff; color: #41546E; font-size: .88rem; }
    [dir="rtl"] .media-card i { transform: scaleX(-1); }
    .media-note strong { color: #06152F; }
    @media (max-width: 760px) { .media-intro, .media-grid { grid-template-columns: 1fr; } .media-card { min-height: 220px; } }
</style>
@endpush

@section('content')
<section class="media-hero">
    <div class="media-wrap media-hero__inner">
        <p class="media-eyebrow">{{ __('gallery.hero.eyebrow') }}</p>
        <h1>{{ __('gallery.hero.title') }}</h1>
        <p>{{ __('gallery.hero.description') }}</p>
    </div>
</section>

<section class="media-main">
    <div class="media-wrap">
        <div class="media-intro">
            <h2>{{ __('gallery.intro.title') }}</h2>
            <p>{{ __('gallery.intro.description') }}</p>
        </div>
        <div class="media-grid">
            @php($galleryRoutes = [route('news.index'), route('events'), route('impact.map'), route('world.indicators.performance')])
            @foreach (trans('gallery.cards') as $index => $card)
                <a class="media-card" href="{{ $galleryRoutes[$index] }}"><span>{{ $card['label'] }}</span><i>&rarr;</i><h2>{{ $card['title'] }}</h2><p>{{ $card['description'] }}</p></a>
            @endforeach
        </div>
        <div class="media-note"><strong>{{ __('gallery.note.label') }}</strong> {{ __('gallery.note.description') }}</div>
    </div>
</section>
@endsection
