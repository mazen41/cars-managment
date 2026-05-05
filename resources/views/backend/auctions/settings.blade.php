@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Action Settings') }}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-4 col-from-label">{{ translate('Insurance Deposit Amount') }}</label>
                            <div class="col-sm-4">
                                <input type="hidden" name="types[]" value="insurance_deposit_amount">
                                <div class="input-group">
                                    <input type="number" name="insurance_deposit_amount" class="form-control"
                                        value="{{ get_setting('insurance_deposit_amount') ? get_setting('insurance_deposit_amount') : '500' }}">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            {{ \App\Models\Currency::find(get_setting('system_default_currency'))->symbol }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-from-label">{{ translate('Allow All Users to Bid') }}</label>
                                <div class="col-sm-4">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" onclick="updateSettings(this, 'allow_all_user_to_bid')" <?php if (get_setting('allow_all_user_to_bid') == 1) {
                                        echo 'checked';
                                    } ?>>
                                <span class="slider round"></span>
                            </label>
                            <div class="alert"
                                style="color: #850026;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                                {{ translate('Setting this allows all users to bid on auctions even if they did not have insurance deposits') }}
                            </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
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
    </script>
@endsection
