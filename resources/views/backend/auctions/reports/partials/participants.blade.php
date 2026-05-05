<!-- Statistics Cards -->
<div class="row gutters-10">
    <!-- Total Registered Participants -->
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-user-check la-3x text-primary mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ $participants['total_registered'] }}</h3>
                <p class="text-muted mb-0">{{ translate('Registered Participants') }}</p>
                <small class="text-muted">{{ translate('Paid insurance deposit') }}</small>
            </div>
        </div>
    </div>

    <!-- Total Unique Bidders -->
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-hand-paper la-3x text-info mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ $participants['total_bidders'] }}</h3>
                <p class="text-muted mb-0">{{ translate('Unique Bidders') }}</p>
                <small class="text-muted">{{ translate('Placed at least one bid') }}</small>
            </div>
        </div>
    </div>

    <!-- Total Winners -->
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-trophy la-3x text-success mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ $participants['total_winners'] }}</h3>
                <p class="text-muted mb-0">{{ translate('Winners') }}</p>
                <small class="text-muted">{{ translate('Won auction items') }}</small>
            </div>
        </div>
    </div>

    <!-- Participation Rate -->
    <div class="col-md-6 col-lg-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-percentage la-3x text-warning mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ number_format($participants['participation_rate'], 2) }}%</h3>
                <p class="text-muted mb-0">{{ translate('Participation Rate') }}</p>
                <small class="text-muted">{{ translate('Bidders / Registered') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Average Bids Per Item -->
<div class="row mt-3">
    <div class="col-lg-4 mx-auto">
        <div class="card">
            <div class="card-body text-center">
                <i class="las la-chart-bar la-3x text-secondary mb-3"></i>
                <h3 class="mb-0 font-weight-bold">{{ number_format($participants['average_bids_per_item'], 2) }}</h3>
                <p class="text-muted mb-0">{{ translate('Average Bids Per Item') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Winners List -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Winners List') }} ({{ $participants['winners']->count() }})</h5>
            </div>
            <div class="card-body">
                @if($participants['winners']->count() > 0)
                    <div class="table-responsive">
                        <table class="table aiz-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ translate('Winner') }}</th>
                                    <th>{{ translate('Contact') }}</th>
                                    <th>{{ translate('Item Won') }}</th>
                                    <th>{{ translate('Winning Amount') }}</th>
                                    <th>{{ translate('Item Details') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($participants['winners'] as $winner)
                                    <tr>
                                        <!-- Winner Name -->
                                        <td>
                                            @if($winner['user'])
                                                <div>
                                                    <strong>{{ $winner['user']->name }}</strong>
                                                </div>
                                                <div class="small text-muted">ID: {{ $winner['user']->id }}</div>
                                            @else
                                                <span class="text-muted">{{ translate('Unknown') }}</span>
                                            @endif
                                        </td>

                                        <!-- Contact Information -->
                                        <td>
                                            @if($winner['user'])
                                                <div class="small">
                                                    <div>{{ $winner['user']->email }}</div>
                                                    @if($winner['user']->phone)
                                                        <div class="text-muted">{{ $winner['user']->phone }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Item Won -->
                                        <td>
                                            @if($winner['item'] && $winner['item']->car)
                                                <div class="row gutters-5 w-200px">
                                                    <div class="col-auto">
                                                        <img src="{{ $winner['item']->car->main_photo_url ?? static_asset('assets/img/placeholder.jpg') }}" 
                                                             alt="Car" 
                                                             class="size-50px img-fit rounded">
                                                    </div>
                                                    <div class="col">
                                                        <div class="text-truncate-2 small">
                                                            {{ $winner['item']->car->car_name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Winning Amount -->
                                        <td>
                                            <strong class="text-success">{{ format_price($winner['amount']) }}</strong>
                                        </td>

                                        <!-- Item Details -->
                                        <td>
                                            @if($winner['item'])
                                                <div class="small">
                                                    <div>{{ translate('Sequence:') }} {{ $winner['item']->sequence_order }}</div>
                                                    <div class="text-muted">{{ translate('Bids:') }} {{ $winner['item']->total_bids }}</div>
                                                    @if($winner['item']->started_at && $winner['item']->ended_at)
                                                        <div class="text-muted">
                                                            {{ translate('Duration:') }} {{ gmdate('i:s', $winner['item']->started_at->diffInSeconds($winner['item']->ended_at)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="las la-trophy la-3x text-muted mb-3"></i>
                        <p class="text-muted">{{ translate('No winners found') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Participation Insights -->
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="las la-info-circle"></i>
            <strong>{{ translate('Participation Insights:') }}</strong>
            @if($participants['total_registered'] > 0)
                {{ translate('Out of') }} {{ $participants['total_registered'] }} {{ translate('registered participants,') }}
                {{ $participants['total_bidders'] }} {{ translate('actively placed bids, resulting in a') }}
                {{ number_format($participants['participation_rate'], 2) }}% {{ translate('participation rate.') }}
                @if($participants['total_winners'] > 0)
                    {{ $participants['total_winners'] }} {{ translate('participants won items.') }}
                @endif
            @else
                {{ translate('No participants registered for this auction room.') }}
            @endif
        </div>
    </div>
</div>
