@extends('backend.layouts.app')

@section('content')

<h4 class="text-center text-muted">{{ translate('App Settings') }}</h4>
<div class="row mt-3">
    <div class="col-6">
        <div class="mb-1">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 text-center">{{ translate('Android App Version') }}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal mt-3" action="{{ route('mobile-app.version.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Android App Minimum Version')
                                    }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="settings[android_app_min_version]" placeholder="1.0.0"
                                value="{{get_setting('android_app_min_version')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Should Force Update') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden"name="settings[should_force_android_update]" value="off">
                                    <input type="checkbox" name="settings[should_force_android_update]" <?php if
                                        (get_setting('should_force_android_update')==1) { echo 'checked' ; } ?>>
                                    <span class="slider round"></span>
                                </label>
                                <span class="text-info">{{translate('Turning this on will force the user to update the app')}}</span>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="mb-1">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 text-center">{{ translate('IOS App Version') }}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal mt-3" action="{{ route('mobile-app.version.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('IOS App Minimum Version')
                                    }}</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="settings[ios_app_min_version]" placeholder="1.0.0"
                                value="{{get_setting('ios_app_min_version')}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label class="col-from-label">{{ translate('Should Force Update') }}</label>
                            </div>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden"name="settings[should_force_ios_update]" value="off">
                                    <input type="checkbox" name="settings[should_force_ios_update]" <?php if
                                        (get_setting('should_force_ios_update')==1) { echo 'checked' ; } ?>>
                                    <span class="slider round"></span>
                                </label>
                                <span class="text-info">{{translate('Turning this on will force the user to update the app')}}</span>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection