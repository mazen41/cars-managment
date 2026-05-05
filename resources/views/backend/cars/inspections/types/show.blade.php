@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Inspection Type Details') }}</h1>
            <p class="text-muted mb-0">{{ $carInspectionType->name }}</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.car-inspection-types.edit', $carInspectionType->id) }}" class="btn btn-success">
                <i class="las la-edit mr-1"></i>{{ translate('Edit Type') }}
            </a>
            <a href="{{ route('admin.car-inspection-types.index') }}" class="btn btn-light">
                <i class="las la-arrow-left mr-1"></i>{{ translate('Back to Types') }}
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Information Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Type Overview') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50"><strong>{{ translate('Name') }}:</strong></td>
                                <td>{{ $carInspectionType->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Slug') }}:</strong></td>
                                <td><span class="badge badge-inline badge-soft-secondary">{{ $carInspectionType->slug }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Status') }}:</strong></td>
                                <td>
                                    <span class="badge badge-inline badge-{{ $carInspectionType->is_active ? 'success' : 'secondary' }}">
                                        {{ $carInspectionType->is_active ? translate('Active') : translate('Inactive') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Sort Order') }}:</strong></td>
                                <td>{{ $carInspectionType->sort_order ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Price') }}:</strong></td>
                                <td><span class="text-success fw-600">{{ $carInspectionType->formatted_price }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50"><strong>{{ translate('Total Sections') }}:</strong></td>
                                <td>{{ $carInspectionType->sections->count() }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Total Fields') }}:</strong></td>
                                <td>{{ $carInspectionType->sections->sum(function($section) { return $section->fields->count(); }) }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Created') }}:</strong></td>
                                <td>{{ $carInspectionType->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Last Updated') }}:</strong></td>
                                <td>{{ $carInspectionType->updated_at->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($carInspectionType->description)
                    <div class="mt-3">
                        <strong>{{ translate('Description') }}:</strong>
                        <p class="mt-2 p-3 bg-light rounded">{{ $carInspectionType->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sections Management -->
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ translate('Inspection Sections') }} ({{ $carInspectionType->sections->count() }})</h6>
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addSectionModal">
                    <i class="las la-plus mr-1"></i>{{ translate('Add Section') }}
                </button>
            </div>
            <div class="card-body">
                @if($carInspectionType->sections->count() > 0)
                    <div id="sections-container">
                        @foreach($carInspectionType->sections->sortBy('sort_order') as $index => $section)
                            <div class="section-item border rounded mb-3" data-section-id="{{ $section->id }}">
                                <div class="section-header bg-light p-3 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="section-handle mr-3" style="cursor: move;">
                                            <i class="las la-grip-vertical text-muted"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $section->name }}</h6>
                                            <small class="text-muted">{{ $section->fields->count() }} {{ translate('fields') }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-inline badge-{{ $section->is_active ? 'success' : 'secondary' }} mr-2">
                                            {{ $section->is_active ? translate('Active') : translate('Inactive') }}
                                        </span>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-soft-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                <i class="las la-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item edit-section-btn" href="#" data-section-id="{{ $section->id }}">
                                                    <i class="las la-edit mr-2"></i>{{ translate('Edit') }}
                                                </a>
                                                <a class="dropdown-item toggle-section-btn" href="#" data-section-id="{{ $section->id }}">
                                                    <i class="las la-{{ $section->is_active ? 'eye-slash' : 'eye' }} mr-2"></i>
                                                    {{ $section->is_active ? translate('Deactivate') : translate('Activate') }}
                                                </a>
                                                <a class="dropdown-item duplicate-section-btn" href="#" data-section-id="{{ $section->id }}">
                                                    <i class="las la-copy mr-2"></i>{{ translate('Duplicate') }}
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger delete-section-btn" href="#" data-section-id="{{ $section->id }}">
                                                    <i class="las la-trash mr-2"></i>{{ translate('Delete') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($section->description)
                                    <div class="px-3 pt-2">
                                        <small class="text-muted" id="section_description_{{ $section->id }}">{{ $section->description }}</small>
                                    </div>
                                @endif

                                <!-- Fields within this section -->
                                <div class="section-fields p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">{{ translate('Fields') }} ({{ $section->fields->count() }})</h6>
                                        <button class="btn btn-sm btn-outline-primary add-field-btn" data-section-id="{{ $section->id }}">
                                            <i class="las la-plus mr-1"></i>{{ translate('Add Field') }}
                                        </button>
                                    </div>

                                    @if($section->fields->count() > 0)
                                        <div class="fields-container" data-section-id="{{ $section->id }}">
                                            @foreach($section->fields->sortBy('sort_order') as $field)
                                                <div class="field-item border-left border-primary pl-3 mb-2" data-field-id="{{ $field->id }}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <div class="field-handle mr-2" style="cursor: move;">
                                                                <i class="las la-grip-lines text-muted"></i>
                                                            </div>
                                                            <div>
                                                                <strong>{{ $field->name }}</strong>
                                                                <span class="badge badge-inline badge-soft-info ml-2">{{ $field->field_type_display }}</span>
                                                                @if($field->is_required)
                                                                    <span class="badge badge-inline badge-soft-danger ml-1">{{ translate('Required') }}</span>
                                                                @endif
                                                                @if(!$field->is_active)
                                                                    <span class="badge badge-inline badge-secondary ml-1">{{ translate('Inactive') }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-link text-muted dropdown-toggle" type="button" data-toggle="dropdown">
                                                                <i class="las la-ellipsis-v"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item edit-field-btn" href="#" data-field-id="{{ $field->id }}" data-section-id="{{ $section->id }}">
                                                                    <i class="las la-edit mr-2"></i>{{ translate('Edit') }}
                                                                </a>
                                                                <a class="dropdown-item toggle-field-btn" href="#" data-field-id="{{ $field->id }}" data-section-id="{{ $section->id }}">
                                                                    <i class="las la-{{ $field->is_active ? 'eye-slash' : 'eye' }} mr-2"></i>
                                                                    {{ $field->is_active ? translate('Deactivate') : translate('Activate') }}
                                                                </a>
                                                                <a class="dropdown-item duplicate-field-btn" href="#" data-field-id="{{ $field->id }}" data-section-id="{{ $section->id }}">
                                                                    <i class="las la-copy mr-2"></i>{{ translate('Duplicate') }}
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger delete-field-btn" href="#" data-field-id="{{ $field->id }}" data-section-id="{{ $section->id }}">
                                                                    <i class="las la-trash mr-2"></i>{{ translate('Delete') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($field->description)
                                                        <div class="mt-1">
                                                            <small class="text-muted">{{ $field->description }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <i class="las la-layer-group la-3x text-muted mb-2"></i>
                                            <p class="text-muted">{{ translate('No fields in this section yet') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="las la-list-ul la-4x text-muted mb-3"></i>
                        <h6 class="text-muted">{{ translate('No sections created yet') }}</h6>
                        <p class="text-muted">{{ translate('Create your first section to start building your inspection form') }}</p>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addSectionModal">
                            <i class="las la-plus mr-1"></i>{{ translate('Add First Section') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Statistics Card -->
        @if(isset($statistics))
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Statistics') }}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-soft-primary rounded">
                            <h4 class="mb-1 text-primary">{{ $statistics['total_sections'] ?? 0 }}</h4>
                            <small class="text-muted">{{ translate('Sections') }}</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-soft-info rounded">
                            <h4 class="mb-1 text-info">{{ $statistics['total_fields'] ?? 0 }}</h4>
                            <small class="text-muted">{{ translate('Fields') }}</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-soft-success rounded">
                            <h4 class="mb-1 text-success">{{ $statistics['total_inspections'] ?? 0 }}</h4>
                            <small class="text-muted">{{ translate('Inspections') }}</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-soft-warning rounded">
                            <h4 class="mb-1 text-warning">{{ $statistics['completion_rate'] ?? 0 }}%</h4>
                            <small class="text-muted">{{ translate('Completion Rate') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Inspections -->
        @if($carInspectionType->inspections->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Recent Inspections') }}</h6>
            </div>
            <div class="card-body">
                @foreach($carInspectionType->inspections->take(5) as $inspection)
                    <div class="d-flex justify-content-between align-items-center {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                        <div>
                            <h6 class="mb-1">{{ $inspection->car->car_name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ $inspection->created_at->format('M d, Y') }}</small>
                        </div>
                        <span class="badge badge-inline badge-{{ $inspection->status === 'completed' ? 'success' : ($inspection->status === 'in_progress' ? 'warning' : 'secondary') }}">
                            {{ translate(ucfirst($inspection->status)) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('Add New Section') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSectionForm">
                <div class="modal-body">
                    @csrf
                    <div class="form-group">
                        <label for="section_name">{{ translate('Section Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="section_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="section_description">{{ translate('Description') }}</label>
                        <textarea class="form-control" id="section_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="section_is_active" name="is_active" checked>
                            <label class="form-check-label" for="section_is_active">
                                {{ translate('Active') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Add Section') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('Edit Section') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editSectionForm">
                <div class="modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_section_id">
                    <div class="form-group">
                        <label for="edit_section_name">{{ translate('Section Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_section_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_section_description">{{ translate('Description') }}</label>
                        <textarea class="form-control" id="edit_section_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_section_is_active" name="is_active">
                            <label class="form-check-label" for="edit_section_is_active">
                                {{ translate('Active') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Update Section') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Field Modal -->
<div class="modal fade" id="addFieldModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('Add New Field') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addFieldForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="field_section_id" name="section_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field_name">{{ translate('Field Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="field_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field_type">{{ translate('Field Type') }} <span class="text-danger">*</span></label>
                                <select class="form-control" id="field_type" name="field_type" required>
                                    <option value="">{{ translate('Select Field Type') }}</option>
                                    @foreach(App\Models\CarInspectionField::FIELD_TYPES as $type => $label)
                                        <option value="{{ $type }}">{{ translate($label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group d-none" id="field_options_group">
                        <label for="field_options">{{ translate('Options') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="field_options" name="field_options" rows="2" placeholder="{{ translate('Enter one option per line') }}"></textarea>
                        <small class="form-text text-muted">{{ translate('Required for select, checkbox, and radio types.') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="field_description">{{ translate('Description') }}</label>
                        <textarea class="form-control" id="field_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="field_placeholder">{{ translate('Placeholder') }}</label>
                        <input type="text" class="form-control" id="field_placeholder" name="placeholder">
                    </div>
                    <div class="form-group">
                        <label for="field_help_text">{{ translate('Help Text') }}</label>
                        <input type="text" class="form-control" id="field_help_text" name="help_text">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="field_is_required" name="is_required">
                                    <label class="form-check-label" for="field_is_required">
                                        {{ translate('Required Field') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="field_is_active" name="is_active" checked>
                                    <label class="form-check-label" for="field_is_active">
                                        {{ translate('Active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Add Field') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Field Modal -->
<div class="modal fade" id="editFieldModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('Edit Field') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editFieldForm">
                <div class="modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_field_id">
                    <input type="hidden" id="edit_field_section_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_field_name">{{ translate('Field Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_field_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_field_type">{{ translate('Field Type') }} <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_field_type" name="field_type" required>
                                    @foreach(App\Models\CarInspectionField::FIELD_TYPES as $type => $label)
                                        <option value="{{ $type }}">{{ translate($label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group d-none" id="edit_field_options_group">
                        <label for="edit_field_options">{{ translate('Options') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_field_options" name="field_options" rows="2" placeholder="{{ translate('Enter one option per line') }}"></textarea>
                        <small class="form-text text-muted">{{ translate('Required for select, checkbox, and radio types.') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_field_description">{{ translate('Description') }}</label>
                        <textarea class="form-control" id="edit_field_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_field_placeholder">{{ translate('Placeholder') }}</label>
                        <input type="text" class="form-control" id="edit_field_placeholder" name="placeholder">
                    </div>
                    <div class="form-group">
                        <label for="edit_field_help_text">{{ translate('Help Text') }}</label>
                        <input type="text" class="form-control" id="edit_field_help_text" name="help_text">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_field_is_required" name="is_required">
                                    <label class="form-check-label" for="edit_field_is_required">
                                        {{ translate('Required Field') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_field_is_active" name="is_active">
                                    <label class="form-check-label" for="edit_field_is_active">
                                        {{ translate('Active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Update Field') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    const inspectionTypeId = {{ $carInspectionType->id }};

    // Add Section Form
    $('#addSectionForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        $.ajax({
           url: `{{ route('admin.car-inspection-types.sections.store', $carInspectionType->id) }}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // Edit Section
    $(document).on('click', '.edit-section-btn', function(e) {
        e.preventDefault();
        const sectionId = $(this).data('section-id');

        // Find section data from the DOM
        const sectionElement = $(`.section-item[data-section-id="${sectionId}"]`);
        const sectionName = sectionElement.find('h6').first().text().trim();
        const isActive = sectionElement.find('.badge-success').length > 0;
        const sectionDescription = sectionElement.find('#section_description_' + sectionId).text().trim() || '';
        $('#edit_section_id').val(sectionId);
        $('#edit_section_name').val(sectionName);
        $('#edit_section_description').val(sectionDescription);
        $('#edit_section_is_active').prop('checked', isActive);

        $('#editSectionModal').modal('show');
    });

    // Update Section Form
    $('#editSectionForm').on('submit', function(e) {
        e.preventDefault();

        const sectionId = $('#edit_section_id').val();
        const formData = new FormData(this);
        const url = `{{ route('admin.car-inspection-types.sections.update', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__'
            ]) }}`
                .replace('__SECTION_ID__', sectionId);

        $.ajax({
           url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'PUT',
                'accept': 'application/json',
            },

        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // Delete Section
    $(document).on('click', '.delete-section-btn', function(e) {
        e.preventDefault();

        if (!confirm('{{ translate("Are you sure you want to delete this section? This will also delete all its fields.") }}')) {
            return;
        }

        const sectionId = $(this).data('section-id');
         const url = `{{ route('admin.car-inspection-types.sections.destroy', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__'
            ]) }}`
            .replace('__SECTION_ID__', sectionId);
        $.ajax({
           url: url,
            method: 'DELETE',
            headers: {
            'accept': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // Toggle Section Status
    $(document).on('click', '.toggle-section-btn', function(e) {
        e.preventDefault();
        const sectionId = $(this).data('section-id');
        const url =  `{{ route('admin.car-inspection-types.sections.toggle-status', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__'
            ]) }}`
                .replace('__SECTION_ID__', sectionId);

        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // Duplicate Section
    $(document).on('click', '.duplicate-section-btn', function(e) {
        e.preventDefault();
        const sectionId = $(this).data('section-id');
        const url = `{{ route('admin.car-inspection-types.sections.duplicate', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__'
            ]) }}`
                .replace('__SECTION_ID__', sectionId);

        $.ajax({
            url: url,
            method: 'POST',
             headers: {
                'accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // Add Field
    $(document).on('click', '.add-field-btn', function(e) {
        e.preventDefault();
        const sectionId = $(this).data('section-id');
        $('#field_section_id').val(sectionId);
        $('#addFieldModal').modal('show');
    });

    // Add Field Form
    $('#addFieldForm').on('submit', function(e) {
        e.preventDefault();

        const sectionId = $('#field_section_id').val();
        const formData = new FormData(this);
        const url = `{{ route('admin.car-inspection-types.sections.fields.store', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__',
            ]) }}`
                .replace('__SECTION_ID__', sectionId);

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
             headers: {
                'accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // Edit Field
    $(document).on('click', '.edit-field-btn', function(e) {
        e.preventDefault();
        const fieldId = $(this).data('field-id');
        const sectionId = $(this).data('section-id');

       $.ajax({
            url: `{{ route('admin.car-inspection-types.sections.fields.edit', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__',
                'carInspectionField' => '__FIELD_ID__'
            ]) }}`
                .replace('__SECTION_ID__', sectionId)
                .replace('__FIELD_ID__', fieldId),
            method: 'GET',
            headers: {
                'accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function (response) {
            if (response.success) {
                populateEditFieldModal(response.data);
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        }
        });
    });

    // Show/hide options textarea based on field type for Add Field
    $('#field_type').on('change', function() {
        let type = $(this).val();
        if (['select', 'checkbox', 'radio'].includes(type)) {
            $('#field_options_group').removeClass('d-none');
            $('#field_options').prop('required', true);
        } else {
            $('#field_options_group').addClass('d-none');
            $('#field_options').prop('required', false);
        }
    });

    // Show/hide options textarea based on field type for Edit Field
    $('#edit_field_type').on('change', function() {
        let type = $(this).val();
        if (['select', 'checkbox', 'radio'].includes(type)) {
            $('#edit_field_options_group').removeClass('d-none');
            $('#edit_field_options').prop('required', true);
        } else {
            $('#edit_field_options_group').addClass('d-none');
            $('#edit_field_options').prop('required', false);
        }
    });

    // Populate Edit Field Modal with options if present
    function populateEditFieldModal(field) {
        $('#edit_field_id').val(field.id);
        $('#edit_field_section_id').val(field.section_id);
        $('#edit_field_name').val(field.name);
        $('#edit_field_type').val(field.field_type).trigger('change');
        $('#edit_field_description').val(field.description || '');
        $('#edit_field_placeholder').val(field.placeholder || '');
        $('#edit_field_help_text').val(field.help_text || '');
        $('#edit_field_is_required').prop('checked', field.is_required);
        $('#edit_field_is_active').prop('checked', field.is_active);

        if (['select', 'checkbox', 'radio'].includes(field.field_type)) {
            $('#edit_field_options_group').removeClass('d-none');
            $('#edit_field_options').val((field.field_options || []).join('\n'));
        } else {
            $('#edit_field_options_group').addClass('d-none');
            $('#edit_field_options').val('');
        }
        $('#editFieldModal').modal('show');
    }

    // Update Field Form
    $('#editFieldForm').on('submit', function(e) {
        e.preventDefault();

        const fieldId = $('#edit_field_id').val();
        const sectionId = $('#edit_field_section_id').val();
        const formData = new FormData(this);
        const url = `{{ route('admin.car-inspection-types.sections.fields.update', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__',
                'carInspectionField' => '__FIELD_ID__'
            ]) }}`
                .replace('__SECTION_ID__', sectionId)
                .replace('__FIELD_ID__', fieldId);
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'PUT',
                  'accept': 'application/json',
            },
        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // Delete Field
    $(document).on('click', '.delete-field-btn', function(e) {
        e.preventDefault();

        if (!confirm('{{ translate("Are you sure you want to delete this field?") }}')) {
            return;
        }

        const fieldId = $(this).data('field-id');
        const sectionId = $(this).data('section-id');
        const url =  `{{ route('admin.car-inspection-types.sections.fields.destroy', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__',
                'carInspectionField' => '__FIELD_ID__'
            ]) }}`
                .replace('__SECTION_ID__', sectionId)
                .replace('__FIELD_ID__', fieldId);

        $.ajax({
            url: url,
            method: 'DELETE',
            headers: {
                'accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // Toggle Field Status
    $(document).on('click', '.toggle-field-btn', function(e) {
        e.preventDefault();
        const fieldId = $(this).data('field-id');
        const sectionId = $(this).data('section-id');
        const url = `{{ route('admin.car-inspection-types.sections.fields.toggle-status', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__',
                'carInspectionField' => '__FIELD_ID__'
            ]) }}`
                .replace('__SECTION_ID__', sectionId)
                .replace('__FIELD_ID__', fieldId);

        $.ajax({
            url: url,
            method: 'POST',
             headers: {
                'accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // Duplicate Field
    $(document).on('click', '.duplicate-field-btn', function(e) {
        e.preventDefault();
        const fieldId = $(this).data('field-id');
        const sectionId = $(this).data('section-id');
        const url =  `{{ route('admin.car-inspection-types.sections.fields.duplicate', [
                'carInspectionType' => $carInspectionType->id,
                'carInspectionSection' => '__SECTION_ID__',
                'carInspectionField' => '__FIELD_ID__'
            ]) }}`
                .replace('__SECTION_ID__', sectionId)
                .replace('__FIELD_ID__', fieldId);

        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function (response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                var response = xhr.responseJSON;
                AIZ.plugins.notify('warning', response.message);
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        }
        });
    });

    // --- DRAG SORTING WITH SORTABLEJS ---

    // Sortable for Sections
    if (window.Sortable) {
        new Sortable(document.getElementById('sections-container'), {
            handle: '.section-handle',
            animation: 150,
            onEnd: function (evt) {
                let order = [];
                $('#sections-container .section-item').each(function (idx, el) {
                    order.push($(el).data('section-id'));
                });
                $.ajax({
                    url: `{{ route('admin.car-inspection-types.sections.sort-sections', $carInspectionType->id) }}`,
                    method: 'POST',
                    data: {
                        order: order,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: {
                    'accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                    success: function (response) {
                        if (response.success) {
                            AIZ.plugins.notify('success', response.message);
                        } else {
                            AIZ.plugins.notify('danger', response.message);
                        }
                    },
                    error: function () {
                        AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                    }
                });
            }
        });

        // Sortable for Fields in each Section
        $('.fields-container').each(function () {
            let sectionId = $(this).data('section-id');
            new Sortable(this, {
                handle: '.field-handle',
                animation: 150,
                onEnd: function (evt) {
                    let order = [];
                    $(`.fields-container[data-section-id="${sectionId}"] .field-item`).each(function (idx, el) {
                        order.push($(el).data('field-id'));
                    });
                    $.ajax({
                        url: `{{ route('admin.car-inspection-types.sections.fields.sort-fields', [
                            'carInspectionType' => $carInspectionType->id,
                            'carInspectionSection' => '__SECTION_ID__'
                        ]) }}`.replace('__SECTION_ID__', sectionId),
                        method: 'POST',
                        data: {
                            order: order,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        headers: {
                            'accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                AIZ.plugins.notify('success', response.message);
                            } else {
                                AIZ.plugins.notify('danger', response.message);
                            }
                        },
                        error: function () {
                            AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                        }
                    });
                }
            });
        });
    }
});
</script>
@endsection
