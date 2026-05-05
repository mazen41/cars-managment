<form action="{{ route('seller.shop.update') }}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
    @csrf
    @if (get_setting('google_map') == 1)
        <div class="row mb-3">
            <input id="searchInput" class="controls" type="text" placeholder="{{translate('Enter a location')}}">
            <div id="map"></div>
            <ul id="geoData">
                <li style="display: none;">{{ translate('Full Address') }}: <span id="location"></span></li>
                <li style="display: none;">{{ translate('Postal Code') }}: <span id="postal_code"></span></li>
                <li style="display: none;">{{ translate('Country') }}: <span id="country"></span></li>
                <li style="display: none;">{{ translate('Latitude') }}: <span id="lat"></span></li>
                <li style="display: none;">{{ translate('Longitude') }}: <span id="lon"></span></li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-2" id="">
                <label for="exampleInputuname">{{ translate('Longitude') }}</label>
            </div>
            <div class="col-md-10" id="">
                <input type="text" class="form-control mb-3" id="longitude" name="delivery_pickup_longitude" readonly="" value="{{ $shop->delivery_pickup_longitude }}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-2" id="">
                <label for="exampleInputuname">{{ translate('Latitude') }}</label>
            </div>
            <div class="col-md-10" id="">
                <input type="text" class="form-control mb-3" id="latitude" name="delivery_pickup_latitude" readonly="" value="{{ $shop->delivery_pickup_latitude }}">
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-md-2" id="">
                <label for="exampleInputuname">{{ translate('Longitude') }}</label>
            </div>
            <div class="col-md-10" id="">
                <input type="text" class="form-control mb-3" id="longitude" name="delivery_pickup_longitude" value="{{ $shop->delivery_pickup_longitude }}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-2" id="">
                <label for="exampleInputuname">{{ translate('Latitude') }}</label>
            </div>
            <div class="col-md-10" id="">
                <input type="text" class="form-control mb-3" id="latitude" name="delivery_pickup_latitude" value="{{ $shop->delivery_pickup_latitude }}">
            </div>
        </div>
    @endif
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
    </div>
</form>
