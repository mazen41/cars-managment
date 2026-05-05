@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ $room->name }}</h1>
        </div>
        <div class="col text-right">
            @if($room->status == 'draft')
                <a href="{{ route('admin.auction-rooms.edit', $room->id) }}" class="btn btn-circle btn-info">
                    <span>{{translate('Edit Room')}}</span>
                </a>
            @endif
            @if($room->status == 'draft' && !is_null($room->scheduled_start_at) && $room->auctionItems->count() > 0)
                <button onclick="scheduleRoom()" class="btn btn-circle btn-info">
                    <span>{{translate('Schedule Room')}}</span>
                </button>
            @endif
            @if($room->status == 'draft' || $room->status == 'scheduled')
                <button onclick="startRoom()" class="btn btn-circle btn-success">
                    <span>{{translate('Start Room')}}</span>
                </button>
            @endif

            @if($room->status == 'active')
                <a href="{{ route('admin.auction-rooms.monitor', $room->id) }}" class="btn btn-circle btn-primary">
                    <span>{{translate('Live Monitor')}}</span>
                </a>
            @endif
            @if($room->status == 'completed')
                <a  href="{{ route('admin.auction-rooms.report', $room->id) }}" class="btn btn-circle btn-primary">
                    <span>{{translate('View Report')}}</span>
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Room Statistics -->
<div class="row gutters-10">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-gavel la-3x text-primary mb-3"></i>
                <h3 class="mb-0">{{ $statistics['total_items'] ?? 0 }}</h3>
                <p class="text-muted mb-0">{{ translate('Total Items') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-check-circle la-3x text-success mb-3"></i>
                <h3 class="mb-0">{{ $statistics['sold_items'] ?? 0 }}</h3>
                <p class="text-muted mb-0">{{ translate('Sold Items') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-dollar-sign la-3x text-info mb-3"></i>
                <h3 class="mb-0">{{ format_price($statistics['total_sales'] ?? 0) }}</h3>
                <p class="text-muted mb-0">{{ translate('Total Sales') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-percentage la-3x text-warning mb-3"></i>
                <h3 class="mb-0">{{ format_price($statistics['total_commission'] ?? 0) }}</h3>
                <p class="text-muted mb-0">{{ translate('Total Commission') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Room Details -->
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Room Details') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted">{{ translate('Status') }}</td>
                        <td class="text-right">
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
                    </tr>
                    {{-- <tr>
                        <td class="text-muted">{{ translate('Currency') }}</td>
                        <td class="text-right">{{ $room->currency->name ?? 'N/A' }}</td>
                    </tr> --}}
                    <tr>
                        <td class="text-muted">{{ translate('Commission') }}</td>
                        <td class="text-right">{{ $room->commission_percentage }}%</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Bid Increment') }}</td>
                        <td class="text-right">
                            @if($room->bid_increment_type == 'percentage')
                                {{ $room->bid_increment_value }}%
                            @else
                                {{ format_price($room->bid_increment_value) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Base Timer') }}</td>
                        <td class="text-right">{{ $room->base_timer_seconds }}s</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Extension Time') }}</td>
                        <td class="text-right">{{ $room->extension_seconds }}s</td>
                    </tr>
                    {{-- <tr>
                        <td class="text-muted">{{ translate('Insurance Deposit') }}</td>
                        <td class="text-right">{{ format_price($room->insurance_deposit_amount) }}</td>
                    </tr> --}}
                    <tr>
                        <td class="text-muted">{{ translate('Scheduled Start') }}</td>
                        <td class="text-right">
                            @if($room->scheduled_start_at)
                                {{ $room->scheduled_start_at->format('d M Y H:i') }}
                            @else
                                {{ translate('Manual Start') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Started At') }}</td>
                        <td class="text-right">
                            @if($room->started_at)
                                {{ $room->started_at->format('d M Y H:i') }}
                            @else
                                {{ translate('Not Started') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Created By') }}</td>
                        <td class="text-right">{{ $room->creator->name ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0 h6">{{ translate('Auction Items') }}</h5>
                    </div>
                    @if($room->status == 'draft')
                        <div class="col-auto">
                            <button class="btn btn-sm btn-primary" onclick="showAddItemModal()">
                                <i class="las la-plus"></i> {{ translate('Add Item') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($room->auctionItems->count() > 0)
                    <div class="table-responsive">
                        <table class="table aiz-table mb-0" id="items-table">
                            <thead>
                                <tr>
                                    @if($room->status == 'draft')
                                        <th width="30">{{ translate('Order') }}</th>
                                    @endif
                                    <th>{{ translate('Car') }}</th>
                                    <th>{{ translate('Starting Price') }}</th>
                                    <th>{{ translate('Current Price') }}</th>
                                    <th>{{ translate('Status') }}</th>
                                    <th>{{ translate('Bids') }}</th>
                                    <th class="text-right">{{ translate('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody @if($room->status == 'draft') id="sortable-items" @endif>
                                @foreach($room->auctionItems->sortBy('sequence_order') as $item)
                                    <tr data-id="{{ $item->id }}">
                                        @if($room->status == 'draft')
                                            <td>
                                                <i class="las la-grip-vertical handle" style="cursor: move;"></i>
                                                {{ $item->sequence_order }}
                                            </td>
                                        @endif
                                        <td>
                                            <div class="row gutters-5 w-200px">
                                                <div class="col-auto">
                                                    <img src="{{ $item->car->main_photo_url ?? static_asset('assets/img/placeholder.jpg') }}" alt="Car" class="size-50px img-fit">
                                                </div>
                                                <div class="col text-truncate">
                                                    <span class="text-muted text-truncate-2">{{ $item->car->car_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ format_price($item->starting_price) }}</td>
                                        <td>
                                            @if($item->current_price)
                                                {{ format_price($item->current_price) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->status == 'pending')
                                                <span class="badge badge-inline badge-secondary">{{ translate('Pending') }}</span>
                                            @elseif($item->status == 'active')
                                                <span class="badge badge-inline badge-success">{{ translate('Active') }}</span>
                                            @elseif($item->status == 'sold')
                                                <span class="badge badge-inline badge-primary">{{ translate('Sold') }}</span>
                                            @elseif($item->status == 'unsold')
                                                <span class="badge badge-inline badge-warning">{{ translate('Unsold') }}</span>
                                            @else
                                                <span class="badge badge-inline badge-info">{{ translate(ucfirst($item->status)) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->total_bids }}</td>
                                        <td class="text-right">
                                            @if($room->status == 'draft' && $item->status == 'pending')
                                                <a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm" onclick="removeItem({{ $item->id }})" title="{{ translate('Remove') }}">
                                                    <i class="las la-trash"></i>
                                                </a>
                                            @endif
                                            @if ($room->status == 'scheduled' && $item->has('auctionOffers'))
                                                <a class="btn btn-primary btn-icon btn-circle btn-sm" href="{{route('admin.auction-offers.index', ['auction_item_id'=> $item->id])}}" title="{{translate('View offers')}}"><i class="las la-handshake"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted">{{ translate('No items added yet') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="add-item-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Add Item to Auction') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-item-form">
                    <div class="form-group">
                        <label>{{ translate('Select Car') }} <span class="text-danger">*</span></label>
                        <select class="form-control aiz-selectpicker" name="car_id" id="car_id" data-live-search="true" required>
                            <option value="">{{ translate('Select a car') }}</option>
                            @foreach($availableCars as $car)
                                <option value="{{ $car->id }}">
                                    {{ $car->car_name }} - {{ $car->vin ?? 'No VIN' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Starting Price') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="starting_price" min="0" step="0.01" required>
                    </div>
                    {{-- <div class="form-group">
                        <label>{{ translate('Reserve Price') }}</label>
                        <input type="number" class="form-control" name="reserve_price" min="0" step="0.01">
                        <small class="text-muted">{{ translate('Minimum price to sell (optional)') }}</small>
                    </div> --}}
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="addItem()">{{ translate('Add Item') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script type="text/javascript">
        var roomId = {{ $room->id }};
        var roomStatus = '{{ $room->status }}';

        function startRoom() {
            if (confirm('{{ translate('Are you sure you want to start this auction room?') }}')) {
                $.post('{{ route('admin.auction-rooms.start', $room->id) }}', {
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

        function scheduleRoom() {
            if (confirm('{{ translate('Are you sure you want to schedule this auction room?') }}')) {
                $.post('{{ route('admin.auction-rooms.set-scheduled', $room->id) }}', {
                    _token: '{{ csrf_token() }}'
                }, function(data) {
                    if (data.success) {
                        AIZ.plugins.notify('success', data.message);
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                    }
                }).fail(function(xhr) {
                    AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to schedule room') }}');
                });
            }
        }

        function showAddItemModal() {
            $('#add-item-modal').modal('show');
        }

        function addItem() {
            var formData = {
                _token: '{{ csrf_token() }}',
                car_id: $('#car_id').val(),
                starting_price: $('input[name="starting_price"]').val(),
                reserve_price: $('input[name="reserve_price"]').val()
            };

            if (!formData.car_id || !formData.starting_price) {
                AIZ.plugins.notify('warning', '{{ translate('Please fill all required fields') }}');
                return;
            }

            $.post('{{ route('admin.auction-rooms.add-item', $room->id) }}', formData, function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    $('#add-item-modal').modal('hide');
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            }).fail(function(xhr) {
                AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to add item') }}');
            });
        }

        function removeItem(itemId) {
            if (confirm('{{ translate('Are you sure you want to remove this item?') }}')) {
                $.ajax({
                    url: '{{ route('admin.auction-rooms.remove-item', ['auctionRoom' => $room->id, 'itemId' => '__ITEM_ID__']) }}'.replace('__ITEM_ID__', itemId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        if (data.success) {
                            AIZ.plugins.notify('success', data.message);
                            location.reload();
                        } else {
                            AIZ.plugins.notify('danger', data.message);
                        }
                    },
                    error: function(xhr) {
                        AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to remove item') }}');
                    }
                });
            }
        }

        // Get starting price for chosen car
        $("#car_id").on('change', function() {
            var carId = $(this).val();
            const starting_price_input = $('input[name="starting_price"]');
            if (carId) {
                starting_price_input.attr('disabled', true);
                starting_price_input.val('{{ translate('Loading...') }}');
                $.get('{{ route('admin.auction-rooms.get-starting-price') }}', { car_id: carId }, function(data) {
                    if (data.success) {
                        starting_price_input.val(data.starting_price);
                        starting_price_input.attr('disabled', false);
                    } else {
                        starting_price_input.val('');
                        starting_price_input.attr('disabled', false);
                    }
                });
            } else {
                starting_price_input.val('');
                starting_price_input.attr('disabled', false);
            }
        });

        // Initialize sortable for draft rooms
        @if($room->status == 'draft' && $room->auctionItems->count() > 0)
        var sortable = Sortable.create(document.getElementById('sortable-items'), {
            handle: '.handle',
            animation: 150,
            onEnd: function(evt) {
                var items = [];
                $('#sortable-items tr').each(function(index) {
                    items.push({
                        id: $(this).data('id'),
                        sequence_order: index + 1
                    });
                });

                $.ajax({
                    url: '{{ route('admin.auction-rooms.reorder-items', $room->id) }}',
                    type: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        items: items
                    },
                    success: function(data) {
                        if (data.success) {
                            AIZ.plugins.notify('success', data.message);
                            location.reload();
                        } else {
                            AIZ.plugins.notify('danger', data.message);
                        }
                    },
                    error: function(xhr) {
                        AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to reorder items') }}');
                    }
                });
            }
        });
        @endif
    </script>
@endsection
