@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Commession Configuration') }}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="types[]" value="inspection_commission_type">
                        <div class="form-group row">
                            <label class="col-sm-4 col-from-label">{{ translate('Commission Type') }}</label>
                            <select class="aiz-selectpicker form-select" name="inspection_commission_type"
                                data-selected="{{ get_setting('inspection_commission_type') }}"
                                data-live-search="true" data-minimum-results-for-search="Infinity">
                                <option value="percent" @if(get_setting('inspection_commission_type') === 'percent') selected @endif>{{ translate('Percentage') }}</option>
                                <option value="flat" @if(get_setting('inspection_commission_type') === 'flat') selected @endif>{{ translate('Flat') }}</option>
                            </select>
                        </div>
                        <div class="form-group row" id="commission_div">
                            <label class="col-sm-4 col-from-label">{{ translate('Commission Rate') }}</label>
                            <div class="col-sm-8">
                                <input type="hidden" name="types[]" value="inspection_commission">
                                <div class="input-group">
                                    <input type="number" name="inspection_commission" class="form-control"
                                        value="{{ get_setting('inspection_commission') ? get_setting('inspection_commission') : '0' }}">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupPrepend">
                                            @if (get_setting('inspection_commission_type') === 'percent')
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
    $(document).ready(function(){
         AIZ.plugins.bootstrapSelect('refresh');
        $('select[name="inspection_commission_type"]').on('change', function(){
            if($(this).val() === 'percent'){
                $('#inputGroupPrepend').html('%');
            } else if($(this).val() === 'flat'){
                $('#inputGroupPrepend').html("{{ \App\Models\Currency::find(get_setting('system_default_currency'))->symbol }}")
            }
        });
    });
</script>
@endsection
