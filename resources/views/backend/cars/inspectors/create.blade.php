@extends('backend.layouts.app')

@section('content')
 <style>
        #map {
            width: 100%;
            height: 250px;
        }
    </style>

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Add New Car Inspector') }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.car-inspectors.index') }}" class="btn btn-circle btn-light">
                <span>{{ translate('Back to Inspectors') }}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Inspector Information') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.car-inspectors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <!-- User Information Section -->
                <div class="col-lg-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">{{ translate('User Account') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="name">{{ translate('Full Name') }} <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <input type="text" placeholder="{{ translate('Full Name') }}" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                                    @error('name')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="email">{{ translate('Email') }} <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <input type="email" placeholder="{{ translate('Email') }}" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                    @error('email')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="password">{{ translate('Password') }} <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <input type="password" placeholder="{{ translate('Password') }}" id="password" name="password" class="form-control" required>
                                    @error('password')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="password_confirmation">{{ translate('Confirm Password') }} <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <input type="password" placeholder="{{ translate('Confirm Password') }}" id="password_confirmation" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="phone">{{ translate('Phone') }} <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <input type="text" placeholder="{{ translate('Phone') }}" id="phone-code" name="phone" class="form-control" value="{{ old('phone') }}" required>
                                    <input type="hidden" name="country_code" value="">
                                    @error('phone')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shop Information Section -->
                <div class="col-lg-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">{{ translate('Shop Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="shop_name">{{ translate('Shop Name') }} <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <input type="text" placeholder="{{ translate('Shop Name') }}" id="shop_name" name="shop_name" class="form-control" value="{{ old('shop_name') }}" required>
                                    @error('shop_name')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="can_manual_examination">{{ translate('Manual Examinations') }}</label>
                                <div class="col-md-9">
                                    <input type="hidden" name="can_manual_examination" value="0">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="checkbox" name="can_manual_examination" id="can_manual_examination" value="1" {{ old('can_manual_examination', true) ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    <small class="form-text text-muted">{{ translate('Allow this inspection center to access manual examination pages and APIs') }}</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="country_id">{{ translate('Country') }} <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <select class="form-control aiz-selectpicker" id="country_id" name="country_id" data-live-search="true" required>
                                        <option value="">{{ translate('Select Country') }}</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="state_id">{{ translate('State/Province') }}</label>
                                <div class="col-md-9">
                                    <select class="form-control aiz-selectpicker" id="state_id" name="state_id" data-live-search="true">
                                        <option value="">{{ translate('Select State') }}</option>
                                    </select>
                                    @error('state_id')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="city_id">{{ translate('City') }}</label>
                                <div class="col-md-9">
                                    <select class="form-control aiz-selectpicker" id="city_id" name="city_id" data-live-search="true">
                                        <option value="">{{ translate('Select City') }}</option>
                                    </select>
                                    @error('city_id')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="address">{{ translate('Address') }} <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <textarea placeholder="{{ translate('Shop Address') }}" id="address" name="address" class="form-control" rows="3" required>{{ old('address') }}</textarea>
                                    @error('address')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                              @if (get_setting('google_map') == 1)
                            <div class="row">
                                <input id="searchInput" class="controls" type="text"
                                    placeholder="{{ translate('Enter a location') }}">
                                <div id="map"></div>
                                <ul id="geoData">
                                    <li style="display: none;">Full Address: <span id="location"></span></li>
                                    <li style="display: none;">Country: <span id="country"></span></li>
                                    <li style="display: none;">Latitude: <span id="lat"></span></li>
                                    <li style="display: none;">Longitude: <span id="lon"></span></li>
                                </ul>
                            </div>
                             <div class="row">
                                        <div class="col-md-6">
                                            <input type="number" step="any" readonly="" id="latitude" placeholder="{{ translate('Latitude') }}" name="latitude" class="form-control" value="{{ old('latitude') }}">
                                            @error('latitude')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <input type="number" step="any" readonly="" id="longitude" placeholder="{{ translate('Longitude') }}" name="longitude" class="form-control" value="{{ old('longitude') }}">
                                            @error('longitude')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ translate('Location') }}</label>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="number" step="any" placeholder="{{ translate('Latitude') }}" name="latitude" class="form-control" value="{{ old('latitude') }}">
                                            @error('latitude')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <input type="number" step="any" placeholder="{{ translate('Longitude') }}" name="longitude" class="form-control" value="{{ old('longitude') }}">
                                            @error('longitude')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="description">{{ translate('Description') }}</label>
                                <div class="col-md-9">
                                    <textarea placeholder="{{ translate('Shop Description') }}" id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row d-none">
                <!-- Professional Information -->
                <div class="col-lg-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">{{ translate('Professional Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="certification_number">{{ translate('Certification Number') }}</label>
                                <div class="col-md-9">
                                    <input type="text" placeholder="{{ translate('Certification Number') }}" id="certification_number" name="certification_number" class="form-control" value="{{ old('certification_number') }}">
                                    @error('certification_number')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="experience_years">{{ translate('Years of Experience') }}</label>
                                <div class="col-md-9">
                                    <input type="number" min="0" max="50" placeholder="{{ translate('Years') }}" id="experience_years" name="experience_years" class="form-control" value="{{ old('experience_years') }}">
                                    @error('experience_years')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ translate('Services Offered') }}</label>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" name="services_offered[]" value="basic_inspection" {{ in_array('basic_inspection', old('services_offered', [])) ? 'checked' : '' }}>
                                                <span class="aiz-square-check"></span>
                                                <span>{{ translate('Basic Inspection') }}</span>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" name="services_offered[]" value="advanced_inspection" {{ in_array('advanced_inspection', old('services_offered', [])) ? 'checked' : '' }}>
                                                <span class="aiz-square-check"></span>
                                                <span>{{ translate('Advanced Inspection') }}</span>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" name="services_offered[]" value="pre_purchase" {{ in_array('pre_purchase', old('services_offered', [])) ? 'checked' : '' }}>
                                                <span class="aiz-square-check"></span>
                                                <span>{{ translate('Pre-Purchase Inspection') }}</span>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="aiz-checkbox">
                                                <input type="checkbox" name="services_offered[]" value="insurance_claim" {{ in_array('insurance_claim', old('services_offered', [])) ? 'checked' : '' }}>
                                                <span class="aiz-square-check"></span>
                                                <span>{{ translate('Insurance Claim') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images and Working Hours -->
                <div class="col-lg-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">{{ translate('Images & Schedule') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="image">{{ translate('Profile Image') }}</label>
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
                                    @error('image')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="banner_image">{{ translate('Shop Banner') }}</label>
                                <div class="col-md-9">
                                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="banner_image" class="selected-files" value="{{ old('banner_image') }}">
                                    </div>
                                    <div class="file-preview box sm">
                                    </div>
                                    @error('banner_image')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ translate('Working Hours') }}</label>
                                <div class="col-md-9">
                                    <div id="working-hours">
                                        @php
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                        @endphp
                                        @foreach($days as $index => $day)
                                            <div class="row mb-2">
                                                <div class="col-md-3">
                                                    <label class="aiz-checkbox">
                                                        <input type="checkbox" name="working_hours[{{ $day }}][active]" value="1" {{ old("working_hours.{$day}.active") ? 'checked' : '' }}>
                                                        <span class="aiz-square-check"></span>
                                                        <span>{{ translate($dayNames[$index]) }}</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="time" name="working_hours[{{ $day }}][open]" class="form-control form-control-sm" value="{{ old("working_hours.{$day}.open", '09:00') }}">
                                                </div>
                                                <div class="col-md-1 text-center">
                                                    <span>to</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="time" name="working_hours[{{ $day }}][close]" class="form-control form-control-sm" value="{{ old("working_hours.{$day}.close", '18:00') }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-0 text-right">
                <button type="submit" class="btn btn-primary">{{ translate('Create Inspector') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        //phone intl input
        input = document.querySelector("#phone-code");

        var iti = intlTelInput(input, {
            separateDialCode: true,
            utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}?1590403638580",
            onlyCountries: @php echo get_active_countries()->pluck('code') @endphp,
            initialCountry: "ye"

        });

        var country = iti.getSelectedCountryData();
        $('input[name=country_code]').val(country.dialCode);

        input.addEventListener("countrychange", function(e) {
            var country = iti.getSelectedCountryData();
            $('input[name=country_code]').val(country.dialCode);

        });

        // Toggle working hours inputs based on checkbox
        $('input[type="checkbox"][name*="working_hours"]').on('change', function() {
            var day = $(this).attr('name').match(/\[(.*?)\]/)[1];
            var timeInputs = $('input[name*="working_hours[' + day + ']"][type="time"]');

            if (this.checked) {
                timeInputs.prop('disabled', false);
            } else {
                timeInputs.prop('disabled', true);
            }
        });

        // Toggle working hours inputs based on checkbox
        $('input[type="checkbox"][name*="working_hours"]').on('change', function() {
            var day = $(this).attr('name').match(/\[(.*?)\]/)[1];
            var timeInputs = $('input[name*="working_hours[' + day + ']"][type="time"]');

            if (this.checked) {
                timeInputs.prop('disabled', false);
            } else {
                timeInputs.prop('disabled', true);
            }
        });

        // Initialize disabled state
        $('input[type="checkbox"][name*="working_hours"]').each(function() {
            var day = $(this).attr('name').match(/\[(.*?)\]/)[1];
            var timeInputs = $('input[name*="working_hours[' + day + ']"][type="time"]');

            if (!this.checked) {
                timeInputs.prop('disabled', true);
            }
        });

        // Geographic cascading dropdowns
        $('#country_id').on('change', function() {
            var countryId = $(this).val();
            $('#state_id').html('<option value="">{{ translate("Select State") }}</option>');
            $('#city_id').html('<option value="">{{ translate("Select City") }}</option>');

            if (countryId) {
                $.ajax({
                    url: "{{ url('/admin/get-states') }}/" + countryId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $.each(data, function(key, value) {
                            $('#state_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        $('#state_id').selectpicker('refresh');
                    }
                });
            }
            $('#state_id').selectpicker('refresh');
            $('#city_id').selectpicker('refresh');
        });

        $('#state_id').on('change', function() {
            var stateId = $(this).val();
            $('#city_id').html('<option value="">{{ translate("Select City") }}</option>');

            if (stateId) {
                $.ajax({
                    url: "{{ url('/admin/get-cities') }}/" + stateId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $.each(data, function(key, value) {
                            $('#city_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        $('#city_id').selectpicker('refresh');
                    }
                });
            }
            $('#city_id').selectpicker('refresh');
        });
    });
</script>
@if (get_setting('google_map') == 1)
        <script>
            let default_longtitude = "{{ get_setting('google_map_longtitude') }}";
            let default_latitude = "{{ get_setting('google_map_latitude') }}";

            function initialize(lat = -33.8688, lang = 151.2195, id_format = '') {

                var long = lang;
                var lat = lat;
                if (default_longtitude != '' && default_latitude != '') {
                    long = default_longtitude;
                    lat = default_latitude;
                }
                 @if (get_setting('delivery_pickup_latitude'))
                    long = {{ get_setting('delivery_pickup_longitude') }};
                    lat = {{ get_setting('delivery_pickup_latitude') }};
                @endif

                var map = new google.maps.Map(document.getElementById(id_format + 'map'), {
                    center: {
                        lat: lat,
                        lng: long
                    },
                    zoom: 13
                });

                var myLatlng = new google.maps.LatLng(lat, long);

                var input = document.getElementById(id_format + 'searchInput');
                //                console.log(input);
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

                var autocomplete = new google.maps.places.Autocomplete(input);

                autocomplete.bindTo('bounds', map);

                var infowindow = new google.maps.InfoWindow();
                var marker = new google.maps.Marker({
                    map: map,
                    position: myLatlng,
                    anchorPoint: new google.maps.Point(0, -29),
                    draggable: true,
                });

                map.addListener('click', function(event) {
                    marker.setPosition(event.latLng);
                    document.getElementById(id_format + 'latitude').value = event.latLng.lat();
                    document.getElementById(id_format + 'longitude').value = event.latLng.lng();
                    infowindow.setContent('Latitude: ' + event.latLng.lat() + '<br>Longitude: ' + event.latLng.lng());
                    infowindow.open(map, marker);
                });

                google.maps.event.addListener(marker, 'dragend', function(event) {
                    document.getElementById(id_format + 'latitude').value = event.latLng.lat();
                    document.getElementById(id_format + 'longitude').value = event.latLng.lng();
                    infowindow.setContent('Latitude: ' + event.latLng.lat() + '<br>Longitude: ' + event.latLng.lng());
                    infowindow.open(map, marker);
                });

                autocomplete.addListener('place_changed', function() {
                    infowindow.close();
                    marker.setVisible(false);
                    var place = autocomplete.getPlace();

                    if (!place.geometry) {
                        window.alert("Autocomplete's returned place contains no geometry");
                        return;
                    }

                    // If the place has a geometry, then present it on a map.
                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(17);
                    }
                    marker.setPosition(place.geometry.location);
                    marker.setVisible(true);

                    var address = '';
                    if (place.address_components) {
                        address = [
                            (place.address_components[0] && place.address_components[0].short_name || ''),
                            (place.address_components[1] && place.address_components[1].short_name || ''),
                            (place.address_components[2] && place.address_components[2].short_name || '')
                        ].join(' ');
                    }

                    infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
                    infowindow.open(map, marker);

                    //Location details
                    for (var i = 0; i < place.address_components.length; i++) {
                        if (place.address_components[i].types[0] == 'postal_code') {
                            document.getElementById('postal_code').innerHTML = place.address_components[i].long_name;
                        }
                        if (place.address_components[i].types[0] == 'country') {
                            document.getElementById('country').innerHTML = place.address_components[i].long_name;
                        }
                    }
                    document.getElementById('location').innerHTML = place.formatted_address;
                    document.getElementById(id_format + 'latitude').value = place.geometry.location.lat();
                    document.getElementById(id_format + 'longitude').value = place.geometry.location.lng();
                });

            }
        </script>

        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ env('MAP_API_KEY') }}&libraries=places&language=en&callback=initialize"
            async defer></script>
    @endif
@endsection
