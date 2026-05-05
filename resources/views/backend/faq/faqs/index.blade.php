@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('All FAQs')}}</h1>
        </div>
        @can('add_faq')
            <div class="col-md-6 text-md-right">
                <a href="{{ route('faqs.create') }}" class="btn btn-circle btn-info">
                    <span>{{translate('Add New FAQ')}}</span>
                </a>
            </div>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('FAQs') }}</h5>
        <div class="pull-right clearfix">
            <form class="form-inline" id="sort_faqs" action="" method="GET">
                <div class="form-group mr-3">
                    <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Search FAQs...') }}">
                </div>
                <div class="form-group mr-3">
                    <select class="form-control aiz-selectpicker" name="faq_type" data-live-search="true">
                        <option value="">{{ translate('All Types') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" @if($faq_type == $type) selected @endif>
                                {{ translate($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-3">
                    <select class="form-control aiz-selectpicker" name="status">
                        <option value="">{{ translate('All Status') }}</option>
                        <option value="1" @if($status === '1') selected @endif>{{ translate('Published') }}</option>
                        <option value="0" @if($status === '0') selected @endif>{{ translate('Draft') }}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
            </form>
        </div>
    </div>
    <div class="card-body">

        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th width="5%">
                        <div class="form-group">
                            <div class="aiz-checkbox-inline">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" id="select-all">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </div>
                    </th>
                    <th width="5%">#</th>
                    <th>{{translate('Question')}}</th>
                    <th width="15%">{{translate('Type')}}</th>
                    {{-- <th width="10%">{{translate('Views')}}</th> --}}
                    <th width="10%">{{translate('Status')}}</th>
                    <th width="15%" class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($faqs as $key => $faq)
                <tr>
                    <td>
                        <div class="form-group">
                            <div class="aiz-checkbox-inline">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="faq-checkbox" value="{{ $faq->id }}">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </div>
                    </td>
                    <td>{{ ($key+1) + ($faqs->currentPage() - 1)*$faqs->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div>
                                <strong>{{ Str::limit($faq->getTranslation('question'), 60) }}</strong>
                                <br><small class="text-muted">{{ Str::limit(strip_tags($faq->getTranslation('answer')), 80) }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-inline badge-info">{{ translate($faq->type) }}</span>
                    </td>
                    {{-- <td>
                        <span class="badge badge-inline badge-secondary">{{ $faq->view_count }}</span>
                    </td> --}}
                    <td>
                        <label class="aiz-switch aiz-switch-success mb-0">
                            <input onchange="update_published(this)" value="{{ $faq->id }}" type="checkbox" <?php if($faq->is_published == 1) echo "checked";?>>
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <td class="text-right">
                        @can('edit_faq')
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('faqs.show', $faq->id)}}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('faqs.edit', $faq->id)}}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a class="btn btn-soft-warning btn-icon btn-circle btn-sm" href="{{route('faqs.duplicate', $faq->id)}}" title="{{ translate('Duplicate') }}">
                                <i class="las la-copy"></i>
                            </a>
                        @endcan
                        @can('delete_faq')
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('faqs.destroy', $faq->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $faqs->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection

@section('modal')
@include('modals.delete_modal')
@endsection

@section('script')
<script type="text/javascript">
    function update_published(el){
        if(el.checked){
            var status = 1;
        }
        else{
            var status = 0;
        }
        $.post('{{ route('faqs.toggle-status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
            if(data.success == 1){
                AIZ.plugins.notify('success', '{{ translate('FAQ status updated successfully') }}');
            }
            else{
                if(status){
                    el.checked = false;
                }
                else{
                    el.checked = true;
                }
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        });
    }

    $(document).ready(function() {
        // Select all functionality
        $('#select-all').on('change', function() {
            $('.faq-checkbox').prop('checked', this.checked);
        });

        // Individual checkbox change
        $('.faq-checkbox').on('change', function() {
            if (!this.checked) {
                $('#select-all').prop('checked', false);
            }
        });

        // Search functionality
        $('#search').on('keyup', function(e) {
            if (e.keyCode == 13) {
                $('#sort_faqs').submit();
            }
        });

        // Filter change
        $('select[name="faq_type"], select[name="status"]').on('change', function() {
            $('#sort_faqs').submit();
        });
    });
</script>
@endsection
