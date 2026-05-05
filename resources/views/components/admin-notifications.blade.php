<div class="aiz-topbar-item mr-3">
    <div class="align-items-stretch d-flex dropdown">
        <a class="dropdown-toggle no-arrow" data-toggle="dropdown" href="javascript:void(0);" role="button"
            aria-haspopup="false" aria-expanded="false" id="notification-dropdown-trigger">
            <span class="btn btn-topbar btn-circle btn-soft-primary p-0 d-flex justify-content-center align-items-center" data-toggle="tooltip" data-title="{{ translate('Notification') }}">
                <span class="d-flex align-items-center position-relative">
                    <div class="px-2 hov-svg-dark">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16">
                            <g id="Group_23884" data-name="Group 23884" transform="translate(-677 5110)">
                              <path id="Union_38" data-name="Union 38" d="M5.5,16a.5.5,0,0,1,0-1h3a.5.5,0,1,1,0,1Zm-5-2a.5.5,0,0,1,0-1H2V7A5.008,5.008,0,0,1,6.5,2.025V.5a.5.5,0,1,1,1,0V2.025A5.007,5.007,0,0,1,12,7H11A4,4,0,1,0,3,7v6h8V7h1v6h1.5a.5.5,0,1,1,0,1Z" transform="translate(677 -5110)" fill="#9da3ae"/>
                            </g>
                        </svg>
                    </div>
                    @if ($notificationCount > 0)
                        <span class="badge fw-600 badge-circle badge-danger position-absolute" style="right: -8px; top: -8px" id="notification-badge">{{ $notificationCount }}</span>
                    @endif
                </span>
            </span>
        </a>

        <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-xl py-0">
            <div class="notifications">
                <ul class="nav nav-tabs nav-justified" role="tablist">
                    @foreach($notificationTypes as $key => $type)
                    <li class="nav-item">
                        <a class="nav-link @if($loop->first) active @endif"
                           data-toggle="tab"
                           data-type="{{ $key }}"
                           href="#{{ $key }}-notifications"
                           role="tab"
                           id="{{ $key }}-tab">
                            {{ translate($type['label']) }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content c-scrollbar-light overflow-auto" style="max-height: 400px; overflow-y: auto;">
                    @foreach($notificationTypes as $key => $type)
                    <div class="tab-pane @if($loop->first) active @endif"
                         id="{{ $key }}-notifications"
                         role="tabpanel"
                         data-notification-type="{{ is_array($type['type']) ? json_encode($type['type']) : $type['type'] }}"
                         data-like="{{ $type['like'] ?? false }}">
                        <div class="text-center py-4">
                            <div class="h-100 d-flex align-items-center justify-content-center">
                                <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center border-top">
                <a href="{{ route('admin.all-notifications') }}" class="text-reset d-block py-2">
                    {{ translate('View All Notifications') }}
                </a>
            </div>
        </div>
    </div>
</div>
