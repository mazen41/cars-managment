@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Car Custom Fields')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-custom-fields.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add New Field')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_fields" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Custom Fields') }}</h5>
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
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="type" onchange="sort_fields()">
                    <option value="">{{ translate('Filter by Type') }}</option>
                    <option value="text" @if(request('type') == 'text') selected @endif>{{translate('Text')}}</option>
                    <option value="number" @if(request('type') == 'number') selected @endif>{{translate('Number')}}</option>
                    <option value="email" @if(request('type') == 'email') selected @endif>{{translate('Email')}}</option>
                    <option value="select" @if(request('type') == 'select') selected @endif>{{translate('Select')}}</option>
                    <option value="checkbox" @if(request('type') == 'checkbox') selected @endif>{{translate('Checkbox')}}</option>
                    <option value="radio" @if(request('type') == 'radio') selected @endif>{{translate('Radio')}}</option>
                    <option value="textarea" @if(request('type') == 'textarea') selected @endif>{{translate('Textarea')}}</option>
                    <option value="date" @if(request('type') == 'date') selected @endif>{{translate('Date')}}</option>
                    <option value="file" @if(request('type') == 'file') selected @endif>{{translate('File')}}</option>
                    <option value="image" @if(request('type') == 'image') selected @endif>{{translate('Image')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="required" onchange="sort_fields()">
                    <option value="">{{ translate('All Fields') }}</option>
                    <option value="1" @if(request('required') == '1') selected @endif>{{translate('Required')}}</option>
                    <option value="0" @if(request('required') == '0') selected @endif>{{translate('Optional')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="sort_by" onchange="sort_fields()">
                    <option value="">{{ translate('Sort By') }}</option>
                    <option value="name" @if(request('sort_by') == 'name') selected @endif>{{translate('Name')}}</option>
                    <option value="order" @if(request('sort_by') == 'order') selected @endif>{{translate('Order')}}</option>
                    <option value="created_at" @if(request('sort_by') == 'created_at') selected @endif>{{translate('Date')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="{{ translate('Search fields') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0" id="sortable-table">
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
                        <th>{{translate('Icon')}}</th>
                        <th>{{translate('Field Name')}}</th>
                        <th data-breakpoints="md">{{translate('Type')}}</th>
                        <th data-breakpoints="md">{{translate('Order')}}</th>
                        <th data-breakpoints="md">{{translate('Required')}}</th>
                        <th data-breakpoints="md">{{translate('Options')}}</th>
                        <th data-breakpoints="md">{{translate('Values')}}</th>
                        <th data-breakpoints="md">{{translate('Created Date')}}</th>
                        <th class="text-right">{{translate('Actions')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customFields as $key => $field)
                    <tr data-id="{{ $field->id }}">
                        <td>
                            <div class="form-group d-inline-block">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-one" name="id[]" value="{{$field->id}}">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5">
                                <div class="col-auto">
                                    <img src="{{ $field->icon_url ?: static_asset('assets/img/placeholder.jpg') }}" alt="Icon" class="size-40px img-fit">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="las text-muted mr-2" style="cursor: move;"></i>
                                <span class="text-muted">{{ $field->getTranslation('name') }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-inline badge-info">{{ ucfirst($field->type) }}</span>
                        </td>
                        <td>
                            <span class="badge badge-inline badge-secondary">{{ $field->order }}</span>
                        </td>
                        <td>
                            @if($field->required)
                                <span class="badge badge-inline badge-danger">{{translate('Required')}}</span>
                            @else
                                <span class="badge badge-inline badge-light">{{translate('Optional')}}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-inline badge-primary">{{ $field->options_count }}</span>
                        </td>
                        <td>
                            <span class="badge badge-inline badge-success">{{ $field->values_count }}</span>
                        </td>
                        <td>{{ $field->created_at->format('d-m-Y') }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('admin.car-custom-fields.edit', ['carCustomField' => $field->id, 'lang' => app()->getLocale()])}}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('admin.car-custom-fields.destroy', $field->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $customFields->appends(request()->input())->links() }}
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
                    <h4 class="modal-title h6">{{translate('Delete Selected Fields')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <p>{{translate('Are you sure you want to delete selected custom fields?')}}</p>
                    <form method="post" action="{{ route('admin.car-custom-fields.bulk-delete') }}">
                        @csrf
                        <input type="hidden" name="field_ids" id="bulk_field_ids" value="">
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
        function sort_fields(el){
            $('#sort_fields').submit();
        }

        $(document).ready(function(){
            // Make table sortable
            $("#sortable-table tbody").sortable({
                handle: ".drag-handle",
                update: function(event, ui) {
                    var order = [];
                    $('#sortable-table tbody tr').each(function(index) {
                        order.push({
                            id: $(this).data('id'),
                            order: index + 1
                        });
                    });

                    $.ajax({
                        url: '{{ route('admin.car-custom-fields.update-order') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            order: order
                        },
                        success: function(response) {
                            AIZ.plugins.notify('success', '{{ translate('Order updated successfully') }}');
                        },
                        error: function() {
                            AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                        }
                    });
                }
            });
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
                AIZ.plugins.notify('warning', '{{ translate('Please select at least one field') }}');
                return;
            }

            $('#bulk_field_ids').val(selectedIds.join(','));
            $(target).modal('show');
        });
    </script>
@endsection
