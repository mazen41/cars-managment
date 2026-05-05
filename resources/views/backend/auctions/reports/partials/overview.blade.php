<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Room Information') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted w-50">{{ translate('Room Name') }}</td>
                        <td class="font-weight-bold">{{ $overview['name'] }}</td>
                    </tr>
                    @if(isset($overview['description']) && $overview['description'])
                    <tr>
                        <td class="text-muted">{{ translate('Description') }}</td>
                        <td>{{ $overview['description'] }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">{{ translate('Status') }}</td>
                        <td>
                            @if($overview['status'] == 'completed')
                                <span class="badge badge-inline badge-success">{{ translate('Completed') }}</span>
                            @else
                                <span class="badge badge-inline badge-info">{{ translate(ucfirst($overview['status'])) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Total Items') }}</td>
                        <td class="font-weight-bold">{{ $overview['total_items'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Scheduled Start') }}</td>
                        <td>
                            @if($overview['scheduled_start_at'])
                                {{ $overview['scheduled_start_at']->format('d M Y, h:i A') }}
                            @else
                                <span class="text-muted">{{ translate('Not scheduled') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Actual Start') }}</td>
                        <td>
                            @if($overview['started_at'])
                                {{ $overview['started_at']->format('d M Y, h:i A') }}
                            @else
                                <span class="text-muted">{{ translate('Not started') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Completed At') }}</td>
                        <td>
                            @if($overview['completed_at'])
                                {{ $overview['completed_at']->format('d M Y, h:i A') }}
                            @else
                                <span class="text-muted">{{ translate('Not completed') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Total Duration') }}</td>
                        <td>
                            @if($overview['total_duration'] > 0)
                                {{ gmdate('H:i:s', $overview['total_duration']) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Configuration Settings') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted w-50">{{ translate('Commission Percentage') }}</td>
                        <td class="font-weight-bold">{{ $overview['configuration']['commission_percentage'] }}%</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Bid Increment Type') }}</td>
                        <td>{{ translate(ucfirst($overview['configuration']['bid_increment_type'])) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Bid Increment Value') }}</td>
                        <td>
                            @if($overview['configuration']['bid_increment_type'] == 'percentage')
                                {{ $overview['configuration']['bid_increment_value'] }}%
                            @else
                                {{ format_price($overview['configuration']['bid_increment_value']) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Base Timer') }}</td>
                        <td>{{ $overview['configuration']['base_timer_seconds'] }} {{ translate('seconds') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Extension Time') }}</td>
                        <td>{{ $overview['configuration']['extension_seconds'] }} {{ translate('seconds') }}</td>
                    </tr>
                    {{-- @if(isset($overview['configuration']['insurance_deposit_amount']))
                    <tr>
                        <td class="text-muted">{{ translate('Insurance Deposit') }}</td>
                        <td>{{ format_price($overview['configuration']['insurance_deposit_amount']) }}</td>
                    </tr>
                    @endif --}}
                    {{-- @if(isset($overview['configuration']['currency']))
                    <tr>
                        <td class="text-muted">{{ translate('Currency') }}</td>
                        <td>{{ $overview['configuration']['currency'] }}</td>
                    </tr>
                    @endif --}}
                </table>
            </div>
        </div>
    </div>
</div>
