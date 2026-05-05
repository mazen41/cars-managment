@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class=" align-items-center">
        <h1 class="h3">{{translate('Commission History')}}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="row gutters-10 mb-3">
            <!-- Total Seller Order Commissions -->
            @if (request()->input('commissionable_type') == 'order' || request()->input('commissionable_type') == null)
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-muted mb-1">{{ translate('Order Commissions') }}</div>
                                <h3 class="mb-0" id="stat-total-count">{{ $stats['order_commissions'] ?? 0 }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm d-flex align-items-center justify-content-center w-30 h-30 rounded-circle bg-soft-primary text-primary">
                                    {{ $stats['order_commissions_count'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- Total Car Reservation Commissions -->
            @if (request()->input('commissionable_type') == 'car_reservation' || request()->input('commissionable_type') == null)
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-muted mb-1">{{ translate('Car Reservation Commissions') }}</div>
                                <h3 class="mb-0" id="stat-paid-amount">{{ $stats['car_reservation_commissions'] }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm d-flex align-items-center justify-content-center w-30 h-30 rounded-circle bg-soft-success text-success">
                                {{ $stats['car_reservation_commissions_count'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- Total Car Inspections commissions -->
            @if (request()->input('commissionable_type') == 'car_inspection' || request()->input('commissionable_type') == null)
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-muted mb-1">{{ translate('Car Inspection Commissions') }}</div>
                                <h3 class="mb-0" id="stat-refunded-amount">{{ $stats['car_inspection_commissions'] ?? 0
                                    }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm d-flex align-items-center justify-content-center w-30 h-30 rounded-circle bg-soft-info text-info">
                                    {{ $stats['car_inspection_commissions_count'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- Total Auction Invoice Commissions -->
            @if (request()->input('commissionable_type') == 'auction_invoice' || request()->input('commissionable_type') == null)
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-muted mb-1">{{ translate('Auction Invoice Commissions') }}</div>
                                <h3 class="mb-0" id="stat-pending-count">{{ $stats['auction_invoice_commissions'] ?? 0
                                    }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm d-flex align-items-center justify-content-center w-30 h-30 rounded-circle bg-soft-warning text-warning">
                                    {{ $stats['auction_invoice_commissions_count'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="card">
            @include('backend.reports.partials.commission_history_section')
        </div>
    </div>
</div>

@endsection
