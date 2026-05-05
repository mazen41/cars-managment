@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3 class="fs-18 mb-0">{{translate('Send Bulk SMS')}}</h3>
            </div>
            <form class="form-horizontal" action="{{ route('sms.send') }}" method="POST" enctype="multipart/form-data">
            	@csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-2 control-label" for="name">{{translate('Mobile Users')}}</label>
                        <div class="col-sm-10">
                            <div class="mb-2 d-flex">
                                <span class="mb-0 mr-1" for="all-customers">{{ translate('All Customers') }}</span>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" id="all-customers" name="all_customers" value="1" class="aiz-checkbox" checked>
                                    <span class="slider round"></span>
                                </label>

                            </div>
                            <select class="form-control selectpicker" id="customer-select" data-live-search="true"
                            data-show-phone="true" data-with-phone="true" data-with-phone-verified="true"
                            data-ajax-url="{{ route('users.ajax.search') }}" data-size="10" name="user_ids[]" multiple>
                                <option value="">{{translate('Select Customer')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 control-label" for="name">{{translate('SMS content')}}</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" name="content" required></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">{{translate('Template ID')}}</label>
                        <div class="col-md-10">
                            <input type="text" name="template_id"  class="form-control" placeholder="{{translate('Template Id')}}">
                            <small class="form-text text-danger">{{ ('**N.B : Template ID is Required Only for Fast2SMS DLT Manual & Whatsapp **') }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" type="submit">{{translate('Send')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('script')
@include('backend.js.user_ajax_search')
<script type="text/javascript">
 $('#all-customers').change(function() {
            if($(this).is(':checked')) {
                // Disable the select
                $('#customer-select').prop('disabled', true);
                $('#customer-select').selectpicker('refresh');
                // Clear any selected values
                $('#customer-select').selectpicker('deselectAll');
            } else {
                // Enable the select
                $('#customer-select').prop('disabled', false);
                $('#customer-select').selectpicker('refresh');
            }
        });
        $('#all-customers').trigger('change');
</script>
@endsection
