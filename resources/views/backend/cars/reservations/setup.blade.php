@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Car Reservation Amount') }}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-4 col-from-label">{{ translate('Reservation Amount') }}</label>
                            <div class="col-sm-8">
                                <input type="hidden" name="types[]" value="car_reservation_amount">
                                <div class="input-group">
                                    <input type="number" name="car_reservation_amount" class="form-control"
                                        value="{{ get_setting('car_reservation_amount') ? get_setting('car_reservation_amount') : '0' }}">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            {{ \App\Models\Currency::find(get_setting('system_default_currency'))->symbol }}
                                        </span>
                                    </div>
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
        <div class="col-lg-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Car Reservation Commission') }}</h5>
                </div>
                <div class="card-body">
                    <form id="commission_form" class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="types[]" value="reservation_commission_type">
                        <div class="form-group row">
                            <label class="col-sm-4 col-from-label">{{ translate('Commission Type') }}</label>
                            <select class="aiz-selectpicker form-select" name="reservation_commission_type"
                                data-selected="{{ get_setting('reservation_commission_type') }}"
                                data-live-search="true" data-minimum-results-for-search="Infinity" required>
                                <option value="percent" @if(get_setting('reservation_commission_type') === 'percent') selected @endif>{{ translate('Percentage') }}</option>
                                <option value="flat" @if(get_setting('reservation_commission_type') === 'flat') selected @endif>{{ translate('Flat') }}</option>
                            </select>
                        </div>
                        <div class="form-group row" id="commission_div">
                            <label class="col-sm-4 col-from-label">{{ translate('Commission Rate') }}</label>
                            <div class="col-sm-8">
                                <input type="hidden" name="types[]" value="reservation_commission">
                                <div class="input-group">
                                    <input type="number" name="reservation_commission" class="form-control"
                                        value="{{ get_setting('reservation_commission') ? get_setting('reservation_commission') : '0' }}" required>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupPrepend">
                                            @if (get_setting('reservation_commission_type') === 'percent')
                                                %
                                                @else
                                                {{ \App\Models\Currency::find(get_setting('system_default_currency'))->symbol }}
                                            @endif
                                        </span>
                                    </div>
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
<script>
   $('#commission_form').on('submit', function(e) {
    e.preventDefault();

    const form = $(this);
    const reservation_amount = parseFloat($('input[name="car_reservation_amount"]').val()) || 0;
    const commission_type = $('select[name="reservation_commission_type"]').val();
    const commission_amount = parseFloat($('input[name="reservation_commission"]').val()) || 0;

    if (!reservation_amount) {
        AIZ.plugins.notify('danger', '{{ translate("Please set reservation amount first") }}');
        return;
    }

    if ((commission_type === 'flat' && commission_amount > reservation_amount) || (commission_type === 'percent' && commission_amount > 100)) {
        AIZ.plugins.notify('danger', '{{ translate("Reservation commission can not be greater than reservation amount") }}');
        return;
    }


    form.off('submit').submit(); // Prevent infinite loop
    });

    $(document).ready(function(){
         AIZ.plugins.bootstrapSelect('refresh');
        $('select[name="reservation_commission_type"]').on('change', function(){
            if($(this).val() === 'percent'){
                $('#inputGroupPrepend').html('%');
            } else if($(this).val() === 'flat'){
                $('#inputGroupPrepend').html("{{ \App\Models\Currency::find(get_setting('system_default_currency'))->symbol }}")
            }
        });
    });
</script>
@endsection
