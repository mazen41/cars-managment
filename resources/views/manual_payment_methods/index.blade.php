@extends('backend.layouts.app')

@section('content')

@can('add_manual_payment_method')
    <div class="aiz-titlebar mt-2 mb-3">
        <div class="text-md-right">
            <a href="{{ route('manual_payment_methods.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add New Payment Method')}}</span>
            </a>
    </div>
    </div>
@endcan

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Manual Payment Method')}}</h5>
        <label class="aiz-switch aiz-switch-success mb-0 float-right">
            <input type="checkbox" onchange="updateSettings(this, 'manual_payment')" @if (get_setting('manual_payment') == 1) checked @endif>
            <span class="slider round"></span>
        </label>
    </div>
    <div class="card-body">
        <table class="table aiz-table" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Logo')}}</th>
                    <th width="10%" class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($manual_payment_methods as $key => $manual_payment_method)
                    <tr>
                        <td>{{ ($key+1) }}</td>
                        <td>{{ $manual_payment_method->name }}</td>
                        <td><img class="w-50px" src="{{ uploaded_asset($manual_payment_method->photo) }}" alt="Logo"></td>
                        <td class="text-right">
                            @can('edit_manual_payment_method')
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('manual_payment_methods.edit', encrypt($manual_payment_method->id))}}" title="{{ translate('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                            @endcan
                            @can('delete_manual_payment_method')
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('manual_payment_methods.destroy', $manual_payment_method->id)}}" title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
@section('script')
<script>
    function updateSettings(el, type) {
                if ($(el).is(':checked')) {
                    var value = 1;
                } else {
                    var value = 0;
                }

                $.post('{{ route('business_settings.update.activation') }}', {
                    _token: '{{ csrf_token() }}',
                    type: type,
                    value: value
                }, function(data) {
                    if (data == 1) {
                        AIZ.plugins.notify('success', '{{ translate('Settings updated successfully') }}');
                    } else {
                        AIZ.plugins.notify('danger', 'Something went wrong');
                    }
                });
            }
</script>
@endsection
