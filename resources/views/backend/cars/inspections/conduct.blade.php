@extends('backend.layouts.app')

@section('content')
<style>
.field-container.border {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { border-color: #dc3545; }
    50% { border-color: #fd7e7e; }
    100% { border-color: #dc3545; }
}

.timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.sticky-top {
    position: sticky !important;
}

.save-status i {
    font-size: 12px;
}

.section-nav-item {
    cursor: pointer;
    padding: 5px 0;
    border-bottom: 1px solid #f0f0f0;
}

.section-nav-item:last-child {
    border-bottom: none;
}

.section-nav-item:hover {
    background-color: #f8f9fa;
}

.new-files .d-flex {
    background-color: #e3f2fd !important;
}

.existing-files .d-flex {
    background-color: #f5f5f5;
}

.card-body .table td {
    padding: 0.25rem 0;
    border: none;
}

.aiz-checkbox-inline .aiz-checkbox,
.aiz-radio-inline .aiz-radio {
    margin-right: 15px;
    margin-bottom: 10px;
}

.progress {
    background-color: #e9ecef;
}

.badge-sm {
    font-size: 0.75em;
    padding: 0.25em 0.4em;
}
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h3">{{ translate('Conduct Inspection') }}</h1>
            <div class="d-flex align-items-center">
                <span class="text-muted mr-3">{{ $inspection->inspection_number }}</span>
                @php
                    $status_colors = [
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'secondary',
                        'failed' => 'danger'
                    ];
                    $color = $status_colors[$inspection->status] ?? 'secondary';
                @endphp
                <span class="badge badge-{{ $color }}">
                    {{ translate(ucfirst(str_replace('_', ' ', $inspection->status))) }}
                </span>
            </div>
        </div>
        <div class="col-md-4 text-right">
            @if($inspection->status == 'in_progress')
                <button type="button" class="btn btn-success" id="complete-inspection">
                    <i class="las la-check mr-1"></i>{{ translate('Complete Inspection') }}
                </button>
            @endif
            <a href="{{ route('admin.car-inspections.show', $inspection->id) }}" class="btn btn-light">
                <i class="las la-eye mr-1"></i>{{ translate('View Details') }}
            </a>
        </div>
    </div>
</div>

<!-- Progress Bar -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="text-muted">{{ translate('Overall Progress') }}</small>
            <small class="text-primary" id="progress-text">{{ $overall_progress }}% {{ translate('Complete') }}</small>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" id="progress-bar" style="width: {{ $overall_progress }}%"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Inspection Form -->
        <form id="inspection-form">
            @csrf
            <input type="hidden" name="inspection_id" value="{{ $inspection->id }}">

            @foreach($inspection->inspectionType->sections as $section)
                <div class="card mb-3" data-section-id="{{ $section->id }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">{{ $section->name }}</h6>
                            @if($section->description)
                                <small class="text-muted">{{ $section->description }}</small>
                            @endif
                        </div>
                        <div class="d-flex align-items-center">
                            @php
                                $section_completion = $section_completions[$section->id] ?? ['completion_percentage' => 0, 'is_complete' => false];
                            @endphp
                            <small class="mr-2 section-progress-text">{{ $section_completion['completion_percentage'] }}%</small>
                            <div class="progress section-progress-bar" style="width: 80px; height: 6px;">
                                <div class="progress-bar {{ $section_completion['is_complete'] ? 'bg-success' : 'bg-info' }}"
                                     style="width: {{ $section_completion['completion_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($section->fields as $field)
                                @php
                                    $field_value = $inspection->fieldValues->where('field_id', $field->id)->first();
                                @endphp
                                <div class="col-lg-6 mb-4" data-field-id="{{ $field->id }}">
                                    <div class="field-container {{ $field_value && $field_value->is_flagged ? 'border border-danger rounded p-3' : '' }}">
                                        <div class="form-group">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span>
                                                    {{ $field->name }}
                                                    @if($field->is_required)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </span>
                                                <div>
                                                    @if($field_value)
                                                        <span class="badge badge-success badge-sm">
                                                            <i class="las la-check"></i>
                                                        </span>
                                                    @endif
                                                    <button type="button" class="btn btn-sm btn-outline-secondary flag-field"
                                                            data-field-id="{{ $field->id }}"
                                                            title="{{ translate('Flag for attention') }}"
                                                            {{ $field_value && $field_value->is_flagged ? 'style=background-color:#dc3545;color:white;' : '' }}>
                                                        <i class="las la-flag"></i>
                                                    </button>
                                                </div>
                                            </label>

                                            @if($field->help_text)
                                                <small class="form-text text-muted d-block mb-2">{{ $field->help_text }}</small>
                                            @endif

                                            <!-- Field Input Based on Type -->
                                            @if($field->field_type == 'text')
                                                <input type="text"
                                                       class="form-control field-input"
                                                       name="field_{{ $field->id }}_value"
                                                       data-field-id="{{ $field->id }}"
                                                       value="{{ $field_value->value ?? '' }}"
                                                       placeholder="{{ $field->placeholder ?? '' }}"
                                                       {{ $field->is_required ? 'required' : '' }}>

                                            @elseif($field->field_type == 'textarea')
                                                <textarea class="form-control field-input"
                                                          name="field_{{ $field->id }}_value"
                                                          data-field-id="{{ $field->id }}"
                                                          rows="3"
                                                          placeholder="{{ $field->placeholder ?? '' }}"
                                                          {{ $field->is_required ? 'required' : '' }}>{{ $field_value->value ?? '' }}</textarea>

                                            @elseif($field->field_type == 'number')
                                                <input type="number"
                                                       class="form-control field-input"
                                                       name="field_{{ $field->id }}_value"
                                                       data-field-id="{{ $field->id }}"
                                                       value="{{ $field_value->value ?? '' }}"
                                                       placeholder="{{ $field->placeholder ?? '' }}"
                                                       {{ $field->is_required ? 'required' : '' }}>

                                            @elseif($field->field_type == 'email')
                                                <input type="email"
                                                       class="form-control field-input"
                                                       name="field_{{ $field->id }}_value"
                                                       data-field-id="{{ $field->id }}"
                                                       value="{{ $field_value->value ?? '' }}"
                                                       placeholder="{{ $field->placeholder ?? '' }}"
                                                       {{ $field->is_required ? 'required' : '' }}>

                                            @elseif($field->field_type == 'url')
                                                <input type="url"
                                                       class="form-control field-input"
                                                       name="field_{{ $field->id }}_value"
                                                       data-field-id="{{ $field->id }}"
                                                       value="{{ $field_value->value ?? '' }}"
                                                       placeholder="{{ $field->placeholder ?? '' }}"
                                                       {{ $field->is_required ? 'required' : '' }}>

                                            @elseif($field->field_type == 'date')
                                                <input type="date"
                                                       class="form-control field-input"
                                                       name="field_{{ $field->id }}_value"
                                                       data-field-id="{{ $field->id }}"
                                                       value="{{ $field_value->value ?? '' }}"
                                                       {{ $field->is_required ? 'required' : '' }}>

                                            @elseif($field->field_type == 'boolean')
                                                <div class="aiz-radio-inline">
                                                    <label class="aiz-radio">
                                                        <input type="radio"
                                                               name="field_{{ $field->id }}_value"
                                                               value="1"
                                                               data-field-id="{{ $field->id }}"
                                                               class="field-input"
                                                               {{ ($field_value && $field_value->value == '1') ? 'checked' : '' }}
                                                               {{ $field->is_required ? 'required' : '' }}>
                                                        <span class="aiz-rounded-check"></span>
                                                        <span class="aiz-radio-text">{{ translate('Yes') }}</span>
                                                    </label>
                                                    <label class="aiz-radio">
                                                        <input type="radio"
                                                               name="field_{{ $field->id }}_value"
                                                               value="0"
                                                               data-field-id="{{ $field->id }}"
                                                               class="field-input"
                                                               {{ ($field_value && $field_value->value == '0') ? 'checked' : '' }}
                                                               {{ $field->is_required ? 'required' : '' }}>
                                                        <span class="aiz-rounded-check"></span>
                                                        <span class="aiz-radio-text">{{ translate('No') }}</span>
                                                    </label>
                                                </div>

                                            @elseif($field->field_type == 'select')
                                                @php $options = $field->field_options ?? []; @endphp
                                                <select class="form-control aiz-selectpicker field-input"
                                                        name="field_{{ $field->id }}_value"
                                                        data-field-id="{{ $field->id }}"
                                                        {{ $field->is_required ? 'required' : '' }}>
                                                    <option value="">{{ translate('Select Option') }}</option>
                                                    @foreach($options as $option)
                                                        <option value="{{ $option }}"
                                                                {{ ($field_value && $field_value->value == $option) ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            @elseif($field->field_type == 'radio')
                                                @php $options = $field->field_options ?? []; @endphp
                                                <div class="aiz-radio-inline">
                                                    @foreach($options as $option)
                                                        <label class="aiz-radio">
                                                            <input type="radio"
                                                                   name="field_{{ $field->id }}_value"
                                                                   value="{{ $option }}"
                                                                   data-field-id="{{ $field->id }}"
                                                                   class="field-input"
                                                                   {{ ($field_value && $field_value->value == $option) ? 'checked' : '' }}
                                                                   {{ $field->is_required ? 'required' : '' }}>
                                                            <span class="aiz-rounded-check"></span>
                                                            <span class="aiz-radio-text">{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>

                                            @elseif($field->field_type == 'checkbox')
                                                @php
                                                    $options = $field->field_options ?? [];
                                                    $selected_values = $field_value ? json_decode($field_value->value, true) : [];
                                                @endphp
                                                <div class="aiz-checkbox-inline">
                                                    @foreach($options as $option)
                                                        <label class="aiz-checkbox">
                                                            <input type="checkbox"
                                                                   name="field_{{ $field->id }}_value[]"
                                                                   value="{{ $option }}"
                                                                   data-field-id="{{ $field->id }}"
                                                                   class="field-input checkbox-input"
                                                                   {{ (is_array($selected_values) && in_array($option, $selected_values)) ? 'checked' : '' }}>
                                                            <span class="aiz-square-check"></span>
                                                            <span class="aiz-checkbox-text">{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <!-- Score Field (if scoring is enabled) -->
                                            @if($field->enable_scoring ?? false)
                                                <div class="mt-2">
                                                    <label class="form-label small">{{ translate('Score (0-100)') }}</label>
                                                    <input type="number"
                                                           class="form-control form-control-sm score-input"
                                                           name="field_{{ $field->id }}_score"
                                                           data-field-id="{{ $field->id }}"
                                                           value="{{ $field_value->score ?? '' }}"
                                                           min="0" max="100"
                                                           placeholder="0-100">
                                                </div>
                                            @endif

                                            <!-- Notes Field -->
                                            <div class="mt-2">
                                                <label class="form-label small">{{ translate('Notes (Optional)') }}</label>
                                                <textarea class="form-control form-control-sm notes-input"
                                                          name="field_{{ $field->id }}_notes"
                                                          data-field-id="{{ $field->id }}"
                                                          rows="2"
                                                          placeholder="{{ translate('Add any notes or observations...') }}">{{ $field_value->notes ?? '' }}</textarea>
                                            </div>

                                            <!-- File Upload Field -->
                                            <div class="mt-2">
                                                <label class="form-label small">{{ translate('Attachments (Optional)') }}</label>
                                                <div class="input-group">
                                                    <input type="file"
                                                           class="form-control form-control-sm file-input"
                                                           name="field_{{ $field->id }}_files[]"
                                                           data-field-id="{{ $field->id }}"
                                                           multiple
                                                           accept="image/*,.pdf,.doc,.docx">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-sm btn-outline-primary upload-files" data-field-id="{{ $field->id }}">
                                                            <i class="las la-upload"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Existing Files Display -->
                                                @if($field_value && $field_value->file_attachments)
                                                    @php $attachments = json_decode($field_value->file_attachments, true); @endphp
                                                    @if($attachments && count($attachments) > 0)
                                                        <div class="mt-2 existing-files">
                                                            @foreach($attachments as $index => $attachment)
                                                                <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded mb-1">
                                                                    <span class="small">
                                                                        <i class="las la-file mr-1"></i>{{ $attachment['original_name'] }}
                                                                    </span>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-file"
                                                                            data-field-id="{{ $field->id }}"
                                                                            data-file-index="{{ $index }}">
                                                                        <i class="las la-trash"></i>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif

                                                <!-- New Files Display -->
                                                <div class="mt-2 new-files" data-field-id="{{ $field->id }}" style="display: none;"></div>
                                            </div>

                                            <!-- Auto-save indicator -->
                                            <div class="mt-1">
                                                <small class="text-muted save-status" data-field-id="{{ $field->id }}">
                                                    @if($field_value)
                                                        <i class="las la-check text-success"></i> {{ translate('Saved') }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </form>
    </div>

    <div class="col-lg-4">
        <!-- Car Information -->
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Car Information') }}</h6>
            </div>
            <div class="card-body">
                @if($inspection->car)
                    <div class="text-center mb-3">
                        @if($inspection->car->thumbnail)
                            <img src="{{ uploaded_asset($inspection->car->thumbnail->file_name) }}" alt="Car" class="img-fluid rounded" style="max-height: 120px;">
                        @else
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" alt="Car" class="img-fluid rounded" style="max-height: 120px;">
                        @endif
                    </div>

                    <h6 class="text-center mb-3">{{ $inspection->car->name }}</h6>

                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>{{ translate('Brand') }}:</strong></td>
                            <td>{{ $inspection->car->brand->name ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Model') }}:</strong></td>
                            <td>{{ $inspection->car->model->name ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Year') }}:</strong></td>
                            <td>{{ $inspection->car->year ?? translate('N/A') }}</td>
                        </tr>
                    </table>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Quick Actions') }}</h6>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-outline-primary btn-sm btn-block mb-2" id="save-all">
                    <i class="las la-save mr-1"></i>{{ translate('Save All Changes') }}
                </button>

                {{-- <button type="button" class="btn btn-outline-secondary btn-sm btn-block mb-2" data-toggle="modal" data-target="#pauseModal">
                    <i class="las la-pause mr-1"></i>{{ translate('Pause Inspection') }}
                </button> --}}

                <button type="button" class="btn btn-outline-info btn-sm btn-block" id="preview-report">
                    <i class="las la-eye mr-1"></i>{{ translate('Preview Report') }}
                </button>
            </div>
        </div>

        <!-- Section Navigation -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Section Navigation') }}</h6>
            </div>
            <div class="card-body">
                @foreach($inspection->inspectionType->sections as $section)
                    @php
                        $section_completion = $section_completions[$section->id] ?? ['completion_percentage' => 0, 'is_complete' => false];
                    @endphp
                    <div class="d-flex align-items-center justify-content-between mb-2 section-nav-item" data-section-id="{{ $section->id }}">
                        <a href="#section-{{ $section->id }}" class="text-decoration-none">
                            <small>{{ $section->name }}</small>
                        </a>
                        <div class="d-flex align-items-center">
                            <small class="mr-2">{{ $section_completion['completion_percentage'] }}%</small>
                            @if($section_completion['is_complete'])
                                <i class="las la-check-circle text-success"></i>
                            @else
                                <i class="las la-clock text-warning"></i>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Complete Inspection Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ translate('Complete Inspection') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="complete-form" action="{{ route('admin.car-inspections.complete', $inspection->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('Overall Score') }} (0-100)</label>
                        <input type="number" name="total_score" class="form-control" min="0" max="100" required>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('Overall Condition') }}</label>
                        <select name="overall_condition" class="form-control aiz-selectpicker" required>
                            <option value="excellent">{{ translate('Excellent') }}</option>
                            <option value="good">{{ translate('Good') }}</option>
                            <option value="fair">{{ translate('Fair') }}</option>
                            <option value="poor">{{ translate('Poor') }}</option>
                            <option value="critical">{{ translate('Critical') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('Final Notes') }}</label>
                        <textarea name="inspector_notes" class="form-control" rows="3" placeholder="{{ translate('Summary of inspection findings...') }}"></textarea>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('Recommendations') }}</label>
                        <textarea name="recommendations" class="form-control" rows="3" placeholder="{{ translate('Recommended actions or repairs...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ translate('Complete Inspection') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pause Inspection Modal -->
<div class="modal fade" id="pauseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ translate('Pause Inspection') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>{{ translate('Are you sure you want to pause this inspection? Your progress will be saved.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-warning" id="pause-inspection">{{ translate('Yes, Pause') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
$(document).ready(function() {
    let saveTimeout;
    let unsavedChanges = false;

    // Auto-save functionality
    function autoSave(fieldId, type = 'value') {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function() {
            saveFieldValue(fieldId, type);
        }, 1000);
    }

    // Save field value via AJAX
    function saveFieldValue(fieldId, type = 'value') {
        const fieldData = {};
        fieldData.inspection_id = {{ $inspection->id }};
        fieldData.field_id = fieldId;

        // Get field value based on type
        const fieldInput = $(`[data-field-id="${fieldId}"].field-input`);

        if (fieldInput.is(':checkbox')) {
            const checkedValues = [];
            $(`[data-field-id="${fieldId}"].field-input:checked`).each(function() {
                checkedValues.push($(this).val());
            });
            fieldData.value = JSON.stringify(checkedValues);
        } else if (fieldInput.is(':radio')) {
            fieldData.value = $(`[data-field-id="${fieldId}"].field-input:checked`).val() || '';
        } else {
            fieldData.value = fieldInput.val() || '';
        }

        // Get score and notes
        fieldData.score = $(`.score-input[data-field-id="${fieldId}"]`).val() || null;
        fieldData.notes = $(`.notes-input[data-field-id="${fieldId}"]`).val() || '';

        // Show saving indicator
        $(`.save-status[data-field-id="${fieldId}"]`).html('<i class="las la-spinner la-spin text-info"></i> {{ translate("Saving...") }}');

        // AJAX save
        $.ajax({
            url: '/admin/car-inspection-field-values',
            method: 'POST',
            data: {
                ...fieldData,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $(`.save-status[data-field-id="${fieldId}"]`).html('<i class="las la-check text-success"></i> {{ translate("Saved") }}');
                    unsavedChanges = false;
                    updateProgress();

                    // Add success badge
                    const badge = $(`[data-field-id="${fieldId}"] .badge.badge-success`);
                    if (badge.length === 0) {
                        $(`[data-field-id="${fieldId}"] .form-label span:first`).after(' <span class="badge badge-success badge-sm"><i class="las la-check"></i></span>');
                    }
                } else {
                    $(`.save-status[data-field-id="${fieldId}"]`).html('<i class="las la-times text-danger"></i> {{ translate("Save failed") }}');
                }
            },
            error: function() {
                $(`.save-status[data-field-id="${fieldId}"]`).html('<i class="las la-times text-danger"></i> {{ translate("Save failed") }}');
            }
        });
    }

    // Update progress bars
    function updateProgress() {
        $.get(`/admin/car-inspections/{{ $inspection->id }}/progress`, function(data) {
            if (data.success) {
                // Update overall progress
                $('#progress-bar').css('width', data.overall_progress + '%');
                $('#progress-text').text(data.overall_progress + '% {{ translate("Complete") }}');

                // Update section progress
                Object.keys(data.section_progress).forEach(sectionId => {
                    const section = data.section_progress[sectionId];
                    const sectionCard = $(`[data-section-id="${sectionId}"]`);
                    sectionCard.find('.section-progress-text').text(section.completion_percentage + '%');
                    sectionCard.find('.section-progress-bar .progress-bar')
                        .css('width', section.completion_percentage + '%')
                        .removeClass('bg-info bg-success')
                        .addClass(section.is_complete ? 'bg-success' : 'bg-info');

                    // Update navigation
                    const navItem = $(`.section-nav-item[data-section-id="${sectionId}"]`);
                    navItem.find('small:last').text(section.completion_percentage + '%');
                    navItem.find('i').removeClass('las la-check-circle text-success las la-clock text-warning')
                        .addClass(section.is_complete ? 'las la-check-circle text-success' : 'las la-clock text-warning');
                });
            }
        });
    }

    // Field input change events
    $(document).on('input change', '.field-input', function() {
        const fieldId = $(this).data('field-id');
        unsavedChanges = true;
        autoSave(fieldId);
    });

    // Score input change
    $(document).on('input change', '.score-input', function() {
        const fieldId = $(this).data('field-id');
        unsavedChanges = true;
        autoSave(fieldId);
    });

    // Notes input change
    $(document).on('input change', '.notes-input', function() {
        const fieldId = $(this).data('field-id');
        unsavedChanges = true;
        autoSave(fieldId);
    });

    // Checkbox special handling
    $(document).on('change', '.checkbox-input', function() {
        const fieldId = $(this).data('field-id');
        unsavedChanges = true;
        autoSave(fieldId);
    });

    // Flag field functionality
    $(document).on('click', '.flag-field', function() {
        const fieldId = $(this).data('field-id');
        const button = $(this);
        const isFlagged = button.css('background-color') === 'rgb(220, 53, 69)';

        $.ajax({
            url: `/admin/car-inspection-field-values/flag/${fieldId}`,
            method: 'POST',
            data: {
                inspection_id: {{ $inspection->id }},
                is_flagged: !isFlagged,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    if (!isFlagged) {
                        button.css({'background-color': '#dc3545', 'color': 'white'});
                        button.closest('.field-container').addClass('border border-danger rounded p-3');
                    } else {
                        button.css({'background-color': '', 'color': ''});
                        button.closest('.field-container').removeClass('border border-danger rounded p-3');
                    }
                }
            }
        });
    });

    // File upload functionality
    $(document).on('change', '.file-input', function() {
        const fieldId = $(this).data('field-id');
        const files = this.files;
        const newFilesContainer = $(`.new-files[data-field-id="${fieldId}"]`);

        if (files.length > 0) {
            newFilesContainer.empty().show();
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                newFilesContainer.append(`
                    <div class="d-flex align-items-center justify-content-between p-2 bg-soft-info rounded mb-1">
                        <span class="small">
                            <i class="las la-file mr-1"></i>${file.name}
                        </span>
                        <small class="text-muted">(${(file.size / 1024).toFixed(1)} KB)</small>
                    </div>
                `);
            }
        }
    });

    // Upload files
    $(document).on('click', '.upload-files', function() {
        const fieldId = $(this).data('field-id');
        const fileInput = $(`.file-input[data-field-id="${fieldId}"]`);
        const files = fileInput[0].files;

        if (files.length === 0) {
            AIZ.plugins.notify('warning', '{{ translate("Please select files to upload") }}');
            return;
        }

        const formData = new FormData();
        formData.append('inspection_id', {{ $inspection->id }});
        formData.append('field_id', fieldId);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        $(this).prop('disabled', true).html('<i class="las la-spinner la-spin"></i>');

        $.ajax({
            url: '/admin/car-inspection-field-values/upload',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    AIZ.plugins.notify('success', '{{ translate("Files uploaded successfully") }}');
                    // Refresh the field to show new attachments
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', response.message || '{{ translate("Upload failed") }}');
                }
            },
            error: function() {
                AIZ.plugins.notify('danger', '{{ translate("Upload failed") }}');
            },
            complete: function() {
                $(`.upload-files[data-field-id="${fieldId}"]`).prop('disabled', false).html('<i class="las la-upload"></i>');
            }
        });
    });

    // Delete file
    $(document).on('click', '.delete-file', function() {
        const fieldId = $(this).data('field-id');
        const fileIndex = $(this).data('file-index');
        const button = $(this);

        if (confirm('{{ translate("Are you sure you want to delete this file?") }}')) {
            $.ajax({
                url: '/admin/car-inspection-field-values/delete-file',
                method: 'POST',
                data: {
                    inspection_id: {{ $inspection->id }},
                    field_id: fieldId,
                    file_index: fileIndex,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        button.closest('.d-flex').remove();
                        AIZ.plugins.notify('success', '{{ translate("File deleted successfully") }}');
                    }
                }
            });
        }
    });

    // Complete inspection
    $('#complete-inspection').on('click', function() {
        // Check if all required fields are filled
        let incompleteFields = 0;
        $('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val() || ($(this).is(':radio') && !$(this).is(':checked'))) {
                incompleteFields++;
            }
        });

        if (incompleteFields > 0) {
            if (!confirm(`{{ translate("There are still") }} ${incompleteFields} {{ translate("incomplete required fields. Do you want to proceed anyway?") }}`)) {
                return;
            }
        }

        $('#completeModal').modal('show');
    });



    // Save all changes
    $('#save-all').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).html('<i class="las la-spinner la-spin mr-1"></i>{{ translate("Saving...") }}');

        // Save all field values
        $('.field-input').each(function() {
            const fieldId = $(this).data('field-id');
            if (fieldId) {
                saveFieldValue(fieldId);
            }
        });

        setTimeout(function() {
            button.prop('disabled', false).html('<i class="las la-save mr-1"></i>{{ translate("Save All Changes") }}');
            AIZ.plugins.notify('success', '{{ translate("All changes saved") }}');
        }, 2000);
    });

    // Preview report
    $('#preview-report').on('click', function() {
        window.open('{{ route("admin.car-inspections.report", $inspection->id) }}', '_blank');
    });

    // Section navigation
    $('.section-nav-item a').on('click', function(e) {
        e.preventDefault();
        const target = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(target).offset().top - 100
        }, 500);
    });

    // Smooth scrolling for section anchors
    @foreach($inspection->inspectionType->sections as $section)
        $('[data-section-id="{{ $section->id }}"]').attr('id', 'section-{{ $section->id }}');
    @endforeach

    // Warn about unsaved changes
    $(window).on('beforeunload', function() {
        if (unsavedChanges) {
            return '{{ translate("You have unsaved changes. Are you sure you want to leave?") }}';
        }
    });

    // Auto-save every 5 minutes
    setInterval(function() {
        if (unsavedChanges) {
            $('#save-all').trigger('click');
        }
    }, 300000);

    // Initial progress update
    updateProgress();
});
</script>
@endsection
