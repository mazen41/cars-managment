<form action="{{ route('seller.shop.update') }}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
    @csrf
    <div class="form-box-content p-3">
        <div class="row mb-3">
            <label class="col-md-2 col-form-label">{{ translate('Facebook') }}</label>
            <div class="col-md-10">
                <input type="text" class="form-control" placeholder="{{ translate('Facebook')}}" name="facebook" value="{{ $shop->facebook }}">
                <small class="text-muted">{{ translate('Insert link with https ') }}</small>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-md-2 col-form-label">{{ translate('Instagram') }}</label>
            <div class="col-md-10">
                <input type="text" class="form-control" placeholder="{{ translate('Instagram')}}" name="instagram" value="{{ $shop->instagram }}">
                <small class="text-muted">{{ translate('Insert link with https ') }}</small>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-md-2 col-form-label">{{ translate('Twitter') }}</label>
            <div class="col-md-10">
                <input type="text" class="form-control" placeholder="{{ translate('Twitter')}}" name="twitter" value="{{ $shop->twitter }}">
                <small class="text-muted">{{ translate('Insert link with https ') }}</small>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-md-2 col-form-label">{{ translate('Google') }}</label>
            <div class="col-md-10">
                <input type="text" class="form-control" placeholder="{{ translate('Google')}}" name="google" value="{{ $shop->google }}">
                <small class="text-muted">{{ translate('Insert link with https ') }}</small>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-md-2 col-form-label">{{ translate('Youtube') }}</label>
            <div class="col-md-10">
                <input type="text" class="form-control" placeholder="{{ translate('Youtube')}}" name="youtube" value="{{ $shop->youtube }}">
                <small class="text-muted">{{ translate('Insert link with https ') }}</small>
            </div>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
    </div>
</form>
