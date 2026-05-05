<div class="row gutters-10">
    <!-- Total Sales -->
    <div class="col-md-6 col-lg-6">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-dollar-sign la-3x text-success mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ format_price($financial_summary['total_sales']) }}</h3>
                <p class="text-muted mb-0">{{ translate('Total Sales') }}</p>
                <small class="text-muted">{{ translate('Revenue from sold items') }}</small>
            </div>
        </div>
    </div>

    <!-- Total Commission -->
    <div class="col-md-6 col-lg-6">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-percentage la-3x text-warning mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ format_price($financial_summary['total_commission']) }}</h3>
                <p class="text-muted mb-0">{{ translate('Total Commission') }}</p>
                <small class="text-muted">{{ translate('Platform earnings') }}</small>
            </div>
        </div>
    </div>

</div>

<!-- Detailed Financial Breakdown -->
<div class="row mt-4">
    <div class="col-lg-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Financial Breakdown') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="text-muted">{{ translate('Total Sales Value') }}</td>
                            <td class="text-right font-weight-bold">{{ format_price($financial_summary['total_sales']) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Platform Commission') }} ({{ $room->commission_percentage }}%)</td>
                            <td class="text-right font-weight-bold">{{ format_price($financial_summary['total_commission']) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="text-muted font-weight-bold">{{ translate('Net Revenue') }}</td>
                            <td class="text-right font-weight-bold text-success h5 mb-0">{{ format_price($financial_summary['net_revenue']) }}</td>
                        </tr>
                    </tbody>
                </table>

                <hr>
            </div>
        </div>
    </div>
</div>

<!-- Summary Note -->
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="las la-info-circle"></i>
            <strong>{{ translate('Note:') }}</strong>
            {{ translate('Net Revenue represents the total sales value plus platform commission.') }}
        </div>
    </div>
</div>
