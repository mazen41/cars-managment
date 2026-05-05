@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">

        </div>
        <div class="col text-right">
             <a href="{{ route('admin.car-features.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Manage Features')}}</span>
            </a>
            <a href="{{ route('admin.car-features.section.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add New Section')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_sections" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Car Feature Sections') }}</h5>
            </div>

            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="sort_by" onchange="sort_sections()">
                    <option value="">{{ translate('Sort By') }}</option>
                    <option value="name" @if(request('sort_by') == 'name') selected @endif>{{translate('Name')}}</option>
                    <option value="created_at" @if(request('sort_by') == 'created_at') selected @endif>{{translate('Date')}}</option>
                    <option value="features_count" @if(request('sort_by') == 'features_count') selected @endif>{{translate('Features Count')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="sort_order" onchange="sort_sections()">
                    <option value="asc" @if(request('sort_order') == 'asc') selected @endif>{{translate('Ascending')}}</option>
                    <option value="desc" @if(request('sort_order') == 'desc') selected @endif>{{translate('Descending')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="{{ translate('Search sections') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm">{{ translate('Filter') }}</button>
            </div>
        </div>
    </form>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{translate('Name')}}</th>
                    <th data-breakpoints="md">{{translate('Features Count')}}</th>
                    <th data-breakpoints="md">{{translate('Created Date')}}</th>
                    <th data-breakpoints="md" class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sections as $key => $section)
                    <tr>
                        <td>{{ ($key+1) + ($sections->currentPage() - 1)*$sections->perPage() }}</td>
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col">
                                    <span class="text-muted text-truncate-2">{{ $section->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-inline badge-info">{{ $section->features_count }}</span>
                        </td>
                        <td>{{ $section->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                               href="{{ route('admin.car-features.section.edit', $section->id) }}"
                               title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            @if($section->features_count == 0)
                                <a href="{{ route('admin.car-features.section.destroy', $section->id) }}"
                                   class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                   data-href="{{ route('admin.car-features.section.destroy', $section->id) }}"
                                   title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            @else
                                <span class="btn btn-soft-secondary btn-icon btn-circle btn-sm"
                                      title="{{ translate('Cannot delete section with features') }}">
                                    <i class="las la-trash"></i>
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $sections->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        function sort_sections(){
            var url = '{{ route('admin.car-features.section.index') }}';
            var sort_by = $('[name=sort_by]').val();
            var sort_order = $('[name=sort_order]').val();
            var search = $('[name=search]').val();

            if(sort_by != '' || sort_order != '' || search != ''){
                url = url+'?';
                if(sort_by != ''){
                    url = url+'sort_by='+sort_by+'&';
                }
                if(sort_order != ''){
                    url = url+'sort_order='+sort_order+'&';
                }
                if(search != ''){
                    url = url+'search='+search+'&';
                }
            }

            location.href = url;
        }

        $(document).ready(function(){
            $('#search').on('keyup', function(){
                if($(this).val().length > 2 || $(this).val().length == 0){
                    sort_sections();
                }
            });
        });
    </script>
@endsection
