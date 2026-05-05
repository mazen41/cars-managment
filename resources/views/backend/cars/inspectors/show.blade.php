@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Car Inspector Details') }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.car-inspectors.index') }}" class="btn btn-circle btn-light">
                <span>{{ translate('Back to Inspectors') }}</span>
            </a>
            @can('edit_car_inspectors')
                <a href="{{ route('admin.car-inspectors.edit', $carInspector->id) }}" class="btn btn-circle btn-info">
                    <span>{{ translate('Edit Inspector') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <!-- Inspector Profile -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <img src="{{ $carInspector->image_url }}" alt="{{ $carInspector->full_name }}" class="rounded-circle size-100px">
                </div>
                <h4 class="mb-1">{{ $carInspector->full_name }}</h4>
                <p class="text-muted">{{ $carInspector->shop_name }}</p>

                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-center">
                            <h5 class="fw-700 mb-0">{{ $stats['total_inspections'] }}</h5>
                            <small class="text-muted">{{ translate('Total Inspections') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center">
                            <h5 class="fw-700 mb-0">{{ $stats['completed_inspections'] }}</h5>
                            <small class="text-muted">{{ translate('Completed') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center">
                            <h5 class="fw-700 mb-0">{{ number_format($carInspector->rating, 1) }}</h5>
                            <small class="text-muted">{{ translate('Rating') }}</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="text-left">
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>{{ translate('Status') }}:</strong></div>
                        <div class="col-sm-7">
                            @if($carInspector->is_active)
                                <span class="badge badge-inline badge-success">{{ translate('Active') }}</span>
                            @else
                                <span class="badge badge-inline badge-danger">{{ translate('Inactive') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>{{ translate('Email') }}:</strong></div>
                        <div class="col-sm-7">{{ $carInspector->email }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>{{ translate('Phone') }}:</strong></div>
                        <div class="col-sm-7">{{ $carInspector->phone ?? translate('Not provided') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>{{ translate('Experience') }}:</strong></div>
                        <div class="col-sm-7">{{ $carInspector->experience_years ? $carInspector->experience_years . ' ' . translate('years') : translate('Not specified') }}</div>
                    </div>
                    @if($carInspector->certification_number)
                        <div class="row mb-2">
                            <div class="col-sm-5"><strong>{{ translate('Certification') }}:</strong></div>
                            <div class="col-sm-7">{{ $carInspector->certification_number }}</div>
                        </div>
                    @endif
                    @if($carInspector->country)
                        <div class="row mb-2">
                            <div class="col-sm-5"><strong>{{ translate('Country') }}:</strong></div>
                            <div class="col-sm-7">{{ $carInspector->country->name }}</div>
                        </div>
                    @endif
                    @if($carInspector->state)
                        <div class="row mb-2">
                            <div class="col-sm-5"><strong>{{ translate('State') }}:</strong></div>
                            <div class="col-sm-7">{{ $carInspector->state->name }}</div>
                        </div>
                    @endif
                    @if($carInspector->city)
                        <div class="row mb-2">
                            <div class="col-sm-5"><strong>{{ translate('City') }}:</strong></div>
                            <div class="col-sm-7">{{ $carInspector->city->getTranslation('name') }}</div>
                        </div>
                    @endif
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>{{ translate('Member Since') }}:</strong></div>
                        <div class="col-sm-7">{{ $carInspector->created_at->format('M d, Y') }}</div>
                    </div>
                </div>

                @can('manage_car_inspector_payments')
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            @if($carInspector->admin_to_pay > 0)
                                <div class="alert alert-warning">
                                    <strong>{{ translate('Amount Owed') }}:</strong>
                                    <h5 class="mb-0">{{ format_price($carInspector->admin_to_pay) }}</h5>
                                    <a href="{{ route('admin.car-inspectors.show-payment-form', $carInspector->id) }}" class="btn btn-sm btn-primary mt-2">
                                        {{ translate('Make Payment') }}
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <strong>{{ translate('Payment Status') }}:</strong>
                                    <p class="mb-0">{{ translate('All payments up to date') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Inspector Details -->
    <div class="col-lg-8">
        <!-- Shop Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Shop Information') }}</h6>
            </div>
            <div class="card-body">
                @if($carInspector->banner_image)
                    <div class="mb-3">
                        <img src="{{ $carInspector->banner_image_url }}" alt="{{ $carInspector->shop_name }}" class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>{{ translate('Shop Name') }}:</strong>
                            <p>{{ $carInspector->shop_name }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>{{ translate('Address') }}:</strong>
                            <p>{{ $carInspector->address }}</p>
                        </div>
                        @if($carInspector->latitude && $carInspector->longitude)
                            <div class="mb-3">
                                <strong>{{ translate('Location') }}:</strong>
                                <p>{{ $carInspector->latitude }}, {{ $carInspector->longitude }}</p>
                                <a href="https://maps.google.com/?q={{ $carInspector->latitude }},{{ $carInspector->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    {{ translate('View on Map') }}
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($carInspector->description)
                            <div class="mb-3">
                                <strong>{{ translate('Description') }}:</strong>
                                <p>{{ $carInspector->description }}</p>
                            </div>
                        @endif

                        @if($carInspector->services_offered)
                            <div class="mb-3">
                                <strong>{{ translate('Services Offered') }}:</strong>
                                <div class="mt-2">
                                    @foreach($carInspector->services_offered as $service)
                                        <span class="badge badge-inline badge-soft-info mr-1">{{ ucwords(str_replace('_', ' ', $service)) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Working Hours -->
        @if($carInspector->working_hours)
            <div class="card mb-3 d-none">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Working Hours') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        @endphp
                        @foreach($days as $index => $day)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ translate($dayNames[$index]) }}:</strong>
                                    @if(isset($carInspector->working_hours[$day]['active']) && $carInspector->working_hours[$day]['active'])
                                        <span>
                                            {{ $carInspector->working_hours[$day]['open'] ?? '09:00' }} -
                                            {{ $carInspector->working_hours[$day]['close'] ?? '18:00' }}
                                        </span>
                                    @else
                                        <span class="text-muted">{{ translate('Closed') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistics -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Statistics') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="border rounded p-3">
                            <h4 class="text-primary mb-1">{{ $stats['pending_inspections'] }}</h4>
                            <small class="text-muted">{{ translate('Pending Inspections') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="border rounded p-3">
                            <h4 class="text-success mb-1">{{ format_price($stats['total_earnings']) }}</h4>
                            <small class="text-muted">{{ translate('Total Earnings') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="border rounded p-3">
                            <h4 class="text-info mb-1">{{ format_price($stats['total_paid']) }}</h4>
                            <small class="text-muted">{{ translate('Total Paid') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="border rounded p-3">
                            <h4 class="text-warning mb-1">{{ format_price($stats['balance_owed']) }}</h4>
                            <small class="text-muted">{{ translate('Balance Owed') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payment History -->
        @can('manage_car_inspector_payments')
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ translate('Recent Payment History') }}</h6>
                    <a href="{{ route('admin.car-inspectors.payments', $carInspector->id) }}" class="btn btn-sm btn-outline-primary">
                        {{ translate('View All') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($recentPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ translate('Date') }}</th>
                                        {{-- <th>{{ translate('Type') }}</th> --}}
                                        <th>{{ translate('Amount') }}</th>
                                        <th>{{ translate('Method') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPayments as $payment)
                                        <tr>
                                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                            {{-- <td>
                                                <span class="badge badge-inline {{ $payment->type_badge_class }}">
                                                    {{ $payment->type_display }}
                                                </span>
                                            </td> --}}
                                            <td>{{ $payment->formatted_amount }}</td>
                                            <td>{{ $payment->payment_method ?? translate('N/A') }}</td>
                                            <td>
                                                <span class="badge badge-inline {{ $payment->status_badge_class }}">
                                                    {{ $payment->status_display }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ translate('No payment history found.') }}</p>
                    @endif
                </div>
            </div>
        @endcan
    </div>
</div>

@endsection
