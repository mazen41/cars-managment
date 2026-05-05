@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Insurance Deposit Details') }}</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('insurance-deposits.index') }}" class="btn btn-sm btn-light">
                <i class="las la-arrow-left"></i>
                {{ translate('Back to List') }}
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Customer Information -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Customer Information') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="text-muted" width="40%">{{ translate('Customer Name') }}</td>
                            <td class="font-weight-bold">{{ $depositDetails['customer_info']['name'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Email') }}</td>
                            <td>{{ $depositDetails['customer_info']['email'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Phone') }}</td>
                            <td>{{ $depositDetails['customer_info']['phone'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Customer ID') }}</td>
                            <td>
                                <a href="{{ route('customers.details', $depositDetails['customer_info']['id']) }}"
                                   class="text-primary">
                                    #{{ $depositDetails['customer_info']['id'] }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deposit Information -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Deposit Information') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="text-muted" width="40%">{{ translate('Amount') }}</td>
                            <td class="font-weight-bold text-success">
                                {{ single_price($depositDetails['deposit_info']['amount']) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Status') }}</td>
                            <td>
                                @if($depositDetails['deposit_info']['status'] == 'pending')
                                    <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                                @elseif($depositDetails['deposit_info']['status'] == 'paid')
                                    <span class="badge badge-inline badge-success">{{ translate('Paid') }}</span>
                                @elseif($depositDetails['deposit_info']['status'] == 'refunded')
                                    <span class="badge badge-inline badge-info">{{ translate('Refunded') }}</span>
                                @elseif($depositDetails['deposit_info']['status'] == 'cancelled')
                                    <span class="badge badge-inline badge-danger">{{ translate('Cancelled') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Created Date') }}</td>
                            <td>
                                {{ $depositDetails['deposit_info']['created_at']->format('M d, Y') }}
                                <br>
                                <small class="text-muted">{{ $depositDetails['deposit_info']['created_at']->format('h:i A') }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Payment Date') }}</td>
                            <td>
                                @if($depositDetails['deposit_info']['paid_at'])
                                    {{ $depositDetails['deposit_info']['paid_at']->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $depositDetails['deposit_info']['paid_at']->format('h:i A') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details -->
<div class="row mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Payment Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="text-muted">{{ translate('Payment Method') }}</label>
                            <p class="font-weight-medium">{{ $depositDetails['payment_details']['method'] }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="text-muted">{{ translate('Transaction ID') }}</label>
                            <p class="font-weight-medium">{{ $depositDetails['payment_details']['transaction_id'] }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="text-muted">{{ translate('Reference ID') }}</label>
                            <p class="font-weight-medium">{{ $depositDetails['payment_details']['reference_id'] }}</p>
                        </div>
                    </div>
                    @if ($depositDetails['refund_requested'])
                       <div class="col-md-4">
                        <div class="mb-3">
                            <label class="text-muted">{{ translate('Refund requested at') }}</label>
                            <p class="font-weight-medium text-danger">{{ $depositDetails['refund_requested_at'] }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- Actions -->
    @can('manage_insurance_deposits')
    @if($depositDetails['can_update_payment'])
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Actions')}}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary"
                        onclick="updatePaymentStatus('paid')">
                        <i class="las la-check-circle"></i> {{translate('Mark as Paid')}}
                    </button>
                    <button type="button" class="btn btn-danger"
                        onclick="updatePaymentStatus('cancelled')">
                        <i class="las la-times-circle"></i> {{translate('Cancel payment')}}
                    </button>

                </div>
            </div>
        </div>
        @endif
        @endcan
    </div>
</div>

<!-- Refund Information (Conditional) -->
@can('manage_insurance_deposits')
@if($depositDetails['refund_details'])
<div class="row mt-3">
    <div class="col-lg-12">
        <div class="card border-info">
            <div class="card-header bg-soft-info">
                <h5 class="mb-0 h6">
                    <i class="las la-undo"></i>
                    {{ translate('Refund Information') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="text-muted">{{ translate('Refund Date') }}</label>
                            <p class="font-weight-medium">
                                {{ $depositDetails['refund_details']['refunded_at']->format('M d, Y') }}
                                <br>
                                <small class="text-muted">{{ $depositDetails['refund_details']['refunded_at']->format('h:i A') }}</small>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="text-muted">{{ translate('Refund Method') }}</label>
                            <p class="font-weight-medium">{{ $depositDetails['refund_details']['refund_method'] }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="text-muted">{{ translate('Refund Transaction ID') }}</label>
                            <p class="font-weight-medium">{{ $depositDetails['refund_details']['refund_transaction_id'] }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="text-muted">{{ translate('Refund Reference ID') }}</label>
                            <p class="font-weight-medium">{{ $depositDetails['refund_details']['refund_reference_id'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endcan

<!-- Action Buttons -->
@if($depositDetails['can_be_refunded'])
<div class="row mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body text-center">
                <button type="button"
                        class="btn btn-info"
                        onclick="showRefundModal({{ $deposit->id }})">
                    <i class="las la-undo"></i>
                    {{ translate('Process Refund') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Refund Confirmation Modal -->
<div class="modal fade" id="refund-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Confirm Refund') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="refund-form">
                @csrf
                <input type="hidden" name="deposit_id" id="refund-deposit-id">
                <div class="modal-body">
                    <p>{{ translate('Are you sure you want to process a refund for this deposit?') }}</p>
                    <p class="text-muted small">{{ translate('This action will return the deposit amount to the customer and cannot be undone.') }}</p>

                    <div class="alert alert-info">
                        <strong>{{ translate('Amount to be refunded:') }}</strong>
                        {{ single_price($depositDetails['deposit_info']['amount']) }}
                    </div>
                      <div class="form-group" id="transaction-details">
                        <label for="transaction_id">{{ translate('Transaction ID') }}</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" required
                            placeholder="{{ translate('Enter transaction ID') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-info">
                        <span class="refund-btn-text">{{ translate('Process Refund') }}</span>
                        <span class="refund-btn-loading d-none">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ translate('Processing...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    function updatePaymentStatus(status){
        if(confirm('{{ translate("Are you sure you want to update the payment?") }}'))
        $.ajax({
            url: '{{ route('insurance-deposits.update-status') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                'id': {{ $deposit->id }},
                'status': status
            },
            success: function(data) {
                if(data.success){
                    AIZ.plugins.notify('success', data.message);
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            },
            error: function(xhr){
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                AIZ.plugins.notify('danger', message);
            }
        });
    }

    function showRefundModal(depositId) {
        $('#refund-deposit-id').val(depositId);
        $('#refund-modal').modal('show');
    }

    $('#refund-form').on('submit', function(e) {
        e.preventDefault();
        var depositId = $('#refund-deposit-id').val();
        var $submitBtn = $(this).find('button[type="submit"]');
        var transactionId = $("#transaction_id").val();
        // Show loading state
        $submitBtn.prop('disabled', true);
        $('.refund-btn-text').addClass('d-none');
        $('.refund-btn-loading').removeClass('d-none');

        $.ajax({
            url: '{{ route("insurance-deposits.refund", ":id") }}'.replace(':id', depositId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                'transaction_id': transactionId
            },
            success: function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    $('#refund-modal').modal('hide');
                    // Reload page to show updated information
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                    // Reset button state
                    $submitBtn.prop('disabled', false);
                    $('.refund-btn-text').removeClass('d-none');
                    $('.refund-btn-loading').addClass('d-none');
                }
            },
            error: function(xhr) {
                var message = 'An error occurred while processing the refund.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                AIZ.plugins.notify('danger', message);
                // Reset button state
                $submitBtn.prop('disabled', false);
                $('.refund-btn-text').removeClass('d-none');
                $('.refund-btn-loading').addClass('d-none');
            }
        });
    });
</script>
@endsection
