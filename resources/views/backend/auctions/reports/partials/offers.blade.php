<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0 h6">{{ translate('All Offers') }} ({{ $offers->count() }})</h5>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-soft-primary" type="button" data-toggle="collapse" data-target="#offer-filters">
                    <i class="las la-filter"></i> {{ translate('Filters') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="collapse" id="offer-filters">
        <div class="card-body border-bottom">
            <form id="offer-filter-form" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2">{{ translate('Status:') }}</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">{{ translate('All') }}</option>
                        <option value="pending">{{ translate('Pending') }}</option>
                        <option value="accepted">{{ translate('Accepted') }}</option>
                        <option value="rejected">{{ translate('Rejected') }}</option>
                        <option value="expired">{{ translate('Expired') }}</option>
                    </select>
                </div>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2">{{ translate('Buyer:') }}</label>
                    <input type="text" name="buyer_name" class="form-control form-control-sm" placeholder="{{ translate('Search by name') }}">
                </div>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2">{{ translate('Seller:') }}</label>
                    <input type="text" name="seller_name" class="form-control form-control-sm" placeholder="{{ translate('Search by name') }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary mb-2">
                    <i class="las la-search"></i> {{ translate('Apply') }}
                </button>
                <button type="button" class="btn btn-sm btn-secondary mb-2 ml-2" onclick="resetOfferFilters()">
                    <i class="las la-redo"></i> {{ translate('Reset') }}
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($offers->count() > 0)
            <div class="table-responsive">
                <table class="table aiz-table mb-0" id="offers-table">
                    <thead>
                        <tr>
                            <th>{{ translate('Item') }}</th>
                            <th>{{ translate('Buyer') }}</th>
                            <th>{{ translate('Seller') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Message') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Response') }}</th>
                            <th>{{ translate('Timestamps') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($offers as $offer)
                            <tr>
                                <!-- Auction Item -->
                                <td>
                                    @if($offer->auctionItem && $offer->auctionItem->car)
                                        <div class="small">
                                            <div class="text-truncate" style="max-width: 150px;">
                                                <strong>{{ $offer->auctionItem->car->car_name }}</strong>
                                            </div>
                                            <div class="text-muted">{{ translate('Seq:') }} {{ $offer->auctionItem->sequence_order }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Buyer Information -->
                                <td>
                                    @if($offer->buyer)
                                        <div class="small">
                                            <div><strong>{{ $offer->buyer->name }}</strong></div>
                                            <div class="text-muted">{{ $offer->buyer->email }}</div>
                                            @if($offer->buyer->phone)
                                                <div class="text-muted">{{ $offer->buyer->phone }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">{{ translate('Unknown') }}</span>
                                    @endif
                                </td>

                                <!-- Seller Information -->
                                <td>
                                    @if($offer->seller)
                                        <div class="small">
                                            <div><strong>{{ $offer->seller->name }}</strong></div>
                                            <div class="text-muted">{{ $offer->seller->email }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted">{{ translate('Unknown') }}</span>
                                    @endif
                                </td>

                                <!-- Offer Amount -->
                                <td>
                                    <strong class="text-primary">{{ format_price($offer->amount) }}</strong>
                                </td>

                                <!-- Offer Message -->
                                <td>
                                    <div class="small text-truncate" style="max-width: 200px;" title="{{ $offer->message ?? '-' }}">
                                        {{ $offer->message ?? '-' }}
                                    </div>
                                </td>

                                <!-- Offer Status -->
                                <td>
                                    @if($offer->status == 'accepted')
                                        <span class="badge badge-inline badge-success">
                                            <i class="las la-check-circle"></i> {{ translate('Accepted') }}
                                        </span>
                                    @elseif($offer->status == 'rejected')
                                        <span class="badge badge-inline badge-danger">
                                            <i class="las la-times-circle"></i> {{ translate('Rejected') }}
                                        </span>
                                    @elseif($offer->status == 'expired')
                                        <span class="badge badge-inline badge-warning">
                                            <i class="las la-clock"></i> {{ translate('Expired') }}
                                        </span>
                                    @elseif($offer->status == 'pending')
                                        <span class="badge badge-inline badge-info">
                                            <i class="las la-hourglass-half"></i> {{ translate('Pending') }}
                                        </span>
                                    @else
                                        <span class="badge badge-inline badge-secondary">{{ translate(ucfirst($offer->status)) }}</span>
                                    @endif
                                </td>

                                <!-- Seller Response -->
                                <td>
                                    @if(in_array($offer->status, ['accepted', 'rejected']) && $offer->seller_response)
                                        <div class="small">
                                            <div class="text-muted">{{ translate('Response:') }}</div>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $offer->seller_response }}">
                                                {{ $offer->seller_response }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Timestamps -->
                                <td>
                                    <div class="small">
                                        <div class="text-muted">{{ translate('Created:') }}</div>
                                        <div>{{ $offer->created_at->format('d M Y, h:i A') }}</div>
                                        @if($offer->responded_at)
                                            <div class="text-muted mt-1">{{ translate('Responded:') }}</div>
                                            <div>{{ $offer->responded_at->format('d M Y, h:i A') }}</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="las la-handshake la-3x text-muted mb-3"></i>
                <p class="text-muted">{{ translate('No offers found') }}</p>
            </div>
        @endif
    </div>
</div>

@push('script')
<script>
    function resetOfferFilters() {
        $('#offer-filter-form')[0].reset();
        $('#offer-filter-form').submit();
    }
</script>
@endpush
