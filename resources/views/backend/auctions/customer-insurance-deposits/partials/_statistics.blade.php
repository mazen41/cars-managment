<div class="row gutters-10 mb-3">
    <!-- Total Deposits Card -->
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted mb-1">{{ translate('Total Deposits') }}</div>
                        <h3 class="mb-0" id="stat-total-count">{{ $statistics['total_count'] ?? 0 }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-soft-primary text-primary">
                            <i class="las la-file-invoice-dollar la-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Paid Amount Card -->
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted mb-1">{{ translate('Total Paid') }}</div>
                        <h3 class="mb-0" id="stat-paid-amount">{{ single_price($statistics['paid_amount'] ?? 0) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-soft-success text-success">
                            <i class="las la-check-circle la-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Refunded Amount Card -->
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted mb-1">{{ translate('Total Refunded') }}</div>
                        <h3 class="mb-0" id="stat-refunded-amount">{{ single_price($statistics['refunded_amount'] ?? 0) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-soft-info text-info">
                            <i class="las la-undo la-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Deposits Card -->
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted mb-1">{{ translate('Pending') }}</div>
                        <h3 class="mb-0" id="stat-pending-count">{{ $statistics['pending_count'] ?? 0 }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm rounded-circle bg-soft-warning text-warning">
                            <i class="las la-clock la-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-soft-primary {
    background-color: var(--soft-primary) !important;
}

.bg-soft-success {
    background-color: rgba(40, 167, 69, 0.15) !important;
}

.bg-soft-info {
    background-color: rgba(23, 162, 184, 0.15) !important;
}

.bg-soft-warning {
    background-color: rgba(255, 193, 7, 0.15) !important;
}

.text-success {
    color: #28a745 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-warning {
    color: #ffc107 !important;
}
</style>
