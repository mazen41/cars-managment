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

    @yield('meta')

    @if (!isset($page) && !isset($blog))
        @php
            $meta_image = uploaded_asset(get_setting('meta_image'));
        @endphp
        <!-- Schema.org markup for Google+ -->
        <meta itemprop="name" content="{{ get_setting('meta_title') }}">
        <meta itemprop="description" content="{{ get_setting('meta_description') }}">
        <meta itemprop="image" content="{{ $meta_image }}">

        <!-- Twitter Card data -->
        <meta name="twitter:card" content="product">
        <meta name="twitter:site" content="@publisher_handle">
        <meta name="twitter:title" content="{{ get_setting('meta_title') }}">
        <meta name="twitter:description" content="{{ get_setting('meta_description') }}">
        <meta name="twitter:creator" content="@author_handle">
        <meta name="twitter:image" content="{{ $meta_image }}">

        <!-- Open Graph data -->
        <meta property="og:title" content="{{ get_setting('meta_title') }}" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ route('home') }}" />
        <meta property="og:image" content="{{ $meta_image }}" />
        <meta property="og:description" content="{{ get_setting('meta_description') }}" />
        <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
        <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">
    @endif

    <!-- Favicon -->
    @php
        $site_icon = uploaded_asset(get_setting('site_icon'));
    @endphp
    <link rel="icon" href="{{ $site_icon }}">
    <link rel="apple-touch-icon" href="{{ $site_icon }}">

    @vite('public/assets/css/third-party.css')
    @if ($rtl == 1)
    @vite('public/assets/css/bootstrap-rtl.min.css')
    @endif
    @vite('public/assets/css/web-styles.css')
        <link rel="stylesheet" href="{{ static_asset('assets/css/custom-style.css') }}">
        <link rel="stylesheet" href="{{ static_asset('assets/css/fonts.css') }}">

	<script src="{{ static_asset('assets/js/swiper.min.js') }}"></script>
    <script>
        var AIZ = AIZ || {};
        AIZ.local = {
            nothing_selected: '{!! translate('Nothing selected', null, true) !!}',
            nothing_found: '{!! translate('Nothing found', null, true) !!}',
            choose_file: '{{ translate('Choose file') }}',
            file_selected: '{{ translate('File selected') }}',
            files_selected: '{{ translate('Files selected') }}',
            add_more_files: '{{ translate('Add more files') }}',
            adding_more_files: '{{ translate('Adding more files') }}',
            drop_files_here_paste_or: '{{ translate('Drop files here, paste or') }}',
            browse: '{{ translate('Browse') }}',
            upload_complete: '{{ translate('Upload complete') }}',
            upload_paused: '{{ translate('Upload paused') }}',
            resume_upload: '{{ translate('Resume upload') }}',
            pause_upload: '{{ translate('Pause upload') }}',
            retry_upload: '{{ translate('Retry upload') }}',
            cancel_upload: '{{ translate('Cancel upload') }}',
            uploading: '{{ translate('Uploading') }}',
            processing: '{{ translate('Processing') }}',
            complete: '{{ translate('Complete') }}',
            file: '{{ translate('File') }}',
            files: '{{ translate('Files') }}',
        }
    </script>

    <style>
        :root{
            --blue: #3490f3;
            --hov-blue: #2e7fd6;
            --soft-blue: rgba(0, 123, 255, 0.15);
            --secondary-base: {{ get_setting('secondary_base_color', '#ffc519') }};
            --hov-secondary-base: {{ get_setting('secondary_base_hov_color', '#dbaa17') }};
            --soft-secondary-base: {{ hex2rgba(get_setting('secondary_base_color', '#ffc519'), 0.15) }};
            --gray: #9d9da6;
            --gray-dark: #8d8d8d;
            --secondary: #919199;
            --soft-secondary: rgba(145, 145, 153, 0.15);
            --success: #85b567;
            --soft-success: rgba(133, 181, 103, 0.15);
            --warning: #f3af3d;
            --soft-warning: rgba(243, 175, 61, 0.15);
            --light: #f5f5f5;
            --soft-light: #dfdfe6;
            --soft-white: #b5b5bf;
            --dark: #292933;
            --soft-dark: #1b1b28;
            --primary: {{ get_setting('base_color', '#d43533') }};
            --hov-primary: {{ get_setting('base_hov_color', '#9d1b1a') }};
            --soft-primary: {{ hex2rgba(get_setting('base_color', '#d43533'), 0.15) }};
        }
        body{
            font-family: 'Public Sans', sans-serif;
            font-weight: 400;
        }

        .pagination .page-link,
        .page-item.disabled .page-link {
            min-width: 32px;
            min-height: 32px;
            line-height: 32px;
            text-align: center;
            padding: 0;
            border: 1px solid var(--soft-light);
            font-size: 0.875rem;
            border-radius: 0 !important;
            color: var(--dark);
        }
        .pagination .page-item {
            margin: 0 5px;
        }

        .aiz-carousel.coupon-slider .slick-track{
            margin-left: 0;
        }

        .form-control:focus {
            border-width: 2px !important;
        }
        .iti__flag-container {
            padding: 2px;
        }
        .modal-content {
            border: 0 !important;
            border-radius: 0 !important;
        }

        .tagify.tagify--focus{
            border-width: 2px;
            border-color: var(--primary);
        }

        #map{
            width: 100%;
            height: 250px;
        }
        #edit_map{
            width: 100%;
            height: 250px;
        }

        .pac-container { z-index: 100000; }
    </style>

