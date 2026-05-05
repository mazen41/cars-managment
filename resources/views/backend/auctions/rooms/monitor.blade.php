@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ translate('Live Auction Monitor') }} - {{ $room->name }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.auction-rooms.show', $room->id) }}" class="btn btn-circle btn-light">
                <span>{{translate('Back to Details')}}</span>
            </a>
        </div>
    </div>
</div>

<!-- Live Statistics -->
<div class="row gutters-10 mb-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center py-3">
                <h4 class="mb-0" id="current-item-number">{{ $currentItem ? $currentItem->car->car_name ?? 'N/A' : 'N/A' }}</h4>
                <small class="text-muted">{{ translate('Current Item') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <h4 class="mb-0 text-primary" id="current-price">${{ format_price($currentItem->current_price ?? 0) }}</h4>
                <small class="text-muted">{{ translate('Current Price') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <h4 class="mb-0" id="timer-display">--:--:--</h4>
                <small class="text-muted">{{ translate('Time Remaining') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Current Item Display -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Current Item') }}</h5>
            </div>
            <div class="card-body" id="current-item-display">
                @if($currentItem)
                    <div class="row">
                        <div class="col-md-4">
                            <img id="current-item-image" src="{{ $currentItem->car->main_photo_url ?? static_asset('assets/img/placeholder.jpg') }}" alt="Car" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4 id="item-car-name">{{ $currentItem->car->car_name ?? 'N/A' }}</h4>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">{{ translate('VIN') }}</td>
                                    <td id="item-vin">{{ $currentItem->car->vin ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">{{ translate('Starting Price') }}</td>
                                    <td id="item-starting-price">{{ format_price($currentItem->starting_price) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">{{ translate('Current Price') }}</td>
                                    <td><strong id="item-current-price" class="text-success">{{ format_price($currentItem->current_price ?? $currentItem->starting_price) }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">{{ translate('Total Bids') }}</td>
                                    <td id="item-total-bids">{{ $currentItem->total_bids }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">{{ translate('Current Winner') }}</td>
                                    <td id="item-current-winner">
                                        @if($currentItem->currentWinner)
                                            {{ $currentItem->currentWinner->name }}
                                        @else
                                            {{ translate('No bids yet') }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            <div id="current-item-route">
                                <a href="{{ route('admin.cars.show', $currentItem->car->id) }}" class="btn btn-primary btn-sm mt-2" target="_blank">
                                    <i class="las la-eye"></i> {{ translate('View Car Details') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="las la-gavel la-5x text-muted mb-3"></i>
                        <p class="text-muted">{{ translate('No active item') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-danger" onclick="showCancelModal()">
                        <i class="las la-ban"></i> {{ translate('Cancel Room') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bid Activity Feed -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Bid Activity Feed') }}</h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                <div id="bid-activity-feed">
                    @if ($currentItem->bids)
                        @foreach ($currentItem->bids as $bid)
                             <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $bid->bidder->name }}</strong>
                                    <span class="text-success">{{ single_price($bid->amount) }}</span>
                                </div>
                                <small class="text-muted">{{ $bid->created_at->format('H:i:s') }}</small>
                                @if($bid->time_extended_by)
                                    <small class="text-info d-block">+{{ $bid->time_extended_by }}</small>
                                @endif
                            </div>
                        @endforeach
                    @else
                    <div class="text-center text-muted py-3">
                        <small>{{ translate('Waiting for bids...') }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Room Modal -->
<div class="modal fade" id="cancel-room-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Cancel Auction Room') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cancel-room-form">
                    <div class="form-group">
                        <label for="cancel_reason">{{ translate('Cancellation Reason') }}</label>
                        <textarea class="form-control" id="cancel_reason" name="reason" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Close') }}</button>
                <button type="button" class="btn btn-danger" onclick="cancelRoom()">{{ translate('Cancel Room') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        var roomId = {{ $room->id }};
        var currentItemId = {{ $currentItem->id ?? 'null' }};
        var timerInterval = null;
        var endsAt = null;

        // Initialize Echo for WebSocket
        @if($currentItem)
        endsAt = new Date('{{ $currentItem->ends_at }}');
        @endif
         document.addEventListener('DOMContentLoaded', function() {
        // Subscribe to auction room channel
        window.Echo.channel('auction-room.' + roomId)
            .listen('.AuctionRoomStarted', (e) => {
                console.log('Room started:', e);
                AIZ.plugins.notify('info', '{{ translate('Auction room started') }}');
                location.reload();
            })
            .listen('.AuctionItemStarted', (e) => {
                console.log('Item started:', e);
                updateCurrentItem(e);
                AIZ.plugins.notify('info', '{{ translate('New item started') }}');
            })
            .listen('.ItemSold', (e) => {
                console.log('Item sold:', e);
                AIZ.plugins.notify('success', '{{ translate('Item sold!') }}');
                // Wait a moment then reload to show next item
                setTimeout(() => location.reload(), 2000);
            })
            .listen('.ItemUnsold', (e) => {
                console.log('Item unsold:', e);
                AIZ.plugins.notify('warning', '{{ translate('Item did not sell') }}');
                setTimeout(() => location.reload(), 2000);
            })
            .listen('.AuctionRoomCompleted', (e) => {
                console.log('Room completed:', e);
                AIZ.plugins.notify('success', '{{ translate('Auction room completed') }}');
                setTimeout(() => window.location.href = '{{ route('admin.auction-rooms.show', $room->id) }}', 2000);
            });

        // Subscribe to current item channel if exists
        @if($currentItem)
        window.Echo.channel('auction-item.' + currentItemId)
            .listen('.BidAccepted', (e) => {
                console.log('Bid accepted:', e);
                updateBidInfo(e);
                addBidToFeed(e);
            })
            .listen('.TimerUpdate', (e) => {
                console.log('Timer update:', e);
                endsAt = new Date(e.ends_at);
            });
        @endif

        function updateCurrentItem(data) {
            currentItemId = data.item_id;
            $('#item-car-name').text(data.car.name || 'N/A');
            $('#item-vin').text(data.car.vin || 'N/A');
            $('#item-starting-price').text(formatPrice(data.starting_price));
            $('#item-current-price').text(formatPrice(data.current_price));
            $('#item-total-bids').text('0');
            $('#item-current-winner').text('{{ translate('No bids yet') }}');
            $('#current-item-number').text(data.sequence_order || '-');
            $('#current-price').text(formatPrice(data.current_price));
            $('#current-item-route a').attr('href', '/admin/cars/' + data.car.id);
            $('#current-item-image').attr('src', data.car.main_photo_url || '{{ static_asset('assets/img/placeholder.jpg') }}');
            endsAt = new Date(data.ends_at);
            startTimerCountdown();

            // Clear bid feed
            $('#bid-activity-feed').html('<div class="text-center text-muted py-3"><small>{{ translate('Waiting for bids...') }}</small></div>');
        }

        function updateBidInfo(data) {
            $('#item-current-price').text(formatPrice(data.current_price));
            $('#item-total-bids').text(data.total_bids);
            $('#item-current-winner').text(data.bidder_name);
            $('#current-price').text(formatPrice(data.current_price));

            // Update timer with extended time
            endsAt = new Date(data.ends_at);
        }

        function addBidToFeed(data) {
            var feedHtml = $('#bid-activity-feed').html();
            if (feedHtml.includes('Waiting for bids')) {
                $('#bid-activity-feed').html('');
            }

            var bidHtml = `
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>${data.bidder_name}</strong>
                        <span class="text-success">${formatPrice(data.amount)}</span>
                    </div>
                    <small class="text-muted">${new Date().toLocaleTimeString()}</small>
                    ${data.time_extended_by ? '<small class="text-info d-block">+' + data.time_extended_by + 's</small>' : ''}
                </div>
            `;

            $('#bid-activity-feed').prepend(bidHtml);
        }

        function startTimerCountdown() {
            if (timerInterval) {
                clearInterval(timerInterval);
            }

            timerInterval = setInterval(function() {
                if (!endsAt) return;

                var now = new Date();
                var diff = endsAt - now;

                if (diff <= 0) {
                    $('#timer-display').text('00:00:00');
                    clearInterval(timerInterval);
                    return;
                }

                var hours = Math.floor(diff / 3600000);
                var minutes = Math.floor((diff % 3600000) / 60000);
                var seconds = Math.floor((diff % 60000) / 1000);
                
                $('#timer-display').text(
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0')
                );
            }, 1000);
        }

        function updateTimer(endsAtTimestamp) {
            var now = new Date();
            var endTime = new Date(endsAtTimestamp);
            var diff = endTime - now;

            if (diff <= 0) {
                $('#timer-display').text('00:00:00');
                return;
            }

            var hours = Math.floor(diff / 3600000);
            var minutes = Math.floor((diff % 3600000) / 60000);
            var seconds = Math.floor((diff % 60000) / 1000);
            
            $('#timer-display').text(
                String(hours).padStart(2, '0') + ':' + 
                String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0')
            );
        }

        function formatPrice(amount) {
            return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                style: 'currency',
                currency: '{{ currency_code() }}'
            }).format(amount);
        }

        function showCancelModal() {
            $('#cancel_reason').val('');
            $('#cancel-room-modal').modal('show');
        }

        function cancelRoom() {
            var reason = $('#cancel_reason').val();

            if (!reason) {
                AIZ.plugins.notify('warning', '{{ translate('Please provide a cancellation reason') }}');
                return;
            }

            $.post('{{ route('admin.auction-rooms.cancel', $room->id) }}', {
                _token: '{{ csrf_token() }}',
                reason: reason
            }, function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    $('#cancel-room-modal').modal('hide');
                    setTimeout(() => window.location.href = '{{ route('admin.auction-rooms.show', $room->id) }}', 1000);
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            }).fail(function(xhr) {
                AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to cancel room') }}');
            });
        }

        function pauseAuction() {
            AIZ.plugins.notify('info', '{{ translate('Pause functionality not yet implemented') }}');
        }

        function skipItem() {
            AIZ.plugins.notify('info', '{{ translate('Skip item functionality not yet implemented') }}');
        }

        // Start timer countdown on page load
        @if($currentItem)
        startTimerCountdown();
        @endif

        });
    </script>
@endsection
