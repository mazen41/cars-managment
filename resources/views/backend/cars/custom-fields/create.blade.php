@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Add New Custom Field')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-custom-fields.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to Custom Fields')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Custom Field Information')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.car-custom-fields.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Field Name -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Field Name')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" placeholder="{{translate('Field Name')}}" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Field Type -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Field Type')}} <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <select class="form-control aiz-selectpicker" name="type" id="field_type" required onchange="toggleOptions()">
                        <option value="">{{translate('Select Field Type')}}</option>
                        <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>{{translate('Text')}}</option>
                        <option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>{{translate('Number')}}</option>
                        <option value="email" {{ old('type') == 'email' ? 'selected' : '' }}>{{translate('Email')}}</option>
                        <option value="url" {{ old('type') == 'url' ? 'selected' : '' }}>{{translate('URL')}}</option>
                        <option value="textarea" {{ old('type') == 'textarea' ? 'selected' : '' }}>{{translate('Textarea')}}</option>
                        <option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>{{translate('Select')}}</option>
                        <option value="radio" {{ old('type') == 'radio' ? 'selected' : '' }}>{{translate('Radio')}}</option>
                        <option value="checkbox" {{ old('type') == 'checkbox' ? 'selected' : '' }}>{{translate('Checkbox')}}</option>
                        <option value="date" {{ old('type') == 'date' ? 'selected' : '' }}>{{translate('Date')}}</option>
                        <option value="time" {{ old('type') == 'time' ? 'selected' : '' }}>{{translate('Time')}}</option>
                        <option value="datetime" {{ old('type') == 'datetime' ? 'selected' : '' }}>{{translate('DateTime')}}</option>
                        <option value="file" {{ old('type') == 'file' ? 'selected' : '' }}>{{translate('File')}}</option>
                        <option value="image" {{ old('type') == 'image' ? 'selected' : '' }}>{{translate('Image')}}</option>
                    </select>
                    @error('type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Order -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Order')}}</label>
                <div class="col-md-9">
                    <input type="number" placeholder="{{translate('Order')}}" name="order" class="form-control" value="{{ old('order', 0) }}" min="0">
                    <small class="text-muted">{{translate('Fields will be sorted by this order')}}</small>
                    @error('order')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Required -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Required')}}</label>
                <div class="col-md-9">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" name="required" value="1" {{ old('required') ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                    <small class="text-muted">{{translate('Make this field required for users')}}</small>
                    @error('required')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Field Icon -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{translate('Field Icon')}}</label>
                <div class="col-md-9">
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                        <input type="hidden" name="icon" class="selected-files" value="{{ old('icon') }}">
                    </div>
                    <div class="file-preview box sm">
                    </div>
                    <small class="text-muted">{{translate('Upload an icon for the field. Image format: jpg, jpeg, png, gif, svg. Max size: 1MB')}}</small>
                    @error('icon')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Field Options (for select, radio, checkbox) -->
            <div class="form-group row" id="options-container" style="display: none;">
                <label class="col-md-3 col-form-label">{{translate('Field Options')}}</label>
                <div class="col-md-9">
                    <div class="bg-soft-secondary p-3 rounded">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-center">
                                    <button type="button" class="btn btn-info" onclick="add_more_option()">
                                        <i class="las la-plus"></i> {{translate('Add Option')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="field-options-container">
                            @if(old('options'))
                                @foreach(old('options') as $key => $option)
                                <div class="row gutters-5 option-row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{translate('Option Label')}}</label>
                                            <input type="text" class="form-control" name="options[{{ $key }}][label]" value="{{ $option['label'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{translate('Option Value')}}</label>
                                            <input type="text" class="form-control" name="options[{{ $key }}][value]" value="{{ $option['value'] }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger form-control" onclick="remove_option(this)">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="form-group row">
                <div class="col-md-9 col-md-offset-3">
                    <button type="submit" class="btn btn-primary">{{translate('Save Custom Field')}}</button>
                    <a href="{{ route('admin.car-custom-fields.index') }}" class="btn btn-light">{{translate('Cancel')}}</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
    var translation_counter = {{ old('translations') ? count(old('translations')) : 0 }};
    var option_counter = {{ old('options') ? count(old('options')) : 0 }};

    function toggleOptions() {
        var fieldType = $('#field_type').val();
        var optionFields = ['select', 'radio', 'checkbox'];

        if (optionFields.includes(fieldType)) {
            $('#options-container').show();
        } else {
            $('#options-container').hide();
            $('#field-options-container').empty(); // Clear options if not applicable
        }
    }

    function add_more_option() {
        var html = '<div class="row gutters-5 option-row">';
        html += '<div class="col-md-4">';
        html += '<div class="form-group">';
        html += '<label>{{translate("Option Label")}}</label>';
        html += '<input type="text" class="form-control" name="options[' + option_counter + '][label]" required>';
        html += '</div>';
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<div class="form-group">';
        html += '<label>{{translate("Option Value")}}</label>';
        html += '<input type="text" class="form-control" name="options[' + option_counter + '][value]" required>';
        html += '</div>';
        html += '</div>';
        html += '<div class="col-md-2">';
        html += '<div class="form-group">';
        html += '<label>&nbsp;</label>';
        html += '<button type="button" class="btn btn-danger form-control" onclick="remove_option(this)">';
        html += '<i class="las la-trash"></i>';
        html += '</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        $('#field-options-container').append(html);
        option_counter++;
    }

    function remove_option(button) {
        $(button).closest('.option-row').remove();
    }
    // Initialize on page load
    $(document).ready(function() {
        toggleOptions();
    });
</script>
@endsection
