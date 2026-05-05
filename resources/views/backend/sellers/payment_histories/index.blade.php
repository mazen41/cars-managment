@extends('backend.layouts.app')

@section('content')

<div class="card">
    <form action="" id="sort_payments" method="GET">
        <div class="card-header">
            <h3 class="mb-0 h6">{{translate('Seller Payments')}}</h3>
            @canany(['export_payment'])
            <div class="col-md-2 mb-2 mb-md-0">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        {{ translate('Bulk Action') }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        @can('export_payment')
                        <a class="dropdown-item" href="javascript:void(0)" onclick="payment_bulk_export('PDF')">{{
                            translate('Export PDF') }}</a>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="payment_bulk_export('XLS')">{{
                            translate('Export XLS') }}</a>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="payment_bulk_export('CSV')">{{
                            translate('Export CSV') }}</a>
                        @endcan
                    </div>
                </div>
            </div>
            @endcan
            <!-- Filters -->
            <div class="row gutters-5 mt-3">
                <!-- Seller Filter -->
                <div class="col-md-3 mb-2">
                    <select class="form-control aiz-selectpicker" name="seller_id" data-live-search="true">
                        <option value="">{{translate('Filter By Seller')}}</option>
                        @foreach (\App\Models\User::where('user_type', 'seller')->get() as $seller)
                        <option value="{{ $seller->id }}" @if(isset($seller_id) && $seller_id==$seller->id) selected
                            @endif>{{
                            $seller->shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Date Range Filter -->
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control aiz-date-range" value="{{ $date }}" name="date"
                        placeholder="{{ translate('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to "
                        data-advanced-range="true" autocomplete="off">
                </div>

                <!-- Search Filter -->
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control" id="txn_search" name="txn_search" @isset($txn_search)
                        value="{{ $txn_search }}" @endisset placeholder="{{ translate('Type Txn code & hit Enter') }}">
                </div>

                <!-- Filter Button -->
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary btn-block">{{ translate('Filter') }}</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        @if (auth()->user()->can('export_payments'))
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
                        @else
                        <th data-breakpoints="lg">#</th>
                        @endif
                        <th data-breakpoints="lg">{{translate('Date')}}</th>
                        <th>{{translate('Seller')}}</th>
                        <th>{{translate('Amount')}}</th>
                        <th>{{translate('payment_method')}}</th>
                        <th>{{translate('Txn code')}}</th>
                        <th data-breakpoints="lg">{{ translate('Payment Details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $key => $payment)
                    @php $user = \App\Models\User::find($payment->seller_id); @endphp
                    @if ($user && $user->shop)
                    <tr>
                        @if (auth()->user()->can('export_payment'))
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{ $payment->id }}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        @else
                        <td>{{ $key + 1 + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                        @endif
                        <td>{{ $payment->created_at }}</td>
                        <td>
                            {{ $user->name }} ({{ $user->shop->name }})
                        </td>
                        <td>
                            {{ single_price($payment->amount) }}
                        </td>
                        <td>{{ translate(ucfirst(str_replace('_', ' ', $payment->payment_method))) }}</td>
                        <td>{{$payment->txn_code}}</td>
                        <td>{{$payment->payment_details}}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>

            <x-table_pagination :data="$payments" :paginate="$paginate" />
        </div>
    </form>
</div>
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
            $('#payments_orders').submit();
        })
        function payment_bulk_export (type){
            var url = '{{route('payment-bulk-export')}}';
            $("#sort_payments").attr("action", url);
            $('input[name="export_type"]').val(type);
            $('#sort_payments').submit();
            $("#sort_payments").attr("action", '');
        }
</script>
@endsection
@endsection
