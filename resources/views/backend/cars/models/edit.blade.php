@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Edit Car Model')}}</h1>
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
        <form action="{{ route('admin.car-models.update', $carModel->id) }}" method="POST" enctype="multipart/form-data">
          <ul class="nav nav-tabs nav-fill language-bar">
                @foreach (get_all_active_language() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == request()->input('lang')) active @endif py-3" href="{{ route('admin.car-models.edit', ['carModel'=>$carModel->id, 'lang'=> $language->code] ) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                            <span>{{ $language->name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            @csrf
            @method('PUT')
            <input type="hidden" name="lang" value="{{request()->input('lang')}}"/>

            <!-- Model Name -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Model Name')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" placeholder="{{translate('Model Name')}}" id="name" name="name" class="form-control" required value="{{ old('name', $carModel->getTranslation('name', request()->input('lang'))) }}">
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
                            <option value="{{ $brand->id }}" {{ old('brand_id', $carModel->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
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
                        <option value="active" {{ old('status', $carModel->status) == 'active' ? 'selected' : '' }}>{{translate('Active')}}</option>
                        <option value="inactive" {{ old('status', $carModel->status) == 'inactive' ? 'selected' : '' }}>{{translate('Inactive')}}</option>
                    </select>
                    @error('status')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Submit -->
            <div class="form-group row">
                <div class="col-md-9 col-md-offset-3">
                    <button type="submit" class="btn btn-primary">{{translate('Update Model')}}</button>
                    <a href="{{ route('admin.car-models.index') }}" class="btn btn-light">{{translate('Cancel')}}</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
