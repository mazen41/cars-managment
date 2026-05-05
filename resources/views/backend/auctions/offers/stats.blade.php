@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Auction Offers Statistics')}}</h1>
        </div>
    </div>
</div>

<!-- Summary Statistics Cards -->
<div class="row gutters-10 mb-3">
    <div class="col-md-12">
        <h5 class="mb-3">{{ translate('Summary Statistics') }}</h5>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-handshake la-3x text-primary mb-2"></i>
                <h4 class="mb-0">{{ $stats['total_offers'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Total Offers') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-dollar-sign la-3x text-success mb-2"></i>
                <h4 class="mb-0">{{ format_price($stats['total_offer_value'] ?? 0) }}</h4>
                <small class="text-muted">{{ translate('Total Value (Accepted)') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-chart-line la-3x text-info mb-2"></i>
                <h4 class="mb-0">{{ format_price($stats['average_offer_amount'] ?? 0) }}</h4>
                <small class="text-muted">{{ translate('Average Offer Amount') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-trophy la-3x text-warning mb-2"></i>
                <h4 class="mb-0">{{ format_price($stats['highest_offer'] ?? 0) }}</h4>
                <small class="text-muted">{{ translate('Highest Offer') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Offer Status Breakdown -->
<div class="row gutters-10 mb-3">
    <div class="col-md-12">
        <h5 class="mb-3">{{ translate('Offers by Status') }}</h5>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-clock la-3x text-info mb-2"></i>
                <h4 class="mb-0">{{ $stats['pending_offers'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Pending') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-check-circle la-3x text-success mb-2"></i>
                <h4 class="mb-0">{{ $stats['accepted_offers'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Accepted') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-times-circle la-3x text-danger mb-2"></i>
                <h4 class="mb-0">{{ $stats['rejected_offers'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Rejected') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-hourglass-end la-3x text-secondary mb-2"></i>
                <h4 class="mb-0">{{ $stats['expired_offers'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Expired') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-undo la-3x text-warning mb-2"></i>
                <h4 class="mb-0">{{ $stats['withdrawn_offers'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Withdrawn') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row gutters-10 mb-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Offers Distribution by Status') }}</h5>
            </div>
            <div class="card-body">
                <canvas id="offersStatusChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Monthly Offer Trends (Last 6 Months)') }}</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendsChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent High-Value Offers -->
<div class="row gutters-10 mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Recent High-Value Pending Offers (≥ 10,000)') }}</h5>
            </div>
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Offer Amount') }}</th>
                            <th>{{ translate('Car Name') }}</th>
                            <th>{{ translate('Brand') }}</th>
                            <th>{{ translate('Buyer') }}</th>
                            <th>{{ translate('Seller') }}</th>
                            <th>{{ translate('Created') }}</th>
                            <th class="text-right">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['recent_high_value_offers'] ?? [] as $offer)
                            <tr>
                                <td>
                                    <strong class="text-success">{{ format_price($offer->amount) }}</strong>
                                </td>
                                <td>{{ $offer->auctionItem->car->name }}</td>
                                <td>{{ $offer->auctionItem->car->carBrand->name ?? 'N/A' }}</td>
                                <td>
                                    <div>{{ $offer->buyer->name }}</div>
                                    <small class="text-muted">{{ $offer->buyer->email }}</small>
                                </td>
                                <td>
                                    <div>{{ $offer->seller->name }}</div>
                                    <small class="text-muted">{{ $offer->seller->email }}</small>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $offer->created_at->format('d M Y H:i') }}</span>
                                </td>
                                <td class="text-right">
                                    <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" 
                                       href="{{ route('admin.auction-offers.show', $offer->id) }}" 
                                       title="{{ translate('View Details') }}">
                                        <i class="las la-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">{{ translate('No high-value pending offers found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script type="text/javascript">
        // Offers by Status Chart
        var offersStatusData = @json($stats['offers_by_status'] ?? []);
        var statusLabels = [];
        var statusCounts = [];
        var statusColors = {
            'pending': '#17a2b8',
            'accepted': '#28a745',
            'rejected': '#dc3545',
            'expired': '#6c757d',
            'withdrawn': '#ffc107'
        };
        var backgroundColors = [];

        for (var status in offersStatusData) {
            statusLabels.push(status.charAt(0).toUpperCase() + status.slice(1));
            statusCounts.push(offersStatusData[status]);
            backgroundColors.push(statusColors[status] || '#6c757d');
        }

        var ctx = document.getElementById('offersStatusChart').getContext('2d');
        var offersStatusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: '{{ translate('Number of Offers') }}',
                    data: statusCounts,
                    backgroundColor: backgroundColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Monthly Trends Chart
        var monthlyTrendsData = @json($stats['monthly_offer_trends'] ?? []);
        var monthLabels = [];
        var totalOffersData = [];
        var acceptedOffersData = [];
        var avgAmountData = [];

        monthlyTrendsData.forEach(function(item) {
            monthLabels.push(item.month);
            totalOffersData.push(item.total_offers);
            acceptedOffersData.push(item.accepted_offers);
            avgAmountData.push(parseFloat(item.avg_amount || 0).toFixed(2));
        });

        var ctx2 = document.getElementById('monthlyTrendsChart').getContext('2d');
        var monthlyTrendsChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [
                    {
                        label: '{{ translate('Total Offers') }}',
                        data: totalOffersData,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '{{ translate('Accepted Offers') }}',
                        data: acceptedOffersData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
@endsection
