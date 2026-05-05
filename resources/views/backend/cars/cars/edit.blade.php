@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="aiz-titlebar text-left mt-2 pb-2 px-3 px-md-2rem border-bottom border-gray">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3">{{ translate('Edit Car') }}</h1>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.cars.show', $car->id) }}" class="btn btn-primary">
                    <i class="las la-eye"></i> {{ translate('Show car') }}
                </a>
        </div>
    </div>

    <div class="d-sm-flex">
        <!-- page side nav -->
        <div class="page-side-nav c-scrollbar-light px-3 py-2">
            <ul class="nav nav-tabs flex-sm-column border-0" role="tablist" aria-orientation="vertical">
                <!-- General -->
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" href="#general"
                        data-toggle="tab" data-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                        {{ translate('General') }}
                    </a>
                </li>
                <!-- Photos -->
                <li class="nav-item">
                    <a class="nav-link" id="photos-tab" href="#photos"
                        data-toggle="tab" data-target="#photos" type="button" role="tab" aria-controls="photos" aria-selected="false">
                        {{ translate('Photos') }}
                    </a>
                </li>
                <!-- Features -->
                <li class="nav-item">
                    <a class="nav-link" id="features-tab" href="#features"
                        data-toggle="tab" data-target="#features" type="button" role="tab" aria-controls="features" aria-selected="false">
                        {{ translate('Features') }}
                    </a>
                </li>
                <!-- Custom Fields -->
                <li class="nav-item">
                    <a class="nav-link" id="custom-fields-tab" href="#custom_fields"
                        data-toggle="tab" data-target="#custom_fields" type="button" role="tab" aria-controls="custom_fields" aria-selected="false">
                        {{ translate('Custom Fields') }}
                    </a>
                </li>
                <!-- Location -->
                <li class="nav-item">
                    <a class="nav-link" id="location-tab" href="#location"
                        data-toggle="tab" data-target="#location" type="button" role="tab" aria-controls="location" aria-selected="false">
                        {{ translate('Location') }}
                    </a>
                </li>
            </ul>
        </div>

        <!-- tab content -->
        <div class="flex-grow-1 p-sm-3 p-lg-2rem mb-2rem mb-md-0">
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.cars.update', $car->id) }}" method="POST" enctype="multipart/form-data" id="car_form" novalidate>
                @csrf
                @method('PUT')
                <div class="tab-content">
                    <!-- General -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                        <div class="bg-surface p-3 p-sm-2rem">
                            <!-- Car Information -->
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">{{translate('Car Information')}}</h5>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="vin">{{ translate('VIN') }}</label>
                                        <input type="text" class="form-control" name="vin" value="{{ old('vin', $car->vin) }}" maxlength="17">
                                    </div>
                                </div>
                                  <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="condition">{{ translate('Condition') }} <span class="text-danger">*</span></label>
                                        <select class="form-control aiz-selectpicker" id="condition" name="condition" required>
                                            <option value="">{{ translate('Select Condition') }}</option>
                                            <option value="new" {{ old('condition', $car->condition) == 'new' ? 'selected' : '' }}>{{ translate('New') }}</option>
                                            <option value="used" {{ old('condition', $car->condition) == 'used' ? 'selected' : '' }}>{{ translate('Used') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="color">{{ translate('Color') }} <span class="text-danger">*</span></label>
                                        <select class="form-control aiz-selectpicker" id="color_id" name="color_id" required>
                                            <option value="">{{ translate('Select Color') }}</option>
                                            @foreach ($colors as $color)
                                                <option value="{{ $color->id }}" {{ old('color', $car->color_id) == $color->id ? 'selected' : '' }}>{{ $color->getTranslation('name') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">{{ translate('Description') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control aiz-text-editor" id="description" name="description" rows="8" placeholder="{{ translate('Enter car description') }}" required>{{ old('description', $car->description) }}</textarea>
                            </div>

                            <!-- Brand & Model -->
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">{{translate('Brand & Model')}}</h5>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="brand_id">{{ translate('Brand') }} <span class="text-danger">*</span></label>
                                        <select class="form-control aiz-selectpicker" id="brand_id" name="brand_id" data-live-search="true" onchange="get_models_by_brand()" required>
                                            <option value="">{{ translate('Select Brand') }}</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ old('brand_id', $car->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->getTranslation('name') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="model_id">{{ translate('Model') }} <span class="text-danger">*</span></label>
                                        <select class="form-control aiz-selectpicker" id="model_id" name="model_id" data-live-search="true" required>
                                            <option value="">{{ translate('Select Model') }}</option>
                                            <option value="{{ $car->model_id }}" {{ old('model_id', $car->model_id) == $car->model_id ? 'selected' : '' }}>{{ $car->model->getTranslation('name') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category_id">{{ translate('Category') }}</label>
                                        <select class="form-control aiz-selectpicker" id="category_id" name="category_id" data-live-search="true">
                                            <option value="">{{ translate('Select Category') }}</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id', $car->category_id) == $category->id ? 'selected' : '' }}>{{ $category->getTranslation('name') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Car Details -->
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">{{translate('Car Details')}}</h5>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="manufacture_year">{{ translate('Manufacture Year') }} <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="manufacture_year" name="manufacture_year" value="{{ old('manufacture_year', $car->manufacture_year) }}" min="1900" max="{{ date('Y') + 1 }}" placeholder="{{ translate('Enter year') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="milage">{{ translate('Mileage (km)') }} <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="milage" name="milage" value="{{ old('milage', $car->milage) }}" min="0" step="0.01" placeholder="{{ translate('Enter mileage') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="price">{{ translate('Price') }}</label>
                                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $car->price) }}" min="0" step="0.01" placeholder="{{ translate('Enter price') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="transmission">{{ translate('Transmission') }}</label>
                                         <select class="form-control aiz-selectpicker" id="transmission" name="transmission" required>
                                            @foreach (\App\Enums\CarTransmissionTypeEnum::values() as $transmission)
                                                <option value="{{ $transmission->getValue() }}" {{ old('transmission', $car->transmission) == $transmission->getValue() ? 'selected' : '' }}>
                                                    {{ $transmission->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fuel_type">{{ translate('Fuel Type') }}</label>
                                        <select class="form-control aiz-selectpicker" id="fuel_type" name="fuel_type" required>
                                            @foreach (\App\Enums\CarFuelTypeEnum::values() as $fuelType)
                                                <option value="{{ $fuelType->getValue() }}" {{ old('fuel_type', $car->fuel_type) == $fuelType->getValue() ? 'selected' : '' }}>
                                                    {{ $fuelType->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">{{translate('Status')}}</h5>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="moderation_status">{{ translate('Moderation Status') }} <span class="text-danger">*</span></label>
                                        <select class="form-control aiz-selectpicker" id="moderation_status" name="moderation_status" required>
                                            <option value="pending" {{ old('moderation_status', $car->moderation_status->getValue()) == 'pending' ? 'selected' : '' }}>{{ translate('Pending') }}</option>
                                            <option value="published" {{ old('moderation_status', $car->moderation_status->getValue()) == 'published' ? 'selected' : '' }}>{{ translate('Published') }}</option>
                                            <option value="rejected" {{ old('moderation_status', $car->moderation_status->getValue()) == 'rejected' ? 'selected' : '' }}>{{ translate('Rejected') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="car_status">{{ translate('Car Status') }} <span class="text-danger">*</span></label>
                                        <select class="form-control aiz-selectpicker" id="car_status" name="car_status" required>
                                            <option value="available" {{ old('car_status', $car->car_status->getValue()) == 'available' ? 'selected' : '' }}>{{ translate('Available') }}</option>
                                            <option value="reserved" {{ old('car_status', $car->car_status->getValue()) == 'reserved' ? 'selected' : '' }}>{{ translate('Reserved') }}</option>
                                            <option value="in_auction" {{ old('car_status', $car->car_status->getValue()) == 'in_auction' ? 'selected' : '' }}>{{ translate('In Auction') }}</option>
                                            <option value="sold" {{ old('car_status', $car->car_status->getValue()) == 'sold' ? 'selected' : '' }}>{{ translate('Sold') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Photos -->
                    <div class="tab-pane fade" id="photos" role="tabpanel" aria-labelledby="photos-tab">
                       <div class="bg-surface p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">{{translate('Car Photos')}}</h5>
                            <div class="form-group">
                                <label for="photos">{{ translate('Main Photo') }}<span class="text-danger">*</span></label>
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose Files') }}</div>
                                    <input type="hidden" name="main_photo" class="selected-files" value="{{$car->main_photo}}" required>
                                </div>
                                <div class="file-preview box sm">
                                </div>
                                <small class="text-muted">{{translate('These images are visible in car gallery.')}} {{translate('Use 600x600 sizes images for best view.')}}</small>
                            </div>
                            <div class="form-group">
                                <label for="photos">{{ translate('Photos') }}</label>
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose Files') }}</div>
                                    <input type="hidden" name="photos" class="selected-files" value="{{ $car->photos }}">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                                <small class="text-muted">{{translate('These images are visible in car gallery.')}} {{translate('Use 600x600 sizes images for best view.')}}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="tab-pane fade" id="features" role="tabpanel" aria-labelledby="features-tab">
                        <div class="bg-surface p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">{{translate('Car Features')}}</h5>


                                @foreach ($features as $section_name => $feature_list)
                                <h6 class="mb-3 pb-3 fs-17 fw-700 text-primary text-center" >{{$section_name}}</h6>
                                  <div class="row">
                                    @foreach ($feature_list as $feature)
                                         <div class="col-md-4 col-sm-6">
                                        <div class="form-group">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" name="features[]" value="{{ $feature->id }}" {{ in_array($feature->id, old('features', $car->features->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                <span class="aiz-square-check"></span>
                                                <span class="aiz-checkbox-text">{{ $feature->getTranslation('name') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>


                    <!-- Custom Fields -->
                    <div class="tab-pane fade" id="custom_fields" role="tabpanel" aria-labelledby="custom-fields-tab">
                        <div class="bg-surface p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">{{translate('Custom Fields')}}</h5>

                            @foreach ($customFields as $field)
                                @php
                                    $customFieldValue = $car->customFieldValues->where('custom_field_id', $field->id)->first();
                                    $fieldValue = $customFieldValue ? $customFieldValue->value : '';
                                @endphp
                                <div class="form-group">
                                    <label for="custom_field_{{ $field->id }}">
                                        {{ $field->name }}
                                        @if($field->required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    @if($field->type == 'text')
                                        <input type="text" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" value="{{ old('custom_field_' . $field->id, $fieldValue) }}" {{ $field->required ? 'required' : '' }}>
                                    @elseif($field->type == 'number')
                                        <input type="number" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" value="{{ old('custom_field_' . $field->id, $fieldValue) }}" {{ $field->required ? 'required' : '' }}>
                                    @elseif($field->type == 'email')
                                        <input type="email" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" value="{{ old('custom_field_' . $field->id, $fieldValue) }}" {{ $field->required ? 'required' : '' }}>
                                    @elseif($field->type == 'url')
                                        <input type="url" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" value="{{ old('custom_field_' . $field->id, $fieldValue) }}" {{ $field->required ? 'required' : '' }}>
                                    @elseif($field->type == 'textarea')
                                        <textarea class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" rows="4" {{ $field->required ? 'required' : '' }}>{{ old('custom_field_' . $field->id, $fieldValue) }}</textarea>
                                    @elseif($field->type == 'select')
                                        <select class="form-control aiz-selectpicker" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" {{ $field->required ? 'required' : '' }}>
                                            <option value="">{{ translate('Select Option') }}</option>
                                            @foreach($field->options as $option)
                                                <option value="{{ $option->value }}" {{ old('custom_field_' . $field->id, $fieldValue) == $option->value ? 'selected' : '' }}>{{ $option->label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($field->type == 'radio')
                                        <div class="row">
                                            @foreach($field->options as $option)
                                                <div class="col-md-3">
                                                    <label class="aiz-radio">
                                                        <input type="radio" name="custom_field_{{ $field->id }}" value="{{ $option->value }}" {{ old('custom_field_' . $field->id, $fieldValue) == $option->value ? 'checked' : '' }} {{ $field->required ? 'required' : '' }}>
                                                        <span class="aiz-rounded-check"></span>
                                                        <span class="aiz-radio-text">{{ $option->label }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($field->type == 'checkbox')
                                        @php
                                            $checkboxValues = is_string($fieldValue) ? json_decode($fieldValue, true) : [];
                                            if (!is_array($checkboxValues)) $checkboxValues = [];
                                        @endphp
                                        <div class="row">
                                            @foreach($field->options as $option)
                                                <div class="col-md-3">
                                                    <label class="aiz-checkbox">
                                                        <input type="checkbox" name="custom_field_{{ $field->id }}[]" value="{{ $option->value }}" {{ in_array($option->value, old('custom_field_' . $field->id, $checkboxValues)) ? 'checked' : '' }}>
                                                        <span class="aiz-square-check"></span>
                                                        <span class="aiz-checkbox-text">{{ $option->label }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($field->type == 'date')
                                        <input type="date" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" value="{{ old('custom_field_' . $field->id, $fieldValue) }}" {{ $field->required ? 'required' : '' }}>
                                    @elseif($field->type == 'time')
                                        <input type="time" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" value="{{ old('custom_field_' . $field->id, $fieldValue) }}" {{ $field->required ? 'required' : '' }}>
                                    @elseif($field->type == 'datetime')
                                        <input type="datetime-local" class="form-control" id="custom_field_{{ $field->id }}" name="custom_field_{{ $field->id }}" value="{{ old('custom_field_' . $field->id, $fieldValue) }}" {{ $field->required ? 'required' : '' }}>
                                    @elseif($field->type == 'file')
                                        <div class="input-group" data-toggle="aizuploader" data-type="document">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="custom_field_{{ $field->id }}" class="selected-files" value="{{ old('custom_field_' . $field->id, $fieldValue) }}">
                                        </div>
                                    @elseif($field->type == 'image')
                                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose Image') }}</div>
                                            <input type="hidden" name="custom_field_{{ $field->id }}" class="selected-files" value="{{ old('custom_field_' . $field->id, $fieldValue) }}">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="tab-pane fade" id="location" role="tabpanel" aria-labelledby="location-tab">
                        <div class="bg-surface p-3 p-sm-2rem">
                            <h5 class="mb-3 pb-3 fs-17 fw-700" style="border-bottom: 1px dashed #e4e5eb;">{{translate('Location')}}<span class="text-danger">*</span></h5>

                            <div class="form-group">
                                <label for="location">{{ translate('Location') }}</label>
                                <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $car->location) }}" placeholder="{{ translate('Enter location') }}" required>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country_id">{{ translate('Country') }}</label>
                                        <select class="form-control aiz-selectpicker" id="country_id" name="country_id" data-live-search="true" onchange="get_states()" required>
                                            <option value="">{{ translate('Select Country') }}</option>
                                            @if(isset($countries))
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}" {{ old('country_id', $car->country_id) == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state_id">{{ translate('State') }}</label>
                                        <select class="form-control aiz-selectpicker" id="state_id" name="state_id" data-live-search="true" onchange="get_cities()"  required>
                                            @if ($car->state)
                                                <option value="{{ $car->state_id }}" {{ old('state_id', $car->state_id) == $car->state_id ? 'selected' : '' }}>{{ $car->state->name }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city_id">{{ translate('City') }}</label>
                                        <select class="form-control aiz-selectpicker" id="city_id" name="city_id" data-live-search="true">
                                            @if ($car->city)
                                                <option value="{{ $car->city_id }}" {{ $car->city_id == $car->city_id ? 'selected' : '' }}>{{ $car->city->name }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-surface p-3 p-sm-2rem">
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">{{ translate('Update Car') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<style>
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    .bootstrap-select .is-invalid + .dropdown-toggle {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
</style>
<script type="text/javascript">
    function get_models_by_brand() {
        var brand_id = $('#brand_id').val();
        var model_id = $("#model_id").val();
        if (brand_id) {
            $.get('{{ route('admin.cars.models-by-brand', '') }}/' + brand_id, function(data) {
                $('#model_id').html('<option value="">{{ translate('Select Model') }}</option>');
                for (var i = 0; i < data.models.length; i++) {
                    $('#model_id').append('<option value="' + data.models[i].id + '"'+ (model_id == data.models[i].id ? ' selected' : '') +'>' + data.models[i].name + '</option>');
                }
                AIZ.plugins.bootstrapSelect('refresh');
            });
        } else {
            $('#model_id').html('<option value="">{{ translate('Select Model') }}</option>');
            AIZ.plugins.bootstrapSelect('refresh');
        }
    }

    function get_states() {
        var country_id = $('#country_id').val();

        if (country_id) {
            $.post('{{ route('get-state') }}', {
                _token: '{{ csrf_token() }}',
                country_id: country_id
            }, function(data) {
                let states = JSON.parse(data)
                if(states != ''){
                    $('#state_id').append(states);
                }

                AIZ.plugins.bootstrapSelect('refresh');

                $('#city_id').html('<option value="">{{ translate('Select City') }}</option>');
                AIZ.plugins.bootstrapSelect('refresh');
            });
        } else {
            $('#state_id').html('<option value="">{{ translate('Select State') }}</option>');
            $('#city_id').html('<option value="">{{ translate('Select City') }}</option>');
            AIZ.plugins.bootstrapSelect('refresh');
        }
    }

    function get_cities() {
        var state_id = $('#state_id').val();
        var city_id = $('#city_if').val();
        if (state_id) {
            $.post('{{ route('get-city') }}', {
                _token: '{{ csrf_token() }}',
                state_id: state_id
            }, function(data) {
                let cities = JSON.parse(data);
                if(cities != ''){
                    $('#city_id').append(cities)
                }
                AIZ.plugins.bootstrapSelect('refresh');
            });
        } else {
            $('#city_id').html('<option value="">{{ translate('Select City') }}</option>');
            AIZ.plugins.bootstrapSelect('refresh');
        }
    }

    $(document).ready(function() {
        // Initialize tab navigation
        $('.nav-tabs a').click(function(e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // Set first tab as active
        $('#general-tab').tab('show');

        // Load models if brand is selected (for edit mode)
        if ($('#brand_id').val()) {
            get_models_by_brand();
        }

        // Load states if country is selected (for edit mode)
        if ($('#country_id').val()) {
            get_states();
        }

        // Load cities if state is selected (for edit mode)
        if ($('#state_id').val()) {
            get_cities();
        }

        // Form validation before submission
        $('#car_form').on('submit', function(e) {
            e.preventDefault();
            // Find all required fields that are invalid
            var invalidFields = [];
            var form = this;

            // Check all required fields
            $(form).find('[required]').each(function() {
                var field = $(this);
                var isValid = true;

                // Skip hidden selectpicker elements to avoid focus issues
                if (field.is('select.aiz-selectpicker') && field.is(':hidden')) {
                    // Check if selectpicker has a value
                    isValid = field.val() !== '' && field.val() !== null;
                } else if (field.is('select')) {
                    isValid = field.val() !== '' && field.val() !== null;
                } else if (field.is('input[type="radio"]')) {
                    var radioName = field.attr('name');
                    isValid = $('input[name="' + radioName + '"]:checked').length > 0;
                } else if (field.is('input[type="checkbox"]')) {
                    var checkboxName = field.attr('name');
                    if (checkboxName && checkboxName.includes('[]')) {
                        // For checkbox arrays, check if at least one is checked
                        isValid = $('input[name="' + checkboxName + '"]:checked').length > 0;
                    } else {
                        isValid = field.is(':checked');
                    }
                } else if (field.is('textarea')) {
                    isValid = field.val().trim() !== '';
                } else {
                    isValid = field.val().trim() !== '';
                }

                if (!isValid) {
                    invalidFields.push(field);
                }
            });

            if (invalidFields.length > 0) {
                // Find the first invalid field and determine which tab it belongs to
                var firstInvalidField = invalidFields[0];
                var targetTab = null;
                var targetTabPane = firstInvalidField.closest('.tab-pane');

                if (targetTabPane.length > 0) {
                    var tabId = targetTabPane.attr('id');
                    targetTab = $('[data-target="#' + tabId + '"]');

                    // Activate the tab containing the first invalid field
                    if (targetTab.length > 0) {
                        targetTab.tab('show');
                    }
                }

                // Focus on the first invalid field after a short delay to ensure tab is fully shown
                setTimeout(function() {
                    // Remove any existing error highlighting
                    $('.form-control, .aiz-selectpicker').removeClass('is-invalid');

                    // Highlight all invalid fields
                    invalidFields.forEach(function(field) {
                        field.addClass('is-invalid');
                    });

                    // Focus on the first invalid field
                    if (firstInvalidField.hasClass('aiz-selectpicker')) {
                        // For selectpicker, find the bootstrap-select button
                        var selectpickerContainer = firstInvalidField.parent('.bootstrap-select');
                        var selectpickerBtn = selectpickerContainer.find('.dropdown-toggle');
                        if (selectpickerBtn.length > 0) {
                            selectpickerBtn.focus();
                            // Scroll to the selectpicker button instead of hidden select
                            $('html, body').animate({
                                scrollTop: selectpickerBtn.offset().top - 100
                            }, 300);
                        }
                    } else if (firstInvalidField.is(':visible') && !firstInvalidField.is(':hidden')) {
                        firstInvalidField.focus();
                        // Scroll to the first invalid field
                        $('html, body').animate({
                            scrollTop: firstInvalidField.offset().top - 100
                        }, 300);
                    } else {
                        // For hidden elements, just scroll to their container
                        var visibleContainer = firstInvalidField.closest('.form-group');
                        if (visibleContainer.length > 0) {
                            $('html, body').animate({
                                scrollTop: visibleContainer.offset().top - 100
                            }, 300);
                        }
                    }
                }, 150);

                // Show validation message
                if ($('.alert-danger').length === 0) {
                    AIZ.plugins.notify('danger',  "{{ translate("Please fill in all required fields.") }}");
                }

                return false; // Prevent form submission
            } else {
                // Remove any error highlighting if all fields are valid
                $('.form-control, .aiz-selectpicker').removeClass('is-invalid');
                $('.alert-danger').remove();

                // Allow form submission
                form.submit();
            }
        });

        // Remove invalid class when user starts typing/selecting
        $(document).on('input change', '.form-control, .aiz-selectpicker', function() {
            $(this).removeClass('is-invalid');
        });

        // Handle selectpicker change events specifically
        $(document).on('changed.bs.select', '.aiz-selectpicker', function() {
            $(this).removeClass('is-invalid');
        });
    });
</script>
@endsection
