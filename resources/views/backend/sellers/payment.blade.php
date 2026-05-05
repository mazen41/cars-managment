@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ $user->name }} ({{ $user->shop->name }})</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{translate('Date')}}</th>
                    <th>{{translate('Amount')}}</th>
                    <th>{{translate('Payment Method')}}</th>
                    <th data-breakpoints="lg">{{ translate('Payment Details') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $key => $payment)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>{{ $payment->created_at }}</td>
                        <td>
                            {{ single_price($payment->amount) }}
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                        <td>
                            @if ($payment->txn_code != null) ({{ translate('Txn Code') }} : {{ $payment->txn_code }}) <br> @endif
                            @if($payment->payment_details) {{ translate('Bank/wallet') }} :  {{$payment->payment_details}} @endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
