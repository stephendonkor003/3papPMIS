@php($documentDirection = config('localization.supported.'.app()->getLocale().'.direction', 'ltr'))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $documentDirection }}">

<head>
    <meta charset="UTF-8">
    <title>{{ __('procurement_public.meta_title') }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('procurement_public.meta_description') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #F4F7FA;
            color: #14213D;
            margin: 0;
        }

        /* ===== HEADER ===== */
        .events-header {
            background:
                linear-gradient(135deg, rgba(6, 21, 47, .18), rgba(10, 110, 209, .22)),
                url('{{ asset('assets/images/3pap-og-image.svg') }}') center/cover no-repeat;
            height: 360px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .events-header::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(6, 21, 47, .92), rgba(10, 52, 121, .76));
        }

        .header-content {
            position: relative;
            text-align: center;
            max-width: 800px;
            padding: 0 1rem;
        }

        .header-content h1 {
            color: #2FD0ED;
            font-size: 2.5rem;
            margin-bottom: .5rem;
        }

        .header-content p {
            font-size: 1.05rem;
            line-height: 1.6;
        }

        /* ===== FILTER BAR ===== */
        .filter-bar {
            background: #fff;
            margin: -50px auto 2.5rem;
            max-width: 1100px;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            display: flex;
            gap: 1rem;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .1);
        }

        .filter-bar input {
            padding: .8rem 1rem;
            width: 360px;
            border-radius: 8px;
            border: 1px solid #DBE4EE;
            font-size: 1rem;
        }

        .filter-bar button {
            background: #0A6ED1;
            color: #fff;
            border: none;
            padding: .8rem 1.6rem;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: .3s;
        }

        .filter-bar button:hover {
            background: #0756C7;
        }

        /* ===== GRID ===== */
        .events-container {
            max-width: 1200px;
            margin: 0 auto 4rem;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .event-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .1);
            transition: transform .3s ease;
        }

        .event-card:hover {
            transform: translateY(-6px);
        }

        .event-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .event-card-media {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: #DCEEFF;
        }

        .event-card-media > img {
            height: 100%;
        }

        .event-card-owner {
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-radius: 9px;
            background: rgba(6, 21, 47, .9);
            padding: 8px 10px;
            color: #fff;
            backdrop-filter: blur(8px);
        }

        .event-card-owner img,
        .event-card-owner span:first-child {
            display: grid;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            place-items: center;
            border-radius: 7px;
            background: #fff;
            object-fit: contain;
            color: #0756C7;
            font-weight: 800;
        }

        .event-card-owner strong {
            overflow: hidden;
            font-size: .78rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin: .8rem 0 0;
        }

        .card-meta span {
            border-radius: 999px;
            background: #EFF8FF;
            padding: 5px 8px;
            color: #0756C7;
            font-size: .74rem;
            font-weight: 700;
        }

        .card-body {
            padding: 1.2rem 1.3rem;
        }

        .card-body h4 {
            color: #0A2450;
            margin-bottom: .5rem;
            font-size: 1.1rem;
        }

        .card-body p {
            color: #60728B;
            font-size: .95rem;
            line-height: 1.5;
        }

        .btn-view {
            display: inline-block;
            margin-top: 1rem;
            background: #0A6ED1;
            color: #fff;
            padding: .6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: .3s;
        }

        .btn-view:hover {
            background: #0756C7;
        }

        @media (max-width: 992px) {
            .events-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .filter-bar {
                width: calc(100% - 2rem);
                margin: -35px 1rem 2rem;
                padding: 1rem;
                flex-direction: column;
            }

            .filter-bar input,
            .filter-bar button {
                width: 100%;
            }

            .events-container {
                grid-template-columns: 1fr;
                padding: 0 1rem;
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/3pap-public.css') }}">
    @if ($documentDirection === 'rtl')
        <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}">
    @endif
</head>

<body class="public-mobile-nav pmis-marketing-page" dir="{{ $documentDirection }}">
    @include('partials.public-marketing-header')



    <section class="events-header" id="main-content" tabindex="-1">
        <div class="header-content">
            <h1>{{ __('procurement_public.hero_title') }}</h1>
            <p>{{ __('procurement_public.hero_description') }}</p>
        </div>
    </section>
    <br>
    <br>
    <br>

    <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="{{ __('procurement_public.search_placeholder') }}">
        <button onclick="filterProcurements()">{{ __('procurement_public.search_button') }}</button>
    </div>


    <section class="events-container" id="procurementContainer">

        @forelse($procurements as $procurement)
            <div class="event-card">
                <div class="event-card-media">
                    <img src="{{ $procurement->cover_image_url ?: asset('assets/images/3pap-og-image.svg') }}" alt="{{ __('procurement_public.cover_image_alt', ['title' => $procurement->title]) }}">
                    @if($procurement->thinkTankMember)
                        <div class="event-card-owner">
                            @if($procurement->thinkTankMember->logo_url)
                                <img src="{{ $procurement->thinkTankMember->logo_url }}" alt="{{ __('procurement_public.organization_logo_alt', ['organization' => $procurement->thinkTankMember->name]) }}">
                            @else
                                <span>{{ Str::upper(Str::substr($procurement->thinkTankMember->name, 0, 1)) }}</span>
                            @endif
                            <strong>{{ $procurement->thinkTankMember->name }}</strong>
                        </div>
                    @endif
                </div>

                <div class="card-body">
                    <h4>{{ $procurement->title }}</h4>

                    <p>
                        {{ \Illuminate\Support\Str::limit(strip_tags($procurement->description), 130) }}
                    </p>

                    <div class="card-meta">
                        <span>{{ $procurement->reference_no ?: __('procurement_public.open_call') }}</span>
                        @if($procurement->application_end_date)<span>{{ __('procurement_public.closes', ['date' => $procurement->application_end_date->locale(app()->getLocale())->translatedFormat('d M Y')]) }}</span>@endif
                        @if($procurement->activeForm)<span>{{ trans_choice('procurement_public.form_questions', $procurement->activeForm->fields->count(), ['count' => $procurement->activeForm->fields->count()]) }}</span>@endif
                    </div>

                    <a href="{{ route('public.procurement.show', $procurement->slug) }}" class="btn-view">
                        {{ __('procurement_public.view_details_apply') }}
                    </a>
                </div>
            </div>

        @empty
            <p style="grid-column:1/-1;text-align:center;font-weight:600;">
                {{ __('procurement_public.no_open_procurements') }}
            </p>
        @endforelse

    </section>


    <script>
        function filterProcurements() {
            const search = document
                .getElementById('searchInput')
                .value
                .toLowerCase();

            document.querySelectorAll('.event-card').forEach(card => {
                card.style.display = card.innerText.toLowerCase().includes(search) ?
                    'block' :
                    'none';
            });
        }
    </script>

    @include('partials.gallery-strip')

    @include('partials.public-marketing-footer')

    <script src="{{ asset('assets/script.js') }}"></script>
    @include('partials.public-marketing-scripts')

    <script type="text/javascript">
        var Tawk_API = Tawk_API || {},
            Tawk_LoadStart = new Date();
        (function() {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/69204852eba156195f5dae48/1jaj1l0r8';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>


</body>

</html>
