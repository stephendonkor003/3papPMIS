<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', '3PAP PMIS — Portfolio Management Information System')">
    <meta name="theme-color" content="#06152F">
    <title>@yield('title', '3PAP PMIS')</title>
    <link rel="icon" href="{{ asset('assets/images/3pap-icon.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/css/feather.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
    @if (app()->getLocale() === 'ar')
        <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}">
    @endif
    <style>
        body.attp-public-page { margin: 0; background: #f4f7fa; font-family: Inter, sans-serif; color: #14213d; }
        .attp-public-main { min-height: calc(100vh - 280px); }
    </style>
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/3pap-public.css') }}">
</head>
<body class="public-mobile-nav attp-public-page pmis-marketing-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    @include('partials.public-marketing-header')

    <main class="attp-public-main" id="main-content">
        @yield('content')
    </main>

    @include('partials.public-marketing-footer')

    <script src="{{ asset('admin/assets/vendors/js/bootstrap.min.js') }}"></script>
    @include('partials.public-marketing-scripts')
    @stack('scripts')
    @stack('modals')
</body>
</html>
