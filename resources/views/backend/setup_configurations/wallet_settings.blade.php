@extends('backend.layouts.app')

@section('content')

<h4 class="text-center text-muted">{{ translate('Wallet Settings') }}</h4>
<div class="row mt-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6 text-center">{{ translate('Wallet System Activation') }}</h3>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateSettings(this, 'wallet_system')" <?php if (get_setting('wallet_system') == 1) {
                        echo 'checked';
                    } ?>>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6 text-center">{{ translate('Wallet recharge') }}</h5>
                </div>
                <div class="card-body text-center">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'recharge_wallet_active')" <?php if
                            (get_setting('recharge_wallet_active')==1) { echo 'checked' ; } ?>>
                        <span class="slider round"></span>
                    </label>
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

            $.post("{{ route('business_settings.update.activation') }}", {
                _token: '{{ csrf_token() }}',
                type: type,
                value: value
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', "{{ translate('Settings updated successfully') }}");
                } else {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
</script>
@endsection
