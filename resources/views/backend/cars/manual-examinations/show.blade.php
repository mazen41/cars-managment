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
$manualPhotoUrl = fn ($path) => manual_examination_photo_url($manualExamination, $path);
@endphp

@section('content')
<style>
    .inspection-photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }
    .inspection-photo-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }
    .inspection-photo-media {
        position: relative;
        aspect-ratio: 1 / 1;
        background: #eef2f7;
    }
    .inspection-photo-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .inspection-photo-fallback {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1rem;
        color: #64748b;
        background: linear-gradient(180deg, #f8fafc, #e2e8f0);
        font-size: 0.85rem;
        font-weight: 600;
    }
    .inspection-photo-card.is-missing .inspection-photo-fallback {
        display: flex;
    }
    .inspection-photo-card.is-missing img {
        display: none;
    }
    .inspection-photo-body {
        padding: 0.9rem 1rem 1rem;
    }
    .inspection-photo-title {
        margin: 0;
        font-weight: 700;
        color: #0f172a;
    }
    .inspection-photo-subtitle {
        margin-top: 0.3rem;
        font-size: 0.82rem;
        color: #64748b;
    }
    .inspection-photo-section {
        border-top: 1px solid #eef2f7;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
    }
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h3">{{ translate('Manual Examination Details') }}</h1>
            <div class="text-muted">{{ $manualExamination->inspection_number }}</div>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.manual-examinations.download', $manualExamination->id) }}" class="btn btn-primary">
                <i class="las la-download mr-1"></i>{{ translate('Download PDF') }}
            </a>
            <a href="{{ route('admin.manual-examinations.index') }}" class="btn btn-light">
                <i class="las la-arrow-left mr-1"></i>{{ translate('Back') }}
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Inspector Info') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="fw-600">{{ translate('Shop Name') }}</td>
                        <td>{{ $manualExamination->inspector->shop_name ?? translate('N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-600">{{ translate('Name') }}</td>
                        <td>{{ $manualExamination->inspector->user->name ?? translate('N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-600">{{ translate('Email') }}</td>
                        <td>{{ $manualExamination->inspector->user->email ?? translate('N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-600">{{ translate('Phone') }}</td>
                        <td>{{ $manualExamination->inspector->user->phone ?? translate('N/A') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Examination Summary') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="fw-600">{{ translate('Status') }}</td>
                        <td>{!! $manualExamination->status_badge !!}</td>
                    </tr>
                    <tr>
                        <td class="fw-600">{{ translate('Inspection Type') }}</td>
                        <td>{{ $manualExamination->inspectionType->name ?? translate('N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-600">{{ translate('Created At') }}</td>
                        <td>{{ $manualExamination->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-600">{{ translate('Total Score') }}</td>
                        <td>{{ $manualExamination->total_score ?? translate('N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-600">{{ translate('Overall Condition') }}</td>
                        <td>{{ $manualExamination->condition_display ?? translate('N/A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Car Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-600">{{ translate('VIN') }}</td>
                                <td>{{ $manualExamination->car->vin ?? translate('N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Plate Number') }}</td>
                                <td>{{ $manualExamination->car->plate_number ?? translate('N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Brand') }}</td>
                                <td>{{ $manualExamination->car->brand->name ?? translate('N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Model') }}</td>
                                <td>{{ $manualExamination->car->model->name ?? translate('N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Category') }}</td>
                                <td>{{ $manualExamination->car->category?->getTranslation('name') ?? translate('N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Color') }}</td>
                                <td>{{ $manualExamination->car->color?->getTranslation('name') ?? translate('N/A') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-600">{{ translate('Manufacture Year') }}</td>
                                <td>{{ $manualExamination->car->manufacture_year ?? translate('N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Mileage') }}</td>
                                <td>{{ $manualExamination->car->formatted_milage ?? translate('N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Condition') }}</td>
                                <td>{{ translate(ucfirst($manualExamination->car->condition ?? 'N/A')) }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Transmission') }}</td>
                                <td>{{ $manualExamination->car->transmission ?? translate('N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Fuel Type') }}</td>
                                <td>{{ $manualExamination->car->fuel_type ?? translate('N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">{{ translate('Price') }}</td>
                                <td>{{ $manualExamination->car->formatted_price ?? translate('N/A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>
                <h6>{{ translate('Description') }}</h6>
                <div>{!! $manualExamination->car->description ?? translate('N/A') !!}</div>

                <hr>
                <h6>{{ translate('Location') }}</h6>
                <p class="mb-0">
                    {{ $manualExamination->car->location ?? translate('N/A') }}
                    @if($manualExamination->car?->country || $manualExamination->car?->state || $manualExamination->car?->city)
                        <br>
                        <small class="text-muted">
                            {{ $manualExamination->car->country->name ?? '' }}
                            {{ $manualExamination->car->state->name ?? '' }}
                            {{ $manualExamination->car->city->name ?? '' }}
                        </small>
                    @endif
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Uploaded photos') }}</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-3">{{ translate('Vehicle & overview') }}</h6>
                <div class="inspection-photo-grid mb-4">
                    @php $hasVehiclePhotos = false; @endphp
                    @if(!empty($manualExamination->car?->main_photo))
                        @php $hasVehiclePhotos = true; @endphp
                        <div class="inspection-photo-card">
                            <div class="inspection-photo-media">
                                <img src="{{ uploaded_asset($manualExamination->car->main_photo) }}" alt="{{ translate('Main photo') }}" onerror="this.closest('.inspection-photo-card').classList.add('is-missing')">
                                <div class="inspection-photo-fallback">{{ translate('Image unavailable') }}</div>
                            </div>
                            <div class="inspection-photo-body">
                                <p class="inspection-photo-title">{{ translate('Main photo') }}</p>
                                <div class="inspection-photo-subtitle">{{ translate('Primary vehicle image') }}</div>
                            </div>
                        </div>
                    @endif
                    @foreach(array_filter(explode(',', (string) ($manualExamination->car?->photos ?? ''))) as $photoId)
                        @php $hasVehiclePhotos = true; @endphp
                        <div class="inspection-photo-card">
                            <div class="inspection-photo-media">
                                <img src="{{ uploaded_asset(trim($photoId)) }}" alt="{{ translate('Vehicle photo') }}" onerror="this.closest('.inspection-photo-card').classList.add('is-missing')">
                                <div class="inspection-photo-fallback">{{ translate('Image unavailable') }}</div>
                            </div>
                            <div class="inspection-photo-body">
                                <p class="inspection-photo-title">{{ translate('Vehicle photo') }}</p>
                                <div class="inspection-photo-subtitle">{{ translate('Uploaded from the vehicle listing') }}</div>
                            </div>
                        </div>
                    @endforeach
                    @foreach($manualInspectionPhotoSlots as $column => $label)
                        @if(!empty($manualExamination->{$column}))
                            @php $hasVehiclePhotos = true; @endphp
                            <div class="inspection-photo-card">
                                <div class="inspection-photo-media">
                                    <img src="{{ $manualPhotoUrl($manualExamination->{$column}) }}" alt="{{ $label }}" onerror="this.closest('.inspection-photo-card').classList.add('is-missing')">
                                    <div class="inspection-photo-fallback">{{ translate('Image unavailable') }}</div>
                                </div>
                                <div class="inspection-photo-body">
                                    <p class="inspection-photo-title">{{ $label }}</p>
                                    <div class="inspection-photo-subtitle">{{ translate('Manual examination upload') }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @unless($hasVehiclePhotos)
                        <div class="col-12"><p class="text-muted mb-0">{{ translate('No vehicle photos uploaded.') }}</p></div>
                    @endunless
                </div>

                @php
                    $fieldPhotoCount = 0;
                    foreach ($manualExamination->fieldValues as $__fv) {
                        if (!empty($__fv->file_attachments)) {
                            $fieldPhotoCount++;
                        }
                    }
                @endphp
                @if($fieldPhotoCount > 0)
                    <div class="inspection-photo-section">
                    <h6 class="mb-3">{{ translate('Photos by inspection field') }}</h6>
                    <div class="inspection-photo-grid">
                        @foreach($manualExamination->fieldValues as $fieldValue)
                            @foreach(($fieldValue->file_attachments ?? []) as $attachment)
                                @php $attachmentUrl = $attachment['url'] ?? (!empty($attachment['path']) ? $manualPhotoUrl($attachment['path']) : null); @endphp
                                @if(!empty($attachmentUrl))
                                    <div class="inspection-photo-card">
                                        <div class="inspection-photo-media">
                                            <img src="{{ $attachmentUrl }}" alt="{{ $fieldValue->field?->name ?? translate('Field photo') }}" onerror="this.closest('.inspection-photo-card').classList.add('is-missing')">
                                            <div class="inspection-photo-fallback">{{ translate('Image unavailable') }}</div>
                                        </div>
                                        <div class="inspection-photo-body">
                                            <p class="inspection-photo-title">{{ $fieldValue->field?->name ?? translate('Field') }}</p>
                                            <div class="inspection-photo-subtitle">{{ translate('Field attachment') }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                    </div>
                @endif

                @php
                    $hasSectionPhotos = false;
                    foreach ($sectionPhotosBySection as $__sectionItems) {
                        if (is_array($__sectionItems) && count($__sectionItems) > 0) {
                            $hasSectionPhotos = true;
                            break;
                        }
                    }
                @endphp
                @if($hasSectionPhotos)
                    <div class="inspection-photo-section">
                    <h6 class="mb-3">{{ translate('Photos by inspection section') }}</h6>
                    @foreach($sectionPhotosBySection as $sectionId => $items)
                        @if(!empty($items))
                            @php $sectionMeta = $manualExamination->inspectionType?->sections?->firstWhere('id', (int) $sectionId); @endphp
                            <div class="mb-4">
                                <div class="fw-600 mb-2">{{ $sectionMeta->name ?? (translate('Section') . ' #' . $sectionId) }}</div>
                                <div class="inspection-photo-grid">
                                    @foreach($items as $photoRow)
                                        @php $path = $photoRow['path'] ?? null; @endphp
                                        @if(!empty($path))
                                            <div class="inspection-photo-card">
                                                <div class="inspection-photo-media">
                                                    <img src="{{ $manualPhotoUrl($path) }}" alt="{{ $sectionMeta->name ?? translate('Section photo') }}" onerror="this.closest('.inspection-photo-card').classList.add('is-missing')">
                                                    <div class="inspection-photo-fallback">{{ translate('Image unavailable') }}</div>
                                                </div>
                                                <div class="inspection-photo-body">
                                                    <p class="inspection-photo-title">{{ $sectionMeta->name ?? translate('Section photo') }}</p>
                                                    <div class="inspection-photo-subtitle">{{ translate('Section upload') }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Inspection Fields') }}</h5>
            </div>
            <div class="card-body">
                @foreach($sectionData as $data)
                    <div class="mb-4">
                        <h6 class="pb-2 border-bottom">{{ $data['section']->name }}</h6>
                        @if($data['section']->description)
                            <p class="text-muted">{{ $data['section']->description }}</p>
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
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Inspector Notes') }}</h5>
            </div>
            <div class="card-body">
                <h6>{{ translate('Final Notes') }}</h6>
                <p>{{ $manualExamination->inspector_notes ?? translate('N/A') }}</p>
                <h6>{{ translate('Recommendations') }}</h6>
                <p class="mb-0">{{ $manualExamination->recommendations ?? translate('N/A') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
