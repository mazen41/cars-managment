@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Reservation Details') }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.car-reservations.index') }}" class="btn btn-light">
                <span>{{ translate('Back to Reservations') }}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Reservation Details -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Reservation Information') }}</h5>
                <div class="text-right">
                    <span class="badge badge-inline {{ $carReservation->status_badge_class }} fs-12">
                        {{ ucfirst($carReservation->status) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Reservation ID') }}:</td>
                                <td>#{{ $carReservation->id }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Status') }}:</td>
                                <td>
                                    <span class="badge badge-inline {{ $carReservation->status_badge_class }}">
                                        {{ ucfirst($carReservation->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Reserved At') }}:</td>
                                <td>{{ $carReservation->reserved_at->format('M j, Y g:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Reservation Amount') }}:</td>
                                <td class="fs-16 fw-700">{{ $carReservation->formatted_reservation_amount }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Payment Method') }}:</td>
                                <td>{{ $carReservation->payment && $carReservation->payment->method ? ucfirst(str_replace('_', ' ', $carReservation->payment->method)) : 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Payment Status') }}:</td>
                                <td>
                                    <span class="badge badge-inline {{ $carReservation->payment->status_badge_class?? 'badge-warning' }}">
                                        {{ ucfirst($carReservation->payment && $carReservation->payment->status ? $carReservation->payment->status : 'N/A') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Transaction ID') }}:</td>
                                <td>{{ $carReservation->payment->transaction_id ?? 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($carReservation->notes)
                    <div class="mt-3">
                        <h6 class="fw-600">{{ translate('Customer Notes') }}:</h6>
                        <div class="bg-light p-3 rounded">
                            {{ $carReservation->notes }}
                        </div>
                    </div>
                @endif

                @if($carReservation->admin_notes)
                    <div class="mt-3">
                        <h6 class="fw-600">{{ translate('Admin Notes') }}:</h6>
                        <div class="bg-soft-info p-3 rounded">
                            {{ $carReservation->admin_notes }}
                        </div>
                    </div>
                @endif

                @if($carReservation->status == 'cancelled')
                    <div class="mt-3">
                        <h6 class="fw-600 text-danger">{{ translate('Cancellation Details') }}:</h6>
                        <div class="bg-soft-danger p-3 rounded">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>{{ translate('Cancelled At') }}:</strong><br>
                                    {{ $carReservation->cancelled_at ? $carReservation->cancelled_at->format('M j, Y g:i A') : 'Not recorded' }}
                                </div>
                                <div class="col-md-6">
                                    <strong>{{ translate('Cancelled By') }}:</strong><br>
                                    {{ $carReservation->cancelledBy->name ?? 'System' }}
                                </div>
                            </div>
                            @if($carReservation->cancellation_reason)
                                <div class="mt-2">
                                    <strong>{{ translate('Reason') }}:</strong><br>
                                    {{ $carReservation->cancellation_reason }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Car Details -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Car Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($carReservation->car->main_photo_url)
                            <img src="{{ $carReservation->car->main_photo_url }}" class="img-fluid rounded" alt="Car Photo">
                        @else
                            <div class="bg-light rounded d-flex justify-content-center align-items-center" style="height: 200px;">
                                <i class="las la-car fs-48 text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h4 class="fw-600">{{ $carReservation->car->car_name ?? $carReservation->car->description }}</h4>
                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-600">{{ translate('VIN') }}:</td>
                                        <td>{{ $carReservation->car->vin ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-600">{{ translate('Brand') }}:</td>
                                        <td>{{ $carReservation->car->brand->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-600">{{ translate('Model') }}:</td>
                                        <td>{{ $carReservation->car->model->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-600">{{ translate('Year') }}:</td>
                                        <td>{{ $carReservation->car->manufacture_year }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-600">{{ translate('Color') }}:</td>
                                        <td>{{ $carReservation->car->color->name ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-sm-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-600">{{ translate('Condition') }}:</td>
                                        <td>{{ ucfirst($carReservation->car->condition) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-600">{{ translate('Mileage') }}:</td>
                                        <td>{{ $carReservation->car->formatted_milage }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-600">{{ translate('Transmission') }}:</td>
                                        <td>{{ $carReservation->car->transmission ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-600">{{ translate('Fuel Type') }}:</td>
                                        <td>{{ $carReservation->car->fuel_type ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        @if($carReservation->car->price)
                            <div class="mt-2">
                                <span class="fs-20 fw-700 text-success">{{ $carReservation->car->formatted_price }}</span>
                                <span class="text-muted">{{ translate('Car Price') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Customer Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Customer Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="mr-3">
                        @if($carReservation->user->avatar_original)
                            <img src="{{ uploaded_asset($carReservation->user->avatar_original) }}" class="rounded-circle" width="50" height="50" alt="">
                        @else
                            <div class="bg-light rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                                <i class="las la-user fs-20"></i>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h6 class="mb-1">{{ $carReservation->user->name }}</h6>
                        <div class="text-muted fs-12">{{ $carReservation->user->email }}</div>
                        @if($carReservation->user->phone)
                            <div class="text-muted fs-12">{{ $carReservation->user->phone }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @can('edit_car_reservations')
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Quick Actions') }}</h5>
                </div>
                <div class="card-body">
                    @if($carReservation->status == 'pending')
                        <form action="{{ route('admin.car-reservations.confirm', $carReservation->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('{{ translate('Are you sure you want to confirm this reservation?') }}')">
                                <i class="las la-check-circle"></i> {{ translate('Confirm Reservation') }}
                            </button>
                        </form>
                    @endif

                    @if($carReservation->status == 'confirmed')
                        <form action="{{ route('admin.car-reservations.mark-as-sold', $carReservation->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-info btn-block" onclick="return confirm('{{ translate('Are you sure you want to mark this car as sold?') }}')">
                                <i class="las la-handshake"></i> {{ translate('Mark as Sold') }}
                            </button>
                        </form>
                    @endif

                    @if($carReservation->can_be_cancelled)
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#cancel-reservation-modal">
                            <i class="las la-times-circle"></i> {{ translate('Cancel Reservation') }}
                        </button>
                    @endif

                    <!-- Payment Status Update -->
                    <button type="button" class="btn btn-warning btn-block mt-2" data-toggle="modal" data-target="#update-payment-modal">
                        <i class="las la-credit-card"></i> {{ translate('Update Payment') }}
                    </button>
                </div>
            </div>
        @endcan

        <!-- Timeline -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Timeline') }}</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">{{ translate('Reservation Created') }}</h6>
                            <p class="timeline-text">{{ $carReservation->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>

                    @if($carReservation->status == 'confirmed')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ translate('Reservation Confirmed') }}</h6>
                                <p class="timeline-text">{{ translate('Car status updated to reserved') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($carReservation->status == 'cancelled')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ translate('Reservation Cancelled') }}</h6>
                                <p class="timeline-text">{{ $carReservation->cancelled_at ? $carReservation->cancelled_at->format('M j, Y g:i A') : 'Date not recorded' }}</p>
                                @if($carReservation->cancellation_reason)
                                    <small class="text-muted">{{ $carReservation->cancellation_reason }}</small>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($carReservation->status == 'completed')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ translate('Car Sold') }}</h6>
                                <p class="timeline-text">{{ translate('Reservation completed successfully') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
<!-- Cancel Reservation Modal -->
@can('edit_car_reservations')
<div class="modal fade" id="cancel-reservation-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ translate('Cancel Reservation') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('admin.car-reservations.cancel', $carReservation->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('Cancellation Reason') }}</label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" placeholder="{{ translate('Enter reason for cancellation...') }}"></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="las la-exclamation-triangle"></i>
                        {{ translate('This action will cancel the reservation and make the car available for other customers.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Close') }}</button>
                    <button type="submit" class="btn btn-danger">{{ translate('Cancel Reservation') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Payment Modal -->
<div class="modal fade" id="update-payment-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ translate('Update Payment Status') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('admin.car-reservations.update-payment-status', $carReservation->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('Payment Status') }}</label>
                        <select name="payment_status" class="form-control" required>
                            <option value="pending" {{$carReservation->payment && $carReservation->payment->status == 'pending' ? 'selected' : '' }}>{{ translate('Pending') }}</option>
                            <option value="paid" {{ $carReservation->payment && $carReservation->payment->status == 'paid' ? 'selected' : '' }}>{{ translate('Paid') }}</option>
                            <option value="failed" {{ $carReservation->payment && $carReservation->payment->status == 'failed' ? 'selected' : '' }}>{{ translate('Failed') }}</option>
                            <option value="refunded" {{ $carReservation->payment && $carReservation->payment->status == 'refunded' ? 'selected' : '' }}>{{ translate('Refunded') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Transaction ID') }}</label>
                        <input type="text" name="transaction_id" class="form-control" value="{{ $carReservation->transaction_id }}" placeholder="{{ translate('Enter transaction ID...') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Update Payment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@section('script')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 3px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-text {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 0;
}
</style>
@endsection
