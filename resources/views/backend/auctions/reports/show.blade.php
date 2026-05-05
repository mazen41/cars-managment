@extends('backend.layouts.app')
@section('css')
<style>
    td{
        display: table-cell !important;
    }
</style>
@endsection
@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Auction Room Report') }}</h1>
            <p class="text-muted mb-0">{{ $room->name }}</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.auction-rooms.index') }}" class="btn btn-circle btn-light">
                <i class="las la-arrow-left"></i> {{ translate('Back to Rooms') }}
            </a>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#overview-tab" role="tab">
                    <i class="las la-info-circle"></i> {{ translate('Overview') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#financial-tab" role="tab">
                    <i class="las la-dollar-sign"></i> {{ translate('Financial Summary') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#items-tab" role="tab">
                    <i class="las la-gavel"></i> {{ translate('Auction Items') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#bids-tab" role="tab">
                    <i class="las la-hand-paper"></i> {{ translate('Bids') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#offers-tab" role="tab">
                    <i class="las la-handshake"></i> {{ translate('Offers') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#participants-tab" role="tab">
                    <i class="las la-users"></i> {{ translate('Participants') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#timing-tab" role="tab">
                    <i class="las la-clock"></i> {{ translate('Timing') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#audit-tab" role="tab">
                    <i class="las la-history"></i> {{ translate('Audit Log') }}
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview-tab" role="tabpanel">
                @include('backend.auctions.reports.partials.overview')
            </div>

            <!-- Financial Summary Tab -->
            <div class="tab-pane fade" id="financial-tab" role="tabpanel">
                @include('backend.auctions.reports.partials.financial')
            </div>

            <!-- Auction Items Tab -->
            <div class="tab-pane fade" id="items-tab" role="tabpanel">
                @include('backend.auctions.reports.partials.items')
            </div>

            <!-- Bids Tab -->
            <div class="tab-pane fade" id="bids-tab" role="tabpanel">
                @include('backend.auctions.reports.partials.bids')
            </div>

            <!-- Offers Tab -->
            <div class="tab-pane fade" id="offers-tab" role="tabpanel">
                @include('backend.auctions.reports.partials.offers')
            </div>

            <!-- Participants Tab -->
            <div class="tab-pane fade" id="participants-tab" role="tabpanel">
                @include('backend.auctions.reports.partials.participants')
            </div>

            <!-- Timing Tab -->
            <div class="tab-pane fade" id="timing-tab" role="tabpanel">
                @include('backend.auctions.reports.partials.timing')
            </div>

            <!-- Audit Log Tab -->
            <div class="tab-pane fade" id="audit-tab" role="tabpanel">
                @include('backend.auctions.reports.partials.audit-log')
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ static_asset('assets/js/auction-report-filters.js') }}"></script>
<script>
    // Preserve active tab on page reload
    $(document).ready(function() {
        // Get the hash from URL
        var hash = window.location.hash;
        if (hash) {
            $('.nav-tabs a[href="' + hash + '"]').tab('show');
        }

        // Update URL hash when tab changes
        $('.nav-tabs a').on('shown.bs.tab', function(e) {
            window.location.hash = e.target.hash;
        });

        // Initialize AJAX filtering
        if (typeof AuctionReportFilters !== 'undefined') {
            AuctionReportFilters.init({{ $room->id }});
        }
    });
</script>
@endsection
