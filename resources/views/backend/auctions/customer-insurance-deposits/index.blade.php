@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="align-items-center">
        <h1 class="h3">{{ translate('Customer Insurance Deposits') }}</h1>
    </div>
</div>

<!-- Statistics Cards -->
@include('backend.auctions.customer-insurance-deposits.partials._statistics')

<!-- Main Card -->
<div class="card">
    <form class="" id="filter_deposits" action="{{ route('insurance-deposits.index') }}" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-0 h6">{{ translate('Insurance Deposits Management') }}</h5>
            </div>
        </div>

        <!-- Filters -->
        @include('backend.auctions.customer-insurance-deposits.partials._filters')

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('insurance-deposits.index', array_merge(request()->all(), [
                                'sort_by' => 'customer_name',
                                'sort_direction' => request('sort_by') == 'customer_name' && request('sort_direction') == 'asc' ? 'desc' : 'asc'
                            ])) }}" class="text-reset">
                                {{ translate('Customer') }}
                                @if(request('sort_by') == 'customer_name')
                                    <i class="las la-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="las la-sort opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('insurance-deposits.index', array_merge(request()->all(), [
                                'sort_by' => 'amount',
                                'sort_direction' => request('sort_by') == 'amount' && request('sort_direction') == 'asc' ? 'desc' : 'asc'
                            ])) }}" class="text-reset">
                                {{ translate('Amount') }}
                                @if(request('sort_by') == 'amount')
                                    <i class="las la-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="las la-sort opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('insurance-deposits.index', array_merge(request()->all(), [
                                'sort_by' => 'status',
                                'sort_direction' => request('sort_by') == 'status' && request('sort_direction') == 'asc' ? 'desc' : 'asc'
                            ])) }}" class="text-reset">
                                {{ translate('Status') }}
                                @if(request('sort_by') == 'status')
                                    <i class="las la-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="las la-sort opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('insurance-deposits.index', array_merge(request()->all(), [
                                'sort_by' => 'paid_at',
                                'sort_direction' => request('sort_by') == 'paid_at' && request('sort_direction') == 'asc' ? 'desc' : 'asc'
                            ])) }}" class="text-reset">
                                {{ translate('Payment Date') }}
                                @if(request('sort_by') == 'paid_at')
                                    <i class="las la-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="las la-sort opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th>{{ translate('Refund Date') }}</th>
                        <th class="text-right">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                    <tr>
                        <td>
                            <div>
                                <strong>{{ $deposit->user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $deposit->user->email }}</small>
                            </div>
                        </td>
                        <td>
                            <strong>{{ single_price($deposit->amount) }}</strong>
                        </td>
                        <td>
                            @if($deposit->status == 'pending')
                                <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                            @elseif($deposit->status == 'paid')
                                <span class="badge badge-inline badge-success">{{ translate('Paid') }}</span>
                            @elseif($deposit->status == 'refunded')
                                <span class="badge badge-inline badge-info">{{ translate('Refunded') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($deposit->paid_at)
                                {{ $deposit->paid_at->format('M d, Y') }}
                                <br>
                                <small class="text-muted">{{ $deposit->paid_at->format('h:i A') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($deposit->refunded_at)
                                {{ $deposit->refunded_at->format('M d, Y') }}
                                <br>
                                <small class="text-muted">{{ $deposit->refunded_at->format('h:i A') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                               href="{{ route('insurance-deposits.show', $deposit->id) }}"
                               title="{{ translate('View Details') }}">
                                <i class="las la-eye"></i>
                        </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <p class="mb-0 text-muted">{{ translate('No deposits found') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $deposits->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>


@endsection

@section('script')
<script type="text/javascript">
    function filterDeposits() {
        $('#filter_deposits').submit();
    }

    function showRefundModal(depositId) {
        $('#refund-deposit-id').val(depositId);
        $('#refund-modal').modal('show');
    }


    // Auto-refresh statistics every 5 minutes
    setInterval(function() {
        refreshStatistics();
    }, 300000); // 5 minutes

    function refreshStatistics() {
        var filters = {
            status: $('select[name="status"]').val(),
            search: $('input[name="search"]').val(),
            date_from: $('input[name="date_from"]').val(),
            date_to: $('input[name="date_to"]').val()
        };

        $.ajax({
            url: '{{ route("insurance-deposits.statistics") }}',
            type: 'GET',
            data: filters,
            success: function(response) {
                if (response.success) {
                    // Update statistics cards
                    $('#stat-total-count').text(response.data.total_count);
                    $('#stat-paid-amount').text(formatCurrency(response.data.paid_amount));
                    $('#stat-refunded-amount').text(formatCurrency(response.data.refunded_amount));
                    $('#stat-pending-count').text(response.data.pending_count);
                }
            }
        });
    }

    function formatCurrency(amount) {
        // This is a simple formatter - adjust based on your currency settings
        return '{{ get_setting('currency_symbol', '$') }}' + parseFloat(amount).toFixed(2);
    }
</script>
@endsection
