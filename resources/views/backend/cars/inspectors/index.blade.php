@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ translate('Car Inspectors') }}</h1>
        </div>
        <div class="col text-right">
            @can('create_car_inspectors')
                <a href="{{ route('admin.car-inspectors.create') }}" class="btn btn-circle btn-info">
                    <span>{{ translate('Add New Inspector') }}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_inspectors" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Car Inspectors') }}</h5>
            </div>

            @can('edit_car_inspectors')
            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    {{ translate('Bulk Action') }}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_status_update('active')">{{ translate('Activate Selected') }}</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_status_update('inactive')">{{ translate('Deactivate Selected') }}</a>
                </div>
            </div>
            @endcan

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status" name="status" onchange="sort_inspectors()">
                    <option value="">{{ translate('All Status') }}</option>
                    <option value="active" @if (request('status') == 'active') selected @endif>{{ translate('Active') }}</option>
                    <option value="inactive" @if (request('status') == 'inactive') selected @endif>{{ translate('Inactive') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="sort" name="sort" onchange="sort_inspectors()">
                    <option value="created_at" @if (request('sort') == 'created_at') selected @endif>{{ translate('Sort by Date') }}</option>
                    <option value="shop_name" @if (request('sort') == 'shop_name') selected @endif>{{ translate('Sort by Shop Name') }}</option>
                    <option value="admin_to_pay" @if (request('sort') == 'admin_to_pay') selected @endif>{{ translate('Sort by Amount Owed') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>
    </form>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    @can('edit_car_inspectors')
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
                    @endcan
                    <th>{{ translate('Inspector') }}</th>
                    <th data-breakpoints="md">{{ translate('Shop Information') }}</th>
                    <th data-breakpoints="md">{{ translate('Contact') }}</th>
                    <th data-breakpoints="md">{{ translate('Stats') }}</th>
                    <th data-breakpoints="md">{{ translate('Amount Owed') }}</th>
                    <th data-breakpoints="md">{{ translate('Status') }}</th>
                    <th data-breakpoints="md">{{ translate('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inspectors as $inspector)
                    <tr>
                        @can('edit_car_inspectors')
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]" value="{{ $inspector->id }}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                        @endcan
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $inspector->image_url }}" alt="{{ $inspector->full_name }}" class="size-50px rounded-circle mr-2">
                                <div>
                                    <div class="text-truncate">
                                        <strong>{{ $inspector->full_name }}</strong>
                                    </div>
                                    <div class="text-muted small">
                                        {{ translate('Member since') }} {{ $inspector->created_at->format('M Y') }}
                                    </div>
                                    @if($inspector->certification_number)
                                        <div class="text-muted small">
                                            {{ translate('Cert #') }}: {{ $inspector->certification_number }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $inspector->shop_name }}</strong>
                            </div>
                            <div class="text-muted small mt-1">
                                {{ Str::limit($inspector->address, 50) }}
                            </div>
                            <div class="text-muted small">
                                @if($inspector->country)
                                    <i class="las la-globe"></i> {{ $inspector->country->name }}
                                @endif
                                @if($inspector->state)
                                    , {{ $inspector->state->name }}
                                @endif
                                @if($inspector->city)
                                    , {{ $inspector->city->getTranslation('name') }}
                                @endif
                            </div>
                            @if($inspector->experience_years)
                                <div class="text-muted small">
                                    {{ $inspector->experience_years }} {{ translate('years experience') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($inspector->phone)
                                <div class="small">
                                    <i class="las la-phone"></i> {{ $inspector->phone }}
                                </div>
                            @endif
                            @if($inspector->email)
                                <div class="small">
                                    <i class="las la-envelope"></i> {{ $inspector->email }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                <div>{{ translate('Total') }}: <strong>{{ $inspector->stats['total_inspections'] }}</strong></div>
                                <div class="text-success">{{ translate('Completed') }}: {{ $inspector->stats['completed_inspections'] }}</div>
                                <div class="text-warning">{{ translate('Pending') }}: {{ $inspector->stats['pending_inspections'] }}</div>
                            </div>
                        </td>
                        <td>
                            @if($inspector->admin_to_pay > 0)
                                <span class="badge badge-warning badge-inline">
                                    {{ format_price($inspector->admin_to_pay) }}
                                </span>
                            @else
                                <span class="badge badge-success badge-inline">
                                    {{ translate('Paid Up') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($inspector->is_active)
                                <span class="badge badge-success badge-inline">{{ translate('Active') }}</span>
                            @else
                                <span class="badge badge-danger badge-inline">{{ translate('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="dropdown">
                                <button class="btn btn-soft-secondary btn-icon btn-circle btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="las la-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @can('view_car_inspectors')
                                        <a class="dropdown-item" href="{{ route('admin.car-inspectors.show', $inspector->id) }}">{{ translate('View Details') }}</a>
                                    @endcan

                                    @can('edit_car_inspectors')
                                        <a class="dropdown-item" href="{{ route('admin.car-inspectors.edit', $inspector->id) }}">{{ translate('Edit') }}</a>
                                    @endcan

                                    @can('manage_car_inspector_payments')
                                        @if($inspector->admin_to_pay > 0)
                                            <a class="dropdown-item" href="{{ route('admin.car-inspectors.show-payment-form', $inspector->id) }}">{{ translate('Make Payment') }}</a>
                                        @endif
                                        <a class="dropdown-item" href="{{ route('admin.car-inspectors.payments', $inspector->id) }}">{{ translate('Payment History') }}</a>
                                    @endcan

                                    @can('delete_car_inspectors')
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item confirm-delete" data-href="{{ route('admin.car-inspectors.destroy', $inspector->id) }}" href="javascript:void(0)">{{ translate('Delete') }}</a>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $inspectors->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        function sort_inspectors(el) {
            $('#sort_inspectors').submit();
        }

        function bulk_status_update(status) {
            var data = new FormData();
            data.append('_token', '{{ csrf_token() }}');
            data.append('status', status === 'active' ? 1 : 0);

            var inspector_ids = [];
            $('input:checkbox.check-one:checked').each(function() {
                inspector_ids.push($(this).val());
            });

            if(inspector_ids.length === 0) {
                AIZ.plugins.notify('warning', '{{ translate('Please select at least one inspector') }}');
                return;
            }

            data.append('inspector_ids', JSON.stringify(inspector_ids));

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('admin.car-inspectors.bulk-update-status') }}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if(data.success) {
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                    }
                }
            });
        }

        $(document).ready(function() {
            $('.check-all').on('change', function() {
                if(this.checked) {
                    $('.check-one').each(function() {
                        this.checked = true;
                    });
                } else {
                    $('.check-one').each(function() {
                        this.checked = false;
                    });
                }
            });

            $('.check-one').on('change', function() {
                if($('.check-one:checked').length == $('.check-one').length) {
                    $('.check-all').prop('checked', true);
                } else {
                    $('.check-all').prop('checked', false);
                }
            });

            // Search functionality
            $('#search').on('keyup', function(e) {
                if(e.keyCode == 13) {
                    sort_inspectors();
                }
            });
        });
    </script>
@endsection
