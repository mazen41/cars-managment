@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Auction Rooms')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.auction-rooms.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Create New Room')}}</span>
            </a>
        </div>
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_rooms" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Auction Rooms') }}</h5>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status" name="status" onchange="sort_rooms()">
                    <option value="">{{ translate('All Status') }}</option>
                    <option value="draft" @if (request('status') == 'draft') selected @endif>{{ translate('Draft') }}</option>
                    <option value="scheduled" @if (request('status') == 'scheduled') selected @endif>{{ translate('Scheduled') }}</option>
                    <option value="active" @if (request('status') == 'active') selected @endif>{{ translate('Active') }}</option>
                    <option value="completed" @if (request('status') == 'completed') selected @endif>{{ translate('Completed') }}</option>
                    <option value="cancelled" @if (request('status') == 'cancelled') selected @endif>{{ translate('Cancelled') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="{{ translate('From Date') }}" onchange="sort_rooms()">
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="{{ translate('To Date') }}" onchange="sort_rooms()">
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @if(request('search')) value="{{ request('search') }}" @endif placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ translate('Name') }}</th>
                        <th data-breakpoints="md">{{ translate('Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Items') }}</th>
                        <th data-breakpoints="md">{{ translate('Commission') }}</th>
                        <th data-breakpoints="md">{{ translate('Scheduled Start') }}</th>
                        <th data-breakpoints="md">{{ translate('Started At') }}</th>
                        <th data-breakpoints="md">{{ translate('Created By') }}</th>
                        <th data-breakpoints="md">{{ translate('Created') }}</th>
                        <th width="15%" class="text-right">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rooms as $room)
                        <tr>
                            <td>
                                <span class="text-muted">{{ $room->name }}</span>
                            </td>
                            <td>
                                @if($room->status == 'draft')
                                    <span class="badge badge-inline badge-secondary">{{ translate('Draft') }}</span>
                                @elseif($room->status == 'scheduled')
                                    <span class="badge badge-inline badge-info">{{ translate('Scheduled') }}</span>
                                @elseif($room->status == 'active')
                                    <span class="badge badge-inline badge-success">{{ translate('Active') }}</span>
                                @elseif($room->status == 'completed')
                                    <span class="badge badge-inline badge-primary">{{ translate('Completed') }}</span>
                                @else
                                    <span class="badge badge-inline badge-danger">{{ translate('Cancelled') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $room->getTotalItems() }} {{ translate('items') }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $room->commission_percentage }}%</span>
                            </td>
                            <td>
                                @if($room->scheduled_start_at)
                                    <span class="text-muted">{{ $room->scheduled_start_at->format('d M Y H:i') }}</span>
                                @else
                                    <span class="text-muted">{{ translate('N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($room->started_at)
                                    <span class="text-muted">{{ $room->started_at->format('d M Y H:i') }}</span>
                                @else
                                    <span class="text-muted">{{ translate('N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($room->creator)
                                    <span class="text-muted">{{ $room->creator->name }}</span>
                                @else
                                    <span class="text-muted">{{ translate('N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $room->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="text-right">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('admin.auction-rooms.show', $room->id) }}" title="{{ translate('View Details') }}">
                                    <i class="las la-eye"></i>
                                </a>
                                @if($room->status == 'completed')
                                    <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="{{ route('admin.auction-rooms.report', $room->id) }}" title="{{ translate('View Report') }}">
                                        <i class="las la-chart-bar"></i>
                                    </a>
                                @endif
                                @if($room->status == 'draft')
                                    <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('admin.auction-rooms.edit', $room->id) }}" title="{{ translate('Edit') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                @endif
                                @if($room->status == 'draft' || $room->status == 'scheduled')
                                    <a href="javascript:void(0)" class="btn btn-soft-success btn-icon btn-circle btn-sm" onclick="startRoom({{ $room->id }})" title="{{ translate('Start Room') }}">
                                        <i class="las la-play"></i>
                                    </a>
                                @endif
                                @if($room->status == 'active' || $room->status == 'scheduled')
                                    <a href="javascript:void(0)" class="btn btn-soft-warning btn-icon btn-circle btn-sm" onclick="showCancelModal({{ $room->id }})" title="{{ translate('Cancel Room') }}">
                                        <i class="las la-ban"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $rooms->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
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
                    <input type="hidden" id="cancel_room_id" name="room_id">
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
        function sort_rooms() {
            $('#sort_rooms').submit();
        }
        const startUrl = '{{ route('admin.auction-rooms.start', ':id') }}';
        function startRoom(id) {
            let url = startUrl.replace(':id', id);
            if (confirm('{{ translate('Are you sure you want to start this auction room?') }}')) {
                $.post(url, {
                    _token: '{{ csrf_token() }}'
                }, function(data) {
                    if (data.success) {
                        AIZ.plugins.notify('success', data.message);
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                    }
                }).fail(function(xhr) {
                    AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to start room') }}');
                });
            }
        }

        function showCancelModal(id) {
            $('#cancel_room_id').val(id);
            $('#cancel_reason').val('');
            $('#cancel-room-modal').modal('show');
        }

        function cancelRoom() {
            var roomId = $('#cancel_room_id').val();
            var reason = $('#cancel_reason').val();

            if (!reason) {
                AIZ.plugins.notify('warning', '{{ translate('Please provide a cancellation reason') }}');
                return;
            }
            const cancelUrl = '{{ route('admin.auction-rooms.cancel', ':id') }}';
            $.post(cancelUrl.replace(':id', roomId), {
                _token: '{{ csrf_token() }}',
                reason: reason
            }, function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    $('#cancel-room-modal').modal('hide');
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            }).fail(function(xhr) {
                AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to cancel room') }}');
            });
        }
    </script>
@endsection
