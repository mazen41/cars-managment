@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h3">{{ translate('Manual Examination Details') }}</h1>
            <div class="text-muted">{{ $manualExamination->inspection_number }}</div>
        </div>
        <div class="col-md-4 text-right">
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
