@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Schedule Manual Examination') }}</h1>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Examination Details') }}</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <strong>{{ translate('Car') }}:</strong>
            {{ $manualExamination->car->brand->name ?? '' }}
            {{ $manualExamination->car->model->name ?? '' }}
            ({{ $manualExamination->car->plate_number ?? translate('N/A') }})
        </div>
        <div class="mb-3">
            <strong>{{ translate('Inspection Number') }}:</strong>
            {{ $manualExamination->inspection_number }}
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Schedule Inspection') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.manual-examinations.update-schedule', $manualExamination->id) }}" method="POST">
            @csrf
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Inspector') }} <span class="text-danger">*</span></label>
                <div class="col-md-8">
                    <select name="inspector_id" class="form-control aiz-selectpicker" data-live-search="true" required>
                        <option value="">{{ translate('Select Inspector') }}</option>
                        @foreach($inspectors as $inspector)
                            <option value="{{ $inspector->id }}"
                                {{ $manualExamination->inspector_id == $inspector->id ? 'selected' : '' }}>
                                {{ $inspector->shop_name }} ({{ $inspector->user->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Scheduled Date & Time') }}</label>
                <div class="col-md-8">
                    <input type="datetime-local" name="scheduled_at" class="form-control"
                        value="{{ $manualExamination->scheduled_at ? $manualExamination->scheduled_at->format('Y-m-d\TH:i') : '' }}">
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-8 offset-md-3">
                    <button type="submit" class="btn btn-primary">{{ translate('Save Schedule') }}</button>
                    <a href="{{ route('admin.manual-examinations.index') }}" class="btn btn-secondary ml-2">{{ translate('Cancel') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
