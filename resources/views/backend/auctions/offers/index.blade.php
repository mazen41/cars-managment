@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Auction Offers')}}</h1>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row gutters-10 mb-3">
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-handshake la-3x text-primary mb-2"></i>
                <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Total Offers') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-clock la-3x text-info mb-2"></i>
                <h4 class="mb-0">{{ $stats['pending'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Pending') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-check-circle la-3x text-success mb-2"></i>
                <h4 class="mb-0">{{ $stats['accepted'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Accepted') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-times-circle la-3x text-danger mb-2"></i>
                <h4 class="mb-0">{{ $stats['rejected'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Rejected') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-hourglass-end la-3x text-secondary mb-2"></i>
                <h4 class="mb-0">{{ $stats['expired'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Expired') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-undo la-3x text-warning mb-2"></i>
                <h4 class="mb-0">{{ $stats['withdrawn'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Withdrawn') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Data Table -->
<div class="card">
    <form class="" id="sort_offers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Auction Offers') }}</h5>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status" name="status" onchange="sort_offers()">
                    <option value="">{{ translate('All Status') }}</option>
                    <option value="pending" @if (request('status') == 'pending') selected @endif>{{ translate('Pending') }}</option>
                    <option value="accepted" @if (request('status') == 'accepted') selected @endif>{{ translate('Accepted') }}</option>
                    <option value="rejected" @if (request('status') == 'rejected') selected @endif>{{ translate('Rejected') }}</option>
                    <option value="expired" @if (request('status') == 'expired') selected @endif>{{ translate('Expired') }}</option>
                    <option value="withdrawn" @if (request('status') == 'withdrawn') selected @endif>{{ translate('Withdrawn') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="auction_room_id" name="auction_room_id" onchange="sort_offers()">
                    <option value="">{{ translate('All Rooms') }}</option>
                    @foreach($auctionRooms as $room)
                        <option value="{{ $room->id }}" @if (request('auction_room_id') == $room->id) selected @endif>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="{{ translate('From Date') }}" onchange="sort_offers()">
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="{{ translate('To Date') }}" onchange="sort_offers()">
            </div>
        </div>

        <div class="card-header row gutters-5">
            <div class="col-md-2">
                <input type="number" class="form-control form-control-sm" id="amount_min" name="amount_min" value="{{ request('amount_min') }}" placeholder="{{ translate('Min Amount') }}" onchange="sort_offers()">
            </div>

            <div class="col-md-2">
                <input type="number" class="form-control form-control-sm" id="amount_max" name="amount_max" value="{{ request('amount_max') }}" placeholder="{{ translate('Max Amount') }}" onchange="sort_offers()">
            </div>

            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @if(request('search')) value="{{ request('search') }}" @endif placeholder="{{ translate('Search by car, buyer, or seller') }}">
                </div>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary">{{ translate('Search') }}</button>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ translate('Offer Amount') }}</th>
                        <th data-breakpoints="md">{{ translate('Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Car Details') }}</th>
                        <th data-breakpoints="md">{{ translate('Buyer') }}</th>
                        <th data-breakpoints="md">{{ translate('Seller') }}</th>
                        <th data-breakpoints="md">{{ translate('Created') }}</th>
                        <th width="15%" class="text-right">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td>
                                <strong>{{ format_price($offer->amount) }}</strong>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $offer->auctionItem->car->name }}</strong>
                                </div>
                                <small class="text-muted">
                                    {{ $offer->auctionItem->car->carBrand->name ?? 'N/A' }} - 
                                    {{ $offer->auctionItem->car->carModel->name ?? 'N/A' }}
                                </small>
                            </td>
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
                                @if($offer->status == 'pending' && $offer->canBeAccepted())
                                    <a href="javascript:void(0)" 
                                       class="btn btn-soft-success btn-icon btn-circle btn-sm" 
                                       onclick="showForceAcceptModal({{ $offer->id }})" 
                                       title="{{ translate('Force Accept') }}">
                                        <i class="las la-check"></i>
                                    </a>
                                @endif
                                @if($offer->status == 'pending')
                                    <a href="javascript:void(0)" 
                                       class="btn btn-soft-danger btn-icon btn-circle btn-sm" 
                                       onclick="showForceRejectModal({{ $offer->id }})" 
                                       title="{{ translate('Force Reject') }}">
                                        <i class="las la-times"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">{{ translate('No offers found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $offers->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
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
        function sort_offers() {
            $('#sort_offers').submit();
        }

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
                url: '{{ route('admin.auction-offers.force-accept', '') }}/' + offerId,
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
                url: '{{ route('admin.auction-offers.force-reject', '') }}/' + offerId,
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
