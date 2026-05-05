<div class="aiz-topbar px-15px px-lg-25px d-flex align-items-stretch justify-content-between">
    <div class="d-flex">
        <div class="aiz-topbar-nav-toggler d-flex align-items-center justify-content-start mr-2 mr-md-3 ml-0"
            data-toggle="aiz-mobile-nav">
            <button class="aiz-mobile-toggler">
                <span></span>
            </button>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-stretch flex-grow-xl-1">
        <div class="d-flex justify-content-around align-items-center align-items-stretch">
            <div class="d-flex justify-content-around align-items-center align-items-stretch">
                <div class="aiz-topbar-item">
                    <div class="d-flex align-items-center">
                        <a class="btn btn-icon btn-circle btn-light" href="{{ route('home')}}" target="_blank"
                            title="{{ translate('Browse Website') }}">
                            <i class="las la-globe"></i>
                        </a>
                    </div>
                </div>
            </div>
            @if (addon_is_activated('pos_system'))
            <div class="d-flex justify-content-around align-items-center align-items-stretch ml-3">
                <div class="aiz-topbar-item">
                    <div class="d-flex align-items-center">
                        <a class="btn btn-icon btn-circle btn-light" href="{{ route('poin-of-sales.seller_index') }}"
                            target="_blank" title="{{ translate('POS') }}">
                            <i class="las la-print"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="d-flex justify-content-around align-items-center align-items-stretch">
            <!-- impersonation -->
            @impersonating
            <a class="btn btn-danger btn-sm d-flex align-items-center rounded-2 hov-svg-white my-2 mr-3 pd-2"
                href="{{route('seller.impersonate-leave')}}">
                <span class="ml-2">{{translate('Leave')}}</span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M8.90039 7.56023C9.21039 3.96023 11.0604 2.49023 15.1104 2.49023H15.2404C19.7104 2.49023 21.5004 4.28023 21.5004 8.75023V15.2702C21.5004 19.7402 19.7104 21.5302 15.2404 21.5302H15.1104C11.0904 21.5302 9.24039 20.0802 8.91039 16.5402"
                            stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <g opacity="0.4">
                            <path d="M14.9991 12H3.61914" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M5.85 8.65039L2.5 12.0004L5.85 15.3504" stroke="#FFFFFF" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                    </svg>
                </span>
            </a>
            @endImpersonating
            <!-- Notifications -->
            <div class="aiz-topbar-item mr-3">
                <div class="align-items-stretch d-flex dropdown">
                    <a class="dropdown-toggle no-arrow" data-toggle="dropdown" href="javascript:void(0);" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <span class="btn btn-icon p-0 d-flex justify-content-center align-items-center">
                            <span class="d-flex align-items-center position-relative">
                                <i class="las la-bell fs-24"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                <span
                                    class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                                @endif
                            </span>
                        </span>
                    </a>
                    @php
                    $unread_order_notifications = auth()->user()->unreadNotifications()->where('type',
                    'App\Notifications\OrderNotification')->take(20)->get();
                    $unread_order_notifications_count = auth()->user()->unreadNotifications()->where('type',
                    'App\Notifications\OrderNotification')->count();
                    $unread_product_notifications = auth()->user()->unreadNotifications()->where('type', 'like',
                    '%shop%')->take(20)->get();
                    $unread_product_notifications_count = auth()->user()->unreadNotifications()->where('type', 'like',
                    '%shop%')->count();
                    $unread_payout_notifications = auth()->user()->unreadNotifications()->where('type',
                    'App\Notifications\PayoutNotification')->take(20)->get();
                    $unread_payout_notifications_count = auth()->user()->unreadNotifications()->where('type',
                    'App\Notifications\PayoutNotification')->count();
                    $unread_conversation_notifications = auth()->user()->unreadNotifications()->where('type',
                    'App\Notifications\ConversationNotification')->take(20)->get();
                    $unread_conversation_notifications_count = auth()->user()->unreadNotifications()->where('type',
                    'App\Notifications\ConversationNotification')->count();
                    $unread_custom_notifications = auth()->user()->unreadNotifications()->where('type',
                    'App\Notifications\CustomNotification')->take(20)->get();
                    $unread_custom_notifications_count = auth()->user()->unreadNotifications()->where('type',
                    'App\Notifications\CustomNotification')->count();
                    @endphp
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-xl py-0">
                        <div class="notifications">
                            <ul class="nav nav-tabs nav-justified" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link position-relative  active" data-toggle="tab" data-type="order"
                                        href="javascript:void(0);" data-target="#orders-notifications" role="tab"
                                        id="orders-tab">{{ translate('Orders') }}
                                        @if( $unread_order_notifications_count > 0)
                                        <span
                                            class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link position-relative " data-toggle="tab" data-type="seller"
                                        href="javascript:void(0);" data-target="#sellers-notifications" role="tab"
                                        id="sellers-tab">{{ translate('Products') }}
                                        @if( $unread_product_notifications_count > 0)
                                        <span
                                            class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link position-relative " data-toggle="tab" data-type="seller"
                                        href="javascript:void(0);" data-target="#payouts-notifications" role="tab"
                                        id="sellers-tab">{{ translate('Payouts') }}
                                        @if( $unread_payout_notifications_count > 0)
                                        <span
                                            class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link position-relative " data-toggle="tab" data-type="seller"
                                        href="javascript:void(0);" data-target="#conversation-notifications" role="tab"
                                        id="sellers-tab">{{ translate('Conversations') }}
                                        @if( $unread_conversation_notifications_count > 0)
                                        <span
                                            class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link position-relative " data-toggle="tab" data-type="seller"
                                        href="javascript:void(0);" data-target="#other-notifications" role="tab"
                                        id="sellers-tab">{{ translate('Other') }}
                                        @if( $unread_custom_notifications_count > 0)
                                        <span
                                            class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                                        @endif

                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content c-scrollbar-light overflow-auto"
                                style="height: 75vh; max-height: 400px; overflow-y: auto;">
                                <div class="tab-pane active" id="orders-notifications" role="tabpanel">
                                    <x-unread_notification :notifications="$unread_order_notifications" />
                                </div>
                                <div class="tab-pane" id="sellers-notifications" role="tabpanel">
                                    <x-unread_notification :notifications="$unread_product_notifications" />
                                </div>
                                <div class="tab-pane" id="payouts-notifications" role="tabpanel">
                                    <x-unread_notification :notifications="$unread_payout_notifications" />
                                </div>
                                <div class="tab-pane" id="conversation-notifications" role="tabpanel">
                                    <x-unread_notification :notifications="$unread_conversation_notifications" />
                                </div>
                                <div class="tab-pane" id="other-notifications" role="tabpanel">
                                    <x-unread_notification :notifications="$unread_custom_notifications" />
                                </div>
                            </div>
                        </div>

                        <div class="text-center border-top">
                            <a href="{{ route('seller.all-notification') }}" class="text-reset d-block py-2">
                                {{ translate('View All Notifications') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- language --}}
            @php
            if(Session::has('locale')){
            $locale = Session::get('locale', Config::get('app.locale'));
            }
            else{
            $locale = config('app.locale');
            }
            @endphp
            <div class="aiz-topbar-item ml-2">
                <div class="align-items-stretch d-flex dropdown " id="lang-change">
                    <a class="dropdown-toggle no-arrow" data-toggle="dropdown" href="javascript:void(0);" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <span class="btn btn-icon">
                            <img src="{{ static_asset('assets/img/flags/'.$locale.'.png') }}" height="11">
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-xs">

                        @foreach (\App\Models\Language::where('status', 1)->get() as $key => $language)
                        <li>
                            <a href="javascript:void(0)" data-flag="{{ $language->code }}"
                                class="dropdown-item @if($locale == $language->code) active @endif">
                                <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" class="mr-2">
                                <span class="language">{{ $language->name }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="aiz-topbar-item ml-2">
                <div class="align-items-stretch d-flex dropdown">
                    <a class="dropdown-toggle no-arrow " data-toggle="dropdown" href="javascript:void(0);"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <span class="avatar avatar-sm mr-md-2">
                                <img src="{{ uploaded_asset(Auth::user()->avatar_original) }}"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                            </span>
                            <span class="d-none d-md-block">
                                <span class="d-block fw-500">{{Auth::user()->name}}</span>
                                <span class="d-block small opacity-60">{{Auth::user()->user_type}}</span>
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-md">
                        <a href="{{ route('seller.profile.index') }}" class="dropdown-item">
                            <i class="las la-user-circle"></i>
                            <span>{{translate('Profile')}}</span>
                        </a>

                        <a href="{{ route('logout')}}" class="dropdown-item">
                            <i class="las la-sign-out-alt"></i>
                            <span>{{translate('Logout')}}</span>
                        </a>
                    </div>
                </div>
            </div><!-- .aiz-topbar-item -->
        </div>
    </div>
</div><!-- .aiz-topbar -->
