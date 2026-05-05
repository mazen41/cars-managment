@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Auction Audit Logs')}}</h1>
        </div>
    </div>
</div>
<br>

<div class="card">
    <form class="" id="filter_logs" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Audit Log Entries') }}</h5>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="auction_room_id" name="auction_room_id" data-live-search="true" onchange="filter_logs()">
                    <option value="">{{ translate('All Rooms') }}</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @if (request('auction_room_id') == $room->id) selected @endif>
                            {{ $room->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="action" name="action" onchange="filter_logs()">
                    <option value="">{{ translate('All Actions') }}</option>
                    <option value="bid_placed" @if (request('action') == 'bid_placed') selected @endif>{{ translate('Bid Placed') }}</option>
                    <option value="bid_accepted" @if (request('action') == 'bid_accepted') selected @endif>{{ translate('Bid Accepted') }}</option>
                    <option value="bid_rejected" @if (request('action') == 'bid_rejected') selected @endif>{{ translate('Bid Rejected') }}</option>
                    <option value="item_started" @if (request('action') == 'item_started') selected @endif>{{ translate('Item Started') }}</option>
                    <option value="item_sold" @if (request('action') == 'item_sold') selected @endif>{{ translate('Item Sold') }}</option>
                    <option value="item_unsold" @if (request('action') == 'item_unsold') selected @endif>{{ translate('Item Unsold') }}</option>
                    <option value="offer_submitted" @if (request('action') == 'offer_submitted') selected @endif>{{ translate('Offer Submitted') }}</option>
                    <option value="offer_accepted" @if (request('action') == 'offer_accepted') selected @endif>{{ translate('Offer Accepted') }}</option>
                    <option value="offer_rejected" @if (request('action') == 'offer_rejected') selected @endif>{{ translate('Offer Rejected') }}</option>
                    <option value="room_started" @if (request('action') == 'room_started') selected @endif>{{ translate('Room Started') }}</option>
                    <option value="room_completed" @if (request('action') == 'room_completed') selected @endif>{{ translate('Room Completed') }}</option>
                    <option value="room_cancelled" @if (request('action') == 'room_cancelled') selected @endif>{{ translate('Room Cancelled') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="{{ translate('From Date') }}" onchange="filter_logs()">
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="{{ translate('To Date') }}" onchange="filter_logs()">
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @if(request('search')) value="{{ request('search') }}" @endif placeholder="{{ translate('Search User/IP') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th width="5%">{{ translate('ID') }}</th>
                            <th>{{ translate('Action') }}</th>
                            <th>{{ translate('Room') }}</th>
                            <th>{{ translate('Item') }}</th>
                            <th>{{ translate('User') }}</th>
                            <th>{{ translate('IP Address') }}</th>
                            <th>{{ translate('Timestamp') }}</th>
                            <th width="10%" class="text-right">{{ translate('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    <span class="badge badge-inline
                                        @if(str_contains($log->action, 'accepted') || str_contains($log->action, 'sold') || str_contains($log->action, 'started')) badge-success
                                        @elseif(str_contains($log->action, 'rejected') || str_contains($log->action, 'cancelled') || str_contains($log->action, 'unsold')) badge-danger
                                        @else badge-info
                                        @endif">
                                        {{ translate(ucwords(str_replace('_', ' ', $log->action))) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->auctionRoom)
                                        <a href="{{ route('admin.auction-rooms.show', $log->auctionRoom->id) }}">
                                            {{ $log->auctionRoom->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->auctionItem)
                                        <span class="text-muted">{{ $log->auctionItem->car->car_name ?? 'N/A' }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->user)
                                        <span class="text-muted">{{ $log->user->name }}</span>
                                        <br>
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                    @else
                                        <span class="text-muted">{{ translate('System') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->ip_address ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $log->created_at->format('d M Y H:i:s') }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="javascript:void(0)" class="btn btn-soft-primary btn-icon btn-circle btn-sm" onclick="showDetails({{ $log->id }})" title="{{ translate('View Details') }}">
                                        <i class="las la-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="aiz-pagination">
                {{ $logs->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="log-details-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Audit Log Details') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="log-details-content">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">{{ translate('Loading...') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function filter_logs() {
            $('#filter_logs').submit();
        }

        function showDetails(logId) {
            $('#log-details-modal').modal('show');
            $('#log-details-content').html('<div class="text-center py-4"><div class="spinner-border" role="status"><span class="sr-only">{{ translate('Loading...') }}</span></div></div>');

            $.get('{{ route('admin.auction-audit-logs.show', '') }}/' + logId, function(data) {
                if (data.success) {
                    var log = data.data;
                    var html = '<table class="table table-borderless">';
                    html += '<tr><td class="text-muted" width="30%">{{ translate('Log ID') }}</td><td>' + log.id + '</td></tr>';
                    html += '<tr><td class="text-muted">{{ translate('Action') }}</td><td><strong>' + log.action.replace(/_/g, ' ').toUpperCase() + '</strong></td></tr>';

                    if (log.auction_room) {
                        html += '<tr><td class="text-muted">{{ translate('Auction Room') }}</td><td>' + log.auction_room.name + '</td></tr>';
                    }

                    if (log.auction_item) {
                        html += '<tr><td class="text-muted">{{ translate('Auction Item') }}</td><td>' + (log.auction_item.car ? log.auction_item.car.car_name : 'N/A') + '</td></tr>';
                    }

                    if (log.user) {
                        html += '<tr><td class="text-muted">{{ translate('User') }}</td><td>' + log.user.name + ' (' + log.user.email + ')</td></tr>';
                    }

                    html += '<tr><td class="text-muted">{{ translate('IP Address') }}</td><td>' + (log.ip_address || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">{{ translate('Timestamp') }}</td><td>' + log.created_at + '</td></tr>';

                    if (log.details && Object.keys(log.details).length > 0) {
                        html += '<tr><td class="text-muted">{{ translate('Additional Details') }}</td><td><pre class="bg-light p-2 rounded">' + JSON.stringify(log.details, null, 2) + '</pre></td></tr>';
                    }

                    html += '</table>';
                    $('#log-details-content').html(html);
                } else {
                    $('#log-details-content').html('<div class="alert alert-danger">{{ translate('Failed to load log details') }}</div>');
                }
            }).fail(function() {
                $('#log-details-content').html('<div class="alert alert-danger">{{ translate('Failed to load log details') }}</div>');
            });
        }


    </script>
@endsection
