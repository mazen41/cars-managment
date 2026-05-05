@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Customer Products Analytics')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.customer-products.index') }}" class="btn btn-light">
                <i class="las la-arrow-left"></i> {{translate('Back to Products')}}
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row gutters-10 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card card-statistics h-100">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm">
                            <span class="avatar-title bg-primary-light rounded-circle">
                                <i class="las la-boxes text-primary"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <h4 class="mb-1">{{ $stats['total_products'] }}</h4>
                        <p class="text-muted mb-0">{{translate('Total Products')}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-statistics h-100">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm">
                            <span class="avatar-title bg-warning-light rounded-circle">
                                <i class="las la-clock text-warning"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <h4 class="mb-1">{{ $stats['pending_products'] }}</h4>
                        <p class="text-muted mb-0">{{translate('Pending Review')}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-statistics h-100">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm">
                            <span class="avatar-title bg-success-light rounded-circle">
                                <i class="las la-check-circle text-success"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <h4 class="mb-1">{{ $stats['approved_products'] }}</h4>
                        <p class="text-muted mb-0">{{translate('Approved')}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-statistics h-100">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm">
                            <span class="avatar-title bg-danger-light rounded-circle">
                                <i class="las la-times-circle text-danger"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <h4 class="mb-1">{{ $stats['rejected_products'] }}</h4>
                        <p class="text-muted mb-0">{{translate('Rejected')}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row gutters-10">
    <!-- Products by Category Chart -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Products by Category')}}</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Products by Month Chart -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Products Created (Last 12 Months)')}}</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row gutters-10">
    <!-- Top Customers -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Top Customers by Product Count')}}</h5>
            </div>
            <div class="card-body">
                @if($topCustomers->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{translate('Customer')}}</th>
                                    <th>{{translate('Products')}}</th>
                                    <th>{{translate('Percentage')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $customer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($customer->user->avatar_original)
                                                <img src="{{ uploaded_asset($customer->user->avatar_original) }}" 
                                                     class="rounded-circle size-30px mr-2" alt="Avatar">
                                            @else
                                                <div class="rounded-circle size-30px bg-light d-flex align-items-center justify-content-center mr-2">
                                                    <i class="las la-user text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $customer->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $customer->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $customer->product_count }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $percentage = $stats['total_products'] > 0 ? round(($customer->product_count / $stats['total_products']) * 100, 1) : 0;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small>{{ $percentage }}%</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="las la-users text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted">{{translate('No customer data available')}}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Moderation Statistics -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Moderation Statistics')}}</h5>
            </div>
            <div class="card-body">
                @php
                    $approvalRate = $stats['total_products'] > 0 ? round(($stats['approved_products'] / $stats['total_products']) * 100, 1) : 0;
                    $rejectionRate = $stats['total_products'] > 0 ? round(($stats['rejected_products'] / $stats['total_products']) * 100, 1) : 0;
                    $pendingRate = $stats['total_products'] > 0 ? round(($stats['pending_products'] / $stats['total_products']) * 100, 1) : 0;
                @endphp

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{translate('Approval Rate')}}</span>
                        <span class="text-success font-weight-bold">{{ $approvalRate }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $approvalRate }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{translate('Rejection Rate')}}</span>
                        <span class="text-danger font-weight-bold">{{ $rejectionRate }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $rejectionRate }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{translate('Pending Rate')}}</span>
                        <span class="text-warning font-weight-bold">{{ $pendingRate }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendingRate }}%"></div>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-right">
                            <h4 class="text-success">{{ $stats['approved_products'] }}</h4>
                            <small class="text-muted">{{translate('Approved')}}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-right">
                            <h4 class="text-danger">{{ $stats['rejected_products'] }}</h4>
                            <small class="text-muted">{{translate('Rejected')}}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <h4 class="text-warning">{{ $stats['pending_products'] }}</h4>
                        <small class="text-muted">{{translate('Pending')}}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    // Products by Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = @json($productsByCategory);
    
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(item => item.category ? item.category.name : 'Unknown'),
            datasets: [{
                data: categoryData.map(item => item.count),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#C9CBCF'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Monthly Products Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($productsByMonth);
    
    // Create labels for last 12 months
    const months = [];
    const counts = [];
    
    for (let i = 11; i >= 0; i--) {
        const date = new Date();
        date.setMonth(date.getMonth() - i);
        const monthYear = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        months.push(monthYear);
        
        // Find count for this month
        const monthData = monthlyData.find(item => 
            item.year == date.getFullYear() && item.month == (date.getMonth() + 1)
        );
        counts.push(monthData ? monthData.count : 0);
    }

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: '{{translate("Products Created")}}',
                data: counts,
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
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
</script>
@endsection