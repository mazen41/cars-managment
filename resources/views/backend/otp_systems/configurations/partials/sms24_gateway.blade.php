<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('SMS gateway 24') }}</h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                <input type="hidden" name="otp_method" value="sms24_gateway">
                @csrf
                <div class="form-group row">
                    <input type="hidden" name="types[]" value="SMS24GATEWAY_API_KEY">
                    <div class="col-lg-3">
                        <label class="col-from-label">{{ translate('WHATSAPP PHONE NUMBER') }}</label>
                    </div>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" name="SMS24GATEWAY_API_KEY"
                            value="{{ env('SMS24GATEWAY_API_KEY') }}" placeholder="{{translate('API Key')}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <input type="hidden" name="types[]" value="SMS24GATEWAY_DEVICE_ID">
                    <div class="col-lg-3">
                        <label class="col-from-label">{{ translate('Device ID') }}</label>
                    </div>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" name="SMS24GATEWAY_DEVICE_ID"
                            value="{{ env('SMS24GATEWAY_DEVICE_ID') }}" placeholder="device id" required>
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
