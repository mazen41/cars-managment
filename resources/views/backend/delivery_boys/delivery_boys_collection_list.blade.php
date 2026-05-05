@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h3 class="h3">{{translate('All Collection List')}}</h3>
        </div>
    </div>
</div>


<div class="card">
    <form class="" id="sort_delivery_boys" action="" method="GET">
        <div class="card-header d-block d-lg-flex">
            <div class="">

                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 250px;">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-toggle="dropdown">
                                {{ translate('Bulk Action') }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                @can('export_delivery_boy')
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
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        @if (auth()->user()->can('export_delivery_boy'))
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
                        <th>{{translate('Delivery Boy')}}</th>
                        <th class="text-center">{{translate('Collected Amount')}}</th>
                        <th class="text-right">{{translate('Created At')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($delivery_boy_collections as $key => $delivery_boy_collection)

                    <tr>
                        @if (auth()->user()->can('export_delivery_boy'))
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]"
                                            value="{{ $delivery_boy_collection->id }}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        @else
                        <td>{{ ($key+1) + ($delivery_boy_collections->currentPage() -
                            1)*$delivery_boy_collections->perPage() }}</td>
                        @endif
                        <td>
                           @if($delivery_boy_collection->user != null)
                           {{ $delivery_boy_collection->user->name }}
                          @else
                          {{translate('Deleted')}}
                          @endif
                        </td>
                        <td class="text-center">
                            {{ $delivery_boy_collection->collection_amount }}
                        </td>
                        <td class="text-right">
                            {{$delivery_boy_collection->created_at}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <x-table_pagination :data="$delivery_boy_collections" :paginate="$paginate" />
        </div>
    </form>
</div>

@endsection
@section('script')
<script>
    (function($) {
			"use strict";
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
            $('#sort_delivery_boys').submit();
        })
        })(jQuery);

        function bulk_export (type){
            var url = '{{route('delivery-boys-bulk-export-collection-histories')}}';
            $("#sort_delivery_boys").attr("action", url);
            $('input[name="export_type"]').val(type);
            $('#sort_delivery_boys').submit();
            $("#sort_delivery_boys").attr("action", '');
        }
</script>
@endsection
