<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>3PAP PMIS</title>
        <link rel="icon" href="{{ asset('assets/images/3pap-icon.svg') }}" type="image/svg+xml">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <style>
            *, *::before, *::after { box-sizing: border-box; }
            body {
                margin: 0;
                color: #111827;
                font-family: Figtree, ui-sans-serif, system-ui, sans-serif;
                -webkit-font-smoothing: antialiased;
            }
            .pmis-guest-shell {
                display: flex;
                min-height: 100vh;
                padding: 1.5rem 1rem;
                align-items: center;
                background: linear-gradient(145deg, #f4f7fa 0%, #dceeff 100%);
                color: #14213d;
                flex-direction: column;
                justify-content: center;
            }
            .pmis-guest-logo { width: 180px; height: 72px; object-fit: contain; }
            .pmis-guest-product { margin-top: .65rem; color: #0a2450; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-align: center; text-transform: uppercase; }
            .pmis-guest-card {
                overflow: hidden;
                width: 100%;
                max-width: 28rem;
                margin-top: 1.5rem;
                padding: 1rem 1.5rem;
                border-top: 4px solid #1687ea;
                border-radius: .5rem;
                background: #fff;
                box-shadow: 0 10px 24px rgba(6, 21, 47, .12);
            }
            .pmis-guest-support { margin-top: 1rem; color: #60728b; font-size: .8rem; }
            .pmis-guest-support a { color: #0a6ed1; font-weight: 700; text-decoration: none; }
        </style>
        <link rel="stylesheet" href="{{ asset('assets/css/3pap-public.css') }}">
    </head>
    <body>
        <div class="pmis-guest-shell">
            <div>
                <a href="{{ route('landing.index') }}" aria-label="3PAP PMIS Home">
                    <img src="{{ asset('assets/images/3pap-logo-dark.png') }}" alt="3PAP PMIS" class="pmis-guest-logo">
                </a>
                <div class="pmis-guest-product">Portfolio Management Information System</div>
            </div>

            <div class="pmis-guest-card">
                {{ $slot }}
            </div>
            <p class="pmis-guest-support">Need help? <a href="mailto:info@3pap.com">info@3pap.com</a></p>
        </div>
    </body>
</html>
