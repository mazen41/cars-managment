<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Auction Items') }} ({{ $items->count() }})</h5>
    </div>
    <div class="card-body">
        @if($items->count() > 0)
            <div class="table-responsive">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Order') }}</th>
                            <th>{{ translate('Car Details') }}</th>
                            <th>{{ translate('Pricing') }}</th>
                            <th>{{ translate('Timing') }}</th>
                            <th>{{ translate('Bidding Stats') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Winner') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <!-- Sequence Order -->
                                <td>
                                    <span class="badge badge-inline badge-secondary">{{ $item->sequence_order }}</span>
                                </td>

                                <!-- Car Details -->
                                <td>
                                    <div class="row gutters-5 w-250px">
                                        <div class="col-auto">
                                            <img src="{{ $item->car->main_photo_url ?? static_asset('assets/img/placeholder.jpg') }}"
                                                 alt="Car"
                                                 class="size-60px img-fit rounded">
                                        </div>
                                        <div class="col">
                                            <div class="text-truncate-2">
                                                <strong>{{ $item->car->car_name ?? 'N/A' }}</strong>
                                            </div>
                                            <div class="text-muted small">
                                                @if($item->car && $item->car->carBrand)
                                                    {{ $item->car->carBrand->name }}
                                                @endif
                                                @if($item->car && $item->car->carModel)
                                                    {{ $item->car->carModel->name }}
                                                @endif
                                            </div>
                                            @if($item->car && $item->car->year)
                                                <div class="text-muted small">{{ translate('Year:') }} {{ $item->car->year }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Pricing -->
                                <td>
                                    <div class="small">
                                        <div><strong>{{ translate('Starting:') }}</strong> {{ format_price($item->starting_price) }}</div>
                                        @if($item->reserve_price)
                                            <div class="text-muted">{{ translate('Reserve:') }} {{ format_price($item->reserve_price) }}</div>
                                        @endif
                                        @if($item->current_price)
                                            <div class="text-success"><strong>{{ translate('Final:') }}</strong> {{ format_price($item->current_price) }}</div>
                                        @else
                                            <div class="text-muted">{{ translate('Final:') }} -</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Timing -->
                                <td>
                                    <div class="small">
                                        @if($item->started_at)
                                            <div>{{ translate('Start:') }} {{ $item->started_at->format('H:i:s') }}</div>
                                        @endif
                                        @if($item->ended_at)
                                            <div>{{ translate('End:') }} {{ $item->ended_at->format('H:i:s') }}</div>
                                        @endif
                                        @if($item->started_at && $item->ended_at)
                                            <div class="text-muted">
                                                {{ translate('Duration:') }} {{ gmdate('i:s', $item->started_at->diffInSeconds($item->ended_at)) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Bidding Statistics -->
                                <td>
                                    <div class="small">
                                        <div><i class="las la-hand-paper"></i> {{ $item->total_bids }} {{ translate('bids') }}</div>
                                        <div><i class="las la-clock"></i> {{ $item->total_extensions }} {{ translate('extensions') }}</div>
                                        <div><i class="las la-users"></i> {{ $item->bids->pluck('bidder_id')->unique()->count() }} {{ translate('bidders') }}</div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td>
                                    @if($item->status == 'sold')
                                        <span class="badge badge-inline badge-success">{{ translate('Sold') }}</span>
                                    @elseif($item->status == 'unsold')
                                        <span class="badge badge-inline badge-warning">{{ translate('Unsold') }}</span>
                                    @elseif($item->status == 'active')
                                        <span class="badge badge-inline badge-info">{{ translate('Active') }}</span>
                                    @else
                                        <span class="badge badge-inline badge-secondary">{{ translate(ucfirst($item->status)) }}</span>
                                    @endif
                                </td>

                                <!-- Winner -->
                                <td>
                                    @if($item->currentWinner)
                                        <div class="small">
                                            <div><strong>{{ $item->currentWinner->name }}</strong></div>
                                            <div class="text-muted">{{ $item->currentWinner->email }}</div>
                                            @if($item->current_price)
                                                <div class="text-success">{{ format_price($item->current_price) }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">{{ translate('N/A') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="las la-gavel la-3x text-muted mb-3"></i>
                <p class="text-muted">{{ translate('No auction items found') }}</p>
            </div>
        @endif
    </div>
</div>
