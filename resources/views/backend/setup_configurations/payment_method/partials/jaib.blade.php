<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="jaib">
    <div class="form-group row">
        <input type="hidden" name="types[]" value="JAIB_USER">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Jaib user') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="JAIB_USER"
                value="{{ env('JAIB_USER') }}" placeholder="{{ translate('Jaib user') }}"
                required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="JAIB_PASS">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Jaib password') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="JAIB_PASS"
                value="{{ env('JAIB_PASS') }}" placeholder="{{ translate('Jaib password') }}"
                required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="JAIB_AGENT_CODE">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Agent Code') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="JAIB_AGENT_CODE"
                value="{{ env('JAIB_AGENT_CODE') }}" placeholder="{{ translate('Agent Code') }}"
                required>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>
{{-- change password ---}}
<h5 class="mb-0 h6">{{__('Change password')}}</h5>
<form class="form-horizontal mt-3" action="{{ route('jaib.change-password') }}" method="POST">
    @csrf
    <div class="form-group row">

        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Current password') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="current_pass"
                value="{{ env('JAIB_PASS') }}"
                required>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('New password') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="new_pass"
                value="" required>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Change password') }}</button>
    </div>
</form>
