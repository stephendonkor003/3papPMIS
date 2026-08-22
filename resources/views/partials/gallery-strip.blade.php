<section class="pmis-resource-strip" aria-label="{{ __('gallery.strip.aria_label') }}">
    <style>
        .pmis-resource-strip { padding: 2.5rem 1.5rem; border-top: 1px solid #DBE4EE; border-bottom: 1px solid #DBE4EE; background: #EFF8FF; }
        .pmis-resource-strip__inner { width: min(1160px, 100%); margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 2rem; }
        .pmis-resource-strip__copy span { color: #0A6ED1; font-size: .7rem; font-weight: 800; letter-spacing: .11em; text-transform: uppercase; }
        .pmis-resource-strip__copy h2 { margin: .3rem 0 0; color: #06152F; font-size: clamp(1.35rem, 3vw, 2rem); letter-spacing: -.03em; }
        .pmis-resource-strip__links { display: flex; flex-wrap: wrap; gap: .65rem; flex: 0 0 auto; }
        .pmis-resource-strip__links a { display: inline-flex; min-height: 42px; align-items: center; padding: .65rem 1rem; border: 1px solid #B9C8D7; border-radius: 2px; background: #fff; color: #0A2450; font-size: .82rem; font-weight: 800; text-decoration: none; }
        .pmis-resource-strip__links a:first-child { border-color: #0A6ED1; background: #0A6ED1; color: #fff; }
        .pmis-resource-strip__links a:hover { border-color: #0756C7; }
        @media (max-width: 720px) { .pmis-resource-strip__inner { align-items: flex-start; flex-direction: column; } .pmis-resource-strip__links { width: 100%; } .pmis-resource-strip__links a { flex: 1; justify-content: center; } }
    </style>
    <div class="pmis-resource-strip__inner">
        <div class="pmis-resource-strip__copy"><span>{{ __('gallery.strip.eyebrow') }}</span><h2>{{ __('gallery.strip.title') }}</h2></div>
        <div class="pmis-resource-strip__links">
            <a href="{{ route('news.index') }}">{{ __('gallery.strip.news') }} &rarr;</a>
            <a href="{{ route('gallery') }}">{{ __('gallery.strip.media') }}</a>
        </div>
    </div>
</section>
