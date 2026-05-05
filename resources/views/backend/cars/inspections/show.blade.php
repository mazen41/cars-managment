@extends('backend.layouts.app')

@section('content')
<style>
.timeline {
    position: relative;
    padding: 0;
    margin: 0;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    left: 8px;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding: 10px 0 10px 30px;
    margin: 0;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 15px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content h6 {
    margin: 0;
    font-size: 14px;
}

.timeline-content small {
    font-size: 12px;
}
</style>
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Inspection Details') }}</h1>
            <p class="text-muted mb-0">{{ $carInspection->inspection_number }}</p>
        </div>
        <div class="col-md-6 text-right">
            @if($carInspection->status == 'completed')
                <a href="{{ route('admin.car-inspections.report', $carInspection->id) }}" class="btn btn-secondary" target="_blank">
                    <i class="las la-file-alt mr-1"></i>{{ translate('View Report') }}
                </a>
                 <a href="{{ route('admin.car-inspections.pdf', $carInspection->id) }}" class="btn btn-secondary" target="_blank">
                    <i class="las la-arrow-down mr-1"></i>{{ translate('Download PDF') }}
                </a>
            @endif
            @if(in_array($carInspection->status, ['pending', 'in_progress']))
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cancelModal">
                    <i class="las la-times mr-1"></i>{{ translate('Cancel Inspection') }}
                </button>
            @endif
            <a href="{{ route('admin.car-inspections.index') }}" class="btn btn-light">
                <i class="las la-arrow-left mr-1"></i>{{ translate('Back to List') }}
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Inspection Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Inspection Overview') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50"><strong>{{ translate('Inspection Number') }}:</strong></td>
                                <td>{{ $carInspection->inspection_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Status') }}:</strong></td>
                                <td>
                                    @php
                                        $status_colors = [
                                            'pending' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'secondary',
                                            'failed' => 'danger'
                                        ];
                                        $color = $status_colors[$carInspection->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-inline badge-{{ $color }}">
                                        {{ translate(ucfirst(str_replace('_', ' ', $carInspection->status))) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Inspector') }}:</strong></td>
                                <td>{{ $carInspection->inspector->shop_name ?? translate('Not Assigned') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Scheduled Date') }}:</strong></td>
                                <td>
                                    @if($carInspection->scheduled_at)
                                        {{ date('M d, Y g:i A', strtotime($carInspection->scheduled_at)) }}
                                    @else
                                        <span class="text-muted">{{ translate('Not Scheduled') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Started At') }}:</strong></td>
                                <td>
                                    @if($carInspection->started_at)
                                        {{ date('M d, Y g:i A', strtotime($carInspection->started_at)) }}
                                    @else
                                        <span class="text-muted">{{ translate('Not Started') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="w-50"><strong>{{ translate('Completed At') }}:</strong></td>
                                <td>
                                    @if($carInspection->completed_at)
                                        {{ date('M d, Y g:i A', strtotime($carInspection->completed_at)) }}
                                    @else
                                        <span class="text-muted">{{ translate('Not Completed') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Total Score') }}:</strong></td>
                                <td>
                                    @if($carInspection->total_score)
                                        <span class="h6 text-primary">{{ number_format($carInspection->total_score, 1) }}%</span>
                                    @else
                                        <span class="text-muted">{{ translate('N/A') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Overall Condition') }}:</strong></td>
                                <td>
                                    @if($carInspection->overall_condition)
                                        @php
                                            $condition_colors = [
                                                'excellent' => 'success',
                                                'good' => 'info',
                                                'fair' => 'warning',
                                                'poor' => 'danger',
                                                'critical' => 'dark'
                                            ];
                                            $condition_color = $condition_colors[$carInspection->overall_condition] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-inline badge-{{ $condition_color }}">
                                            {{ translate(ucfirst($carInspection->overall_condition)) }}
                                        </span>
                                    @else
                                        <span class="text-muted">{{ translate('N/A') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                     <strong>{{ translate('Car delivered to inspector') }}:</strong>
                                </td>
                                <td>
                                    @if ($carInspection->delivered_to_inspector)
                                        <span class="badge badge-inline badge-success">{{ translate('Yes') }}</span>
                                        @else
                                        <span class="badge badge-inline badge-danger">{{ translate('No') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Progress') }}:</strong></td>
                                <td>
                                    @php
                                        $completion = $carInspection->completion_percentage ?? 0;
                                        $progress_color = $completion >= 100 ? 'success' : ($completion >= 50 ? 'info' : 'warning');
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <small class="mr-2">{{ $completion }}%</small>
                                        <div class="progress flex-1" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $progress_color }}" style="width: {{ $completion }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ translate('Created At') }}:</strong></td>
                                <td>{{ date('M d, Y g:i A', strtotime($carInspection->created_at)) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($carInspection->inspector_notes)
                    <div class="mt-3">
                        <strong>{{ translate('Inspector Notes') }}:</strong>
                        <p class="mt-2 p-3 bg-light rounded">{{ $carInspection->inspector_notes }}</p>
                    </div>
                @endif

                @if($carInspection->recommendations)
                    <div class="mt-3">
                        <strong>{{ translate('Recommendations') }}:</strong>
                        <p class="mt-2 p-3 bg-light rounded">{{ $carInspection->recommendations }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Information -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Payment Information') }}</h5>
            </div>
            <div class="card-body">
                @if($carInspection->payment)
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">{{ translate('Reference ID') }}:</td>
                                    <td><code>{{ $carInspection->payment->reference_id }}</code></td>
                                </tr>
                                <tr>
                                <td class="text-muted">{{ translate('Transaction ID') }}:</td>
                                <td>{{ $carInspection->payment->transaction_id ?? 'Not provided' }}</td>
                            </tr>
                                <tr>
                                    <td class="text-muted">{{ translate('Amount') }}:</td>
                                    <td class="fw-700">
                                        @if($carInspection->payment->amount)
                                            <span class="text-success">{{ single_price($carInspection->payment->amount) }}</span>
                                        @else
                                            <span class="text-muted">{{ translate('N/A') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">{{ translate('Method') }}:</td>
                                    <td>
                                        <span class="badge badge-inline badge-secondary">
                                            {{ $carInspection->payment->method_display }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted">{{ translate('Status') }}:</td>
                                    <td>
                                        <span class="badge badge-inline
                                            @if($carInspection->payment->status == 'paid') badge-success
                                            @elseif($carInspection->payment->status == 'pending') badge-warning
                                            @elseif($carInspection->payment->status == 'unpaid') badge-danger
                                            @elseif($carInspection->payment->status == 'cancelled') badge-secondary
                                            @elseif($carInspection->payment->status == 'refunded') badge-dark
                                            @else badge-light @endif">
                                            {{ $carInspection->payment->status_display }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">{{ translate('Paid At') }}:</td>
                                    <td>
                                        @if($carInspection->payment->paid_at)
                                            {{ $carInspection->payment->paid_at->format('M j, Y g:i A') }}
                                        @else
                                            <span class="text-muted">{{ translate('Not paid yet') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                 {{-- @if($carInspection->payment->details)
                                <tr>
                                    <td class="text-muted">{{ translate('Details') }}:</td>
                                    <td>
                                       {{ $carInspection->payment->details}}
                                    </td>
                                </tr>
                                @endif --}}
                            </table>
                        </div>
                    </div>
                    <div class="text-right mt-3">
                        @can('edit_car_inspection_payments')
                       <!-- update payment status -->
                       @if ($carInspection->payment->status == 'pending')
                        <button type="button" class="btn btn-soft-success" data-toggle="modal" data-target="#setPaidPaymentModal">
                            <i class="las la-check mr-1"></i>{{ translate('Set as Paid') }}
                        </button>
                         <button type="button" class="btn btn-soft-danger" data-toggle="modal" data-target="#setUnpaidPaymentModal">
                            <i class="las la-close mr-1"></i>{{ translate('Set as Unpaid') }}
                        </button>
                       @endif
                        @if ($carInspection->payment->can_refund && $carInspection->payment->status == 'paid')
                            <button type="button" class="btn btn-soft-secondary" data-toggle="modal" data-target="#setRefundedPaymentModal">
                                <i class="las la-arrow mr-1"></i>{{ translate('Set as Refunded') }}
                            </button>
                        @endif
                        @endcan
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="text-muted mb-3">
                            <i class="las la-credit-card" style="font-size: 48px;"></i>
                        </div>
                        <h6 class="text-muted">{{ translate('No Payment Record') }}</h6>
                        <p class="text-muted small">{{ translate('No payment has been created for this inspection yet.') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Inspection Sections -->
        @if($carInspection->inspectionType && $carInspection->inspectionType->sections)
            @foreach($carInspection->inspectionType->sections as $section)
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $section->name }}</h6>
                        @php
                            $section_completion = $sectionCompletions[$section->id] ?? ['completion_percentage' => 0, 'is_complete' => false];
                        @endphp
                        <div class="d-flex align-items-center">
                            <small class="mr-2">{{ $section_completion['completion_percentage'] }}%</small>
                            <div class="progress" style="width: 100px; height: 6px;">
                                <div class="progress-bar {{ $section_completion['is_complete'] ? 'bg-success' : 'bg-info' }}"
                                     style="width: {{ $section_completion['completion_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($section->description)
                            <p class="text-muted small mb-3">{{ $section->description }}</p>
                        @endif

                        <div class="row">
                            @foreach($section->fields as $field)
                                @php
                                    $field_value = $carInspection->fieldValues->where('field_id', $field->id)->first();
                                @endphp
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 {{ $field_value && $field_value->is_flagged ? 'border-danger' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <strong >{{ $field->name }}</strong>
                                            @if($field_value && $field_value->is_flagged)
                                                <span class="badge badge-inline badge-danger badge-sm">{{ translate('Flagged') }}</span>
                                            @endif
                                        </div>

                                        @if($field->help_text)
                                            <small class="text-muted d-block mb-2">{{ $field->help_text }}</small>
                                        @endif

                                        <div class="mb-2">
                                            <strong>{{ translate('Value') }}:</strong>
                                            @if($field_value)
                                                @if($field->field_type == 'boolean')
                                                    <span class="badge badge-inline badge-{{ $field_value->value == '1' ? 'success' : 'secondary' }}">
                                                        {{ $field_value->value == '1' ? translate('Yes') : translate('No') }}
                                                    </span>
                                                @elseif($field->field_type == 'checkbox')
                                                    @if($field_value->value)
                                                        @php $selected_options = json_decode($field_value->value, true); @endphp
                                                        @foreach($selected_options as $option)
                                                            <span class="badge badge-inline badge-info mr-1">{{ $option }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">{{ translate('None Selected') }}</span>
                                                    @endif
                                                @elseif($field->field_type == 'select' || $field->field_type == 'radio')
                                                    <span class="badge badge-inline badge-primary">{{ $field_value->value }}</span>
                                                @else
                                                    <span >{{ $field_value->value }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ translate('Not Filled') }}</span>
                                            @endif
                                        </div>

                                        @if($field_value && $field_value->score !== null)
                                            <div class="mb-2">
                                                <strong>{{ translate('Score') }}:</strong>
                                                <span class="text-primary">{{ $field_value->score }}%</span>
                                            </div>
                                        @endif

                                        @if($field_value && $field_value->notes)
                                            <div class="mb-2">
                                                <strong>{{ translate('Notes') }}:</strong>
                                                <p class="mb-0 small">{{ $field_value->notes }}</p>
                                            </div>
                                        @endif

                                        @if($field_value && $field_value->file_attachments)
                                            @if($field_value->file_attachments && count($field_value->file_attachments) > 0)
                                                <div>
                                                    <strong>{{ translate('Attachments') }}:</strong>
                                                    @foreach($field_value->file_attachments as $attachment)
                                                        <div class="mt-1">
                                                            <a href="{{ $attachment['url'] }}" target="_blank" class="text-info">
                                                                <i class="las la-file mr-1"></i>{{ $attachment['url'] }}
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif

                                        <div class="mt-2">
                                            @if($field_value && $field_value->value)
                                                <small class="text-success">
                                                    <i class="las la-check-circle"></i>{{ translate('Completed') }}
                                                </small>
                                            @else
                                                <small class="text-warning">
                                                    <i class="las la-clock"></i>{{ translate('Pending') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Car Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Car Information') }}</h6>
            </div>
            <div class="card-body">
                @if($carInspection->car)
                    <div class="text-center mb-3">
                        @if($carInspection->car->main_photo)
                            <img src="{{ uploaded_asset($carInspection->car->main_photo) }}" alt="Car" class="img-fluid rounded" style="max-height: 200px;">
                        @else
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" alt="Car" class="img-fluid rounded" style="max-height: 200px;">
                        @endif
                    </div>

                    <h6 class="text-center mb-3">{{ $carInspection->car->name }}</h6>

                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>{{ translate('VIN') }}:</strong></td>
                            <td>{{ $carInspection->car->vin ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Brand') }}:</strong></td>
                            <td>{{ $carInspection->car->brand->name ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Model') }}:</strong></td>
                            <td>{{ $carInspection->car->model->name ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Category') }}:</strong></td>
                            <td>{{ $carInspection->car->category->name ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Year') }}:</strong></td>
                            <td>{{ $carInspection->car->manufacture_year ?? translate('N/A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Price') }}:</strong></td>
                            <td>{{ format_price($carInspection->car->price ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Status') }}:</strong></td>
                            <td>
                                <span class="badge badge-inline badge-{{ $carInspection->car->published == 1 ? 'success' : 'warning' }}">
                                    {{ $carInspection->car->published == 1 ? translate('Published') : translate('Draft') }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    <div class="text-center">
                        <a href="{{ route('admin.cars.show', $carInspection->car->id) }}" class="btn btn-sm btn-outline-primary">
                            {{ translate('View Car Details') }}
                        </a>
                    </div>
                @else
                    <p class="text-muted text-center">{{ translate('Car information not available') }}</p>
                @endif
            </div>
        </div>

        <!-- Inspection Type Information -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Inspection Type') }}</h6>
            </div>
            <div class="card-body">
                @if($carInspection->inspectionType)
                    <h6>{{ $carInspection->inspectionType->name }}</h6>
                    @if($carInspection->inspectionType->description)
                        <p class="text-muted small">{{ $carInspection->inspectionType->description }}</p>
                    @endif

                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>{{ translate('Total Sections') }}:</strong></td>
                            <td>{{ $carInspection->inspectionType->sections->count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Total Fields') }}:</strong></td>
                            <td>{{ $carInspection->inspectionType->sections->sum(function($section) { return $section->fields->count(); }) }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ translate('Active') }}:</strong></td>
                            <td>
                                <span class="badge badge-inline badge-{{ $carInspection->inspectionType->is_active ? 'success' : 'secondary' }}">
                                    {{ $carInspection->inspectionType->is_active ? translate('Yes') : translate('No') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                @else
                    <p class="text-muted">{{ translate('Inspection type information not available') }}</p>
                @endif
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Activity Timeline') }}</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">{{ translate('Inspection Created') }}</h6>
                            <small class="text-muted">{{ date('M d, Y g:i A', strtotime($carInspection->created_at)) }}</small>
                        </div>
                    </div>

                    @if($carInspection->started_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ translate('Inspection Started') }}</h6>
                                <small class="text-muted">{{ date('M d, Y g:i A', strtotime($carInspection->started_at)) }}</small>
                            </div>
                        </div>
                    @endif

                    @if($carInspection->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ translate('Inspection Completed') }}</h6>
                                <small class="text-muted">{{ date('M d, Y g:i A', strtotime($carInspection->completed_at)) }}</small>
                            </div>
                        </div>
                    @endif

                    @if($carInspection->status == 'cancelled')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ translate('Inspection Cancelled') }}</h6>
                                <small class="text-muted">{{ date('M d, Y g:i A', strtotime($carInspection->updated_at)) }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('modal')
   <!-- Cancel Inspection Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ translate('Cancel Inspection') }}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('admin.car-inspections.cancel', $carInspection->id) }}" method="GET">
                    @csrf
                    <div class="modal-body">
                        <p>{{ translate('Are you sure you want to cancel this inspection?') }}</p>
                        <div class="form-group">
                            <label>{{ translate('Reason for Cancellation') }}</label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="{{ translate('Please provide a reason for cancellation...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('No, Keep It') }}</button>
                        <button type="submit" class="btn btn-danger">{{ translate('Yes, Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Set paid Payment Modal -->
    <div class="modal fade" id="setPaidPaymentModal" tabindex="-1" role="dialog" aria-labelledby="setPaidPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="setPaidPaymentModalLabel">{{ translate('Set as paid') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.car-inspections.payments.set-paid', $carInspection) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="text-danger">{{translate('You wont be able to change this!')}}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-info">{{ translate('Confirm') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Set unpaid modal -->
    <div class="modal fade" id="setUnpaidPaymentModal" tabindex="-1" role="dialog" aria-labelledby="setUnpaidPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="setUnpaidPaymentModalLabel">{{ translate('Set as unpaid') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.car-inspections.payments.set-unpaid', $carInspection) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="text-danger">{{translate('You wont be able to change this!')}}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-info">{{ translate('Confirm') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Refunded modal -->
    <div class="modal fade" id="setRefundedPaymentModal" tabindex="-1" role="dialog" aria-labelledby="setRedunfedPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="setRefundedPaymentModalLabel">{{ translate('Set as refunded') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.car-inspections.payments.set-refunded', $carInspection) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        {{-- <div class="form-group">
                            <label for="cancel_details">{{ translate('Cancellation Reason') }}</label>
                            <textarea class="form-control" id="cancel_details" name="details" rows="3" placeholder="{{ translate('Enter reason for cancellation...') }}">{{ $carInspection->payment ? $carInspection->payment->details : '' }}</textarea>
                        </div> --}}
                        <p class="text-warning">{{ translate('This action will refund the payment. Are you sure?') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Close') }}</button>
                        <button type="submit" class="btn btn-warning">{{ translate('Refund Payment') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
   function startInspection(){
    $.ajax({
        url: "{{ route('admin.car-inspections.start', $carInspection->id) }}",
        method: 'POST',
        data: {
              _token: $('meta[name="csrf-token"]').attr('content')
            },
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
</script>
@endsection
