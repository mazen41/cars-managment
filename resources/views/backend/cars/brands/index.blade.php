@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Car Brands')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-brands.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add New Brand')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_brands" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Car Brands') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-status-modal">{{translate('Update Status')}}</a>
                </div>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="status" onchange="sort_brands()">
                    <option value="">{{ translate('Filter by Status') }}</option>
                    <option value="active" @if(request('status') == 'active') selected @endif>{{translate('Active')}}</option>
                    <option value="inactive" @if(request('status') == 'inactive') selected @endif>{{translate('Inactive')}}</option>
                    <option value="pending" @if(request('status') == 'pending') selected @endif>{{translate('Pending')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="sort_by" onchange="sort_brands()">
                    <option value="">{{ translate('Sort By') }}</option>
                    <option value="name" @if(request('sort_by') == 'name') selected @endif>{{translate('Name')}}</option>
                    <option value="created_at" @if(request('sort_by') == 'created_at') selected @endif>{{translate('Date')}}</option>
                    <option value="models_count" @if(request('sort_by') == 'models_count') selected @endif>{{translate('Models Count')}}</option>
                    <option value="cars_count" @if(request('sort_by') == 'cars_count') selected @endif>{{translate('Cars Count')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="{{ translate('Search by name') }}">
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
                        <th>{{translate('Logo')}}</th>
                        <th>{{translate('Name')}}</th>
                        <th data-breakpoints="md">{{translate('Models Count')}}</th>
                        <th data-breakpoints="md">{{translate('Cars Count')}}</th>
                        <th data-breakpoints="md">{{translate('Status')}}</th>
                        <th data-breakpoints="md">{{translate('Created Date')}}</th>
                        <th class="text-right">{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brands as $key => $brand)
                    <tr>
                        <td>
                            <div class="form-group d-inline-block">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-one" name="id[]" value="{{$brand->id}}">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5">
                                <div class="col-auto">
                                    <img src="{{ $brand->logo_url ?: static_asset('assets/img/placeholder.jpg') }}" alt="Logo" class="size-50px img-fit">
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">{{ $brand->getTranslation('name') }}</span>
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $brand->models_count }}</span>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $brand->cars_count }}</span>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_status(this)" value="{{ $brand->id }}" type="checkbox" <?php if($brand->status == 'active') echo "checked";?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>{{ $brand->created_at->format('d-m-Y') }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('admin.car-brands.edit', ['carBrand' => $brand->id, 'lang' => app()->getLocale()])}}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                           @if ($brand->canBeDeleted())
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('admin.car-brands.destroy', $brand->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                           @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $brands->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')

    <!-- Bulk Status Update Modal -->
    <div class="modal fade" id="bulk-status-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{translate('Update Status')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('admin.car-brands.bulk-update-status') }}">
                        @csrf
                        <input type="hidden" name="brand_ids" id="bulk_brand_ids" value="">
                        <div class="form-group">
                            <label>{{translate('Status')}}</label>
                            <select class="form-control aiz-selectpicker" name="status" required>
                                <option value="">{{translate('Select Status')}}</option>
                                <option value="active">{{translate('Active')}}</option>
                                <option value="inactive">{{translate('Inactive')}}</option>
                                <option value="pending">{{translate('Pending')}}</option>
                            </select>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function sort_brands(el){
            $('#sort_brands').submit();
        }

        function update_status(el){
            if(el.checked){
                var status = 'active';
            }
            else{
                var status = 'inactive';
            }
            $.post('{{ route('admin.car-brands.toggle-status', '') }}/' + $(el).val(), {_token:'{{ csrf_token() }}', status:status, id:$(el).val()}, function(data){
                if(data.success == 1){
                    AIZ.plugins.notify('success', '{{ translate('Status updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        $(document).ready(function(){
            //$('#container').removeClass('mainnav-lg').addClass('mainnav-sm');
        });

        $(document).on("change", ".check-all", function() {
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

        $(document).on("click", ".confirm-alert", function(e) {
            e.preventDefault();
            var target = $(this).data('target');

            var selectedIds = [];
            $('.check-one:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if(selectedIds.length == 0) {
                AIZ.plugins.notify('warning', '{{ translate('Please select at least one brand') }}');
                return;
            }

            $('#bulk_brand_ids').val(selectedIds.join(','));
            $(target).modal('show');
        });
    </script>
@endsection
