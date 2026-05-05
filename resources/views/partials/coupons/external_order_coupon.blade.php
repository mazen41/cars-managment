<div class="card-header mb-2">
    <h3 class="h6">{{translate('Add Your External Order Coupon')}}</h3>
</div>
<div class="form-group row">
    <label class="col-lg-3 col-from-label" for="code">{{translate('Coupon code')}}</label>
    <div class="col-lg-9">
        <input type="text" placeholder="{{translate('Coupon code')}}" id="code" name="code" class="form-control" required>
    </div>
</div>
<div class="product-choose-list">
    <div class="product-choose">
        <div class="form-group row">
            <label class="col-lg-3 col-from-label" for="name">{{translate('Provider')}}</label>
            <div class="col-lg-9">
                <select name="provider" class="form-control product_id aiz-selectpicker" data-live-search="true" data-selected-text-format="count" required>
                    @foreach($providers as $provider)
                        <option value="{{$provider}}">{{ translate(ucfirst($provider)) }}</option>
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
            <option value="all">{{translate('Product Price + Shipping Fee')}}</option>
            <option value="shipping_only">{{translate('Shipping Fee Only')}}</option>
        </select>
    </div>
</div>
<br>
<div class="form-group row">
    <label class="col-lg-3 col-from-label">{{translate('Minimum Shopping')}}</label>
    <div class="col-lg-9">
       <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Minimum Shopping')}}" name="min_buy" class="form-control" required>
    </div>
 </div>
 <div class="form-group row">
    <label class="col-lg-3 col-from-label">{{translate('Maximum Discount Amount')}}</label>
    <div class="col-lg-9">
       <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Maximum Discount Amount')}}" name="max_discount" class="form-control" required>
    </div>
 </div>
<div class="form-group row">
    <label class="col-sm-3 control-label" for="start_date">{{translate('Date')}}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="{{ translate('Select Date') }}">
    </div>
</div>
<div class="form-group row">
   <label class="col-lg-3 col-from-label">{{translate('Discount')}}</label>
   <div class="col-lg-7">
      <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Discount')}}" name="discount" class="form-control" required>
   </div>
   <div class="col-lg-2">
       <select class="form-control aiz-selectpicker" name="discount_type">
           <option value="amount">{{translate('Amount')}}</option>
           <option value="percent">{{translate('Percent')}}</option>
       </select>
   </div>
</div>

@include('backend.js.user_ajax_search')
<script type="text/javascript">

    $(document).ready(function(){
        $('.aiz-date-range').daterangepicker();
        AIZ.plugins.bootstrapSelect('refresh');
    });

</script>
