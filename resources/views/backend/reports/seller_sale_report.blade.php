@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class=" align-items-center">
       <h1 class="h3">{{translate('Seller Based Selling Report')}}</h1>
	</div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <form id="report-form" action="{{ route('seller_sale_report.index') }}" method="GET">
                    <input type="hidden" name="export_type" value="">
                    <input type="hidden" name="type" value="seller">
                    <div class="form-group row offset-lg-2">
                         <div class="dropdown mb-2 mb-md-0">
                            <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                {{translate('Export')}}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('XLS')">{{
                                    translate('Export XLS') }}</a>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('CSV')">{{
                                    translate('Export CSV') }}</a>
                            </div>
                        </div>
                        <label class="col-md-3 col-form-label">{{translate('Sort by verificarion status')}} :</label>
                        <div class="col-md-5">
                            <select class="from-control aiz-selectpicker" name="verification_status" required>
                                <option value="">{{ translate('All') }}</option>
                               <option value="1" @if($sort_by == '1') selected @endif>{{ translate('Approved') }}</option>
                               <option value="0" @if($sort_by == '0') selected @endif>{{ translate('Non Approved') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary" type="submit">{{ translate('Filter') }}</button>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Seller Name') }}</th>
                            <th data-breakpoints="lg">{{ translate('Shop Name') }}</th>
                            <th data-breakpoints="lg">{{ translate('Number of Product Sale') }}</th>
                            <th>{{ translate('Order Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sellers as $key => $seller)
                            @if($seller != null && isset($seller->user))
                                <tr>
                                    <td>{{ $seller->user->name }}</td>
                                    <td>{{ $seller->user->shop != null ? $seller->user->shop->name : '--' }}</td>
                                    <td>
                                        @php
                                            $num_of_sale = 0;
                                            foreach ($seller->user->products as $key => $product) {
                                                $num_of_sale += $product->num_of_sale;
                                            }
                                        @endphp
                                        {{ $num_of_sale }}
                                    </td>
                                    <td>
                                        {{ single_price(\App\Models\OrderDetail::where('seller_id', $seller->user->id)->where('delivery_status', '!=', 'cancelled')->sum('price')) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    {{ $sellers->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
        function bulk_export(type){
            var url = '{{route('export.sales.report')}}';
            $("#report-form").attr("action", url);
            $('input[name="export_type"]').val(type);
            $('#report-form').submit();
            $("#report-form").attr("action", '');
        }
</script>
@endsection
