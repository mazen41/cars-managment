<div class="aiz-pagination">
    {{ $data->appends(request()->input())->links() }}
</div>
<div class="row justify-content-between align-items-center">
    <div class="col-8">
        <p class="mb-0 mr-3 text-muted">
            {{ translate('Showing') }} {{ $data->firstItem() ?? 0 }} {{ translate('to') }} {{ $data->lastItem() ?? 0 }} {{ translate('of') }} {{ $data->total() }} {{ translate('results') }}
        </p>
    </div>
    <div class="col-auto">
        <div class="form-group">
            <select id="paginate-select" class="form-control" name="paginate">
                <option @if($paginate && $paginate=='15' ) selected @endif value="15">
                    15</option>
                <option @if($paginate && $paginate=='30' ) selected @endif value="30">
                    30</option>
                <option @if($paginate && $paginate=='50' ) selected @endif value="50">
                    50</option>
                <option @if($paginate && $paginate=='100' ) selected @endif value="100">
                    100</option>
                <option @if($paginate && $paginate=='250' ) selected @endif value="250">
                    250</option>
                <option @if($paginate && $paginate=='500' ) selected @endif value="500">
                    500</option>
                <option @if($paginate && $paginate=='1000' ) selected @endif value="1000">
                    1000</option>
            </select>
        </div>
    </div>
</div>
