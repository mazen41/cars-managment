<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="jawali">
    <div class="form-group row">
        <input type="hidden" name="types[]" value="JAWALI_USERNAME">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Jawali user') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="JAWALI_USERNAME"
                value="{{ env('JAWALI_USERNAME') }}" placeholder="{{ translate('Jawali user') }}"
                required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="JAWALI_PASS">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Jawali password') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="JAWALI_PASS"
                value="{{ env('JAWALI_PASS') }}" placeholder="{{ translate('Jawali password') }}"
                required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="JAWALI_ORGID">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Jawali org id') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="JAWALI_ORGID"
                value="{{ env('JAWALI_ORGID') }}" placeholder="{{ translate('Jawali client id') }}"
                required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="JAWALI_AGENT_ID">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Jawali agent id') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="JAWALI_AGENT_ID"
                value="{{ env('JAWALI_AGENT_ID') }}" placeholder="{{ translate('Jawali agent id') }}"
                required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="JAWALI_AGENT_PWD">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Jawali agent password') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="JAWALI_AGENT_PWD"
                value="{{ env('JAWALI_AGENT_PWD') }}" placeholder="{{ translate('Jawali agent password') }}"
                required>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>
