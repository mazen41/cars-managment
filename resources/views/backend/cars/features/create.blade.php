@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Add New Car Feature')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-features.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to Features')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Feature Information')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.car-features.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Feature Name -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Feature Name')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" placeholder="{{translate('Feature Name')}}" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <!-- Feature Section -->
               <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Feature Section')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="section_id" data-live-search="true">
                        <option value="0">{{translate('Section')}}</option>
                        @foreach($carFeatureSections as $section)
                            <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('section_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Feature Image -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Feature Image')}}</label>
                <div class="col-md-9">
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                        <input type="hidden" name="image" class="selected-files" value="{{ old('image') }}">
                    </div>
                    <div class="file-preview box sm">
                    </div>
                    <small class="text-muted">{{translate('Upload an image for the feature. Image format: jpg, jpeg, png, gif, svg. Max size: 2MB')}}</small>
                    @error('image')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>


            <!-- Submit -->
            <div class="form-group row">
                <div class="col-md-9 col-md-offset-3">
                    <button type="submit" class="btn btn-primary">{{translate('Save Feature')}}</button>
                    <a href="{{ route('admin.car-features.index') }}" class="btn btn-light">{{translate('Cancel')}}</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
