@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Schedule New Inspection') }}</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.car-inspections.index') }}" class="btn btn-light">
                <i class="las la-arrow-left mr-1"></i>{{ translate('Back to Inspections') }}
            </a>
        </div>
    </div>
</div>

<form action="{{ route('admin.car-inspections.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Inspection Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Car') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="car_id" id="car_id" data-live-search="true" required>
                                <option value="">{{ translate('Select Car') }}</option>
                                @foreach($cars as $car)
                                    <option value="{{ $car->id }}" data-content="
                                        <div class='d-flex align-items-center'>
                                            @if($car->thumbnail)
                                                <img src='{{ uploaded_asset($car->thumbnail->file_name) }}' class='size-30px img-fit rounded mr-2'>
                                            @else
                                                <div class='size-30px bg-soft-secondary rounded mr-2 d-flex align-items-center justify-content-center'>
                                                    <i class='las la-car text-secondary'></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div>{{ $car->name }}</div>
                                                <small class='text-muted'>{{ $car->brand->name ?? '' }} {{ $car->model->name ?? '' }}</small>
                                            </div>
                                        </div>
                                    ">
                                        {{ $car->name }} - {{ $car->brand->name ?? '' }} {{ $car->model->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{ translate('Select the car to be inspected') }}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Inspection Type') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="inspection_type_id" id="inspection_type_id" required>
                                <option value="">{{ translate('Select Inspection Type') }}</option>
                                @foreach($inspectionTypes as $type)
                                    <option value="{{ $type->id }}"
                                            data-description="{{ $type->description }}"
                                            data-sections="{{ $type->total_sections }}"
                                            data-fields="{{ $type->total_fields }}">
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="inspection_type_info" class="mt-2" style="display: none;">
                                <div class="alert alert-soft-info">
                                    <div id="type_description"></div>
                                    <div class="mt-2">
                                        <small>
                                            <strong>{{ translate('Sections') }}:</strong> <span id="type_sections">0</span> |
                                            <strong>{{ translate('Fields') }}:</strong> <span id="type_fields">0</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Inspector') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="inspector_id" id="inspector_id" data-live-search="true" required>
                                <option value="">{{ translate('Select Inspector') }}</option>
                                @foreach($inspectors as $inspector)
                                    <option value="{{ $inspector->id }}" data-content="
                                        <div class='d-flex align-items-center'>
                                            @if($inspector->avatar)
                                                <img src='{{ uploaded_asset($inspector->image) }}' class='size-30px img-fit rounded-circle mr-2'>
                                            @else
                                                <div class='size-30px bg-soft-primary rounded-circle mr-2 d-flex align-items-center justify-content-center'>
                                                    <i class='las la-user text-primary'></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div>{{ $inspector->shop_name }}</div>
                                                <small class='text-muted'>{{ $inspector->user->email }}</small> - <small class='text-muted'>{{ $inspector->user->phone }}</small>
                                            </div>
                                        </div>
                                    ">
                                        {{ $inspector->user->name }} ({{ $inspector->user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{ translate('Select the inspector who will conduct this inspection') }}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Scheduled Date & Time') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="datetime-local"
                                   class="form-control"
                                   name="scheduled_at"
                                   id="scheduled_at"
                                   min="{{ date('Y-m-d\TH:i') }}"
                                   required>
                            <small class="form-text text-muted">{{ translate('When should this inspection be conducted?') }}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Initial Notes') }}</label>
                        <div class="col-md-9">
                            <textarea class="form-control"
                                      name="inspector_notes"
                                      id="inspector_notes"
                                      rows="4"
                                      placeholder="{{ translate('Any initial notes or special instructions for this inspection...') }}"></textarea>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <!-- Car Preview Card -->
            <div class="card" id="car_preview" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Selected Car') }}</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img id="car_image" src="" alt="Car" class="img-fluid rounded" style="max-height: 150px;">
                    </div>
                    <h6 class="text-center mb-3" id="car_name"></h6>
                    <div id="car_details"></div>
                </div>
            </div>


            <!-- Inspector Preview Card -->
            <div class="card" id="inspector_preview" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Selected Inspector') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img id="inspector_avatar" src="" alt="Inspector" class="size-50px img-fit rounded-circle mr-3">
                        <div>
                            <h6 class="mb-1" id="inspector_name"></h6>
                            <small class="text-muted" id="inspector_email"></small>
                        </div>
                    </div>
                    <div id="inspector_stats">
                        <!-- Inspector statistics will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="text-right">
                        <button type="button" class="btn btn-soft-secondary mr-2" onclick="window.history.back()">
                            {{ translate('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-calendar-plus mr-1"></i>{{ translate('Schedule Inspection') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@section('script')
<script type="text/javascript">
$(document).ready(function() {
    // Car selection change
    $('#car_id').on('change', function() {
        var carId = $(this).val();
        if (carId) {
            // Get car details via AJAX
            $.get('/admin/cars/' + carId + '/details', function(data) {
                if (data.success) {
                    updateCarPreview(data.car);
                }
            });
        } else {
            $('#car_preview').hide();
        }
    });

    // Inspection type selection change
    $('#inspection_type_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var description = selectedOption.data('description');
        var sections = selectedOption.data('sections');
        var fields = selectedOption.data('fields');

        if ($(this).val()) {
            $('#type_description').text(description || '{{ translate("No description available") }}');
            $('#type_sections').text(sections || 0);
            $('#type_fields').text(fields || 0);
            $('#inspection_type_info').show();
        } else {
            $('#inspection_type_info').hide();
        }
    });

    // Inspector selection change
    $('#inspector_id').on('change', function() {
        var inspectorId = $(this).val();
        if (inspectorId) {
            // Get inspector details via AJAX
            $.get('/admin/inspectors/' + inspectorId + '/stats', function(data) {
                if (data.success) {
                    updateInspectorPreview(data.inspector);
                }
            });
        } else {
            $('#inspector_preview').hide();
        }
    });

});

function updateCarPreview(car) {
    $('#car_image').attr('src', car.thumbnail || '{{ static_asset("assets/img/placeholder.jpg") }}');
    $('#car_name').text(car.name);

    var details = '<table class="table table-borderless table-sm">';
    details += '<tr><td><strong>{{ translate("Brand") }}:</strong></td><td>' + (car.brand || '-') + '</td></tr>';
    details += '<tr><td><strong>{{ translate("Model") }}:</strong></td><td>' + (car.model || '-') + '</td></tr>';
    details += '<tr><td><strong>{{ translate("Year") }}:</strong></td><td>' + (car.year || '-') + '</td></tr>';
    details += '<tr><td><strong>{{ translate("Price") }}:</strong></td><td>' + (car.price || '-') + '</td></tr>';
    details += '</table>';

    $('#car_details').html(details);
    $('#car_preview').show();
}

function updateInspectorPreview(inspector) {
    $('#inspector_avatar').attr('src', inspector.avatar || '{{ static_asset("assets/img/avatar-place.png") }}');
    $('#inspector_name').text(inspector.name);
    $('#inspector_email').text(inspector.email);

    var stats = '<div class="row text-center">';
    stats += '<div class="col-6"><div class="text-primary h6 mb-0">' + inspector.total_inspections + '</div><small>{{ translate("Inspections") }}</small></div>';
    stats += '<div class="col-6"><div class="text-success h6 mb-0">' + inspector.average_score + '%</div><small>{{ translate("Avg Score") }}</small></div>';
    stats += '</div>';

    $('#inspector_stats').html(stats);
    $('#inspector_preview').show();
}

function updateScheduleSummary() {
    var scheduledTime = $('#scheduled_at').val();
    var hours = parseInt($('input[name="estimated_duration_hours"]').val()) || 0;
    var minutes = parseInt($('input[name="estimated_duration_minutes"]').val()) || 0;

    if (scheduledTime) {
        var scheduledDate = new Date(scheduledTime);
        var completionDate = new Date(scheduledDate.getTime() + (hours * 60 + minutes) * 60000);

        $('#scheduled_time_display').text(scheduledDate.toLocaleString());
        $('#completion_time_display').text(completionDate.toLocaleString());
        $('#duration_display').text(hours + 'h ' + minutes + 'm');
        $('#schedule_summary').show();
    } else {
        $('#schedule_summary').hide();
    }
}

// Form validation
$('form').on('submit', function(e) {
    var scheduledTime = new Date($('#scheduled_at').val());
    var now = new Date();

    if (scheduledTime <= now) {
        e.preventDefault();
        AIZ.plugins.notify('warning', '{{ translate("Scheduled time must be in the future") }}');
        return false;
    }

    // Show loading state
    $('button[type="submit"]').prop('disabled', true).html('<i class="las la-spinner la-spin mr-1"></i>{{ translate("Scheduling...") }}');
});
</script>
@endsection

