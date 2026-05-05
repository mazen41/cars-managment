@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="align-items-center">
        <h1 class="h3">{{translate('Refund History Report')}}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <form action="{{ route('refund-history.index') }}" method="GET">
                <div class="card-header row gutters-5">
                    <div class="col text-center text-md-left">
                        <h5 class="mb-md-0 h6">{{ translate('Refund History') }}</h5>
                    </div>
                    {{-- <div class="col-md-3 ml-auto">
                        <select id="demo-ease" class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="status">
                            <option value="">{{ translate('Choose Status') }}</option>
                            <option value="pending" @if(request('status') == 'pending') selected @endif>{{ translate('Pending') }}</option>
                            <option value="approved" @if(request('status') == 'approved') selected @endif>{{ translate('Approved') }}</option>
                            <option value="rejected" @if(request('status') == 'rejected') selected @endif>{{ translate('Rejected') }}</option>
                            <option value="completed" @if(request('status') == 'completed') selected @endif>{{ translate('Completed') }}</option>
                        </select>
                    </div> --}}
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
                            <th>{{ translate('Reference')}}</th>
                            <th>{{ translate('Order Code')}}</th>
                            <th data-breakpoints="lg">{{  translate('Date') }}</th>
                            <th>{{ translate('Amount')}}</th>
                            <th data-breakpoints="lg">{{ translate('Payment Method')}}</th>
                            {{-- <th data-breakpoints="lg">{{ translate('Reason')}}</th>
                            <th data-breakpoints="lg" class="text-right">{{ translate('Status')}}</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($refunds as $key => $refund)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $refund->refund_reference }}</td>
                                <td><a href="{{route('external_orders.show', encrypt($refund->externalOrder->id))}}" >{{ $refund->externalOrder->code }}</a></td>
                                <td>{{ date('d-m-Y', strtotime($refund->created_at)) }}</td>
                                <td>{{ single_price($refund->amount) }}</td>
                                <td>{{ translate(ucfirst(str_replace('_', ' ', $refund->payment_method))) }}</td>
                                {{-- <td>{{ $refund->reason }}</td>
                                <td class="text-right">
                                    @php
                                        $status_class = [
                                            'pending' => 'badge-info',
                                            'approved' => 'badge-success',
                                            'rejected' => 'badge-danger',
                                            'completed' => 'badge-success'
                                        ][$refund->status] ?? 'badge-info';
                                    @endphp
                                    <span class="badge badge-inline {{ $status_class }}">
                                        {{translate(ucfirst($refund->status))}}
                                    </span>
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination mt-4">
                    {{ $refunds->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        $('.aiz-date-range').daterangepicker();
    });
</script>
@endsection
