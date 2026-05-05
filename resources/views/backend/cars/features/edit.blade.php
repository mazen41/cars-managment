@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Edit Car Feature')}}</h1>
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
        <form action="{{ route('admin.car-features.update', $carFeature->id) }}" method="POST" enctype="multipart/form-data">
             <ul class="nav nav-tabs nav-fill language-bar">
                @foreach (get_all_active_language() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == request()->input('lang')) active @endif py-3" href="{{ route('admin.car-features.edit', ['carFeature'=>$carFeature->id, 'lang'=> $language->code] ) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                            <span>{{ $language->name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            @csrf
            @method('PUT')
            <input type="hidden" name="lang" value="{{request()->input('lang')}}"/>

            <!-- Feature Name -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Feature Name')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" placeholder="{{translate('Feature Name')}}" id="name" name="name" class="form-control" required value="{{ old('name', $carFeature->getTranslation('name', request()->input('lang'))) }}">
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
                            <option value="{{ $section->id }}" {{ old('section_id', $carFeature->section ? $carFeature->section->id : null) == $section->id ? 'selected' : null }}>
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
                        <input type="hidden" name="image" class="selected-files" value="{{ old('image', $carFeature->image) }}">
                    </div>
                    <div class="file-preview box sm">
                        @if($carFeature->image)
                            <div class="d-flex justify-content-between align-items-center mt-2 file-preview-item" data-id="{{ $carFeature->image }}">
                                <div class="align-items-center align-self-stretch d-flex justify-content-center thumb">
                                    <img src="{{ $carFeature->image_url }}" class="img-fit">
                                </div>
                                <div class="col body">
                                    <h6 class="d-flex">
                                        <span class="text-truncate title">{{ basename($carFeature->image) }}</span>
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
                    <small class="text-muted">{{translate('Upload an image for the feature. Image format: jpg, jpeg, png, gif, svg. Max size: 2MB')}}</small>
                    @error('image')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>


            <!-- Submit -->
            <div class="form-group row">
                <div class="col-md-9 col-md-offset-3">
                    <button type="submit" class="btn btn-primary">{{translate('Update Feature')}}</button>
                    <a href="{{ route('admin.car-features.index') }}" class="btn btn-light">{{translate('Cancel')}}</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
