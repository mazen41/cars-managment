<style>
    .top-customers-list {
        max-height: 150px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .top-customers-list::-webkit-scrollbar {
        width: 4px;
    }

    .top-customers-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .top-customers-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 2px;
    }
</style>
<div class="row gutters-16">

    <div class="col-sm-4">
        <!-- verified customers -->
        <div
            class="bg-success rounded-2 h-90px d-flex align-items-center justify-content-between text-white px-2rem mb-3">
            <div class="d-flex flex-wrap align-items-center">
                <span class="fs-13 fw-600 text-white mb-0">{{ translate('Verified Customers') }}</span>
            </div>
            <h1 class="fs-24 fw-600 mb-0">
                <div class="badge badge-inline ml-2 badge-light">({{$data['total_verified_customers_rate']}}%)</div>
                {{ $data['total_verified_customers'] }}
            </h1>
        </div>
    </div>
    <div class="col-sm-4">
        <!-- unverified customers -->
        <div
            class="bg-danger rounded-2 h-90px d-flex align-items-center justify-content-between text-white px-2rem mb-3">
            <div class="d-flex flex-wrap align-items-center">
                <span class="fs-13 fw-600 text-white mb-0">{{ translate('Unverified Customers') }}</span>
            </div>
            <h1 class="fs-24 fw-600 mb-0">
                <div class="badge badge-inline ml-2 badge-light">({{$data['total_unverified_customers_rate']}}%)</div>
                {{ $data['total_unverified_customers'] }}
            </h1>
        </div>
    </div>
    <!-- Customers with orders -->
    <div class="col-sm-4">
        <div
            class="bg-primary rounded-2 h-90px d-flex align-items-center justify-content-between text-white px-2rem mb-3">
            <div class="d-flex flex-wrap align-items-center">
                <span class="fs-13 fw-600 text-white mb-0">{{ translate('Customers with Orders') }}</span>
            </div>
            <h1 class="fs-24 fw-600 mb-0">
                <div class="badge badge-inline ml-2 badge-light">({{$data['customers_with_orders_rate']}}%)</div>
                {{ $data['customers_with_orders'] }}
            </h1>
        </div>
    </div>
</div>
<div class="dashboard-box bg-surface h-200px mb-2rem overflow-hidden">
    <div class="d-flex flex-column justify-content-between h-100">
        <div class="d-flex justify-content-between">
            <div>
                <h1 class="fs-30 fw-600  mb-1">
                    {{ format_price($data['total_credit_balance']) }}
                </h1>
                <h3 class="fs-13 fw-600 text-secondary mb-0">{{ translate('Total Credit Blance') }}</h3>
            </div>
            <div class="mt-2">
                <svg id="Group_8103" data-name="Group 8103" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="16" viewBox="0 0 16 16">
                    <defs>
                        <clipPath id="clip-path">
                        <rect id="Rectangle_1386" data-name="Rectangle 1386" width="16" height="16" fill="#b5b5bf"/>
                        </clipPath>
                    </defs>
                    <g id="Group_8102" data-name="Group 8102" clip-path="url(#clip-path)">
                        <path id="Path_2936" data-name="Path 2936" d="M13.5,4H13V2.5A2.5,2.5,0,0,0,10.5,0h-8A2.5,2.5,0,0,0,0,2.5v11A2.5,2.5,0,0,0,2.5,16h11A2.5,2.5,0,0,0,16,13.5v-7A2.5,2.5,0,0,0,13.5,4M2.5,1h8A1.5,1.5,0,0,1,12,2.5V4H2.5a1.5,1.5,0,0,1,0-3M15,11H10a1,1,0,0,1,0-2h5Zm0-3H10a2,2,0,0,0,0,4h5v1.5A1.5,1.5,0,0,1,13.5,15H2.5A1.5,1.5,0,0,1,1,13.5v-9A2.5,2.5,0,0,0,2.5,5h11A1.5,1.5,0,0,1,15,6.5Z" fill="#b5b5bf"/>
                    </g>
                </svg>
            </div>
        </div>
    </div>
