@extends('backend.layouts.app')

@section('content')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}



.activity-timeline {
    position: relative;
    padding: 20px 0;
}

.activity-timeline:before {
    content: '';
    position: absolute;
    top: 0;
    left: 20px;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}

.activity-item {
    position: relative;
    padding: 0 0 20px 50px;
    margin: 0;
}

.activity-icon {
    position: absolute;
    left: 8px;
    top: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.activity-content h6 {
    margin-bottom: 5px;
}

.activity-content p {
    margin-bottom: 5px;
    color: #718096;
    font-size: 14px;
}

.badge-sm {
    font-size: 0.75em;
    padding: 0.25em 0.4em;
}

.progress {
    height: 6px;
    background-color: #e9ecef;
    border-radius: 3px;
}

.progress-bar {
    border-radius: 3px;
}

.h5 {
    font-size: 1.25rem;
    font-weight: 600;
}

.position-relative {
    position: relative;
}

.position-absolute {
    position: absolute;
}

.top-0 {
    top: 0;
}

.right-0 {
    right: 0;
}

.rounded-circle {
    border-radius: 50% !important;
}

.img-fit {
    object-fit: cover;
    object-position: center;
}

#inspectionStatusChart {
    height: 300px !important;
}
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Car Inspection Dashboard') }}</h1>
            <p class="text-muted mb-0">{{ translate('Overview of all inspection activities') }}</p>
        </div>
        <div class="col-md-6 text-right">
            <div class="dropdown d-inline-block">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="las la-calendar mr-1"></i>{{ translate('Quick Actions') }}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="{{ route('admin.car-inspection-types.create') }}">
                        <i class="las la-clipboard-list mr-2"></i>{{ translate('Create Inspection Type') }}
                    </a>
                    <a class="dropdown-item" href="{{ route('admin.car-inspection-types.index') }}">
                        <i class="las la-list mr-2"></i>{{ translate('Manage Types') }}
                    </a>
                    <div class="dropdown-divider"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Row -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-white mb-1">{{ $metrics['total_inspections'] ?? 0 }}</h3>
                        <p class="mb-0 text-white-50">{{ translate('Total Inspections') }}</p>
                        @if(isset($metrics['inspections_change']))
                            <small class="text-white-75">
                                <i class="las la-{{ $metrics['inspections_change'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($metrics['inspections_change']) }}% {{ translate('vs last month') }}
                            </small>
                        @endif
                    </div>
                    <div class="text-right">
                        <i class="las la-clipboard-check" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card bg-gradient-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-white mb-1">{{ $metrics['completed_inspections'] ?? 0 }}</h3>
                        <p class="mb-0 text-white-50">{{ translate('Completed') }}</p>
                        @if(isset($metrics['completion_rate']))
                            <small class="text-white-75">
                                {{ number_format($metrics['completion_rate'], 1) }}% {{ translate('completion rate') }}
                            </small>
                        @endif
                    </div>
                    <div class="text-right">
                        <i class="las la-check-circle" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card bg-gradient-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-white mb-1">{{ $metrics['pending_inspections'] ?? 0 }}</h3>
                        <p class="mb-0 text-white-50">{{ translate('Pending') }}</p>
                        @if(isset($metrics['overdue_inspections']))
                            <small class="text-white-75">
                                {{ $metrics['overdue_inspections'] }} {{ translate('overdue') }}
                            </small>
                        @endif
                    </div>
                    <div class="text-right">
                        <i class="las la-clock" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card bg-gradient-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-white mb-1">{{ number_format($metrics['average_score'] ?? 0, 1) }}%</h3>
                        <p class="mb-0 text-white-50">{{ translate('Average Score') }}</p>
                        @if(isset($metrics['score_trend']))
                            <small class="text-white-75">
                                <i class="las la-{{ $metrics['score_trend'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($metrics['score_trend']) }}% {{ translate('vs last month') }}
                            </small>
                        @endif
                    </div>
                    <div class="text-right">
                        <i class="las la-star" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Inspection Status Chart -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ translate('Inspection Status Overview') }}</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" id="inspectionStatusDropdown" type="button" data-toggle="dropdown">
                        <i class="las la-filter mr-1"></i>{{ translate('Last 30 Days') }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#" data-period="7">{{ translate('Last 7 Days') }}</a>
                        <a class="dropdown-item active" href="#" data-period="30">{{ translate('Last 30 Days') }}</a>
                        <a class="dropdown-item" href="#" data-period="90">{{ translate('Last 3 Months') }}</a>
                        <a class="dropdown-item" href="#" data-period="365">{{ translate('Last Year') }}</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="inspectionStatusChart" height="300"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ translate('Recent Activities') }}</h6>
                <a href="{{ route('admin.car-inspections.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ translate('View All') }}
                </a>
            </div>
            <div class="card-body">
                @if(isset($recent_activities) && count($recent_activities) > 0)
                    <div class="activity-timeline">
                        @foreach($recent_activities as $activity)
                            <div class="activity-item">
                                <div class="activity-icon bg-{{ $activity['type'] == 'completed' ? 'success' : ($activity['type'] == 'started' ? 'info' : ($activity['type'] == 'cancelled' ? 'danger' : 'warning')) }}">
                                    @switch($activity['type'])
                                        @case('completed')
                                            <i class="las la-check"></i>
                                            @break
                                        @case('started')
                                            <i class="las la-play"></i>
                                            @break
                                        @case('scheduled')
                                            <i class="las la-calendar"></i>
                                            @break
                                        @case('cancelled')
                                            <i class="las la-times"></i>
                                            @break
                                        @default
                                            <i class="las la-clipboard"></i>
                                    @endswitch
                                </div>
                                <div class="activity-content">
                                    <h6 class="title mb-1">{{ $activity['title'] }}</h6>
                                    <p class="text-bold mb-1">{{ $activity['description'] }}</p>
                                    <small class="text-bold ">{{ $activity['time'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="las la-history text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">{{ translate('No recent activities') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Inspection Types Performance -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Inspection Types') }}</h6>
            </div>
            <div class="card-body">
                @if(isset($inspection_types_stats) && count($inspection_types_stats) > 0)
                    @foreach($inspection_types_stats as $type_stat)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $type_stat['name'] }}</h6>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $type_stat['usage_percentage'] }}%"></div>
                                </div>
                                <small class="text-muted">{{ $type_stat['count'] }} {{ translate('inspections') }}</small>
                            </div>
                            <div class="text-right ml-3">
                                <span class="badge badge-inlinebadge-soft-primary">{{ number_format($type_stat['avg_score'], 1) }}%</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="las la-clipboard-list text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">{{ translate('No inspection types found') }}</p>
                        <a href="{{ route('admin.car-inspection-types.create') }}" class="btn btn-sm btn-primary">
                            {{ translate('Create First Type') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Inspectors -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Top Inspectors') }}</h6>
            </div>
            <div class="card-body">
                @if(isset($top_inspectors) && count($top_inspectors) > 0)
                    @foreach($top_inspectors as $index => $inspector)
                        <div class="d-flex align-items-center mb-3">
                            <div class="position-relative mr-3">
                                @if($inspector['avatar'])
                                    <img src="{{ uploaded_asset($inspector['avatar']) }}" alt="{{ $inspector['name'] }}" class="size-40px img-fit rounded-circle">
                                @else
                                    <div class="size-40px bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="las la-user text-primary"></i>
                                    </div>
                                @endif
                                <span class="position-absolute top-0 right-0 badge badge-inlinebadge-sm badge-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'info') }} rounded-circle">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $inspector['name'] }}</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $inspector['completed_count'] }} {{ translate('completed') }}</small>
                                    <span class="badge badge-inlinebadge-soft-success">{{ number_format($inspector['avg_score'], 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="las la-user-tie text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">{{ translate('No inspector data available') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Quick Stats') }}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="text-primary h5 mb-1">{{ $metrics['total_types'] ?? 0 }}</div>
                        <small class="text-muted">{{ translate('Inspection Types') }}</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-success h5 mb-1">{{ $metrics['active_inspectors'] ?? 0 }}</div>
                        <small class="text-muted">{{ translate('Active Inspectors') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Inspections -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ translate('Upcoming Inspections') }}</h6>
                <a href="{{ route('admin.car-inspections.index', ['status' => 'pending']) }}" class="text-primary">
                    {{ translate('View All') }}
                </a>
            </div>
            <div class="card-body">
                @if(isset($upcoming_inspections) && count($upcoming_inspections) > 0)
                    @foreach($upcoming_inspections as $inspection)
                        <div class="d-flex align-items-center mb-3">
                            <div class="size-40px bg-soft-info rounded mr-3 d-flex align-items-center justify-content-center">
                                <i class="las la-calendar text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $inspection['car_name'] ?? translate('N/A') }}</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $inspection['scheduled_date'] }}</small>
                                    <span class="badge badge-inlinebadge-soft-{{ $inspection['is_overdue'] ? 'danger' : 'warning' }}">
                                        {{ $inspection['is_overdue'] ? translate('Overdue') : translate('Upcoming') }}
                                    </span>
                                </div>
                                <small class="text-muted">{{ $inspection['inspector_name'] ?? translate('No inspector') }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="las la-calendar-check text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">{{ translate('No upcoming inspections') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // Initialize Inspection Status Chart
    var ctx = document.getElementById('inspectionStatusChart').getContext('2d');
    var inspectionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chart_data['labels'] ?? []) !!},
            datasets: [
                {
                    label: '{{ translate("Completed") }}',
                    data: {!! json_encode($chart_data['completed'] ?? []) !!},
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: '{{ translate("In Progress") }}',
                    data: {!! json_encode($chart_data['in_progress'] ?? []) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: '{{ translate("Pending") }}',
                    data: {!! json_encode($chart_data['pending'] ?? []) !!},
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
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
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Period filter functionality
    $('.dropdown-item[data-period]').on('click', function(e) {
        e.preventDefault();
        var period = $(this).data('period');

        // Update active state
        $('.dropdown-item[data-period]').removeClass('active');
        $(this).addClass('active');

        // Update button text
        $('#inspectionStatusDropdown').html('<i class="las la-filter mr-1"></i>' + $(this).text());

        // Reload chart data
        loadChartData(period);
    });

    function loadChartData(period) {
        $.get('/admin/car-inspections/chart-data', { period: period }, function(data) {
            if (data.success) {
                inspectionChart.data.labels = data.labels;
                inspectionChart.data.datasets[0].data = data.completed;
                inspectionChart.data.datasets[1].data = data.in_progress;
                inspectionChart.data.datasets[2].data = data.pending;
                inspectionChart.update();
            }
        });
    }

    // Auto refresh data every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);

    // Notification for overdue inspections
    @if(isset($metrics['overdue_inspections']) && $metrics['overdue_inspections'] > 0)
        AIZ.plugins.notify('warning', '{{ translate("You have") }} {{ $metrics["overdue_inspections"] }} {{ translate("overdue inspections") }}');
    @endif
});
</script>
@endsection
