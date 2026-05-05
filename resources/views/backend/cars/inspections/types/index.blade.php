@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ translate('Inspection Types') }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-inspection-types.create') }}" class="btn btn-circle btn-info">
                <span>{{ translate('Add New Type') }}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_types" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Inspection Types') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ translate('Bulk Action') }}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-status-modal">{{ translate('Update Status') }}</a>
                    <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-delete-modal">{{ translate('Delete selection') }}</a>
                </div>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status" name="status" onchange="sort_types()">
                    <option value="">{{ translate('All Status') }}</option>
                    <option value="active" @if (request('status') == 'active') selected @endif>{{ translate('Active') }}</option>
                    <option value="inactive" @if (request('status') == 'inactive') selected @endif>{{ translate('Inactive') }}</option>
                </select>
            </div>

            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @isset(request()->search) value="{{ request()->search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
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
                        <th>{{ translate('Name') }}</th>
                        <th data-breakpoints="md">{{ translate('Slug') }}</th>
                        <th data-breakpoints="md">{{ translate('Price') }}</th>
                        <th data-breakpoints="md">{{ translate('Sections') }}</th>
                        <th data-breakpoints="md">{{ translate('Fields') }}</th>
                        <th data-breakpoints="md">{{ translate('Inspections') }}</th>
                        <th data-breakpoints="md">{{ translate('Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Sort Order') }}</th>
                        <th data-breakpoints="md">{{ translate('Created') }}</th>
                        <th width="10%" class="text-right">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($carInspectionTypes as $key => $type)
                        <tr>
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]" value="{{ $type->id }}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="size-40px bg-soft-primary rounded mr-2 d-flex align-items-center justify-content-center">
                                        <i class="las la-clipboard-list text-primary"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.car-inspection-types.show', $type->id) }}" class="text-reset">
                                            <div class="text-truncate-2 fw-600">{{ $type->name }}</div>
                                            @if ($type->is_system_default)
                                                <span class="badge badge-inline badge-soft-success">{{ translate('System Default') }}</span>
                                            @endif
                                        </a>
                                        @if($type->description)
                                            <small class="text-muted">{{ Str::limit($type->description, 50) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-inline badge-soft-secondary">{{ $type->slug }}</span>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="text-success fw-600">{{ $type->formatted_price }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="badge badge-inline badge-soft-info">{{ $type->total_sections ?? 0 }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="badge badge-inline badge-soft-success">{{ $type->total_fields ?? 0 }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    @if($type->inspections->count() > 0)
                                        <a href="{{ route('admin.car-inspections.index', ['inspection_type' => $type->id]) }}" class="badge badge-inline badge-soft-primary">
                                            {{ $type->inspections->count() }}
                                        </a>
                                    @else
                                        <span class="badge badge-inline badge-soft-secondary">0</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="badge badge-inline badge-{{ $type->is_active ? 'success' : 'secondary' }}">
                                        {{ $type->is_active ? translate('Active') : translate('Inactive') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <span class="text-muted">{{ $type->sort_order ?? 0 }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    <small class="text-muted">{{ date('M d, Y', strtotime($type->created_at)) }}</small>
                                </div>
                            </td>
                            <td class="text-right">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                   href="{{ route('admin.car-inspection-types.show', $type->id) }}"
                                   title="{{ translate('View Details') }}">
                                    <i class="las la-eye"></i>
                                </a>
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                   href="{{ route('admin.car-inspection-types.edit', $type->id) }}"
                                   title="{{ translate('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                @if($type->inspections_count == 0)
                                    <a href="#"
                                       class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                       data-href="{{ route('admin.car-inspection-types.destroy', $type->id) }}"
                                       title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </a>
                                @endif
                                <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="#"
                                    title="{{ translate('Duplicate') }}"
                                    onclick="duplicateType({{ $type->id }})">
                                    <i class="las la-copy"></i>
                                </a>
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm" href="javascript:void(0)" onclick="change_status({{ $type->id }})"
                                    title=" {{ $type->is_active ? translate('Deactivate') : translate('Activate') }}">
                                    <i class="las la-{{ $type->is_active ? 'eye-slash' : 'eye' }}"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $carInspectionTypes->appends(request()->input())->links() }}
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
            <form action="{{ route('admin.car-inspection-types.bulk-update-status') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ids" value="">
                    <div class="form-group">
                        <label>{{ translate('Status') }}</label>
                        <select class="form-control aiz-selectpicker" name="status" required>
                            <option value="1">{{ translate('Active') }}</option>
                            <option value="0">{{ translate('Inactive') }}</option>
                        </select>
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
<div class="modal fade" id="bulk-delete-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Delete Inspection Types') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ translate('Are you sure you want to delete these inspection types?') }}</p>
                <p class="text-danger small">{{ translate('Warning: This action cannot be undone and will delete all related sections and fields.') }}</p>
                <form action="{{ route('admin.car-inspection-types.bulk-delete') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ids" value="">
                    <div class="text-right">
                        <button type="button" class="btn btn-light mr-2" data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ translate('Delete') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function(){
        //$('#container').removeClass('mainnav-lg').addClass('mainnav-sm');
    });

    function sort_types(){
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

        location.href = url.href;
    }

    $('#search').on('keyup', function(){
        setTimeout(() => {
            sort_types();
        }, 500);
    });

    function change_status(id) {
        $.ajax({
            url: `{{ route('admin.car-inspection-types.toggle-status', [
                'carInspectionType' => '__id__'
            ]) }}`.replace('__id__', id),
           data: {
             _token: '{{ csrf_token() }}',
            id: id
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

    function duplicateType(id) {
        $.ajax({
            url: `{{ route('admin.car-inspection-types.duplicate', ['carInspectionType' => '__id__']) }}`.replace('__id__', id),
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
