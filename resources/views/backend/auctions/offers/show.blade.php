@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Offer Details')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.auction-offers.index') }}" class="btn btn-circle btn-info">
                <i class="las la-arrow-left"></i>
                <span class="d-none d-md-inline-block ml-1">{{translate('Back to List')}}</span>
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<div class="mb-3">
    <ul class="breadcrumb bg-transparent p-0">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}">{{translate('Dashboard')}}</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('admin.auction-offers.index') }}">{{translate('Auction Offers')}}</a>
        </li>
        <li class="breadcrumb-item active">
            {{translate('Offer')}} #{{ $offer->id }}
        </li>
    </ul>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Offer Information Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Offer Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted">{{translate('Offer Amount')}}</label>
                            <h4 class="mb-0">{{ format_price($offer->amount) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted">{{translate('Status')}}</label>
                            <div>
                                @if($offer->status == 'pending')
                                    <span class="badge badge-inline badge-info">{{ translate('Pending') }}</span>
                                @elseif($offer->status == 'accepted')
                                    <span class="badge badge-inline badge-success">{{ translate('Accepted') }}</span>
                                @elseif($offer->status == 'rejected')
                                    <span class="badge badge-inline badge-danger">{{ translate('Rejected') }}</span>
                                @elseif($offer->status == 'expired')
                                    <span class="badge badge-inline badge-secondary">{{ translate('Expired') }}</span>
                                @else
                                    <span class="badge badge-inline badge-warning">{{ translate('Withdrawn') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($offer->message)
                <div class="mb-3">
                    <label class="text-muted">{{translate('Buyer Message')}}</label>
                    <p class="mb-0">{{ $offer->message }}</p>
                </div>
                @endif

                @if($offer->seller_response)
                <div class="mb-3">
                    <label class="text-muted">{{translate('Seller Response')}}</label>
                    <p class="mb-0">{{ $offer->seller_response }}</p>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-2">
                            <label class="text-muted small">{{translate('Created At')}}</label>
                            <div>{{ $offer->created_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                    @if($offer->responded_at)
                    <div class="col-md-4">
                        <div class="mb-2">
                            <label class="text-muted small">{{translate('Responded At')}}</label>
                            <div>{{ $offer->responded_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                    @endif
                    @if($offer->expires_at)
                    <div class="col-md-4">
                        <div class="mb-2">
                            <label class="text-muted small">{{translate('Expires At')}}</label>
                            <div>{{ $offer->expires_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Car Details Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Car Details')}}</h5>
            </div>
            <div class="card-body">
                <!-- Car Images -->
                <div class="mb-3">
                    <div id="carImageCarousel" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item">
                                <img src="{{ $offerDetails['car_details']['main_photo_url'] }}"
                                     class="d-block w-100"
                                     style="max-height: 400px; object-fit: cover;"
                                     alt="{{ $offerDetails['car_details']['name'] }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Car Specifications -->
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <h5>{{ $offerDetails['car_details']['name'] }}</h5>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">{{translate('Brand')}}</td>
                                <td><strong>{{ $offerDetails['car_details']['brand'] }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{translate('Model')}}</td>
                                <td><strong>{{ $offerDetails['car_details']['model'] }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{translate('Year')}}</td>
                                <td><strong>{{ $offerDetails['car_details']['year'] ?? 'N/A' }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">{{translate('Color')}}</td>
                                <td><strong>{{ $offerDetails['car_details']['color'] }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{translate('Mileage')}}</td>
                                <td><strong>{{ $offerDetails['car_details']['mileage'] ?? 'N/A' }} {{translate('km')}}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{translate('Condition')}}</td>
                                <td><strong>{{ ucfirst($offerDetails['car_details']['condition'] ?? 'N/A') }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Auction Context Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Auction Context')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">{{translate('Auction Room')}}</label>
                        <div><strong>{{ $offerDetails['auction_context']['room_name'] }}</strong></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">{{translate('Room Status')}}</label>
                        <div>
                            <span class="badge badge-inline badge-{{ $offerDetails['auction_context']['room_status'] == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($offerDetails['auction_context']['room_status']) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">{{translate('Starting Price')}}</label>
                        <div><strong>{{ format_price($offerDetails['auction_context']['item_starting_price']) }}</strong></div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">{{translate('Current Price')}}</label>
                        <div><strong>{{ format_price($offerDetails['auction_context']['item_current_price']) }}</strong></div>
                    </div>
                    @if($offerDetails['auction_context']['item_reserve_price'])
                    <div class="col-md-4">
                        <label class="text-muted small">{{translate('Reserve Price')}}</label>
                        <div><strong>{{ format_price($offerDetails['auction_context']['item_reserve_price']) }}</strong></div>
                    </div>
                    @endif
                    <div class="col-md-12 mt-2">
                        <label class="text-muted small">{{translate('Item Status')}}</label>
                        <div>
                            <span class="badge badge-inline badge-info">{{ ucfirst($offerDetails['auction_context']['item_status']) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Buyer Information Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Buyer Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Name')}}</label>
                    <div><strong>{{ $offerDetails['participants']['buyer']['name'] }}</strong></div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Email')}}</label>
                    <div>{{ $offerDetails['participants']['buyer']['email'] }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Insurance Deposit')}}</label>
                    <div>
                        @if($offerDetails['participants']['buyer']['has_insurance_deposit'])
                            <span class="badge badge-inline badge-success">{{translate('Paid')}}</span>
                        @else
                            <span class="badge badge-inline badge-warning">{{translate('Not Paid')}}</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('customers.details', $offerDetails['participants']['buyer']['id']) }}"
                   class="btn btn-sm btn-soft-primary">
                    <i class="las la-user"></i> {{translate('View Profile')}}
                </a>
            </div>
        </div>

        <!-- Seller Information Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Seller Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Name')}}</label>
                    <div><strong>{{ $offerDetails['participants']['seller']['name'] }}</strong></div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Email')}}</label>
                    <div>{{ $offerDetails['participants']['seller']['email'] }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Total Cars Sold')}}</label>
                    <div><strong>{{ $offerDetails['participants']['seller']['total_cars_sold'] }}</strong></div>
                </div>
            </div>
        </div>

        <!-- Offer Analysis Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Offer Analysis')}}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Percentage of Starting Price')}}</label>
                    <div>
                        <strong class="h5 {{ $offerDetails['offer_analysis']['percentage_of_starting_price'] >= 100 ? 'text-success' : 'text-warning' }}">
                            {{ $offerDetails['offer_analysis']['percentage_of_starting_price'] }}%
                        </strong>
                    </div>
                </div>
                @if($offerDetails['auction_context']['item_reserve_price'])
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Reserve Price Comparison')}}</label>
                    <div>
                        @if($offerDetails['offer_analysis']['is_above_reserve'])
                            <span class="badge badge-inline badge-success">{{translate('Above Reserve')}}</span>
                        @else
                            <span class="badge badge-inline badge-warning">{{translate('Below Reserve')}}</span>
                        @endif
                    </div>
                </div>
                @endif
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Acceptance Eligibility')}}</label>
                    <div>
                        @if($offerDetails['offer_analysis']['can_be_accepted'])
                            <span class="badge badge-inline badge-success">{{translate('Eligible')}}</span>
                        @else
                            <span class="badge badge-inline badge-danger">{{translate('Not Eligible')}}</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Expiration Status')}}</label>
                    <div>
                        @if($offerDetails['offer_analysis']['is_expired'])
                            <span class="badge badge-inline badge-danger">{{translate('Expired')}}</span>
                        @else
                            <span class="badge badge-inline badge-success">{{translate('Active')}}</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">{{translate('Days Since Creation')}}</label>
                    <div><strong>{{ $offerDetails['offer_analysis']['days_since_created'] }} {{translate('days')}}</strong></div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                @if($offer->status == 'pending' && $offer->canBeAccepted())
                <button type="button"
                        class="btn btn-success btn-block mb-2"
                        onclick="showForceAcceptModal({{ $offer->id }})">
                    <i class="las la-check"></i> {{translate('Force Accept')}}
                </button>
                @endif
                @if($offer->status == 'pending')
                <button type="button"
                        class="btn btn-danger btn-block mb-2"
                        onclick="showForceRejectModal({{ $offer->id }})">
                    <i class="las la-times"></i> {{translate('Force Reject')}}
                </button>
                @endif
                <a href="{{ route('admin.auction-offers.index') }}" class="btn btn-light btn-block">
                    <i class="las la-arrow-left"></i> {{translate('Back to List')}}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Force Accept Modal -->
<div class="modal fade" id="force-accept-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Force Accept Offer') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="force-accept-form">
                    <input type="hidden" id="accept_offer_id" name="offer_id">
                    <div class="form-group">
                        <label for="accept_admin_reason">{{ translate('Admin Reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="accept_admin_reason" name="admin_reason" rows="3" maxlength="500" required placeholder="{{ translate('Provide a reason for force accepting this offer') }}"></textarea>
                        <small class="text-muted">{{ translate('Maximum 500 characters') }}</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-success" onclick="forceAcceptOffer()">{{ translate('Force Accept') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Force Reject Modal -->
<div class="modal fade" id="force-reject-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Force Reject Offer') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="force-reject-form">
                    <input type="hidden" id="reject_offer_id" name="offer_id">
                    <div class="form-group">
                        <label for="reject_admin_reason">{{ translate('Admin Reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject_admin_reason" name="admin_reason" rows="3" maxlength="500" required placeholder="{{ translate('Provide a reason for force rejecting this offer') }}"></textarea>
                        <small class="text-muted">{{ translate('Maximum 500 characters') }}</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-danger" onclick="forceRejectOffer()">{{ translate('Force Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function showForceAcceptModal(offerId) {
            $('#accept_offer_id').val(offerId);
            $('#accept_admin_reason').val('');
            $('#force-accept-modal').modal('show');
        }

        function showForceRejectModal(offerId) {
            $('#reject_offer_id').val(offerId);
            $('#reject_admin_reason').val('');
            $('#force-reject-modal').modal('show');
        }

        function forceAcceptOffer() {
            var offerId = $('#accept_offer_id').val();
            var adminReason = $('#accept_admin_reason').val();

            if (!adminReason || adminReason.trim() === '') {
                AIZ.plugins.notify('warning', '{{ translate('Please provide an admin reason') }}');
                return;
            }

            if (adminReason.length > 500) {
                AIZ.plugins.notify('warning', '{{ translate('Admin reason must not exceed 500 characters') }}');
                return;
            }

            $.ajax({
                url: '{{ route('admin.auction-offers.force-accept', ':id') }}'.replace(':id', offerId),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    admin_reason: adminReason
                },
                success: function(response) {
                    if (response.success) {
                        AIZ.plugins.notify('success', response.message);
                        $('#force-accept-modal').modal('hide');
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', response.message);
                    }
                },
                error: function(xhr) {
                    var message = xhr.responseJSON?.message || '{{ translate('Failed to force accept offer') }}';
                    AIZ.plugins.notify('danger', message);
                }
            });
        }

        function forceRejectOffer() {
            var offerId = $('#reject_offer_id').val();
            var adminReason = $('#reject_admin_reason').val();

            if (!adminReason || adminReason.trim() === '') {
                AIZ.plugins.notify('warning', '{{ translate('Please provide an admin reason') }}');
                return;
            }

            if (adminReason.length > 500) {
                AIZ.plugins.notify('warning', '{{ translate('Admin reason must not exceed 500 characters') }}');
                return;
            }

            $.ajax({
                url: '{{ route('admin.auction-offers.force-reject', ':id') }}'.replace(':id', offerId),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    admin_reason: adminReason
                },
                success: function(response) {
                    if (response.success) {
                        AIZ.plugins.notify('success', response.message);
                        $('#force-reject-modal').modal('hide');
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', response.message);
                    }
                },
                error: function(xhr) {
                    var message = xhr.responseJSON?.message || '{{ translate('Failed to force reject offer') }}';
                    AIZ.plugins.notify('danger', message);
                }
            });
        }
    </script>
@endsection
