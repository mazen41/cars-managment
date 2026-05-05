@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Make Payment') }} - {{ $carInspector->full_name }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.car-inspectors.show', $carInspector->id) }}" class="btn btn-circle btn-light">
                <span>{{ translate('Back to Inspector') }}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Inspector Summary -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <img src="{{ $carInspector->image_url }}" alt="{{ $carInspector->full_name }}" class="rounded-circle size-100px">
                </div>
                <h4 class="mb-1">{{ $carInspector->full_name }}</h4>
                <p class="text-muted">{{ $carInspector->shop_name }}</p>

                <div class="alert alert-warning">
                    <h6 class="mb-2">{{ translate('Amount Owed') }}:</h6>
                    <h3 class="mb-0 text-warning">{{ format_price($carInspector->admin_to_pay) }}</h3>
                </div>

                <hr>

                <div class="text-left">
                    <div class="row mb-2">
                        <div class="col-sm-6"><strong>{{ translate('Total Earnings') }}:</strong></div>
                        <div class="col-sm-6">{{ format_price($carInspector->paymentHistory()->earnings()->completed()->sum('amount')) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-6"><strong>{{ translate('Total Paid') }}:</strong></div>
                        <div class="col-sm-6">{{ format_price($carInspector->paymentHistory()->payments()->completed()->sum('amount')) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-6"><strong>{{ translate('Inspections') }}:</strong></div>
                        <div class="col-sm-6">{{ $carInspector->inspections()->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Payment Information') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.car-inspectors.make-payment', $carInspector->id) }}" method="POST">
                    @csrf

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="amount">{{ translate('Payment Amount') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ get_system_default_currency()->symbol ?? '$' }}</span>
                                </div>
                                <input type="number" step="0.01" min="0.01" max="{{ $carInspector->admin_to_pay }}" placeholder="{{ translate('Enter amount') }}" id="amount" name="amount" class="form-control" value="{{ old('amount') }}" required>
                            </div>
                            <small class="form-text text-muted">
                                {{ translate('Maximum amount') }}: {{ format_price($carInspector->admin_to_pay) }}
                            </small>
                            @error('amount')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="payment_method">{{ translate('Payment Method') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select name="payment_method" id="payment_method" class="form-control aiz-selectpicker" required>
                                <option value="">{{ translate('Select Payment Method') }}</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>{{ translate('Bank Transfer') }}</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>{{ translate('Cash') }}</option>
                                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>{{ translate('Other') }}</option>
                            </select>
                            @error('payment_method')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Payment Details Section -->
                    <div id="payment-details-section" style="display: none;">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Payment Details') }}</label>
                            <div class="col-md-9">
                                <!-- Bank Transfer Details -->
                                <div id="bank-transfer-details" class="payment-details" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" name="payment_details[account_name]" class="form-control mb-2" placeholder="{{ translate('Account Name') }}" value="{{ old('payment_details.account_name') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="payment_details[account_number]" class="form-control mb-2" placeholder="{{ translate('Account Number') }}" value="{{ old('payment_details.account_number') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="payment_details[bank_name]" class="form-control mb-2" placeholder="{{ translate('Bank Name') }}" value="{{ old('payment_details.bank_name') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Other Payment Details -->
                                <div id="other-details" class="payment-details" style="display: none;">
                                    <textarea name="payment_details[details]" class="form-control" rows="3" placeholder="{{ translate('Payment details') }}">{{ old('payment_details.details') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="transaction_reference">{{ translate('Transaction Reference') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{ translate('Transaction Reference') }}" id="transaction_reference" name="transaction_reference" class="form-control" value="{{ old('transaction_reference') }}" required>
                            <small class="form-text text-muted">{{ translate('Reference number, transaction ID, or any identifier') }} </small>
                            @error('transaction_reference')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="description">{{ translate('Description') }}</label>
                        <div class="col-md-9">
                            <textarea placeholder="{{ translate('Payment description (Optional)') }}" id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-9 offset-md-3">
                            <div class="alert alert-info">
                                <i class="las la-info-circle"></i>
                                <strong>{{ translate('Important') }}:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>{{ translate('This action will reduce the inspector\'s balance owed.') }}</li>
                                    <li>{{ translate('Make sure to verify payment details before proceeding.') }}</li>
                                    <li>{{ translate('A record of this transaction will be saved in the payment history.') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="button" class="btn btn-secondary mr-2" onclick="history.back()">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-success" onclick="return confirm('{{ translate('Are you sure you want to process this payment?') }}')">
                            <i class="las la-money-bill-wave"></i>
                            {{ translate('Process Payment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Amount Buttons -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Quick Payment Options') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary btn-block" onclick="setAmount({{ $carInspector->admin_to_pay * 0.25 }})">
                            {{ translate('25%') }}<br>
                            <small>{{ format_price($carInspector->admin_to_pay * 0.25) }}</small>
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary btn-block" onclick="setAmount({{ $carInspector->admin_to_pay * 0.5 }})">
                            {{ translate('50%') }}<br>
                            <small>{{ format_price($carInspector->admin_to_pay * 0.5) }}</small>
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary btn-block" onclick="setAmount({{ $carInspector->admin_to_pay * 0.75 }})">
                            {{ translate('75%') }}<br>
                            <small>{{ format_price($carInspector->admin_to_pay * 0.75) }}</small>
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-success btn-block" onclick="setAmount({{ $carInspector->admin_to_pay }})">
                            {{ translate('Full Amount') }}<br>
                            <small>{{ format_price($carInspector->admin_to_pay) }}</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        // Handle payment method change
        $('#payment_method').on('change', function() {
            var method = $(this).val();
            var detailsSection = $('#payment-details-section');

            // Hide all payment details
            $('.payment-details').hide();

            if (method && method !== 'cash') {
                detailsSection.show();

                // Show relevant payment details
                 let bankTransferDetails = $('#bank-transfer-details');
                switch(method) {
                    case 'bank_transfer':

                        bankTransferDetails.show();
                        bankTransferDetails.find('input').attr('required', 'required');
                        bankTransferDetails.find('input').val('');
                        $('input[name="payment_details[details]"]').removeAttr('required');
                        break;
                    case 'other':
                        $('textarea[name="payment_details[details]"]').attr('required', 'required');
                        bankTransferDetails.find('input').removeAttr('required');
                        $('#other-details').show();
                        break;
                    default:
                         $('input[name="payment_details[details]"]').removeAttr('required');
                        bankTransferDetails.find('input').removeAttr('required');
                }
            } else {
                detailsSection.hide();
            }
        });

        // Trigger change event if payment method is already selected
        if ($('#payment_method').val()) {
            $('#payment_method').trigger('change');
        }
    });

    function setAmount(amount) {
        $('#amount').val(parseFloat(amount).toFixed(2));
    }

    // Validate amount on input
    $('#amount').on('input', function() {
        var amount = parseFloat($(this).val());
        var maxAmount = parseFloat({{ $carInspector->admin_to_pay }});

        if (amount > maxAmount) {
            $(this).val(maxAmount.toFixed(2));
            AIZ.plugins.notify('warning', '{{ translate("Amount cannot exceed the owed amount") }}');
        }
    });
</script>
@endsection
