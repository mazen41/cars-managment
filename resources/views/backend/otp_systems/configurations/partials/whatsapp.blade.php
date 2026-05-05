<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Whatsapp Credentials') }}</h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                <input type="hidden" name="otp_method" value="whatsapp">
                @csrf
                <div class="form-group row">
                    <input type="hidden" name="types[]" value="WHATSAPP_PHONE_NUMBER_ID">
                    <div class="col-lg-3">
                        <label class="col-from-label">{{ translate('WHATSAPP PHONE NUMBER') }}</label>
                    </div>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" name="WHATSAPP_PHONE_NUMBER_ID"
                            value="{{ env('WHATSAPP_PHONE_NUMBER_ID') }}" placeholder="{{translate('WHATSAPP PHONE NUMBER')}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <input type="hidden" name="types[]" value="WHATSAPP_API_ACCESS_TOKEN">
                    <div class="col-lg-3">
                        <label class="col-from-label">{{ translate('Whatsapp API TOKEN') }}</label>
                    </div>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" name="WHATSAPP_API_ACCESS_TOKEN"
                            value="{{ env('WHATSAPP_API_ACCESS_TOKEN') }}" placeholder="Whatsap API TOKEN" required>
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
