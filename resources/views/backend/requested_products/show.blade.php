@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Requested Product Details')}}</h1>
        </div>
        <div class="col text-right">
            @can('edit_requested_products')
                <a href="{{ route('requested-products.edit', $requestedProduct->id) }}" class="btn btn-circle btn-info">
                    <span>{{translate('Edit')}}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label font-weight-bold">{{translate('Name')}}:</label>
                    <div class="col-sm-9">
                        <span>{{ $requestedProduct->name }}</span>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label font-weight-bold">{{translate('Description')}}:</label>
                    <div class="col-sm-9">
                        <div class="aiz-text-editor-content">
                            {!! $requestedProduct->description !!}
                        </div>
                    </div>
                </div>

                @if($requestedProduct->link)
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label font-weight-bold">{{translate('Product Link')}}:</label>
                        <div class="col-sm-9">
                            <a href="{{ $requestedProduct->link }}" target="_blank" class="btn btn-soft-primary btn-sm">
                                {{translate('View Product')}} <i class="las la-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                @endif

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label font-weight-bold">{{translate('Category')}}:</label>
                    <div class="col-sm-9">
                        @if($requestedProduct->category)
                            <span class="badge badge-inline badge-info">{{ $requestedProduct->category->getTranslation('name') }}</span>
                        @else
                            <span class="text-muted">{{ translate('No Category') }}</span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label font-weight-bold">{{translate('Requested By')}}:</label>
                    <div class="col-sm-9">
                        @if($requestedProduct->user)
                            <div class="d-flex align-items-center">
                                @if($requestedProduct->user->avatar_original)
                                    <img src="{{ uploaded_asset($requestedProduct->user->avatar_original) }}" alt="User" class="size-30px rounded-circle mr-2">
                                @else
                                    <div class="size-30px rounded-circle bg-soft-primary d-flex align-items-center justify-content-center mr-2">
                                        <i class="las la-user"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-weight-medium">{{ $requestedProduct->user->name }}</div>
                                    <div class="text-muted small">{{ $requestedProduct->user->email }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted">{{ translate('Unknown User') }}</span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label font-weight-bold">{{translate('Request Count')}}:</label>
                    <div class="col-sm-9">
                        <span class="badge badge-inline badge-success">{{ $requestedProduct->request_count }}</span>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label font-weight-bold">{{translate('Status')}}:</label>
                    <div class="col-sm-9">
                        @if($requestedProduct->status == 'pending')
                            <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                        @elseif($requestedProduct->status == 'approved')
                            <span class="badge badge-inline badge-success">{{ translate('Approved') }}</span>
                        @elseif($requestedProduct->status == 'rejected')
                            <span class="badge badge-inline badge-danger">{{ translate('Rejected') }}</span>
                        @elseif($requestedProduct->status == 'published')
                            <span class="badge badge-inline badge-info">{{ translate('Published') }}</span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label font-weight-bold">{{translate('Created At')}}:</label>
                    <div class="col-sm-9">
                        <span>{{ date('d M Y, h:i A', strtotime($requestedProduct->created_at)) }}</span>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label font-weight-bold">{{translate('Updated At')}}:</label>
                    <div class="col-sm-9">
                        <span>{{ date('d M Y, h:i A', strtotime($requestedProduct->updated_at)) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Product Photos')}}</h5>
            </div>
            <div class="card-body">
                @if($requestedProduct->photos && count($requestedProduct->photos_array) > 0)
                    <div class="row gutters-5">
                        @foreach($requestedProduct->photos_array as $photo)
                            <div class="col-6 mb-3">
                                <div class="img-zoom">
                                    <img src="{{ uploaded_asset($photo) }}" alt="Product Photo" class="img-fluid lazyload">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <img src="{{ static_asset('assets/img/placeholder.jpg') }}" alt="No Photos" class="img-fluid" style="max-width: 200px;">
                        <p class="text-muted mt-2">{{ translate('No photos uploaded') }}</p>
                    </div>
                @endif
            </div>
        </div>

        @can('edit_requested_products')
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Quick Actions')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('requested-products.update-status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $requestedProduct->id }}">
                        <div class="form-group">
                            <label>{{translate('Update Status')}}</label>
                            <select class="form-control" name="status" onchange="this.form.submit()">
                                <option value="pending" @if($requestedProduct->status == 'pending') selected @endif>{{ translate('Pending') }}</option>
                                <option value="approved" @if($requestedProduct->status == 'approved') selected @endif>{{ translate('Approved') }}</option>
                                <option value="rejected" @if($requestedProduct->status == 'rejected') selected @endif>{{ translate('Rejected') }}</option>
                                <option value="published" @if($requestedProduct->status == 'published') selected @endif>{{ translate('Published') }}</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function(){
        AIZ.plugins.zoom();
    });
</script>
@endsection
