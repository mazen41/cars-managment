@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Car Reservations') }}</h1>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Car Reservations') }}</h5>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="{{ route('admin.car-reservations.index') }}">
            <div class="row mb-3">
                <div class="col-md-2">
                    <select name="status" class="form-control aiz-selectpicker" onchange="this.form.submit()">
                        <option value="">{{ translate('All Status') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ translate('Pending') }}</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ translate('Confirmed') }}</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ translate('Cancelled') }}</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ translate('Completed') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_status" class="form-control aiz-selectpicker" onchange="this.form.submit()">
                        <option value="">{{ translate('All Payment Status') }}</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>{{ translate('Pending') }}</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>{{ translate('Paid') }}</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>{{ translate('Failed') }}</option>
                        <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>{{ translate('Refunded') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="{{ translate('From Date') }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="{{ translate('To Date') }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="{{ translate('Search...') }}" value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">{{ translate('Search') }}</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('admin.car-reservations.index') }}" class="btn btn-secondary">{{ translate('Clear') }}</a>
                </div>
            </div>
        </form>

        <!-- Table -->
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>{{ translate('Car') }}</th>
                        <th data-breakpoints="md">{{ translate('Customer') }}</th>
                        <th data-breakpoints="md">{{ translate('Amount') }}</th>
                        <th data-breakpoints="md">{{ translate('Payment Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Reserved At') }}</th>
                        <th width="180" class="text-right">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $key => $reservation)
                        <tr>
                            <td>{{ $key + $reservations->firstItem() }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-2">
                                        @if($reservation->car->main_photo_url)
                                            <img src="{{ $reservation->car->main_photo_url }}" class="rounded" width="40" height="30" alt="">
                                        @else
                                            <div class="bg-light rounded d-flex justify-content-center align-items-center" style="width: 40px; height: 30px;">
                                                <i class="las la-car"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-600">{{ $reservation->car->car_name }}</div>
                                        <div class="text-muted fs-12">{{ $reservation->car->vin }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-600">{{ $reservation->user->name }}</div>
                                    <div class="text-muted fs-12">{{ $reservation->user->email }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-600">{{ $reservation->formatted_reservation_amount }}</div>
                                @if($reservation->payment && $reservation->payment->method)
                                    <div class="text-muted fs-12">{{ $reservation->payment->method }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-inline {{ $reservation->payment->status_badge_class ?? 'badge-warning' }}">
                                    {{ $reservation->payment ? translate(ucfirst($reservation->payment->status)) : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-inline {{ $reservation->status_badge_class }}">
                                    {{ translate(ucfirst($reservation->status)) }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $reservation->reserved_at->format('M j, Y') }}</div>
                                <div class="text-muted fs-12">{{ $reservation->reserved_at->format('g:i A') }}</div>
                            </td>
                            <td class="text-right">
                                <div class="dropdown">
                                    <button class="btn btn-soft-primary btn-icon btn-circle btn-sm" type="button" data-toggle="dropdown">
                                        <i class="las la-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        @can('view_car_reservations')
                                            <a class="dropdown-item" href="{{ route('admin.car-reservations.show', $reservation->id) }}">
                                                {{ translate('View Details') }}
                                            </a>
                                        @endcan
                                        @can('edit_car_reservations')
                                            @if($reservation->status == 'pending')
                                                <form action="{{ route('admin.car-reservations.confirm', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('{{ translate('Are you sure you want to confirm this reservation?') }}')">
                                                        {{ translate('Confirm') }}
                                                    </button>
                                                </form>
                                            @endif
                                            @if($reservation->status == 'confirmed')
                                                <form action="{{ route('admin.car-reservations.mark-as-sold', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('{{ translate('Are you sure you want to mark this car as sold?') }}')">
                                                        {{ translate('Mark as Sold') }}
                                                    </button>
                                                </form>
                                            @endif
                                            @if($reservation->can_be_cancelled)
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('admin.car-reservations.cancel', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="cancellation_reason" value="Cancelled by admin">
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('{{ translate('Are you sure you want to cancel this reservation?') }}')">
                                                        {{ translate('Cancel') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                        @can('delete_car_reservations')
                                            @if(!$reservation->is_active)
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('admin.car-reservations.destroy', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('{{ translate('Are you sure you want to delete this reservation?') }}')">
                                                        {{ translate('Delete') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <x-table_pagination :data="$reservations" :paginate="request()->input('paginate')" />
    </div>
</div>
@endsection

@section('modal')
<!-- Bulk Update Modal -->
<div class="modal fade" id="bulk-update-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ translate('Update Status') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('admin.car-reservations.bulk-update-status') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="reservation_ids" id="bulk-reservation-ids">
                    <div class="form-group">
                        <label>{{ translate('Status') }}</label>
                        <select name="status" class="form-control" required>
                            <option value="">{{ translate('Select Status') }}</option>
                            <option value="pending">{{ translate('Pending') }}</option>
                            <option value="confirmed">{{ translate('Confirmed') }}</option>
                            <option value="cancelled">{{ translate('Cancelled') }}</option>
                            <option value="completed">{{ translate('Completed') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
