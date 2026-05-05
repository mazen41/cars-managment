@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ translate('Car Inspections') }}</h1>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_inspections" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ translate('Bulk Action') }}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-status-modal">{{
                        translate('Update Status') }}</a>
                    <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-delete-modal">{{
                        translate('Cancel Selected') }}</a>
                </div>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status" name="status"
                    onchange="sort_inspections()">
                    <option value="">{{ translate('All Status') }}</option>
                    <option value="pending" @if (request('status')=='pending' ) selected @endif>{{ translate('Pending')
                        }}</option>
                    <option value="in_progress" @if (request('status')=='in_progress' ) selected @endif>{{ translate('In
                        Progress') }}</option>
                    <option value="completed" @if (request('status')=='completed' ) selected @endif>{{
                        translate('Completed') }}</option>
                    <option value="cancelled" @if (request('status')=='cancelled' ) selected @endif>{{
                        translate('Cancelled') }}</option>
                    <option value="failed" @if (request('status')=='failed' ) selected @endif>{{ translate('Failed') }}
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="inspection_type"
                    name="inspection_type" onchange="sort_inspections()">
                    <option value="">{{ translate('All Types') }}</option>
                    @foreach ($inspectionTypes as $type)
                    <option value="{{ $type->id }}" @if ($type->id == request('inspection_type')) selected @endif>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="inspector"
                    name="inspector" onchange="sort_inspections()">
                    <option value="">{{ translate('All Inspectors') }}</option>
                    @foreach ($inspectors as $inspector)
                    <option value="{{ $inspector->id }}" @if ($inspector->id == request('inspector')) selected @endif>
                        {{ $inspector->shop_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range Filter -->
            <div class="col-md-2">
                <input type="text" class="form-control aiz-date-range" @isset(request()->date) value="{{ request()->date
                }}" @endisset placeholder="{{ translate('Filter by date') }}" name="date" onchange="sort_inspections()"
                data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true" autocomplete="off">
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search"
                        @isset(request()->search) value="{{ request()->search }}" @endisset placeholder="{{
                    translate('Type inspection number, car name & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{ translate('Inspection #') }}</th>
                        <th data-breakpoints="md">{{ translate('Car') }}</th>
                        <th data-breakpoints="md">{{ translate('Type') }}</th>
                        <th data-breakpoints="md">{{ translate('Inspector') }}</th>
                        <th data-breakpoints="md">{{ translate('Scheduled Date') }}</th>
                        <th data-breakpoints="md">{{ translate('Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Payment') }}</th>
                        <th data-breakpoints="md">{{ translate('Score') }}</th>
                        <th data-breakpoints="md">{{ translate('Progress') }}</th>
                        <th width="10%" class="text-right">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inspections as $key => $inspection)
                    <tr>
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]"
                                            value="{{ $inspection->id }}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.car-inspections.show', $inspection->id) }}" class="text-reset">
                                {{ $inspection->inspection_number }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($inspection->car && $inspection->car->main_photo)
                                <img src="{{ uploaded_asset($inspection->car->main_photo) }}" alt="Car"
                                    class="size-40px img-fit rounded mr-2">
                                @else
                                <img src="{{ static_asset('assets/img/placeholder.jpg') }}" alt="Car"
                                    class="size-40px img-fit rounded mr-2">
                                @endif
                                <div>
                                    @if($inspection->car)
                                    <small class="text-muted">
                                        {{ $inspection->car->car_name ?? '' }}
                                    </small>
                                     <div class="text-muted fs-12">{{ $inspection->car->vin }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-inline badge-soft-info">
                                {{ $inspection->inspectionType->name ?? translate('N/A') }}
                            </span>
                        </td>
                        <td>
                            {{ $inspection->inspector->shop_name ?? translate('Not Assigned') }}
                        </td>
                        <td>
                            @if($inspection->scheduled_at)
                            {{ date('M d, Y g:i A', strtotime($inspection->scheduled_at)) }}
                            @if($inspection->scheduled_at < now() && $inspection->status == 'pending')
                                <br><small class="text-danger">{{ translate('Overdue') }}</small>
                                @endif
                                @else
                                <span class="text-muted">{{ translate('Not Scheduled') }}</span>
                                @endif
                        </td>
                        <td>
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
                            <span class="badge badge-inline badge-soft-{{ $color }}">
                                {{ translate(ucfirst(str_replace('_', ' ', $inspection->status))) }}
                            </span>
                        </td>
                        <td>
                            @if($inspection->payment)
                            <span class="badge badge-inline
                                        @if($inspection->payment->status == 'paid') badge-soft-success
                                        @elseif($inspection->payment->status == 'pending') badge-soft-warning
                                        @elseif($inspection->payment->status == 'unpaid') badge-soft-danger
                                        @elseif($inspection->payment->status == 'refunded') badge-soft-dark
                                        @else badge-soft-light @endif">
                                {{ $inspection->payment->status_display }}
                            </span>
                            @if($inspection->payment->amount)
                            <br><small class="text-muted">{{ single_price($inspection->payment->amount) }}</small>
                            @endif
                            @else
                            <span class="badge badge-inline badge-soft-secondary">{{ translate('No Payment') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($inspection->total_score)
                            <div class="d-flex align-items-center">
                                <span class="mr-2">{{ number_format($inspection->total_score, 1) }}%</span>
                                @php
                                $score_color = 'danger';
                                if($inspection->total_score >= 90) $score_color = 'success';
                                elseif($inspection->total_score >= 75) $score_color = 'info';
                                elseif($inspection->total_score >= 60) $score_color = 'warning';
                                @endphp
                                <div class="progress flex-1" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $score_color }}"
                                        style="width: {{ $inspection->total_score }}%"></div>
                                </div>
                            </div>
                            @else
                            <span class="text-muted">{{ translate('N/A') }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                            $completion = $inspection->completion_percentage ?? 0;
                            $progress_color = $completion >= 100 ? 'success' : ($completion >= 50 ? 'info' : 'warning');
                            @endphp
                            <div class="d-flex align-items-center">
                                <small class="mr-2">{{ $completion }}%</small>
                                <div class="progress flex-1" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $progress_color }}"
                                        style="width: {{ $completion }}%"></div>
                                </div>
                            </div>
                        </td>

                        <td class="text-right">
                            @can('edit_car_inspection')
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                href="{{ route('admin.car-inspections.show', $inspection->id) }}"
                                title="{{ translate('View Details') }}">
                                <i class="las la-eye"></i>
                            </a>
                            @if ($inspection->status == 'pending')
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                href="{{ route('admin.car-inspections.edit', $inspection->id) }}"
                                title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            @endif
                            @if ($inspection->car->user->user_type != 'seller' && !$inspection->delivered_to_inspector)
                             <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                href="{{ route('admin.car-inspections.deliver-to-inspector', $inspection->id) }}"
                                title="{{ translate('Deliver to inspector') }}">
                                <i class="las la-car"></i>
                            @endif
                            @if(in_array($inspection->status, ['failed', 'cancelled']))
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                data-href="{{ route('admin.car-inspections.destroy', $inspection->id) }}"
                                title="{{ translate('Delte Inspection') }}">
                                <i class="las la-trash"></i>
                            </a>
                            @endif
                            @if($inspection->status == 'completed')
                            <a class="btn btn-soft-secondary btn-icon btn-circle btn-sm"
                                href="{{ route('admin.car-inspections.report', $inspection->id) }}"
                                title="{{ translate('View Report') }}" target="_blank">
                                <i class="las la-file-alt"></i>
                            </a>
                            @endif
                            @endcan
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $inspections->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

<!-- Bulk Status Update Modal -->
<div class="modal fade" id="bulk-status-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Update Status') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.car-inspections.bulk-update-status') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ids" value="">
                    <div class="form-group">
                        <label>{{ translate('Status') }}</label>
                        <select class="form-control aiz-selectpicker" name="status" required>
                            <option value="pending">{{ translate('Pending') }}</option>
                            <option value="cancelled">{{ translate('Cancelled') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Reason (Optional)') }}</label>
                        <textarea class="form-control" name="reason" rows="3"
                            placeholder="{{ translate('Reason for status change...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Update Status') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
{{-- <div class="modal fade" id="bulk-delete-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Cancel Inspections') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ translate('Are you sure you want to cancel these inspections?') }}</p>
                <form action="{{ route('admin.car-inspections.bulk-cancel') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ids" value="">
                    <div class="form-group">
                        <label>{{ translate('Reason') }}</label>
                        <textarea class="form-control" name="reason" rows="3"
                            placeholder="{{ translate('Reason for cancellation...') }}" required></textarea>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-light mr-2" data-dismiss="modal">{{ translate('No')
                            }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('Yes, Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> --}}

@endsection

@section('modal')
@include('modals.delete_modal')
@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function(){
        //$('#container').removeClass('mainnav-lg').addClass('mainnav-sm');
    });

    function sort_inspections(){
        var url = new URL(window.location.href);
        var search = $('input[name=search]').val();
        if(search != '') {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }

        var status = $('#status').val();
        if(status != '') {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }

        var inspection_type = $('#inspection_type').val();
        if(inspection_type != '') {
            url.searchParams.set('inspection_type', inspection_type);
        } else {
            url.searchParams.delete('inspection_type');
        }

        var inspector = $('#inspector').val();
        if(inspector != '') {
            url.searchParams.set('inspector', inspector);
        } else {
            url.searchParams.delete('inspector');
        }

        var date = $('input[name=date]').val();
        if(date != '') {
            url.searchParams.set('date', date);
        } else {
            url.searchParams.delete('date');
        }

        location.href = url.href;
    }

    $('#search').on('keyup', function(){
        setTimeout(() => {
            sort_inspections();
        }, 500);
    });

    $('.aiz-date-range').on('change', function(){
        sort_inspections();
    })

    // Bulk actions
    $(document).on("click", ".confirm-alert", function(e){
        e.preventDefault();
        var target = $(this).data('target');

        if($(":checkbox:checked").length > 0){
            var checkedIds = [];
            $("input:checkbox[name='id[]']:checked").each(function() {
                checkedIds.push($(this).val());
            });
            $(target + ' input[name="ids"]').val(checkedIds.join(','));
            $(target).modal('show');
        } else {
            AIZ.plugins.notify('warning', '{{ translate("Please select at least one item") }}');
        }
    });

    // Check all functionality
    $(document).on('change', '.check-all', function(){
        if(this.checked){
            $('.check-one').each(function(){
                this.checked = true;
            });
        } else {
            $('.check-one').each(function(){
                this.checked = false;
            });
        }
    });

    // Individual checkbox change
    $(document).on('change', '.check-one', function(){
        if($('.check-one:checked').length == $('.check-one').length){
            $('.check-all').prop('checked', true);
        } else {
            $('.check-all').prop('checked', false);
        }
    });
</script>
@endsection
