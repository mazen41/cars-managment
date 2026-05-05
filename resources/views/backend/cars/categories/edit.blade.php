@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Edit Car Category')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-categories.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to Categories')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Category Information')}}</h5>
    </div>
    <div class="card-body">

        <form action="{{ route('admin.car-categories.update', $carCategory->id) }}" method="POST" enctype="multipart/form-data">
            <ul class="nav nav-tabs nav-fill language-bar">
                @foreach (get_all_active_language() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == request()->input('lang')) active @endif py-3" href="{{ route('admin.car-categories.edit', ['carCategory'=>$carCategory->id, 'lang'=> $language->code] ) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                            <span>{{ $language->name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            @csrf
            @method('PUT')
            <input type="hidden" name="lang" value="{{request()->input('lang')}}"/>
            <!-- Category Name -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Category Name')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" placeholder="{{translate('Category Name')}}" id="name" name="name" class="form-control" required value="{{ old('name', $carCategory->getTranslation('name', request()->input('lang'))) }}">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Description')}}</label>
                <div class="col-md-9">
                    <textarea placeholder="{{translate('Category Description')}}" name="description" class="form-control" rows="4">{{ old('description', $carCategory->description) }}</textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- <!-- Parent Category -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Parent Category')}}</label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="parent_id" data-live-search="true">
                        <option value="0">{{translate('No Parent')}}</option>
                        @foreach($categories as $category)
                            @if($category->id != $carCategory->id)
                                <option value="{{ $category->id }}" {{ old('parent_id', $carCategory->parent_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->full_path }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('parent_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div> --}}

            <!-- Category Image -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Category Image')}}</label>
                <div class="col-md-9">
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                        <input type="hidden" name="image" class="selected-files" value="{{ old('image', $carCategory->image) }}">
                    </div>
                    <div class="file-preview box sm">
                        @if($carCategory->image)
                            <div class="d-flex justify-content-between align-items-center mt-2 file-preview-item" data-id="{{ $carCategory->image }}">
                                <div class="align-items-center align-self-stretch d-flex justify-content-center thumb">
                                    <img src="{{ $carCategory->image_url }}" class="img-fit">
                                </div>
                                <div class="col body">
                                    <h6 class="d-flex">
                                        <span class="text-truncate title">{{ basename($carCategory->image) }}</span>
                                    </h6>
                                </div>
                                <div class="remove">
                                    <button class="btn btn-sm btn-link remove-attachment" type="button">
                                        <i class="la la-close"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <small class="text-muted">{{translate('Upload an image for the category. Image format: jpg, jpeg, png, gif, svg. Max size: 2MB')}}</small>
                    @error('image')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Order -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Order')}}</label>
                <div class="col-md-9">
                    <input type="number" placeholder="{{translate('Order')}}" name="order" class="form-control" value="{{ old('order', $carCategory->order) }}" min="0">
                    <small class="text-muted">{{translate('Categories will be sorted by this order')}}</small>
                    @error('order')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Status -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Status')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="status" required>
                        <option value="">{{translate('Select Status')}}</option>
                        <option value="active" {{ old('status', $carCategory->status) == 'active' ? 'selected' : '' }}>{{translate('Active')}}</option>
                        <option value="inactive" {{ old('status', $carCategory->status) == 'inactive' ? 'selected' : '' }}>{{translate('Inactive')}}</option>
                        <option value="pending" {{ old('status', $carCategory->status) == 'pending' ? 'selected' : '' }}>{{translate('Pending')}}</option>
                    </select>
                    @error('status')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Default Category -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Default Category')}}</label>
                <div class="col-md-9">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" name="is_default" value="1" {{ old('is_default', $carCategory->is_default) ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                    <small class="text-muted">{{translate('Default categories cannot be deleted')}}</small>
                    @error('is_default')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
             {{-- Car Brands --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Car Brands')}}</label>
                <div class="col-md-9">
                    <select class="select2 form-control aiz-selectpicker" name="brand_ids[]" data-toggle="select2" data-placeholder="Choose ..."data-live-search="true" data-selected="{{ $carCategory->brands->pluck('id') }}"multiple>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @if(old('brand_ids', $carCategory->brand_ids) && in_array($brand->id, old('brand_ids', $carCategory->brand_ids))) selected @endif >{{ $brand->getTranslation('name') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Submit -->
            <div class="form-group row">
                <div class="col-md-9 col-md-offset-3">
                    <button type="submit" class="btn btn-primary">{{translate('Update Category')}}</button>
                    <a href="{{ route('admin.car-categories.index') }}" class="btn btn-light">{{translate('Cancel')}}</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
