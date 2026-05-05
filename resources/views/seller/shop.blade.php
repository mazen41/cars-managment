@extends('seller.layouts.app')
<style>
    .nav-pills .nav-link {
        border-radius: 0;
        margin-bottom: 5px;
        padding: 15px;
        transition: all 0.3s ease;
        background-color: #f1fafd;
    }

    .nav-pills .nav-link:hover {
        background-color: #007bff;
        color: white;
    }

    .nav-pills .nav-link.active {
        background-color: #007bff;
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card {
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .tab-content {
        padding: 0 15px;
    }

    #map {
        height: 400px;
        width: 100%;
        margin-bottom: 20px;
    }

    .controls {
        margin-top: 10px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        width: 100%;
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .nav-pills {
            margin-bottom: 20px;
        }
    }
</style>
@section('panel_content')
<div class="row">
    <!-- Side Navigation Tabs -->
    <div class="col-md-3">
        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <a class="nav-link" id="basic-info-tab" data-toggle="pill" href="#basic-info" role="tab">
                {{ translate('Basic Info') }}
            </a>
            <a class="nav-link" id="payment-settings-tab" data-toggle="pill" href="#payment-settings" role="tab">
                {{ translate('Payment Settings') }}
            </a>
            @if (addon_is_activated('delivery_boy'))
            <a class="nav-link" id="delivery-pickup-tab" data-toggle="pill" href="#delivery-pickup" role="tab">
                {{ translate('Delivery Boy Pickup Point') }}
            </a>
            @endif
            <a class="nav-link" id="banner-settings-tab" data-toggle="pill" href="#banner-settings" role="tab">
                {{ translate('Banner Settings') }}
            </a>
            <a class="nav-link" id="social-media-tab" data-toggle="pill" href="#social-media" role="tab">
                {{ translate('Social Media Link') }}
            </a>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="col-md-9">
        <div class="tab-content" id="v-pills-tabContent">
            <!-- Basic Info Tab -->
            <div class="tab-pane fade" id="basic-info" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Basic Info') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! $shop->getBasicInfoForm() !!}
                    </div>
                </div>
            </div>
            <!-- Payment Settings Tab -->
            <div class="tab-pane fade" id="payment-settings" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Payment Receive Setting') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! $shop->getPaymentSettingsForm() !!}
                    </div>
                </div>
            </div>
            <!-- Delivery Boy Pickup Point Tab -->
            @if (addon_is_activated('delivery_boy'))
            <div class="tab-pane fade" id="delivery-pickup" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Delivery Boy Pickup Point') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! $shop->getDeliveryPickupForm() !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Banner Settings Tab -->
            <div class="tab-pane fade" id="banner-settings" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Banner Settings') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! $shop->getBannerSettingsForm() !!}
                    </div>
                </div>
            </div>

            <!-- Social Media Tab -->
            <div class="tab-pane fade" id="social-media" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{ translate('Social Media Link') }}</h5>
                    </div>
                    <div class="card-body">
                        {!! $shop->getSocialMediaForm() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

@if (addon_is_activated('delivery_boy') && get_setting('google_map') == 1)

<script>
    $(document).ready(function() {
    // Update hash when tab is shown
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        let hash = $(e.target).attr('href');
        history.pushState(null, null, hash);
    });

    // Activate tab based on hash or default to first
    function activateInitialTab() {
        let hash = window.location.hash;
        if (hash) {
            const tab = $(`a[href="${hash}"]`);
            if (tab.length) {
                tab.tab('show');
            } else {
                // If hash doesn't match any tab, show first tab
                $('.nav-pills a:first').tab('show');
            }
        } else {
            // No hash, show first tab
            $('.nav-pills a:first').tab('show');
        }
    }

    activateInitialTab();
    });
    function toggleWalletSection(checkbox) {
        const walletSection = document.getElementById('wallet-section');
        walletSection.style.display = checkbox.checked ? 'block' : 'none';

        // If unchecked, clear all wallets
        if (!checkbox.checked) {
            document.getElementById('wallet-container').innerHTML = '';
        }
    }

    function addWallet() {
        const template = document.getElementById('wallet-template');
        const container = document.getElementById('wallet-container');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
        updateWalletValidation();
    }

    function removeWallet(button) {
        button.closest('.wallet-entry').remove();
        updateWalletValidation();
    }

    function updateWalletValidation() {
        const walletEntries = document.querySelectorAll('.wallet-entry');
        const errorDiv = document.getElementById('wallet-error');
        const bankPaymentEnabled = document.querySelector('input[name="bank_payment_status"]').checked;

        if (bankPaymentEnabled && walletEntries.length === 0) {
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
        }
    }

    // Form validation before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const bankPaymentEnabled = document.querySelector('input[name="bank_payment_status"]').checked;
        const walletEntries = document.querySelectorAll('.wallet-entry');

        if (bankPaymentEnabled && walletEntries.length === 0) {
            e.preventDefault();
            document.getElementById('wallet-error').style.display = 'block';
            return false;
        }
    });

    // Initialize validation state
    document.addEventListener('DOMContentLoaded', function() {
        updateWalletValidation();
    });

        function initialize(id_format = '') {
            let default_longtitude = '';
            let default_latitude = '';
            @if (get_setting('google_map_longtitude') != '' && get_setting('google_map_longtitude') != '')
                default_longtitude = {{ get_setting('google_map_longtitude') }};
                default_latitude = {{ get_setting('google_map_latitude') }};
            @endif

            var lat = -33.8688;
            var long = 151.2195;

            if (document.getElementById('latitude').value != '' &&
                document.getElementById('longitude').value != '') {
                lat = parseFloat(document.getElementById('latitude').value);
                long = parseFloat(document.getElementById('longitude').value);
            } else if (default_longtitude != '' &&
                default_latitude != '') {
                lat = default_latitude;
                long = default_longtitude;
            }


            var map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: lat,
                    lng: long
                },
                zoom: 13
            });

            var myLatlng = new google.maps.LatLng(lat, long);

            var input = document.getElementById(id_format + 'searchInput');
            // console.log(input);
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
                /*
                marker.setIcon(({
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
                }));
                */
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
