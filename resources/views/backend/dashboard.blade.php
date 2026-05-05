@extends('backend.layouts.app')
@section('content')
@if (auth()->user()->can('smtp_settings') &&
env('MAIL_USERNAME') == null &&
env('MAIL_PASSWORD') == null)
<div class="">
    <div class="alert alert-info d-flex align-items-center">
        {{ translate('Please Configure SMTP Setting to work all email sending functionality') }},
        <a class="alert-link ml-2" href="{{ route('smtp_settings.index') }}">{{ translate('Configure Now') }}</a>
    </div>
</div>
@endif
@can('admin_dashboard')
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a href="#internal_orders" data-toggle="tab" class="nav-link active">
                {{ translate('Orders') }}
            </a>
        </li>
        <li class="nav-item">
            <a href="#customers_data" data-toggle="tab" class="nav-link" id="customers_data_tab">
                {{ translate('Customers') }}
            </a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <div class="tab-pane active" id="internal_orders">
            @include('backend.dashboard.internal_orders')
        </div>

        <div class="tab-pane" id="customers_data">
            <!-- This will be loaded via Ajax -->
           <div class="h-100 d-flex align-items-center justify-content-center">
                <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
                </div>
        </div>
    </div>
</div>
@else
    @include('backend.dashboard.placeholder')
@endcan
@section('modal')
    @parent
@include('modals.customer_details_modal')
@endsection

@endsection
@section('script')
<!-- dashboard script -->
@include('backend.dashboard.dashboard_js')

<script type="text/javascript">
    AIZ.plugins.chart('#graph-3', {
            type: 'line',
            data: {
                labels: [
                    @foreach ($sales_stat as $month => $row)
                        "{{ $month }}",
                    @endforeach
                ],
                datasets: [{
                        fill: false,
                        borderColor: '#009ef7',
                        label: "{{ translate('Yearly Sales') }}",
                        data: [
                            @foreach ($sales_stat as $row)
                                {{ $row[0]->total }},
                            @endforeach
                        ],

                    },

                ]
            },
            options: {
                legend: {
                    labels: {
                        fontFamily: 'sans-serif',
                        fontColor: "#000",
                        boxWidth: 10,
                        usePointStyle: true
                    },
                    onClick: function() {
                        return '';
                    },
                    position: 'bottom'
                },
                scales: {
                    x: {
                        display: false,
                        drawBorder: false,
                    },
                    y: {
                        display: false,
                        drawBorder: false,
                    },
                }
            }
        });

        AIZ.plugins.chart('#graph-2', {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach ($payment_type_wise_inhouse_sale as $row)
                        "{{ ucwords(str_replace('_', ' ', translate($row->payment_type))) }}",
                    @endforeach
                ],
                datasets: [{
                    label: 'Total Sales',
                    data: [
                        @foreach ($payment_type_wise_inhouse_sale as $row)
                            {{ $row->total_amount }},
                        @endforeach
                    ],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        align: 'center',
                        labels: {
                            usePointStyle: true,
                            fontSize: 11,
                            boxWidth: 30
                        },
                    },
                }
            }
        })

        function top_category_products(category_id, e) {
            $(".top_category_products").removeClass("active");
            e.classList.add("active");
            $(".top_category_product_table").removeClass("show");
            $("#top_category_product_table_" + category_id).addClass("show");
        }

        function top_sellers_products(seller_id, e) {
            $(".top_sellers_products").removeClass("active");
            e.classList.add("active");
            $(".top_sellers_product_table").removeClass("show");
            $("#top_sellers_product_table_" + seller_id).addClass("show");
        }

        function top_brands_products(brand_id, e) {
            $(".top_brands_products").removeClass("active");
            e.classList.add("active");
            $(".top_brands_product_table").removeClass("show");
            $("#top_brands_product_table_" + brand_id).addClass("show");
        }
</script>
@endsection
