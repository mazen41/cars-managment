<section class="py-lg-3 text-light footer-widget" style="background-color: #393d41 !important; ">
    <!-- footer widgets ========== [Accordion Fotter widgets are bellow from this]-->
    <div class="container d-none d-lg-block">
        <div class="row">
            <!-- footer logo -->
            <div class="col-md-4 col-sm-6">
                <a href="{{ route('home') }}" class="d-block mt-4">
                    @if(get_setting('footer_logo') != null)
                    <img class="lazyload h-45px" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                        data-src="{{ uploaded_asset(get_setting('footer_logo')) }}" alt="{{ env('APP_NAME') }}"
                        height="45">
                    @else
                    <img class="lazyload h-45px" src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                        data-src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" height="45"
                        width="130">
                    @endif
                    <div class="mb-4 text-light text-justify">
                        {!! get_setting('about_us_description',null,App::getLocale()) !!}
                    </div>
                </a>
            </div>
            <!-- Quick links -->
            <div class="col-md-4 col-sm-6">
                <div class="text-center text-sm-left mt-4">
                    <h4 class="fs-14 text-secondary text-uppercase fw-700 mb-3">
                        {{ get_setting('widget_one',null,App::getLocale()) }}
                    </h4>
                    <ul class="list-unstyled">
                        @if ( get_setting('widget_one_labels',null,App::getLocale()) != null )
                        @foreach (json_decode( get_setting('widget_one_labels',null,App::getLocale()), true) as $key =>
                        $value)
                        @php
                        $widget_one_links = '';
                        if(isset(json_decode(get_setting('widget_one_links'), true)[$key])) {
                        $widget_one_links = json_decode(get_setting('widget_one_links'), true)[$key];
                        }
                        @endphp
                        <li class="mb-2">
                            <a href="{{ $widget_one_links }}" class="fs-13 text-soft-light animate-underline-primary">
                                {{ $value }}
                            </a>
                        </li>
                        @endforeach
                        @endif
                        @guest
                        <li class="mb-2">
                            <a class="fs-13 text-soft-light animate-underline-primary"
                                href="{{ route('seller.login') }}">
                                {{ translate('Login to Seller Panel') }}
                            </a>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>

            <!-- Contacts -->
            <div class="col-md-4 col-sm-6">
                <div class="text-center text-sm-left mt-4">
                    <h4 class="fs-14 text-secondary text-uppercase fw-700 mb-3">{{ translate('Contacts') }}</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <p class="fs-13 text-soft-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
                                    <path
                                        d="M256,48c-79.5,0-144,61.39-144,137,0,87,96,224.87,131.25,272.49a15.77,15.77,0,0,0,25.5,0C304,409.89,400,272.07,400,185,400,109.39,335.5,48,256,48Z"
                                        style="fill:none;stroke:#ffffff;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px" />
                                    <circle cx="256" cy="192" r="48"
                                        style="fill:none;stroke:#fff;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px" />
                                </svg>
                                {{ get_setting('contact_address',null,App::getLocale()) }}
                            </p>
                        </li>
                        <li class="mb-2">

                            <p class="fs-13 text-soft-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
                                    <path
                                        d="M451,374c-15.88-16-54.34-39.35-73-48.76C353.7,313,351.7,312,332.6,326.19c-12.74,9.47-21.21,17.93-36.12,14.75s-47.31-21.11-75.68-49.39-47.34-61.62-50.53-76.48,5.41-23.23,14.79-36c13.22-18,12.22-21,.92-45.3-8.81-18.9-32.84-57-48.9-72.8C119.9,44,119.9,47,108.83,51.6A160.15,160.15,0,0,0,83,65.37C67,76,58.12,84.83,51.91,98.1s-9,44.38,23.07,102.64,54.57,88.05,101.14,134.49S258.5,406.64,310.85,436c64.76,36.27,89.6,29.2,102.91,23s22.18-15,32.83-31a159.09,159.09,0,0,0,13.8-25.8C465,391.17,468,391.17,451,374Z"
                                        style="fill:none;stroke:#fff;stroke-miterlimit:10;stroke-width:32px" />
                                </svg>
                                {{ get_setting('contact_phone') }}
                            </p>
                        </li>
                        <li class="mb-2">
                            <p class="">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
                                    <rect x="48" y="96" width="416" height="320" rx="40" ry="40"
                                        style="fill:none;stroke:#ffffff;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px" />
                                    <polyline points="112 160 256 272 400 160"
                                        style="fill:none;stroke:#ffffff;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px" />
                                </svg>
                                <a href="mailto:{{ get_setting('contact_email') }}"
                                    class="fs-13 text-soft-light hov-text-primary">{{ get_setting('contact_email')
                                    }}</a>
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Accordion Fotter widgets -->
    <div class="d-lg-none bg-transparent">
        <!-- Quick links -->
        <div class="aiz-accordion-wrap bg-black">
            <div class="aiz-accordion-heading container bg-black">
                <button class="aiz-accordion fs-14 text-white bg-transparent " style="padding-top: 40px !important;">{{
                    get_setting('widget_one',null,App::getLocale()) }}</button>
            </div>
            <div class="aiz-accordion-panel bg-transparent" style="background-color: #303334 !important;">
                <div class="container">
                    <ul class="list-unstyled mt-3">
                        @if ( get_setting('widget_one_labels',null,App::getLocale()) != null )
                        @foreach (json_decode( get_setting('widget_one_labels',null,App::getLocale()), true) as $key =>
                        $value)
                        @php
                        $widget_one_links = '';
                        if(isset(json_decode(get_setting('widget_one_links'), true)[$key])) {
                        $widget_one_links = json_decode(get_setting('widget_one_links'), true)[$key];
                        }
                        @endphp
                        <li class="mb-2 pb-2 @if (url()->current() == $widget_one_links) active @endif">
                            <a href="{{ $widget_one_links }}"
                                class="fs-13 text-soft-light text-sm-secondary animate-underline-primary">
                                {{ $value }}
                            </a>
                        </li>
                        @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Contacts -->
        <div class="aiz-accordion-wrap bg-black">
            <div class="aiz-accordion-heading container bg-black">
                <button class="aiz-accordion fs-14 text-white bg-transparent">{{ translate('Contacts') }}</button>
            </div>
            <div class="aiz-accordion-panel bg-transparent" style="background-color: #303334 !important;">
                <div class="container">
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2">
                            <p class="fs-13 text-soft-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
                                    <path
                                        d="M256,48c-79.5,0-144,61.39-144,137,0,87,96,224.87,131.25,272.49a15.77,15.77,0,0,0,25.5,0C304,409.89,400,272.07,400,185,400,109.39,335.5,48,256,48Z"
                                        style="fill:none;stroke:#ffffff;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px" />
                                    <circle cx="256" cy="192" r="48"
                                        style="fill:none;stroke:#fff;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px" />
                                </svg>
                                {{ get_setting('contact_address',null,App::getLocale()) }}
                            </p>
                        </li>
                        <li class="mb-2">

                            <p class="fs-13 text-soft-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
                                    <path
                                        d="M451,374c-15.88-16-54.34-39.35-73-48.76C353.7,313,351.7,312,332.6,326.19c-12.74,9.47-21.21,17.93-36.12,14.75s-47.31-21.11-75.68-49.39-47.34-61.62-50.53-76.48,5.41-23.23,14.79-36c13.22-18,12.22-21,.92-45.3-8.81-18.9-32.84-57-48.9-72.8C119.9,44,119.9,47,108.83,51.6A160.15,160.15,0,0,0,83,65.37C67,76,58.12,84.83,51.91,98.1s-9,44.38,23.07,102.64,54.57,88.05,101.14,134.49S258.5,406.64,310.85,436c64.76,36.27,89.6,29.2,102.91,23s22.18-15,32.83-31a159.09,159.09,0,0,0,13.8-25.8C465,391.17,468,391.17,451,374Z"
                                        style="fill:none;stroke:#fff;stroke-miterlimit:10;stroke-width:32px" />
                                </svg>
                                {{ get_setting('contact_phone') }}
                            </p>
                        </li>
                        <li class="mb-2">
                            <p class="">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
                                    <rect x="48" y="96" width="416" height="320" rx="40" ry="40"
                                        style="fill:none;stroke:#ffffff;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px" />
                                    <polyline points="112 160 256 272 400 160"
                                        style="fill:none;stroke:#ffffff;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px" />
                                </svg>
                                <a href="mailto:{{ get_setting('contact_email') }}"
                                    class="fs-13 text-soft-light hov-text-primary">{{ get_setting('contact_email')
                                    }}</a>
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</section>
<!-- footer subscription & icons -->
<section class="py-3 text-light footer-widget border-bottom"
    style="border-color: #3d3d4600 !important; background-color: #2e3133 !important;">
    <div class="container">

        <div class="row justify-content-between">


            {{-- <div class="col d-none d-lg-block"></div> --}}
            <!-- Apps link -->
            @if((get_setting('play_store_link') != null) || (get_setting('app_store_link') != null))
            <div class="col-xxl-3 col-xl-4 col-lg-4 mt-1rem">
                <h5 class="fs-14 fw-700  text-uppercase mt-3 text-align-center">{{ translate('Mobile Apps') }}
                    <dotlottie-player src="{{static_asset('assets/lottie/download.lottie')}}" background="transparent"
                        speed="1" style="width: 30px; height: 30px" direction="1" playMode="normal" loop autoplay>
                    </dotlottie-player>
                </h5>
                <div class="d-flex mt-3 justify-content-center">
                    <div class="">
                        <a href="{{ get_setting('play_store_link') }}" target="_blank"
                            class="mr-2 mb-2 overflow-hidden hov-scale-img">
                            <img class="lazyload has-transition"
                                src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                data-src="{{ static_asset('assets/img/play.png') }}" alt="{{ env('APP_NAME') }}"
                                height="44" width="150">
                        </a>
                    </div>
                    <div class="">
                        <a href="{{ get_setting('app_store_link') }}" target="_blank"
                            class="overflow-hidden hov-scale-img">
                            <img class="lazyload has-transition"
                                src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                data-src="{{ static_asset('assets/img/app.png') }}" alt="{{ env('APP_NAME') }}"
                                height="44" width="150">
                        </a>
                    </div>
                </div>
            </div>
            @endif
            <!-- Follow & Apps -->
            <div class="col-xxl-3 col-xl-3 col-lg-3 mt-1rem">
                <!-- Social -->
                @if ( get_setting('show_social_links') )
                <h5 class="fs-14 fw-700 text-uppercase mt-3 text-align-md-center">{{ translate('Follow Us') }}</h5>
                <div class="d-flex justify-content-center">

                    <ul class="list-inline social mb-4">
                        @if (!empty(get_setting('facebook_link')))
                        <li class="list-inline-item ml-2 mr-2">
                            <a href="{{ get_setting('facebook_link') }}" target="_blank" class="facebook">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 24 24"
                                    style="enable-background:new 0 0 24 24;" xml:space="preserve" width="24"
                                    height="24">
                                    <g>
                                        <path
                                            d="M24,12.073c0,5.989-4.394,10.954-10.13,11.855v-8.363h2.789l0.531-3.46H13.87V9.86c0-0.947,0.464-1.869,1.95-1.869h1.509   V5.045c0,0-1.37-0.234-2.679-0.234c-2.734,0-4.52,1.657-4.52,4.656v2.637H7.091v3.46h3.039v8.363C4.395,23.025,0,18.061,0,12.073   c0-6.627,5.373-12,12-12S24,5.445,24,12.073z" />
                                    </g>
                                </svg>
                            </a>
                        </li>
                        @endif
                        @if (!empty(get_setting('twitter_link')))
                        <li class="list-inline-item ml-2 mr-2">
                            <a href="{{ get_setting('twitter_link') }}" target="_blank" class="twitter">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24">
                                    <!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                    <path
                                        d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm297.1 84L257.3 234.6 379.4 396H283.8L209 298.1 123.3 396H75.8l111-126.9L69.7 116h98l67.7 89.5L313.6 116h47.5zM323.3 367.6L153.4 142.9H125.1L296.9 367.6h26.3z" />
                                </svg>
                            </a>
                        </li>
                        @endif
                        @if (!empty(get_setting('instagram_link')))
                        <li class="list-inline-item ml-2 mr-2">
                            <a href="{{ get_setting('instagram_link') }}" target="_blank" class="instagram">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24">
                                    <!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                    <path
                                        d="M194.4 211.7a53.3 53.3 0 1 0 59.3 88.7 53.3 53.3 0 1 0 -59.3-88.7zm142.3-68.4c-5.2-5.2-11.5-9.3-18.4-12c-18.1-7.1-57.6-6.8-83.1-6.5c-4.1 0-7.9 .1-11.2 .1c-3.3 0-7.2 0-11.4-.1c-25.5-.3-64.8-.7-82.9 6.5c-6.9 2.7-13.1 6.8-18.4 12s-9.3 11.5-12 18.4c-7.1 18.1-6.7 57.7-6.5 83.2c0 4.1 .1 7.9 .1 11.1s0 7-.1 11.1c-.2 25.5-.6 65.1 6.5 83.2c2.7 6.9 6.8 13.1 12 18.4s11.5 9.3 18.4 12c18.1 7.1 57.6 6.8 83.1 6.5c4.1 0 7.9-.1 11.2-.1c3.3 0 7.2 0 11.4 .1c25.5 .3 64.8 .7 82.9-6.5c6.9-2.7 13.1-6.8 18.4-12s9.3-11.5 12-18.4c7.2-18 6.8-57.4 6.5-83c0-4.2-.1-8.1-.1-11.4s0-7.1 .1-11.4c.3-25.5 .7-64.9-6.5-83l0 0c-2.7-6.9-6.8-13.1-12-18.4zm-67.1 44.5A82 82 0 1 1 178.4 324.2a82 82 0 1 1 91.1-136.4zm29.2-1.3c-3.1-2.1-5.6-5.1-7.1-8.6s-1.8-7.3-1.1-11.1s2.6-7.1 5.2-9.8s6.1-4.5 9.8-5.2s7.6-.4 11.1 1.1s6.5 3.9 8.6 7s3.2 6.8 3.2 10.6c0 2.5-.5 5-1.4 7.3s-2.4 4.4-4.1 6.2s-3.9 3.2-6.2 4.2s-4.8 1.5-7.3 1.5l0 0c-3.8 0-7.5-1.1-10.6-3.2zM448 96c0-35.3-28.7-64-64-64H64C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96zM357 389c-18.7 18.7-41.4 24.6-67 25.9c-26.4 1.5-105.6 1.5-132 0c-25.6-1.3-48.3-7.2-67-25.9s-24.6-41.4-25.8-67c-1.5-26.4-1.5-105.6 0-132c1.3-25.6 7.1-48.3 25.8-67s41.5-24.6 67-25.8c26.4-1.5 105.6-1.5 132 0c25.6 1.3 48.3 7.1 67 25.8s24.6 41.4 25.8 67c1.5 26.3 1.5 105.4 0 131.9c-1.3 25.6-7.1 48.3-25.8 67z" />
                                </svg>
                            </a>
                        </li>
                        @endif
                        @if (!empty(get_setting('youtube_link')))
                        <li class="list-inline-item ml-2 mr-2">
                            <a href="{{ get_setting('youtube_link') }}" target="_blank" class="youtube">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="24" height="24">
                                    <!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                    <path
                                        d="M549.7 124.1c-6.3-23.7-24.8-42.3-48.3-48.6C458.8 64 288 64 288 64S117.2 64 74.6 75.5c-23.5 6.3-42 24.9-48.3 48.6-11.4 42.9-11.4 132.3-11.4 132.3s0 89.4 11.4 132.3c6.3 23.7 24.8 41.5 48.3 47.8C117.2 448 288 448 288 448s170.8 0 213.4-11.5c23.5-6.3 42-24.2 48.3-47.8 11.4-42.9 11.4-132.3 11.4-132.3s0-89.4-11.4-132.3zm-317.5 213.5V175.2l142.7 81.2-142.7 81.2z" />
                                </svg>
                            </a>
                        </li>
                        @endif
                        @if (!empty(get_setting('linkedin_link')))
                        <li class="list-inline-item ml-2 mr-2">
                            <a href="{{ get_setting('linkedin_link') }}" target="_blank" class="linkedin"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24">
                                    <!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                    <path
                                        d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z" />
                                </svg></a>
                        </li>
                        @endif
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="pt-3 pb-7 pb-xl-3 text-soft-light" style="background:#232526">
    <div class="container">
        <div class="row align-items-center py-3">
            <!-- Copyright -->
            <div class="col-lg-6 order-1 order-lg-0">
                <div class="text-center text-lg-left fs-14">
                    {!! get_setting('frontend_copyright_text', null, App::getLocale()) !!} {{date('Y')}}
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex justify-content-center align-items-center">
                    <div class="text-center  fs-14">
                        صنع ب💙 بواسطة
                    </div>
                    <a href="http://www.loop-pr.com/" target="_blank">
                        <img src="{{static_asset('assets/img/loop.svg')}}" class="loop-credit">
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

