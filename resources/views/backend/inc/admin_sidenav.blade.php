<div class="aiz-sidebar-wrap">
    <div class="aiz-sidebar left c-scrollbar">
        <div class="aiz-side-nav-logo-wrap">
            <a href="{{ route('admin.dashboard') }}" class="d-block text-center">
                @if(get_setting('site_icon') != null)
                    <img class="sidenav-logo mw-100" src="{{ uploaded_asset(get_setting('site_icon')) }}" class="brand-icon" alt="{{ get_setting('site_name') }}"
                    data-white-logo="{{ uploaded_asset(get_setting('site_icon')) }}"
                    data-dark-logo="{{ uploaded_asset(get_setting('site_icon')) }}"
                    >
                @else
                    <img class="sidenav-logo mw-100" src="{{ static_asset('assets/img/logo.png') }}" class="brand-icon" alt="{{ get_setting('site_name') }}">
                @endif
            </a>
        </div>
        <div class="aiz-side-nav-wrap">
            <div class="px-3 mb-3 position-relative">
                <input class="form-control bg-transparent rounded-2 form-control-sm fs-14" type="text" name="" placeholder="{{ translate('Search in menu') }}" id="menu-search" onkeyup="menuSearch()">
                <span class="absolute-top-right pr-3 mr-3" style="margin-top: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                        <path id="search_FILL0_wght200_GRAD0_opsz20" d="M176.921-769.231l6.255-6.255a5.99,5.99,0,0,0,1.733.949,5.687,5.687,0,0,0,1.885.329,5.317,5.317,0,0,0,3.9-1.608,5.31,5.31,0,0,0,1.609-3.9,5.322,5.322,0,0,0-1.608-3.9,5.306,5.306,0,0,0-3.9-1.611,5.321,5.321,0,0,0-3.9,1.609,5.312,5.312,0,0,0-1.611,3.9,5.554,5.554,0,0,0,.35,1.946,6.043,6.043,0,0,0,.929,1.672l-6.255,6.255Zm9.874-5.82a4.51,4.51,0,0,1-3.317-1.352,4.51,4.51,0,0,1-1.352-3.317,4.51,4.51,0,0,1,1.352-3.317,4.51,4.51,0,0,1,3.317-1.352,4.51,4.51,0,0,1,3.317,1.352,4.51,4.51,0,0,1,1.352,3.317,4.51,4.51,0,0,1-1.352,3.317A4.51,4.51,0,0,1,186.8-775.051Z" transform="translate(-176.307 785.231)" fill="#4e5767"/>
                    </svg>
                </span>
            </div>
            <ul class="aiz-side-nav-list" id="search-menu">
            </ul>
            <ul class="aiz-side-nav-list" id="main-menu" data-toggle="aiz-side-menu">

                {{-- Dashboard --}}
                @can('admin_dashboard')
                    <li class="aiz-side-nav-item">
                        <a href="{{route('admin.dashboard')}}" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <path id="_3d6902ec768df53cd9e274ca8a57e401" data-name="3d6902ec768df53cd9e274ca8a57e401" d="M18,12.286a1.715,1.715,0,0,0-1.714-1.714h-4a1.715,1.715,0,0,0-1.714,1.714v4A1.715,1.715,0,0,0,12.286,18h4A1.715,1.715,0,0,0,18,16.286Zm-8.571,0a1.715,1.715,0,0,0-1.714-1.714h-4A1.715,1.715,0,0,0,2,12.286v4A1.715,1.715,0,0,0,3.714,18h4a1.715,1.715,0,0,0,1.714-1.714Zm7.429,0v4a.57.57,0,0,1-.571.571h-4a.57.57,0,0,1-.571-.571v-4a.57.57,0,0,1,.571-.571h4a.57.57,0,0,1,.571.571Zm-8.571,0v4a.57.57,0,0,1-.571.571h-4a.57.57,0,0,1-.571-.571v-4a.57.57,0,0,1,.571-.571h4a.57.57,0,0,1,.571.571ZM9.429,3.714A1.715,1.715,0,0,0,7.714,2h-4A1.715,1.715,0,0,0,2,3.714v4A1.715,1.715,0,0,0,3.714,9.429h4A1.715,1.715,0,0,0,9.429,7.714Zm8.571,0A1.715,1.715,0,0,0,16.286,2h-4a1.715,1.715,0,0,0-1.714,1.714v4a1.715,1.715,0,0,0,1.714,1.714h4A1.715,1.715,0,0,0,18,7.714Zm-9.714,0v4a.57.57,0,0,1-.571.571h-4a.57.57,0,0,1-.571-.571v-4a.57.57,0,0,1,.571-.571h4a.57.57,0,0,1,.571.571Zm8.571,0v4a.57.57,0,0,1-.571.571h-4a.57.57,0,0,1-.571-.571v-4a.57.57,0,0,1,.571-.571h4a.57.57,0,0,1,.571.571Z" transform="translate(-2 -2)" fill="#575b6a" fill-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Dashboard')}}</span>
                        </a>
                    </li>
                @endcan
                <!-- Cars -->
                @canany(['view_all_cars'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 7.85 6.156"><path d="M102.889 148.242a.614.614 0 1 0 .611.613.614.614 0 0 0-.611-.613m0 .38c.128 0 .232.105.232.233a.23.23 0 0 1-.232.233.234.234 0 0 1-.235-.233c0-.128.106-.234.235-.234M107.402 148.242a.615.615 0 0 0-.613.613.614.614 0 0 0 1.227 0 .615.615 0 0 0-.614-.613m0 .38c.129 0 .235.105.235.233a.234.234 0 0 1-.467 0c0-.128.104-.234.232-.234" style="stroke-width:.264583" transform="translate(-101.22 -145.354)"/><path d="M103.31 145.354a.86.86 0 0 0-.544.17.94.94 0 0 0-.301.476l-.002.008-.235 1.04h-.892a.116.116 0 0 0-.115.114v.282a.116.116 0 0 0 .115.115h.394a.95.95 0 0 0-.369.746v1.96a.116.116 0 0 0 .116.116h.308v.309c0 .455.364.82.82.82s.82-.364.82-.82v-.309h3.44v.309c0 .455.365.82.82.82.456 0 .82-.364.82-.82v-.309h.31a.116.116 0 0 0 .115-.115v-1.961a.96.96 0 0 0-.377-.746h.402a.116.116 0 0 0 .115-.115v-.282a.116.116 0 0 0-.115-.115h-.879l-.22-1.037-.003-.006c-.11-.41-.452-.65-.873-.65zm0 .511h3.67a.42.42 0 0 1 .254.067c.057.04.1.102.13.205l.25 1.205h-4.932l.273-1.188.002-.004a.5.5 0 0 1 .13-.222c.05-.041.114-.063.224-.063m-.988 1.99h5.645a.45.45 0 0 1 .449.45v1.564h-6.543v-1.564a.45.45 0 0 1 .45-.45m-.025 2.526h.617v.308c0 .181-.127.307-.309.307a.294.294 0 0 1-.308-.307zm5.08 0h.617v.308c0 .181-.127.307-.308.307a.294.294 0 0 1-.31-.307z" style="stroke-width:.264583" transform="translate(-101.22 -145.354)"/></svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Cars')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_all_cars')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.cars.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.cars.index', 'admin.cars.show','admin.cars.edit', 'admin.cars.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('System Cars')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_cars')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.cars.index', ['user_type' => 'seller']) }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.cars.index', 'admin.cars.show','admin.cars.edit', 'admin.cars.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Seller Cars')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_car_categories')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-categories.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-categories.index', 'admin.car-categories.show', 'admin.car-categories.edit', 'admin.car-categories.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Car Categories')}}</span>
                                    </a>
                                </li>
                            @endcan
                             @can('view_all_car_brands')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-brands.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-brands.index', 'admin.car-brands.show', 'admin.car-brands.edit', 'admin.car-brands.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Car Brands')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_car_models')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-models.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-models.index', 'admin.car-models.show', 'admin.car-models.edit', 'admin.car-models.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Car Models')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_car_features')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-features.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-features.index', 'admin.car-features.show', 'admin.car-features.edit', 'admin.car-features.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Car Features')}}</span>
                                    </a>
                                </li>
                            @endcan
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('admin.car-colors.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-colors.index', 'admin.car-colors.show', 'admin.car-colors.edit', 'admin.car-colors.create'])}}">
                                    <span class="aiz-side-nav-text">{{translate('Car Colors')}}</span>
                                </a>
                            </li>
                             @can('view_all_car_custom_fields')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-custom-fields.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-custom-fields.index', 'admin.car-custom-fields.show', 'admin.car-custom-fields.edit', 'admin.car-custom-fields.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('All Car Custom Fields')}}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                <!-- Car Reservations -->
                 @canany(['view_car_reservations'])
                  <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 500 500"><path d="m243.555 15.762 2.7-.03C266.188 15.56 285.42 15.825 305 20l2.678.551C348.903 29.255 387.208 49.316 418 78a746 746 0 0 0 4.188 3.625c6.493 5.688 12.087 11.765 17.496 18.5a293 293 0 0 0 4.539 5.5C454.93 118.317 463.293 132.335 471 147l1.054 1.972c11.169 21.15 18.252 45.48 21.946 69.028l.553 3.502c1.6 11.27 1.845 22.439 1.822 33.81v2.925c-.035 15.904-1.04 31.165-4.375 46.763l-.567 2.726c-1.247 5.83-2.736 11.559-4.433 17.274l-.793 2.695a382 382 0 0 1-2.582 8.18l-.775 2.506c-2.204 6.415-5.73 10.559-11.85 13.619-5.603.595-11.213 1.122-16.062-2.125-3.552-3.11-5.672-6.215-6.154-11.03-.135-6.587 1.184-11.81 3.404-17.97a203 203 0 0 0 6.687-24.687l.614-2.98c1.715-8.647 2.66-17.144 2.75-25.958l.029-2.447c.531-55.19-15.25-106.415-53.19-147.537-1.896-2.068-3.708-4.184-5.515-6.329-31.963-36.497-84.63-58.028-132.27-61.289-54.023-3.113-107.88 12.987-149.027 48.727a482 482 0 0 0-5 4.547c-2.068 1.896-4.184 3.708-6.329 5.516C75.486 139.485 54.533 189.885 50 236c-.158 2.884-.21 5.764-.238 8.652l-.03 2.452c-.53 55.229 15.22 106.473 53.19 147.63 1.896 2.068 3.708 4.184 5.516 6.329C124.82 419.77 147.598 433.677 170 444l3.438 1.61c43.182 19.392 92.36 21.92 137.84 9.782 6.351-1.693 11.482-1.832 17.534.858 3.088 2.47 5.417 5.208 7.188 8.75.583 5.484 1.118 11.181-1.937 16-7.95 8.754-23.114 9.826-34.063 12l-2.453.514C260.573 501 219.785 496.807 184 486l-2.53-.76C148.853 475.195 118.916 457.21 94 434a746 746 0 0 0-4.187-3.625c-6.5-5.693-12.1-11.774-17.508-18.52a205 205 0 0 0-4.422-5.304c-31.646-36.92-51.677-89.43-52.121-138.106l-.03-2.7C15.485 237.416 17.766 211.265 26 184l.76-2.53C40.13 138.06 68.194 97.028 104 69l2.984-2.395c38.032-30.244 87.606-50.397 136.57-50.843"/><path d="m197.266 151.853 3.233-.022 3.53-.008 3.732-.02c4.081-.02 8.162-.031 12.243-.041l4.226-.013q9.936-.029 19.87-.041 11.438-.015 22.874-.077 8.858-.044 17.717-.048c3.522-.002 7.042-.011 10.563-.036 3.937-.028 7.874-.024 11.81-.017l3.489-.039c13.078.084 21.963 4.523 31.385 13.318 8.616 8.932 11.485 22.061 15.384 33.533l1.006 2.951.903 2.673c.596 1.998.596 1.998 1.769 3.034a91 91 0 0 0 4.188-.125c4.385-.028 7.109.6 10.812 3.125 3.942 4.006 6.147 7.095 6.25 12.75-.055 5.406-1.273 9.053-4.937 13.125L376 237c1.279 2.793 2.639 4.843 4.75 7.062 10.172 11.618 11.571 23.624 11.586 38.426l.02 4.528q.017 4.732.015 9.463c0 4.015.027 8.028.061 12.043.023 3.115.026 6.23.025 9.345q.004 2.218.027 4.436c.129 13.47-2.161 23.627-11.484 33.697-7.539 7.64-16.935 12.176-27.725 12.38-13.852-.032-23.729-2.847-34.275-12.38-4.823-5.533-6.951-9.829-9-17H202l-3 9c-5.297 9.358-14.555 16.012-24.684 19.34-12.201 2.429-25.133 1.933-35.765-4.918C129.65 355.89 122.198 347.11 120 336a164 164 0 0 1-.161-6.63l-.015-1.992q-.021-3.255-.027-6.51l-.009-2.259q-.022-5.92-.028-11.841c-.006-4.055-.03-8.11-.058-12.164-.019-3.137-.024-6.274-.026-9.41a654 654 0 0 0-.024-4.48C119.5 262.064 124.965 251.028 137 237l-1.895-.879c-3.546-1.888-4.859-4.383-6.105-8.121-.564-5.693-.307-10.15 3-15 3.285-3.312 6.367-5.84 11.191-6.133l2.372.008 2.378-.008C150 207 150 207 152 208l.522-1.781a539 539 0 0 1 6.29-19.782l.963-2.958c4.31-12.66 11.49-22.224 23.538-28.417 4.76-2.26 8.678-3.185 13.953-3.21m-4.035 37.373c-1.48 3.334-2.761 6.704-3.93 10.157l-.727 2.11q-.755 2.193-1.5 4.391c-.762 2.243-1.536 4.483-2.31 6.721q-.733 2.139-1.463 4.278l-.7 2.026c-.957 2.836-1.601 5.075-1.601 8.091h150c-1.668-7.508-3.68-14.602-6.149-21.85-.666-1.971-1.31-3.95-1.955-5.93q-.643-1.901-1.29-3.802l-1.152-3.445c-1.677-3.43-3.26-4.921-6.454-6.973-2.89-.916-5.512-1.129-8.534-1.14l-2.74-.018-2.983-.003-3.16-.015q-5.176-.021-10.353-.027l-7.196-.017q-7.547-.015-15.094-.02a5421 5421 0 0 1-19.328-.058c-4.956-.019-9.912-.024-14.868-.026q-3.562-.004-7.125-.024c-3.325-.017-6.649-.015-9.974-.008l-2.965-.028c-6.285.038-12.259.337-16.449 5.61m-38.668 78.711c-3.258 6.386-2.738 13.364-2.766 20.375l-.017 3.295q-.014 3.439-.02 6.878c-.01 3.503-.041 7.005-.072 10.507q-.01 3.348-.016 6.695l-.038 3.164c.017 5.44.17 9.514 3.366 14.149 3.18 2.12 5.194 2.559 8.996 2.426 2.83-.602 4.457-2.062 6.121-4.387 1.45-3.347 1.406-6.746 1.602-10.352.448-4.28 1.584-6.298 4.281-9.687 7.14-4.704 13.618-4.57 22-4.518q2.11-.008 4.22-.02c3.802-.019 7.604-.012 11.405 0 3.99.01 7.98.001 11.969-.005q10.047-.01 20.094.022c7.73.021 15.46.014 23.19-.008q9.976-.026 19.954-.01c3.966.006 7.932.007 11.898-.006q5.595-.017 11.19.02c2.011.007 4.023-.005 6.035-.017 7.14.066 12.025.576 18.045 4.542 3.415 4.291 4.142 7.132 4.438 12.562.228 3.603.5 6.156 2.625 9.125 3.296 2.595 4.909 3.154 9.003 2.844 3.298-.906 4.906-2.888 6.934-5.531.965-2.896 1.137-4.774 1.161-7.788l.03-2.944.012-3.182.017-3.286q.014-3.447.02-6.894c.01-3.496.04-6.992.073-10.488q.009-3.353.015-6.707l.038-3.145c-.023-7.581-1.094-13.962-5.178-20.441-4.69-4.556-9.415-5.569-15.765-5.52l-2.25-.018a861 861 0 0 0-7.434-.007q-2.672-.01-5.343-.026c-4.83-.022-9.661-.025-14.492-.023q-6.054-.002-12.107-.017-14.284-.027-28.569-.015a6611 6611 0 0 1-29.45-.053q-12.653-.045-25.305-.038c-5.035.002-10.07-.004-15.105-.028-4.736-.023-9.472-.02-14.208 0q-2.602.005-5.205-.016c-10.496-.081-18.287.01-25.392 8.573M438 368c4.885 2.268 7.732 5.115 10 10 .726 5.394 1.073 10.05-1.602 14.91-2.892 3.726-6.011 7.125-9.273 10.527l-4.05 4.274-2.066 2.17a1433 1433 0 0 0-9.072 9.682L415 427l-3.46 3.71-1.739 1.864-5.203 5.57q-3.812 4.081-7.602 8.18L393 450.625l-1.851 2.01c-10.677 11.464-10.677 11.464-18.774 11.803l-3.04.183c-8.848-1.647-15.39-10.463-21.397-16.621-6.7-6.91-6.7-6.91-13.872-13.312-3.673-3.087-5.803-5.75-6.464-10.625l.023-3.063-.023-3.062c.63-4.643 2.782-7.788 6.398-10.688 4.018-3.014 6.817-3.38 11.77-3.578 5.38.791 9.287 3.98 13.168 7.578l2.582 2.387A369 369 0 0 1 371 423c5.062-4.195 9.614-8.603 14.063-13.437l1.96-2.112q2.01-2.164 4.016-4.333c3.566-3.852 7.154-7.683 10.74-11.515q4.546-4.86 9.034-9.771l1.902-2.07a682 682 0 0 0 3.58-3.928c6.641-7.208 11.842-9.025 21.705-7.834"/><path d="m183.719 268.469 3.824-.016 3.957.047 3.957-.047 3.824.016 3.471.013c4.911.783 7.967 3.341 11.56 6.643 2.455 4.181 2.876 8.064 2.688 12.875-1.544 5.23-3.728 8.602-8 12-5.666 2.072-10.877 2.335-16.875 2.313l-2.566.048c-6.244.005-13.32-.472-18.187-4.813-4.24-4.977-4.724-8.363-4.65-14.72.426-4.336 1.6-6.374 4.278-9.828 4.337-3.43 7.209-4.51 12.719-4.531M316 268.5l3.773-.047c6.896.03 11.734.198 17.227 4.547 3.563 4.17 4.715 9.361 4.453 14.758-1.042 5.155-3.85 9-7.953 12.18-5.658 2.404-11.325 2.245-17.375 2.25l-2.645.037c-6.512.014-12.057-.188-17.23-4.538-4.052-4.84-5.848-7.984-5.594-14.343.813-5.542 2.737-9.071 7.121-12.543 5.623-3.013 12.018-2.378 18.223-2.301"/></svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Car Reservations')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_car_reservations')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-reservations.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-reservations.index', 'admin.cars.show','admin.cars.edit', 'admin.cars.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Car Reservations')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_car_reservations')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-reservations.setup') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-reservations.setup'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Car Reservations Setup')}}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                <!-- Car Inspections -->
                @canany(['view_all_cars_inspections'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="16" height="16" viewBox="0 0 256 256" xml:space="preserve">
                                    <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                                        <path d="M 11.46 88.114 c -1.676 0 -3.352 -0.638 -4.627 -1.913 l -3.034 -3.034 c -2.551 -2.551 -2.551 -6.703 0 -9.254 L 29.051 48.66 c -1.75 -7.659 2.329 -15.738 9.685 -18.784 c 4.756 -1.97 10.251 -1.552 14.698 1.119 c 0.528 0.317 0.881 0.859 0.956 1.47 c 0.075 0.611 -0.136 1.223 -0.571 1.658 L 41.356 46.587 c -0.274 0.274 -0.426 0.64 -0.426 1.028 s 0.151 0.754 0.426 1.028 c 0.55 0.551 1.508 0.551 2.057 0 l 12.466 -12.465 c 0.436 -0.436 1.043 -0.646 1.657 -0.571 c 0.61 0.075 1.152 0.426 1.47 0.954 c 0.473 0.785 0.828 1.476 1.118 2.174 c 3.453 8.34 -0.521 17.935 -8.859 21.389 c -3.142 1.301 -6.62 1.584 -9.923 0.823 L 16.088 86.201 C 14.812 87.477 13.136 88.114 11.46 88.114 z M 44.978 32.627 c -1.591 0 -3.186 0.313 -4.711 0.944 c -5.847 2.421 -8.954 9.05 -7.075 15.092 c 0.221 0.709 0.03 1.482 -0.496 2.008 L 6.627 76.741 c -0.992 0.992 -0.992 2.605 0 3.598 l 3.034 3.034 c 0.991 0.991 2.605 0.993 3.598 0 l 26.071 -26.071 c 0.526 -0.525 1.301 -0.717 2.008 -0.495 c 2.754 0.855 5.734 0.723 8.395 -0.378 c 6.056 -2.509 9.065 -9.304 6.975 -15.423 L 46.242 51.472 c -1.03 1.03 -2.4 1.599 -3.857 1.599 s -2.827 -0.568 -3.857 -1.599 c -1.03 -1.029 -1.597 -2.399 -1.597 -3.856 s 0.567 -2.827 1.598 -3.857 l 10.454 -10.454 C 47.683 32.854 46.331 32.627 44.978 32.627 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10;  fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                                        <path d="M 49.857 90 h -9.714 c -4.251 -0.003 -7.709 -3.461 -7.708 -7.709 c 0 -1.066 -0.43 -1.856 -1.315 -2.418 c -0.522 -0.331 -0.861 -0.884 -0.92 -1.499 c -0.059 -0.615 0.169 -1.223 0.619 -1.646 l 9.987 -9.411 c 0.431 -0.407 1.019 -0.605 1.61 -0.53 c 3.715 0.447 7.512 -0.076 10.981 -1.514 c 5.406 -2.239 9.619 -6.458 11.865 -11.88 c 2.245 -5.422 2.249 -11.385 0.01 -16.79 c -2.239 -5.406 -6.458 -9.62 -11.88 -11.866 c -5.423 -2.247 -11.385 -2.249 -16.79 -0.01 c -9.037 3.743 -14.545 13.144 -13.396 22.86 c 0.072 0.608 -0.139 1.216 -0.572 1.649 l -9.641 9.646 c -0.411 0.409 -0.978 0.619 -1.557 0.581 c -0.579 -0.042 -1.111 -0.332 -1.458 -0.797 c -0.555 -0.741 -1.298 -1.102 -2.271 -1.102 c -2.06 0 -3.996 -0.802 -5.451 -2.258 S 0 51.915 0 49.855 l 0 -9.712 c 0 -2.059 0.801 -3.995 2.257 -5.451 c 1.457 -1.456 3.393 -2.258 5.451 -2.258 c 0 0 0.001 0 0.001 0 c 1.676 0 2.386 -1.113 2.66 -1.777 c 0.276 -0.667 0.562 -1.958 -0.624 -3.142 c -1.457 -1.458 -2.258 -3.393 -2.257 -5.452 c 0 -2.059 0.802 -3.996 2.258 -5.451 l 6.867 -6.867 c 1.457 -1.456 3.391 -2.258 5.447 -2.258 c 0.002 0 0.003 0 0.005 0 c 2.058 0 3.994 0.802 5.45 2.259 c 1.183 1.183 2.475 0.897 3.14 0.623 c 0.664 -0.275 1.778 -0.986 1.778 -2.662 C 32.438 3.458 35.895 0 40.142 0 h 9.714 c 2.06 0 3.995 0.802 5.45 2.258 c 1.456 1.455 2.258 3.39 2.258 5.449 c 0 1.74 1.243 2.442 1.778 2.663 c 0.538 0.225 1.911 0.606 3.141 -0.623 c 3.003 -3.003 7.893 -3.006 10.899 -0.003 l 6.87 6.87 c 1.457 1.456 2.259 3.392 2.259 5.451 c 0 2.06 -0.803 3.996 -2.259 5.452 c -1.229 1.229 -0.846 2.604 -0.624 3.138 c 0.223 0.538 0.924 1.781 2.662 1.781 c 4.251 0.001 7.708 3.458 7.709 7.707 v 9.714 c -0.003 4.245 -3.459 7.702 -7.704 7.709 h -0.001 c -1.679 0 -2.391 1.114 -2.666 1.778 s -0.561 1.956 0.624 3.141 c 3.004 3.005 3.005 7.895 0.003 10.899 l -6.871 6.87 c -3.006 3.001 -7.896 3.003 -10.897 0.003 c -1.186 -1.186 -2.479 -0.901 -3.145 -0.627 c -0.663 0.274 -1.778 0.987 -1.778 2.662 c 0.001 2.057 -0.801 3.993 -2.258 5.45 c -0.723 0.724 -1.563 1.286 -2.499 1.674 S 50.88 90 49.857 90 z M 35.11 78.181 c 0.866 1.148 1.326 2.552 1.325 4.11 c 0 2.044 1.664 3.708 3.71 3.709 h 9.712 c 0.494 0 0.972 -0.094 1.419 -0.279 c 0.448 -0.186 0.853 -0.457 1.202 -0.807 c 0.701 -0.701 1.087 -1.632 1.086 -2.621 c 0 -3.393 2.195 -5.51 4.249 -6.359 c 2.056 -0.849 5.105 -0.903 7.501 1.494 c 1.444 1.441 3.797 1.442 5.243 -0.003 l 6.869 -6.869 c 1.443 -1.445 1.442 -3.798 -0.002 -5.243 c -2.397 -2.397 -2.342 -5.446 -1.491 -7.5 s 2.968 -4.247 6.357 -4.247 c 0.001 0 0.002 0 0.003 0 c 2.04 -0.003 3.705 -1.668 3.706 -3.71 v -9.713 c -0.001 -2.043 -1.663 -3.707 -3.707 -3.708 c -3.395 0 -5.51 -2.195 -6.359 -4.25 c -0.852 -2.049 -0.909 -5.098 1.491 -7.498 c 0.701 -0.701 1.087 -1.632 1.087 -2.623 c 0 -0.99 -0.386 -1.921 -1.087 -2.622 l -6.869 -6.87 c -1.446 -1.444 -3.798 -1.443 -5.244 0.002 c -2.4 2.401 -5.449 2.344 -7.501 1.49 c -2.05 -0.847 -4.246 -2.964 -4.246 -6.358 c 0 -0.99 -0.386 -1.921 -1.086 -2.621 C 51.778 4.386 50.847 4 49.856 4 h -9.714 c -2.043 0 -3.706 1.665 -3.708 3.71 c 0 3.389 -2.194 5.505 -4.248 6.356 c -2.054 0.851 -5.103 0.906 -7.5 -1.49 c -0.701 -0.702 -1.632 -1.087 -2.622 -1.087 c -0.001 0 -0.001 0 -0.002 0 c -0.99 0 -1.92 0.386 -2.621 1.086 l -6.867 6.867 c -0.701 0.701 -1.086 1.632 -1.086 2.623 c 0 0.991 0.385 1.922 1.086 2.623 c 2.398 2.397 2.343 5.446 1.491 7.5 c -0.847 2.052 -2.962 4.247 -6.355 4.248 c -0.001 0 -0.001 0 -0.002 0 c -0.99 0 -1.921 0.386 -2.623 1.087 C 4.386 38.222 4 39.153 4 40.143 l 0 9.712 c 0 0.991 0.386 1.923 1.086 2.623 s 1.631 1.086 2.623 1.086 c 1.418 0 2.723 0.391 3.82 1.126 l 7.609 -7.613 c -0.889 -11.135 5.554 -21.746 15.934 -26.045 c 6.392 -2.648 13.442 -2.645 19.852 0.01 S 66.32 28.68 68.968 35.073 c 2.648 6.393 2.645 13.443 -0.01 19.851 c -2.654 6.409 -7.637 11.396 -14.029 14.045 c -3.81 1.578 -7.957 2.225 -12.044 1.886 L 35.11 78.181 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                                    </g>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Car Inspections')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_all_cars_inspections')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-inspections.dashboard') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-inspections.dashboard'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Overview')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_cars_inspections')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-inspections.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-inspections', 'admin.car-inspections.show', 'admin.car-inspections.edit', 'admin.car-inspections.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('All Car Inspections')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_cars_inspections')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.manual-examinations.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.manual-examinations.index', 'admin.manual-examinations.show'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Manual Examinations')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_cars_inspections')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.manual-examinations.permissions.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.manual-examinations.permissions.index'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Manual Examination Permissions')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_cars_inspections')
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('admin.car-inspection-types.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-inspection-types.show', 'admin.car-inspection-types.edit', 'admin.car-inspection-types.create'])}}">
                                    <span class="aiz-side-nav-text">{{ translate('Inspection Types') }}</span>
                                </a>
                            </li>
                        @endcan
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('pdf_settings.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pdf_settings.index'])}}">
                                <span class="aiz-side-nav-text">{{ translate('PDF Settings') }}</span>
                            </a>
                        </li>
                        </ul>
                    </li>
                @endcanany
                <!-- Car Inspectors -->
                @canany(['view_all_cars_inspectors'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="#040404" fill-rule="evenodd" d="M3.555.168c-.621.256-.864.408-1.236.774-.398.39-.698.915-.876 1.53-.069.239-.078.347-.076.86.002.533.01.616.095.898.184.62.612 1.306 1.003 1.61l.158.124-.336.02c-.628.039-1.1.254-1.566.714C.467 6.95.4 7.04.266 7.32 0 7.877 0 7.88 0 10.1c0 2.23.004 2.27.264 2.81.279.578.805 1.026 1.457 1.24.223.073.35.089.804.103l.54.016.016.522c.022.673.09.898.388 1.278.46.587 1.426.725 2.087.297.508-.328.728-.83.728-1.662v-.443h.561c.31 0 .616-.016.682-.035.208-.058.45-.272.585-.518.116-.21.123-.243.123-.535 0-.272-.012-.334-.096-.497-.095-.185-.095-.186-.025-.235.137-.096.28-.272.372-.46.08-.159.095-.234.095-.468 0-.246-.013-.305-.11-.491l-.108-.211.154-.127c.27-.221.4-.506.396-.864-.002-.187-.102-.498-.191-.597-.047-.052-.037-.069.11-.178a.9.9 0 0 0 .284-.358c.107-.211.12-.267.12-.501a1 1 0 0 0-.367-.809c-.286-.25-.367-.264-1.553-.264H6.284v-.857l.186-.104c.64-.361 1.188-1.089 1.418-1.884.443-1.527-.252-3.205-1.61-3.891C6.03.252 5.4 0 5.336 0c-.027 0-.093.038-.146.083l-.097.084-.017 1.49-.017 1.49-.113.102a.393.393 0 0 1-.614-.11c-.042-.08-.05-.346-.05-1.526V.185L4.204.112c-.137-.128-.224-.12-.65.056m-.118.658c-.682.323-1.171.888-1.424 1.643-.084.252-.092.324-.094.829-.001.486.008.583.08.81.23.734.686 1.312 1.28 1.62.148.077.29.166.317.198.035.043.05.205.06.623l.011.564h2.066v-.555c0-.308.015-.583.035-.62.019-.035.163-.131.32-.212.206-.108.36-.223.563-.425a2.6 2.6 0 0 0 .676-1.088c.11-.317.113-.348.114-.88.001-.485-.009-.585-.08-.81-.244-.78-.743-1.368-1.438-1.697L5.646.695l-.018 1.267c-.01.696-.031 1.298-.049 1.336-.083.18-.24.363-.402.467-.152.098-.216.116-.446.13-.232.013-.292.004-.43-.065a1.06 1.06 0 0 1-.482-.478c-.068-.15-.074-.228-.09-1.408L3.712.694zM1.972 6.58c-.289.077-.634.277-.849.49-.222.22-.336.399-.464.729l-.089.228L.56 9.98c-.008 1.334 0 2.022.027 2.174.118.677.616 1.236 1.312 1.473.18.061.341.065 2.854.065 2.615 0 2.665-.001 2.74-.07a.7.7 0 0 0 .207-.448q0-.445-.507-.54c-.22-.04-.317-.082-.38-.165-.065-.083-.053-.225.026-.326.07-.088.083-.091.448-.091.208 0 .414-.018.462-.04a.6.6 0 0 0 .182-.176.47.47 0 0 0 .095-.322c0-.304-.163-.467-.543-.545-.287-.06-.37-.127-.37-.298 0-.219.09-.267.525-.283.305-.01.386-.025.464-.083a.54.54 0 0 0 .244-.417c.027-.31-.182-.53-.55-.58a.42.42 0 0 1-.273-.121c-.108-.108-.12-.161-.06-.29.057-.126.15-.155.538-.168.378-.012.462-.046.606-.239.17-.227.08-.603-.178-.748l-.134-.075H5.582c-1.915 0-2.738-.012-2.797-.039-.161-.073-.204-.277-.09-.423.059-.074.101-.09.225-.09h.153v-.588l-.457.002c-.284.002-.529.022-.644.053m1.694 8.224c.012.513.018.553.107.724.198.378.653.6 1.077.524.264-.047.372-.105.57-.304.229-.228.28-.396.303-.996l.018-.492H3.654z" />
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Car Inspectors')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_all_cars_inspectors')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-inspectors.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-inspectors', 'admin.car-inspectors.show', 'admin.car-inspectors.edit', 'admin.car-inspectors.payments', 'admin.car-inspectors.make-payment'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Car Inspectors')}}</span>
                                    </a>
                                </li>
                            @endcan
                             @can('view_all_cars_inspectors')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-inspectors.create') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-inspectors.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Add new Car Inspector')}}</span>
                                    </a>
                                </li>
                            @endcan
                             @can('view_all_cars_inspectors')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.car-inspectors.all-payments') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-inspectors.all-payments'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Car Inspector payments')}}</span>
                                    </a>
                                </li>
                            @endcan
                             @can('view_all_cars_inspectors')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('admin.car-inspectors.settings') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.car-inspectors.all-payments'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Car Inspector Settings')}}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                <!-- Auction Management -->
                @canany(['view_auction_rooms', 'view_auction_listing_requests', 'view_auction_offers', 'view_auction_dashboard','manage_auction_settings'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                               <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="16" height="16" viewBox="0 0 512 512"><path d="M475.542 203.546c-15.705-15.707-38.776-18.531-57.022-9.796L296.42 71.648c8.866-18.614 5.615-41.609-9.775-56.999-19.528-19.531-51.307-19.531-70.837 0L144.97 85.486c-19.529 19.529-19.529 51.307 0 70.836 15.351 15.353 38.31 18.678 56.999 9.775l25.645 25.645L14.902 404.454c-19.575 19.574-19.578 51.259 0 70.836 19.575 19.576 51.259 19.579 70.837 0l212.712-212.711 25.642 25.641c-8.868 18.615-5.617 41.609 9.774 57 9.46 9.46 22.039 14.672 35.419 14.672s25.957-5.21 35.418-14.672l70.837-70.837c19.531-19.53 19.531-51.306.001-70.837M192.196 132.71c-6.51 6.509-17.103 6.507-23.613 0-6.509-6.511-6.509-17.102 0-23.612l70.837-70.837c6.509-6.509 17.1-6.512 23.612 0 6.51 6.51 6.51 17.102.001 23.612zM62.127 451.676c-6.526 6.525-17.086 6.526-23.612 0s-6.526-17.087 0-23.612l212.712-212.712 23.612 23.613zm165.487-307.16 11.805-11.807 35.419-35.419L392.9 215.353l-47.224 47.225zm224.317 106.256-70.837 70.837c-6.526 6.526-17.086 6.526-23.612 0-6.51-6.51-6.51-17.103 0-23.613l70.838-70.837c6.524-6.526 17.086-6.525 23.611 0s6.526 17.086 0 23.613M461.691 411.822H328.12c-27.619 0-50.089 22.47-50.089 50.089v33.393c0 9.221 7.476 16.696 16.696 16.696h200.357c9.221 0 16.696-7.476 16.696-16.696v-33.393c.001-27.619-22.469-50.089-50.089-50.089m16.697 66.785H311.424v-16.696c0-9.206 7.49-16.696 16.696-16.696h133.571c9.206 0 16.696 7.49 16.696 16.696z"></path></svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Auction Management')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_auction_dashboard')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.auction-dashboard') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.auction-dashboard'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Dashboard')}}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('view_auction_rooms')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.auction-rooms.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.auction-rooms.index', 'admin.auction-rooms.show', 'admin.auction-rooms.edit', 'admin.auction-rooms.create', 'admin.auction-rooms.monitor', 'admin.auction-rooms.report'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Auction Rooms')}}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('view_auction_listing_requests')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.auction-listing-requests.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.auction-listing-requests.index', 'admin.auction-listing-requests.show'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Listing Requests')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_auction_offers')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.auction-offers.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.auction-offers.index', 'admin.auction-offers.show', 'admin.auction-offers.stats'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Auction Offers')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_insurance_deposits')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('insurance-deposits.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['insurance-deposits.index', 'insurance-deposits.show'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Insurance Deposits')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_auction_invoices')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.auction-invoices.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.auction-invoices.index', 'admin.auction-invoices.show'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Auction Invoices')}}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('view_auction_audit_logs')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.auction-audit-logs') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.auction-audit-logs', 'admin.auction-audit-logs.show'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Audit Logs')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('manage_auction_settings')
                                   <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.auction.settings') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.auction.settings'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Auction Settings')}}</span>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                @endcanany

                <!-- POS Addon-->
                @if (addon_is_activated('pos_system') && (auth()->user()->can('pos_manager') || auth()->user()->can('pos_configuration')))
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13.79" height="16" viewBox="0 0 13.79 16">
                                    <g id="_371925cdd3f531725a9fa8f3ebf8fe9e" data-name="371925cdd3f531725a9fa8f3ebf8fe9e" transform="translate(-2.26 0)">
                                      <path id="Path_40673" data-name="Path 40673" d="M10.69,7H3.26a1.025,1.025,0,0,0-1,1V18.45a1.03,1.03,0,0,0,1,1.05h7.43a1.03,1.03,0,0,0,1.03-1.03V8A1.025,1.025,0,0,0,10.69,7ZM4.94,17.86H3.995v-.95H4.94Zm0-2.355H3.995v-.95H4.94Zm0-2.355H3.995V12.2H4.94Zm2.5,4.71H6.5v-.95h.955Zm0-2.355H6.5v-.95h.955Zm0-2.355H6.5V12.2h.955Zm2.5,4.71H8.99v-.95h.95Zm0-2.355H8.99v-.95h.95Zm0-2.355H8.99V12.2h.95Zm.325-3a.17.17,0,0,1-.165.17H3.835a.17.17,0,0,1-.165-.17V8.795a.165.165,0,0,1,.165-.165H10.13a.165.165,0,0,1,.165.165Zm5.09-1.45H15.13v9.09h.25a.67.67,0,0,0,.67-.67V9.375a.67.67,0,0,0-.695-.675Z" transform="translate(0 -3.5)" fill="#4e5767"/>
                                      <rect id="Rectangle_20842" data-name="Rectangle 20842" width="1.465" height="9.095" transform="translate(12.185 5.2)" fill="#4e5767"/>
                                      <rect id="Rectangle_20843" data-name="Rectangle 20843" width="0.63" height="9.095" transform="translate(14.06 5.2)" fill="#4e5767"/>
                                      <path id="Path_40674" data-name="Path 40674" d="M13.895.895a.89.89,0,0,0-.26-.635A.91.91,0,0,0,13,0a.895.895,0,0,0-.91.895v.53h1.79Zm-2.2,0a.76.76,0,0,1,0-.145.68.68,0,0,1,0-.1h.01A.5.5,0,0,1,11.755.5.43.43,0,0,1,11.79.4a1.2,1.2,0,0,1,.145-.26.5.5,0,0,1,.04-.055L12.045,0H7.995A.815.815,0,0,0,7.18.81V3.03h4.5Z" transform="translate(-2.46)" fill="#4e5767"/>
                                    </g>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('POS System')}}</span>
                            @if (env("DEMO_MODE") == "On")
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.001" viewBox="0 0 16 14.001" class="mx-2">
                                    <path id="Union_49" data-name="Union 49" d="M-19322,3342.5v-5a2.007,2.007,0,0,0-2-2v1.5a3,3,0,0,1-3,3h-4v-10h4a3,3,0,0,1,3,3v1.5a3,3,0,0,1,3,3v5a.506.506,0,0,1-.5.5A.5.5,0,0,1-19322,3342.5Zm-11-2V3339h-3a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v-7.5a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v11a.5.5,0,0,1-.5.5A.506.506,0,0,1-19333,3340.5Zm-3-7.5a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v2Z" transform="translate(19337 -3329)" fill="#f51350"/>
                                </svg>
                            @endif
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @can('pos_manager')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('poin-of-sales.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['poin-of-sales.index', 'poin-of-sales.create'])}}">
                                        <span class="aiz-side-nav-text">{{translate('POS Manager')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('pos_configuration')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('poin-of-sales.activation')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('POS Configuration')}}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif

                <!-- Product -->
                @canany(['add_new_product', 'show_all_products','show_in_house_products','show_seller_products','show_digital_products','product_bulk_import','product_bulk_export','view_product_categories', 'view_all_brands', 'brand_bulk_upload','view_product_attributes','view_colors'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="13.714" viewBox="0 0 16 13.714">
                                    <g id="Layer_2" data-name="Layer 2" transform="translate(-2 -4)">
                                      <path id="Path_40719" data-name="Path 40719" d="M17.429,4H2.571A.571.571,0,0,0,2,4.571V8a.571.571,0,0,0,.571.571h.571v8.571a.571.571,0,0,0,.571.571H16.286a.571.571,0,0,0,.571-.571V8.571h.571A.571.571,0,0,0,18,8V4.571A.571.571,0,0,0,17.429,4ZM15.714,16.571H4.286v-8H15.714Zm1.143-9.143H3.143V5.143H16.857Z" fill="#575b6a"/>
                                      <path id="Path_40720" data-name="Path 40720" d="M12.571,15.143H16A.571.571,0,0,0,16,14H12.571a.571.571,0,0,0,0,1.143Z" transform="translate(-4.286 -4.286)" fill="#575b6a"/>
                                    </g>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Products')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @can('add_new_product')
                                <li class="aiz-side-nav-item">
                                    <a class="aiz-side-nav-link" href="{{route('products.create')}}">
                                        <span class="aiz-side-nav-text">{{translate('Add New product')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('show_all_products')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('products.all')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ translate('All Products') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('show_in_house_products')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('products.admin')}}" class="aiz-side-nav-link {{ areActiveRoutes(['products.admin', 'products.admin.edit']) }}" >
                                        <span class="aiz-side-nav-text">{{ translate('In House Products') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @hasrole('Tech Support')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('digitalproducts.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['digitalproducts.index', 'digitalproducts.create', 'digitalproducts.edit']) }}">
                                        <span class="aiz-side-nav-text">{{ translate('Digital Products') }}</span>
                                    </a>
                                </li>
                          @endhasrole
                            @if(get_setting('vendor_system_activation') == 1)
                                @can('show_seller_products')
                                    <li class="aiz-side-nav-item">
                                        <a href="javascript:void(0);" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Seller Product')}}</span>
                                            <span class="aiz-side-nav-arrow"></span>
                                        </a>
                                        <ul class="aiz-side-nav-list level-3">
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('products.seller','physical') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('All Products')}}</span>
                                                </a>
                                            </li>
                                            @hasrole('Tech Support')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('products.seller','digital') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Digital Products')}}</span>
                                                </a>
                                            </li>
                                            @endhasrole
                                        </ul>
                                    </li>
                                @endcan
                            @endif

                            @can('product_bulk_import')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('product_bulk_upload.index') }}" class="aiz-side-nav-link" >
                                        <span class="aiz-side-nav-text">{{ translate('Bulk Import') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('product_bulk_export')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('product_bulk_export.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Bulk Export')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_product_categories')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('categories.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['categories.index', 'categories.create', 'categories.edit'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Category')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('set_category_wise_discount')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('categories_wise_product_discount')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Category Based Discount')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @canany(['view_all_brands', 'brand_bulk_upload'])
                                <li class="aiz-side-nav-item">
                                    <a href="javascript:void(0);" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Brand')}}</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        @can('view_all_brands')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('brands.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['brands.index', 'brands.create', 'brands.edit'])}}">
                                                    <span class="aiz-side-nav-text">{{translate('All Brands')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('brand_bulk_upload')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('brand_bulk_upload.index') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Brand Bulk Import')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan

                            @can('view_product_attributes')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('attributes.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['attributes.index','attributes.create','attributes.edit','attributes.show','edit-attribute-value'.''])}}">
                                        <span class="aiz-side-nav-text">{{translate('Attribute')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_colors')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('colors')}}" class="aiz-side-nav-link {{ areActiveRoutes(['colors','colors.edit'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Colors')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @canany(['view_size_charts', 'view_measurement_points'])
                                <li class="aiz-side-nav-item">
                                    <a href="javascript:void(0);" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Size Guide')}}</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        @can('view_size_charts')
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('size-charts.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['size-charts.index', 'size-charts.create', 'size-charts.edit'])}}">
                                                <span class="aiz-side-nav-text">{{translate('Size Chart')}}</span>
                                            </a>
                                        </li>
                                        @endcan
                                        @can('view_measurement_points')
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('measurement-points.index') }}" class="aiz-side-nav-link">
                                                <span class="aiz-side-nav-text">{{translate('Measurement Points')}}</span>
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                <!-- Wholesale Product -->
                @if(addon_is_activated('wholesale'))
                    @canany(['add_wholesale_product','view_all_wholesale_products','view_inhouse_wholesale_products','view_sellers_wholesale_products'])
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <div class="aiz-side-nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                        <path id="Union_48" data-name="Union 48" d="M1.2,14.236a1.762,1.762,0,0,1,1.2-1.657V2c0-.325-.268-.823-.6-.823H.6C.268,1.176,0,1.031,0,.7V.647A.645.645,0,0,1,.6,0H2.4A1.407,1.407,0,0,1,3.6,1.41v9.65h10a1.4,1.4,0,0,1,1.2,1.518,1.757,1.757,0,0,1,1.165,2.01,1.8,1.8,0,0,1-3.566-.353,1.761,1.761,0,0,1,1.2-1.656v-.342H3.6v.342a1.754,1.754,0,0,1,1.165,2.01A1.784,1.784,0,0,1,3.338,15.97,1.927,1.927,0,0,1,3,16,1.782,1.782,0,0,1,1.2,14.236Zm12.4,0a.594.594,0,0,0,.6.588h0a.6.6,0,0,0,.6-.589c0-.389-.272-.5-.6-.617C13.872,13.732,13.6,13.846,13.6,14.235Zm-11.2,0a.6.6,0,0,0,.6.588H3a.6.6,0,0,0,.6-.589c0-.389-.272-.5-.6-.617C2.671,13.732,2.4,13.846,2.4,14.235Zm4.216-4.158A1.615,1.615,0,0,1,5,8.462V6.692A1.615,1.615,0,0,1,6.615,5.077h5.77A1.616,1.616,0,0,1,14,6.692V8.462a1.616,1.616,0,0,1-1.616,1.615ZM6.234,6.311a.542.542,0,0,0-.157.382V8.462A.538.538,0,0,0,6.615,9h5.77a.538.538,0,0,0,.538-.538V6.692a.536.536,0,0,0-.538-.538H6.612A.535.535,0,0,0,6.234,6.311ZM5.473,3.527A1.617,1.617,0,0,1,5,2.385V1.616A1.615,1.615,0,0,1,6.615,0H9.384A1.616,1.616,0,0,1,11,1.616v.769A1.615,1.615,0,0,1,9.384,4H6.612A1.614,1.614,0,0,1,5.473,3.527Zm.761-2.293a.542.542,0,0,0-.157.382v.769a.538.538,0,0,0,.538.538H9.384a.538.538,0,0,0,.539-.538V1.616a.542.542,0,0,0-.157-.382.536.536,0,0,0-.382-.157H6.612A.535.535,0,0,0,6.234,1.234Z" fill="#575b6a"/>
                                    </svg>
                                </div>
                                <span class="aiz-side-nav-text">{{translate('Wholesale Products')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.001" viewBox="0 0 16 14.001" class="mx-2">
                                        <path id="Union_49" data-name="Union 49" d="M-19322,3342.5v-5a2.007,2.007,0,0,0-2-2v1.5a3,3,0,0,1-3,3h-4v-10h4a3,3,0,0,1,3,3v1.5a3,3,0,0,1,3,3v5a.506.506,0,0,1-.5.5A.5.5,0,0,1-19322,3342.5Zm-11-2V3339h-3a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v-7.5a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v11a.5.5,0,0,1-.5.5A.506.506,0,0,1-19333,3340.5Zm-3-7.5a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v2Z" transform="translate(19337 -3329)" fill="#f51350"/>
                                    </svg>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                @can('add_wholesale_product')
                                    <li class="aiz-side-nav-item">
                                        <a class="aiz-side-nav-link" href="{{route('wholesale_product_create.admin')}}">
                                            <span class="aiz-side-nav-text">{{translate('Add New Wholesale Product')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_all_wholesale_products')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('wholesale_products.all')}}" class="aiz-side-nav-link {{ areActiveRoutes(['wholesale_product_edit.admin']) }}">
                                            <span class="aiz-side-nav-text">{{ translate('All Wholesale Products') }}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_inhouse_wholesale_products')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('wholesale_products.in_house')}}" class="aiz-side-nav-link {{ areActiveRoutes(['wholesale_product_edit.admin']) }}">
                                            <span class="aiz-side-nav-text">{{ translate('In House Wholesale Products') }}</span>
                                        </a>
                                    </li>
                                @endcan
                                @if (get_setting('vendor_system_activation') == 1)
                                    @can('view_sellers_wholesale_products')
                                        <li class="aiz-side-nav-item">
                                            <a href="{{route('wholesale_products.seller')}}" class="aiz-side-nav-link {{ areActiveRoutes(['wholesale_product_edit.admin']) }}">
                                                <span class="aiz-side-nav-text">{{ translate('Seller Wholesale Products') }}</span>
                                            </a>
                                        </li>
                                    @endcan
                                @endif
                            </ul>
                        </li>
                    @endcanany
                @endif

                  <!-- Customer Products -->
                @canany(['view_customer_products', 'moderate_customer_products', 'view_customer_product_analytics', 'manage_customer_product_settings', 'view_requested_products'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <path id="customer-products-icon" d="M2,2V14H14V2H2M3,3H13V13H3V3M5,5V7H7V5H5M8,5V7H10V5H8M11,5V7H13V5H11M5,8V10H7V8H5M8,8V10H10V8H8M11,8V10H13V8H11M5,11V13H7V11H5M8,11V13H10V11H8M11,11V13H13V11H11Z" fill="#575b6a"/>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{ translate('Customer Products') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_customer_products')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.customer-products.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.customer-products.index', 'admin.customer-products.show'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('All Products') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_customer_product_analytics')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.customer-products.analytics') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.customer-products.analytics'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('Analytics') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_requested_products')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('requested-products.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['requested-products.index', 'requested-products.create', 'requested-products.edit', 'requested-products.show'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Requested Products')}}</span>
                                    </a>
                                </li>
                            @endcan
                            <!-- @can('manage_customer_product_settings')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('admin.customer-products.settings') }}" class="aiz-side-nav-link {{ areActiveRoutes(['admin.customer-products.settings'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('Settings') }}</span>
                                    </a>
                                </li>
                            @endcan -->
                        </ul>
                    </li>
                @endcanany

                <!-- Sale -->
                @canany(['view_all_orders', 'view_inhouse_orders','view_seller_orders','view_pickup_point_orders'])
                     @php
                         $all_orders = \App\Models\Order::where('viewed', 0)->count();
                         $inhouse_orders = \App\Models\Order::where('viewed', 0)->where('seller_id',  \App\Models\User::where('user_type', 'admin')->first()->id)->count();
                         $seller_orders = \App\Models\Order::where('viewed', 0)->where('seller_id', '!=', \App\Models\User::where('user_type', 'admin')->first()->id)->count();
                     @endphp
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15.997" height="16" viewBox="0 0 15.997 16">
                                    <g id="Layer_2" data-name="Layer 2" transform="translate(-2 -1.994)">
                                      <path id="Path_40726" data-name="Path 40726" d="M4.857,12.571H3.714A1.714,1.714,0,0,0,2,14.285V20.57a1.714,1.714,0,0,0,1.714,1.714H4.857A1.714,1.714,0,0,0,6.571,20.57V14.285a1.714,1.714,0,0,0-1.714-1.714Zm.571,8a.571.571,0,0,1-.571.571H3.714a.571.571,0,0,1-.571-.571V14.285a.571.571,0,0,1,.571-.571H4.857a.571.571,0,0,1,.571.571Zm5.142-6.284H9.427A1.714,1.714,0,0,0,7.713,16V20.57a1.714,1.714,0,0,0,1.714,1.714H10.57a1.714,1.714,0,0,0,1.714-1.714V16A1.714,1.714,0,0,0,10.57,14.285Zm.571,6.284a.571.571,0,0,1-.571.571H9.427a.571.571,0,0,1-.571-.571V16a.571.571,0,0,1,.571-.571H10.57a.571.571,0,0,1,.571.571ZM16.283,12H15.14a1.714,1.714,0,0,0-1.714,1.714V20.57a1.714,1.714,0,0,0,1.714,1.714h1.143A1.714,1.714,0,0,0,18,20.57V13.714A1.714,1.714,0,0,0,16.283,12Zm.571,8.57a.571.571,0,0,1-.571.571H15.14a.571.571,0,0,1-.571-.571V13.714a.571.571,0,0,1,.571-.571h1.143a.571.571,0,0,1,.571.571Z" transform="translate(0 -4.289)" fill="#575b6a"/>
                                      <path id="Path_40727" data-name="Path 40727" d="M17.947,2.548a.571.571,0,0,0-.366-.24l-1.588-.3a.571.571,0,1,0-.213,1.122l.093.018L11.233,5.932l-5.45-2.18a.572.572,0,1,0-.424,1.062L11.072,7.1a.571.571,0,0,0,.506-.041L16.68,4l-.067.354a.571.571,0,0,0,.457.668.579.579,0,0,0,.107.01.571.571,0,0,0,.56-.465l.3-1.588A.568.568,0,0,0,17.947,2.548Z" transform="translate(-1.286)" fill="#575b6a"/>
                                    </g>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Sales')}}</span>
                            @if(isset($all_orders) && $all_orders > 0)<span class="badge badge-inline ml-2 badge-success">{{ translate('new') }}</span> @endif
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_all_orders')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('all_orders.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['all_orders.index', 'all_orders.show'])}}">
                                        <span class="aiz-side-nav-text">{{translate('All Orders')}}</span>
                                        @if(isset($all_orders) && $all_orders > 0)<span class="badge badge-info">{{ $all_orders }}</span> @endif
                                    </a>
                                </li>
                            @endcan
                            @can('view_inhouse_orders')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('inhouse_orders.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['inhouse_orders.index', 'inhouse_orders.show'])}}" >
                                        <span class="aiz-side-nav-text">{{translate('Inhouse orders')}}</span>
                                        @if(isset($inhouse_orders) && $inhouse_orders > 0)<span class="badge badge-info">{{ $inhouse_orders }}</span> @endif
                                    </a>
                                </li>
                            @endcan
                            @if (get_setting('vendor_system_activation') == 1)
                                @can('view_seller_orders')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('seller_orders.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['seller_orders.index', 'seller_orders.show'])}}">
                                            <span class="aiz-side-nav-text">{{translate('Seller Orders')}}</span>
                                            @if(isset($seller_orders ) && $seller_orders  > 0)<span class="badge badge-info">{{ $seller_orders  }}</span> @endif
                                        </a>
                                    </li>
                                @endcan
                            @endif
                            {{-- @hasrole('Tech Support')
                            @can('view_pickup_point_orders')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('pick_up_point.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pick_up_point.index','pick_up_point.order_show'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Pick-up Point Order')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @endhasrole --}}
                        </ul>
                    </li>
                @endcanany
                 @canany(['view_product_reviews'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                               <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="16" height="16" viewBox="0 0 275.593 275.593"><path d="m19.604 122.026-.049.579c-.175 4.807 1.295 9.084 4.145 12.043 4.182 4.333 10.921 5.327 17.373 2.413l25.104-13.654 24.626 13.42.483.238c2.495 1.127 5.003 1.701 7.442 1.701 3.846 0 7.369-1.461 9.929-4.119 2.854-2.959 4.322-7.236 4.145-12.043l-3.307-28.227 18.561-19.067.569-.665c3.547-4.669 4.625-10.132 2.959-14.986-1.664-4.856-5.869-8.503-11.527-10.013L91.84 45.072 79.735 20.704l-.425-.73c-3.274-5.008-8.188-7.885-13.479-7.885-5.003 0-9.787 2.623-13.124 7.196l-14.699 25.77-25.1 4.214-.784.177C6.507 51.051 2.361 54.771.734 59.651-.89 64.53.205 69.998 3.737 74.642l19.13 19.732zm-4.664-56.35c-.588-.863-.63-1.4-.602-1.491.033-.091.392-.501 1.405-.845l31.36-5.267 17.392-30.602c.693-.859 1.223-1.041 1.332-1.041.101 0 .621.205 1.281 1.104L82.276 58.06l34.368 5.523c.927.292 1.319.642 1.365.697.014.124-.074.63-.597 1.4L94.451 89.275l4.023 34.1c0 .443-.042.784-.091 1.018a4.6 4.6 0 0 1-.999-.306l-31.211-17.005-31.209 17.005a4.3 4.3 0 0 1-.999.301 5 5 0 0 1-.091-1.013l4.025-34.1zM263.299 49.642l-28.218-4.574-12.102-24.369-.43-.726c-3.271-5.008-8.186-7.885-13.474-7.885-5.003 0-9.787 2.623-13.124 7.196l-14.701 25.77-25.1 4.214-.784.177c-5.614 1.605-9.764 5.325-11.388 10.205s-.531 10.347 3.001 14.991l19.131 19.732-3.263 27.652-.051.579c-.178 4.807 1.297 9.084 4.144 12.043 4.183 4.333 10.926 5.327 17.371 2.413l25.104-13.654 24.628 13.42.485.238c2.492 1.127 5.003 1.701 7.439 1.701 3.846 0 7.369-1.461 9.932-4.119 2.852-2.959 4.317-7.236 4.145-12.043l-3.31-28.227 18.562-19.067.569-.665c3.547-4.669 4.625-10.132 2.959-14.986-1.659-4.854-5.869-8.506-11.525-10.016m-2.638 16.034-22.966 23.595 4.027 34.1c0 .444-.047.784-.094 1.018a4.5 4.5 0 0 1-.998-.306l-31.214-17.004-31.204 17.004a4.6 4.6 0 0 1-.999.301 4.6 4.6 0 0 1-.093-1.013l4.022-34.1-22.962-23.599c-.588-.866-.63-1.4-.602-1.491.032-.091.392-.501 1.404-.845l31.363-5.267 17.389-30.602c.691-.859 1.224-1.041 1.335-1.041.099 0 .621.206 1.279 1.104l15.168 30.525 34.368 5.523c.929.292 1.321.642 1.367.698.022.123-.071.63-.59 1.4M188.882 174.387l-28.212-4.574-12.107-24.367-.42-.732c-3.275-5.008-8.186-7.885-13.476-7.885-5.006 0-9.79 2.623-13.124 7.199l-14.701 25.767-25.104 4.219-.779.178c-5.61 1.601-9.759 5.32-11.383 10.197-1.631 4.882-.537 10.347 2.996 14.991l19.13 19.732-3.265 27.656-.046.579c-.178 4.808 1.295 9.082 4.142 12.041 4.187 4.341 10.931 5.32 17.378 2.413l25.104-13.651 24.618 13.418.481.233c2.497 1.134 5.008 1.703 7.443 1.703 3.851 0 7.37-1.465 9.932-4.116 2.852-2.959 4.322-7.233 4.145-12.041l-3.309-28.227 18.565-19.069.564-.663c3.547-4.672 4.63-10.137 2.959-14.99-1.66-4.85-5.865-8.504-11.531-10.011m-2.632 16.04-22.963 23.593 4.023 34.098c0 .452-.042.783-.089 1.021a5 5 0 0 1-.999-.308l-31.208-17.007-31.211 17.007a4.5 4.5 0 0 1-.999.299 5 5 0 0 1-.091-1.018l4.022-34.098-22.964-23.597c-.586-.868-.63-1.4-.603-1.494.04-.093.393-.504 1.407-.845l31.358-5.269 17.392-30.598c.693-.858 1.223-1.045 1.335-1.045.101 0 .621.205 1.279 1.105l15.17 30.523 34.363 5.525c.929.294 1.316.64 1.368.695.021.134-.068.638-.59 1.413"/></svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Reviews')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <!--Submenu-->
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_product_reviews')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('reviews.index')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('All Reviews')}}</span>
                                        </a>
                                    </li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany
                <!-- Deliver Boy Addon-->
                @if (addon_is_activated('delivery_boy'))
                    @canany(['view_all_delivery_boy','add_delivery_boy','delivery_boy_payment_history','collected_histories_from_delivery_boy','order_cancle_request_by_delivery_boy','delivery_boy_configuration'])
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <div class="aiz-side-nav-icon">
                                    <svg id="Group_28285" data-name="Group 28285" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                        <path id="Path_40728" data-name="Path 40728" d="M12.406,9.375h-.625v-.84a3.28,3.28,0,0,0,1.406-2.691V4.375h2.344a.469.469,0,0,0,0-.937H13.5a3.594,3.594,0,0,0-7.184.156v.313a.469.469,0,0,0,.313.442v1.5A3.28,3.28,0,0,0,8.031,8.535v.84H7.406a3.605,3.605,0,0,0-2.113.688H1.406a.469.469,0,0,0-.419.259L.049,12.2h0a.466.466,0,0,0-.05.209v3.125A.469.469,0,0,0,.469,16H15.531A.469.469,0,0,0,16,15.531V12.969A3.6,3.6,0,0,0,12.406,9.375ZM9.906.938a2.66,2.66,0,0,1,2.652,2.5h-5.3A2.66,2.66,0,0,1,9.906.938ZM7.562,5.844V4.375H12.25V5.844a2.344,2.344,0,0,1-4.688,0ZM9.906,9.125a3.271,3.271,0,0,0,.938-.137V10a.938.938,0,0,1-1.875,0V8.988A3.27,3.27,0,0,0,9.906,9.125ZM1.7,11H5.554l.469.938h-4.8ZM.937,12.875H6.312v2.188H.937Zm14.125,2.188H7.25V12.406A.466.466,0,0,0,7.2,12.2h0l-.836-1.672a2.638,2.638,0,0,1,1.042-.212h.652a1.875,1.875,0,0,0,3.7,0h.652a2.659,2.659,0,0,1,2.656,2.656Z" fill="#575b6a"/>
                                        <path id="Path_40729" data-name="Path 40729" d="M376.719,405h-1.25a.469.469,0,0,0,0,.938h1.25a.469.469,0,0,0,0-.937Z" transform="translate(-363.281 -392.344)" fill="#575b6a"/>
                                    </svg>
                                </div>
                                <span class="aiz-side-nav-text">{{translate('Delivery Boy')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.001" viewBox="0 0 16 14.001" class="mx-2">
                                        <path id="Union_49" data-name="Union 49" d="M-19322,3342.5v-5a2.007,2.007,0,0,0-2-2v1.5a3,3,0,0,1-3,3h-4v-10h4a3,3,0,0,1,3,3v1.5a3,3,0,0,1,3,3v5a.506.506,0,0,1-.5.5A.5.5,0,0,1-19322,3342.5Zm-11-2V3339h-3a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v-7.5a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v11a.5.5,0,0,1-.5.5A.506.506,0,0,1-19333,3340.5Zm-3-7.5a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v2Z" transform="translate(19337 -3329)" fill="#f51350"/>
                                    </svg>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                @can('view_all_delivery_boy')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('delivery-boys.index')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('All Delivery Boy')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('add_delivery_boy')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('delivery-boys.create')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Add Delivery Boy')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('delivery_boy_payment_history')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('delivery-boys-payment-histories')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Payment Histories')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('collected_histories_from_delivery_boy')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('delivery-boys-collection-histories')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Collected Histories')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('order_cancle_request_by_delivery_boy')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('delivery-boy.cancel-request')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Cancel Request')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('delivery_boy_configuration')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('delivery-boy-configuration')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Configuration')}}</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                @endif

                <!-- Refund addon -->
                @if (addon_is_activated('refund_request'))
                    @canany(['view_refund_requests','view_approved_refund_requests','view_rejected_refund_requests','refund_request_configuration'])
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <div class="aiz-side-nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                        <path id="_4436b8ef9250481406399210799cb7f1" data-name="4436b8ef9250481406399210799cb7f1" d="M19.25,11.25a8.031,8.031,0,0,1-15.995,1,.688.688,0,0,1,1.365-.169A6.643,6.643,0,1,0,7.112,6.039h.866a.686.686,0,1,1,0,1.371H5.384A.687.687,0,0,1,4.7,6.724V4.138a.688.688,0,0,1,1.376,0v.987A8.024,8.024,0,0,1,19.25,11.25ZM11.278,6.907a.687.687,0,0,0-.688.686v.253a2.053,2.053,0,0,0-1.824,2.247,2.146,2.146,0,0,0,2.175,1.842h.8a.686.686,0,1,1,0,1.371h-1.6a.686.686,0,1,0,0,1.371h.458v.229a.688.688,0,0,0,1.376,0v-.26a2.113,2.113,0,0,0,1.824-1.811,2.062,2.062,0,0,0-2.053-2.272h-.917a.686.686,0,1,1,0-1.371h1.609a.686.686,0,1,0,0-1.371h-.462V7.593A.687.687,0,0,0,11.278,6.907Z" transform="translate(-3.25 -3.25)" fill="#575b6a"/>
                                    </svg>
                                </div>
                                <span class="aiz-side-nav-text">{{ translate('Refunds') }}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.001" viewBox="0 0 16 14.001" class="mx-2">
                                        <path id="Union_49" data-name="Union 49" d="M-19322,3342.5v-5a2.007,2.007,0,0,0-2-2v1.5a3,3,0,0,1-3,3h-4v-10h4a3,3,0,0,1,3,3v1.5a3,3,0,0,1,3,3v5a.506.506,0,0,1-.5.5A.5.5,0,0,1-19322,3342.5Zm-11-2V3339h-3a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v-7.5a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v11a.5.5,0,0,1-.5.5A.506.506,0,0,1-19333,3340.5Zm-3-7.5a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v2Z" transform="translate(19337 -3329)" fill="#f51350"/>
                                    </svg>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                @can('view_refund_requests')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('refund_requests_all')}}" class="aiz-side-nav-link {{ areActiveRoutes(['refund_requests_all', 'reason_show'])}}">
                                            <span class="aiz-side-nav-text">{{translate('Refund Requests')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_approved_refund_requests')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('paid_refund')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Approved Refunds')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_rejected_refund_requests')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('rejected_refund')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Rejected Refunds')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('refund_request_configuration')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('refund_time_config')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Refund Configuration')}}</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                @endif

                <!-- Customers -->
                @canany(['view_all_customers','view_classified_products','view_classified_packages'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <path id="Path_40769" data-name="Path 40769" d="M8,10.667A2.667,2.667,0,1,1,10.667,8,2.667,2.667,0,0,1,8,10.667Zm0-4A1.333,1.333,0,1,0,9.333,8,1.333,1.333,0,0,0,8,6.667Zm4,8.667a4,4,0,1,0-8,0,.667.667,0,0,0,1.333,0,2.667,2.667,0,1,1,5.333,0,.667.667,0,0,0,1.333,0Zm0-10a2.667,2.667,0,1,1,2.667-2.667A2.667,2.667,0,0,1,12,5.333Zm0-4a1.333,1.333,0,1,0,1.333,1.333A1.333,1.333,0,0,0,12,1.333ZM16,10a4,4,0,0,0-4-4,.667.667,0,0,0,0,1.333A2.667,2.667,0,0,1,14.667,10,.667.667,0,1,0,16,10ZM4,5.333A2.667,2.667,0,1,1,6.667,2.667,2.667,2.667,0,0,1,4,5.333Zm0-4A1.333,1.333,0,1,0,5.333,2.667,1.333,1.333,0,0,0,4,1.333ZM1.333,10A2.667,2.667,0,0,1,4,7.333.667.667,0,0,0,4,6a4,4,0,0,0-4,4,.667.667,0,0,0,1.333,0Z" fill="#575b6a"/>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{ translate('Customers') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_all_customers')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('customers.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ translate('Customer list') }}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany



                 <!-- Wallet System -->
                 @canany(['view_all_customers','view_classified_products','view_classified_packages','features_activation'])
                 <li class="aiz-side-nav-item">
                     <a href="#" class="aiz-side-nav-link">
                         <div class="aiz-side-nav-icon">
                            <svg id="Group_8103" data-name="Group 8103" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="16" viewBox="0 0 16 16">
                                <defs>
                                    <clipPath id="clip-path">
                                    <rect id="Rectangle_1386" data-name="Rectangle 1386" width="16" height="16" fill="#b5b5bf"/>
                                    </clipPath>
                                </defs>
                                <g id="Group_8102" data-name="Group 8102" clip-path="url(#clip-path)">
                                    <path id="Path_2936" data-name="Path 2936" d="M13.5,4H13V2.5A2.5,2.5,0,0,0,10.5,0h-8A2.5,2.5,0,0,0,0,2.5v11A2.5,2.5,0,0,0,2.5,16h11A2.5,2.5,0,0,0,16,13.5v-7A2.5,2.5,0,0,0,13.5,4M2.5,1h8A1.5,1.5,0,0,1,12,2.5V4H2.5a1.5,1.5,0,0,1,0-3M15,11H10a1,1,0,0,1,0-2h5Zm0-3H10a2,2,0,0,0,0,4h5v1.5A1.5,1.5,0,0,1,13.5,15H2.5A1.5,1.5,0,0,1,1,13.5v-9A2.5,2.5,0,0,0,2.5,5h11A1.5,1.5,0,0,1,15,6.5Z" fill="#b5b5bf"/>
                                </g>
                            </svg>
                         </div>
                         <span class="aiz-side-nav-text">{{ translate('Wallet System') }}</span>
                         <span class="aiz-side-nav-arrow"></span>
                     </a>
                     <ul class="aiz-side-nav-list level-2">
                        @can('view_all_offline_wallet_recharges')
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('offline_wallet_recharge_request.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{translate('Offline Wallet Recharge Requests')}}</span>
                                </a>
                            </li>
                        @endcan
                        @can('wallet_transaction_report')
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('wallet-history.index') }}" class="aiz-side-nav-link">
                                <span class="aiz-side-nav-text">{{ translate('Wallet trasnaction history') }}</span>
                            </a>
                        </li>
                    @endcan
                    @can('features_activation')
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('wallet_configuration.index') }}" class="aiz-side-nav-link">
                            <span class="aiz-side-nav-text">{{ translate('Wallet settings') }}</span>
                        </a>
                    </li>
                @endcan
                     </ul>
                 </li>
             @endcanany

                <!-- Sellers -->
                @if (get_setting('vendor_system_activation') == 1)
                    @canany(['view_all_seller','seller_payment_history','view_seller_payout_requests','seller_commission_configuration','view_all_seller_packages','seller_verification_form_configuration'])
                    @can('view_all_seller')
                        @php
                            $sellers = \App\Models\Shop::where('verification_status', 0)->where('verification_info', '!=', null)->count();
                        @endphp
                    @endcan
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <div class="aiz-side-nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                        <path id="ef567a7fa3ca8f4541f8ab7b62352aa6" d="M19,9.625a.638.638,0,0,0-.079-.307l-2.779-5A.614.614,0,0,0,15.606,4H6.394a.614.614,0,0,0-.536.318l-2.779,5A.638.638,0,0,0,3,9.625a2.5,2.5,0,0,0,1.231,2.153V18.75A1.24,1.24,0,0,0,5.462,20H9.08a1.24,1.24,0,0,0,1.231-1.25V16.058a.759.759,0,0,1,.615-.773.684.684,0,0,1,.534.176.706.706,0,0,1,.229.521V18.75A1.24,1.24,0,0,0,12.92,20h3.618a1.24,1.24,0,0,0,1.231-1.25V11.777A2.5,2.5,0,0,0,19,9.625Zm-1.239.149a1.23,1.23,0,0,1-2.453-.149.578.578,0,0,0-.017-.086.548.548,0,0,0-.006-.084L14.114,5.25h1.132ZM9.164,5.25h1.22V9.625a1.23,1.23,0,0,1-2.455.063Zm2.451,0h1.22l1.235,4.437a1.23,1.23,0,0,1-2.455-.062Zm-4.862,0H7.886l-1.169,4.2a.548.548,0,0,0-.006.084.578.578,0,0,0-.018.086,1.23,1.23,0,0,1-2.453.149Zm9.785,13.5H12.92V15.981a1.964,1.964,0,0,0-.635-1.446,1.9,1.9,0,0,0-1.482-.491A2,2,0,0,0,9.08,16.061V18.75H5.462V12.125a2.439,2.439,0,0,0,1.846-.848A2.419,2.419,0,0,0,11,11.261a2.419,2.419,0,0,0,3.692.016,2.439,2.439,0,0,0,1.846.848Z" transform="translate(-3 -4)" fill="#575b6a"/>
                                    </svg>
                                </div>
                                <span class="aiz-side-nav-text">{{ translate('Sellers') }}</span>
                                @if(isset($sellers) && $sellers > 0)<span class="badge badge-inline ml-2 badge-success">{{ translate('new') }}</span> @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                @can('view_all_seller')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('sellers.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['sellers.index', 'sellers.create', 'sellers.edit', 'sellers.payment_history','sellers.approved','sellers.profile_modal','sellers.show_verification_request'])}}">
                                            <span class="aiz-side-nav-text">{{ translate('All Seller') }}</span>
                                            @if($sellers > 0)<span class="badge badge-info">{{ $sellers }}</span> @endif
                                        </a>
                                    </li>
                                @endcan
                                @can('seller_payment_history')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('sellers.payment_histories') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ translate('Payouts') }}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_seller_payout_requests')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('withdraw_requests_all') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ translate('Payout Requests') }}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('seller_commission_configuration')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('business_settings.vendor_commission') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ translate('Seller Commission') }}</span>
                                        </a>
                                    </li>
                                @endcan
                                @if (addon_is_activated('seller_subscription'))
                                    @can('view_all_seller_packages')
                                        <li class="aiz-side-nav-item">
                                            <a href="{{ route('seller_packages.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['seller_packages.index', 'seller_packages.create', 'seller_packages.edit'])}}">
                                                <span class="aiz-side-nav-text">{{ translate('Seller Packages') }}</span>
                                                @if (env("DEMO_MODE") == "On")
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.001" viewBox="0 0 16 14.001" class="mx-2">
                                                        <path id="Union_49" data-name="Union 49" d="M-19322,3342.5v-5a2.007,2.007,0,0,0-2-2v1.5a3,3,0,0,1-3,3h-4v-10h4a3,3,0,0,1,3,3v1.5a3,3,0,0,1,3,3v5a.506.506,0,0,1-.5.5A.5.5,0,0,1-19322,3342.5Zm-11-2V3339h-3a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v-7.5a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v11a.5.5,0,0,1-.5.5A.506.506,0,0,1-19333,3340.5Zm-3-7.5a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v2Z" transform="translate(19337 -3329)" fill="#f51350"/>
                                                    </svg>
                                                @endif
                                            </a>
                                        </li>
                                    @endcan
                                @endif
                                @can('seller_verification_form_configuration')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('seller_verification_form.index') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{ translate('Seller Verification Form') }}</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                @endif

                {{-- Uploads Files --}}
                <li class="aiz-side-nav-item">
                    <a href="{{ route('uploaded-files.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['uploaded-files.create'])}}">
                        <div class="aiz-side-nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <g id="layer1" transform="translate(-0.53 -0.53)">
                                  <path id="path3159" d="M3.386.53A2.862,2.862,0,0,0,.53,3.386V13.67a2.865,2.865,0,0,0,2.856,2.86H13.67a2.869,2.869,0,0,0,2.86-2.86V3.386A2.865,2.865,0,0,0,13.67.53Zm0,1.143H13.67a1.7,1.7,0,0,1,1.718,1.713V13.67a1.7,1.7,0,0,1-1.718,1.718H3.386A1.7,1.7,0,0,1,1.673,13.67V3.386A1.7,1.7,0,0,1,3.386,1.673ZM8.12,3.557,5.34,6.37a.572.572,0,0,0,0,.809.564.564,0,0,0,.81,0l1.8-1.824V10.8a.571.571,0,0,0,1.143,0V5.347l1.8,1.829a.571.571,0,0,0,.81-.806L8.935,3.557a.511.511,0,0,0-.815,0Zm-4.156,8.97a.571.571,0,0,0,0,1.143h9.128a.571.571,0,0,0,0-1.143Z" fill="#575b6a"/>
                                </g>
                            </svg>
                        </div>
                        <span class="aiz-side-nav-text">{{ translate('Uploaded Files') }}</span>
                    </a>
                </li>

                <!-- Reports -->
                @canany(['earning_report', 'in_house_product_sale_report','seller_products_sale_report','products_stock_report','product_wishlist_report','user_search_report','commission_history_report','wallet_transaction_report', 'order_refund_report'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg id="stats_3916778" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <path id="Path_40739" data-name="Path 40739" d="M16,16H2a2,2,0,0,1-2-2V0H1.333V14A.667.667,0,0,0,2,14.667H16Z" fill="#575b6a"/>
                                    <rect id="Rectangle_21340" data-name="Rectangle 21340" width="1.333" height="6" transform="translate(9.333 7.333)" fill="#575b6a"/>
                                    <rect id="Rectangle_21341" data-name="Rectangle 21341" width="1.333" height="6" transform="translate(4 7.333)" fill="#575b6a"/>
                                    <rect id="Rectangle_21342" data-name="Rectangle 21342" width="1.333" height="9.333" transform="translate(12 4)" fill="#575b6a"/>
                                    <rect id="Rectangle_21343" data-name="Rectangle 21343" width="1.333" height="9.333" transform="translate(6.667 4)" fill="#575b6a"/>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{ translate('Reports') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @can('earning_report')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('earning_payout_report.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ translate('Earning Report') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('in_house_product_sale_report')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('in_house_sale_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['in_house_sale_report.index'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('In House Product Sale') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('seller_products_sale_report')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('seller_sale_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['seller_sale_report.index'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('Seller Products Sale') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('products_stock_report')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('stock_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['stock_report.index'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('Products Stock') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('product_wishlist_report')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('wish_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['wish_report.index'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('Products wishlist') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('user_search_report')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('user_search_report.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['user_search_report.index'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('User Searches') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('commission_history_report')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('commission-log.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ translate('Commission History') }}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('wallet_transaction_report')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('wallet-history.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ translate('Wallet Recharge History') }}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                {{-- <!--Blog System-->
                @canany(['view_blogs','view_blog_categories'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <path id="Path_40771" data-name="Path 40771" d="M9.688,16H3.75A3.754,3.754,0,0,1,0,12.25V3.75A3.754,3.754,0,0,1,3.75,0h8.5A3.754,3.754,0,0,1,16,3.75V9.734a.625.625,0,0,1-1.25,0V3.75a2.5,2.5,0,0,0-2.5-2.5H3.75a2.5,2.5,0,0,0-2.5,2.5v8.5a2.5,2.5,0,0,0,2.5,2.5H9.688a.625.625,0,0,1,0,1.25ZM12.875,3.938a.625.625,0,0,0-.625-.625H6.531a.625.625,0,0,0,0,1.25H12.25A.625.625,0,0,0,12.875,3.938Zm0,2.5a.625.625,0,0,0-.625-.625H3.75a.625.625,0,0,0,0,1.25h8.5A.625.625,0,0,0,12.875,6.438Zm-6.25,2.5A.625.625,0,0,0,6,8.313H3.75a.625.625,0,0,0,0,1.25H6A.625.625,0,0,0,6.625,8.938Zm-3.5-5.062a.781.781,0,1,0,.781-.781A.781.781,0,0,0,3.125,3.875ZM15.332,15.332a2.284,2.284,0,0,0,0-3.226L13.141,9.915a4.506,4.506,0,0,0-2.31-1.236L9.06,8.325a.625.625,0,0,0-.735.735l.354,1.771a4.506,4.506,0,0,0,1.236,2.31l2.191,2.191a2.281,2.281,0,0,0,3.226,0ZM10.586,9.9a3.259,3.259,0,0,1,1.671.894l2.191,2.191a1.031,1.031,0,1,1-1.458,1.458L10.8,12.257A3.26,3.26,0,0,1,9.9,10.586l-.17-.852Z" fill="#575b6a"/>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{ translate('Blog System') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_blogs')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('blog.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['blog.create', 'blog.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('All Posts') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_blog_categories')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('blog-category.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['blog-category.create', 'blog-category.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('Categories') }}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany --}}

                <!-- Faq System -->
                @canany(['view_faqs','view_faq_categories'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 3C7.04 3 3 7.04 3 12C3 16.96 7.04 21 12 21C16.96 21 21 16.96 21 12C21 7.04 16.96 3 12 3ZM12 19.5C7.86 19.5 4.5 16.14 4.5 12C4.5 7.86 7.86 4.5 12 4.5C16.14 4.5 19.5 7.86 19.5 12C19.5 16.14 16.14 19.5 12 19.5ZM14.3 7.7C14.91 8.31 15.25 9.13 15.25 10C15.25 10.87 14.91 11.68 14.3 12.3C13.87 12.73 13.33 13.03 12.75 13.16V13.5C12.75 13.91 12.41 14.25 12 14.25C11.59 14.25 11.25 13.91 11.25 13.5V12.5C11.25 12.09 11.59 11.75 12 11.75C12.47 11.75 12.91 11.57 13.24 11.24C13.57 10.91 13.75 10.47 13.75 10C13.75 9.53 13.57 9.09 13.24 8.76C12.58 8.1 11.43 8.1 10.77 8.76C10.44 9.09 10.26 9.53 10.26 10C10.26 10.41 9.92 10.75 9.51 10.75C9.1 10.75 8.76 10.41 8.76 10C8.76 9.13 9.1 8.32 9.71 7.7C10.94 6.47 13.08 6.47 14.31 7.7H14.3ZM13 16.25C13 16.8 12.55 17.25 12 17.25C11.45 17.25 11 16.8 11 16.25C11 15.7 11.45 15.25 12 15.25C12.55 15.25 13 15.7 13 16.25Z" fill="#000000"/>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{ translate('FAQs') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_faqs')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('faqs.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['faqs.create', 'faqs.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('All FAQs') }}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany


                <!-- marketing -->
                @canany(['view_all_flash_deals',
                            'view_all_dynamic_popups',
                                'view_all_custom_alerts',
                                    'send_newsletter',
                                        'notification_settings',
                                            'view_all_notification_types',
                                                'send_custom_notification',
                                                    'view_custom_notification_history',
                                                        'send_bulk_sms',
                                                            'view_all_subscribers',
                                                                'view_all_coupons'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <g id="_8dbc7a38f7bdee3f0be2c44d010760a2" data-name="8dbc7a38f7bdee3f0be2c44d010760a2" transform="translate(0 -4.027)">
                                      <path id="Path_40740" data-name="Path 40740" d="M38.286,16.393a.555.555,0,0,1-.344-.119L34.032,13.2a.557.557,0,0,1-.213-.438v-5.1a.556.556,0,0,1,.212-.438l3.91-3.074a.557.557,0,0,1,.9.438V15.836a.556.556,0,0,1-.556.557Zm-3.354-3.9,2.8,2.2V5.73l-2.8,2.2Z" transform="translate(-25.364 0)" fill="#575b6a"/>
                                      <path id="Path_40741" data-name="Path 40741" d="M9.011,22.556H3.093a3.1,3.1,0,0,1,0-6.192H9.011a.557.557,0,0,1,.557.557V22A.557.557,0,0,1,9.011,22.556ZM3.093,17.478a1.982,1.982,0,0,0,0,3.964H8.455V17.478Z" transform="translate(0 -9.25)" fill="#575b6a"/>
                                      <path id="Path_40742" data-name="Path 40742" d="M10.2,31.9a1.895,1.895,0,0,1-1.847-1.5l-.974-5.455a.557.557,0,1,1,1.089-.229l.975,5.455a.777.777,0,1,0,1.521-.32l-.824-4.74a.557.557,0,1,1,1.089-.229l.824,4.74A1.894,1.894,0,0,1,10.2,31.9Zm8.487-7.6h-.862a.557.557,0,0,1,0-1.114h.862a1.105,1.105,0,0,0,1.1-1.105,1.106,1.106,0,0,0-1.1-1.105h-.862a.557.557,0,0,1,0-1.114h.862a2.22,2.22,0,0,1,1.566,3.79A2.2,2.2,0,0,1,18.683,24.3Z" transform="translate(-4.9 -11.875)" fill="#575b6a"/>
                                    </g>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{ translate('Marketing') }}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_all_flash_deals')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('flash_deals.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['flash_deals.index', 'flash_deals.create', 'flash_deals.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('Flash deals') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_dynamic_popups')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('dynamic-popups.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['dynamic-popups.index', 'dynamic-popups.create', 'dynamic-popups.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('Dynamic Pop-up') }}</span>
                                    </a>
                                </li>
                            @endcan
                            {{-- @hasrole('Tech Support')
                            @can('view_all_custom_alerts')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('custom-alerts.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['custom-alerts.index', 'custom-alerts.create', 'custom-alerts.edit'])}}">
                                        <span class="aiz-side-nav-text">{{ translate('Custom Alert') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @endhasrole --}}
                            @can('send_newsletter')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('newsletters.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ translate('Newsletters') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @canany(['notification_settings','view_all_notification_types','send_custom_notification', 'view_custom_notification_history'])
                                <li class="aiz-side-nav-item">
                                    <a href="javascript:void(0);" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Notification')}}</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        @hasrole('Tech Support')
                                        @can('notification_settings')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('notification.settings') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Settings')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @endhasrole
                                        @can('view_all_notification_types')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('notification-type.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['notification-type.edit'])}}">
                                                    <span class="aiz-side-nav-text">{{translate('Notification Types')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('send_custom_notification')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('custom_notification') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Customer Custom Notification')}}</span>
                                                </a>
                                            </li>
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('shop_custom_notification') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Shop Custom Notification')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('view_custom_notification_history')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('custom_notification.history') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Custom Notification History')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany
                            @if (addon_is_activated('otp_system') && auth()->user()->can('send_bulk_sms'))
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('sms.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ translate('Bulk SMS') }}</span>
                                        @if (env("DEMO_MODE") == "On")
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.001" viewBox="0 0 16 14.001" class="mx-2">
                                                <path id="Union_49" data-name="Union 49" d="M-19322,3342.5v-5a2.007,2.007,0,0,0-2-2v1.5a3,3,0,0,1-3,3h-4v-10h4a3,3,0,0,1,3,3v1.5a3,3,0,0,1,3,3v5a.506.506,0,0,1-.5.5A.5.5,0,0,1-19322,3342.5Zm-11-2V3339h-3a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v-7.5a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v11a.5.5,0,0,1-.5.5A.506.506,0,0,1-19333,3340.5Zm-3-7.5a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v2Z" transform="translate(19337 -3329)" fill="#f51350"/>
                                            </svg>
                                        @endif
                                    </a>
                                </li>
                            @endif
                            {{-- @can('view_all_subscribers')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('subscribers.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ translate('Subscribers') }}</span>
                                    </a>
                                </li>
                            @endcan --}}
                            @if (get_setting('coupon_system') == 1 && auth()->user()->can('view_all_coupons') )
                            <li class="aiz-side-nav-item">
                                <a href="{{route('coupon.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['coupon.index','coupon.create','coupon.edit'])}}">
                                    <span class="aiz-side-nav-text">{{ translate('Coupon') }}</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                @endcanany

                <!-- Support -->
                @canany(['view_all_product_conversations','view_all_product_queries'])

                @can('view_all_product_conversations')
                @php
                 $admins = \App\Models\User::where('user_type', 'admin')->get()->flatMap(function($admin){
                        return collect($admin->id);
                    });
                    $conversation = \App\Models\Conversation::whereIn('receiver_id', $admins)->where('receiver_viewed', '0')->count();
                @endphp
                @endcan
                @can('view_all_product_queries')
                    @php
                        $queries = \App\Models\ProductQuery::whereIn('seller_id', $admins)->where('reply', null)->count();
                    @endphp
                @endcan
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <g id="Group_28286" data-name="Group 28286" transform="translate(0)">
                                      <path id="Path_40743" data-name="Path 40743" d="M16,9.125a3.122,3.122,0,0,0-1.255-2.5,6.9,6.9,0,0,0-1.94-4.6,6.725,6.725,0,0,0-9.61,0,6.9,6.9,0,0,0-1.94,4.6,3.124,3.124,0,0,0,1.87,5.627h1.25A.625.625,0,0,0,5,11.625v-5A.625.625,0,0,0,4.375,6H3.125a3.129,3.129,0,0,0-.569.052,5.487,5.487,0,0,1,10.887,0A3.129,3.129,0,0,0,12.875,6h-1.25A.625.625,0,0,0,11,6.625v5a.625.625,0,0,0,.625.625h.625v.625a1.877,1.877,0,0,1-1.875,1.875H8A.625.625,0,0,0,8,16h2.375A3.129,3.129,0,0,0,13.5,12.875v-.688A3.13,3.13,0,0,0,16,9.125ZM3.75,7.25V11H3.125a1.875,1.875,0,0,1,0-3.75ZM12.875,11H12.25V7.25h.625a1.875,1.875,0,1,1,0,3.75Z" fill="#575b6a"/>
                                      <path id="Path_40744" data-name="Path 40744" d="M197.875,113.25a.626.626,0,0,1,.625.625.618.618,0,0,1-.137.391,4.365,4.365,0,0,0-1.113,2.746v.613a.625.625,0,0,0,1.25,0v-.613a3.186,3.186,0,0,1,.838-1.964A1.875,1.875,0,1,0,196,113.875a.625.625,0,0,0,1.25,0A.626.626,0,0,1,197.875,113.25Z" transform="translate(-189.875 -108.5)" fill="#575b6a"/>
                                      <circle id="Ellipse_891" data-name="Ellipse 891" cx="0.625" cy="0.625" r="0.625" transform="translate(7.375 11)" fill="#575b6a"/>
                                    </g>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Support')}}</span>
                            @if( (isset($conversation) && $conversation > 0) || (isset($queries) && $queries > 0) )<span class="badge badge-inline ml-2 badge-success">{{ translate('new') }}</span>@endif
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_all_product_conversations')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('conversations.admin_index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['conversations.admin_index', 'conversations.admin_show'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Conversations')}}</span>
                                        @if (isset($conversation) && $conversation > 0)
                                            <span class="badge badge-info">{{$conversation }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endcan
                            {{-- @if (get_setting('product_query_activation') == 1)
                                @can('view_all_product_queries')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('product_query.index') }}"
                                            class="aiz-side-nav-link {{ areActiveRoutes(['product_query.index','product_query.show']) }}">
                                            <span class="aiz-side-nav-text">{{ translate('Product Queries') }}</span>
                                            @if (isset($queries) && $queries > 0)
                                            <span class="badge badge-info">{{ $queries }}</span>
                                        @endif
                                        </a>
                                    </li>
                                @endcan
                            @endif --}}
                        </ul>
                    </li>
                @endcanany

                <!-- Affiliate Addon -->
                @if (addon_is_activated('affiliate_system'))
                    @canany(['affiliate_registration_form_config','affiliate_configurations','view_affiliate_users','view_all_referral_users','view_affiliate_withdraw_requests','view_affiliate_logs'])
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <div class="aiz-side-nav-icon">
                                    <svg id="Group_28297" data-name="Group 28297" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                        <path id="Path_40762" data-name="Path 40762" d="M43.75,273.875a1.875,1.875,0,1,0-1.875,1.875A1.877,1.877,0,0,0,43.75,273.875Zm-1.875.625a.625.625,0,1,1,.625-.625A.626.626,0,0,1,41.875,274.5Z" transform="translate(-38.75 -263.5)" fill="#575b6a"/>
                                        <path id="Path_40763" data-name="Path 40763" d="M3.125,392A3.129,3.129,0,0,0,0,395.125a.625.625,0,0,0,.625.625h5a.625.625,0,0,0,.625-.625A3.129,3.129,0,0,0,3.125,392Zm-1.768,2.5a1.875,1.875,0,0,1,3.536,0Z" transform="translate(0 -379.75)" fill="#575b6a"/>
                                        <path id="Path_40764" data-name="Path 40764" d="M355.75,273.875a1.875,1.875,0,1,0-1.875,1.875A1.877,1.877,0,0,0,355.75,273.875Zm-1.875.625a.625.625,0,1,1,.625-.625A.626.626,0,0,1,353.875,274.5Z" transform="translate(-341 -263.5)" fill="#575b6a"/>
                                        <path id="Path_40765" data-name="Path 40765" d="M315.125,392A3.129,3.129,0,0,0,312,395.125a.625.625,0,0,0,.625.625h5a.625.625,0,0,0,.625-.625A3.129,3.129,0,0,0,315.125,392Zm-1.768,2.5a1.875,1.875,0,0,1,3.536,0Z" transform="translate(-302.25 -379.75)" fill="#575b6a"/>
                                        <path id="Path_40766" data-name="Path 40766" d="M199.75,1.875a1.875,1.875,0,1,0-1.875,1.875A1.877,1.877,0,0,0,199.75,1.875Zm-1.875.625a.625.625,0,1,1,.625-.625A.626.626,0,0,1,197.875,2.5Z" transform="translate(-189.875)" fill="#575b6a"/>
                                        <path id="Path_40767" data-name="Path 40767" d="M156.625,123.75h5a.625.625,0,0,0,.625-.625,3.125,3.125,0,0,0-6.25,0A.625.625,0,0,0,156.625,123.75Zm2.5-2.5a1.878,1.878,0,0,1,1.768,1.25h-3.536A1.878,1.878,0,0,1,159.125,121.25Z" transform="translate(-151.125 -116.25)" fill="#575b6a"/>
                                        <path id="Path_40768" data-name="Path 40768" d="M180.893,279.472a.625.625,0,0,0-.173-.867l-1.6-1.064v-.915a.625.625,0,0,0-1.25,0v.915l-1.6,1.064a.625.625,0,1,0,.693,1.04l1.528-1.019,1.528,1.019A.625.625,0,0,0,180.893,279.472Z" transform="translate(-170.498 -267.375)" fill="#575b6a"/>
                                    </svg>
                                </div>
                                <span class="aiz-side-nav-text">{{translate('Affiliate System')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.001" viewBox="0 0 16 14.001" class="mx-2">
                                        <path id="Union_49" data-name="Union 49" d="M-19322,3342.5v-5a2.007,2.007,0,0,0-2-2v1.5a3,3,0,0,1-3,3h-4v-10h4a3,3,0,0,1,3,3v1.5a3,3,0,0,1,3,3v5a.506.506,0,0,1-.5.5A.5.5,0,0,1-19322,3342.5Zm-11-2V3339h-3a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v-7.5a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v11a.5.5,0,0,1-.5.5A.506.506,0,0,1-19333,3340.5Zm-3-7.5a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v2Z" transform="translate(19337 -3329)" fill="#f51350"/>
                                    </svg>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                @can('affiliate_registration_form_config')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('affiliate.configs')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Affiliate Registration Form')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('affiliate_configurations')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('affiliate.index')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Affiliate Configurations')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_affiliate_users')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('affiliate.users')}}" class="aiz-side-nav-link {{ areActiveRoutes(['affiliate.users', 'affiliate_users.show_verification_request', 'affiliate_user.payment_history'])}}">
                                            <span class="aiz-side-nav-text">{{translate('Affiliate Users')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_all_referral_users')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('refferals.users')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Referral Users')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_affiliate_withdraw_requests')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('affiliate.withdraw_requests')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Affiliate Withdraw Requests')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_affiliate_logs')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('affiliate.logs.admin')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Affiliate Logs')}}</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                @endif

                <!-- Club Point Addon-->
                @if (addon_is_activated('club_point'))
                    @canany(['club_point_configurations','set_club_points','view_users_club_points'])
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <div class="aiz-side-nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                        <g id="Group_28289" data-name="Group 28289" transform="translate(-24 -896)">
                                          <g id="Group_28287" data-name="Group 28287" transform="translate(28 900)">
                                            <path id="Path_40745" data-name="Path 40745" d="M6,0a6,6,0,1,0,6,6A6.007,6.007,0,0,0,6,0ZM6,10.909A4.909,4.909,0,1,1,10.909,6,4.915,4.915,0,0,1,6,10.909Z" fill="#575b6a"/>
                                            <path id="Path_40746" data-name="Path 40746" d="M76.442,72.034l-1.726-.251-.772-1.564a.545.545,0,0,0-.978,0l-.772,1.564-1.726.251a.545.545,0,0,0-.3.93l1.249,1.218L71.119,75.9a.545.545,0,0,0,.791.575l1.544-.812L75,76.477a.545.545,0,0,0,.254.063h0a.546.546,0,0,0,.531-.667l-.29-1.69,1.249-1.218a.545.545,0,0,0-.3-.93ZM74.528,73.6a.545.545,0,0,0-.157.483l.157.913-.82-.431a.545.545,0,0,0-.508,0l-.82.431.157-.913a.545.545,0,0,0-.157-.483l-.663-.646.916-.133a.545.545,0,0,0,.411-.3l.41-.83.41.83a.545.545,0,0,0,.411.3l.916.133Z" transform="translate(-67.454 -67.373)" fill="#575b6a"/>
                                          </g>
                                          <path id="Subtraction_228" data-name="Subtraction 228" d="M-19334.447,3339.91h0A6.017,6.017,0,0,1-19337,3335a6.005,6.005,0,0,1,6-6,6.018,6.018,0,0,1,4.91,2.554,7.579,7.579,0,0,0-.906-.054c-.182,0-.365.007-.545.02a4.882,4.882,0,0,0-3.459-1.427,4.912,4.912,0,0,0-4.906,4.906,4.872,4.872,0,0,0,1.428,3.458c-.014.183-.02.361-.02.545a8.1,8.1,0,0,0,.053.905Zm.908-4.586h0l-.754-.732a.547.547,0,0,1-.135-.558.534.534,0,0,1,.441-.369l1.723-.252.771-1.568a.551.551,0,0,1,.49-.3.546.546,0,0,1,.49.3l.207.421a7.491,7.491,0,0,0-3.234,3.057Z" transform="translate(19361 -2433)" fill="#575b6a"/>
                                        </g>
                                    </svg>
                                </div>
                                <span class="aiz-side-nav-text">{{translate('Club Point System')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.001" viewBox="0 0 16 14.001" class="mx-2">
                                        <path id="Union_49" data-name="Union 49" d="M-19322,3342.5v-5a2.007,2.007,0,0,0-2-2v1.5a3,3,0,0,1-3,3h-4v-10h4a3,3,0,0,1,3,3v1.5a3,3,0,0,1,3,3v5a.506.506,0,0,1-.5.5A.5.5,0,0,1-19322,3342.5Zm-11-2V3339h-3a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v-7.5a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v11a.5.5,0,0,1-.5.5A.506.506,0,0,1-19333,3340.5Zm-3-7.5a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v2Z" transform="translate(19337 -3329)" fill="#f51350"/>
                                    </svg>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                @can('club_point_configurations')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('club_points.configs') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Club Point Configurations')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('set_club_points')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('set_product_points')}}" class="aiz-side-nav-link {{ areActiveRoutes(['set_product_points', 'product_club_point.edit'])}}">
                                            <span class="aiz-side-nav-text">{{translate('Set Product Point')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view_users_club_points')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('club_points.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['club_points.index', 'club_point.details'])}}">
                                            <span class="aiz-side-nav-text">{{translate('User Points')}}</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                @endif

                <!--OTP addon -->
                @if (addon_is_activated('otp_system'))
                @hasrole("Tech Support")
                    @canany(['otp_configurations','sms_templates','sms_providers_configurations','send_bulk_sms'])
                        <li class="aiz-side-nav-item">
                            <a href="#" class="aiz-side-nav-link">
                                <div class="aiz-side-nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                        <path id="pin-code" d="M4.25,12.25a.625.625,0,0,1,.625.625h0a.625.625,0,1,1-1.25,0h0A.625.625,0,0,1,4.25,12.25Zm1.875.625h0a.625.625,0,1,0,1.25,0h0a.625.625,0,1,0-1.25,0Zm2.5,0h0a.625.625,0,1,0,1.25,0h0a.625.625,0,1,0-1.25,0Zm2.5,0h0a.625.625,0,1,0,1.25,0h0a.625.625,0,0,0-1.25,0Zm3-3.046a.625.625,0,0,0-.312,1.211,1.249,1.249,0,0,1,.937,1.211V13.5a1.251,1.251,0,0,1-1.25,1.25H2.5A1.251,1.251,0,0,1,1.25,13.5V12.25a1.257,1.257,0,0,1,.9-1.2.625.625,0,1,0-.354-1.2,2.518,2.518,0,0,0-1.284.888A2.478,2.478,0,0,0,0,12.25V13.5A2.5,2.5,0,0,0,2.5,16h11A2.5,2.5,0,0,0,16,13.5V12.25A2.5,2.5,0,0,0,14.125,9.829Zm-10.562-.7V5.749A1.877,1.877,0,0,1,5.437,3.874h.124V2.387a2.438,2.438,0,0,1,4.875,0V3.874h.126a1.877,1.877,0,0,1,1.875,1.875V9.124A1.877,1.877,0,0,1,10.562,11H5.437A1.877,1.877,0,0,1,3.562,9.124Zm3.249-5.25H9.187V2.387a1.189,1.189,0,0,0-2.375,0V3.874Zm-2,5.25a.626.626,0,0,0,.625.625h5.125a.626.626,0,0,0,.625-.625V5.749a.626.626,0,0,0-.625-.625H5.437a.626.626,0,0,0-.625.625ZM8,8.125A.625.625,0,0,0,8.625,7.5h0a.625.625,0,0,0-1.25,0h0A.625.625,0,0,0,8,8.125Z" transform="translate(0)" fill="#575b6a"/>
                                    </svg>
                                </div>
                                <span class="aiz-side-nav-text">{{translate('OTP System')}}</span>
                                @if (env("DEMO_MODE") == "On")
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14.001" viewBox="0 0 16 14.001" class="mx-2">
                                        <path id="Union_49" data-name="Union 49" d="M-19322,3342.5v-5a2.007,2.007,0,0,0-2-2v1.5a3,3,0,0,1-3,3h-4v-10h4a3,3,0,0,1,3,3v1.5a3,3,0,0,1,3,3v5a.506.506,0,0,1-.5.5A.5.5,0,0,1-19322,3342.5Zm-11-2V3339h-3a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v-7.5a.5.5,0,0,1,.5-.5.5.5,0,0,1,.5.5v11a.5.5,0,0,1-.5.5A.506.506,0,0,1-19333,3340.5Zm-3-7.5a1,1,0,0,1-1-1,1,1,0,0,1,1-1h3v2Z" transform="translate(19337 -3329)" fill="#f51350"/>
                                    </svg>
                                @endif
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                @can('otp_configurations')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('otp.configconfiguration') }}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('OTP Configurations')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('sms_templates')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('sms-templates.index')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('SMS Templates')}}</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('sms_providers_configurations')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{route('otp_credentials.index')}}" class="aiz-side-nav-link">
                                            <span class="aiz-side-nav-text">{{translate('Set OTP Credentials')}}</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                    @endhasrole
                @endif

                <!-- Mobile App Settings-->
                @canany(['mobile_app_settings'])
                <li class="aiz-side-nav-item">
                    <a href="#" class="aiz-side-nav-link">
                        <div class="aiz-side-nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" width="16" height="16" viewBox="0 0 24 24"><path d="M19,12v9a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V3A2,2,0,0,1,7,1h5a1,1,0,0,1,0,2H7V21H17V12a1,1,0,0,1,2,0Zm-7,6a1,1,0,1,0,1,1A1,1,0,0,0,12,18ZM23,4.6V5.4a.4.4,0,0,1-.4.4h-.628a.416.416,0,0,0-.38.269l0,.006a.415.415,0,0,0,.08.458l.444.444a.4.4,0,0,1,0,.571l-.561.561a.4.4,0,0,1-.571,0l-.444-.444a.415.415,0,0,0-.458-.08l-.006,0a.414.414,0,0,0-.269.38V8.6a.4.4,0,0,1-.4.4H18.6a.4.4,0,0,1-.4-.4V7.968a.414.414,0,0,0-.269-.38l-.006,0a.415.415,0,0,0-.458.08l-.444.444a.4.4,0,0,1-.571,0l-.561-.561a.4.4,0,0,1,0-.571l.444-.444a.415.415,0,0,0,.08-.458l0-.006a.416.416,0,0,0-.38-.269H15.4a.4.4,0,0,1-.4-.4V4.6a.4.4,0,0,1,.4-.4h.628a.415.415,0,0,0,.38-.27l0-.005a.415.415,0,0,0-.08-.458l-.444-.444a.4.4,0,0,1,0-.571l.561-.561a.4.4,0,0,1,.571,0l.444.444a.413.413,0,0,0,.458.079l.006,0a.416.416,0,0,0,.269-.38V1.4a.4.4,0,0,1,.4-.4H19.4a.4.4,0,0,1,.4.4v.628a.416.416,0,0,0,.269.38l.006,0a.413.413,0,0,0,.458-.079l.444-.444a.4.4,0,0,1,.571,0l.561.561a.4.4,0,0,1,0,.571l-.444.444a.415.415,0,0,0-.08.458l0,.005a.415.415,0,0,0,.38.27H22.6A.4.4,0,0,1,23,4.6ZM20.2,5A1.2,1.2,0,1,0,19,6.2,1.2,1.2,0,0,0,20.2,5Z"/><script xmlns=""/></svg>
                        </div>
                        <span class="aiz-side-nav-text">{{translate('Mobile App Settings')}}</span>
                        <span class="aiz-side-nav-arrow"></span>
                    </a>
                    <ul class="aiz-side-nav-list level-2">
                            <li class="aiz-side-nav-item">
                                <a href="{{route('mobile-app.version')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{translate('App version')}}</span>
                                </a>
                            </li>
                            <li class="aiz-side-nav-item">
                                <a href="{{route('mobile-app.sliders', ['lang'=>config('app.locale')])}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{translate('App Sliders')}}</span>
                                </a>
                            </li>
                    </ul>
                </li>
                @endcanany
                <!-- Website Setup -->

                @canany(['header_setup','footer_setup','view_all_website_pages','website_appearance','authentication_layout_settings'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link {{ areActiveRoutes(['website.footer', 'website.header'])}}" >
                            <div class="aiz-side-nav-icon">
                                <svg id="Group_28315" data-name="Group 28315" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <circle id="Ellipse_893" data-name="Ellipse 893" cx="0.625" cy="0.625" r="0.625" transform="translate(7.375 6.125)" fill="#575b6a"/>
                                    <path id="Path_40777" data-name="Path 40777" d="M13.5,0H2.5A2.5,2.5,0,0,0,0,2.5V11a2.5,2.5,0,0,0,2.5,2.5H7.375v1.25H5.5A.625.625,0,0,0,5.5,16h5a.625.625,0,0,0,0-1.25H8.625V12.875A.625.625,0,0,0,8,12.25H2.5A1.251,1.251,0,0,1,1.25,11V2.5A1.251,1.251,0,0,1,2.5,1.25h11A1.251,1.251,0,0,1,14.75,2.5V11a1.251,1.251,0,0,1-1.25,1.25h-3a.625.625,0,0,0,0,1.25h3A2.5,2.5,0,0,0,16,11V2.5A2.5,2.5,0,0,0,13.5,0Z" fill="#575b6a"/>
                                    <path id="Path_40778" data-name="Path 40778" d="M120.375,84.75a.625.625,0,0,0,.625-.625v-.688a3.107,3.107,0,0,0,1.1-.456l.487.487a.625.625,0,0,0,.884-.884l-.487-.487a3.108,3.108,0,0,0,.456-1.1h.688a.625.625,0,1,0,0-1.25h-.688a3.108,3.108,0,0,0-.456-1.1l.487-.487a.625.625,0,0,0-.884-.884l-.487.487a3.107,3.107,0,0,0-1.1-.456v-.688a.625.625,0,0,0-1.25,0v.688a3.108,3.108,0,0,0-1.1.456l-.487-.487a.625.625,0,0,0-.884.884l.487.487a3.108,3.108,0,0,0-.456,1.1h-.688a.625.625,0,0,0,0,1.25h.688a3.108,3.108,0,0,0,.456,1.1l-.487.487a.625.625,0,0,0,.884.884l.487-.487a3.107,3.107,0,0,0,1.1.456v.688A.625.625,0,0,0,120.375,84.75ZM118.5,80.375a1.875,1.875,0,1,1,1.875,1.875A1.877,1.877,0,0,1,118.5,80.375Z" transform="translate(-112.375 -73.625)" fill="#575b6a"/>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Website Setup')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            {{-- @can('edit_website_page')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('custom-pages.edit', ['id'=>'home', 'lang'=>config('app.locale'), 'page'=>'home']) }}"
                                        class="aiz-side-nav-link {{ (url()->current() == url('/admin/website/custom-pages/edit/home')) ? 'active' : '' }}">
                                        <span class="aiz-side-nav-text">{{translate('Homepage Settings')}}</span>
                                    </a>
                                </li>
                            @endcan --}}
                            @hasrole('Tech Support')
                            @can('authentication_layout_settings')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('website.authentication-layout-settings') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Authentication Layout & Settings')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('footer_setup')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('website.footer', ['lang'=>  App::getLocale()] ) }}" class="aiz-side-nav-link {{ areActiveRoutes(['website.footer'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Footer')}}</span>
                                    </a>
                                </li>
                            @endcan


                            @can('header_setup')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('website.header') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Header')}}</span>
                                    </a>
                                </li>
                            @endcan


                            @can('website_appearance')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('website.appearance') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Appearance')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @endhasrole
                            @can('view_all_website_pages')
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('website.pages') }}" class="aiz-side-nav-link {{ areActiveRoutes(['website.pages', 'custom-pages.create' ,'custom-pages.edit'])}}">
                                    <span class="aiz-side-nav-text">{{translate('Pages')}}</span>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                <!-- Setup & Configurations -->
                @canany(['general_settings','features_activation','language_setup','currency_setup','vat_&_tax_setup',
                        'pickup_point_setup','smtp_settings','payment_methods_configurations','order_configuration','file_system_&_cache_configuration',
                        'social_media_logins','facebook_chat','facebook_comment','analytics_tools_configuration','google_recaptcha_configuration','google_map_setting',
                        'google_firebase_setting','shipping_configuration','shipping_country_setting','manage_shipping_states','manage_shipping_cities','manage_zones','manage_carriers'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <path id="Path_40779" data-name="Path 40779" d="M7.688,16h.625a1.877,1.877,0,0,0,1.875-1.875V13.81a.209.209,0,0,1,.133-.191l.011,0a.209.209,0,0,1,.23.041l.223.223a1.875,1.875,0,0,0,2.652,0l.442-.442a1.875,1.875,0,0,0,0-2.652l-.223-.223a.209.209,0,0,1-.041-.23l0-.012a.209.209,0,0,1,.191-.133h.315A1.877,1.877,0,0,0,16,8.313V7.688a1.877,1.877,0,0,0-1.875-1.875H13.81a.209.209,0,0,1-.191-.133l0-.011a.209.209,0,0,1,.041-.23l.223-.223a1.875,1.875,0,0,0,0-2.652l-.442-.442a1.875,1.875,0,0,0-2.652,0l-.223.223a.21.21,0,0,1-.23.041l-.012,0a.209.209,0,0,1-.133-.191V1.875A1.877,1.877,0,0,0,8.312,0H7.687A1.877,1.877,0,0,0,5.812,1.875V2.19a.209.209,0,0,1-.133.191l-.012,0a.209.209,0,0,1-.23-.041l-.223-.223a1.875,1.875,0,0,0-2.652,0l-.442.442a1.875,1.875,0,0,0,0,2.652l.223.223a.209.209,0,0,1,.041.23l0,.011a.209.209,0,0,1-.191.133H1.875A1.877,1.877,0,0,0,0,7.687v.625a1.874,1.874,0,0,0,1.407,1.816.625.625,0,1,0,.312-1.211.624.624,0,0,1-.468-.605V7.688a.626.626,0,0,1,.625-.625H2.19a1.455,1.455,0,0,0,1.347-.906l0-.011a1.455,1.455,0,0,0-.312-1.591l-.223-.223a.625.625,0,0,1,0-.884l.442-.442a.625.625,0,0,1,.884,0l.223.223a1.456,1.456,0,0,0,1.593.311l.009,0A1.455,1.455,0,0,0,7.063,2.19V1.875a.626.626,0,0,1,.625-.625h.625a.626.626,0,0,1,.625.625V2.19a1.455,1.455,0,0,0,.906,1.347l.009,0a1.455,1.455,0,0,0,1.593-.311l.223-.223a.625.625,0,0,1,.884,0l.442.442a.625.625,0,0,1,0,.884l-.223.223a1.455,1.455,0,0,0-.311,1.593l0,.009a1.455,1.455,0,0,0,1.347.906h.315a.626.626,0,0,1,.625.625v.625a.626.626,0,0,1-.625.625H13.81a1.455,1.455,0,0,0-1.347.906l0,.009a1.455,1.455,0,0,0,.311,1.593l.223.223a.625.625,0,0,1,0,.884l-.442.442a.625.625,0,0,1-.884,0l-.223-.223a1.456,1.456,0,0,0-1.593-.311l-.009,0a1.455,1.455,0,0,0-.906,1.347v.315a.626.626,0,0,1-.625.625H7.688a.622.622,0,0,1-.6-.437.625.625,0,1,0-1.193.375A1.867,1.867,0,0,0,7.688,16ZM.536,15.433a1.829,1.829,0,0,1,0-2.586h0L4.589,8.811a3.234,3.234,0,0,1-.308-1.259,2.97,2.97,0,0,1,.9-2.141A4.228,4.228,0,0,1,8.13,4.255h.007a3.322,3.322,0,0,1,1.086.188A.625.625,0,0,1,9.47,5.473L7.964,7.01l.188.811L8.95,8,10.479,6.47a.625.625,0,0,1,1.034.24,3.472,3.472,0,0,1,.2,1.121,4.373,4.373,0,0,1-.8,2.556,3.047,3.047,0,0,1-2.49,1.3H8.417A3.414,3.414,0,0,1,7.159,11.4L3.122,15.433a1.829,1.829,0,0,1-2.586,0Zm6.876-5.311a2.1,2.1,0,0,0,1.007.316,1.818,1.818,0,0,0,1.487-.792,2.988,2.988,0,0,0,.528-1.361l-.843.845A.625.625,0,0,1,9.01,9.3L7.494,8.953a.625.625,0,0,1-.471-.468L6.669,6.959a.625.625,0,0,1,.162-.579l.823-.84A2.844,2.844,0,0,0,6.067,6.3,1.723,1.723,0,0,0,5.531,7.55a2.123,2.123,0,0,0,.342,1,.625.625,0,0,1-.065.809L1.419,13.731a.579.579,0,1,0,.819.818l4.368-4.361a.625.625,0,0,1,.806-.066Z" fill="#575b6a"/>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Setup & Configurations')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @hasrole(['Tech Support'])
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('activation.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Features activation')}}</span>
                                    </a>
                                </li>
                            @endhasrole
                            @hasrole(['Tech Support', 'Super Admin'])
                            @can('language_setup')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('languages.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['languages.index', 'languages.create', 'languages.store', 'languages.show', 'languages.edit'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Languages')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @endhasrole
                            @hasrole('Tech Support')
                            @can('general_settings')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('general_setting.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('General Settings')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('general_settings')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('pdf_settings.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['pdf_settings.index'])}}">
                                        <span class="aiz-side-nav-text">{{translate('PDF Settings')}}</span>
                                    </a>
                                </li>
                            @endcan


                            @can('smtp_settings')
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('smtp_settings.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{translate('SMTP Settings')}}</span>
                                </a>
                            </li>
                        @endcan
                        @can('order_configuration')
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('order_configuration.index') }}" class="aiz-side-nav-link">
                                <span class="aiz-side-nav-text">{{translate('Order Configuration')}}</span>
                            </a>
                        </li>
                    @endcan
                    @can('file_system_&_cache_configuration')
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('file_system.index') }}" class="aiz-side-nav-link">
                                <span class="aiz-side-nav-text">{{translate('File System & Cache Configuration')}}</span>
                            </a>
                        </li>
                    @endcan
                    @can('social_media_logins')
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('social_login.index') }}" class="aiz-side-nav-link">
                                <span class="aiz-side-nav-text">{{translate('Social media Logins')}}</span>
                            </a>
                        </li>
                    @endcan
                    @canany(['facebook_chat','facebook_comment'])
                                <li class="aiz-side-nav-item">
                                    <a href="javascript:void(0);" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Facebook')}}</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        @can('facebook_chat')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('facebook_chat.index') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Facebook Chat')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('facebook_comment')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('facebook-comment') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Facebook Comment')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany
                            @canany(['analytics_tools_configuration','google_recaptcha_configuration','google_map_setting','google_firebase_setting'])
                                <li class="aiz-side-nav-item">
                                    <a href="javascript:void(0);" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Google')}}</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">

                                        @can('google_recaptcha_configuration')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('google_recaptcha.index') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Google reCAPTCHA')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('google_map_setting')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('google-map.index') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Google Map')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('google_firebase_setting')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{ route('google-firebase.index') }}" class="aiz-side-nav-link">
                                                    <span class="aiz-side-nav-text">{{translate('Google Firebase')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany
                            @endhasrole
                            {{-- @can('analytics_tools_configuration')
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('google_analytics.index') }}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{translate('Analytics Tools')}}</span>
                                </a>
                            </li>
                            @endcan --}}
                            @can('currency_setup')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('currency.index')}}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Currency')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('vat_&_tax_setup')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('tax.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['tax.index', 'tax.create', 'tax.store', 'tax.show', 'tax.edit'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Vat & TAX')}}</span>
                                    </a>
                                </li>
                            @endcan
                             @can('pickup_point_setup')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('pick_up_points.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['pick_up_points.index','pick_up_points.create','pick_up_points.edit'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Pickup point')}}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('payment_methods_configurations')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('payment_method.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Payment Methods')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_all_manual_payment_methods')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('manual_payment_methods.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['manual_payment_methods.index', 'manual_payment_methods.create', 'manual_payment_methods.edit'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Manual Payment Methods')}}</span>
                                    </a>
                                </li>
                            @endcan


                            @canany(['shipping_configuration','shipping_country_setting','manage_shipping_states','manage_shipping_cities','manage_zones','manage_carriers'])
                                <li class="aiz-side-nav-item">
                                    <a href="javascript:void(0);" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{translate('Shipping')}}</span>
                                        <span class="aiz-side-nav-arrow"></span>
                                    </a>
                                    <ul class="aiz-side-nav-list level-3">
                                        @can('shipping_configuration')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{route('shipping_configuration.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['shipping_configuration.index','shipping_configuration.edit','shipping_configuration.update'])}}">
                                                    <span class="aiz-side-nav-text">{{translate('Shipping Configuration')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('shipping_country_setting')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{route('countries.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['countries.index','countries.edit','countries.update'])}}">
                                                    <span class="aiz-side-nav-text">{{translate('Shipping Countries')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('manage_shipping_states')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{route('states.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['states.index','states.edit','states.update'])}}">
                                                    <span class="aiz-side-nav-text">{{translate('Shipping States')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('manage_shipping_cities')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{route('cities.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['cities.index','cities.edit','cities.update'])}}">
                                                    <span class="aiz-side-nav-text">{{translate('Shipping Cities')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('manage_zones')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{route('zones.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['zones.index','zones.create','zones.edit'])}}">
                                                    <span class="aiz-side-nav-text">{{translate('Shipping Zones')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('manage_carriers')
                                            <li class="aiz-side-nav-item">
                                                <a href="{{route('carriers.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['carriers.index','carriers.create','carriers.edit'])}}">
                                                    <span class="aiz-side-nav-text">{{translate('Shipping Carrier')}}</span>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcanany
                        </ul>
                    </li>
                @endcanany

                <!-- Staffs -->
                @canany(['view_all_staffs','view_staff_roles'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <g id="Group_28314" data-name="Group 28314" transform="translate(-19299 2175)">
                                      <path id="Path_40774" data-name="Path 40774" d="M87.867,3.07H84.133V1.72A.716.716,0,0,0,83.422,1H80.578a.716.716,0,0,0-.711.72V3.07H76.133A2.149,2.149,0,0,0,74,5.229V14.84A2.149,2.149,0,0,0,76.133,17H87.867A2.149,2.149,0,0,0,90,14.84V5.229A2.149,2.149,0,0,0,87.867,3.07Zm-6.578-.63h1.422V3.79a.711.711,0,1,1-1.422,0Zm7.289,12.4a.716.716,0,0,1-.711.72H76.133a.716.716,0,0,1-.711-.72V5.229a.716.716,0,0,1,.711-.72h3.856a2.124,2.124,0,0,0,4.022,0h3.856a.716.716,0,0,1,.711.72Z" transform="translate(19225 -2176)" fill="#575b6a"/>
                                      <g id="Group_28312" data-name="Group 28312" transform="translate(19305.07 -2169.197)">
                                        <path id="Path_40775" data-name="Path 40775" d="M199.864,197.932a1.932,1.932,0,1,0-1.932,1.932A1.934,1.934,0,0,0,199.864,197.932Zm-1.932.644a.644.644,0,1,1,.644-.644A.645.645,0,0,1,197.932,198.576Z" transform="translate(-196 -196)" fill="#575b6a"/>
                                      </g>
                                      <g id="Group_28313" data-name="Group 28313" transform="translate(19303.779 -2165)">
                                        <path id="Path_40776" data-name="Path 40776" d="M160.508,316h-2.576A1.934,1.934,0,0,0,156,317.932v1.288a.644.644,0,1,0,1.288,0v-1.288a.645.645,0,0,1,.644-.644h2.576a.645.645,0,0,1,.644.644v1.288a.644.644,0,1,0,1.288,0v-1.288A1.934,1.934,0,0,0,160.508,316Z" transform="translate(-156 -316)" fill="#575b6a"/>
                                      </g>
                                    </g>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('Staffs')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">
                            @can('view_all_staffs')
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('staffs.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['staffs.index', 'staffs.create', 'staffs.edit'])}}">
                                        <span class="aiz-side-nav-text">{{translate('All staffs')}}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view_staff_roles')
                                <li class="aiz-side-nav-item">
                                    <a href="{{route('roles.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['roles.index', 'roles.create', 'roles.edit'])}}">
                                        <span class="aiz-side-nav-text">{{translate('Staff permissions')}}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                <!-- System Update & Server Status -->
                @hasrole('Tech Support')
                @canany(['system_update','server_status'])
                    <li class="aiz-side-nav-item">
                        <a href="#" class="aiz-side-nav-link">
                            <div class="aiz-side-nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                    <g id="Group_28317" data-name="Group 28317" transform="translate(-19315.001 1976)">
                                      <g id="layer1" transform="translate(19314.471 -1976.53)">
                                        <path id="path3159" d="M3.386.53A2.862,2.862,0,0,0,.53,3.386V13.67a2.865,2.865,0,0,0,2.856,2.86H13.67a2.869,2.869,0,0,0,2.86-2.86V3.386A2.865,2.865,0,0,0,13.67.53Zm0,1.143H13.67a1.7,1.7,0,0,1,1.718,1.713V13.67a1.7,1.7,0,0,1-1.718,1.718H3.386A1.7,1.7,0,0,1,1.673,13.67V3.386A1.7,1.7,0,0,1,3.386,1.673Z" fill="#575b6a"/>
                                      </g>
                                      <g id="Group_28316" data-name="Group 28316" transform="translate(19317.551 -1973.449)">
                                        <g id="LWPOLYLINE" transform="translate(0 3.708)">
                                          <path id="Path_25666" data-name="Path 25666" d="M194.061,143.129a.436.436,0,0,0,0,.873h1.527a.436.436,0,0,0,0-.873Z" transform="translate(-193.625 -143.129)" fill="#575b6a"/>
                                        </g>
                                        <g id="LWPOLYLINE-2" data-name="LWPOLYLINE" transform="translate(3.663)">
                                          <path id="Path_25667" data-name="Path 25667" d="M199.926,137.186a.436.436,0,0,1,.872,0v1.527a.436.436,0,0,1-.872,0Z" transform="translate(-199.926 -136.75)" fill="#575b6a"/>
                                        </g>
                                        <g id="LWPOLYLINE-3" data-name="LWPOLYLINE" transform="translate(5.239 1.075)">
                                          <path id="Path_25668" data-name="Path 25668" d="M204.463,139.345a.436.436,0,1,0-.617-.617l-1.079,1.079a.436.436,0,1,0,.617.617Z" transform="translate(-202.638 -138.6)" fill="#575b6a"/>
                                        </g>
                                        <g id="LWPOLYLINE-4" data-name="LWPOLYLINE" transform="translate(1.097 1.075)">
                                          <path id="Path_25669" data-name="Path 25669" d="M195.64,139.345a.436.436,0,1,1,.617-.617l1.079,1.079a.436.436,0,1,1-.617.617Z" transform="translate(-195.512 -138.6)" fill="#575b6a"/>
                                        </g>
                                        <g id="LWPOLYLINE-5" data-name="LWPOLYLINE" transform="translate(1.097 5.261)">
                                          <path id="Path_25670" data-name="Path 25670" d="M195.64,147.008a.436.436,0,0,0,.617.617l1.079-1.079a.436.436,0,1,0-.617-.617Z" transform="translate(-195.512 -145.8)" fill="#575b6a"/>
                                        </g>
                                        <path id="Path_25671" data-name="Path 25671" d="M206.87,148.144,205,146.269l.864-.471a.436.436,0,0,0-.044-.786l-5.682-2.322a.436.436,0,0,0-.569.568l2.322,5.682a.436.436,0,0,0,.786.044l.471-.864,1.875,1.875a.437.437,0,0,0,.617,0l1.233-1.233A.437.437,0,0,0,206.87,148.144Zm-1.544.913-1.977-1.977a.436.436,0,0,0-.691.1l-.311.57-1.58-3.868,3.868,1.58-.57.311a.436.436,0,0,0-.174.591.467.467,0,0,0,.074.1l1.977,1.977Z" transform="translate(-196.099 -139.223)" fill="#575b6a"/>
                                      </g>
                                    </g>
                                </svg>
                            </div>
                            <span class="aiz-side-nav-text">{{translate('System')}}</span>
                            <span class="aiz-side-nav-arrow"></span>
                        </a>
                        <ul class="aiz-side-nav-list level-2">

                            @can('server_status')
                            <li class="aiz-side-nav-item">
                                <a href="{{route('system_server')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{translate('Server status')}}</span>
                                </a>
                            </li>
                            @endcan

                        </ul>
                        <ul class="aiz-side-nav-list level-2">
                            @can('view-horizon')
                            <li class="aiz-side-nav-item">
                                <a href="{{route('horizon.index')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{translate('Horizon')}}</span>
                                </a>
                            </li>
                            @endcan
                            @can('view-log')
                            <li class="aiz-side-nav-item">
                                <a href="{{route('log-viewer.index')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{translate('System Logs')}}</span>
                                </a>
                            </li>
                            @endcan
                            @can('view-health')
                            <li class="aiz-side-nav-item">
                                <a href="{{route('health.index')}}" class="aiz-side-nav-link">
                                    <span class="aiz-side-nav-text">{{translate('Health Check')}}</span>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @endhasrole
            </ul><!-- .aiz-side-nav -->
        </div><!-- .aiz-side-nav-wrap -->
    </div><!-- .aiz-sidebar -->
    <div class="aiz-sidebar-overlay"></div>
</div><!-- .aiz-sidebar -->
