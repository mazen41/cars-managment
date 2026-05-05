@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Add New Car Brand')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-brands.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to Brands')}}</span>
            </a>
        </div>
    </div>
</div>
<form action="{{ route('admin.car-brands.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

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
                                class="form-control" required value="{{ old('name') }}">
                            @error('name')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Brand Logo -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Brand Logo')}} <span
                                class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{
                                        translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="logo" class="selected-files" value="{{ old('logo') }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                            <small class="text-muted">{{translate('Upload a logo for the brand. Image format: jpg, jpeg,
                                png, gif, svg. Max size: 2MB')}}</small>
                            @error('logo')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
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
                                <option value="active" {{ old('status')=='active' ? 'selected' : '' }}>
                                    {{translate('Active')}}</option>
                                <option value="inactive" {{ old('status')=='inactive' ? 'selected' : '' }}>
                                    {{translate('Inactive')}}</option>
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
    <!-- Submit -->
    <div class="form-group row">
        <div class="col-md-9 col-md-offset-3">
            <button type="submit" class="btn btn-primary">{{translate('Save Brand')}}</button>
            <a href="{{ route('admin.car-brands.index') }}"
                class="btn btn-light">{{translate('Cancel')}}</a>
        </div>
    </div>
</form>
@endsection
