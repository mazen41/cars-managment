@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Edit Car Feature Section')}}</h1>
        </div>
    </div>
</div>

<div class="col-lg-8 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Car Feature Section Information')}}</h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('admin.car-features.section.update', $carFeatureSection->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{translate('Section Name')}} <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <input type="text" placeholder="{{translate('Section Name')}}" id="name" name="name" class="form-control" value="{{ old('name', $carFeatureSection->name) }}" required>
                        @error('name')
                            <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{translate('Features Count')}}</label>
                    <div class="col-md-9">
                        <div class="form-control-plaintext">
                            <span class="badge badge-inline badge-info">{{ $carFeatureSection->features()->count() }} {{translate('features')}}</span>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                    <a href="{{ route('admin.car-features.section.index') }}" class="btn btn-secondary">{{translate('Cancel')}}</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
