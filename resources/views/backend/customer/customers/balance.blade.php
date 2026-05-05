@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="align-items-center">
        <h1 class="h3">{{translate('All Customers')}}</h1>
    </div>
</div>


<div class="card">
    <form class="" id="sort_customers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-0 h6">{{translate('Customers')}}</h5>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" @isset($sort_search)
                        value="{{ $sort_search }}" @endisset
                        placeholder="{{ translate('Type email or name & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <!--<th data-breakpoints="lg">#</th>-->
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <input type="hidden" name="export_type" value="">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{translate('Name')}}</th>
                        <th data-breakpoints="lg">{{translate('Email Address')}}</th>
                        <th data-breakpoints="lg">{{translate('Phone')}}</th>
                        <th data-breakpoints="lg">{{translate('Wallet Balance')}}</th>
                        <th class="text-right">{{translate('Wallet Transaction')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $key => $user)
                    @if ($user != null)
                    <tr>
                        <td>{{ ($key+1) + ($users->currentPage() - 1)*$users->perPage() }}</td>
                        <td>@if($user->banned == 1) <i class="fa fa-ban text-danger" aria-hidden="true"></i> @endif
                            {{$user->name}}</td>
                        <td>{{$user->email}}</td>
                        <td>{{$user->phone}}</td>
                        <td>{{single_price($user->balance)}}</td>
                        <td class="text-right">
                            @can('wallet_transaction_report')
                            <a href="{{route('wallet-history.index', ['user_id' => $user->id])}}"
                                class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                title="{{ translate('Wallet Transaction') }}">
                                <i class="las la-list"></i>
                            </a>
                            @endcan

                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
           <x-table_pagination :data="$users" :paginate="$paginate" />
        </div>
    </form>
</div>

@endsection

