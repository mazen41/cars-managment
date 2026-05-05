@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Payment History') }} - {{ $carInspector->full_name }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.car-inspectors.show', $carInspector->id) }}" class="btn btn-circle btn-light">
                <span>{{ translate('Back to Inspector') }}</span>
            </a>
            @can('manage_car_inspector_payments')
                @if($carInspector->admin_to_pay > 0)
                    <a href="{{ route('admin.car-inspectors.show-payment-form', $carInspector->id) }}" class="btn btn-circle btn-success">
                        <span>{{ translate('Make Payment') }}</span>
                    </a>
                @endif
            @endcan
        </div>
    </div>
</div>

<!-- Inspector Summary Card -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <img src="{{ $carInspector->image_url }}" alt="{{ $carInspector->full_name }}" class="rounded-circle size-60px">
            </div>
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h5 class="fw-700 mb-0 text-success">{{ format_price($carInspector->paymentHistory()->earnings()->completed()->sum('amount')) }}</h5>
                            <small class="text-muted">{{ translate('Total Earnings') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h5 class="fw-700 mb-0 text-info">{{ format_price($carInspector->paymentHistory()->payments()->completed()->sum('amount')) }}</h5>
                            <small class="text-muted">{{ translate('Total Paid') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h5 class="fw-700 mb-0 text-warning">{{ format_price($carInspector->admin_to_pay) }}</h5>
                            <small class="text-muted">{{ translate('Balance Owed') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h5 class="fw-700 mb-0">{{ $carInspector->paymentHistory()->count() }}</h5>
                            <small class="text-muted">{{ translate('Total Transactions') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Table -->
<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">{{ translate('Payment Transactions') }}</h5>
        </div>
        <div class="col-md-2">
            <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="type_filter" onchange="filterPayments()">
                <option value="">{{ translate('All Types') }}</option>
                <option value="earning" @if (request('type') == 'earning') selected @endif>{{ translate('Earnings') }}</option>
                <option value="payment" @if (request('type') == 'payment') selected @endif>{{ translate('Payments') }}</option>
                <option value="adjustment" @if (request('type') == 'adjustment') selected @endif>{{ translate('Adjustments') }}</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status_filter" onchange="filterPayments()">
                <option value="">{{ translate('All Status') }}</option>
                <option value="pending" @if (request('status') == 'pending') selected @endif>{{ translate('Pending') }}</option>
                <option value="completed" @if (request('status') == 'completed') selected @endif>{{ translate('Completed') }}</option>
                <option value="failed" @if (request('status') == 'failed') selected @endif>{{ translate('Failed') }}</option>
                <option value="cancelled" @if (request('status') == 'cancelled') selected @endif>{{ translate('Cancelled') }}</option>
            </select>
        </div>
    </div>

    <div class="card-body">
        @if($payments->count() > 0)
            <div class="table-responsive">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Description') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Payment Method') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Processed By') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>
                                    <div>{{ $payment->created_at->format('M d, Y') }}</div>
                                    <div class="text-muted small">{{ $payment->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-inline {{ $payment->type_badge_class }}">
                                        {{ $payment->type_display }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $payment->description ?? translate('N/A') }}</div>
                                    @if($payment->transaction_reference)
                                        <div class="text-muted small">
                                            {{ translate('Ref') }}: {{ $payment->transaction_reference }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-700 {{ $payment->isEarning() ? 'text-success' : ($payment->isPayment() ? 'text-primary' : 'text-info') }}">
                                        {{ $payment->isEarning() ? '+' : '-' }}{{ $payment->formatted_amount }}
                                    </span>
                                </td>
                                <td>
                                    {{ $payment->payment_method ?? translate('N/A') }}
                                    @if($payment->payment_details)
                                        <div class="text-muted small">
                                            @if(is_array($payment->payment_details))
                                                @foreach($payment->payment_details as $key => $value)
                                                    <div><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</div>
                                                @endforeach
                                            @else
                                                {{ $payment->payment_details }}
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-inline {{ $payment->status_badge_class }}">
                                        {{ $payment->status_display }}
                                    </span>
                                </td>
                                <td>
                                    @if($payment->processedBy)
                                        <div>{{ $payment->processedBy->name }}</div>
                                        <div class="text-muted small">{{ $payment->processedBy->user_type }}</div>
                                    @else
                                        <span class="text-muted">{{ translate('System') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-icon btn-circle btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="las la-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="showPaymentDetails({{ $payment->id }})">
                                                {{ translate('View Details') }}
                                            </a>
                                            @if($payment->isPending() && auth()->user()->can('manage_car_inspector_payments'))
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-success" href="javascript:void(0)" onclick="updatePaymentStatus({{ $payment->id }}, 'completed')">
                                                    {{ translate('Mark as Completed') }}
                                                </a>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="updatePaymentStatus({{ $payment->id }}, 'failed')">
                                                    {{ translate('Mark as Failed') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="aiz-pagination">
                {{ $payments->appends(request()->input())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="las la-money-bill-wave display-3 text-muted"></i>
                <h4 class="mt-3">{{ translate('No Payment History') }}</h4>
                <p class="text-muted">{{ translate('No payment transactions found for this inspector.') }}</p>
            </div>
        @endif
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="payment-details-modal" tabindex="-1" role="dialog" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentDetailsModalLabel">{{ translate('Payment Details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="payment-details-content">
                    <!-- Payment details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    function filterPayments() {
        var url = new URL(window.location.href);
        var type = document.getElementById('type_filter').value;
        var status = document.getElementById('status_filter').value;

        if (type) {
            url.searchParams.set('type', type);
        } else {
            url.searchParams.delete('type');
        }

        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }

        window.location.href = url.toString();
    }

    function showPaymentDetails(paymentId) {
        $('#payment-details-content').html('<div class="text-center"><i class="las la-spinner la-spin"></i> {{ translate("Loading...") }}</div>');
        $('#payment-details-modal').modal('show');

        $.ajax({
            url: "{{ route('admin.car-inspectors.payment-details', ':id') }}".replace(':id', paymentId),
            type: 'GET',
            success: function(response) {
                $('#payment-details-content').html(response.html);
            },
            error: function() {
                $('#payment-details-content').html('<div class="alert alert-danger">{{ translate("Error loading payment details") }}</div>');
            }
        });
    }

    function updatePaymentStatus(paymentId, status) {
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
@endsection
