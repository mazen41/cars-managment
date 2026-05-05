@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Role Information')}}</h5>
</div>

<div class="col-lg-12 mx-auto">
    <div class="card">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill language-bar">
                @foreach (get_all_active_language() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3" href="{{ route('roles.edit', ['id'=>$role->id, 'lang'=> $language->code] ) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                            <span>{{$language->name}}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <form class="p-4" action="{{ route('roles.update', $role->id) }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
                <input type="hidden" name="lang" value="{{ $lang }}">
            	   @csrf
                <div class="form-group row">
                    <label class="col-md-3 col-from-label" for="name">{{translate('Name')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                    <div class="col-md-9">
                        @php $roleForTranslation = \App\Models\Role::where('id',$role->id)->first(); @endphp
                        <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" class="form-control" value="{{ $roleForTranslation->getTranslation('name', $lang) }}" required>
                    </div>
                </div>
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Permissions') }}</h5>
                </div>
                <br>
                @php
                  $high_level_permissions = [
                        'show_digital_products',
                        'add_digital_product',
                        'edit_digital_product',
                        'delete_digital_product',
                        'download_digital_product',
                        'view_all_dynamic_popups',
                        'add_dynamic_popups',
                        'edit_dynamic_popups',
                        'delete_dynamic_popups',
                        'publish_dynamic_popups',
                        'header_setup',
                        'footer_setup',
                        'website_appearance',
                        'select_homepage',
                        'authentication_layout_settings',
                        'general_settings',
                        'features_activation',
                        'language_setup',
                        'smtp_settings',
                        'social_media_logins',
                        'facebook_chat',
                        'facebook_comment',
                        'analytics_tools_configuration',
                        'google_recaptcha_configuration',
                        'google_firebase_setting',
                        'system_update',
                        'server_status',
                        'manage_addons',
                        'view_all_offline_customer_package_payments',
                        'approve_offline_customer_package_payment',
                        'view_all_offline_seller_package_payments',
                        'approve_offline_seller_package_payment',
                        'otp_configurations',
                        'sms_templates',
                        'sms_providers_configurations'

                    ];
                    $high_level_sections = [
                        // 'otp_system'
                    ];
                    $permission_groups =  \App\Models\Permission::all()->groupBy('section');
                    $addons = array("club_point", "pos_system", "paytm", "seller_subscription", "otp_system", "refund_request", "affiliate_system", "african_pg", "delivery_boy","wholesale");
                @endphp
                @foreach ($permission_groups as $key => $permission_group)
                    @php
                        $show_permission_group = true;

                        if(in_array($permission_group[0]['section'], $addons)){

                            if (addon_is_activated($permission_group[0]['section']) == false) {
                                $show_permission_group = false;
                            }
                        }
                        if( !auth()->user()->hasRole('Tech Support') && in_array($permission_group[0]['section'], $high_level_sections)){
                            $show_permission_group = false;
                        }
                    @endphp
                    @if($show_permission_group)
                        <ul class="list-group mb-4">
                            <li class="list-group-item bg-light" aria-current="true">{{ translate(Str::headline($permission_group[0]['section'])) }}</li>
                            <li class="list-group-item">
                                <div class="row">
                                    @foreach ($permission_group as $key => $permission)
                                    @if (!auth()->user()->hasRole('Tech Support') && in_array($permission->name, $high_level_permissions))
                                        @continue
                                    @endif
                                        <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
                                            <div class="p-2 border mt-1 mb-2">
                                                <label class="control-label d-flex">{{ translate(Str::headline($permission->name))}}</label>
                                                <label class="aiz-switch aiz-switch-success">
                                                    <input type="checkbox" name="permissions[]" class="form-control demo-sw" value="{{ $permission->name }}"
                                                        @if ($role->hasPermissionTo($permission->name))
                                                            checked
                                                        @endif >
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </li>
                        </ul>
                    @endif
                @endforeach

                <div class="form-group mb-3 mt-3 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection
