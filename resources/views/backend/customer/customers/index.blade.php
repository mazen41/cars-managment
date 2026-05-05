@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="align-items-center">
        <h1 class="h3">{{translate('All Customers')}}</h1>
    </div>
</div>


<div class="card">
    <form class="" id="sort_customers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-0 h6">{{translate('Customers')}}</h5>
            </div>
            @canany(['delete_customer', 'export_customer'])
            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    @can('delete_customer')

                    <a class="dropdown-item confirm-alert" href="javascript:void(0)"
                        data-target="#bulk-delete-modal">{{translate('Delete selection')}}</a>

                    @endcan
                    @can('export_customer')
                    <a class="dropdown-item" href="javascript:void(0)" onclick="customer_bulk_export('PDF')">{{
                        translate('Export PDF') }}</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="customer_bulk_export('XLS')">{{
                        translate('Export XLS') }}</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="customer_bulk_export('CSV')">{{
                        translate('Export CSV') }}</a>
                    @endcan
                </div>
            </div>
            @can('export_customer')
            <div class="mb-2 mb-md-0">
                <button onclick="exportAll(this)" class="btn btn-outline-info" type="button" id="export-btn" data-export-url="{{ route('customer-bulk-export') }}">
                    {{ translate('Export All') }}
                </button>
            </div>
            @endcan
            @endcan
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="email_verification" onchange="sort_customers(this)">
                        <option value="">{{ translate('Email Verification') }}</option>
                        <option value="verified" @if(request()->email_verification == 'verified') selected @endif>
                            {{ translate('Verified') }}
                        </option>
                        <option value="unverified" @if(request()->email_verification == 'unverified') selected @endif>
                            {{ translate('Unverified') }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="phone_verification" onchange="sort_customers(this)">
                        <option value="">{{ translate('Phone Verification') }}</option>
                        <option value="verified" @if(request()->phone_verification == 'verified') selected @endif>
                            {{ translate('Verified') }}
                        </option>
                        <option value="unverified" @if(request()->phone_verification == 'unverified') selected @endif>
                            {{ translate('Unverified') }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search"
                        value="{{ request()->search }}"
                        placeholder="{{ translate('Type email or name & Enter') }}">
                </div>
            </div>
            <x-table-sort-filter />
            <div class=" mt-2 col-md-6 mb-2 d-flex">
                <div class="aiz-checkbox-inline mr-2">
                    <label class="aiz-checkbox">
                        <input type="checkbox" onchange="sort_customers(this)" name="with_credit" @if ( request()->with_credit == 'on')
                            checked
                        @endif>
                        <span class="aiz-square-check"></span>
                        <span class="badge badge-inline badge-info">{{ translate('Only with credit?') }}</span>
                    </label>
                </div>
                 <div class="aiz-checkbox-inline mr-2">
                    <label class="aiz-checkbox">
                        <input type="checkbox" onchange="sort_customers(this)" name="deletion_request" @if ( request()->deletion_request == 'on')
                            checked
                        @endif>
                        <span class="aiz-square-check"></span>
                        <span class="badge badge-inline badge-danger">{{ translate('Deletion requests') }}</span>
                    </label>
                </div>
                 <div class="aiz-checkbox-inline mr-2   ">
                    <label class="aiz-checkbox">
                        <input type="checkbox" onchange="sort_customers(this)" name="banned" @if ( request()->banned == 'on')
                            checked
                        @endif>
                        <span class="aiz-square-check"></span>
                        <span class="badge badge-inline badge-danger">{{ translate('Banned') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <!--<th data-breakpoints="lg">#</th>-->
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <input type="hidden" name="export_type" value="">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{translate('Name')}}</th>
                        <th data-breakpoints="lg">{{translate('Email Address')}}</th>
                        <th data-breakpoints="lg">{{translate('Phone')}}</th>
                        <th data-breakpoints="lg">{{translate('Orders')}}</th>
                        <th data-breakpoints="lg">{{translate('Paid Amount')}}</th>
                        <th data-breakpoints="lg">{{translate('Unpaid Amount')}}</th>
                        <th data-breakpoints="lg">{{translate('Refunds Amount')}}</th>
                        <th data-breakpoints="lg">{{translate('Wallet Balance')}}</th>
                        <th data-breakpoints="lg">{{translate('Join Date')}}</th>
                        <th class="text-right">{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $key => $user)
                    @if ($user != null)
                    <tr>
                        <!--<td>{{ ($key+1) + ($users->currentPage() - 1)*$users->perPage() }}</td>-->
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$user->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{$user->name}}
                            @if($user->banned == 1)<br><span class="badge badge-inline badge-danger  ml-1">{{translate('Banned')}}
                                <i class="la la-user-slash" aria-hidden="true"></i></span>@endif
                            @if ($user->deletion_request)
                            <br><span class="badge badge-inline badge-danger  ml-1">{{translate('Deletion Requested')}}
                                <i class="la la-trash" aria-hidden="true"></i></span>
                            @endif
                        </td>
                        <td>
                            {{$user->email?? '-'}}
                            @if($user->email)
                            <br>
                                <span class="badge badge-inline  @if($user->email_verified_at) badge-success @else badge-warning @endif ml-1">
                                    <i class="las la-envelope"></i>
                                    {{ $user->email_verified_at ? translate('Verified') : translate('Unverified') }}
                                </span>
                            @endif
                        </td>
                        <td dir="ltr">
                            {{$user->phone?? '-'}}
                            @if($user->phone)
                            <br>
                            <span class="badge badge-inline @if($user->phone_verified_at) badge-success @else badge-warning @endif">
                                <i class="las la-phone"></i>
                                {{ $user->phone_verified_at ? translate('Verified') : translate('Unverified') }}
                            </span>
                            @endif
                        </td>
                        <td>
                            {{$user->orders_count}}
                        </td>
                        <td>{{single_price($user->paid_amount)}}</td>
                        <td>{{single_price($user->unpaid_amount)}}</td>
                        <td>{{single_price($user->refund_amount)}}</td>
                        <td>{{single_price($user->balance)}}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($user->created_at)->locale(App::getLocale() == 'ye' ? 'ar' : App::getLocale())->diffForHumans() }}</td>
                        <td class="text-right">
                            @can('ban_customer')
                            @if($user->banned != 1)
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm"
                                onclick="confirm_ban('{{route('customers.ban', encrypt($user->id))}}');"
                                title="{{ translate('Ban this Customer') }}">
                                <i class="las la-user-slash"></i>
                            </a>
                            @else
                            <a href="#" class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                onclick="confirm_unban('{{route('customers.ban', encrypt($user->id))}}');"
                                title="{{ translate('Unban this Customer') }}">
                                <i class="las la-user-check"></i>
                            </a>
                            @endif
                            @endcan
                            @can('delete_customer')
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                data-href="{{route('customers.destroy', $user->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                            @endcan
                            @can('verify_customer')
                            @if($user->phone)
                            @if(!$user->phone_verified_at)
                            <a href="javascript:void(0)" onclick="verifyPhone('{{$user->id}}')"
                                class="btn btn-soft-warning btn-icon btn-circle btn-sm"
                                title="{{ translate('Verify Phone') }}">
                                <i class="las la-phone"></i>
                            </a>
                            @else
                            <a href="javascript:void(0)" onclick="unverifyPhone('{{$user->id}}')"
                                class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                title="{{ translate('Phone Verified - Click to Unverify') }}">
                                <i class="las la-phone-slash"></i>
                            </a>
                            @endif
                            @endif

                            @if($user->email)
                            @if(!$user->email_verified_at)
                            <a href="javascript:void(0)" onclick="verifyEmail('{{$user->id}}')"
                                class="btn btn-soft-warning btn-icon btn-circle btn-sm"
                                title="{{ translate('Verify Email') }}">
                                <i class="las la-envelope"></i>
                            </a>
                            @else
                            <a href="javascript:void(0)" onclick="unverifyEmail('{{$user->id}}')"
                                class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                title="{{ translate('Email Verified - Click to Unverify') }}">
                                <i class="las la-envelope-open"></i>
                            </a>
                            @endif
                            @endif
                            @endcan
                            <a href="{{ route('customers.details', $user->id) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('View Details') }}">
                                <i class="las la-list"></i>
                            </a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            <x-table_pagination :data="$users" :paginate="request()->paginate" />
        </div>
    </form>
