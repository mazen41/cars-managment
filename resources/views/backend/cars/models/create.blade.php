@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Add New Car Model')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-models.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to Models')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Model Information')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.car-models.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Model Name -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Model Name')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" placeholder="{{translate('Model Name')}}" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Brand -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Brand')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="brand_id" required>
                        <option value="">{{translate('Select Brand')}}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id')
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
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>{{translate('Active')}}</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{translate('Inactive')}}</option>
                    </select>
                    @error('status')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Submit -->
            <div class="form-group row">
                <div class="col-md-9 col-md-offset-3">
                    <button type="submit" class="btn btn-primary">{{translate('Save Model')}}</button>
                    <a href="{{ route('admin.car-models.index') }}" class="btn btn-light">{{translate('Cancel')}}</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
