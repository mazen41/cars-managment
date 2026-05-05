<form action="{{ route('seller.shop.update.payment-settings')}}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
    @csrf

    <div class="row">
        <label class="col-md-3 col-form-label">{{ translate('Receive Cash Payment?') }}</label>
        <div class="col-md-9">
            <label class="aiz-switch aiz-switch-success mb-3">
                <input value="1" name="cash_on_delivery_status" type="checkbox" @if ($shop->cash_on_delivery_status == 1) checked @endif>
                <span class="slider round"></span>
            </label>
        </div>
    </div>

    <div class="row">
        <label class="col-md-3 col-form-label">{{ translate('Transfer Payment') }}</label>
        <div class="col-md-9">
            <label class="aiz-switch aiz-switch-success mb-3">
                <input value="1" name="bank_payment_status" type="checkbox"
                    @if ($shop->bank_payment_status == 1) checked @endif
                    onchange="toggleWalletSection(this)">
                <span class="slider round"></span>
            </label>
        </div>
    </div>

    <div id="wallet-section" class="card" style="display: {{ $shop->bank_payment_status ? 'block' : 'none' }};">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Wallets') }}</h5>
            <button type="button" class="btn btn-sm btn-primary" onclick="addWallet()">
                {{ translate('Add New Wallet') }}
            </button>
        </div>
        <div class="card-body">
            <div id="wallet-container">
                @foreach($shop->wallets as $wallet)
                <div class="wallet-entry border p-3 mb-3">
                    <div class="row">
                        <div class="col-md-11">
                            <div class="form-group">
                                <input type="hidden" name="wallet_ids[]" value="{{ $wallet->id }}">
                                <label>{{ translate('Wallet/Bank Name') }}<span class="text-danger">*</span></label>
                                <input type="text" name="wallet_names[]" class="form-control" value="{{ $wallet->wallet_name }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{ translate('Account Holder Name') }}<span class="text-danger">*</span></label>
                                <input type="text" name="account_holder_names[]" class="form-control" value="{{ $wallet->account_holder_name }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{ translate('Account Number') }}<span class="text-danger">*</span></label>
                                <input type="text" name="account_numbers[]" class="form-control" value="{{ $wallet->account_number }}" required>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-icon" onclick="removeWallet(this)">
                                <i class="las la-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div id="wallet-error" class="text-danger" style="display: none;">
                {{ translate('At least one wallet is required when transfer payment is enabled') }}
            </div>
        </div>
    </div>

    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
    </div>
</form>

<template id="wallet-template">
    <div class="wallet-entry border p-3 mb-3">
        <div class="row">
            <div class="col-md-11">
                <div class="form-group">
                    <input type="hidden" name="wallet_ids[]" value="">
                    <label>{{ translate('Wallet/Bank Name') }}<span class="text-danger">*</span></label>
                    <input type="text" name="wallet_names[]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>{{ translate('Account Holder Name') }}<span class="text-danger">*</span></label>
                    <input type="text" name="account_holder_names[]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>{{ translate('Account Number') }}<span class="text-danger">*</span></label>
                    <input type="text" name="account_numbers[]" class="form-control" required>
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-icon" onclick="removeWallet(this)">
                    <i class="las la-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>
