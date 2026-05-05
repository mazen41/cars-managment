@extends('backend.layouts.app')
@section('style')
<style>
    .card-header {
        padding: 1.5rem;
    }
    .form-control {
        height: calc(1.5em + 1rem + 2px);
    }
    .btn-block {
        height: calc(1.5em + 1rem + 2px);
    }
    .aiz-selectpicker {
        height: calc(1.5em + 1rem + 2px) !important;
    }
</style>
@endsection

@section('content')
<div class="card">
    <form action="" id="sort_orders" method="GET">
        <div class="card-header">
            <div class="col-md-3 mb-2 mb-md-0">
                <h5 class="mb-0 h6">{{ translate('All Orders') }}</h5>
            </div>
        </div>
        <div class="card-header">


            <!-- Filters -->
            <div class="row gutters-5 mt-3">
                @canany(['delete_order', 'export_order'])
            <div class="col-md-2 mb-2 mb-md-0">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        {{ translate('Bulk Action') }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        @can('delete_order')
                            <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-delete-modal">{{ translate('Delete selection') }}</a>
                        @endcan
                        @can('export_order')
                            <a class="dropdown-item" href="javascript:void(0)" onclick="order_bulk_export('PDF')">{{ translate('Export PDF') }}</a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="order_bulk_export('XLS')">{{ translate('Export XLS') }}</a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="order_bulk_export('CSV')">{{ translate('Export CSV') }}</a>
                        @endcan
                    </div>
                </div>
            </div>
            @endcan
                <!-- Customer Filter -->
                <div class="col-md-4 mb-2">
                    <select class="form-control selectpicker" id="customer-select" data-live-search="true"
                        data-ajax-url="{{ route('users.ajax.search') }}" data-size="10" name="user_id"
                         data-show-phone="true" data-show-email="true"
                        >
                        <option value="">{{translate('Filter By Customer')}}</option>
                        @if(isset($user_id) && $selected_user)
                        <option value="{{ $selected_user->id }}" selected>
                            {{ $selected_user->name}}
                            @if ($selected_user->email)
                            ({{ $selected_user->email }})
                            @else
                            ({{$selected_user->phone}})
                            @endif
                        </option>
                        @endif
                    </select>
                </div>

                <!-- Seller Filter -->
                @if(request()->is('admin/seller_orders'))
                <div class="col-md-3 mb-2">
                    <select class="form-control aiz-selectpicker" name="seller_id" data-live-search="true">
                        <option value="">{{translate('Filter By Seller')}}</option>
                        @foreach (\App\Models\User::where('user_type', 'seller')->get() as $seller)
                            <option value="{{ $seller->id }}" @if(isset($seller_id) && $seller_id == $seller->id) selected @endif>{{ $seller->shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Delivery Status Filter -->
                <div class="col-md-3 mb-2">
                    <select class="form-control aiz-selectpicker" name="delivery_status">
                        <option value="">{{ translate('Filter by Delivery Status') }}</option>
                        <option value="pending" @if ($delivery_status == 'pending') selected @endif>{{ translate('Pending') }}</option>
                        <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>{{ translate('Confirmed') }}</option>
                        <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>{{ translate('Picked Up') }}</option>
                        <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>{{ translate('On The Way') }}</option>
                        <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>{{ translate('Delivered') }}</option>
                        <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>{{ translate('Cancel') }}</option>
                    </select>
                </div>

                <!-- Payment Status Filter -->
                <div class="col-md-3 mb-2">
                    <select class="form-control aiz-selectpicker" name="payment_status">
                        <option value="">{{ translate('Filter by Payment Status') }}</option>
                        <option value="paid" @isset($payment_status) @if($payment_status == 'paid') selected @endif @endisset>{{ translate('Paid') }}</option>
                        <option value="pending" @isset($payment_status) @if ($payment_status=='pending' ) selected @endif @endisset>{{ translate('Pending') }}</option>
                        <option value="unpaid" @isset($payment_status) @if($payment_status == 'unpaid') selected @endif @endisset>{{ translate('Unpaid') }}</option>
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="col-md-3 mb-2">
                    <select class="form-control aiz-selectpicker" name="payment_type">
                        <option value="">{{ translate('Filter by Payment Method') }}</option>
                        <option value="jaib" @isset($payment_type) @if($payment_type == 'jaib') selected @endif @endisset>{{ translate('Jaib') }}</option>
                        <option value="jawali" @isset($payment_type) @if($payment_type == 'jawali') selected @endif @endisset>{{ translate('Jawali') }}</option>
                        <option value="floosak" @isset($payment_type) @if($payment_type == 'floosak') selected @endif @endisset>{{ translate('Floosak') }}</option>
                        <option value="wallet" @isset($payment_type) @if($payment_type == 'wallet') selected @endif @endisset>{{ translate('Wallet') }}</option>
                        <option value="manual_payment" @isset($payment_type) @if($payment_type == 'manual_payment') selected @endif @endisset>{{ translate('Manual payment') }}</option>
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control aiz-date-range" value="{{ $date }}" name="date" placeholder="{{ translate('Filter by date') }}" data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
                </div>

                <!-- Search Filter -->
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code & hit Enter') }}">
                </div>

                <!-- Filter Button -->
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary btn-block">{{ translate('Filter') }}</button>
                </div>
            </div>
        </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                @if (auth()->user()->can('delete_order') || auth()->user()->can('export_order'))
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

                                <th>{{ translate('Order Code') }}</th>
                                <th >{{ translate('Num. of Products') }}</th>
                                <th >{{ translate('Customer') }}</th>
                                <th >{{ translate('Seller') }}</th>
                                @if(request()->is('admin/seller_orders'))
                                <th >{{ translate('Commission')}}</th>
                                @endif
                                <th >{{ translate('Shipping Cost')}}</th>
                                <th >{{ translate('Subtotal') }}</th>
                                <th >{{ translate('Grand Total') }}</th>
                                <th >{{ translate('Delivery Status') }}</th>
                                <th >{{ translate('Payment method') }}</th>
                                <th >{{ translate('Payment Status') }}</th>
                                <th >{{ translate('Date') }}</th>
                                @if (addon_is_activated('refund_request'))
                                    <th>{{ translate('Refund') }}</th>
                                @endif
                                <th class="text-right" width="15%">{{ translate('options') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $key => $order)
                                <tr>
                                    @if (auth()->user()->can('delete_order') || auth()->user()->can('export_order'))
                                        <td>
                                            <div class="form-group">
                                                <div class="aiz-checkbox-inline">
                                                    <label class="aiz-checkbox">
                                                        <input type="checkbox" class="check-one" name="id[]"
                                                            value="{{ $order->id }}">
                                                        <span class="aiz-square-check"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                    @else
                                        <td>{{ $key + 1 + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                                    @endif
                                    <td>
                                        {{ $order->code }}
                                        @if ($order->viewed == 0)
                                            <span class="badge badge-inline badge-info">{{ translate('New') }}</span>
                                        @endif
                                        @if (addon_is_activated('pos_system') && $order->order_from == 'pos')
                                            <span class="badge badge-inline badge-danger">{{ translate('POS') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ count($order->orderDetails) }}
                                    </td>
                                    <td>
                                        @if ($order->user != null)
                                            {{ $order->user->name }}
                                        @else
                                            Guest ({{ $order->guest_id }})
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order->shop)
                                            {{ $order->shop->name }}
                                        @else
                                            {{ translate('Inhouse Order') }}
                                        @endif
                                    </td>
                                    @if(request()->is('admin/seller_orders'))
                                    <td>
                                        @if ($order->commission_calculated && $order->commission)
                                        {{ single_price($order->commission->admin_commission) }}
                                        @else
                                        {{single_price(0.00)}}
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        {{ single_price($order->orderDetails->sum('shipping_cost')) }}
                                    </td>
                                    <td>
                                        {{ format_price($order->orderDetails->sum('price')) }}
                                    </td>
                                    <td>
                                        {{ single_price($order->grand_total) }}
                                    </td>
                                    <td>
                                        {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                    </td>
                                    <td>
                                        {{ $order->payment_type ? translate(ucfirst(str_replace('_', ' ', $order->payment_type))) : translate('N/A')}}</td>
                                    </td>
                                    <td>
                                        @if ($order->payment_status == 'paid')
                                        <span class="badge badge-inline badge-success">{{ translate('Paid') }}</span>
                                        @elseif($order->payment_status == 'unpaid')
                                        <span class="badge badge-inline badge-danger">{{ translate('Unpaid') }}</span>
                                        @elseif($order->payment_status == 'pending')
                                        <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td >
                                        {{\Carbon\Carbon::parse($order->created_at)->format('d/m/Y')}}
                                    </td>
                                    @if (addon_is_activated('refund_request'))
                                        <td>
                                            @if (count($order->refund_requests) > 0)
                                                {{ count($order->refund_requests) }} {{ translate('Refund') }}
                                            @else
                                                {{ translate('No Refund') }}
                                            @endif
                                        </td>
                                    @endif
                                    <td class="text-right">
                                        @if (addon_is_activated('pos_system') && $order->order_from == 'pos')
                                            <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                                href="{{ route('admin.invoice.thermal_printer', $order->id) }}" target="_blank"
                                                title="{{ translate('Thermal Printer') }}">
                                                <i class="las la-print"></i>
                                            </a>
                                        @endif
                                        @can('view_order_details')
                                            @php
                                                $order_detail_route = route('orders.show', encrypt($order->id));
                                                if (Route::currentRouteName() == 'seller_orders.index') {
                                                    $order_detail_route = route('seller_orders.show', encrypt($order->id));
                                                } elseif (Route::currentRouteName() == 'pick_up_point.index') {
                                                    $order_detail_route = route('pick_up_point.order_show', encrypt($order->id));
                                                }
                                                if (Route::currentRouteName() == 'inhouse_orders.index') {
                                                    $order_detail_route = route('inhouse_orders.show', encrypt($order->id));
                                                }
                                            @endphp
                                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                                href="{{ $order_detail_route }}" title="{{ translate('View') }}">
                                                <i class="las la-eye"></i>
                                            </a>
                                        @endcan
                                        <a class="btn btn-soft-info btn-icon btn-circle btn-sm"
                                            href="{{ route('invoice.download', $order->id) }}"
                                            title="{{ translate('Download Invoice') }}">
                                            <i class="las la-download"></i>
                                        </a>
                                        @can('delete_order')
                                            <a href="#"
                                                class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                                data-href="{{ route('orders.destroy', $order->id) }}"
                                                title="{{ translate('Delete') }}">
                                                <i class="las la-trash"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-table_pagination :data="$orders" :paginate="$paginate" />

            </div>
        </form>
    </div>
@endsection

@section('modal')
    <!-- Delete modal -->
    @include('modals.delete_modal')
    <!-- Bulk Delete modal -->
    @include('modals.bulk_delete_modal')
@endsection

@section('script')
@include('backend.js.user_ajax_search')
    <script type="text/javascript">
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
            $('#sort_orders').submit();
        })

        function bulk_delete() {
            var data = new FormData($('#sort_orders')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('bulk-order-delete') }}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response == 1) {
                        location.reload();
                    }
                }
            });
        }

        function order_bulk_export (type){
            var url = '{{route('order-bulk-export')}}';
            $("#sort_orders").attr("action", url);
            $('input[name="export_type"]').val(type);
            $('#sort_orders').submit();
            $("#sort_orders").attr("action", '');
        }
    </script>
@endsection
