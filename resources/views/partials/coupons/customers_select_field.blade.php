<div class="d-none" id="coupon_customers_fields">
    <div class="form-group row" id="is_welcome_coupon_field">
         <label class="col-lg-3 col-from-label"></label>
        <div class="col-lg-9">
            <div class="mb-2 d-flex">
                <span class="mb-0 mr-1" for="is_welcome_coupon">{{ translate('Welcome Coupon?') }}</span>
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" id="is_welcome_coupon" name="is_welcome_coupon" class="aiz-checkbox"
                        @if($is_welcome_coupon ?? false) checked @endif>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="form-group row" id="customer-select-field">
        <label class="col-lg-3 col-from-label" for="code">{{translate('Customers')}}</label>
        <div class="col-lg-9">
            <select class="form-control selectpicker" id="customer-select" data-live-search="true"
                data-ajax-url="{{ route('users.ajax.search') }}" data-size="10" name="user_ids[]" data-show-phone="true"
                data-show-email="true" multiple>
                @if ($customers != null)
                @foreach ($customers as $selected_user)
                <option value="{{ $selected_user->id }}" selected>
                    {{ $selected_user->name}}
                    @if ($selected_user->email)
                    ({{ $selected_user->email }})
                    @else
                    ({{$selected_user->phone}})
                    @endif
                </option>
                @endforeach
                @endif
            </select>
            <span class="text-info">{{translate('Leave empty to select all customers')}}</span>
        </div>
    </div>
</div>
@section('script')
@parent
@include('backend.js.user_ajax_search')
<script>
    $(document).ready(function() {
         $('#is_welcome_coupon').change(function() {
            if($(this).is(':checked')) {
                // Disable the select
                $('#customer-select').prop('disabled', true);
                $('#customer-select').selectpicker('refresh');
                // Clear any selected values
                $('#customer-select').selectpicker('deselectAll');
                $('#customer-select-field').addClass('d-none');
            } else {
                // Enable the select
                $('#customer-select').prop('disabled', false);
                $('#customer-select').selectpicker('refresh');
                // Show the select field
                $('#customer-select-field').removeClass('d-none');
            }
        });
        $('#is_welcome_coupon').trigger('change');
    });
</script>
@endsection
