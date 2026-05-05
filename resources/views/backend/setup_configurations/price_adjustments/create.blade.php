@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="aiz-titlebar text-left mt-2 pb-2 px-3 px-md-2rem border-bottom border-gray">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3">{{ translate('Add New Price Adjustment') }}</h1>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('price_adjustment.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="lang" value="{{ env('DEFAULT_LANGUAGE') }}">

                <div class="card">
                    <div class="card-body">
                        <!-- Status -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Status')}}</label>
                            <div class="col-md-9">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" name="is_active" value="1" checked>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <!-- Label -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Label')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="label"
                                    value="{{ old('label') }}"
                                    placeholder="{{ translate('Label') }}" required>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Type')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="type" required>
                                    <option value="addition" @if(old('type') == 'addition') selected @endif>
                                        {{translate('Addition')}}
                                    </option>
                                    <option value="deduction" @if(old('type') == 'deduction') selected @endif>
                                        {{translate('Deduction')}}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Amount')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="number" class="form-control" name="amount"
                                    value="{{ old('amount') }}"
                                    placeholder="{{ translate('Amount') }}" required>
                            </div>
                        </div>

                        <!-- Amount Type -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Amount Type')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="amount_type" required>
                                    <option value="fixed" @if(old('amount_type') == 'fixed') selected @endif>
                                        {{translate('Fixed')}}
                                    </option>
                                    <option value="percentage" @if(old('amount_type') == 'percentage') selected @endif>
                                        {{translate('Percentage')}}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Provider -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Provider')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="provider" required>
                                    <option value="alibaba" @if(old('provider') == 'alibaba') selected @endif>
                                        {{translate('Alibaba')}}
                                    </option>
                                    <option value="aliexpress" @if(old('provider') == 'aliexpress') selected @endif>
                                        {{translate('AliExpress')}}
                                    </option>
                                    <option value="shein" @if(old('provider') == 'shein') selected @endif>
                                        {{translate('SHEIN')}}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Base Calculation -->
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{translate('Base Calculation')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="base_calculation" required>
                                    <option value="subtotal" @if(old('base_calculation') == 'subtotal') selected @endif>
                                        {{translate('Subtotal')}}
                                    </option>
                                    <option value="shipping" @if(old('base_calculation') == 'shipping') selected @endif>
                                        {{translate('Shipping')}}
                                    </option>
                                    <option value="subtotal_with_shipping" @if(old('base_calculation') == 'subtotal_with_shipping') selected @endif>
                                        {{translate('Subtotal With Shipping')}}
                                    </option>
                                    <option value="grand_total" @if(old('base_calculation') == 'grand_total') selected @endif>
                                        {{translate('Grand Total')}}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
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
