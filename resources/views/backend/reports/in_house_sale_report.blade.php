@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class=" align-items-center">
       <h1 class="h3">{{translate('Inhouse Product sale report')}}</h1>
	</div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <form id="report-form" action="{{ route('in_house_sale_report.index') }}" method="GET">
                     <input type="hidden" name="export_type" value="">
                    <input type="hidden" name="type" value="in_house">
                    <div class="form-group row">
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
                        <label class="col-md-3 col-form-label">{{translate('Sort by Category')}} :</label>
                        <div class="col-md-5">
                            <select id="demo-ease" class="aiz-selectpicker" name="category_id" >
                                <option value="">{{ translate('All') }}</option>
                                @foreach (\App\Models\Category::all() as $key => $category)
                                    <option value="{{ $category->id }}" @if($category->id == $sort_by) selected @endif >{{ $category->getTranslation('name') }}</option>
                                @endforeach
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
                            <th>#</th>
                            <th>{{ translate('Product Name') }}</th>
                            <th>{{ translate('Num of Sale') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $key => $product)
                            <tr>
                                <td>{{ ($key+1) + ($products->currentPage() - 1)*$products->perPage() }}</td>
                                <td>{{ $product->getTranslation('name') }}</td>
                                <td>{{ $product->num_of_sale }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    {{ $products->appends(request()->input())->links() }}
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
