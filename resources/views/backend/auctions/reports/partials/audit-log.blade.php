<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0 h6">{{ translate('Audit Log') }} ({{ $audit_log->count() }})</h5>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-soft-primary" type="button" data-toggle="collapse" data-target="#audit-filters">
                    <i class="las la-filter"></i> {{ translate('Filters') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="collapse" id="audit-filters">
        <div class="card-body border-bottom">
            <form id="audit-filter-form" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2">{{ translate('Action Type:') }}</label>
                    <select name="action" class="form-control form-control-sm">
                        <option value="">{{ translate('All') }}</option>
                        <option value="room_started">{{ translate('Room Started') }}</option>
                        <option value="room_completed">{{ translate('Room Completed') }}</option>
                        <option value="item_started">{{ translate('Item Started') }}</option>
                        <option value="item_sold">{{ translate('Item Sold') }}</option>
                        <option value="item_unsold">{{ translate('Item Unsold') }}</option>
                        <option value="bid_placed">{{ translate('Bid Placed') }}</option>
                        <option value="bid_accepted">{{ translate('Bid Accepted') }}</option>
                        <option value="bid_rejected">{{ translate('Bid Rejected') }}</option>
                        <option value="timer_extended">{{ translate('Timer Extended') }}</option>
                    </select>
                </div>
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2">{{ translate('User:') }}</label>
                    <input type="text" name="user_name" class="form-control form-control-sm" placeholder="{{ translate('Search by name') }}">
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
                <button type="button" class="btn btn-sm btn-secondary mb-2 ml-2" onclick="resetAuditFilters()">
                    <i class="las la-redo"></i> {{ translate('Reset') }}
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($audit_log->count() > 0)
            <div class="table-responsive">
                <table class="table aiz-table mb-0" id="audit-log-table">
                    <thead>
                        <tr>
                            <th>{{ translate('Timestamp') }}</th>
                            <th>{{ translate('Action') }}</th>
                            <th>{{ translate('User') }}</th>
                            <th>{{ translate('Item') }}</th>
                            <th>{{ translate('Details') }}</th>
                            <th>{{ translate('IP Address') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($audit_log as $log)
                            @php
                                // Determine if this is a critical event
                                $criticalActions = ['room_started', 'room_completed', 'item_sold'];
                                $isCritical = in_array($log->action, $criticalActions);
                                $rowClass = $isCritical ? 'table-warning' : '';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <!-- Timestamp -->
                                <td>
                                    <div class="small">
                                        <div>{{ $log->created_at->format('d M Y') }}</div>
                                        <div class="text-muted">{{ $log->created_at->format('h:i:s A') }}</div>
                                    </div>
                                </td>

                                <!-- Action Type -->
                                <td>
                                    @php
                                        $actionBadges = [
                                            'room_started' => 'badge-success',
                                            'room_completed' => 'badge-primary',
                                            'item_started' => 'badge-info',
                                            'item_sold' => 'badge-success',
                                            'item_unsold' => 'badge-warning',
                                            'bid_placed' => 'badge-secondary',
                                            'bid_accepted' => 'badge-success',
                                            'bid_rejected' => 'badge-danger',
                                            'timer_extended' => 'badge-warning',
                                        ];
                                        $badgeClass = $actionBadges[$log->action] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge badge-inline {{ $badgeClass }}">
                                        @if($isCritical)
                                            <i class="las la-star"></i>
                                        @endif
                                        {{ translate(str_replace('_', ' ', ucwords($log->action))) }}
                                    </span>
                                </td>

                                <!-- User -->
                                <td>
                                    @if($log->user)
                                        <div class="small">
                                            <div><strong>{{ $log->user->name }}</strong></div>
                                            <div class="text-muted">ID: {{ $log->user->id }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted">{{ translate('System') }}</span>
                                    @endif
                                </td>

                                <!-- Auction Item -->
                                <td>
                                    @if($log->auctionItem && $log->auctionItem->car)
                                        <div class="small">
                                            <div class="text-truncate" style="max-width: 150px;">
                                                {{ $log->auctionItem->car->car_name }}
                                            </div>
                                            <div class="text-muted">{{ translate('Seq:') }} {{ $log->auctionItem->sequence_order }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Details -->
                                <td>
                                    @if($log->details)
                                        @php
                                            try {
                                                $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
                                            } catch (\Exception $e) {
                                                $details = null;
                                            }
                                        @endphp
                                        @if($details && is_array($details))
                                            <div class="small">
                                                @foreach($details as $key => $value)
                                                    @if(!is_array($value) && !is_object($value))
                                                        <div>
                                                            <strong>{{ translate(ucwords(str_replace('_', ' ', $key))) }}:</strong>
                                                            @if(in_array($key, ['amount', 'price', 'starting_price', 'final_price']))
                                                                {{ format_price($value) }}
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">{{ $log->details }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- IP Address -->
                                <td>
                                    <span class="small text-muted">{{ $log->ip_address ?? '-' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Legend for Critical Events -->
            <div class="mt-3">
                <small class="text-muted">
                    <i class="las la-star text-warning"></i> {{ translate('Critical events are highlighted') }}
                </small>
            </div>
        @else
            <div class="text-center py-4">
                <i class="las la-history la-3x text-muted mb-3"></i>
                <p class="text-muted">{{ translate('No audit log entries found') }}</p>
            </div>
        @endif
    </div>
</div>

<!-- Audit Log Summary -->
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="las la-info-circle"></i>
            <strong>{{ translate('Audit Log Summary:') }}</strong>
            {{ translate('This log contains') }} {{ $audit_log->count() }} {{ translate('entries tracking all actions and events during the auction.') }}
            {{ translate('Critical events such as room start, completion, and item sales are highlighted for easy identification.') }}
        </div>
    </div>
</div>

@push('script')
<script>
    function resetAuditFilters() {
        $('#audit-filter-form')[0].reset();
        $('#audit-filter-form').submit();
    }
</script>
@endpush
