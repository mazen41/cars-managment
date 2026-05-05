<form action="{{ route('commission-log.index') }}" method="GET">
    <div class="card-header row gutters-5">
        <div class="col text-center text-md-left">
            <h5 class="mb-md-0 h6">{{ translate('Commission History') }}</h5>
        </div>
         <div class="col-md-4">
            <div class="form-group mb-0">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="commissionable_type" id="commissionable_type">
                    <option value="">{{ translate('Select Type') }}</option>
                    <option value="order" @if(request()->input('commissionable_type') == 'order') selected @endif>{{ translate('Order') }}</option>
                    <option value="car_inspection" @if(request()->input('commissionable_type') == 'car_inspection') selected @endif>{{ translate('Car Inspection') }}</option>
                    <option value="car_reservation" @if(request()->input('commissionable_type') == 'car_reservation') selected @endif>{{ translate('Car Reservation') }}</option>
                    <option value="auction_invoice" @if(request()->input('commissionable_type') == 'auction_invoice') selected @endif>{{ translate('Auction Invoice') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-0">
                <input type="text" class="form-control form-control-sm aiz-date-range" id="search" name="date_range"@isset($date_range) value="{{ $date_range }}" @endisset placeholder="{{ translate('Daterange') }}">
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn btn-md btn-primary" type="submit">
                {{ translate('Filter') }}
            </button>
        </div>
    </div>
</form>
<div class="card-body">

    <table class="table aiz-table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th data-breakpoints="lg">{{ translate('Reference') }}</th>
                <th>{{ translate('Commission Type') }}</th>
                <th>{{ translate('Admin Commission') }}</th>
                <th>{{ translate('Seller Earning') }}</th>
                <th data-breakpoints="lg">{{ translate('Created At') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($commission_history as $key => $history)
            <tr>
                <td>{{ ($key+1) }}</td>
                <td>
                    @if(isset($history->commissionable))
                    @if ($history->commissionable_type == 'App\Models\Order')
                      <a href="{{ route('orders.show', encrypt($history->commissionable->id)) }}">{{ $history->commissionable->code }}</a>

                    @elseif ($history->commissionable_type == 'App\Models\CarInspection')
                       <a href="{{ route('admin.car-inspections.show', $history->commissionable->id) }}">{{ $history->commissionable->inspection_number }}</a>
                    @elseif($history->commissionable_type == 'App\Models\CarReservation')
                        <a href="{{ route('admin.car-reservations.show', $history->commissionable->id) }}">{{ $history->commissionable->reservation_id }}</a>
                    @elseif($history->commissionable_type == 'App\Models\AuctionInvoice')
                        <a href="{{ route('admin.auction-invoices.show', $history->commissionable->id) }}">{{ $history->commissionable->id }}</a>
                    @endif
                    @else
                        <span class="badge badge-inline badge-danger">
                            {{ translate('Reference Deleted') }}
                        </span>
                    @endif
                </td>
                <td>{{ $history->commissionable_name }}</td>
                <td>{{ single_price($history->admin_commission) }}</td>
                <td>{{ single_price($history->ownable_earning) }}</td>
                <td>{{ $history->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="aiz-pagination mt-4">
        {{ $commission_history->appends(request()->input())->links() }}
    </div>
</div>
