@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ isset($inspection_type) ? translate('Edit Inspection Type') : translate('Add New Inspection Type') }}</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.car-inspection-types.index') }}" class="btn btn-light">
                <i class="las la-arrow-left mr-1"></i>{{ translate('Back to Types') }}
            </a>
        </div>
    </div>
</div>

<form action="{{ isset($inspection_type) ? route('admin.car-inspection-types.update', $inspection_type->id) : route('admin.car-inspection-types.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($inspection_type))
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Inspection Type Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Name') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="name" id="name" value="{{ old('name', isset($inspection_type) ? $inspection_type->name : '') }}" placeholder="{{ translate('Enter inspection type name') }}" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Slug') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="slug" id="slug" value="{{ old('slug', isset($inspection_type) ? $inspection_type->slug : '') }}" placeholder="{{ translate('inspection-type-slug') }}" required>
                            <small class="form-text text-muted">{{ translate('URL friendly version of the name. Only lowercase letters, numbers and hyphens allowed.') }}</small>
                            @error('slug')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Description') }}</label>
                        <div class="col-md-9">
                            <textarea class="form-control" name="description" rows="4" placeholder="{{ translate('Brief description of this inspection type...') }}">{{ old('description', isset($inspection_type) ? $inspection_type->description : '') }}</textarea>
                            <small class="form-text text-muted">{{ translate('Optional description to help users understand what this inspection covers') }}</small>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Price') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="price" id="price" value="{{ old('price', isset($inspection_type) ? $inspection_type->price : '0.00') }}" placeholder="{{ translate('0.00') }}" step="0.01" min="0" required>
                            <small class="form-text text-muted">{{ translate('Inspection fee amount in your local currency') }}</small>
                            @error('price')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status and Ordering -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Status & Ordering') }}</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">{{ translate('System Default') }}</label>
                        <span class="form-text text-muted">{{ translate("If enabled, this will make the inspection's inspector selected by the admin") }}</span>
                        <select class="form-control aiz-selectpicker" name="is_system_default">
                            <option value="0" {{ old('is_system_default')}}>{{ translate('No') }}</option>
                            <option value="1" {{ old('is_system_default')}}>{{ translate('Yes') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ translate('Sort Order') }}</label>
                        <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', isset($inspection_type) ? $inspection_type->sort_order : 1) }}" min="1">
                        <small class="form-text text-muted">{{ translate('Lower numbers appear first') }}</small>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mt-3">
                <div class="card-body">
                    <div class="text-right">
                        <button type="button" class="btn btn-soft-secondary mr-2" onclick="window.history.back()">
                            {{ translate('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            @if(isset($inspection_type))
                                <i class="las la-save mr-1"></i>{{ translate('Update Type') }}
                            @else
                                <i class="las la-plus mr-1"></i>{{ translate('Create Type') }}
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            @if(isset($inspection_type))
                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ translate('Quick Actions') }}</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.car-inspection-types.sections.index', $inspection_type->id) }}" class="btn btn-outline-info btn-sm btn-block mb-2">
                            <i class="las la-list mr-1"></i>{{ translate('Manage Sections') }}
                        </a>
                        <a href="{{ route('admin.car-inspection-types.duplicate', $inspection_type->id) }}" class="btn btn-outline-secondary btn-sm btn-block mb-2">
                            <i class="las la-copy mr-1"></i>{{ translate('Duplicate Type') }}
                        </a>
                        <a href="{{ route('admin.cars.inspections.index', ['inspection_type' => $inspection_type->id]) }}" class="btn btn-outline-primary btn-sm btn-block">
                            <i class="las la-eye mr-1"></i>{{ translate('View Inspections') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</form>

@endsection

@section('script')
<script type="text/javascript">
$(document).ready(function() {
    // Auto-generate slug from name
    $('#name').on('input', function() {
        const name = $(this).val();
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .replace(/-+/g, '-');
        $('#slug').val(slug);
        updatePreview();
    });

    // Update preview when inputs change
    $('#name, #description, #price, #category, input[name="estimated_duration_hours"], input[name="estimated_duration_minutes"]').on('input change', function() {
        updatePreview();
    });

    // Toggle scoring options
    $('#enable_scoring').on('change', function() {
        if ($(this).is(':checked')) {
            $('#scoring_options').show();
            $('#preview-scoring').text('{{ translate("Enabled") }}');
        } else {
            $('#scoring_options').hide();
            $('#preview-scoring').text('{{ translate("Disabled") }}');
        }
    });

    // Form validation
    $('form').on('submit', function(e) {
        const name = $('#name').val().trim();
        const slug = $('#slug').val().trim();
        const price = $('#price').val();

        if (!name) {
            e.preventDefault();
            AIZ.plugins.notify('warning', '{{ translate("Please enter inspection type name") }}');
            $('#name').focus();
            return false;
        }

        if (!slug) {
            e.preventDefault();
            AIZ.plugins.notify('warning', '{{ translate("Please enter a valid slug") }}');
            $('#slug').focus();
            return false;
        }

        if (!price || parseFloat(price) < 0) {
            e.preventDefault();
            AIZ.plugins.notify('warning', '{{ translate("Please enter a valid price") }}');
            $('#price').focus();
            return false;
        }

        // Validate slug format
        const slugPattern = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
        if (!slugPattern.test(slug)) {
            e.preventDefault();
            AIZ.plugins.notify('warning', '{{ translate("Slug can only contain lowercase letters, numbers and hyphens") }}');
            $('#slug').focus();
            return false;
        }

        // Show loading state
        $('button[type="submit"]').prop('disabled', true).html('<i class="las la-spinner la-spin mr-1"></i>{{ translate("Saving...") }}');
    });

    function updatePreview() {
        const name = $('#name').val() || '{{ translate("Inspection Type Name") }}';
        const description = $('#description').val() || '{{ translate("Description will appear here...") }}';
        const category = $('#category').val() || '{{ translate("Not selected") }}';
        const hours = $('input[name="estimated_duration_hours"]').val() || 2;
        const minutes = $('input[name="estimated_duration_minutes"]').val() || 0;

        $('#preview-name').text(name);
        $('#preview-description').text(description);
        $('#preview-category').text(category.replace('_', ' ').toUpperCase());
        $('#preview-duration').text(hours + 'h ' + minutes + 'm');
    }

    // Initialize preview
    updatePreview();
});
</script>
@endsection

@section('style')
<style>
.preview-content h6 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.preview-content p {
    margin-bottom: 15px;
    font-size: 0.875rem;
}

.preview-content .d-flex {
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375em 0.5em;
}

.aiz-checkbox-inline .aiz-checkbox {
    margin-right: 0;
    margin-bottom: 10px;
}

.card-body .table td {
    padding: 0.25rem 0;
    border: none;
}

.text-editor {
    min-height: 100px;
}

#scoring_options {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}
</style>
@endsection
