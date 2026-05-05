<div class="aiz-topbar px-15px px-lg-25px d-flex align-items-stretch justify-content-between">
    <div class="d-flex">
        <!-- Mobile toggler -->
        <div class="aiz-topbar-nav-toggler d-flex align-items-center justify-content-start ml-0 mr-2" data-toggle="aiz-mobile-nav">
            <a class="btn btn-topbar has-transition btn-icon p-0 d-flex align-items-center" href="javascript:void(0)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                    <g id="Group_28009" data-name="Group 28009" transform="translate(0 16) rotate(-90)">
                      <rect id="Rectangle_18283" data-name="Rectangle 18283" width="2" height="7" rx="1" fill="#9da3ae"/>
                      <rect id="Rectangle_16236" data-name="Rectangle 16236" width="2" height="11" rx="1" transform="translate(14)" fill="#9da3ae"/>
                      <rect id="Rectangle_18284" data-name="Rectangle 18284" width="2" height="16" rx="1" transform="translate(7)" fill="#9da3ae"/>
                    </g>
                </svg>
            </a>
        </div>

    </div>
    <div class="d-flex justify-content-between align-items-stretch flex-grow-1">
        <div class="d-flex justify-content-around align-items-center align-items-stretch">
            <!-- Topbar menus -->
            <div class="aiz-topbar-item mr-2 d-none d-xl-block">
                <div class="d-flex align-items-center h-100">
                    <a class="aiz-topbar-menu fs-13 fw-600 d-flex align-items-center justify-content-center {{ areActiveRoutes(['admin.dashboard']) }}"
                        href="{{ route('admin.dashboard') }}">{{ translate('Dashboard') }}</a>
                    @can('view_all_orders')
                        <a class="aiz-topbar-menu fs-13 fw-600 d-flex align-items-center justify-content-center {{ areActiveRoutes(['all_orders.index']) }}"
                            href="{{ route('all_orders.index') }}">{{ translate('Orders') }}</a>
                    @endcan
                    @can('earning_report')
                        <a class="aiz-topbar-menu fs-13 fw-600 d-flex align-items-center justify-content-center {{ areActiveRoutes(['earning_payout_report.index']) }}"
                            href="{{ route('earning_payout_report.index') }}">{{ translate('Earnings') }}</a>
                    @endcan
                </div>
            </div>
            <!-- Add New Button -->
            @canany(['add_new_product', 'add_brand', 'add_product_category'])
            <div class="d-flex justify-content-around align-items-center align-items-stretch">
                <div class="aiz-topbar-item d-none d-sm-block">
                    <div class="d-flex align-items-center h-100 dropdown">
                        <a class="dropdown-toggle no-arrow h-100" data-toggle="dropdown" href="javascript:void(0);"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="btn btn-soft-blue btn-sm d-flex align-items-center rounded-2 hov-svg-white">
                                <span class="fw-500 mx-2 mr-0 d-none d-md-block">{{ translate('Add New') }}</span>
                                <i class="las fs-18 la-plus"></i>
                            </span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-left dropdown-menu-animated dropdown-menu-md" style="top: 15px !important;">
                            @can('add_new_product')
                                <a href="{{ route('products.create') }}" class="dropdown-item">
                                    <i class="las la-plus"></i>
                                    <span>{{ translate('New Product') }}</span>
                                </a>
                            @endcan
                            @can('add_brand')
                                <a href="{{ route('categories.create') }}" class="dropdown-item">
                                    <i class="las la-plus"></i>
                                    <span>{{ translate('New Category') }}</span>
                                </a>
                            @endcan
                            @can('add_product_category')
                                <a href="{{ route('brands.index') }}" class="dropdown-item">
                                    <i class="las la-plus"></i>
                                    <span>{{ translate('New Brand') }}</span>
                                </a>
                            @endcan
                        </div>

                    </div>
                </div>
            </div>
            @endcanany
        </div>
        <div class="d-flex justify-content-around align-items-center align-items-stretch">
             @if (addon_is_activated('pos_system') && auth()->user()->can('pos_manager'))
            <!-- POS -->
            <div class="aiz-topbar-item mr-3">
                <div class="d-flex align-items-center">
                    <a class="btn btn-topbar has-transition btn-icon btn-circle btn-soft-primary p-0 hov-bg-primary hov-svg-white d-flex align-items-center justify-content-center"
                        href="{{ route('poin-of-sales.index') }}" target="_blank" data-toggle="tooltip" data-title="{{ translate('POS') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13.79" height="16" viewBox="0 0 13.79 16">
                            <g id="_371925cdd3f531725a9fa8f3ebf8fe9e" data-name="371925cdd3f531725a9fa8f3ebf8fe9e" transform="translate(-2.26 0)">
                            <path id="Path_40673" data-name="Path 40673" d="M10.69,7H3.26a1.025,1.025,0,0,0-1,1V18.45a1.03,1.03,0,0,0,1,1.05h7.43a1.03,1.03,0,0,0,1.03-1.03V8A1.025,1.025,0,0,0,10.69,7ZM4.94,17.86H3.995v-.95H4.94Zm0-2.355H3.995v-.95H4.94Zm0-2.355H3.995V12.2H4.94Zm2.5,4.71H6.5v-.95h.955Zm0-2.355H6.5v-.95h.955Zm0-2.355H6.5V12.2h.955Zm2.5,4.71H8.99v-.95h.95Zm0-2.355H8.99v-.95h.95Zm0-2.355H8.99V12.2h.95Zm.325-3a.17.17,0,0,1-.165.17H3.835a.17.17,0,0,1-.165-.17V8.795a.165.165,0,0,1,.165-.165H10.13a.165.165,0,0,1,.165.165Zm5.09-1.45H15.13v9.09h.25a.67.67,0,0,0,.67-.67V9.375a.67.67,0,0,0-.695-.675Z" transform="translate(0 -3.5)" fill="#717580"/>
                            <rect id="Rectangle_20842" data-name="Rectangle 20842" width="1.465" height="9.095" transform="translate(12.185 5.2)" fill="#717580"/>
                            <rect id="Rectangle_20843" data-name="Rectangle 20843" width="0.63" height="9.095" transform="translate(14.06 5.2)" fill="#717580"/>
                            <path id="Path_40674" data-name="Path 40674" d="M13.895.895a.89.89,0,0,0-.26-.635A.91.91,0,0,0,13,0a.895.895,0,0,0-.91.895v.53h1.79Zm-2.2,0a.76.76,0,0,1,0-.145.68.68,0,0,1,0-.1h.01A.5.5,0,0,1,11.755.5.43.43,0,0,1,11.79.4a1.2,1.2,0,0,1,.145-.26.5.5,0,0,1,.04-.055L12.045,0H7.995A.815.815,0,0,0,7.18.81V3.03h4.5Z" transform="translate(-2.46)" fill="#717580"/>
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
            @endif
            <!-- Clear Cache -->
            @hasrole(['Tech Support', 'Super Admin'])
            <div class="aiz-topbar-item mr-3">
                <div class="d-flex align-items-center">
                    <a class="btn btn-topbar has-transition btn-icon btn-circle btn-soft-primary p-0 hov-bg-primary hov-svg-white d-flex align-items-center justify-content-center"
                        href="{{ route('cache.clear') }}" data-toggle="tooltip" data-title="{{ translate('Clear Cache') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                            <path id="_74846e5be5db5b666d3893933be03656" data-name="74846e5be5db5b666d3893933be03656" d="M7.719,8.911H8.9V10.1H7.719v1.185H6.539V10.1H5.36V8.911h1.18V7.726h1.18ZM5.36,13.652h1.18v1.185H5.36v1.185H4.18V14.837H3V13.652H4.18V12.467H5.36Zm13.626-2.763H10.138V10.3a1.182,1.182,0,0,1,1.18-1.185h2.36V2h1.77V9.111h2.36a1.182,1.182,0,0,1,1.18,1.185ZM18.4,18H16.044a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,14.755,18H12.5a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,11.216,18H8.958a22.825,22.825,0,0,0,1.163-5.926H18.99A19.124,19.124,0,0,1,18.4,18Z" transform="translate(-3 -2)" fill="#717580"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="aiz-topbar-item mr-3">
                <div class="d-flex align-items-center">
                    <a class="btn btn-topbar has-transition btn-icon btn-circle btn-soft-primary p-0 hov-bg-primary hov-svg-white d-flex align-items-center justify-content-center"
                        href="{{ route('optimize') }}" data-toggle="tooltip" data-title="{{ translate('Optimize') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 15 15" version="1.1" id="rocket">
                            <path id="path7143" fill="#717580" d="M12.5547,1c-2.1441,0-5.0211,1.471-6.9531,4H4&#10;&#9;C2.8427,5,2.1794,5.8638,1.7227,6.7773L1.1113,8h1.4434H4l1.5,1.5L7,11v1.4453v1.4434l1.2227-0.6113&#10;&#9;C9.1362,12.8206,10,12.1573,10,11V9.3984c2.529-1.932,4-4.809,4-6.9531V1H12.5547z M10,4c0.5523,0,1,0.4477,1,1l0,0&#10;&#9;c0,0.5523-0.4477,1-1,1l0,0C9.4477,6,9,5.5523,9,5v0C9,4.4477,9.4477,4,10,4L10,4z M3.5,10L3,10.5C2.2778,11.2222,2,13,2,13&#10;&#9;s1.698-0.198,2.5-1L5,11.5L3.5,10z"/>
                          </svg>
                    </a>
                </div>
            </div>
            @endhasrole
            <div class="aiz-topbar-item mr-3">
                <div class="align-items-stretch d-flex dropdown" >
                    <a class="dropdown-toggle no-arrow" data-toggle="dropdown" href="javascript:void(0);" id="theme-change"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="btn btn-topbar btn-circle btn-soft-primary p-0 d-flex justify-content-center align-items-center" data-toggle="tooltip" data-title="{{ translate('Theme') }}">
                            <i class="las la-palette"></i>
                        </span>
                    </a>
                </div>
            </div>
            <!-- Notifications -->
            @can('view_notifications')
                <x-admin-notifications />
            @endcan

            <!-- language -->
            @php
                if (Session::has('locale')) {
                    $locale = Session::get('locale', Config::get('app.locale'));
                } else {
                    $locale = config('app.locale');
                }
            @endphp
            <div class="aiz-topbar-item mr-3">
                <div class="align-items-stretch d-flex dropdown" id="lang-change">
                    <a class="dropdown-toggle no-arrow" data-toggle="dropdown" href="javascript:void(0);"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="btn btn-topbar btn-circle btn-soft-primary p-0 d-flex justify-content-center align-items-center" data-toggle="tooltip" data-title="{{ translate('Language') }}">
                            <img src="{{ static_asset('assets/img/flags/' . $locale . '.png') }}" height="11">
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-xs">

                        @foreach (\App\Models\Language::where('status', 1)->get() as $key => $language)
                            <li>
                                <a href="javascript:void(0)" data-flag="{{ $language->code }}"
                                    class="dropdown-item @if ($locale == $language->code) active @endif">
                                    <img src="{{ static_asset('assets/img/flags/' . $language->code . '.png') }}"
                                        class="mr-2">
                                    <span class="language">{{ $language->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <!-- User -->
            <div class="aiz-topbar-item">
                <div class="align-items-stretch d-flex dropdown">
                    <!-- Image & Name -->
                    <a class="dropdown-toggle no-arrow " data-toggle="dropdown" href="javascript:void(0);"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <span class="d-none d-md-block">
                                <span class="d-block fw-500">{{ Auth::user()->name }}</span>
                                <span class="d-block small opacity-60 text-right">{{ Auth::user()->user_type }}</span>
                            </span>
                            <span class="size-40px rounded-content overflow-hidden ml-md-2">
                                <img src="{{ uploaded_asset(Auth::user()->avatar_original) }}" class="img-fit"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                            </span>
                        </span>
                    </a>
                    <!-- User dropdown Menus -->
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-md">
                        <a href="{{ route('profile.index') }}" class="dropdown-item">
                            <i class="las la-user-circle"></i>
                            <span>{{ translate('Profile') }}</span>
                        </a>

                        <a href="{{ route('logout') }}" class="dropdown-item">
                            <i class="las la-sign-out-alt"></i>
                            <span>{{ translate('Logout') }}</span>
                        </a>
                    </div>
                </div>
            </div><!-- .aiz-topbar-item -->
        </div>
    </div>
</div><!-- .aiz-topbar -->
