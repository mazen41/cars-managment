@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="align-items-center">
        <h1 class="h3">{{translate('Customer Details')}}</h1>
    </div>
</div>

<div class="row gutters-10">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <span class="avatar avatar-xxl mb-3">
                    @if ($user->avatar_original != null)
                    <img src="{{ uploaded_asset($user->avatar_original) }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                    @else
                    <img src="{{ static_asset('assets/img/avatar-place.png') }}" class="image rounded-circle">
                    @endif
                </span>
                <h4 class="h4 fw-600 mb-0">{{ $user->name }}</h4>
                <p>{{ $user->email }}</p>

                <div class="mb-2">
                    @if ($user->email)
                    <span
                        class="badge badge-inline badge-{{ $user->email_verified_at != null ? 'success' : 'danger' }}">
                        {{ $user->email_verified_at != null ? translate('Email verified') : translate('Email not
                        verified') }}
                    </span>
                    @endif
                    @if($user->phone)
                    <span
                        class="badge badge-inline badge-{{ $user->phone_verified_at != null ? 'success' : 'danger' }}">
                        {{ $user->phone_verified_at != null ? translate('Phone verified') : translate('Phone not
                        verified') }}
                    </span>
                    @endif
                    @if ($user->banned)
                    <span class="badge badge-inline badge-danger">{{ translate('Banned') }}</span>
                    @endif
                </div>
                @can('login_as_customer')
                <a href="{{route('customers.login', encrypt($user->id))}}"
                    class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                    title="{{ translate('Log in as this Customer') }}">
                    <i class="las la-sign-in-alt"></i>
                </a>
                @endcan
                @can('ban_customer')
                @if($user->banned != 1)
                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm"
                    onclick="confirm_ban('{{route('customers.ban', encrypt($user->id))}}');"
                    title="{{ translate('Ban this Customer') }}">
                    <i class="las la-user-slash"></i>
                </a>
                @else
                <a href="#" class="btn btn-soft-success btn-icon btn-circle btn-sm"
                    onclick="confirm_unban('{{route('customers.ban', encrypt($user->id))}}');"
                    title="{{ translate('Unban this Customer') }}">
                    <i class="las la-user-check"></i>
                </a>
                @endif
                @endcan
                @can('delete_customer')
                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                    data-href="{{route('customers.destroy', $user->id)}}" title="{{ translate('Delete') }}">
                    <i class="las la-trash"></i>
                </a>
                @endcan
                @can('verify_customer')
                @if($user->phone)
                @if(!$user->phone_verified_at)
                <a href="javascript:void(0)" onclick="verifyPhone('{{$user->id}}')"
                    class="btn btn-soft-warning btn-icon btn-circle btn-sm" title="{{ translate('Verify Phone') }}">
                    <i class="las la-phone"></i>
                </a>
                @else
                <a href="javascript:void(0)" onclick="unverifyPhone('{{$user->id}}')"
                    class="btn btn-soft-success btn-icon btn-circle btn-sm"
                    title="{{ translate('Phone Verified - Click to Unverify') }}">
                    <i class="las la-phone-slash"></i>
                </a>
                @endif
                @endif

                @if($user->email)
                @if(!$user->email_verified_at)
                <a href="javascript:void(0)" onclick="verifyEmail('{{$user->id}}')"
                    class="btn btn-soft-warning btn-icon btn-circle btn-sm" title="{{ translate('Verify Email') }}">
                    <i class="las la-envelope"></i>
                </a>
                @else
                <a href="javascript:void(0)" onclick="unverifyEmail('{{$user->id}}')"
                    class="btn btn-soft-success btn-icon btn-circle btn-sm"
                    title="{{ translate('Email Verified - Click to Unverify') }}">
                    <i class="las la-envelope-open"></i>
                </a>
                @endif
                @endif
                @endcan
                <div class="text-left mt-3">
                    <h6 class="h6 fw-600 mb-2">{{ translate('Contact Info') }}:</h6>
                    <p class="text-muted mb-1">
                        <span class="ml-2">{{ $user->phone }}</span>
                    </p>
                    <p class="text-muted mb-1">
                        <span class="ml-2">{{ $user->email }}</span>
                    </p>
                </div>

                <div class="text-left mt-3">
                    <h6 class="h6 fw-600 mb-2">{{ translate('Member Since') }}:</h6>
                    <p class="text-muted">{{ date('d M, Y', strtotime($user->created_at)) }}</p>
                </div>
            </div>
            <!-- Customer Stats -->
            <div class="row gutters-10 px-3">
                <div class="col-md-6">
                    <div class="bg-grad-1 pb-3 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="h3 fw-700">{{ $user->orders_count }}</div>
                            <div class="opacity-50">{{ translate('Total Orders') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-grad-2 pb-3 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="h3 fw-700">{{ single_price($user->paid_amount) }}</div>
                            <div class="opacity-50">{{ translate('Total Spent') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-grad-3 pb-3 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="h3 fw-700">{{ single_price($user->balance) }}</div>
                            <div class="opacity-50">{{ translate('Wallet Balance') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-grad-4 pb-3 text-white rounded-lg mb-4 overflow-hidden">
                        <div class="px-3 pt-3">
                            <div class="h3 fw-700">{{ single_price($user->unpaid_amount) }}</div>
                            <div class="opacity-50">{{ translate('Unpaid Order Amount') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-lg-8">
        @if ($user->deletion_request)
        <div class="alert alert-danger mb-1">
            <strong>{{ translate('Warning!') }}</strong>
            <span>{{ translate('This customer applied for account deletion at') }} {{$user->deletion_requested_at->format('d/m/Y H:i')}}</span>
            <div class="text-right">
                @can('cancel_deletion_request')
            <a href="{{ route('customers.cancel_deletion', encrypt($user->id)) }}"
                class="btn btn-soft-primary btn-sm ml-2">
                {{ translate('Cancel Deletion Request') }}
            </a>
            @endcan
            @can('delete_customer')
            <a href="{{ route('customers.destroy', $user->id) }}"
                class="btn btn-soft-danger btn-sm ml-2 confirm-delete">
                {{ translate('Delete Customer') }}
            </a>
            @endcan
            </div>
        </div>
        @endif
        <!-- Tabs -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Customer Details') }}</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="customerTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="orders-tab" data-toggle="tab" href="#orders" role="tab">{{
                            translate('Orders') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="wallet-tab" data-toggle="tab" href="#wallet" role="tab">{{
                            translate('Wallet Details') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="addresses-tab" data-toggle="tab" href="#addresses" role="tab">{{
                            translate('Addresses') }}</a>
                    </li>
                </ul>

                <div class="tab-content" id="customerTabContent">
                    <!-- Orders Tab -->
                    <div class="tab-pane fade show active" id="orders" role="tabpanel">
                        <table class="table aiz-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ translate('Order Code') }}</th>
                                    <th>{{ translate('Amount') }}</th>
                                    <th>{{ translate('Delivery Status') }}</th>
                                    <th>{{ translate('Payment Status') }}</th>
                                    <th data-breakpoints="md">{{ translate('Date') }}</th>
                                    <th data-breakpoints="md">{{ translate('Options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $key => $order)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $order->code }}</td>
                                    <td>{{ single_price($order->grand_total) }}</td>
                                    <td>
                                        <span
                                            class="badge badge-inline badge-{{ get_delivery_status_badge($order->delivery_status) }}">
                                            {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-inline badge-{{ get_payment_status_badge($order->payment_status) }}">
                                            {{ translate(ucfirst(str_replace('_', ' ', $order->payment_status))) }}
                                        </span>
                                    </td>
                                    <td>{{ date('d-m-Y', strtotime($order->created_at)) }}</td>
                                    <td>
                                        <a href="{{ route('orders.show', encrypt($order->id)) }}"
                                            class="btn btn-soft-info btn-icon btn-circle btn-sm"
                                            title="{{ translate('View') }}">
                                            <i class="las la-eye"></i>
                                        </a>
                                        <a href="{{ route('invoice.download', $order->id) }}"
                                            class="btn btn-soft-warning btn-icon btn-circle btn-sm"
                                            title="{{ translate('Download Invoice') }}">
                                            <i class="las la-download"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($orders->count() > 0)
                        <div class="text-center mt-3">
                            <a href="{{ route('all_orders.index', ['user_id' => $user->id]) }}" class="btn btn-primary"
                                target="_blank">
                                {{ translate('All') }}
                            </a>
                        </div>
                        @endif
                    </div>

                    <!-- Wallet Tab -->
                    <div class="tab-pane fade" id="wallet" role="tabpanel">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ translate('Wallet Transactions') }}</h5>
                            </div>
                            <div class="card-body">
                                <table class="table aiz-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ translate('Date') }}</th>
                                            <th>{{ translate('Amount') }}</th>
                                            <th>{{ translate('Payment Method') }}</th>
                                            <th>{{ translate('Approval') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($wallet_transactions as $key => $wallet_transaction)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ date('d-m-Y', strtotime($wallet_transaction->created_at)) }}</td>
                                            <td>
                                                <span
                                                    class="{{ $wallet_transaction->amount < 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ single_price($wallet_transaction->amount) }}
                                                </span>
                                            </td>
                                            <td>{{ translate(ucfirst(str_replace('_', ' ',
                                                $wallet_transaction->payment_method ??
                                                ''))) }}</td>
                                            <td>
                                                @if ($wallet_transaction->offline_payment)
                                                @if ($wallet_transaction->approval)
                                                <span
                                                    class="badge badge-inline badge-success">{{translate('Approved')}}</span>
                                                @else
                                                <span
                                                    class="badge badge-inline badge-info">{{translate('Pending')}}</span>
                                                @endif
                                                @else
                                                <span
                                                    class="badge badge-inline badge-success">{{translate('Approved')}}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if ($wallet_transactions->count() > 0)
                                <div class="text-center mt-3">
                                    <a href="{{ route('wallet-history.index', ['user_id' => $user->id]) }}"
                                        class="btn btn-primary" target="_blank">
                                        {{ translate('All') }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ translate('Wallet Summary') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="bg-primary text-white rounded-lg p-4 mb-3">
                                            <div class="h4">{{ single_price($user->balance) }}</div>
                                            <div>{{ translate('Current Balance') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-success text-white rounded-lg p-4 mb-3">
                                            <div class="h4">{{ single_price($user->approved_wallet_amount) }}</div>
                                            <div>{{ translate('Total Added') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-danger text-white rounded-lg p-4 mb-3">
                                            <div class="h4">{{ single_price(abs($user->wallet_spent_amount))}}</div>
                                            <div>{{ translate('Total Credit Spent') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0 h6">{{ translate('Wallet Spent Details') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border">
                                            <div class="card-header bg-soft-primary">
                                                <h5 class="mb-0 h6">{{ translate('Orders Paid with Wallet') }}
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                @php
                                                $wallet_orders = \App\Models\Order::where('user_id', $user->id)
                                                ->where('payment_type', 'wallet')
                                                ->get();
                                                $wallet_orders_amount = $wallet_orders->sum('grand_total');
                                                @endphp
                                                <div class="text-center mb-3">
                                                    <h4>{{ single_price($wallet_orders_amount) }}</h4>
                                                    <div class="text-muted">{{ translate('Total Amount') }}</div>
                                                </div>
                                                <div class="text-center">
                                                    <h5>{{ $wallet_orders->count() }}</h5>
                                                    <div class="text-muted">{{ translate('Orders Count') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <h5 class="h6 fw-600">{{ translate('Recent Orders Paid with Wallet') }}</h5>
                                    <table class="table aiz-table mb-0 mt-2">
                                        <thead>
                                            <tr>
                                                <th>{{ translate('Order Code') }}</th>
                                                <th>{{ translate('Type') }}</th>
                                                <th>{{ translate('Amount') }}</th>
                                                <th>{{ translate('Date') }}</th>
                                                <th>{{ translate('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $combined_wallet_orders = [];

                                            foreach($wallet_orders->take(5) as $order) {
                                            $combined_wallet_orders[] = [
                                            'code' => $order->code,
                                            'type' => 'internal',
                                            'amount' => $order->grand_total,
                                            'date' => $order->created_at,
                                            'id' => $order->id
                                            ];
                                            }

                                            // Sort by date descending
                                            usort($combined_wallet_orders, function($a, $b) {
                                            return $b['date'] <=> $a['date'];
                                                });

                                                $combined_wallet_orders = array_slice($combined_wallet_orders, 0, 5);
                                                @endphp

                                                @foreach($combined_wallet_orders as $order)
                                                <tr>
                                                    <td>{{ $order['code'] }}</td>

                                                    <td>{{ single_price($order['amount']) }}</td>
                                                    <td>{{ date('d-m-Y', strtotime($order['date'])) }}</td>
                                                    <td>
                                                        @if($order['type'] == 'internal')
                                                        <a href="{{ route('orders.show', encrypt($order['id'])) }}"
                                                            class="btn btn-soft-info btn-icon btn-circle btn-sm"
                                                            title="{{ translate('View') }}">
                                                            <i class="las la-eye"></i>
                                                        </a>

                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses Tab -->
                    <div class="tab-pane fade" id="addresses" role="tabpanel">
                        <div class="row gutters-10">
                            @foreach($user->addresses as $key => $address)
                            <div class="col-md-6">
                                <div class="border p-3 pr-5 rounded mb-3 position-relative">
                                    <div>
                                        <span class="w-50 fw-600">{{ translate('Address') }}:</span>
                                        <span class="ml-2">{{ $address->address }}</span>
                                    </div>
                                    <div>
                                        <span class="w-50 fw-600">{{ translate('City') }}:</span>
                                        <span class="ml-2">{{ $address->city->getTranslation('name') }}</span>
                                    </div>
                                    <div>
                                        <span class="w-50 fw-600">{{ translate('State') }}:</span>
                                        <span class="ml-2">{{ $address->state->name }}</span>
                                    </div>
                                    <div>
                                        <span class="w-50 fw-600">{{ translate('Country') }}:</span>
                                        <span class="ml-2">{{ $address->country->name }}</span>
                                    </div>
                                    <div>
                                        <span class="w-50 fw-600">{{ translate('Phone') }}:</span>
                                        <span class="ml-2">{{ $address->phone }}</span>
                                    </div>
                                    @if($address->set_default)
                                    <div class="position-absolute right-0 bottom-0 pr-2 pb-3">
                                        <span class="badge badge-inline badge-primary">{{ translate('Default') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('modal')
    @include('modals.delete_modal')
    @include('modals.customer_ban_modal')
@endsection
@section('script')
    @include('backend.customer.customers.customer_js')
@endsection
