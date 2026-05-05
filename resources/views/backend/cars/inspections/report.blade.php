@extends('backend.layouts.app')

@section('content')
<style>
.report-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 30px;
    margin-bottom: 30px;
}

.inspection-details {
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.section-card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.section-header {
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-content {
    padding: 20px;
}

.field-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 12px 0;
    border-bottom: 1px solid #f1f1f1;
}

.field-row:last-child {
    border-bottom: none;
}

.field-label {
    font-weight: 600;
    color: #87919a;
    flex: 0 0 40%;
}

.field-value {
    flex: 1;
    margin-left: 20px;
}

.status-badge-inline {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-completed {
    color: #155724;
}

.status-pending {
    color: #856404;
}

.status-failed {
    color: #721c24;
}

.completion-bar {
    border-radius: 10px;
    height: 8px;
    overflow: hidden;
}

.completion-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    transition: width 0.3s ease;
}

.car-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #dee2e6;
}

.print-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    font-weight: 600;
}

.print-btn:hover {
    background: linear-gradient(135deg, #218838, #1fa187);
    color: white;
}
.notes-section {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 15px;
    margin-top: 20px;
}

.notes-title {
    font-weight: bold;
    margin-bottom: 10px;
    color: #856404;
}
@media print {
    .no-print, .aiz-topbar {
        display: none !important;
    }

    .report-header {
        background: #667eea !important;
        -webkit-print-color-adjust: exact;
    }

    .section-card {
        page-break-inside: avoid;
        margin-bottom: 15px;
    }
}
</style>

<div class="aiz-titlebar text-left mt-2 mb-3 no-print">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Inspection Report') }}</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.car-inspections.index') }}" class="btn btn-light">
                <i class="las la-arrow-left"></i> {{ translate('Back to Inspections') }}
            </a>
            <a href="{{ route('admin.car-inspections.pdf', $carInspection->id) }}" class="btn print-btn ml-2">
                <i class="las la-download"></i> {{ translate('Download PDF') }}
            </a>
            <button onclick="window.print()" class="btn btn-secondary ml-2">
                <i class="las la-print"></i> {{ translate('Print') }}
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Report Header -->
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">{{ translate('Car Inspection Report') }}</h2>
                    <h4 class="mb-3">{{ translate('Inspection #') }}{{ $carInspection->inspection_number }}</h4>
                    <p class="mb-0">
                        <i class="las la-calendar"></i> {{ translate('Generated on') }}: {{ now()->format('F j, Y \a\t g:i A') }}
                    </p>
                    <!-- Report score -->
                @if($carInspection->total_score)
                    <div class="d-flex align-items-center">
                        <span>{{translate('Score')}}: </span>
                        <span class="mr-2">{{ number_format($carInspection->total_score, 1) }}%</span>
                        @php
                            $score_color = 'danger';
                            if($carInspection->total_score >= 90) $score_color = 'success';
                            elseif($carInspection->total_score >= 75) $score_color = 'info';
                            elseif($carInspection->total_score >= 60) $score_color = 'warning';
                        @endphp
                        <div class="progress flex-1" style="height: 6px; width: 200px;">
                            <div class="progress-bar bg-{{ $score_color }}" style="width: {{ $carInspection->total_score }}%"></div>
                        </div>
                    </div>
                @endif
                </div>

                <div class="col-md-4 text-right">
                    @if($carInspection->car->main_photo)
                        <img src="{{ uploaded_asset($carInspection->car->main_photo) }}" alt="Car Image" class="car-image">
                    @else
                        <div class="car-image d-flex align-items-center justify-content-center bg-secondary">
                            <i class="las la-car text-white"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Inspection Details -->
        <div class="inspection-details">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">{{ translate('Vehicle Information') }}</h5>
                     <div class="field-row">
                        <span class="field-label">{{ translate('VIN') }}:</span>
                        <span class="field-value">{{ $carInspection->car->vin ?? translate('N/A') }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Brand') }}:</span>
                        <span class="field-value">{{ $carInspection->car->brand->name ?? translate('N/A') }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Model') }}:</span>
                        <span class="field-value">{{ $carInspection->car->model->name ?? translate('N/A') }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Category') }}:</span>
                        <span class="field-value">{{ $carInspection->car->category->name ?? translate('N/A') }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Year') }}:</span>
                        <span class="field-value">{{ $carInspection->car->manufacture_year ?? translate('N/A') }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Transmission') }}:</span>
                        <span class="field-value">{{ translate(ucfirst(str_replace('_', ' ', $carInspection->car->transmission))) ?? translate('N/A') }}</span>
                    </div>
                     <div class="field-row">
                        <span class="field-label">{{ translate('Fuel type') }}:</span>
                        <span class="field-value">{{ translate(ucfirst(str_replace('_', ' ', $carInspection->car->fuel_type))) ?? translate('N/A') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3">{{ translate('Inspection Information') }}</h5>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Type') }}:</span>
                        <span class="field-value">{{ $carInspection->inspectionType->name }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Inspector') }}:</span>
                        <span class="field-value">{{ $carInspection->inspector->name ?? translate('N/A') }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Requester') }}:</span>
                        <span class="field-value">{{ $carInspection->requester->name ?? translate('N/A') }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Scheduled Date') }}:</span>
                        <span class="field-value">{{ $carInspection->scheduled_at ? $carInspection->scheduled_at->format('M j, Y g:i A') : translate('N/A') }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Completed Date') }}:</span>
                        <span class="field-value">{{ $carInspection->completed_at ? $carInspection->completed_at->format('M j, Y g:i A') : translate('N/A') }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">{{ translate('Status') }}:</span>
                        <span class="field-value">
                            <span class="status-badge-inline status-{{ strtolower($carInspection->status) }}">
                                {{ ucfirst($carInspection->status) }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Summary -->
        <div class="section-card">
            <div class="section-header">
                <h5 class="mb-0">{{ translate('Inspection Summary') }}</h5>
                <span class="text-muted">{{ translate('Overall Progress') }}</span>
            </div>
            <div class="section-content">
                <div class="row">
                    @php
                        $totalSections = count($sectionData);
                        $completedSections = 0;
                        $totalFields = 0;
                        $completedFields = 0;

                        foreach($sectionData as $section) {
                            if($section['completion']['completion_percentage'] == 100) {
                                $completedSections++;
                            }
                            $totalFields += $section['completion']['total_fields'];
                            $completedFields += $section['completion']['completed_fields'];
                        }

                        $overallCompletion = $totalFields > 0 ? round(($completedFields / $totalFields) * 100, 1) : 0;
                    @endphp

                    <div class="col-md-3 text-center">
                        <h3 class="text-primary">{{ $totalSections }}</h3>
                        <p class="text-muted mb-0">{{ translate('Total Sections') }}</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h3 class="text-success">{{ $completedSections }}</h3>
                        <p class="text-muted mb-0">{{ translate('Completed Sections') }}</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h3 class="text-info">{{ $totalFields }}</h3>
                        <p class="text-muted mb-0">{{ translate('Total Fields') }}</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h3 class="text-warning">{{ $overallCompletion }}%</h3>
                        <p class="text-muted mb-0">{{ translate('Overall Completion') }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="completion-bar">
                        <div class="completion-fill" style="width: {{ $overallCompletion }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inspection Sections -->
        @foreach($sectionData as $sectionId => $data)
            <div class="section-card">
                <div class="section-header">
                    <div>
                        <h5 class="mb-1">{{ $data['section']->name }}</h5>
                        @if($data['section']->description)
                            <p class="text-muted mb-0 small">{{ $data['section']->description }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="badge-inline badge-{{ $data['completion']['completion_percentage'] == 100 ? 'success' : ($data['completion']['completion_percentage'] > 0 ? 'warning' : 'secondary') }}">
                            {{ $data['completion']['completed_fields'] }}/{{ $data['completion']['total_fields'] }} {{ translate('Fields') }}
                        </span>
                        <div class="completion-bar mt-2" style="width: 100px;">
                            <div class="completion-fill" style="width: {{ $data['completion']['completion_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="section-content">
                    @if(count($data['fields']) > 0)
                        @foreach($data['fields'] as $fieldData)
                            <div class="field-row">
                                <div class="field-label">
                                    {{ $fieldData['field']->name }}
                                    @if($fieldData['field']->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </div>
                                <div class="field-value">
                                    @if($fieldData['value'])
                                        @switch($fieldData['field']->field_type)
                                            @case('text')
                                            @case('textarea')
                                                {{ $fieldData['value']->value ?: translate('No value provided') }}
                                                @break

                                            @case('select')
                                            @case('radio')
                                            @if ($fieldData['value']->value)
                                                  <span class="badge badge-inline badge-info mr-1">{{ $fieldData['value']->value }}</span>
                                                  @else
                                                    {{ translate('No selection made') }}
                                            @endif

                                                @break

                                            @case('checkbox')
                                                @php
                                                    $checkboxValues = json_decode($fieldData['value']->value, true);
                                                @endphp
                                                @if(is_array($checkboxValues) && count($checkboxValues) > 0)
                                                    @foreach($checkboxValues as $option)
                                                        <span class="badge badge-inline badge-info mr-1">{{ $option }}</span>
                                                    @endforeach
                                                @else
                                                    {{ translate('No selections made') }}
                                                @endif
                                                @break

                                            @case('file')
                                                @if($fieldData['value']->value)
                                                    <a href="{{ uploaded_asset($fieldData['value']->value) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="las la-download"></i> {{ translate('View File') }}
                                                    </a>
                                                @else
                                                    {{ translate('No file uploaded') }}
                                                @endif
                                                @break

                                            @case('date')
                                                {{ $fieldData['value']->value ? \Carbon\Carbon::parse($fieldData['value']->value)->format('M j, Y') : translate('No date selected') }}
                                                @break

                                            @case('number')
                                                {{ $fieldData['value']->value ?: translate('No value provided') }}
                                                @break

                                            @default
                                                {{ $fieldData['value']->value ?: translate('No value provided') }}
                                        @endswitch

                                        @if($fieldData['value']->notes)
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <strong>{{ translate('Notes') }}:</strong> {{ $fieldData['value']->notes }}
                                                </small>
                                            </div>
                                        @endif
                                        @if($fieldData['value']->file_attachments  && count($fieldData['value']->file_attachments) > 0)
                                        <div class="row">
                                            @foreach ( $fieldData['value']->file_attachments as $attachment)

                                                <div class="col-md-4 mb-3">
                                                    <div class="card">
                                                        <img src="{{ $attachment['url'] }}" class="card-img-top" alt="Product Photo" style="height: 200px; object-fit: cover;">
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @else
                                        <span class="text-muted">{{ translate('Not completed') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-4">{{ translate('No fields defined for this section') }}</p>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Notes Section -->
        @if($carInspection->inspector_notes)
            <div class="notes-section">
                <div class="section-header">
                    <h5 class="notes-title mb-0">{{ translate('Inspector Notes') }}</h5>
                </div>
                <div class="section-content">
                    <p class="mb-0">{{ $carInspection->inspector_notes }}</p>
                </div>
            </div>
            @endif
             @if($carInspection->recommendations)
            <div class="notes-section">
                <div class="section-header">
                    <h5 class="notes-title">{{ translate('Recommendations') }}</h5>
                </div>
                <div class="section-content">
                    <p class="mb-0">{{ $carInspection->recommendations }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