@if (get_setting('google_analytics') == 1)
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('TRACKING_ID') }}"></script>

    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ env('TRACKING_ID') }}');
    </script>
@endif

@if (get_setting('facebook_pixel') == 1)
    <!-- Facebook Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ env('FACEBOOK_PIXEL_ID') }}');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ env('FACEBOOK_PIXEL_ID') }}&ev=PageView&noscript=1"/>
    </noscript>
    <!-- End Facebook Pixel Code -->
@endif

@php
    echo get_setting('header_script');
@endphp

</head>
<body>
    <!-- aiz-main-wrapper -->
    <div class="aiz-main-wrapper d-flex flex-column bg-surface">
        @php
            $user = auth()->user();
            $user_avatar = null;
            $carts = [];
            if ($user && $user->avatar_original != null) {
                $user_avatar = uploaded_asset($user->avatar_original);
            }

            $system_language = get_system_language();
        @endphp
        <!-- Header -->
        @include('frontend.inc.nav')

        @yield('content')

        <!-- footer -->
        {{-- @include('frontend.inc.footer') --}}

    </div>

    <!-- Floating Buttons -->
    {{-- @include('frontend.inc.floating_buttons') --}}

    <div class="aiz-refresh">
        <div class="aiz-refresh-content"><div></div><div></div><div></div></div>
    </div>

    <!-- cookies agreement -->
    {{-- @if (get_setting('show_cookies_agreement') == 'on') --}}
        @php
            $alert_location = get_setting('custom_alert_location');
            $order = in_array($alert_location, ['top-left', 'top-right']) ? 'asc' : 'desc';
            $custom_alerts = App\Models\CustomAlert::where('status', 1)->orderBy('id', $order)->get();
        @endphp

        <div class="aiz-custom-alert {{ get_setting('custom_alert_location') }}">
            @foreach ($custom_alerts as $custom_alert)
                @if($custom_alert->id == 1)
                    <div class="aiz-cookie-alert mb-3" style="box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.24);">
                        <div class="p-3 px-lg-2rem rounded-2" style="background: {{ $custom_alert->background_color }};">
                            <div class="text-{{ $custom_alert->text_color }} mb-3">
                                {!! $custom_alert->description !!}
                            </div>
                            <button class="btn btn-block btn-primary rounded-2 aiz-cookie-accept">
                                {{ translate('Ok. I Understood') }}
                            </button>
                        </div>
                    </div>
                @else
                    <div class="mb-3 custom-alert-box removable-session d-none" data-key="custom-alert-box-{{ $custom_alert->id }}" data-value="removed" style="box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.24);">
                        <div class="rounded-0 position-relative" style="background: {{ $custom_alert->background_color }};">
                            <a href="{{ $custom_alert->link }}" class="d-block h-100 w-100">
                                <div class="@if ($custom_alert->type == 'small') d-flex @endif">
                                    <img class="@if ($custom_alert->type == 'small') h-140px w-120px img-fit @else w-100 @endif" src="{{ uploaded_asset($custom_alert->banner) }}" alt="custom_alert">
                                    <div class="text-{{ $custom_alert->text_color }} p-2rem">
                                        {!! $custom_alert->description !!}
                                    </div>
                                </div>
                            </a>
                            <button class="absolute-top-right bg-transparent btn btn-circle btn-icon d-flex align-items-center justify-content-center text-{{ $custom_alert->text_color }} hov-text-primary set-session" data-key="custom-alert-box-{{ $custom_alert->id }}" data-value="removed" data-toggle="remove-parent" data-parent=".custom-alert-box">
                                <i class="la la-close fs-20"></i>
                            </button>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    {{-- @endif --}}

    <!-- website popup -->
    {{-- @if (get_setting('show_website_popup') == 'on') --}}
        @php
            $dynamic_popups = App\Models\DynamicPopup::where('status', 1)->orderBy('id', 'desc')->get();
        @endphp
        @foreach ($dynamic_popups as $key => $dynamic_popup)
            @if($dynamic_popup->id == 1)
                <div class="modal website-popup removable-session d-none" data-key="website-popup" data-value="removed">
                    <div class="absolute-full bg-black opacity-60"></div>
                    <div class="modal-dialog modal-dialog-centered modal-dialog-zoom modal-md mx-4 mx-md-auto">
                        <div class="modal-content position-relative border-0 rounded-0">
                            <div class="aiz-editor-data">
                                <div class="d-block">
                                    <img class="w-100" src="{{ uploaded_asset($dynamic_popup->banner) }}" alt="dynamic_popup">
                                </div>
                            </div>
                            <div class="pb-5 pt-4 px-3 px-md-2rem">
                                <h1 class="fs-30 fw-700 ">{{ $dynamic_popup->title }}</h1>
                                <p class="fs-14 fw-400 mt-3 mb-4">{{ $dynamic_popup->summary }}</p>
                                @if ($dynamic_popup->show_subscribe_form == 'on')
                                    <form class="" method="POST" action="{{ route('subscribers.store') }}">
                                        @csrf
                                        <div class="form-group mb-0">
                                            <input type="email" class="form-control" placeholder="{{ translate('Your Email Address') }}" name="email" required>
                                        </div>
                                        <button type="submit" class="btn btn-block mt-3 rounded-0 text-{{ $dynamic_popup->btn_text_color }}" style="background: {{ $dynamic_popup->btn_background_color }};">
                                            {{ $dynamic_popup->btn_text }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <button class="absolute-top-right bg-surface shadow-lg btn btn-circle btn-icon mr-n3 mt-n3 set-session" data-key="website-popup" data-value="removed" data-toggle="remove-parent" data-parent=".website-popup">
                                <i class="la la-close fs-20"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="modal website-popup removable-session d-none" data-key="website-popup-{{ $dynamic_popup->id }}" data-value="removed">
                    <div class="absolute-full bg-black opacity-60"></div>
                    <div class="modal-dialog modal-dialog-centered modal-dialog-zoom modal-md mx-4 mx-md-auto">
                        <div class="modal-content position-relative border-0 rounded-0">
                            <div class="aiz-editor-data">
                                <div class="d-block">
                                    <img class="w-100" src="{{ uploaded_asset($dynamic_popup->banner) }}" alt="dynamic_popup">
                                </div>
                            </div>
                            <div class="pb-5 pt-4 px-3 px-md-2rem">
                                <h1 class="fs-30 fw-700 ">{{ $dynamic_popup->title }}</h1>
                                <p class="fs-14 fw-400 mt-3 mb-4">{{ $dynamic_popup->summary }}</p>
                                <a href="{{ $dynamic_popup->btn_link }}" class="btn btn-block mt-3 rounded-0 text-{{ $dynamic_popup->btn_text_color }}" style="background: {{ $dynamic_popup->btn_background_color }};">
                                    {{ $dynamic_popup->btn_text }}
                                </a>
                            </div>
                            <button class="absolute-top-right bg-surface shadow-lg btn btn-circle btn-icon mr-n3 mt-n3 set-session" data-key="website-popup-{{ $dynamic_popup->id }}" data-value="removed" data-toggle="remove-parent" data-parent=".website-popup">
                                <i class="la la-close fs-20"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    {{-- @endif --}}



    <div class="modal fade" id="addToCart">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="c-preloader text-center p-3">
                    <dotlottie-player src="{{static_asset('assets/lottie/loading.lottie')}}" background="transparent" speed="1" style="width: 200px; height: 200px" direction="1" playMode="normal" loop autoplay></dotlottie-player>
                </div>
                <button type="button" class="close-icon close absolute-top-right btn-icon close z-1 btn-circle bg-gray mr-2 mt-2 d-flex justify-content-center align-items-center" data-dismiss="modal" aria-label="Close" style="background: #ededf2; width: calc(2rem + 2px); height: calc(2rem + 2px); display:inline-block !important">
                    {{-- <span aria-hidden="true" class="fs-24 fw-700" style="margin-left: 2px;">&times;</span> --}}
                </button>
                <div id="addToCart-modal-body">

                </div>
            </div>
        </div>
    </div>

    @yield('modal')

    <!-- SCRIPTS -->

    <script src="{{ static_asset('assets/js/vendors.js') }}"></script>
    <script src="{{ static_asset('assets/js/aiz-core.js?v=1.78') }}"></script>
    <script src="{{static_asset('assets/js/dotlottie-player.js?v=2.3')}}" defer></script>



    @if (get_setting('facebook_chat') == 1)
        <script type="text/javascript">
            window.fbAsyncInit = function() {
                FB.init({
                  xfbml            : true,
                  version          : 'v3.3'
                });
              };

              (function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
        <div id="fb-root"></div>
        <!-- Your customer chat code -->
        <div class="fb-customerchat"
          attribution=setup_tool
          page_id="{{ env('FACEBOOK_PAGE_ID') }}">
        </div>
    @endif

    <script>
        @foreach (session('flash_notification', collect())->toArray() as $message)
            AIZ.plugins.notify('{{ $message['level'] }}', '{{ $message['message'] }}');
        @endforeach
    </script>

    <script>
        var acc = document.getElementsByClassName("aiz-accordion-heading");
        var i;
        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.maxHeight) {
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = panel.scrollHeight + "px";
                }
            });
        }
    </script>

    @yield('script')
    @php
        echo get_setting('footer_script');
    @endphp

</body>
</html>
