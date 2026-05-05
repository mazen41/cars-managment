@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">

        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-features.section.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Manage Sections')}}</span>
            </a>
            <a href="{{ route('admin.car-features.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add New Feature')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_features" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Car Features') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item confirm-alert" href="javascript:void(0)" data-target="#bulk-delete-modal">{{translate('Delete Selection')}}</a>
                </div>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="sort_by" onchange="sort_features()">
                    <option value="">{{ translate('Sort By') }}</option>
                    <option value="name" @if(request('sort_by') == 'name') selected @endif>{{translate('Name')}}</option>
                    <option value="created_at" @if(request('sort_by') == 'created_at') selected @endif>{{translate('Date')}}</option>
                    <option value="cars_count" @if(request('sort_by') == 'cars_count') selected @endif>{{translate('Usage Count')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="sort_order" onchange="sort_features()">
                    <option value="asc" @if(request('sort_order') == 'asc') selected @endif>{{translate('Ascending')}}</option>
                    <option value="desc" @if(request('sort_order') == 'desc') selected @endif>{{translate('Descending')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="{{ translate('Search features') }}">
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
                        <th>{{translate('Image')}}</th>
                        <th>{{translate('Feature Name')}}</th>
                        <th data-breakpoints="md">{{translate('Section')}}</th>
                        <th data-breakpoints="md">{{translate('Cars Count')}}</th>
                        <th data-breakpoints="md">{{translate('Created Date')}}</th>
                        <th class="text-right">{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($features as $key => $feature)
                    <tr>
                        <td>
                            <div class="form-group d-inline-block">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-one" name="id[]" value="{{$feature->id}}">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5">
                                <div class="col-auto">
                                    <img src="{{ $feature->image_url ?: static_asset('assets/img/placeholder.jpg') }}" alt="Image" class="size-50px img-fit">
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">{{ $feature->name }}</span>
                        </td>
                        <td>
                            <span class="text-muted">{{ $feature->section->name ?? translate("N/A") }}</span>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $feature->cars_count }}</span>
                        </td>
                        <td>{{ $feature->created_at->format('d-m-Y') }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('admin.car-features.edit', ['carFeature' => $feature->id, 'lang' => app()->getLocale()])}}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('admin.car-features.destroy', $feature->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $features->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulk-delete-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{translate('Delete Selected Features')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <p>{{translate('Are you sure you want to delete selected features?')}}</p>
                    <form method="post" action="{{ route('admin.car-features.bulk-delete') }}">
                        @csrf
                        <input type="hidden" name="feature_ids" id="bulk_feature_ids" value="">
                        <div class="form-group text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                            <button type="submit" class="btn btn-danger">{{translate('Delete')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function sort_features(el){
            $('#sort_features').submit();
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
                AIZ.plugins.notify('warning', '{{ translate('Please select at least one feature') }}');
                return;
            }

            $('#bulk_feature_ids').val(selectedIds.join(','));
            $(target).modal('show');
        });
    </script>
@endsection
