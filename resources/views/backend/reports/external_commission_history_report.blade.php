@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class=" align-items-center">
       <h1 class="h3">{{translate('Commission History report')}}</h1>
	</div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <form action="{{ route('external-commission-log.index') }}" method="GET">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">{{ translate('External Commission History') }}</h5>
                    </div>
                    <div class="col-md-3 ml-auto">
                        <select id="demo-ease" class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="provider">
                            <option value="">{{ translate('Choose Provider') }}</option>
                            @php
                                $providers = [
                                    'alibaba','aliexpress','shein'
                                ]
                            @endphp
                            @foreach ($providers as $single)
                                <option value="{{ $single }}" @if($single == $provider) selected @endif >
                                    {{ translate($single) }}
                                </option>
                            @endforeach
                        </select>
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
                            <th data-breakpoints="lg">{{ translate('Order Code') }}</th>
                            <th>{{ translate('Admin Commission') }}</th>
                            <th>{{ translate('Provider') }}</th>
                            <th data-breakpoints="lg">{{ translate('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($commission_history as $key => $order)
                        <tr>
                            <td>{{ ($key+1) }}</td>
                            <td>
                               {{$order->code}}
                            </td>
                            <td>{{ format_price_in_usd($order->commission) }}</td>
                            <td>{{ translate($order->provider) }}</td>
                            <td>{{ $order->created_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    {{ $commission_history->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