</div>

@endsection

@section('modal')
<!-- Delete modal -->
@include('modals.delete_modal')
<!-- Bulk Delete modal -->
@include('modals.bulk_delete_modal')
<!-- Ban modal -->
@include('modals.customer_ban_modal')
@endsection

@section('script')
@include('backend.customer.customers.customer_js')
<script type="text/javascript">
    $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });
        function customer_bulk_export (type){
            var url = '{{route('customer-bulk-export')}}';
            $("#sort_customers").attr("action", url);
            $('input[name="export_type"]').val(type);
            $('#sort_customers').submit();
            $("#sort_customers").attr("action", '');
        }
        $('#paginate-select').on('change', function(){
            $('#sort_customers').submit();
        })
        function sort_customers(el){
            $('#sort_customers').submit();
        }


        function exportAll(el) {

            const form = el.closest('form');
            var formData = new FormData(form);
            const url = el.getAttribute('data-export-url');

            var exportAllBtn = $('#export-btn');
            exportAllBtn.prop('disabled', true);
            exportAllBtn.removeClass('btn-outline-info').addClass('btn-success');
            exportAllBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ translate('Please wait') }}');

            // Add extra fixed param
            formData.append('select_all', true);
            const formDataObject = {};
            for (let [key, value] of formData.entries()) {
                formDataObject[key] = value;
            }
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                data: formDataObject,

                success: function (response) {
                    if (response.success) {
                        AIZ.plugins.notify('success', response.message);
                    } else {
                        AIZ.plugins.notify('danger', response.message);
                    }
                    exportAllBtn.prop('disabled', false).html('{{ translate('Export All') }}')
                        .removeClass('btn-success').addClass('btn-outline-info');
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        AIZ.plugins.notify('warning', xhr.responseJSON.message);
                    } else {
                        AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                    }
                    exportAllBtn.prop('disabled', false).html('{{ translate('Export All') }}')
                        .removeClass('btn-success').addClass('btn-outline-info');
                }
            });
        }

        function bulk_delete() {
            var data = new FormData($('#sort_customers')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-customer-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }
</script>
@endsection
