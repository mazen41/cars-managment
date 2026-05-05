@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Invoice Details')}} #{{ $auctionInvoice->id }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.auction-invoices.index') }}" class="btn btn-circle btn-light">
                <span>{{translate('Back to List')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Invoice Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Invoice Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{translate('Invoice ID')}}:</td>
                                <td>#{{ $auctionInvoice->id }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{translate('Type')}}:</td>
                                <td>
                                    <span class="badge badge-inline
                                        @if($auctionInvoice->invoice_type == 'buyer_payment') badge-info
                                        @else badge-warning @endif">
                                        {{ $auctionInvoice->invoice_type == 'buyer_payment' ? translate('Buyer Payment')
                                        : translate('Seller Payout') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{translate('Status')}}:</td>
                                <td>
                                    <span class="badge badge-inline {{ $auctionInvoice->status_badge }}">
                                        {{ translate($auctionInvoice->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{translate('Created Date')}}:</td>
                                <td>{{ $auctionInvoice->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{translate('Amount')}}:</td>
                                <td class="fw-600">{{ format_price($auctionInvoice->amount) }}</td>
                            </tr>
                            @if($auctionInvoice->invoice_type == 'seller_payout')
                            <tr>
                                <td class="w-50 fw-600">{{translate('Commission')}}:</td>
                                <td>{{ format_price($auctionInvoice->commission_amount ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{translate('Net Amount')}}:</td>
                                <td class="fw-600">{{ format_price($auctionInvoice->net_amount) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="w-50 fw-600">{{translate('Due Date')}}:</td>
                                <td>
                                    @if($auctionInvoice->due_date)
                                    {{ $auctionInvoice->due_date->format('M d, Y') }}
                                    @if($auctionInvoice->status == 'pending' && $auctionInvoice->due_date->isPast())
                                    <span class="text-danger">({{ translate('Overdue') }})</span>
                                    @endif
                                    @else
                                    <span class="opacity-60">{{ translate('N/A') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($auctionInvoice->paid_at)
                            <tr>
                                <td class="w-50 fw-600">{{translate('Paid Date')}}:</td>
                                <td>{{ $auctionInvoice->paid_at->format('M d, Y h:i A') }}</td>
                            </tr>
                            @endif
                            <tr class="border-top">
                                <td class="fw-600">{{translate('Admin notes')}}:</td>
                                <td class="text-right fw-600">{{ $auctionInvoice->notes }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('User Information')}}</h5>
            </div>
            <div class="card-body">
                @if($auctionInvoice->user)
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{translate('Name')}}:</td>
                                <td>{{ $auctionInvoice->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{translate('Email')}}:</td>
                                <td>{{ $auctionInvoice->user->email }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{translate('Phone')}}:</td>
                                <td>{{ $auctionInvoice->user->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{translate('User ID')}}:</td>
                                <td>#{{ $auctionInvoice->user->id }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @else
                <p class="text-muted">{{ translate('User information not available') }}</p>
                @endif
            </div>
        </div>

        <!-- Auction Item Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Auction Item Information')}}</h5>
            </div>
            <div class="card-body">
                @if($auctionInvoice->auctionItem)
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{translate('Auction Room')}}:</td>
                                <td>{{ $auctionInvoice->auctionItem->auctionRoom->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{translate('Car')}}:</td>
                                <td>{{ $auctionInvoice->auctionItem->car->car_name ?? 'N/A' }} - {{
                                    $auctionInvoice->auctionItem->car->vin ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{translate('Item ID')}}:</td>
                                <td>#{{ $auctionInvoice->auctionItem->id }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @else
                <p class="text-muted">{{ translate('Auction item information not available') }}</p>
                @endif
            </div>
        </div>

        <!-- Bidding History -->
        @if($auctionInvoice->auctionItem && $auctionInvoice->auctionItem->bids->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Recent Bidding History')}}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{translate('Bidder')}}</th>
                                <th>{{translate('Amount')}}</th>
                                <th>{{translate('Time')}}</th>
                                <th>{{translate('Status')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auctionInvoice->auctionItem->bids as $bid)
                            <tr>
                                <td>{{ $bid->user->name ?? 'N/A' }}</td>
                                <td class="fw-600">{{ format_price($bid->amount) }}</td>
                                <td>{{ $bid->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <span class="badge badge-inline
                                                @if($bid->status == 'accepted') badge-success
                                                @elseif($bid->status == 'rejected') badge-danger
                                                @else badge-warning @endif">
                                        {{ ucfirst($bid->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Payment Information -->
        @if($auctionInvoice->payment)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Payment Information')}}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="w-50 fw-600">{{translate('Payment Method')}}:</td>
                        <td>{{ $auctionInvoice->payment->method ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="w-50 fw-600">{{translate('Payment Status')}}:</td>
                        <td>
                            <span class="badge badge-inline
                                @if($auctionInvoice->payment->status == 'paid') badge-success
                                @elseif($auctionInvoice->payment->status == 'pending') badge-warning
                                @else badge-danger @endif">
                                {{ translate($auctionInvoice->payment->status ?? 'N/A') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="w-50 fw-600">{{translate('Transaction ID')}}:</td>
                        <td>{{ $auctionInvoice->payment->transaction_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="w-50 fw-600">{{translate('Payment Date')}}:</td>
                        <td>{{ $auctionInvoice->payment->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="w-50 fw-600">{{translate('Amount Paid')}}:</td>
                        <td class="fw-600">{{ format_price($auctionInvoice->payment->amount ?? $auctionInvoice->amount)
                            }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Actions')}}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.auction-invoices.download-pdf', $auctionInvoice->id) }}"
                        class="btn btn-success">
                        <i class="las la-download"></i> {{translate('Download PDF')}}
                    </a>

                    @if($auctionInvoice->canUpdateStatus('paid'))
                    <button type="button" class="btn btn-primary"
                        onclick="updateInvoiceStatus({{ $auctionInvoice->id }}, 'paid')">
                        <i class="las la-check-circle"></i> {{translate('Mark as Paid')}}
                    </button>
                    @endif
                    @if ($auctionInvoice->status == 'pending')
                        <button type="button" class="btn btn-warning"
                        onclick="showReminderModal()">
                        <i class="las la-clock"></i> {{ translate('Send Payment Reminder') }}
                    </button>
                    @endif
                    @if($auctionInvoice->canUpdateStatus('cancelled'))
                    <button type="button" class="btn btn-danger"
                        onclick="updateInvoiceStatus({{ $auctionInvoice->id }}, 'cancelled')">
                        <i class="las la-times-circle"></i> {{translate('Cancel Invoice')}}
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Invoice Summary')}}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    @if($auctionInvoice->invoice_type == 'buyer_payment')
                    <tr>
                        <td class="fw-600">{{translate('Winning Bid Amount')}}:</td>
                        <td class="text-right fw-600">{{ format_price($auctionInvoice->amount) }}</td>
                    </tr>
                    @else
                    <tr>
                        <td>{{translate('Gross Amount')}}:</td>
                        <td class="text-right">{{ format_price($auctionInvoice->amount) }}</td>
                    </tr>
                    <tr>
                        <td>{{translate('Commission')}}:</td>
                        <td class="text-right">-{{ format_price($auctionInvoice->commission_amount ?? 0) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td class="fw-600">{{translate('Net Payout')}}:</td>
                        <td class="text-right fw-600">{{ format_price($auctionInvoice->net_amount) }}</td>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="status-update-modal" tabindex="-1" role="dialog" aria-labelledby="statusUpdateModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusUpdateModalLabel">{{ translate('Update Invoice Status') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="status-update-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="invoice-id" name="invoice_id">
                    <input type="hidden" id="new-status" name="status">

                    <div class="form-group" id="payment-details" style="display: none;">
                        <label for="payment_method">{{ translate('Payment Method') }}</label>
                        <input type="text" class="form-control" id="payment_method" name="payment_method" value="{{ $auctionInvoice->payment->method ?? '' }}"
                            placeholder="{{ translate('Enter payment method') }}">
                    </div>

                    <div class="form-group" id="transaction-details" style="display: none;">
                        <label for="transaction_id">{{ translate('Transaction ID') }}</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" value="{{ $auctionInvoice->payment->transaction_id ?? '' }}"
                            placeholder="{{ translate('Enter transaction ID') }}">
                    </div>

                    <div class="form-group">
                        <label for="notes">{{ translate('Notes') }} ({{ translate('Optional') }})</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                            placeholder="{{ translate('Add any additional notes') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel')
                        }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Update Status') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('modal')
<!-- Bulk Reminder Modal -->
<div class="modal fade" id="reminder-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ translate('Send Bulk Payment Reminders') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="reminder-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('Reminder Type') }}</label>
                        <select name="reminder_type" class="form-control" required>
                            <option value="email">{{ translate('Email Only') }}</option>
                            <option value="sms">{{ translate('SMS Only') }}</option>
                            <option value="both">{{ translate('Both Email & SMS') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Custom Message (Optional)') }}</label>
                        <textarea name="custom_message" class="form-control" rows="3"
                            placeholder="{{ translate('Add a custom message to the reminder...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel')
                        }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Send Reminders') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    function updateInvoiceStatus(invoiceId, status) {
        $('#invoice-id').val(invoiceId);
        $('#new-status').val(status);

        if (status === 'paid') {
            $('#payment-details').show();
            $('#transaction-details').show();
            $('#payment_method').attr('required', true);
            $('#transaction_id').attr('required', true);
        } else {
            $('#payment-details').hide();
            $('#transaction-details').hide();
            $('#payment_method').attr('required', false);
            $('#transaction_id').attr('required', false);
        }

        $('#status-update-modal').modal('show');
    }

    $('#status-update-form').on('submit', function(e) {
        e.preventDefault();

        var invoiceId = $('#invoice-id').val();
        var formData = $(this).serialize();

        $.ajax({
            url: '{{ route('admin.auction-invoices.update-status', ':id') }}'.replace(':id', invoiceId),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    AIZ.plugins.notify('success', response.message);
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', response.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    var errorMessage = '';
                    Object.keys(errors).forEach(function(key) {
                        errorMessage += errors[key][0] + '\n';
                    });
                    AIZ.plugins.notify('danger', errorMessage);
                } else {
                    AIZ.plugins.notify('danger', '{{ translate('An error occurred') }}');
                }
            }
        });
    });

    // Reminder form
        $('#reminder-form').on('submit', function(e) {
            e.preventDefault();
            const selectedIds = ["{{ $auctionInvoice->id }}"]
            var form = $(this);
            var formData = new FormData(form[0]);
            formData.append('invoice_ids[]', selectedIds);
            $.ajax({
                url: '{{ route("admin.auction-invoices.bulk-reminders") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        AIZ.plugins.notify('success', response.message);
                        $('#bulk-reminder-modal').modal('hide');
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', response.message);
                    }
                },
                error: function() {
                    AIZ.plugins.notify('danger', '{{ translate("An error occurred") }}');
                }
            });
        });

    function showReminderModal() {
        $('#reminder-modal').modal('show');
    }
</script>
@endsection
