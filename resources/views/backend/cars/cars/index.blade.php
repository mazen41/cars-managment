@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('All Cars')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.cars.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add New Car')}}</span>
            </a>
        </div>
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_cars" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Cars') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    @can('export_cars')
                        <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('PDF')">{{ translate('Export PDF') }}</a>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('XLS')">{{ translate('Export XLS') }}</a>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('CSV')">{{ translate('Export CSV') }}</a>
                    @endcan
                    <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-status-modal">{{translate('Update Status')}}</a>
                    <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-delete-modal">{{translate('Delete selection')}}</a>
                </div>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="brand_id" name="brand_id" onchange="sort_cars()">
                    <option value="">{{ translate('All Brands') }}</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @if ($brand->id == request('brand_id')) selected @endif>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="moderation_status" name="moderation_status" onchange="sort_cars()">
                    <option value="">{{ translate('All Moderation Status') }}</option>
                    <option value="pending" @if (request('moderation_status') == 'pending') selected @endif>{{ translate('Pending') }}</option>
                    <option value="published" @if (request('moderation_status') == 'published') selected @endif>{{ translate('Published') }}</option>
                    <option value="rejected" @if (request('moderation_status') == 'rejected') selected @endif>{{ translate('Rejected') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="car_status" name="car_status" onchange="sort_cars()">
                    <option value="">{{ translate('All Car Status') }}</option>
                    <option value="available" @if (request('car_status') == 'available') selected @endif>{{ translate('Available') }}</option>
                    <option value="reserved" @if (request('car_status') == 'reserved') selected @endif>{{ translate('Reserved') }}</option>
                    <option value="in_auction" @if (request('car_status') == 'in_auction') selected @endif>{{ translate('In Auction') }}</option>
                    <option value="sold" @if (request('car_status') == 'sold') selected @endif>{{ translate('Sold') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="sort" id="sort" onchange="sort_cars()">
                    <option value="">{{ translate('Sort By') }}</option>
                    <option value="created_at,desc" @if(request('sort') == 'created_at,desc') selected @endif>{{translate('Newest First')}}</option>
                    <option value="created_at,asc" @if(request('sort') == 'created_at,asc') selected @endif>{{translate('Oldest First')}}</option>
                    <option value="price,desc" @if(request('sort') == 'price,desc') selected @endif>{{translate('Price (High > Low)')}}</option>
                    <option value="price,asc" @if(request('sort') == 'price,asc') selected @endif>{{translate('Price (Low > High)')}}</option>
                    <option value="milage,desc" @if(request('sort') == 'milage,desc') selected @endif>{{translate('Mileage (High > Low)')}}</option>
                    <option value="milage,asc" @if(request('sort') == 'milage,asc') selected @endif>{{translate('Mileage (Low > High)')}}</option>
                </select>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @if(request('search')) value="{{ request('search') }}" @endif placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <input type="hidden" name="export_type" value="PDF">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{ translate('Car') }}</th>
                        <th >{{ translate('VIN') }}</th>
                        <th >{{ translate('Brand') }}</th>
                        <th >{{ translate('Model') }}</th>
                        <th >{{ translate('Price') }}</th>
                        <th >{{ translate('Year') }}</th>
                        <th >{{ translate('Mileage') }}</th>
                        <th >{{ translate('Car Condition') }}</th>
                        <th >{{ translate('Todays Deal') }}</th>
                        <th >{{ translate('Featured') }}</th>
                        <th >{{ translate('Moderation') }}</th>
                        <th >{{ translate('Car Status') }}</th>
                        <th >{{ translate('User') }}</th>
                        <th >{{ translate('Published') }}</th>
                        <th width="10%" class="text-right">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cars as $key => $car)
                        <tr>
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]" value="{{ $car->id }}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row gutters-5 w-200px w-md-300px mw-100">
                                    <div class="col-auto">
                                        <img src="{{ $car->main_photo_url ?? static_asset('assets/img/placeholder.jpg') }}" alt="{{translate('Car')}}" class="size-50px img-fit">
                                    </div>
                                    <div class="col text-truncate">
                                        <span class="text-muted text-truncate-2">{{$car->car_name}}</span>
                                </div>
                            </td>
                             <td>
                                @if($car->vin)
                                  <span class="text-muted">{{ $car->vin }}</span>
                                @else
                                <span class="text-muted">{{ translate('N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($car->brand)
                                    <span class="text-muted">{{ $car->brand->name }}</span>
                                @else
                                    <span class="text-muted">{{ translate('N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($car->model)
                                    <span class="text-muted">{{ $car->model->name }}</span>
                                @else
                                    <span class="text-muted">{{ translate('N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($car->price)
                                    <span class="text-muted">{{ format_price($car->price) }}</span>
                                @else
                                    <span class="text-muted">{{ translate('N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $car->manufacture_year }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ number_format($car->milage) }} {{ translate('km') }}</span>
                            </td>
                            <td>
                                @if($car->condition == 'new')
                                    <span class="badge badge-inline badge-success">{{ translate('New') }}</span>
                                @else
                                    <span class="badge badge-inline badge-info">{{ translate('Used') }}</span>
                                @endif
                            </td>
                             <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input onchange="update_featured_and_todays_deal(this, 'todays_deal')" value="{{ $car->id }}" type="checkbox" <?php if ($car->todays_deal == 1) echo "checked"; ?> >
                                    <span class="slider round"></span>
                                </label>
                            </td>
                             <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input onchange="update_featured_and_todays_deal(this, 'featured')" value="{{ $car->id }}" type="checkbox" <?php if ($car->featured == 1) echo "checked"; ?> >
                                    <span class="slider round"></span>
                                </label>
                             </td>
                            <td>
                               {!!$car->moderation_status_html_badge !!}
                            </td>
                            <td>
                               {!!$car->car_status_html_badge !!}
                            </td>
                            <td>
                                @if($car->user)
                                    <span class="text-muted">{{ $car->user->name }}</span>
                                @else
                                    <span class="text-muted">{{ translate('N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $car->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="text-right">
                                 <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('admin.cars.show', $car->id) }}" title="{{ translate('View') }}">
                                    <i class="las la-eye"></i>
                                </a>
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('admin.cars.edit', $car->id) }}" title="{{ translate('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-soft-success btn-icon btn-circle btn-sm" onclick="change_status({{ $car->id }})" title="{{ translate('Toggle Status') }}">
                                    <i class="las la-sync"></i>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('admin.cars.destroy', $car->id) }}" title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <x-table_pagination :data="$cars" :paginate="request()->input('paginate')" />
        </div>
    </form>
</div>

<!-- Bulk Delete Modal -->
<div class="modal fade" id="bulk-delete-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Delete Cars') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ translate('Are you sure you want to delete selected cars?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-danger" onclick="bulk_delete()">{{ translate('Delete') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Moderation Status Modal -->
<div class="modal fade" id="bulk-status-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Update Moderation Status') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="bulk_moderation_status">{{ translate('Moderation Status') }}</label>
                    <select class="form-control aiz-selectpicker" id="bulk_moderation_status" name="bulk_moderation_status">
                        <option value="pending">{{ translate('Pending') }}</option>
                        <option value="published">{{ translate('Published') }}</option>
                        <option value="rejected">{{ translate('Rejected') }}</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="bulk_status_update()">{{ translate('Update') }}</button>
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
        function sort_cars() {
            $('#sort_cars').submit();
        }

        function change_status(id) {
            $.post('{{ route('admin.cars.toggle-status', '') }}/' + id, {
                _token: '{{ csrf_token() }}'
            }, function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            });
        }

        function bulk_delete() {
            var ids = [];
            $("input[name='id[]']:checked").each(function() {
                ids.push($(this).val());
            });

            if (ids.length > 0) {
                $.post('{{ route('admin.cars.bulk-delete') }}', {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                }, function(data) {
                    if (data.success) {
                        AIZ.plugins.notify('success', data.message);
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                    }
                });
            } else {
                AIZ.plugins.notify('warning', '{{ translate('Please select at least one car') }}');
            }
            $('#bulk-delete-modal').modal('hide');
        }

        function bulk_status_update() {
            var ids = [];
            $("input[name='id[]']:checked").each(function() {
                ids.push($(this).val());
            });

            var moderation_status = $('#bulk_moderation_status').val();

            if (ids.length > 0) {
                $.post('{{ route('admin.cars.bulk-update-status') }}', {
                    _token: '{{ csrf_token() }}',
                    ids: ids,
                    moderation_status: moderation_status
                }, function(data) {
                    if (data.success) {
                        AIZ.plugins.notify('success', data.message);
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                    }
                });
            } else {
                AIZ.plugins.notify('warning', '{{ translate('Please select at least one car') }}');
            }
            $('#bulk-status-modal').modal('hide');
        }

        $(document).ready(function() {
            $(document).on("change", ".check-all", function() {
            if (this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });
        });

        function bulk_export (type){
            var url = '{{route('admin.cars.bulk-export')}}';
            $("#sort_cars").attr("action", url);
            $('input[name="export_type"]').val(type);
            $('#sort_cars').submit();
            $("#sort_cars").attr("action", '');
        }

        function update_featured_and_todays_deal(el, type){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            var url = "{{ route('admin.cars.update-featured-todays-deal', ['car' => '__CAR_ID__']) }}";
            url = url.replace('__CAR_ID__', el.value);
            $.post(url, {_token:'{{ csrf_token() }}', id:el.value, type:type, status:status}, function(data){
                if(data.success == 1){
                    AIZ.plugins.notify('success', '{{ translate('Updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

    </script>
@endsection
