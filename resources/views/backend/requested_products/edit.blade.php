@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Edit Requested Product')}}</h5>
</div>

<div class="col-lg-8 mx-auto">
    <div class="card">
        <div class="card-body p-0">
            <form class="p-4" action="{{ route('requested-products.update', $requestedProduct->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="name">{{translate('Product Name')}} <i class="las la-language" title="{{translate('Translatable')}}"></i></label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Product Name')}}" id="name" name="name" class="form-control" value="{{ old('name', $requestedProduct->name) }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="description">{{translate('Description')}}</label>
                    <div class="col-sm-9">
                        <textarea class="aiz-text-editor" name="description" placeholder="{{translate('Description')}}">{{ old('description', $requestedProduct->description) }}</textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="photos">{{translate('Product Photos')}}</label>
                    <div class="col-sm-9">
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose Files') }}</div>
                            <input type="hidden" name="photos" class="selected-files" value="{{ old('photos', $requestedProduct->photos) }}">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="link">{{translate('Product Link')}}</label>
                    <div class="col-sm-9">
                        <input type="url" placeholder="{{translate('Product Link (Optional)')}}" id="link" name="link" class="form-control" value="{{ old('link', $requestedProduct->link) }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="category_id">{{translate('Category')}}</label>
                    <div class="col-sm-9">
                        <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-live-search="true">
                            <option value="">{{translate('Select Category')}}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @if($requestedProduct->category_id == $category->id) selected @endif>
                                    {{ $category->getTranslation('name') }}
                                </option>
                                @foreach ($category->childrenCategories as $childCategory)
                                    @include('categories.child_category', ['child_category' => $childCategory, 'selected_id' => $requestedProduct->category_id])
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="requested_by">{{translate('Requested By')}}</label>
                    <div class="col-sm-9">
                        <select class="form-control aiz-selectpicker" name="requested_by" id="requested_by" data-live-search="true" required>
                            <option value="">{{translate('Select User')}}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @if($requestedProduct->requested_by == $user->id) selected @endif>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="status">{{translate('Status')}}</label>
                    <div class="col-sm-9">
                        <select class="form-control aiz-selectpicker" name="status" id="status" required>
                            <option value="pending" @if($requestedProduct->status == 'pending') selected @endif>{{translate('Pending')}}</option>
                            <option value="rejected" @if($requestedProduct->status == 'rejected') selected @endif>{{translate('Rejected')}}</option>
                            <option value="published" @if($requestedProduct->status == 'published') selected @endif>{{translate('Published')}}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">{{translate('Request Count')}}</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" value="{{ $requestedProduct->request_count }}" readonly>
                        <small class="text-muted">{{translate('This field is automatically managed')}}</small>
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
