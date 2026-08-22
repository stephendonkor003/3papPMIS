<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $direction = config("localization.supported.{$locale}.direction", 'ltr');
@endphp
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('news_public.meta.title') }}</title>
    <meta name="description" content="{{ __('news_public.meta.description') }}">
    <link rel="icon" href="{{ asset('assets/images/3pap-icon.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
    <style>
        :root {
            --brand-primary: #0A6ED1;
            --brand-navy:    #06152F;
            --brand-blue:    #1687EA;
            --brand-accent:  #2FD0ED;
            --brand-surface: #F4F7FA;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', Arial, sans-serif; background: var(--brand-surface); color: #14213D; }

        /* ── NAVBAR ── */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 5%; background: var(--brand-primary);
            box-shadow: 0 2px 12px rgba(0,0,0,.2);
            position: sticky; top: 0; z-index: 100;
        }
        .nav-links a { margin-left: 20px; font-weight: 500; color: #fff; text-decoration: none; font-size: .9rem; transition: color .2s; }
        .nav-links a.active, .nav-links a:hover { color: var(--brand-accent); }

        /* ── HERO ── */
        .news-hero {
            min-height: 320px;
            background: linear-gradient(135deg, rgba(6,21,47,.94), rgba(10,110,209,.86)),
                        url('{{ asset('assets/images/3pap-og-image.svg') }}') center/cover no-repeat;
            display: flex; align-items: center; justify-content: center;
            text-align: center; padding: 60px 24px 80px;
            color: #fff;
        }
        .news-hero h1 { font-size: 2.8rem; margin: 0 0 12px; color: var(--brand-accent); }
        .news-hero p { max-width: 680px; margin: 0 auto; line-height: 1.75; opacity: .92; font-size: 1.05rem; }

        /* ── FILTER BAR ── */
        .filter-wrap {
            max-width: 1160px; margin: -44px auto 0; padding: 0 24px; position: relative; z-index: 10;
        }
        .filter-bar {
            background: #fff; border-radius: 14px;
            box-shadow: 0 12px 32px rgba(0,0,0,.13);
            padding: 20px 24px;
            display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
        }
        .filter-bar input, .filter-bar select {
            flex: 1; min-width: 180px;
            border: 1px solid #DBE4EE; border-radius: 8px;
            padding: 11px 14px; font: inherit; font-size: .95rem;
            background: #EFF8FF; outline: none; transition: border-color .2s;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color: var(--brand-primary); background: #fff; }
        .filter-bar button {
            padding: 11px 26px; border-radius: 30px; border: none;
            background: var(--brand-primary); color: #fff; font-weight: 700; font-size: .95rem;
            cursor: pointer; white-space: nowrap; transition: background .2s;
        }
        .filter-bar button:hover { background: #0756C7; }

        /* ── MAIN CONTENT ── */
        .content-wrap { max-width: 1160px; margin: 40px auto 60px; padding: 0 24px; }

        /* ── CATEGORY TABS ── */
        .category-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 28px; }
        .cat-tab {
            padding: 6px 16px; border-radius: 99px; font-size: .85rem; font-weight: 600;
            border: 1.5px solid #DBE4EE; background: #fff; color: #283B58;
            text-decoration: none; transition: all .2s; white-space: nowrap;
        }
        .cat-tab:hover, .cat-tab.active {
            background: var(--brand-primary); color: #fff; border-color: var(--brand-primary);
        }

        /* ── CARD GRID ── */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
        }

        .news-card {
            background: #fff; border-radius: 14px;
            overflow: hidden; display: flex; flex-direction: column;
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
            border: 1px solid #DBE4EE;
            transition: transform .25s, box-shadow .25s;
        }
        .news-card:hover { transform: translateY(-4px); box-shadow: 0 10px 32px rgba(10,110,209,.14); }

        .card-cover { height: 200px; overflow: hidden; background: #DCEEFF; flex-shrink: 0; }
        .card-cover img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
        .news-card:hover .card-cover img { transform: scale(1.04); }

        .card-body { padding: 20px; display: flex; flex-direction: column; gap: 10px; flex: 1; }

        .card-badge {
            display: inline-block; background: #EFF8FF; color: var(--brand-primary);
            padding: 4px 10px; border-radius: 99px; font-size: .75rem; font-weight: 700;
            text-transform: capitalize; width: fit-content;
        }

        .card-title {
            font-size: 1.05rem; font-weight: 700; line-height: 1.4;
            color: #14213D; margin: 0; text-decoration: none; display: block;
        }
        .card-title:hover { color: var(--brand-primary); }

        .card-meta { font-size: .8rem; color: #60728B; }

        .card-excerpt { color: #283B58; line-height: 1.6; font-size: .9rem; margin: 0; flex: 1; }

        .card-link {
            display: inline-block; margin-top: 4px; padding: 9px 18px; border-radius: 8px;
            background: var(--brand-primary); color: #fff; font-weight: 700; font-size: .85rem;
            text-decoration: none; width: fit-content; transition: background .2s;
        }
        .card-link:hover { background: #0756C7; }

        /* ── EMPTY STATE ── */
        .news-empty {
            grid-column: 1 / -1; text-align: center;
            background: #fff; border: 1px solid #DBE4EE; border-radius: 14px;
            padding: 60px 40px;
        }
        .news-empty h2 { color: var(--brand-primary); margin-bottom: 10px; }
        .news-empty p { color: #60728B; }

        /* ── PAGINATION ── */
        .pagination-wrap { margin-top: 36px; display: flex; justify-content: center; }
        .pagination-wrap nav { display: flex; }

        /* ── SUBSCRIBE BANNER ── */
        .subscribe-banner {
            margin-top: 48px; background: var(--brand-navy);
            border-radius: 16px; padding: 36px 40px;
            display: grid; grid-template-columns: 1fr auto;
            gap: 24px; align-items: center;
        }
        .subscribe-banner h2 { margin: 0 0 8px; color: var(--brand-accent); font-size: 1.5rem; }
        .subscribe-banner p { margin: 0; color: #DCEEFF; line-height: 1.6; }
        .sub-form { display: flex; gap: 10px; }
        .sub-form input {
            min-width: 260px; border: none; border-radius: 8px;
            padding: 12px 16px; font: inherit; font-size: .95rem; outline: none;
        }
        .sub-form button {
            padding: 12px 24px; border: none; border-radius: 8px;
            background: var(--brand-accent); color: #06152F; font-weight: 700; font-size: .95rem;
            cursor: pointer; white-space: nowrap; transition: opacity .2s;
        }
        .sub-form button:hover { opacity: .88; }

        /* ── ALERT ── */
        .alert-success {
            background: #dcfce7; color: #166534;
            border: 1px solid #86efac; border-radius: 10px;
            padding: 14px 18px; margin-bottom: 24px; font-weight: 500;
        }

        /* ── FOOTER ── */
        footer.footer { margin-top: 0; }

        /* ── RESPONSIVE ── */
        @media (max-width: 960px) {
            .news-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .news-hero h1 { font-size: 2rem; }
            .news-grid { grid-template-columns: 1fr; }
            .filter-bar { flex-direction: column; }
            .filter-wrap { margin-top: -30px; }
            .subscribe-banner { grid-template-columns: 1fr; }
            .sub-form { flex-direction: column; }
            .sub-form input { min-width: 0; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/3pap-public.css') }}">
    @if($direction === 'rtl')
        <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}">
    @endif
</head>
<body class="public-mobile-nav pmis-marketing-page" dir="{{ $direction }}">
@include('partials.public-marketing-header')

<!-- ── HERO ── -->
<section class="news-hero" id="main-content" tabindex="-1">
    <div>
        <h1>{{ __('news_public.hero.title') }}</h1>
        <p>{{ __('news_public.hero.description') }}</p>
    </div>
</section>

<!-- ── FILTER BAR ── -->
<div class="filter-wrap">
    <form class="filter-bar" method="GET" action="{{ route('news.index') }}">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('news_public.filters.search_placeholder') }}" aria-label="{{ __('news_public.filters.search_label') }}">
        <select name="category" aria-label="{{ __('news_public.filters.category_label') }}">
            <option value="">{{ __('news_public.filters.all_categories') }}</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" @selected(request('category') === $cat)>
                    {{ ucfirst(str_replace('_', ' ', $cat)) }}
                </option>
            @endforeach
        </select>
        <button type="submit">{{ __('news_public.filters.search_button') }}</button>
        @if(request('q') || request('category'))
            <a href="{{ route('news.index') }}" style="padding:11px 16px;border-radius:8px;border:1.5px solid #DBE4EE;color:#283B58;text-decoration:none;font-weight:600;font-size:.9rem;white-space:nowrap;">{{ __('news_public.filters.clear') }}</a>
        @endif
    </form>
</div>

<!-- ── CONTENT ── -->
<main class="content-wrap">
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if(request('q') || request('category'))
        <p style="color:#60728B;margin-bottom:20px;">
            {{ __('news_public.results.showing') }}
            @if(request('q')) {{ __('news_public.results.for_query') }} "<strong>{{ request('q') }}</strong>"@endif
            @if(request('category')) {{ __('news_public.results.in_category') }} "<strong>{{ ucfirst(str_replace('_', ' ', request('category'))) }}</strong>"@endif
            — {{ trans_choice('news_public.results.count', $posts->total(), ['count' => $posts->total()]) }}
        </p>
    @endif

    <section class="news-grid">
        @forelse($posts as $post)
            <article class="news-card">
                <div class="card-cover">
                    <img src="{{ $post->cover_image_url ?: $newsCoverFallbackUrl }}"
                         alt="{{ $post->title }}" loading="lazy"
                         data-fallback-src="{{ $newsCoverFallbackUrl }}"
                         onerror="this.onerror=null;this.src=this.dataset.fallbackSrc;">
                </div>
                <div class="card-body">
                    <span class="card-badge">{{ str_replace('_', ' ', $post->category) }}</span>
                    <a class="card-title" href="{{ route('news.show', $post) }}">{{ $post->title }}</a>
                    <div class="card-meta">
                        {{ $post->published_at?->locale($locale)->translatedFormat('d M Y') }}
                    </div>
                    <p class="card-excerpt">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 140) }}</p>
                    <a class="card-link" href="{{ route('news.show', $post) }}">{{ __('news_public.cards.read_more') }} &rarr;</a>
                </div>
            </article>
        @empty
            <div class="news-empty">
                <h2>{{ __('news_public.empty.title') }}</h2>
                <p>{{ __('news_public.empty.description') }}</p>
            </div>
        @endforelse
    </section>

    @if($posts->hasPages())
        <div class="pagination-wrap">
            {{ $posts->links() }}
        </div>
    @endif

    <!-- ── SUBSCRIBE BANNER ── -->
    <div class="subscribe-banner">
        <div>
            <h2>{{ __('news_public.subscribe.title') }}</h2>
            <p>{{ __('news_public.subscribe.description') }}</p>
        </div>
        <form class="sub-form" method="POST" action="{{ route('news.subscribe') }}">
            @csrf
            <input type="email" name="email" placeholder="{{ __('news_public.subscribe.email_placeholder') }}" required aria-label="{{ __('news_public.subscribe.email_label') }}">
            <button type="submit">{{ __('news_public.subscribe.button') }}</button>
        </form>
    </div>
</main>

@include('partials.gallery-strip')

@include('partials.public-marketing-footer')
@include('partials.public-marketing-scripts')

<script>
    document.addEventListener('click', function(e) {
        document.querySelectorAll('.lang-switcher.open').forEach(function(el) {
            if (!el.contains(e.target)) el.classList.remove('open');
        });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.lang-switcher.open').forEach(function(el) {
                el.classList.remove('open');
            });
        }
    });
</script>
</body>
</html>
