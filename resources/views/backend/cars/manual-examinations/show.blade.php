@extends('backend.layouts.app')

@php
$manualInspectionPhotoSlots = [
    'photo_front' => translate('Front view'),
    'photo_back' => translate('Rear view'),
    'photo_left' => translate('Left side'),
    'photo_right' => translate('Right side'),
    'photo_interior_front' => translate('Interior front'),
    'photo_interior_back' => translate('Interior rear'),
    'photo_engine' => translate('Engine'),
    'photo_trunk' => translate('Trunk'),
    'photo_odometer' => translate('Odometer'),
    'photo_dashboard' => translate('Dashboard'),
    'photo_vin_plate' => translate('VIN plate'),
    'photo_tires' => translate('Tires'),
    'photo_undercarriage' => translate('Undercarriage'),
];

$sectionPhotosBySection = (($manualExamination->metadata ?? [])['section_photos'] ?? []);
$manualPhotoUrl = fn ($path) => manual_examination_admin_photo_url($manualExamination, $path);
@endphp

@section('content')
<style>
.timeline {
    position: relative;
    padding: 0;
    margin: 0;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    left: 8px;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding: 10px 0 10px 30px;
    margin: 0;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 15px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content h6 {
    margin: 0;
    font-size: 14px;
}

.timeline-content small {
    font-size: 12px;
}
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Manual Examination') }}</h1>
            <p class="text-muted mb-0">{{ $manualExamination->inspection_number }}</p>
        </div>
        <div class="col-md-6 text-right">
            @if($manualExamination->status == 'completed')
                <a href="{{ route('admin.manual-examinations.download', $manualExamination->id) }}" class="btn btn-secondary">
                    <i class="las la-download mr-1"></i>{{ translate('Download PDF') }}
                </a>
            @endif
            <a href="{{ route('admin.manual-examinations.schedule', $manualExamination->id) }}" class="btn btn-info">
                <i class="las la-calendar mr-1"></i>{{ translate('Schedule') }}
            </a>
            <a href="{{ route('admin.manual-examinations.index') }}" class="btn btn-light">
                <i class="las la-arrow-left mr-1"></i>{{ translate('Back to List') }}
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Inspection Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Inspection Overview') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50"><strong>{{ translate('Inspection Number') }}:</strong></td>
                                <td>{{ $manualExamination->inspection_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Status') }}:</strong></td>
                                <td>{!! $manualExamination->status_badge !!}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Inspector') }}:</strong></td>
                                <td>{{ $manualExamination->inspector->shop_name ?? translate('Not Assigned') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Scheduled Date') }}:</strong></td>
                                <td>
                                    @if($manualExamination->scheduled_at)
                                        {{ date('M d, Y g:i A', strtotime($manualExamination->scheduled_at)) }}
                                    @else
                                        <span class="text-muted">{{ translate('Not Scheduled') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Created At') }}:</strong></td>
                                <td>{{ $manualExamination->created_at?->format('M d, Y g:i A') ?? translate('N/A') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50"><strong>{{ translate('Completed At') }}:</strong></td>
                                <td>
                                    @if($manualExamination->completed_at)
                                        {{ $manualExamination->completed_at->format('M d, Y g:i A') }}
                                    @else
                                        <span class="text-muted">{{ translate('Not Completed') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Total Score') }}:</strong></td>
                                <td>
                                    @if($manualExamination->total_score)
                                        <span class="h6 text-primary">{{ number_format($manualExamination->total_score, 1) }}%</span>
                                    @else
                                        <span class="text-muted">{{ translate('N/A') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Overall Condition') }}:</strong></td>
                                <td>
                                    @if($manualExamination->condition_display)
                                        <span class="badge badge-inline badge-info">
                                            {{ $manualExamination->condition_display }}
                                        </span>
                                    @else
                                        <span class="text-muted">{{ translate('N/A') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Created By') }}:</strong></td>
                                <td>{{ $manualExamination->requester?->name ?? translate('N/A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($manualExamination->inspector_notes)
                    <div class="mt-3">
                        <strong>{{ translate('Inspector Notes') }}:</strong>
                        <p class="mt-2 p-3 bg-light rounded">{{ $manualExamination->inspector_notes }}</p>
                    </div>
                @endif

                @if($manualExamination->recommendations)
                    <div class="mt-3">
                        <strong>{{ translate('Recommendations') }}:</strong>
                        <p class="mt-2 p-3 bg-light rounded">{{ $manualExamination->recommendations }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Inspection Sections -->
        @if($manualExamination->inspectionType && $manualExamination->inspectionType->sections)
            @foreach($sectionData as $data)
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $data['section']->name }}</h6>
                    </div>
                    <div class="card-body">
                        @if($data['section']->description)
                            <p class="text-muted small mb-3">{{ $data['section']->description }}</p>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ translate('Field') }}</th>
                                        <th>{{ translate('Value') }}</th>
                                        <th>{{ translate('Score') }}</th>
                                        <th>{{ translate('Notes') }}</th>
                                        <th>{{ translate('Flag') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['fields'] as $fieldData)
                                        <tr>
                                            <td>
                                                <div class="fw-600">{{ $fieldData['field']->name }}</div>
                                                @if($fieldData['field']->description)
                                                    <small class="text-muted">{{ $fieldData['field']->description }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $fieldData['value']?->formatted_value ?? translate('N/A') }}</td>
                                            <td>{{ $fieldData['value']?->score ?? translate('N/A') }}</td>
                                            <td>{{ $fieldData['value']?->notes ?? translate('N/A') }}</td>
                                            <td>
                                                @if($fieldData['value']?->is_flagged)
                                                    <span class="badge badge-danger">{{ translate('Flagged') }}</span>
                                                    @if($fieldData['value']->flag_reason)
                                                        <div><small>{{ $fieldData['value']->flag_reason }}</small></div>
                                                    @endif
                                                @else
                                                    <span class="badge badge-soft-secondary">{{ translate('No') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Car Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Car Information') }}</h6>
            </div>
            <div class="card-body">
                @if($manualExamination->car)
                    <div class="text-center mb-3">
                        @if($manualExamination->car->main_photo)
                            <img src="{{ uploaded_asset($manualExamination->car->main_photo) }}" alt="Car" class="img-fluid rounded" style="max-height: 200px;">
                        @else
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" alt="Car" class="img-fluid rounded" style="max-height: 200px;">
                        @endif
                    </div>

                    <h6 class="text-center mb-3">{{ $manualExamination->car->brand?->name ?? '' }} {{ $manualExamination->car->model?->name ?? '' }}</h6>

                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>{{ translate('VIN') }}:</strong></td>
                            <td>{{ $manualExamination->car->vin ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Brand') }}:</strong></td>
                            <td>{{ $manualExamination->car->brand?->name ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Model') }}:</strong></td>
                            <td>{{ $manualExamination->car->model?->name ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Category') }}:</strong></td>
                            <td>{{ $manualExamination->car->category?->getTranslation('name') ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Year') }}:</strong></td>
                            <td>{{ $manualExamination->car->manufacture_year ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Plate Number') }}:</strong></td>
                            <td>{{ $manualExamination->car->plate_number ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Color') }}:</strong></td>
                            <td>{{ $manualExamination->car->color?->getTranslation('name') ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Mileage') }}:</strong></td>
                            <td>{{ $manualExamination->car->formatted_milage ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Transmission') }}:</strong></td>
                            <td>{{ $manualExamination->car->transmission ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Fuel Type') }}:</strong></td>
                            <td>{{ $manualExamination->car->fuel_type ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Price') }}:</strong></td>
                            <td>{{ format_price($manualExamination->car->price ?? 0) }}</td>
                        </tr>
                    </table>

                    @if($manualExamination->car->description)
                        <div class="mt-2 pt-2 border-top">
                            <strong>{{ translate('Description') }}:</strong>
                            <p class="mt-1 text-muted small">{{ $manualExamination->car->description }}</p>
                        </div>
                    @endif
                @else
                    <p class="text-muted text-center">{{ translate('Car information not available') }}</p>
                @endif
            </div>
        </div>

        <!-- Inspection Type Information -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Inspection Type') }}</h6>
            </div>
            <div class="card-body">
                @if($manualExamination->inspectionType)
                    <h6>{{ $manualExamination->inspectionType->name }}</h6>
                    @if($manualExamination->inspectionType->description)
                        <p class="text-muted small">{{ $manualExamination->inspectionType->description }}</p>
                    @endif

                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>{{ translate('Total Sections') }}:</strong></td>
                            <td>{{ $manualExamination->inspectionType->sections->count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Total Fields') }}:</strong></td>
                            <td>{{ $manualExamination->inspectionType->sections->sum(function($section) { return $section->fields->count(); }) }}</td>
                        </tr>
                    </table>
                @else
                    <p class="text-muted">{{ translate('Inspection type information not available') }}</p>
                @endif
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Activity Timeline') }}</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">{{ translate('Inspection Created') }}</h6>
                            <small class="text-muted">{{ $manualExamination->created_at?->format('M d, Y g:i A') ?? translate('N/A') }}</small>
                        </div>
                    </div>

                    @if($manualExamination->started_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ translate('Inspection Started') }}</h6>
                                <small class="text-muted">{{ $manualExamination->started_at->format('M d, Y g:i A') }}</small>
                            </div>
                        </div>
                    @endif

                    @if($manualExamination->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ translate('Inspection Completed') }}</h6>
                                <small class="text-muted">{{ $manualExamination->completed_at->format('M d, Y g:i A') }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Inspector Information -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Inspector Info') }}</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>{{ translate('Shop Name') }}:</strong></td>
                        <td>{{ $manualExamination->inspector->shop_name ?? translate('N/A') }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ translate('Name') }}:</strong></td>
                        <td>{{ $manualExamination->inspector->user->name ?? translate('N/A') }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ translate('Email') }}:</strong></td>
                        <td>{{ $manualExamination->inspector->user->email ?? translate('N/A') }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ translate('Phone') }}:</strong></td>
                        <td>{{ $manualExamination->inspector->user->phone ?? translate('N/A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection