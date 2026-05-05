<div class="row">
    <div class="col-md-6">
        <h6 class="mb-3">{{ translate('Payment Information') }}</h6>
        <table class="table table-borderless table-sm">
            <tr>
                <td><strong>{{ translate('Transaction ID') }}:</strong></td>
                <td>#{{ $payment->id }}</td>
            </tr>
            <tr>
                <td><strong>{{ translate('Date') }}:</strong></td>
                <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
            </tr>
            <tr>
                <td><strong>{{ translate('Type') }}:</strong></td>
                <td>
                    <span class="badge badge-inline {{ $payment->type_badge_class }}">
                        {{ $payment->type_display }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>{{ translate('Amount') }}:</strong></td>
                <td>
                    <span class="h5 {{ $payment->isEarning() ? 'text-success' : ($payment->isPayment() ? 'text-primary' : 'text-info') }}">
                        {{ $payment->isEarning() ? '+' : '-' }}{{ $payment->formatted_amount }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>{{ translate('Status') }}:</strong></td>
                <td>
                    <span class="badge badge-inline {{ $payment->status_badge_class }}">
                        {{ $payment->status_display }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>{{ translate('Payment Method') }}:</strong></td>
                <td>{{ $payment->payment_method ?? translate('N/A') }}</td>
            </tr>
            @if($payment->transaction_reference)
                <tr>
                    <td><strong>{{ translate('Reference') }}:</strong></td>
                    <td>{{ $payment->transaction_reference }}</td>
                </tr>
            @endif
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="mb-3">{{ translate('Inspector Information') }}</h6>
        <div class="d-flex align-items-center mb-3">
            <img src="{{ $payment->carInspector->image_url }}" alt="{{ $payment->carInspector->full_name }}" class="rounded-circle size-50px mr-3">
            <div>
                <h6 class="mb-0">{{ $payment->carInspector->full_name }}</h6>
                <small class="text-muted">{{ $payment->carInspector->shop_name }}</small>
            </div>
        </div>

        <table class="table table-borderless table-sm">
            <tr>
                <td><strong>{{ translate('Current Balance') }}:</strong></td>
                <td class="text-warning">{{ format_price($payment->carInspector->admin_to_pay) }}</td>
            </tr>
            <tr>
                <td><strong>{{ translate('Processed By') }}:</strong></td>
                <td>
                    @if($payment->processedBy)
                        {{ $payment->processedBy->name }}
                        <br><small class="text-muted">{{ ucfirst($payment->processedBy->user_type) }}</small>
                    @else
                        <span class="text-muted">{{ translate('System') }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

@if($payment->description)
    <hr>
    <div class="row">
        <div class="col-12">
            <h6>{{ translate('Description') }}</h6>
            <p class="text-muted">{{ $payment->description }}</p>
        </div>
    </div>
@endif

@if($payment->payment_details && is_array($payment->payment_details))
    <hr>
    <div class="row">
        <div class="col-12">
            <h6>{{ translate('Payment Details') }}</h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    @foreach($payment->payment_details as $key => $value)
                        <tr>
                            <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></td>
                            <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endif

@if($payment->notes)
    <hr>
    <div class="row">
        <div class="col-12">
            <h6>{{ translate('Notes') }}</h6>
            <div class="alert alert-light">
                {{ $payment->notes }}
            </div>
        </div>
    </div>
@endif

<hr>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                {{ translate('Last updated') }}: {{ $payment->updated_at->format('M d, Y h:i A') }}
            </small>
            @if($payment->isPending() && auth()->user()->can('manage_car_inspector_payments'))
                <div>
                    <button type="button" class="btn btn-sm btn-success" onclick="updatePaymentStatusFromModal({{ $payment->id }}, 'completed')">
                        {{ translate('Mark as Completed') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="updatePaymentStatusFromModal({{ $payment->id }}, 'failed')">
                        {{ translate('Mark as Failed') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function updatePaymentStatusFromModal(paymentId, status) {
    if (confirm('{{ translate("Are you sure you want to update the payment status?") }}')) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('admin.car-inspectors.update-payment-status', ':id') }}".replace(':id', paymentId),
            type: 'POST',
            data: {
                status: status
            },
            success: function(response) {
                if (response.success) {
                    AIZ.plugins.notify('success', response.message);
                    $('#payment-details-modal').modal('hide');
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', response.message);
                }
            },
            error: function() {
                AIZ.plugins.notify('danger', '{{ translate("Error updating payment status") }}');
            }
        });
    }
}
</script>
