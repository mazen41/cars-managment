@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.cars.index') }}" class="btn btn-light btn-sm mr-3">
                    <i class="las la-arrow-left"></i>
                </a>
                <div>
                    <h1 class="h3 mb-0">{{ $car->car_name ?? translate('Car Details') }}</h1>
                    <p class="text-muted mb-0">{{ translate('View detailed car information') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-right">
            <div class="btn-group" role="group">
                @can('update', $car)
                    <a href="{{ route('admin.cars.edit', $car->id) }}" class="btn btn-primary">
                        <i class="las la-edit mr-1"></i>{{ translate('Edit Car') }}
                    </a>
                @endcan

                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        <i class="las la-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="toggleStatus({{ $car->id }})">
                            <i class="las la-{{ $car->isPublished() ? 'eye-slash' : 'eye' }} mr-2"></i>
                            {{ $car->isPublished() ? translate('Unpublish') : translate('Publish') }}
                        </a>
                        @can('delete', $car)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#" onclick="confirmDelete({{ $car->id }})">
                                <i class="las la-trash mr-2"></i>{{ translate('Delete Car') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Car Status Badges -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center flex-wrap">
            <span class="mr-2 mb-1">{!! $car->moderation_status_html_badge !!}</span>
            <span class="mr-2 mb-1">{!! $car->car_status_html_badge !!}</span>
            @if($car->created_at)
                <small class="text-muted">{{ translate('Created') }}: {{ $car->created_at->format('M d, Y H:i') }}</small>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Car Images -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Photos') }}</h6>
            </div>
            <div class="card-body">
                @if($car->main_photo || $car->photos)
                    <div class="row">
                        <!-- Main Photo -->
                        @if($car->main_photo)
                            <div class="col-md-6 mb-3">
                                <div class="position-relative">
                                    <img src="{{ uploaded_asset($car->main_photo) }}" alt="{{ $car->name }}" class="img-fluid rounded shadow-sm">
                                    <span class="badge badge-inline badge-primary position-absolute" style="top: 10px; left: 10px;">
                                        {{ translate('Main Photo') }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        <!-- Additional Photos -->
                        @if($car->photos)
                            @php
                                $photos = is_string($car->photos) ? explode(',', $car->photos) : $car->photos;
                            @endphp
                            @foreach($photos as $photo)
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <img src="{{ uploaded_asset($photo) }}" alt="{{ $car->name }}" class="img-fluid rounded shadow-sm" style="height: 150px; object-fit: cover; width: 100%;">
                                </div>
                            @endforeach
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="las la-image text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">{{ translate('No photos available') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Car Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Car Information') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                       <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('VIN') }}</label>
                        <p class="mb-0 font-weight-medium">{{ $car->vin ?? translate('N/A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Brand') }}</label>
                        <p class="mb-0 font-weight-medium">{{ $car->brand->name ?? translate('N/A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Model') }}</label>
                        <p class="mb-0 font-weight-medium">{{ $car->model->name ?? translate('N/A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Category') }}</label>
                        <p class="mb-0 font-weight-medium">{{ $car->category->name ?? translate('N/A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Color') }}</label>
                        <p class="mb-0 font-weight-medium">{{ $car->color->name ?? translate('N/A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Condition') }}</label>
                        <span class="badge badge-inline badge-{{ $car->condition === 'excellent' ? 'success' : ($car->condition === 'good' ? 'primary' : ($car->condition === 'fair' ? 'warning' : 'danger')) }}">
                            {{ ucfirst($car->condition ?? translate('N/A')) }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Mileage') }}</label>
                        <p class="mb-0 font-weight-medium">{{ $car->milage ? number_format($car->milage) . ' km' : translate('N/A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Year') }}</label>
                        <p class="mb-0 font-weight-medium">{{ $car->manufacture_year ?? translate('N/A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Transmission') }}</label>
                        <p class="mb-0 font-weight-medium">{{ ucfirst(translate(str_replace('_', ' ', $car->transmission)) ?? translate('N/A')) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Fuel Type') }}</label>
                        <p class="mb-0 font-weight-medium">{{ ucfirst(translate(str_replace('_', ' ', $car->fuel_type)) ?? translate('N/A')) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">{{ translate('Location') }}</label>
                        <p class="mb-0 font-weight-medium">{{ $car->location ?? translate('N/A') }}</p>
                    </div>
                </div>

                @if($car->description)
                    <div class="mt-4">
                        <label class="text-muted">{{ translate('Description') }}</label>
                        <div class="bg-light p-3 rounded">
                            {!! $car->description !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Features -->
        @if($car->features && count($car->features) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Features') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($feature_sections as $section_name => $feature_list)
                        <div class="col-12 mb-3">
                            <h6 class="text-primary">{{ $section_name }}</h6>
                        </div>
                        @foreach ($feature_list as $feature)
                            <div class="col-md-4 col-sm-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="las la-check-circle text-success mr-2"></i>
                                    <span>{{ $feature->name }}</span>
                                </div>
                            </div>
                        @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Custom Fields -->
        @if($car->customFieldValues && count($car->customFieldValues) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Additional Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($car->customFieldValues as $fieldValue)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted">{{ $fieldValue->customField->name }}</label>
                                <span class="badge badge-inline badge-secondary mr-1">{{ $fieldValue->display_value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Price & Owner Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Price & Owner') }}</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h3 class="text-primary mb-0">
                        @if($car->price)
                            {{ single_price($car->price) }}
                        @else
                            {{ translate('Price not set') }}
                        @endif
                    </h3>
                </div>

                @if($car->user)
                    <div class="d-flex align-items-center">
                        @if($car->user->avatar)
                            <img src="{{ uploaded_asset($car->user->avatar) }}" alt="{{ $car->user->name }}" class="size-40px img-fit rounded-circle mr-3">
                        @else
                            <div class="size-40px bg-soft-primary rounded-circle d-flex align-items-center justify-content-center mr-3">
                                <i class="las la-user text-primary"></i>
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0">{{ $car->user->name }}</h6>
                            <small class="text-muted">{{ translate('Owner') }}</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Car Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Statistics') }}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="text-primary h5 mb-1">{{ \Carbon\Carbon::parse($car->created_at)->locale('ar')->diffForHumans() }}</div>
                        <small class="text-muted">{{ translate('Listed') }}</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-success h5 mb-1">{{ \Carbon\Carbon::parse($car->updated_at)->locale('ar')->diffForHumans() }}</div>
                        <small class="text-muted">{{ translate('Updated') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Car Reservations -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ translate('Car Reservations') }}</h6>
            </div>
            <div class="card-body">
                @if($car->reservations && count($car->reservations) > 0)
                    @foreach($car->reservations->take(5) as $reservation)
                        <div class="d-flex align-items-center mb-3 p-3 border rounded">
                            <div class="size-40px bg-soft-info rounded mr-3 d-flex align-items-center justify-content-center">
                                <i class="las la-calendar-check text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $reservation->user->name ?? 'Unknown User' }}</h6>
                                        <div class="d-flex align-items-center mb-1">
                                            @if($reservation->payment)
                                                <small class="text-muted mr-3">{{ translate('Amount') }}: {{ single_price($reservation->payment->amount) }}</small>
                                                <span class="badge badge-inline badge-success mr-2">{{ translate('Paid') }}</span>
                                            @else
                                                <span class="badge badge-inline badge-warning mr-2">{{ translate('Pending Payment') }}</span>
                                            @endif
                                            <span class="badge badge-inline {{ $reservation->status_badge_class }}">{{ ucfirst($reservation->status) }}</span>
                                        </div>
                                        <small class="text-muted">{{ translate('Reserved') }}: {{ $reservation->reserved_at->format('M d, Y H:i') }}</small>
                                    </div>
                                    <div class="text-right">
                                        @can('view_car_reservations')
                                            <a href="{{ route('admin.car-reservations.show', $reservation->id) }}" class="btn btn-sm btn-outline-primary">
                                                {{ translate('View') }}
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($car->reservations->count() > 5)
                        <div class="text-center">
                            <a href="{{ route('admin.car-reservations.index', ['car_id' => $car->id]) }}" class="btn btn-sm btn-outline-secondary">
                                {{ translate('View All Reservations') }} ({{ $car->reservations->count() }})
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="las la-calendar-check text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">{{ translate('No reservations yet') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Car Inspections -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Car Inspections') }}</h6>
            </div>
            <div class="card-body">
              @if ($car->inspections && count($car->inspections) > 0)
                    @foreach ($car->inspections as $inspection)
                    <a href="{{ route('admin.car-inspections.show', $inspection->id) }}" target="_blank">
                        <div class="d-flex align-items-center mb-3">
                            <div class="size-40px bg-soft-info rounded mr-3 d-flex align-items-center justify-content-center">
                                <i class="las la-calendar text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $inspection->inspection_number }}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $inspection->scheduled_at }}</small>
                            {!! $inspection->status_badge !!}
                        </div>
                        <small class="text-muted">{{ $inspection->inspector->shop_name ?? translate('No inspector') }}</small>
                    </div>
                </div>
                    @endforeach
              @else
                <i class="las la-calendar-check text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted">{{ translate('No inspections available') }}</p>
              @endif
            </div>
        </div>
    </div>
</div>

<!-- Related Cars -->
@if(isset($relatedCars) && count($relatedCars) > 0)
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">{{ translate('Related Cars') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($relatedCars as $relatedCar)
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="card h-100">
                            <div class="position-relative">
                                @if($relatedCar->main_photo)
                                    <img src="{{ uploaded_asset($relatedCar->main_photo) }}" alt="{{ $relatedCar->name }}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                                        <i class="las la-car text-muted" style="font-size: 2rem;"></i>
                                    </div>
                                @endif
                                <div class="position-absolute top-0 right-0 m-1">
                                    {!! $relatedCar->moderation_status_html_badge !!}
                                </div>
                            </div>
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1 text-truncate">{{ $relatedCar->name }}</h6>
                                <p class="card-text text-muted small mb-1">{{ $relatedCar->brand->name ?? '' }} {{ $relatedCar->model->name ?? '' }}</p>
                                @if($relatedCar->price)
                                    <p class="text-primary font-weight-bold mb-2">{{ single_price($relatedCar->price) }}</p>
                                @endif
                                <a href="{{ route('admin.cars.show', $relatedCar->id) }}" class="btn btn-sm btn-outline-primary btn-block">
                                    {{ translate('View') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@endsection

@section('script')
<script>
function toggleStatus(carId) {
    if (confirm('{{ translate("Are you sure you want to change the status of this car?") }}')) {
        $.post('{{ route("admin.cars.toggle-status", ":id") }}'.replace(':id', carId), {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                AIZ.plugins.notify('danger', '{{ translate("Something went wrong") }}');
            }
        })
        .fail(function() {
            AIZ.plugins.notify('danger', '{{ translate("Something went wrong") }}');
        });
    }
}

function confirmDelete(carId) {
    if (confirm('{{ translate("Are you sure you want to delete this car? This action cannot be undone.") }}')) {
        $('#delete-form-' + carId).submit();
    }
}

function copyCarLink() {
    var url = '{{ route("admin.cars.show", $car->id) }}';
    navigator.clipboard.writeText(url).then(function() {
        AIZ.plugins.notify('success', '{{ translate("Link copied to clipboard") }}');
    }, function(err) {
        console.error('Could not copy text: ', err);
        AIZ.plugins.notify('danger', '{{ translate("Failed to copy link") }}');
    });
}

// Delete form (hidden)
@can('delete', $car)
    $('body').append('<form id="delete-form-{{ $car->id }}" action="{{ route("admin.cars.destroy", $car->id) }}" method="POST" style="display: none;">' +
        '@csrf' +
        '@method("DELETE")' +
        '</form>');
@endcan
</script>
@endsection
