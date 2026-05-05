@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-body" style="min-height:460px;">
                <form class="form-horizontal" action="{{ route('custom_notification.send') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <p class="fs-13 fw-700 mb-3">{{ translate('Send Custom Notification') }}</p>

                    <!-- Error Meassages -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 control-label fw-700" for="name">{{ translate('Customers') }}</label>
                        <div class="col-sm-9">
                            <div class="mb-2 d-flex">
                                <span class="mb-0 mr-1" for="all-customers">{{ translate('All Customers') }}</span>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" id="all-customers" name="all_customers" value="1" class="aiz-checkbox" checked>
                                    <span class="slider round"></span>
                                </label>

                            </div>

                            <select class="form-control selectpicker" id="customer-select" data-live-search="true"
                            data-ajax-url="{{ route('users.ajax.search') }}" data-size="10" name="user_ids[]" data-show-phone="true" data-show-email="true" multiple>
                                <option value="">{{translate('Select Customer')}}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 control-label fw-700"
                            for="name">{{ translate('Select Type') }}</label>
                        <div class="col-sm-9">
                            <select class="form-control form-control-sm aiz-selectpicker" data-live-search="true"
                                onchange="getContent(this.value)" name="notification_type_id" required>
                                <option value="">{{ translate('Select the type of the notification') }}</option>
                                @foreach ($customNotificationTypes as $customNotificationType)
                                    <option value="{{ $customNotificationType->id }}">
                                        {{ $customNotificationType->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 control-label fw-700" for="name">
                            {{ translate('Content') }}
                            <br>
                            <span
                                class="fs-12 text-secondary fw-400">({{ translate('Best within 80 character') }})</span>
                        </label>
                        <div class="col-sm-9">
                            <textarea class="form-control form-control-sm" id="notification_content" rows="4"
                                placeholder="{{ translate('Write what your notification will display…') }}" readonly></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 control-label fw-700" for="link">{{ translate('Link') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control form-control-sm" name="link"
                                placeholder="{{ translate('Paste your link here') }}">
                        </div>
                    </div>
                    <div class="float-right my-3">
                        <button type="submit"
                            class="btn btn-primary btn-sm fw-700 rounded-2 shadow-primary w-170px">{{ translate('Send Notifications') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
@include('backend.js.user_ajax_search')
    <script type="text/javascript">
       $(document).ready(function() {
        $('#all-customers').change(function() {
            if($(this).is(':checked')) {
                // Disable the select
                $('#customer-select').prop('disabled', true);
                $('#customer-select').selectpicker('refresh');
                // Clear any selected values
                $('#customer-select').selectpicker('deselectAll');
            } else {
                // Enable the select
                $('#customer-select').prop('disabled', false);
                $('#customer-select').selectpicker('refresh');
            }
        });
        $('#all-customers').trigger('change');
    });

        // Get default content by the notification type
        function getContent(id) {
            $('#notification_content').prop("disabled", true);
            $.post('{{ route('notification_type.get_default_text') }}', {
                _token: '{{ @csrf_token() }}',
                id: id,
            }, function(data) {
                if (data != null) {
                    $('textarea#notification_content').val(data);
                }
            });
        }
    </script>
@endsection
