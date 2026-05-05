    @extends('backend.layouts.app')

    @section('content')
        <div class="row">
            @foreach ($payment_methods as $payment_method)
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <img class="mr-3" src="{{ static_asset('assets/img/cards/'.$payment_method->name.'.png') }}" height="30">
                            <h5 class="mb-0 h6">{{ ucfirst(translate($payment_method->name)) }}</h5>
                        </div>
                        <label class="aiz-switch aiz-switch-success mb-0 float-right">
                            <input type="checkbox" onchange="updatePaymentSettings(this, {{ $payment_method->id }})" @if ($payment_method->active == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="card-body">
                        @include('backend.setup_configurations.payment_method.partials.'.$payment_method->name)
                    </div>
                </div>
            </div>
            @endforeach
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <img class="mr-3" src="{{ static_asset('assets/img/warning.png') }}" height="30">
                            <h5 class="mb-0 h6">{{ translate('Test Payment') }}</h5>
                            <span class="text-danger ">({{translate('Use only in development')}})</span>
                        </div>

                        <label class="aiz-switch aiz-switch-success mb-0 float-right">
                            <input type="checkbox" onchange="updateSettings(this, 'test_payment')" @if (get_setting('test_payment') == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="car-body p-2">
                        <div class="">
                            <span class="text-warning ">{{translate('Warning: This will add a new payment method that will always process the payments')}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <img class="mr-3" src="{{ static_asset('assets/img/cards/cod.png') }}" height="30">
                            <h5 class="mb-0 h6">{{ translate('Cash Payment') }}</h5>
                        </div>
                        <label class="aiz-switch aiz-switch-success mb-0 float-right">
                            <input type="checkbox" onchange="updateSettings(this, 'cash_payment')" @if (get_setting('cash_payment') == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('script')
        <script type="text/javascript">
            function updatePaymentSettings(el, id) {
                if ($(el).is(':checked')) {
                    var value = 1;
                } else {
                    var value = 0;
                }

                $.post('{{ route('payment.activation') }}', {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    value: value
                }, function(data) {
                    if (data == 1) {
                        AIZ.plugins.notify('success', '{{ translate('Payment Settings updated successfully') }}');
                    } else {
                        AIZ.plugins.notify('danger', 'Something went wrong');
                    }
                });
            }

            function updateSettings(el, type) {
                if ($(el).is(':checked')) {
                    var value = 1;
                } else {
                    var value = 0;
                }

                $.post('{{ route('business_settings.update.activation') }}', {
                    _token: '{{ csrf_token() }}',
                    type: type,
                    value: value
                }, function(data) {
                    if (data == 1) {
                        AIZ.plugins.notify('success', '{{ translate('Settings updated successfully') }}');
                    } else {
                        AIZ.plugins.notify('danger', 'Something went wrong');
                    }
                });
            }

            function get_floosak_key(){
                $(this).attr('disabled')
                $.ajax({
                    url: '{{route("floosak.get-new-key")}}',
                    method: 'GET',
                    success: function(response) {
                       if(response.success == true){
                        $('input[name="floosak_request_id"]').val(response.request_id);
                        $('#verify_key_modal').modal('show');
                       } else {
                        AIZ.plugins.notify('danger', response.message);
                       }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            function verify_floosak_key(){
                var request_id = $('input[name="floosak_request_id"]').val();
                var otp = $('input[name="floosak_otp_code"]').val();
                $.post('{{ route('floosak.verify-key') }}', {
                    _token: '{{ csrf_token() }}',
                   request_id: request_id,
                    otp: otp
                }, function(data) {
                    if (data.success == true) {
                        AIZ.plugins.notify('success', data.message);
                        $('#verify_key_modal').modal('hide');
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                    }
                });
            }
            $(document).ready(function(){
                $.ajax({
                    url: '{{route("floosak.check-key")}}',
                    method: 'GET',
                    success: function(response) {
                       if(response.result == true){
                       $('#get_key_btn').hide();
                       $('#floosak_expire_at').html('<span class="text-success">'+response.message+'</span>');
                       } else {
                        $('#get_key_btn').prop('disabled', false);
                        $('#floosak_expire_at').html('<span class="text-danger">'+response.message+'</span>');
                        AIZ.plugins.notify('warning', '{{translate("You need to get a new key for floosak")}}');
                       }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            })
        </script>
    @endsection
