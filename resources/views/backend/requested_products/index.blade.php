@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Requested Products')}}</h1>
        </div>
        @can('create_requested_products')
            <div class="col text-right">
                <a href="{{ route('requested-products.create') }}" class="btn btn-circle btn-info">
                    <span>{{translate('Add New Request')}}</span>
                </a>
            </div>
        @endcan
    </div>
</div>

<div class="card">
    <form class="" id="sort_requested_products" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Requested Products') }}</h5>
            </div>

            @can('delete_requested_products')
                <div class="dropdown mb-2 mb-md-0">
                    <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        {{translate('Bulk Action')}}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-delete-modal">{{translate('Delete selection')}}</a>
                    </div>
                </div>
            @endcan

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="{{ translate('Type name & Enter') }}">
                </div>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status" name="status" onchange="sort_requested_products()">
                    <option value="">{{ translate('All Status') }}</option>
                    <option value="pending" @if(request('status') == 'pending') selected @endif>{{ translate('Pending') }}</option>

                    <option value="rejected" @if(request('status') == 'rejected') selected @endif>{{ translate('Rejected') }}</option>
                    <option value="published" @if(request('status') == 'published') selected @endif>{{ translate('Published') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="category_id" name="category_id" onchange="sort_requested_products()">
                    <option value="">{{ translate('All Categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @if(request('category_id') == $category->id) selected @endif>
                            {{ $category->getTranslation('name') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    @can('delete_requested_products')
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
                    @endcan
                    <th>{{translate('Name')}}</th>
                    <th data-breakpoints="md">{{translate('Photos')}}</th>
                    <th data-breakpoints="md">{{translate('Category')}}</th>
                    <th data-breakpoints="md">{{translate('Requested By')}}</th>
                    <th data-breakpoints="md">{{translate('Request Count')}}</th>
                    <th data-breakpoints="md">{{translate('Status')}}</th>
                    <th data-breakpoints="md">{{translate('Date')}}</th>
                    <th width="10%" class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requestedProducts as $key => $requestedProduct)
                    <tr>
                        @can('delete_requested_products')
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]" value="{{$requestedProduct->id}}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                        @endcan
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col">
                                    <span class="text-muted text-truncate-2">{{ $requestedProduct->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5">
                                @if($requestedProduct->main_photo)
                                   <img src="{{ uploaded_asset($requestedProduct->main_photo) }}" alt="Product" class="size-50px img-fit">
                                @else
                                    <div class="col-auto">
                                        <img src="{{ static_asset('assets/img/placeholder.jpg') }}" alt="Placeholder" class="size-50px img-fit">
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($requestedProduct->category)
                                <span class="text-muted">{{ $requestedProduct->category->getTranslation('name') }}</span>
                            @else
                                <span class="text-muted">{{ translate('No Category') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($requestedProduct->user)
                                <span class="text-muted">{{ $requestedProduct->user->name }}</span>
                            @else
                                <span class="text-muted">{{ translate('Unknown User') }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-inline badge-info">{{ $requestedProduct->request_count }}</span>
                        </td>
                        <td>
                            @can('edit_requested_products')
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <select class="form-control form-control-sm" onchange="update_status(this)" data-id="{{ $requestedProduct->id }}">
                                        <option value="pending" @if($requestedProduct->status == 'pending') selected @endif>{{ translate('Pending') }}</option>
                                        <option value="rejected" @if($requestedProduct->status == 'rejected') selected @endif>{{ translate('Rejected') }}</option>
                                        <option value="published" @if($requestedProduct->status == 'published') selected @endif>{{ translate('Published') }}</option>
                                    </select>
                                </label>
                            @else
                                @if($requestedProduct->status == 'pending')
                                    <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                                @elseif($requestedProduct->status == 'rejected')
                                    <span class="badge badge-inline badge-danger">{{ translate('Rejected') }}</span>
                                @elseif($requestedProduct->status == 'published')
                                    <span class="badge badge-inline badge-info">{{ translate('Published') }}</span>
                                @endif
                            @endcan
                        </td>
                        <td>{{ date('d-m-Y', strtotime($requestedProduct->created_at)) }}</td>
                        <td class="text-right">
                            @can('view_requested_products')
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('requested-products.show', $requestedProduct->id)}}" title="{{ translate('View') }}">
                                    <i class="las la-eye"></i>
                                </a>
                            @endcan
                            @can('edit_requested_products')
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('requested-products.edit', $requestedProduct->id)}}" title="{{ translate('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                            @endcan
                            @can('delete_requested_products')
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('requested-products.destroy', $requestedProduct->id)}}" title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $requestedProducts->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@can('delete_requested_products')
    @include('modals.delete_modal')
    @include('modals.bulk_delete_modal')
@endcan

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        function sort_requested_products(el){
            $('#sort_requested_products').submit();
        }

        function update_status(el){
            if(el.value != ''){
                $.post('{{ route('requested-products.update-status') }}', {
                    _token:'{{ csrf_token() }}',
                    id:$(el).data('id'),
                    status:el.value
                }, function(data){
                    if(data.success == 1){
                        AIZ.plugins.notify('success', '{{ translate('Status updated successfully') }}');
                    }
                    else{
                        AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                    }
                });
            }
        }

        $(document).ready(function(){
            //$('#container').removeClass('mainnav-lg').addClass('mainnav-sm');
        });

        function bulk_delete() {
            var data = new FormData($('#sort_requested_products')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('requested-products.bulk-delete')}}",
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