</div>
<div class="row gutters-16">
    <!-- Total Customer -->
    <div class="col-sm-6">
        <div class="dashboard-box bg-surface h-200px mb-2rem overflow-hidden">
            <div class="d-flex flex-column justify-content-between h-100">
                <div class="d-flex justify-content-between">
                    <div>
                        <h1 class="fs-30 fw-600  mb-1">
                            {{ $data['total_customers'] }}
                        </h1>
                        <h3 class="fs-13 fw-600 text-secondary mb-0">{{ translate('Total Customer') }}</h3>
                    </div>
                    <div class="mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                            <path id="Path_41567" data-name="Path 41567"
                                d="M21,13.75a1.25,1.25,0,0,0,2.5,0,7.508,7.508,0,0,0-4.068-6.667,4.375,4.375,0,1,0-6.865,0A7.508,7.508,0,0,0,8.5,13.75a1.25,1.25,0,0,0,2.5,0,5,5,0,0,1,10,0ZM14.125,4.375A1.875,1.875,0,1,1,16,6.25,1.877,1.877,0,0,1,14.125,4.375ZM10.932,24.083a4.375,4.375,0,1,0-6.865,0A7.508,7.508,0,0,0,0,30.75a1.25,1.25,0,0,0,2.5,0,5,5,0,0,1,10,0,1.25,1.25,0,0,0,2.5,0A7.508,7.508,0,0,0,10.932,24.083ZM5.625,21.375A1.875,1.875,0,1,1,7.5,23.25,1.877,1.877,0,0,1,5.625,21.375Zm22.307,2.708a4.375,4.375,0,1,0-6.865,0A7.508,7.508,0,0,0,17,30.75a1.25,1.25,0,0,0,2.5,0,5,5,0,0,1,10,0,1.25,1.25,0,0,0,2.5,0A7.508,7.508,0,0,0,27.932,24.083Zm-5.307-2.708A1.875,1.875,0,1,1,24.5,23.25,1.877,1.877,0,0,1,22.625,21.375Zm0,0"
                                fill="#d5d6db" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-box bg-surface h-300px mb-2rem overflow-hidden">
            <h2 class="fs-16 fw-600  mb-3">
                {{ translate('Top Customers') }}
            </h2>
            <div class="top-customers-list">
                @foreach ($data['top_customers'] as $top_customer)
                <a href="javascript:void(0)" onclick="showUserDetails({{ $top_customer->id }})">
                    <div class="d-flex align-items-center mb-2">
                        <div class="symbol size-40px rounded-content overflow-hidden mr-3">
                            <img src="{{ uploaded_asset($top_customer->avatar_original) }}"
                                alt="{{ translate('customer') }}" class="h-100 img-fit lazyload"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </div>
                        <div class="customer-info">
                            <h4 class="fs-12 fw-600 mb-0">{{ $top_customer->name }}</h4>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    <!-- Customer Growth Graph -->
    <div class="col-lg-6">
        <div class="dashboard-box bg-surface mb-2rem overflow-hidden">
            <div class="p-2rem">
                <div class="chart-loader d-none">
                    <div class="position-absolute h-100 d-flex align-items-center justify-content-center"
                        style="  top: 0;left: 0;right: 0; bottom: 0; background: rgba(255, 255, 255, 0.8);">
                        <div class="lds-ellipsis">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="fs-16 fw-600  mb-0">{{ translate('Customer Growth') }}</h2>
                    <ul class="nav nav-tabs dashboard-tab border-0">
                        <li class="nav-item">
                            <a class="nav-link customer-growth-period active" data-period="daily" href="#">
                                {{ translate('Daily') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link customer-growth-period" data-period="weekly" href="#">
                                {{ translate('Weekly') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link customer-growth-period" data-period="monthly" href="#">
                                {{ translate('Monthly') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link customer-growth-period" data-period="yearly" href="#">
                                {{ translate('Yearly') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="text-center mb-3">
                    <span class="period-label fs-14">{{ translate('Last 30 Days') }}</span>
                </div>
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="customerGrowthChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row gutters-16">
    <!-- Top Cities by Customers -->
    <div class="col-lg-6">
        <div class="dashboard-box bg-surface mb-2rem overflow-hidden" style="height: 400px">
            <div class="p-2rem h-100">
                <h2 class="fs-16 fw-600  mb-3">{{ translate('Top Cities by Customers') }}</h2>
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="topCitiesCustomersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Cities by Orders -->
    <div class="col-lg-6">
        <div class="dashboard-box bg-surface mb-2rem overflow-hidden" style="height: 400px">
            <div class="p-2rem h-100">
                <h2 class="fs-16 fw-600  mb-3">{{ translate('Top Cities by Orders') }}</h2>
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="topCitiesOrdersChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row gutters-16">
    <div class="col-lg-6">
        <div class="dashboard-box bg-surface mb-2rem overflow-hidden" style="height: 600px">
            <div class="p-2rem h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="fs-16 fw-600  mb-0">{{ translate('Customer Retention') }}</h2>
                    <select class="form-control form-control-sm aiz-selectpicker mb-0 retention-chart-type" style="width: 120px">
                        <option value="doughnut">{{ translate('Doughnut') }}</option>
                        <option value="bar">{{ translate('Bar Chart') }}</option>
                    </select>
                </div>
                <div class="customer-retention-info mb-3">
                    <div class="row">
                        @foreach($data['customer_retention'] as $category)
                        <div class="col-sm-6 mb-2">
                            <div class="d-flex align-items-center">
                                <span class="mr-2" style="width: 12px; height: 12px; background: {{ $category['color'] }}; display: inline-block; border-radius: 2px;"></span>
                                <div class="flex-grow-1">
                                    <h5 class="fs-14 fw-600 mb-0">{{ $category['label'] }}</h5>
                                    <p class="fs-12 text-muted mb-0">{{ $category['description'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="chart-container" style="position: relative; height: 280px;">
                    <canvas id="customerRetentionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let customerGrowthChart = null;

function initializeCustomerGrowthChart(data = @json($data['customer_signup_data'])) {
    if (customerGrowthChart) {
        customerGrowthChart.destroy();
    }

      // Update period label
      document.querySelector('.period-label').textContent = data.period_label;

var ctx = document.getElementById('customerGrowthChart').getContext('2d');
customerGrowthChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: data.labels,
        datasets: [{
            label: '{{ translate("New Customers") }}',
            data: data.data,
            backgroundColor: 'rgba(55, 125, 255, 0.1)',
            borderColor: 'rgba(55, 125, 255, 1)',
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
                position: 'top',
                labels: {
                    // Use callback to handle RTL text if needed
                    generateLabels: function(chart) {
                        return Chart.defaults.plugins.legend.labels.generateLabels(chart).map(label => {
                            label.text = '{{ translate("New Customers") }}';
                            return label;
                        });
                    }
                }
            },
            title: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '{{ translate("New Customers") }}: ' + context.raw;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    borderDash: [6],
                    borderDashOffset: 0.0,
                    color: '#e7eaf3',
                    drawBorder: false
                },
                ticks: {
                    // Add thousands separator based on locale
                    callback: function(value) {
                        return new Intl.NumberFormat('{{ app()->getLocale() }}').format(value);
                    }
                }
            },
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                }
            }
        }
    }
});

// Handle RTL if needed
if (document.dir === 'rtl') {
    customerGrowthChart.options.plugins.legend.rtl = true;
    customerGrowthChart.options.plugins.tooltip.rtl = true;
    customerGrowthChart.update();
}
}

 // Handle period changes
 document.querySelectorAll('.customer-growth-period').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();

            // Update active state
            document.querySelectorAll('.customer-growth-period').forEach(el => el.classList.remove('active'));
            this.classList.add('active');
            showLoader();

        // Fetch new data
        fetch(`{{ route('admin.customer.growth.data') }}?period=${this.dataset.period}`)
            .then(response => response.json())
            .then(data => {
                initializeCustomerGrowthChart(data);
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message to user if needed
            })
            .finally(() => {
                // Hide loader after data is loaded or if there's an error
                hideLoader();
            });
        });
    });
    function initializeTopCitiesCharts() {
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false, // This is important
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: ${new Intl.NumberFormat('{{ app()->getLocale() }}').format(context.raw)}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('{{ app()->getLocale() }}').format(value);
                    }
                }
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45,
                    callback: function(value, index) {
                        const label = this.getLabelForValue(value);
                        return label.length > 15 ? label.substr(0, 12) + '...' : label;
                    }
                }
            }
        }
    };

    // Top Cities by Customers
    const customerCitiesData = @json($data['top_cities_customers']);
    new Chart(document.getElementById('topCitiesCustomersChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: customerCitiesData.map(item => item.city),
            datasets: [{
                label: '{{ translate("Number of Customers") }}',
                data: customerCitiesData.map(item => item.total),
                backgroundColor: 'rgba(55, 125, 255, 0.6)',
                borderColor: 'rgba(55, 125, 255, 1)',
                borderWidth: 1
            }]
        },
        options: chartOptions
    });

    // Top Cities by Orders
    const orderCitiesData = @json($data['top_cities_orders']);
    new Chart(document.getElementById('topCitiesOrdersChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: orderCitiesData.map(item => item.city),
            datasets: [{
                label: '{{ translate("Number of Orders") }}',
                data: orderCitiesData.map(item => item.total),
                backgroundColor: 'rgba(40, 167, 69, 0.6)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: chartOptions
    });
}

let customerRetentionChart = null;

function initializeCustomerRetentionChart(chartType = 'doughnut') {
    const retentionData = @json($data['customer_retention']);

    if (customerRetentionChart) {
        customerRetentionChart.destroy();
    }

    const ctx = document.getElementById('customerRetentionChart').getContext('2d');

    const config = {
        type: chartType,
        data: {
            labels: retentionData.map(item => item.label),
            datasets: [{
                label: '{{ translate("Number of Customers") }}', // Added label for bar chart
                data: retentionData.map(item => item.value),
                backgroundColor: retentionData.map(item => item.color),
                borderColor: retentionData.map(item => item.color),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: chartType === 'doughnut', // Only show legend for doughnut
                    position: chartType === 'doughnut' ? 'right' : 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (chartType === 'doughnut') {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${new Intl.NumberFormat('{{ app()->getLocale() }}').format(value)} (${percentage}%)`;
                            } else {
                                // For bar chart
                                const value = context.raw || 0;
                                return `${new Intl.NumberFormat('{{ app()->getLocale() }}').format(value)} {{ translate("customers") }}`;
                            }
                        },
                        afterLabel: function(context) {
                            return retentionData[context.dataIndex].description;
                        }
                    }
                }
            },
            // Specific options for bar chart
            ...(chartType === 'bar' ? {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('{{ app()->getLocale() }}').format(value);
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                indexAxis: 'x' // Ensure bars are vertical
            } : {})
        }
    };

    // For doughnut chart specific options
    if (chartType === 'doughnut') {
        config.options = {
            ...config.options,
            cutout: '60%',
            radius: '90%'
        };
    }

    customerRetentionChart = new Chart(ctx, config);
}

document.querySelector('.retention-chart-type').addEventListener('change', function(e) {
        initializeCustomerRetentionChart(e.target.value);
    });

    function showLoader() {
    document.querySelector('.chart-loader').classList.remove('d-none');
}

    function hideLoader() {
        document.querySelector('.chart-loader').classList.add('d-none');
    }
</script>
