@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="aiz-titlebar text-left mt-2 pb-2 px-3 px-md-2rem border-bottom border-gray">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3">{{ translate('Edit Price Adjustment') }}</h1>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('price_adjustment.update', $adjustment->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input name="_method" type="hidden" value="POST">
                <input type="hidden" name="id" value="{{ $adjustment->id }}">
                <input type="hidden" name="lang" value="{{ $lang }}">
                @if ($adjustment->is_commission)
                <input type="hidden" value="1" name="is_active">
                <input type="hidden" value="{{$adjustment->label}}" name="label">
                <input type="hidden" value="{{$adjustment->type}}" name="type">
                <input type="hidden" value="{{$adjustment->provider}}" name="provider">

                @endif
                <!-- Language Bar -->
                <ul class="nav nav-tabs nav-fill language-bar">
                    @foreach (get_all_active_language() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3"
                            href="{{ route('price_adjustment.edit', ['adjustment' => $adjustment->id, 'lang' => $language->code]) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11"
                                class="mr-1">
                            <span>{{$language->name}}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>

                <div class="card">
                    <div class="card-body">
                         <!-- Status -->
                         @if(!$adjustment->is_commission)
                         <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Status')}}</label>
                            <div class="col-md-9">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" name="is_active" value="1" @if($adjustment->is_active)
                                    checked @endif >
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        @endif
                        <!-- Label -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Label')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="label"
                                    value="{{ $adjustment->getTranslation('label', $lang) }}"
                                    placeholder="{{ translate('Label') }}" required @readonly($adjustment->is_commission)>
                            </div>
                        </div>
                        @if(!$adjustment->is_commission)
                        <!-- Type -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Type')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="type" required >
                                    <option value="addition" @if($adjustment->type == 'addition') selected @endif>
                                        {{translate('Addition')}}
                                    </option>
                                    <option value="deduction" @if($adjustment->type == 'deduction') selected @endif>
                                        {{translate('Deduction')}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        @else
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Type')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control"
                                    value="{{ translate(ucfirst($adjustment->type)) }}"
                                    placeholder="{{ translate('Type') }}" disabled>
                            </div>
                        </div>
                        @endif
                        <!-- Amount -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Amount')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="number" class="form-control" name="amount"
                                    value="{{ $adjustment->amount }}" placeholder="{{ translate('Amount') }}" required>
                            </div>
                        </div>

                        <!-- Amount Type -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Amount Type')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="amount_type" required>
                                    <option value="fixed" @if($adjustment->amount_type == 'fixed') selected
                                        @endif>{{translate('Fixed')}}</option>
                                    <option value="percentage" @if($adjustment->amount_type == 'percentage') selected
                                        @endif>{{translate('Percentage')}}</option>
                                </select>
                            </div>
                        </div>
                        @if(!$adjustment->is_commission)
                        <!-- Provider -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Provider')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="provider" required @readonly($adjustment->is_commission)>
                                    <option value="alibaba" @if($adjustment->provider == 'alibaba') selected @endif>
                                        {{translate('Alibaba')}}
                                    </option>
                                    <option value="aliexpress" @if($adjustment->provider == 'aliexpress') selected
                                        @endif>
                                        {{translate('AliExpress')}}
                                    </option>
                                    <option value="shein" @if($adjustment->provider == 'shein') selected @endif>
                                        {{translate('SHEIN')}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        @else
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Provider')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control"
                                    value="{{ translate(ucfirst($adjustment->provider)) }}"
                                    placeholder="{{ translate('Label') }}" disabled>
                            </div>
                        </div>
                        @endif
                        <!-- Base Calculation -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Base Calculation')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="base_calculation" required>
                                    <option value="subtotal" @if($adjustment->base_calculation == 'subtotal') selected
                                        @endif>
                                        {{translate('Subtotal')}}
                                    </option>
                                    <option value="shipping" @if($adjustment->base_calculation == 'shipping') selected
                                        @endif>
                                        {{translate('Shipping')}}
                                    </option>
                                    <option value="subtotal_with_shipping" @if($adjustment->base_calculation ==
                                        'subtotal_with_shipping') selected @endif>
                                        {{translate('Subtotal With Shipping')}}
                                    </option>
                                    <option value="grand_total" @if($adjustment->base_calculation == 'grand_total')
                                        selected @endif>
                                        {{translate('Grand Total')}}
                                    </option>
                                </select>
                            </div>
                        </div>


                        <!-- Update Button -->
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary">{{translate('Save Changes')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function(){
        AIZ.plugins.bootstrapSelect('refresh');
    });
</script>
@endsection
