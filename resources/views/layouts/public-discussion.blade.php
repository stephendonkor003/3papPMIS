<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description')">
    <meta name="referrer" content="no-referrer">
    <meta name="theme-color" content="#06152F">
    <title>@yield('title')</title>
    <link rel="icon" href="{{ asset('assets/images/3pap-icon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/discussion-forum.css') }}?v={{ filemtime(public_path('assets/css/discussion-forum.css')) }}">
    @if(app()->getLocale() === 'ar')
        <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/3pap-public.css') }}">
</head>
<body class="public-mobile-nav discussion-forum-page pmis-marketing-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    @include('partials.public-marketing-header', ['skipTarget' => '#discussion-content', 'skipLabel' => 'Skip to forum content'])

    <main class="discussion-main" id="discussion-app">
        <section class="discussion-hero">
            <div class="discussion-hero__pattern" aria-hidden="true"></div>
            <div class="discussion-hero__inner">
                <div class="discussion-hero__copy">
                    <span class="discussion-eyebrow"><span class="forum-live-dot" aria-hidden="true"></span>{{ __('discussion.eyebrow') }}</span>
                    <h1>@yield('heading')</h1>
                    <p>@yield('description')</p>
                    <div class="discussion-hero__actions">
                        <a class="forum-button forum-button--gold" href="{{ route('discussion.current') }}">Explore active discussions <span aria-hidden="true">→</span></a>
                        <a class="forum-button forum-button--hero-outline" href="{{ route('discussion.join') }}">Participate in a discussion</a>
                    </div>
                </div>
                <aside class="discussion-hero__card" aria-label="Forum participation">
                    <div class="discussion-hero__card-icon" aria-hidden="true">✦</div>
                    <span class="discussion-hero__card-label">3PAP PMIS Community Forum</span>
                    <strong>Ideas become stronger through informed dialogue.</strong>
                    <p>Read openly. Register simply. Contribute respectfully.</p>
                    <div class="discussion-hero__member" v-cloak v-if="participant">
                        <span class="forum-avatar forum-avatar--tiny" aria-hidden="true" v-text="initials(participant.display_name)"></span>
                        <span>Signed in as <strong v-text="participant.display_name"></strong></span>
                    </div>
                </aside>
            </div>
        </section>

        <section class="discussion-content" id="discussion-content">
            @include('discussion._forum-sections')
        </section>

        <noscript>
            <section class="forum-noscript">
                <h2>JavaScript is required for the live discussion forum</h2>
                <p>Please enable JavaScript to load thematic areas, read discussions, or create a participant account.</p>
            </section>
        </noscript>
    </main>

    @include('partials.public-marketing-footer')

    @php
        $discussionConfig = [
            'apiBase' => url('/api/discussions'),
            'initialView' => trim($__env->yieldContent('forum_view', 'themes')),
            'locale' => app()->getLocale(),
            'pollInterval' => 30000,
            'urls' => [
                'themes' => route('discussion.thematic-areas'),
                'current' => route('discussion.current'),
                'join' => route('discussion.join'),
            ],
        ];
    @endphp
    <script>window.ATTP_DISCUSSION_CONFIG = @json($discussionConfig);</script>
    <script src="{{ asset('admin/assets/vendors/js/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendors/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/vue.global.prod.js') }}"></script>
    <script src="{{ asset('assets/js/discussion-forum.js') }}?v={{ filemtime(public_path('assets/js/discussion-forum.js')) }}"></script>
    @include('partials.public-marketing-scripts')
</body>
</html>
