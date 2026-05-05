@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Auction Invoices')}}</h1>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_invoices" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Auction Invoices') }}</h5>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="type" name="type" onchange="sort_invoices()">
                    <option value="">{{ translate('All Types') }}</option>
                    <option value="buyer_payment" @if (request('type') == 'buyer_payment') selected @endif>{{ translate('Buyer Payment') }}</option>
                    <option value="seller_payout" @if (request('type') == 'seller_payout') selected @endif>{{ translate('Seller Payout') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status" name="status" onchange="sort_invoices()">
                    <option value="">{{ translate('All Status') }}</option>
                    <option value="pending" @if (request('status') == 'pending') selected @endif>{{ translate('Pending') }}</option>
                    <option value="paid" @if (request('status') == 'paid') selected @endif>{{ translate('Paid') }}</option>
                    <option value="overdue" @if (request('status') == 'overdue') selected @endif>{{ translate('Overdue') }}</option>
                    <option value="cancelled" @if (request('status') == 'cancelled') selected @endif>{{ translate('Cancelled') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="{{ translate('From Date') }}" onchange="sort_invoices()">
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="{{ translate('To Date') }}" onchange="sort_invoices()">
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="user_search" name="user_search" @if(request('user_search')) value="{{ request('user_search') }}" @endif placeholder="{{ translate('Search User') }}">
                </div>
            </div>
        </div>
    </form>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>{{ translate('Invoice ID') }}</th>
                    <th>{{ translate('Type') }}</th>
                    <th>{{ translate('User') }}</th>
                    <th>{{ translate('Auction Item') }}</th>
                    <th>{{ translate('Amount') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Due Date') }}</th>
                    <th>{{ translate('Created') }}</th>
                    <th class="text-right">{{ translate('Options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>
                            <a href="{{ route('admin.auction-invoices.show', $invoice->id) }}" class="text-reset">
                                #{{ $invoice->id }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-inline
                                @if($invoice->invoice_type == 'buyer_payment') badge-info
                                @else badge-warning @endif">
                                {{ $invoice->invoice_type == 'buyer_payment' ? translate('Buyer Payment') : translate('Seller Payout') }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="fs-13">{{ $invoice->user->name ?? 'N/A' }}</div>
                                    <div class="opacity-60 fs-12">{{ $invoice->user->phone ?? 'N/A' }}</div>
                                    <div class="opacity-60 fs-12">{{ $invoice->user->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fs-13">{{ $invoice->auctionItem->car->car_name ?? 'N/A' }}</div>
                             <div class="fs-13">{{ $invoice->auctionItem->car->vin ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div class="fs-13 fw-600">{{ format_price($invoice->amount) }}</div>
                            @if($invoice->invoice_type == 'seller_payout' && $invoice->commission_amount)
                                <div class="opacity-60 fs-12">{{ translate('Commission') }}: {{ format_price($invoice->commission_amount) }}</div>
                                <div class="opacity-60 fs-12">{{ translate('Net') }}: {{ format_price($invoice->net_amount) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-inline {{ $invoice->status_badge }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            @if($invoice->due_date)
                                <div class="fs-13">{{ $invoice->due_date->format('M d, Y') }}</div>
                                @if($invoice->status == 'pending' && $invoice->due_date->isPast())
                                    <div class="text-danger fs-12">{{ translate('Overdue') }}</div>
                                @endif
                            @else
                                <span class="opacity-60">{{ translate('N/A') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="fs-13">{{ $invoice->created_at->format('M d, Y') }}</div>
                            <div class="opacity-60 fs-12">{{ $invoice->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="text-right">
                            <div class="dropdown">
                                <button class="btn btn-soft-secondary btn-icon btn-circle btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="las la-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('admin.auction-invoices.show', $invoice->id) }}">
                                        <i class="las la-eye opacity-50 mr-2"></i>{{ translate('View Details') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.auction-invoices.download-pdf', $invoice->id) }}">
                                        <i class="las la-download opacity-50 mr-2"></i>{{ translate('Download PDF') }}
                                    </a>
                                    @if($invoice->canUpdateStatus('paid') || $invoice->canUpdateStatus('cancelled'))
                                        <div class="dropdown-divider"></div>
                                        @if($invoice->canUpdateStatus('paid'))
                                            <a class="dropdown-item" href="#" onclick="updateInvoiceStatus({{ $invoice->id }}, 'paid')">
                                                <i class="las la-check-circle opacity-50 mr-2"></i>{{ translate('Mark as Paid') }}
                                            </a>
                                        @endif
                                        @if($invoice->canUpdateStatus('cancelled'))
                                            <a class="dropdown-item" href="#" onclick="updateInvoiceStatus({{ $invoice->id }}, 'cancelled')">
                                                <i class="las la-times-circle opacity-50 mr-2"></i>{{ translate('Cancel Invoice') }}
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $invoices->appends(request()->input())->links() }}
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="status-update-modal" tabindex="-1" role="dialog" aria-labelledby="statusUpdateModalLabel" aria-hidden="true">
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
                        <input type="text" class="form-control" id="payment_method" name="payment_method" placeholder="{{ translate('Enter payment method') }}">
                    </div>

                    <div class="form-group" id="transaction-details" style="display: none;">
                        <label for="transaction_id">{{ translate('Transaction ID') }}</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="{{ translate('Enter transaction ID') }}">
                    </div>

                    <div class="form-group">
                        <label for="notes">{{ translate('Notes') }} ({{ translate('Optional') }})</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="{{ translate('Add any additional notes') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Update Status') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    function sort_invoices() {
        var url = '{{ route('admin.auction-invoices.index') }}';
        var type = $('#type').val();
        var status = $('#status').val();
        var date_from = $('#date_from').val();
        var date_to = $('#date_to').val();
        var user_search = $('#user_search').val();

        if (type || status || date_from || date_to || user_search) {
            url += '?';
            if (type) url += 'type=' + type + '&';
            if (status) url += 'status=' + status + '&';
            if (date_from) url += 'date_from=' + date_from + '&';
            if (date_to) url += 'date_to=' + date_to + '&';
            if (user_search) url += 'user_search=' + user_search + '&';
        }

        location.href = url;
    }

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

    // Auto-submit search on Enter key
    $('#user_search').on('keypress', function(e) {
        if (e.which == 13) {
            sort_invoices();
        }
    });
</script>
@endsection
