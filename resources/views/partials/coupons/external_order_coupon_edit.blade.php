@php
    $details = json_decode($coupon->details);
@endphp
<div class="card-header mb-2">
    <h5 class="mb-0 h6">{{translate('Add Your External Order Coupon')}}</h5>
</div>
<div class="form-group row">
    <label class="col-lg-3 control-label" for="code">{{translate('Coupon code')}}</label>
    <div class="col-lg-9">
        <input type="text" placeholder="{{translate('Coupon code')}}" id="code" name="code" value="{{ $coupon->code }}" class="form-control" required>
    </div>
</div>
<div class="product-choose-list">
    <div class="product-choose">
        <div class="form-group row">
            <label class="col-lg-3 control-label" for="name">{{translate('Provider')}}</label>
            <div class="col-lg-9">
                <select name="provider" class="form-control product_id aiz-selectpicker" data-live-search="true" data-selected-text-format="count" required>
                    @foreach($providers as $provider)
                        <option value="{{$provider}}" @if($details->provider == $provider) selected @endif>{{ translate(ucfirst($provider)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="form-group row">
    <label class="col-lg-3 col-from-label">{{translate('Base Discount')}}</label>
    <div class="col-lg-9">
        <select class="form-control aiz-selectpicker" name="base_discount">
            <option value="all" @if(isset($details->base_discount) && $details->base_discount == 'all') selected @endif>{{translate('Product Price + Shipping Fee')}}</option>
            <option value="shipping_only" @if(isset($details->base_discount) && $details->base_discount == 'shipping_only') selected @endif>{{translate('Shipping Fee Only')}}</option>
        </select>
    </div>
</div>
<div class="form-group row">
    <label class="col-lg-3 col-from-label">{{translate('Minimum Shopping')}}</label>
    <div class="col-lg-9">
       <input type="number" lang="en" min="0" step="0.01" name="min_buy" class="form-control" value="{{ $details->min_buy }}" required>
    </div>
  </div>
  <div class="form-group row">
    <label class="col-lg-3 col-from-label">{{translate('Maximum Discount Amount')}}</label>
    <div class="col-lg-9">
       <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Maximum Discount Amount')}}" name="max_discount" class="form-control" value="{{ $details->max_discount }}" required>
    </div>
  </div>
@php
  $start_date = date('m/d/Y', $coupon->start_date);
  $end_date = date('m/d/Y', $coupon->end_date);
@endphp
<div class="form-group row">
    <label class="col-sm-3 control-label" for="start_date">{{translate('Date')}}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control aiz-date-range" value="{{ $start_date .' - '. $end_date }}" name="date_range" placeholder="{{ translate('Select Date') }}">
    </div>
</div>

<div class="form-group row">
   <label class="col-lg-3 col-from-label">{{translate('Discount')}}</label>
   <div class="col-lg-7">
       <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Discount')}}" value="{{ $coupon->discount }}" name="discount" class="form-control" required>

   </div>
   <div class="col-lg-2">
       <select class="form-control aiz-selectpicker" name="discount_type">
           <option value="amount" @if ($coupon->discount_type == 'amount') selected  @endif>{{translate('Amount')}}</option>
           <option value="percent" @if ($coupon->discount_type == 'percent') selected  @endif>{{translate('Percent')}}</option>
       </select>
   </div>
</div>

<script type="text/javascript">

    $(document).ready(function(){
        $('.aiz-date-range').daterangepicker();
        AIZ.plugins.bootstrapSelect('refresh');
    });

</script>
