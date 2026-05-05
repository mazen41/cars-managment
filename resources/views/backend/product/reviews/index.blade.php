@extends('backend.layouts.app')

@section('content')


<div class="card">
    <div class="card-header">
        <div class="row flex-grow-1">
            <div class="col">
                <h5 class="mb-0 h6">{{translate('Reviews')}}</h5>

            </div>
            <div class="col-md-6 col-xl-4 ml-auto mr-0">
                <form class="" id="sort_by_rating" action="{{ route('reviews.index') }}" method="GET">
                    <div class="" style="min-width: 200px;">
                        <select class="form-control aiz-selectpicker" name="rating" id="rating" onchange="filter_by_rating()">
                            <option value="">{{translate('Filter by Rating')}}</option>
                            <option value="rating,desc" {{ request('rating') == 'rating,desc' ? 'selected' : '' }}>{{translate('Rating (High > Low)')}}</option>
                            <option value="rating,asc" {{ request('rating') == 'rating,asc' ? 'selected' : '' }}>{{translate('Rating (Low > High)')}}</option>
                        </select>
                    </div>
                    <div class="" style="min-width: 200px;">
                        <select class="form-control aiz-selectpicker" name="type" id="type" onchange="$(this).closest('form').submit()">
                            <option value="">{{translate('Filter by Type')}}</option>
                            <option value="App\Models\Product" {{ request('type') == 'App\Models\Product' ? 'selected' : '' }}>{{translate('Product')}}</option>
                            <option value="App\Models\Shop" {{ request('type') == 'App\Models\Shop' ? 'selected' : '' }}>{{translate('Shop')}}</option>
                            <option value="App\Models\DeliveryBoy" {{ request('type') == 'App\Models\DeliveryBoy' ? 'selected' : '' }}>{{translate('Delivery Boy')}}</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{translate('Type')}}</th>
                    <th data-breakpoints="lg">{{translate('Customer')}}</th>
                    <th>{{translate('Rating')}}</th>
                    <th data-breakpoints="lg">{{translate('Comment')}}</th>
                    <th data-breakpoints="lg">{{translate('Published')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $key => $review)
                    @if ($review->reviewable != null && $review->user != null)
                        <tr>
                            <td>{{ ($key+1) + ($reviews->currentPage() - 1)*$reviews->perPage() }}</td>
                            <td>
                                @switch($review->reviewable_type)
                                    @case('App\Models\Product')
                                        {{ translate('Product') }} - <a href="{{ route('product', $review->reviewable->slug) }}" target="_blank" class="text-reset text-truncate-2">{{ $review->reviewable->getTranslation('name') }}</a>
                                        @break
                                    @case('App\Models\Shop')
                                        {{ translate('Shop') }} - {{ $review->reviewable->name }}
                                    @break
                                    @case('App\Models\DeliveryBoy')
                                        {{ translate('Delivery Boy') }} - {{ $review->reviewable->user->name }}
                                    @break
                                    @default
                                        {{ translate('Unknown') }}
                                @endswitch
                            </td>
                            </td>

                            <td>{{ $review->user->name }} ({{ $review->user->email?? $review->user->phone }})</td>
                            <td>
                                 <span class="rating rating-sm">
                                        @for ($i=0; $i < $review->rating; $i++)
                                            <i class="las la-star active"></i>
                                        @endfor
                                        @for ($i=0; $i < 5-$review->rating; $i++)
                                            <i class="las la-star"></i>
                                        @endfor
                                    </span>
                            </td>
                            <td>{{ $review->comment }}</td>
                            <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input
                                        @can('publish_product_review') onchange="update_published(this)" @endcan
                                        value="{{ $review->id }}" type="checkbox"
                                        @if($review->status == 1) checked @endif
                                        @cannot('publish_product_review') disabled @endcan
                                    >
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $reviews->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function update_published(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('reviews.published') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Published reviews updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }
        function filter_by_rating(el){
            var rating = $('#rating').val();
            if (rating != '') {
                $('#sort_by_rating').submit();
            }
        }
    </script>
@endsection
