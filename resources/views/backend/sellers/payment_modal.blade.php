<form action="{{ route('commissions.pay_to_seller') }}" method="POST">
    @csrf
    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
    <div class="modal-header">
        <h5 class="modal-title h6">{{translate('Pay to seller')}}</h5>
        <button type="button" class="close" data-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <table class="table table-striped table-bordered">
            <tbody>
                <tr>
                    @if($shop->admin_to_pay >= 0)
                        <td>{{ translate('Due to seller') }}</td>
                        <td>{{ single_price($shop->admin_to_pay) }}</td>
                    @else
                        <td>{{ translate('Due to admin') }}</td>
                        <td>{{ single_price(abs($shop->admin_to_pay)) }}</td>
                    @endif
                </tr>
                @if($shop->bank_payment_status == 1 && $shop->wallets->count() > 0)
                    <tr>
                        <td colspan="2">
                            <strong>{{ translate('Available Wallets') }}</strong>
                        </td>
                    </tr>
                    @foreach($shop->wallets as $wallet)
                        <tr>
                            <td colspan="2">
                                <div class="wallet-details">
                                    <strong>{{ translate('Wallet') }} #{{ $loop->iteration }}</strong><br>
                                    {{ translate('Name') }}: {{ $wallet->wallet_name }}<br>
                                    {{ translate('Account Holder') }}: {{ $wallet->account_holder_name }}<br>
                                    {{ translate('Account Number') }}: {{ $wallet->account_number }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        @if ($shop->admin_to_pay > 0)
            <div class="form-group row">
                <label class="col-md-3 col-from-label" for="amount">{{translate('Amount')}}</label>
                <div class="col-md-9">
                    <input type="number" lang="en" min="0" step="0.01" name="amount" id="amount" value="{{ $shop->admin_to_pay }}" class="form-control" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-from-label" for="payment_option">{{translate('Payment Method')}}</label>
                <div class="col-md-9">
                    <select name="payment_option" id="payment_option" class="form-control aiz-selectpicker" required>
                        @if ($shop->bank_payment_status || $shop->cash_on_delivery_status)
                            <option value="">{{translate('Select Payment Method')}}</option>
                        @else
                            <option value="">{{translate('Shop has not set their payment details')}}</option>
                        @endif
                        @if($shop->cash_on_delivery_status == 1)
                            <option value="cash">{{translate('Cash')}}</option>
                        @endif
                        @if($shop->bank_payment_status == 1 && $shop->wallets->count() > 0)
                            <option value="bank_payment">{{translate('Bank Payment')}}</option>
                        @endif
                    </select>
                </div>
            </div>

            @if($shop->bank_payment_status == 1 && $shop->wallets->count() > 0)
                <div id="wallet_selection_section" style="display: none;">
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label" for="wallet_id">{{translate('Select Wallet')}}</label>
                        <div class="col-md-9">
                            <select name="wallet_id" id="wallet_id" class="form-control aiz-selectpicker">
                                @foreach($shop->wallets as $wallet)
                                    <option value="{{ $wallet->id }}">
                                        {{ $wallet->wallet_name }} - {{ $wallet->account_holder_name }} ({{ $wallet->account_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <div class="form-group row" id="txn_div">
                <label class="col-md-3 col-from-label" for="txn_code">{{translate('Txn Code')}}</label>
                <div class="col-md-9">
                    <input type="text" name="txn_code" id="txn_code" class="form-control">
                </div>
            </div>
        @else
            <div class="form-group row">
                <label class="col-md-3 col-from-label" for="amount">{{translate('Amount')}}</label>
                <div class="col-md-9">
                    <input type="number" lang="en" min="0" step="0.01" name="amount" id="amount" value="{{ abs($shop->admin_to_pay) }}" class="form-control" required>
                </div>
            </div>
            <div class="form-group row" id="txn_div">
                <label class="col-md-3 col-from-label" for="txn_code">{{translate('Txn Code')}}</label>
                <div class="col-md-9">
                    <input type="text" name="txn_code" id="txn_code" class="form-control">
                </div>
            </div>
        @endif
    </div>
    <div class="modal-footer">
        @if ($shop->admin_to_pay > 0)
            <button type="submit" class="btn btn-primary">{{translate('Pay')}}</button>
        {{-- @else
            <button type="submit" class="btn btn-primary">{{translate('Clear due')}}</button> --}}
        @endif
        <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
    </div>
</form>

<script>
$(document).ready(function(){
    $('#payment_option').on('change', function() {
        if (this.value == 'bank_payment') {
            $("#txn_div").show();
            $("#wallet_selection_section").show();
        } else if (this.value == 'cash') {
            $("#txn_div").show();
            $("#wallet_selection_section").hide();
        } else {
            $("#txn_div").hide();
            $("#wallet_selection_section").hide();
        }
    });
    $("#txn_div").hide();
    $("#wallet_selection_section").hide();
    AIZ.plugins.bootstrapSelect('refresh');
});
</script>
