<form action="{{ route('seller.shop.update') }}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
    @csrf
    <div class="row mb-3">
        <label class="col-md-2 col-form-label">{{ translate('Top Banner') }} (1920x360)</label>
        <div class="col-md-10">
            <div class="input-group" data-toggle="aizuploader" data-type="image">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                </div>
                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                <input type="hidden" name="top_banner" value="{{ $shop->top_banner }}" class="selected-files">
            </div>
            <div class="file-preview box sm"></div>
            <small class="text-muted">{{ translate('We had to limit height to maintian consistancy. In some device both side of the banner might be cropped for height limitation.') }}</small>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label">{{ translate('Slider Banners') }} (1500x450)</label>
        <div class="col-md-10">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                </div>
                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                <input type="hidden" name="sliders" value="{{ $shop->sliders }}" class="selected-files">
            </div>
            <div class="file-preview box sm"></div>
            <small class="text-muted">{{ translate('We had to limit height to maintian consistancy. In some device both side of the banner might be cropped for height limitation.') }}</small>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label">{{ translate('Banner Full width 1') }}</label>
        <div class="col-md-10">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                </div>
                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                <input type="hidden" name="banner_full_width_1" value="{{ $shop->banner_full_width_1 }}" class="selected-files">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label">{{ translate('Banners half width') }} ({{ translate('2 Equal Banners') }})</label>
        <div class="col-md-10">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                </div>
                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                <input type="hidden" name="banners_half_width" value="{{ $shop->banners_half_width }}" class="selected-files">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label">{{ translate('Banner Full width 2') }}</label>
        <div class="col-md-10">
            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                </div>
                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                <input type="hidden" name="banner_full_width_2" value="{{ $shop->banner_full_width_2 }}" class="selected-files">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
    </div>
</form>
