<!DOCTYPE html>

@php
$rtl = get_session_language()->rtl;
@endphp

@if ($rtl == 1)
<html dir="rtl" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@else
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endif

<head>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ getBaseURL() }}">
    <meta name="file-base-url" content="{{ getFileBaseURL() }}">

    <title>@yield('meta_title', get_setting('website_name') . ' | ' . get_setting('site_motto'))</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="description" content="@yield('meta_description', get_setting('meta_description'))" />
    <meta name="keywords" content="@yield('meta_keywords', get_setting('meta_keywords'))">

    <!-- Favicon -->
    @php
    $site_icon = uploaded_asset(get_setting('site_icon'));
    @endphp
    <link rel="icon" href="{{ $site_icon }}">
    <link rel="apple-touch-icon" href="{{ $site_icon }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body class="text-white d-flex flex-column justify-content-center align-items-center min-vh-100">

    <!-- Logo -->
    <div class="mb-4">
        <img src="{{ uploaded_asset(get_setting('site_icon'))}}" alt="Logo" class="img-fluid" style="width: 100%;">
    </div>

    <!-- Mobile App Links -->
    <div class="col-xxl-3 col-xl-4 col-lg-4 mt-1rem justify-content-center">
        <div class="d-flex mt-3 justify-content-center">
            <div class="">
                <a href="{{ get_setting('play_store_link') }}" target="_blank"
                    class="mr-2 mb-2 overflow-hidden hov-scale-img">
                    <img class="lazyload has-transition" src="{{ static_asset('assets/img/play.png') }}"
                        alt="{{ env('APP_NAME') }}" height="44" width="150">
                </a>
            </div>
            <div class="">
                <a href="{{ get_setting('app_store_link') }}" target="_blank" class="overflow-hidden hov-scale-img">
                    <img class="lazyload has-transition" src="{{ static_asset('assets/img/app.png') }}"
                        alt="{{ env('APP_NAME') }}" height="44" width="150">
                </a>
            </div>
        </div>
    </div>
</body>
</html>
