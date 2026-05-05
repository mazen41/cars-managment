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
            <h1 class="h3">{{translate('Customer Product Details')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.customer-products.index') }}" class="btn btn-light">
                <i class="las la-arrow-left"></i> {{translate('Back to List')}}
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{translate('Product Name')}}</label>
                            <div class="mb-0 font-weight-medium">{{ $product->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{translate('Price')}}</label>
                            <div class="mb-0 font-weight-medium">{{ single_price($product->price) }}</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{translate('Category')}}</label>
                            <div class="mb-0 font-weight-medium">{{ $product->category->getTranslation('name') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{translate('Condition')}}</label>
                            <div class="mb-0 font-weight-medium">
                                <span class="badge badge-inline badge-{{ $product->condition == 'new' ? 'success' : 'info' }}">
                                    {{ ucfirst($product->condition) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{translate('Description')}}</label>
                    <div class="mb-0 font-weight-medium">{{ $product->description }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{translate('Address')}}</label>
                    <div class="mb-0 font-weight-medium">{{ $product->address }}</div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{translate('State')}}</label>
                            <div class="mb-0 font-weight-medium">{{ $product->state->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">{{translate('City')}}</label>
                            <div class="mb-0 font-weight-medium">{{ $product->city->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         <!-- Location -->
         <div class="col-md-12">
            <div class="form-group">
                <label class="form-label">{{translate('Coordinates')}}</label>
                    @if($product->latitude && $product->longitude)
                        @if (get_setting('google_map') == 1)
                            <div class="row">
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
                                    <input type="number" step="any" id="latitude" placeholder="{{ translate('Latitude') }}" name="latitude" class="form-control" value="{{ $product->latitude }}" readonly>
                                    @error('latitude')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <input type="number" step="any" id="longitude" placeholder="{{ translate('Longitude') }}" name="longitude" class="form-control" value="{{$product->longitude }}" readonly>
                                    @error('longitude')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        @else
                        {{ $product->latitude }}, {{ $product->longitude }}
                        @endif
                        @else
                        {{translate('N/A')}}
                    @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Product Images')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($product->mainPhoto)
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <img src="{{ uploaded_asset($product->main_photo) }}"
                                     class="card-img-top" alt="Main Photo" style="height: 200px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <small class="text-muted">{{translate('Main Photo')}}</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    @foreach($product->photos_array as $photo)
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <img src="{{ uploaded_asset($photo) }}"
                                     class="card-img-top" alt="Product Photo" style="height: 200px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <small class="text-muted">{{translate('Additional Photo')}}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if(!$product->mainPhoto && $product->photoUploads->isEmpty())
                        <div class="col-12">
                            <div class="text-center py-4">
                                <i class="las la-image text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted">{{translate('No images available')}}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($product->translations->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Translations')}}</h5>
            </div>
            <div class="card-body">
                @foreach($product->translations as $translation)
                    <div class="border rounded p-3 mb-3">
                        <h6>{{ strtoupper($translation->lang) }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>{{translate('Name')}}:</strong> {{ $translation->name }}
                            </div>
                            <div class="col-md-12 mt-2">
                                <strong>{{translate('Description')}}:</strong> {{ $translation->description }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Customer Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($product->user->avatar_original)
                        <img src="{{ uploaded_asset($product->user->avatar_original) }}"
                             class="rounded-circle size-60px" alt="Customer Avatar">
                    @else
                        <div class="rounded-circle size-60px bg-light d-flex align-items-center justify-content-center mx-auto">
                            <i class="las la-user text-muted" style="font-size: 2rem;"></i>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">{{translate('Name')}}</label>
                    <div class="mb-0 font-weight-medium">{{ $product->user->name }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{translate('Email')}}</label>
                    <div class="mb-0 font-weight-medium">{{ $product->user->email }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{translate('Phone')}}</label>
                    <div class="mb-0 font-weight-medium">{{ $product->user->phone ?? 'N/A' }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{translate('Member Since')}}</label>
                    <div class="mb-0 font-weight-medium">{{ $product->user->created_at->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Moderation Status')}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">{{translate('Current Status')}}</label>
                    <div class="mb-0 font-weight-medium">
                        @if($product->moderation_status == 'pending')
                            <span class="badge badge-inline badge-warning badge-lg">{{translate('Pending Review')}}</span>
                        @elseif($product->moderation_status == 'approved')
                            <span class="badge badge-inline badge-success badge-lg">{{translate('Approved')}}</span>
                        @else
                            <span class="badge badge-inline badge-danger badge-lg">{{translate('Rejected')}}</span>
                        @endif
                    </div>
                </div>

                @if($product->rejection_reason)
                <div class="form-group">
                    <label class="form-label">{{translate('Rejection Reason')}}</label>
                    <div class="mb-0 font-weight-medium">{{ $product->rejection_reason }}</div>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">{{translate('Availability')}}</label>
                    <div class="mb-0 font-weight-medium">
                        <span class="badge badge-inline badge-{{ $product->availability_status == 'available' ? 'success' : 'secondary' }}">
                            {{ ucfirst($product->availability_status) }}
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{translate('Created At')}}</label>
                    <div class="mb-0 font-weight-medium">{{ $product->created_at->format('M d, Y H:i') }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{translate('Last Updated')}}</label>
                    <div class="mb-0 font-weight-medium">{{ $product->updated_at->format('M d, Y H:i') }}</div>
                </div>

                @if($product->moderation_status == 'pending')
                <div class="form-group">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-success" onclick="moderate_product({{ $product->id }}, 'approve')">
                            <i class="las la-check"></i> {{translate('Approve')}}
                        </button>
                        <button type="button" class="btn btn-danger" onclick="show_reject_modal()">
                            <i class="las la-times"></i> {{translate('Reject')}}
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="reject-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{translate('Reject Product')}}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="reject-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{translate('Rejection Reason')}}</label>
                        <textarea class="form-control" name="rejection_reason" rows="4" required
                                  placeholder="{{translate('Please provide a detailed reason for rejection...')}}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-danger">{{translate('Reject Product')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
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

                    long = {{ $product->longitude }};
                    lat = {{ $product->latitude }};


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
<script type="text/javascript">
    function moderate_product(productId, action) {
        $.post('{{ route("admin.customer-products.moderate", $product->id) }}', {
            _token: '{{ csrf_token() }}',
            action: action
        }, function(data) {
            if (data.success) {
                AIZ.plugins.notify('success', data.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', data.message);
            }
        });
    }

    function show_reject_modal() {
        $('#reject-modal').modal('show');
    }

    $('#reject-form').on('submit', function(e) {
        e.preventDefault();
        var reason = $(this).find('[name="rejection_reason"]').val();

        $.post('{{ route("admin.customer-products.moderate", $product->id) }}', {
            _token: '{{ csrf_token() }}',
            action: 'reject',
            rejection_reason: reason
        }, function(data) {
            if (data.success) {
                AIZ.plugins.notify('success', data.message);
                $('#reject-modal').modal('hide');
                location.reload();
            } else {
                AIZ.plugins.notify('danger', data.message);
            }
        });
    });
</script>
@endsection
