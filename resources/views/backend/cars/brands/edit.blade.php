@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Edit Car Brand')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-brands.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to Brands')}}</span>
            </a>
        </div>
    </div>
</div>
<ul class="nav nav-tabs nav-fill language-bar">
    @foreach (get_all_active_language() as $key => $language)
        <li class="nav-item">
            <a class="nav-link text-reset @if ($language->code == request()->input('lang')) active @endif py-3" href="{{ route('admin.car-brands.edit', ['carBrand'=>$carBrand->id, 'lang'=> $language->code] ) }}">
                <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                <span>{{ $language->name }}</span>
            </a>
        </li>
    @endforeach
</ul>
<form action="{{ route('admin.car-brands.update', $carBrand->id) }}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="lang" value="{{request()->input('lang')}}"/>
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Brand Information')}}</h5>
                </div>
                <div class="card-body">


                    <!-- Brand Name -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Brand Name')}} <span
                                class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Brand Name')}}" id="name" name="name"
                                class="form-control" required value="{{ old('name', $carBrand->getTranslation('name', request()->input('lang'))) }}">
                            @error('name')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Brand Logo -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Brand Logo')}}</label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{
                                        translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="logo" class="selected-files"
                                    value="{{ old('logo', $carBrand->logo) }}">
                            </div>
                            <div class="file-preview box sm">
                                @if($carBrand->logo)
                                <div class="d-flex justify-content-between align-items-center mt-2 file-preview-item"
                                    data-id="{{ $carBrand->logo }}">
                                    <div
                                        class="align-items-center align-self-stretch d-flex justify-content-center thumb">
                                        <img src="{{ $carBrand->logo_url }}" class="img-fit">
                                    </div>
                                    <div class="col body">
                                        <h6 class="d-flex">
                                            <span class="text-truncate title">{{ basename($carBrand->logo) }}</span>
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
                            <small class="text-muted">{{translate('Upload a logo for the brand. Image format: jpg, jpeg,
                                png, gif, svg. Max size: 2MB')}}</small>
                            @error('logo')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <!-- Submit -->
                    <div class="form-group row">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">{{translate('Update Brand')}}</button>
                            <a href="{{ route('admin.car-brands.index') }}"
                                class="btn btn-light">{{translate('Cancel')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Additional Information')}}</h5>
                </div>
                <div class="card-body">
                    <!-- Status -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Status')}} <span
                                class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="status" required>
                                <option value="">{{translate('Select Status')}}</option>
                                <option value="active" {{ old('status', $carBrand->status) == 'active' ? 'selected'
                                    : ''
                                    }}>{{translate('Active')}}</option>
                                <option value="inactive" {{ old('status', $carBrand->status) == 'inactive' ?
                                    'selected' : ''
                                    }}>{{translate('Inactive')}}</option>
                                <option value="pending" {{ old('status', $carBrand->status) == 'pending' ?
                                    'selected' : ''
                                    }}>{{translate('Pending')}}</option>
                            </select>
                            @error('status')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
