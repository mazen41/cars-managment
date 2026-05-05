@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Add New FAQ')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('faqs.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to FAQs')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('FAQ Information')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" method="POST" action="{{ route('faqs.store') }}">
                    @csrf

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Type')}} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="type" data-live-search="true" required>
                                <option value="">{{ translate('Select Type') }}</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                        {{ translate($type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Question')}} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Enter the question')}}" id="question" name="question" class="form-control" value="{{ old('question') }}" required>
                            @error('question')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Answer')}} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <textarea class="form-control" name="answer"  placeholder="{{translate('Enter the answer')}}" required>{{ old('answer') }}</textarea>
                            @error('answer')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Slug')}}</label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Leave empty to auto-generate')}}" id="slug" name="slug" class="form-control" value="{{ old('slug') }}">
                            <small class="form-text text-muted">{{translate('Leave empty to auto-generate from question')}}</small>
                            @error('slug')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Sort Order')}}</label>
                        <div class="col-md-9">
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0" placeholder="{{translate('Sort Order')}}">
                            <small class="form-text text-muted">{{translate('Higher number has higher priority')}}</small>
                            @error('sort_order')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Publication Status')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <small class="form-text text-muted">{{translate('Publish this FAQ immediately')}}</small>
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">
                            {{translate('Save FAQ')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        // Auto-generate slug from question
        $('#question').on('input', function() {
            if ($('#slug').val() === '') {
                var slug = $(this).val()
                    .toLowerCase()
                    .replace(/[^a-z0-9 -]/g, '') // Remove invalid chars
                    .replace(/\s+/g, '-') // Replace spaces with -
                    .replace(/-+/g, '-'); // Replace multiple - with single -
                $('#slug').val(slug);
            }
        });
    });
</script>
@endsection
