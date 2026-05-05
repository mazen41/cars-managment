@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Edit Car Color')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-colors.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to Colors')}}</span>
            </a>
        </div>
    </div>
</div>

<form action="{{ route('admin.car-colors.update', $carColor->id) }}" method="POST">
    <ul class="nav nav-tabs nav-fill language-bar">
        @foreach (get_all_active_language() as $key => $language)
            <li class="nav-item">
                <a class="nav-link text-reset @if ($language->code == request()->input('lang')) active @endif py-3" href="{{ route('admin.car-colors.edit', ['carColor'=>$carColor->id, 'lang'=> $language->code] ) }}">
                    <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                    <span>{{ $language->name }}</span>
                </a>
            </li>
        @endforeach
    </ul>
    @csrf
    @method('PUT')
    <input type="hidden" name="lang" value="{{request()->input('lang')}}"/>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Color Information')}}</h5>
                </div>
                <div class="card-body">

                    <!-- Color Name -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Color Name')}} <span
                                class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Color Name')}}" id="name" name="name"
                                class="form-control" required value="{{ old('name', $carColor->getTranslation('name', request()->input('lang'))) }}">
                            @error('name')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Hex Code -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Hex Code')}}</label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input type="text" class="form-control aiz-color-input"
                                placeholder="{{translate('Enter hex code (e.g., FF0000)')}}"  id="hex_code" name="hex_code"
                                 value="{{ old('hex_code', $carColor->hex_code) }}" pattern="^#[A-Fa-f0-9]{3,6}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text p-0">
                                        <input class="aiz-color-picker border-0 size-40px" type="color" value="{{ $carColor->hex_code ?: '#ffffff' }}">
                                    </span>
                                </div>
                            </div>
                            @error('hex_code')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Additional Information')}}</h5>
                </div>
                <div class="card-body">
                    <!-- Status -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Status')}} <span
                                class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="status" required>
                                <option value="">{{translate('Select Status')}}</option>
                                <option value="active" {{ old('status', $carColor->status)=='active' ? 'selected' : '' }}>
                                    {{translate('Active')}}</option>
                                <option value="inactive" {{ old('status', $carColor->status)=='inactive' ? 'selected' : '' }}>
                                    {{translate('Inactive')}}</option>
                            </select>
                            @error('status')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Color Preview -->
                    <div class="form-group row" id="color-preview-section" @if(!$carColor->hex_code) style="display: none;" @endif>
                        <label class="col-md-3 col-form-label">{{translate('Preview')}}</label>
                        <div class="col-md-9">
                            <div class="color-preview-large" style="width: 100%; height: 60px; border: 1px solid #ddd; border-radius: 4px; background-color: {{ $carColor->hex_code ?: '#ffffff' }};"></div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Usage')}}</label>
                        <div class="col-md-9">
                            <div class="text-info">
                                <small>{{translate('Cars using this color')}}: <strong>{{ $carColor->cars->count() }}</strong></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="form-group row">
        <div class="col-md-9 col-md-offset-3">
            <button type="submit" class="btn btn-primary">{{translate('Update Color')}}</button>
            <a href="{{ route('admin.car-colors.index') }}"
                class="btn btn-light">{{translate('Cancel')}}</a>
        </div>
    </div>
</form>

@endsection

@section('script')
<script>
$(document).ready(function() {
    // Color preview functionality
    function updateColorPreview() {
        var hexCode = $('#hex_code').val();
        if (hexCode && hexCode.length >= 3) {
            // Ensure hex code is valid
            if (/^[A-Fa-f0-9]{3,6}$/.test(hexCode)) {
                var fullHexCode = '#' + hexCode;
                $('.color-preview-box, .color-preview-large').css('background-color', fullHexCode);
                $('#color-preview-section').show();
            } else {
                $('.color-preview-box, .color-preview-large').css('background-color', '#ffffff');
                $('#color-preview-section').hide();
            }
        } else {
            $('.color-preview-box, .color-preview-large').css('background-color', '#ffffff');
            $('#color-preview-section').hide();
        }
    }

    // Update preview on input change
    $('#hex_code').on('input', updateColorPreview);

    // Initialize preview if there's already a value
    updateColorPreview();

    // Format hex code input (remove # if user enters it)
    $('#hex_code').on('input', function() {
        var value = $(this).val().replace('#', '').toUpperCase();
        $(this).val(value);
    });
});
</script>
@endsection
