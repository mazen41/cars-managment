<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="floosak">
    <div class="form-group row">
        <input type="hidden" name="types[]" value="FLOOSAK_PHONE_NUMBER">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Floosak phone') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="FLOOSAK_PHONE_NUMBER"
                value="{{ env('FLOOSAK_PHONE_NUMBER') }}" placeholder="{{ translate('Floosak phone') }}"
                required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="FLOOSAK_SHORT_CODE">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Floosak short code') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="FLOOSAK_SHORT_CODE"
                value="{{ env('FLOOSAK_SHORT_CODE') }}" placeholder="{{ translate('Floosak short code') }}"
                required>
        </div>
    </div>

    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>
<div class="row">
    <div class="col">
     <button id="get_key_btn" class="btn btn-sm btn-primary rounded-2" disabled onclick="get_floosak_key()" >{{translate('Get key')}}</button>
    </div>
 </div>
 <div class="mt-1" id="floosak_expire_at"></div>
 <!-- modal -->
 <div class="modal fade" id="verify_key_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('Verify Key') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="edit_modal_body">

                <div class="form-group row">
                    <div class="col-md-4">
                        <label class="col-from-label">{{ translate('Floosak request id') }}</label>
                    </div>
                    <div class="col-md-8">
                    <input type="text" class="form-control" name="floosak_request_id"
                    value="" readonly
                    required>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-4">
                        <label class="col-from-label">{{ translate('Floosak sms otp') }}</label>
                    </div>
                    <div class="col-md-8">
                    <input  type="text" class="form-control" name="floosak_otp_code"
                    value=""
                    required>
                    </div>
                </div>
                <span class="text-info ">{{translate('You will recieve an sms otp from Floosak')}}</span>
               <div class="row mt-3 float-right">
                <button class="btn btn-sm btn-primary rounded-2 " onclick="verify_floosak_key()">{{translate('Verify key')}}</button>
               </div>
            </div>
        </div>
    </div>
</div>
