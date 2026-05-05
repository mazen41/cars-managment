<!-- Timing Statistics Cards -->
<div class="row gutters-10">
    <!-- Total Duration -->
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-clock la-3x text-primary mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ gmdate('H:i:s', $timing['total_duration']) }}</h3>
                <p class="text-muted mb-0">{{ translate('Total Duration') }}</p>
                <small class="text-muted">{{ translate('Hours:Minutes:Seconds') }}</small>
            </div>
        </div>
    </div>

    <!-- Average Time Per Item -->
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-hourglass-half la-3x text-info mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ gmdate('i:s', $timing['average_time_per_item']) }}</h3>
                <p class="text-muted mb-0">{{ translate('Avg Time Per Item') }}</p>
                <small class="text-muted">{{ translate('Minutes:Seconds') }}</small>
            </div>
        </div>
    </div>

    <!-- Total Extensions -->
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-plus-circle la-3x text-warning mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ $timing['total_extensions'] }}</h3>
                <p class="text-muted mb-0">{{ translate('Total Extensions') }}</p>
                <small class="text-muted">{{ translate('Across all items') }}</small>
            </div>
        </div>
    </div>

    <!-- Items With Extensions -->
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-list-ol la-3x text-success mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ $timing['items_with_extensions'] }}</h3>
                <p class="text-muted mb-0">{{ translate('Items With Extensions') }}</p>
                <small class="text-muted">{{ translate('Had timer extensions') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Scheduled vs Actual Start Time -->
<div class="row mt-4">
    <div class="col-lg-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Scheduled vs Actual Start Time') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted w-50">{{ translate('Scheduled Start Time') }}</td>
                        <td class="font-weight-bold">
                            @if($timing['scheduled_vs_actual']['scheduled'])
                                {{ $timing['scheduled_vs_actual']['scheduled']->format('d M Y, h:i:s A') }}
                            @else
                                <span class="text-muted">{{ translate('Not scheduled') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Actual Start Time') }}</td>
                        <td class="font-weight-bold">
                            @if($timing['scheduled_vs_actual']['actual'])
                                {{ $timing['scheduled_vs_actual']['actual']->format('d M Y, h:i:s A') }}
                            @else
                                <span class="text-muted">{{ translate('Not started') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Time Difference') }}</td>
                        <td>
                            @if($timing['scheduled_vs_actual']['difference_seconds'] != 0)
                                @php
                                    $diff = abs($timing['scheduled_vs_actual']['difference_seconds']);
                                    $isLate = $timing['scheduled_vs_actual']['difference_seconds'] > 0;
                                @endphp
                                <span class="badge badge-inline {{ $isLate ? 'badge-warning' : 'badge-success' }}">
                                    {{ $isLate ? translate('Started Late') : translate('Started Early') }}
                                    {{ gmdate('H:i:s', $diff) }}
                                </span>
                            @else
                                <span class="badge badge-inline badge-success">{{ translate('On Time') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Extension Details Per Item -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Extension Details Per Item') }}</h5>
            </div>
            <div class="card-body">
                @if($items->where('total_extensions', '>', 0)->count() > 0)
                    <div class="table-responsive">
                        <table class="table aiz-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ translate('Sequence') }}</th>
                                    <th>{{ translate('Item') }}</th>
                                    <th>{{ translate('Total Extensions') }}</th>
                                    <th>{{ translate('Extension Time Added') }}</th>
                                    <th>{{ translate('Item Duration') }}</th>
                                    <th>{{ translate('Total Bids') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items->where('total_extensions', '>', 0)->sortBy('sequence_order') as $item)
                                    <tr>
                                        <!-- Sequence Order -->
                                        <td>
                                            <span class="badge badge-inline badge-secondary">{{ $item->sequence_order }}</span>
                                        </td>

                                        <!-- Item Name -->
                                        <td>
                                            @if($item->car)
                                                <div class="row gutters-5 w-200px">
                                                    <div class="col-auto">
                                                        <img src="{{ $item->car->main_photo_url ?? static_asset('assets/img/placeholder.jpg') }}"
                                                             alt="Car"
                                                             class="size-40px img-fit rounded">
                                                    </div>
                                                    <div class="col">
                                                        <div class="text-truncate-2 small">
                                                            {{ $item->car->car_name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Total Extensions -->
                                        <td>
                                            <span class="badge badge-inline badge-warning">
                                                {{ $item->total_extensions }} {{ translate('extensions') }}
                                            </span>
                                        </td>

                                        <!-- Extension Time Added -->
                                        <td>
                                            @php
                                                $extensionTimeAdded = $item->total_extensions * $room->extension_seconds;
                                            @endphp
                                            <strong>{{ gmdate('i:s', $extensionTimeAdded) }}</strong>
                                            <small class="text-muted">({{ $extensionTimeAdded }}s)</small>
                                        </td>

                                        <!-- Item Duration -->
                                        <td>
                                            @if($item->started_at && $item->ended_at)
                                                {{ gmdate('i:s', $item->started_at->diffInSeconds($item->ended_at)) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Total Bids -->
                                        <td>
                                            {{ $item->total_bids }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="las la-clock la-3x text-muted mb-3"></i>
                        <p class="text-muted">{{ translate('No items had timer extensions') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Timing Insights -->
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="las la-info-circle"></i>
            <strong>{{ translate('Timing Insights:') }}</strong>
            {{ translate('The auction ran for a total of') }} {{ gmdate('H:i:s', $timing['total_duration']) }}.
            @if($items->count() > 0)
                {{ translate('With') }} {{ $items->count() }} {{ translate('items, the average time per item was') }}
                {{ gmdate('i:s', $timing['average_time_per_item']) }}.
            @endif
            @if($timing['total_extensions'] > 0)
                {{ translate('There were') }} {{ $timing['total_extensions'] }} {{ translate('timer extensions across') }}
                {{ $timing['items_with_extensions'] }} {{ translate('items, adding approximately') }}
                {{ gmdate('i:s', $timing['total_extensions'] * $room->extension_seconds) }} {{ translate('to the total duration.') }}
            @else
                {{ translate('No timer extensions occurred during this auction.') }}
            @endif
        </div>
    </div>
</div>
