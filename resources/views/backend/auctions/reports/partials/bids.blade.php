<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0 h6">{{ translate('All Bids') }} ({{ $bids->count() }})</h5>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-soft-primary" type="button" data-toggle="collapse" data-target="#bid-filters">
                    <i class="las la-filter"></i> {{ translate('Filters') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="collapse" id="bid-filters">
        <div class="card-body border-bottom">
            <form id="bid-filter-form" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2">{{ translate('Status:') }}</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">{{ translate('All') }}</option>
                        <option value="accepted">{{ translate('Accepted') }}</option>
                        <option value="rejected">{{ translate('Rejected') }}</option>
                        <option value="outbid">{{ translate('Outbid') }}</option>
                    </select>
                </div>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2">{{ translate('Bidder:') }}</label>
                    <input type="text" name="bidder_name" class="form-control form-control-sm" placeholder="{{ translate('Search by name') }}">
                </div>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2">{{ translate('Date From:') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm">
                </div>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2">{{ translate('Date To:') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-sm btn-primary mb-2">
                    <i class="las la-search"></i> {{ translate('Apply') }}
                </button>
                <button type="button" class="btn btn-sm btn-secondary mb-2 ml-2" onclick="resetBidFilters()">
                    <i class="las la-redo"></i> {{ translate('Reset') }}
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($bids->count() > 0)
            <div class="table-responsive">
                <table class="table aiz-table mb-0" id="bids-table">
                    <thead>
                        <tr>
                            <th>{{ translate('Time') }}</th>
                            <th>{{ translate('Bidder') }}</th>
                            <th>{{ translate('Item') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('IP Address') }}</th>
                            <th>{{ translate('User Agent') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bids as $bid)
                            <tr>
                                <!-- Timestamp -->
                                <td>
                                    <div class="small">
                                        <div>{{ $bid->created_at->format('d M Y') }}</div>
                                        <div class="text-muted">{{ $bid->created_at->format('h:i:s A') }}</div>
                                    </div>
                                </td>

                                <!-- Bidder Information -->
                                <td>
                                    @if($bid->bidder)
                                        <div class="small">
                                            <div><strong>{{ $bid->bidder->name }}</strong></div>
                                            <div class="text-muted">ID: {{ $bid->bidder->id }}</div>
                                            <div class="text-muted">{{ $bid->bidder->email }}</div>
                                            @if($bid->bidder->phone)
                                                <div class="text-muted">{{ $bid->bidder->phone }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">{{ translate('Unknown') }}</span>
                                    @endif
                                </td>

                                <!-- Auction Item -->
                                <td>
                                    @if($bid->auctionItem && $bid->auctionItem->car)
                                        <div class="small">
                                            <div class="text-truncate" style="max-width: 200px;">
                                                <strong>{{ $bid->auctionItem->car->car_name }}</strong>
                                            </div>
                                            <div class="text-muted">{{ translate('Seq:') }} {{ $bid->auctionItem->sequence_order }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Bid Amount -->
                                <td>
                                    <strong class="text-primary">{{ format_price($bid->amount) }}</strong>
                                </td>

                                <!-- Bid Status -->
                                <td>
                                    @if($bid->status == 'accepted')
                                        <span class="badge badge-inline badge-success">
                                            <i class="las la-check-circle"></i> {{ translate('Accepted') }}
                                        </span>
                                    @elseif($bid->status == 'rejected')
                                        <span class="badge badge-inline badge-danger">
                                            <i class="las la-times-circle"></i> {{ translate('Rejected') }}
                                        </span>
                                    @elseif($bid->status == 'outbid')
                                        <span class="badge badge-inline badge-warning">
                                            <i class="las la-exclamation-circle"></i> {{ translate('Outbid') }}
                                        </span>
                                    @else
                                        <span class="badge badge-inline badge-secondary">{{ translate(ucfirst($bid->status)) }}</span>
                                    @endif
                                </td>

                                <!-- IP Address -->
                                <td>
                                    <span class="small text-muted">{{ $bid->ip_address ?? '-' }}</span>
                                </td>

                                <!-- User Agent -->
                                <td>
                                    <div class="small text-muted text-truncate" style="max-width: 200px;" title="{{ $bid->user_agent ?? '-' }}">
                                        {{ $bid->user_agent ?? '-' }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="las la-hand-paper la-3x text-muted mb-3"></i>
                <p class="text-muted">{{ translate('No bids found') }}</p>
            </div>
        @endif
    </div>
</div>

@push('script')
<script>
    function resetBidFilters() {
        $('#bid-filter-form')[0].reset();
        $('#bid-filter-form').submit();
    }
</script>
@endpush
