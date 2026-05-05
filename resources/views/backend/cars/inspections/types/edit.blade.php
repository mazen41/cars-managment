@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Edit Inspection Type') }}</h1>
            <p class="text-muted mb-0">{{ $carInspectionType->name }}</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.car-inspection-types.show', $carInspectionType->id) }}" class="btn btn-info">
                <i class="las la-eye mr-1"></i>{{ translate('View Details') }}
            </a>
            <a href="{{ route('admin.car-inspection-types.index') }}" class="btn btn-light">
                <i class="las la-arrow-left mr-1"></i>{{ translate('Back to Types') }}
            </a>
        </div>
    </div>
</div>

<form action="{{ route('admin.car-inspection-types.update', $carInspectionType->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Basic Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Name') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="name" id="name"
                                   value="{{ old('name', $carInspectionType->name) }}"
                                   placeholder="{{ translate('Enter inspection type name') }}" required>
                            @error('name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Slug') }}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="slug" id="slug"
                                   value="{{ old('slug', $carInspectionType->slug) }}"
                                   placeholder="{{ translate('inspection-type-slug') }}">
                            <small class="form-text text-muted">{{ translate('URL friendly version of the name. Leave empty to auto-generate.') }}</small>
                            @error('slug')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Description') }}</label>
                        <div class="col-md-9">
                            <textarea class="form-control" name="description" rows="4"
                                      placeholder="{{ translate('Brief description of this inspection type...') }}">{{ old('description', $carInspectionType->description) }}</textarea>
                            <small class="form-text text-muted">{{ translate('Optional description to help users understand what this inspection covers') }}</small>
                            @error('description')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Price') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="price" id="price"
                                   value="{{ old('price', $carInspectionType->price) }}"
                                   placeholder="{{ translate('0.00') }}" step="0.01" min="0" required>
                            <small class="form-text text-muted">{{ translate('Inspection fee amount in your local currency') }}</small>
                            @error('price')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Sort Order') }}</label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="sort_order" min="0"
                                   value="{{ old('sort_order', $carInspectionType->sort_order ?? 0) }}"
                                   placeholder="{{ translate('0') }}">
                            <small class="form-text text-muted">{{ translate('Higher numbers appear later in the list') }}</small>
                            @error('sort_order')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('System Default') }}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="is_system_default" value="1"
                                       {{ old('is_system_default', $carInspectionType->is_system_default) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <span class="ml-2">{{ translate('System Default') }}</span>
                            <small class="form-text text-muted d-block">{{ translate("If enabled, this will make the inspection's inspector selected by the admin") }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sections Management Notice -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Sections & Fields Management') }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="las la-info-circle text-info mr-3" style="font-size: 2rem;"></i>
                            <div>
                                <h6 class="mb-1">{{ translate('Manage Sections and Fields') }}</h6>
                                <p class="mb-2">{{ translate('To add, edit, or manage sections and fields for this inspection type, please save your changes and use the detailed view.') }}</p>
                                <a href="{{ route('admin.car-inspection-types.show', $carInspectionType->id) }}" class="btn btn-sm btn-info">
                                    <i class="las la-eye mr-1"></i>{{ translate('Go to Detailed View') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Current sections summary -->
                    @if($carInspectionType->sections->count() > 0)
                        <div class="mt-3">
                            <h6>{{ translate('Current Sections') }} ({{ $carInspectionType->sections->count() }})</h6>
                            <div class="row">
                                @foreach($carInspectionType->sections->take(6) as $section)
                                    <div class="col-md-4 mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="las la-folder text-muted mr-2"></i>
                                            <span class="text-truncate">{{ $section->name }}</span>
                                            <small class="text-muted ml-1">({{ $section->fields->count() }})</small>
                                        </div>
                                    </div>
                                @endforeach
                                @if($carInspectionType->sections->count() > 6)
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted">+{{ $carInspectionType->sections->count() - 6 }} {{ translate('more sections') }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="las la-folder-open text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">{{ translate('No sections created yet') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Actions') }}</h6>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="las la-save mr-1"></i>{{ translate('Update Inspection Type') }}
                    </button>

                    <a href="{{ route('admin.car-inspection-types.show', $carInspectionType->id) }}" class="btn btn-info btn-block mt-2">
                        <i class="las la-eye mr-1"></i>{{ translate('View & Manage Sections') }}
                    </a>

                    <a href="{{ route('admin.car-inspection-types.index') }}" class="btn btn-secondary btn-block mt-2">
                        <i class="las la-times mr-1"></i>{{ translate('Cancel') }}
                    </a>

                    <hr>

                    <button type="button" class="btn btn-outline-secondary btn-block" onclick="duplicateType()">
                        <i class="las la-copy mr-1"></i>{{ translate('Duplicate Type') }}
                    </button>

                    <button type="button" class="btn btn-outline-warning btn-block mt-2" onclick="change_status()">
                        <i class="las la-{{ $carInspectionType->is_active ? 'eye-slash' : 'eye' }} mr-1"></i>
                        {{ $carInspectionType->is_active ? translate('Deactivate') : translate('Activate') }}
                    </button>

                    @if($carInspectionType->inspections->count() === 0)
                        <button type="button" class="btn btn-outline-danger btn-block mt-2" onclick="confirmDelete()">
                            <i class="las la-trash mr-1"></i>{{ translate('Delete Type') }}
                        </button>
                    @endif
                </div>
            </div>

            <!-- Information Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Information') }}</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>{{ translate('ID') }}:</strong></td>
                            <td>#{{ $carInspectionType->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Sections') }}:</strong></td>
                            <td>{{ $carInspectionType->sections->count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Total Fields') }}:</strong></td>
                            <td>{{ $carInspectionType->sections->sum(function($section) { return $section->fields->count(); }) }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Inspections') }}:</strong></td>
                            <td>{{ $carInspectionType->inspections->count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Price') }}:</strong></td>
                            <td><span class="text-success fw-600">{{ $carInspectionType->formatted_price }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Created') }}:</strong></td>
                            <td>{{ $carInspectionType->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Updated') }}:</strong></td>
                            <td>{{ $carInspectionType->updated_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@section('script')
<script>
$(document).ready(function() {
    // Auto-generate slug from name
    $('#name').on('input', function() {
        const name = $(this).val();
        const slug = name.toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove special characters
            .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
        $('#slug').val(slug);
    });

    // Form validation
    $('form').on('submit', function(e) {
        const name = $('#name').val().trim();
        const price = $('#price').val();

        if (!name) {
            e.preventDefault();
            alert('{{ translate("Name is required") }}');
            $('#name').focus();
            return false;
        }

        if (!price || parseFloat(price) < 0) {
            e.preventDefault();
            alert('{{ translate("Please enter a valid price") }}');
            $('#price').focus();
            return false;
        }
    });
});

   function change_status() {
        $.ajax({
            url: "{{ route('admin.car-inspection-types.toggle-status', ['carInspectionType' => $carInspectionType->id]) }}",
           data: {
             _token: '{{ csrf_token() }}',
           },
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
    }

    function duplicateType() {
        $.ajax({
            url: "{{ route('admin.car-inspection-types.duplicate', ['carInspectionType' => $carInspectionType->id]) }}",
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
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        });
    }

function confirmDelete() {
    if (confirm('{{ translate("Are you sure you want to delete this inspection type? This action cannot be undone.") }}')) {
        window.location.href = '{{ route('admin.car-inspection-types.destroy', $carInspectionType->id) }}';
    }
}
</script>
@endsection
