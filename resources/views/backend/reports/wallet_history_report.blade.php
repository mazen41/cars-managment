@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">{{translate('Wallet Transaction Report')}}</h1>
    </div>
</div>
<form id="sort" action="" method="GET">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">{{ translate('Wallet Transaction') }}</h5>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-toggle="dropdown">
                                {{ translate('Bulk Action') }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                @can('wallet_transaction_report')
                                <input type="hidden" name="export_type" value="">
                                <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('PDF')">{{
                                    translate('Export PDF') }}</a>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('XLS')">{{
                                    translate('Export XLS') }}</a>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('CSV')">{{
                                    translate('Export CSV') }}</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @can('wallet_transaction_report')
                    <div class="float-right">
                        <button onclick="exportAll(this)" class="btn btn-outline-info" type="button" id="export-btn"
                            data-export-url="{{ route('wallet-transation-export') }}">
                            {{ translate('Export All') }}
                        </button>
                    </div>
                    @endcan
                    @if(Auth::user()->user_type != 'seller')
                    <div class="col-md-3 ml-auto">
                        <select id="demo-ease" class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0"
                            data-live-search="true" name="user_id">
                            <option value="">{{ translate('Choose User') }}</option>
                            @foreach ($users_with_wallet as $key => $user)
                            <option value="{{ $user->id }}" @if($user->id == $user_id) selected @endif >
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm aiz-date-range" id="search"
                                name="date_range" @isset($date_range) value="{{ $date_range }}" @endisset
                                placeholder="{{ translate('Daterange') }}">
                        </div>
                    </div>
                    <x-table-sort-filter />
                    <div class="col-md-2 mb-2">
                        <div class="aiz-checkbox-inline">
                            <label class="aiz-checkbox">
                                <input type="checkbox" name="approved" @if (request()->approved == 'on')
                                checked
                                @endif>
                                <span class="aiz-square-check"></span>
                                <span class="badge badge-inline badge-info">{{ translate('only approved?') }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-md btn-primary" type="submit">
                            {{ translate('Filter') }}
                        </button>
                    </div>
                </div>

                <div class="card-body">

                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                @if (auth()->user()->can('wallet_transaction_report'))
                                <th>
                                    <div class="form-group">
                                        <div class="aiz-checkbox-inline">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" class="check-all">
                                                <span class="aiz-square-check"></span>
                                            </label>
                                        </div>
                                    </div>
                                </th>
                                @else
                                <th data-breakpoints="lg">#</th>
                                @endif
                                <th>{{ translate('Customer')}}</th>
                                <th data-breakpoints="lg">{{ translate('Date') }}</th>
                                <th>{{ translate('Amount')}}</th>
                                <th data-breakpoints="lg">{{ translate('Payment Method')}}</th>
                                <th data-breakpoints="lg" class="text-right">{{ translate('Approval')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($wallets as $key => $wallet)
                            <tr>
                                @if (auth()->user()->can('wallet_transaction_report'))
                                <td>
                                    <div class="form-group">
                                        <div class="aiz-checkbox-inline">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" class="check-one" name="id[]"
                                                    value="{{ $wallet->id }}">
                                                <span class="aiz-square-check"></span>
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                @else
                                <td>{{ $key + 1 + ($wallets->currentPage() - 1) * $wallets->perPage() }}</td>
                                @endif
                                @if ($wallet->user != null)
                                <td>
                                    <a href="javascript:void(0)" onclick="showUserDetails({{ $wallet->user->id }})"
                                        class="text-primary">
                                        {{ $wallet->user->name }}
                                    </a>
                                </td>
                                @else
                                <td>{{ translate('User Not found') }}</td>
                                @endif
                                <td>{{ date('d-m-Y', strtotime($wallet->created_at)) }}</td>
                                <td class="{{ $wallet->amount < 0 ? 'text-danger' : 'text-success' }}">{{
                                    single_price($wallet->amount) }}</td>
                                <td>{{ translate(ucfirst(str_replace('_', ' ', $wallet ->payment_method))) }}</td>
                                <td class="text-right">
                                    @if ($wallet->offline_payment)
                                    @if ($wallet->approval)
                                    <span class="badge badge-inline badge-success">{{translate('Approved')}}</span>
                                    @else
                                    <span class="badge badge-inline badge-info">{{translate('Pending')}}</span>
                                    @endif
                                    @else
                                    N/A
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <x-table_pagination :data="$wallets" :paginate="request()->paginate" />
                </div>
            </div>
        </div>
</form>
@endsection
@section('modal')
<!-- User Details Modal -->
@include('modals.customer_details_modal')
@endsection
@section('script')
<script>
    $(document).on("change", ".check-all", function() {
        if (this.checked) {
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
    $('#paginate-select').on('change', function(){
        $('#sort').submit();
    })
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

    function bulk_export (type){
            var url = '{{route('wallet-transation-export')}}';
            $("#sort").attr("action", url);
            $('input[name="export_type"]').val(type);
            $('#sort').submit();
            $("#sort").attr("action", '');
        }
</script>
@endsection
