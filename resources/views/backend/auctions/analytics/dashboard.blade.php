@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h1 class="h3">{{ translate('Auction Analytics Dashboard') }}</h1>
</div>

<!-- Room Statistics -->
<div class="row gutters-10 mb-3">
    <div class="col-md-12">
        <h5 class="mb-3">{{ translate('Room Statistics') }}</h5>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-door-open la-3x text-info mb-2"></i>
                <h4 class="mb-0">{{ $metrics['rooms']['total'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Total Rooms') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-play-circle la-3x text-success mb-2"></i>
                <h4 class="mb-0">{{ $metrics['rooms']['active'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Active') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-clock la-3x text-warning mb-2"></i>
                <h4 class="mb-0">{{ $metrics['rooms']['scheduled'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Scheduled') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-check-circle la-3x text-primary mb-2"></i>
                <h4 class="mb-0">{{ $metrics['rooms']['completed'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Completed') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-ban la-3x text-danger mb-2"></i>
                <h4 class="mb-0">{{ $metrics['rooms']['cancelled'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Cancelled') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Financial Metrics -->
<div class="row gutters-10 mb-3">
    <div class="col-md-12">
        <h5 class="mb-3">{{ translate('Financial Metrics') }}</h5>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-dollar-sign la-3x text-success mb-2"></i>
                <h4 class="mb-0">{{ format_price($metrics['financial']['total_sales_value'] ?? 0) }}</h4>
                <small class="text-muted">{{ translate('Total Sales') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-percentage la-3x text-info mb-2"></i>
                <h4 class="mb-0">{{ format_price($metrics['financial']['total_commission_earned'] ?? 0) }}</h4>
                <small class="text-muted">{{ translate('Commission Earned') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-hourglass-half la-3x text-warning mb-2"></i>
                <h4 class="mb-0">{{ format_price($metrics['financial']['pending_buyer_payments'] ?? 0) }}</h4>
                <small class="text-muted">{{ translate('Pending Payments') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-shield-alt la-3x text-primary mb-2"></i>
                <h4 class="mb-0">{{ format_price($metrics['financial']['insurance_deposits_collected'] ?? 0) }}</h4>
                <small class="text-muted">{{ translate('Insurance Deposits') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Item & Bidding Statistics -->
<div class="row gutters-10 mb-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Item Statistics') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center py-2 border-right">
                        <h3 class="mb-0">{{ $metrics['items']['total'] ?? 0 }}</h3>
                        <small class="text-muted">{{ translate('Total Items') }}</small>
                    </div>
                    <div class="col-6 text-center py-2">
                        <h3 class="mb-0">{{ $metrics['items']['active'] ?? 0 }}</h3>
                        <small class="text-muted">{{ translate('Active') }}</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-4 text-center py-2">
                        <h4 class="mb-0 text-success">{{ $metrics['items']['sold'] ?? 0 }}</h4>
                        <small class="text-muted">{{ translate('Sold') }}</small>
                    </div>
                    <div class="col-4 text-center py-2">
                        <h4 class="mb-0 text-warning">{{ $metrics['items']['unsold'] ?? 0 }}</h4>
                        <small class="text-muted">{{ translate('Unsold') }}</small>
                    </div>
                    <div class="col-4 text-center py-2">
                        <h4 class="mb-0 text-info">{{ $metrics['items']['offer_accepted'] ?? 0 }}</h4>
                        <small class="text-muted">{{ translate('Offer Accepted') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Bidding Activity') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center py-2 border-right">
                        <h3 class="mb-0">{{ $metrics['bidding']['total_bids'] ?? 0 }}</h3>
                        <small class="text-muted">{{ translate('Total Bids') }}</small>
                    </div>
                    <div class="col-6 text-center py-2">
                        <h3 class="mb-0">{{ number_format($metrics['bidding']['average_bids_per_item'] ?? 0, 1) }}</h3>
                        <small class="text-muted">{{ translate('Avg Bids/Item') }}</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-4 text-center py-2">
                        <h4 class="mb-0 text-success">{{ $metrics['bidding']['accepted_bids'] ?? 0 }}</h4>
                        <small class="text-muted">{{ translate('Accepted') }}</small>
                    </div>
                    <div class="col-4 text-center py-2">
                        <h4 class="mb-0 text-danger">{{ $metrics['bidding']['rejected_bids'] ?? 0 }}</h4>
                        <small class="text-muted">{{ translate('Rejected') }}</small>
                    </div>
                    <div class="col-4 text-center py-2">
                        <h4 class="mb-0 text-info">{{ format_price($metrics['bidding']['highest_bid_amount'] ?? 0) }}</h4>
                        <small class="text-muted">{{ translate('Highest Bid') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offer Statistics -->
<div class="row gutters-10 mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Pre-Auction Offer Statistics') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center py-2">
                        <h3 class="mb-0">{{ $metrics['offers']['total_offers'] ?? 0 }}</h3>
                        <small class="text-muted">{{ translate('Total Offers') }}</small>
                    </div>
                    <div class="col-md-2 text-center py-2">
                        <h3 class="mb-0 text-warning">{{ $metrics['offers']['pending_offers'] ?? 0 }}</h3>
                        <small class="text-muted">{{ translate('Pending') }}</small>
                    </div>
                    <div class="col-md-2 text-center py-2">
                        <h3 class="mb-0 text-success">{{ $metrics['offers']['accepted_offers'] ?? 0 }}</h3>
                        <small class="text-muted">{{ translate('Accepted') }}</small>
                    </div>
                    <div class="col-md-2 text-center py-2">
                        <h3 class="mb-0 text-danger">{{ $metrics['offers']['rejected_offers'] ?? 0 }}</h3>
                        <small class="text-muted">{{ translate('Rejected') }}</small>
                    </div>
                    <div class="col-md-4 text-center py-2">
                        <h3 class="mb-0 text-info">{{ number_format($metrics['offers']['offer_acceptance_rate'] ?? 0, 1) }}%</h3>
                        <small class="text-muted">{{ translate('Acceptance Rate') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics by Room -->
<div class="row gutters-10 mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Performance by Room') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ translate('Room Name') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Items') }}</th>
                                <th>{{ translate('Sold') }}</th>
                                <th>{{ translate('Total Sales') }}</th>
                                <th>{{ translate('Avg Sale Price') }}</th>
                                <th>{{ translate('Commission') }}</th>
                                <th>{{ translate('Bid Participation') }}</th>
                                <th>{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roomPerformance ?? [] as $room)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.auction-rooms.show', $room['id']) }}">
                                            {{ $room['name'] }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($room['status'] == 'active')
                                            <span class="badge badge-inline badge-success">{{ translate('Active') }}</span>
                                        @elseif($room['status'] == 'completed')
                                            <span class="badge badge-inline badge-primary">{{ translate('Completed') }}</span>
                                        @else
                                            <span class="badge badge-inline badge-secondary">{{ translate(ucfirst($room['status'])) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $room['total_items'] }}</td>
                                    <td>{{ $room['sold_items'] }}</td>
                                    <td>{{ format_price($room['total_sales']) }}</td>
                                    <td>{{ format_price($room['avg_sale_price']) }}</td>
                                    <td>{{ format_price($room['total_commission']) }}</td>
                                    <td>{{ number_format($room['bid_participation_rate'], 1) }}%</td>
                                    <td>
                                         <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('admin.auction-rooms.show', $room['id']) }}" title="{{ translate('View Details') }}">
                                            <i class="las la-eye"></i>
                                        </a>
                                        @if($room['status'] == 'completed')
                                            <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="{{ route('admin.auction-rooms.report', $room['id']) }}" title="{{ translate('View Report') }}">
                                                <i class="las la-chart-bar"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">{{ translate('No data available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        // Auto-refresh every 30 seconds for live data
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
@endsection
