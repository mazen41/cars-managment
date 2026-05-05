@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Add New Requested Product')}}</h5>
</div>

<div class="col-lg-8 mx-auto">
    <div class="card">
        <div class="card-body p-0">
            <form class="p-4" action="{{ route('requested-products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="name">{{translate('Product Name')}} <i class="las la-language" title="{{translate('Translatable')}}"></i></label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Product Name')}}" id="name" name="name" class="form-control" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="description">{{translate('Description')}}</label>
                    <div class="col-sm-9">
                        <textarea class="aiz-text-editor" name="description" placeholder="{{translate('Description')}}"></textarea>
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
                            <input type="hidden" name="photos" class="selected-files">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="link">{{translate('Product Link')}}</label>
                    <div class="col-sm-9">
                        <input type="url" placeholder="{{translate('Product Link (Optional)')}}" id="link" name="link" class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="category_id">{{translate('Category')}}</label>
                    <div class="col-sm-9">
                        <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-live-search="true">
                            <option value="">{{translate('Select Category')}}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                @foreach ($category->childrenCategories as $childCategory)
                                    @include('categories.child_category', ['child_category' => $childCategory])
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
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="status">{{translate('Status')}}</label>
                    <div class="col-sm-9">
                        <select class="form-control aiz-selectpicker" name="status" id="status" required>
                            <option value="pending">{{translate('Pending')}}</option>
                            <option value="rejected">{{translate('Rejected')}}</option>
                            <option value="published">{{translate('Published')}}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
