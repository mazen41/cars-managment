<form action="{{ route('seller.shop.update') }}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
    @csrf
    <div class="row">
        <label class="col-md-2 col-form-label">{{ translate('Shop Name') }}<span class="text-danger text-danger">*</span></label>
        <div class="col-md-10">
            <input type="text" class="form-control mb-3" placeholder="{{ translate('Shop Name')}}" name="name" value="{{ $shop->name }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-md-2 col-form-label">{{ translate('Shop Logo') }}</label>
        <div class="col-md-10">
            <div class="input-group" data-toggle="aizuploader" data-type="image">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                </div>
                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                <input type="hidden" name="logo" value="{{ $shop->logo }}" class="selected-files">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <label>{{ translate('Shop Phone') }} <span class="text-danger">*</span></label>
        </div>
        <div class="col-md-10">
            <input type="text" class="form-control mb-3" placeholder="{{ translate('Phone')}}" name="phone" value="{{ $shop->phone }}" required>
        </div>
    </div>
    <div class="row">
        <label class="col-md-2 col-form-label">{{ translate('Shop Address') }} <span class="text-danger text-danger">*</span></label>
        <div class="col-md-10">
            <input type="text" class="form-control mb-3" placeholder="{{ translate('Address')}}" name="address" value="{{ $shop->address }}" required>
        </div>
    </div>
    <div class="row">
        <label class="col-md-2 col-form-label">{{ translate('Commercial Register') }} <span class="text-danger text-danger">*</span></label>
        <div class="col-md-10">
            <input type="text" class="form-control mb-3" placeholder="{{ translate('Commercial Register')}}" name="commercial_register" value="{{ $shop->commercial_register }}" required>
        </div>
    </div>
    <div class="row">
        <label class="col-md-2 col-form-label">{{ translate('Tax Number') }} <span class="text-danger text-danger">*</span></label>
        <div class="col-md-10">
            <input type="text" class="form-control mb-3" placeholder="{{ translate('Tax number')}}" name="tax_number" value="{{ $shop->tax_number }}" required>
        </div>
    </div>
    @if (get_setting('shipping_type') == 'seller_wise_shipping')
        <div class="row">
            <div class="col-md-2">
                <label>{{ translate('Shipping Cost')}} <span class="text-danger">*</span></label>
            </div>
            <div class="col-md-10">
                <input type="number" lang="en" min="0" class="form-control mb-3" placeholder="{{ translate('Shipping Cost')}}" name="shipping_cost" value="{{ $shop->shipping_cost }}" required>
            </div>
        </div>
    @endif
    <div class="row">
        <label class="col-md-2 col-form-label">{{ translate('Meta Title') }}<span class="text-danger text-danger">*</span></label>
        <div class="col-md-10">
            <input type="text" class="form-control mb-3" placeholder="{{ translate('Meta Title')}}" name="meta_title" value="{{ $shop->meta_title }}" required>
        </div>
    </div>
    <div class="row">
        <label class="col-md-2 col-form-label">{{ translate('Meta Description') }}<span class="text-danger text-danger">*</span></label>
        <div class="col-md-10">
            <textarea name="meta_description" rows="3" class="form-control mb-3" required>{{ $shop->meta_description }}</textarea>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
    </div>
</form>
