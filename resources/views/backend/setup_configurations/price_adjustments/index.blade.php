@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('All Price Adjustments')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('price_adjustment.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add New Price Adjustment')}}</span>
            </a>
        </div>
    </div>
</div>
<br>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Price Adjustments')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Label')}}</th>
                    <th data-breakpoints="md">{{translate('Type')}}</th>
                    <th data-breakpoints="md">{{translate('Amount')}}</th>
                    <th data-breakpoints="md">{{translate('Amount Type')}}</th>
                    <th data-breakpoints="md">{{translate('Provider')}}</th>
                    <th data-breakpoints="md">{{translate('Base Calculation')}}</th>
                    <th data-breakpoints="md">{{translate('Status')}}</th>
                    <th class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adjustments as $key => $adjustment)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $adjustment->getTranslation('label') }}</td>
                    <td>{{ ucfirst(translate($adjustment->type)) }}</td>
                    <td>{{ $adjustment->amount }}</td>
                    <td>{{ ucfirst(translate($adjustment->amount_type)) }}</td>
                    <td>{{ translate($adjustment->provider )}}</td>
                    <td>{{ ucwords(str_replace('_', ' ', translate( $adjustment->base_calculation))) }}</td>
                    <td>
                        @if (!$adjustment->is_commission)

                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" onchange="update_status(this)" value="{{ $adjustment->id }}" <?php
                                    if($adjustment->is_active == 1) echo "checked";?>>
                                <span class="slider round"></span>
                            </label>

                        @else
                            <span class="badge badge-inline badge-success"> {{translate('On')}}</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                            href="{{ route('price_adjustment.edit', $adjustment->id )}}"
                            title="{{ translate('Edit') }}">
                            <i class="las la-edit"></i>
                        </a>
                        @if (!$adjustment->is_commission)
                        <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                            data-href="{{ route('price_adjustment.destroy', $adjustment->id) }}"
                            title="{{ translate('Delete') }}">
                            <i class="las la-trash"></i>
                        </a>
                        @endif
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
<script type="text/javascript">
    function update_status(el){
    if(el.checked){
        var status = 1;
    }
    else{
        var status = 0;
    }
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: '{{ route('price_adjustment.update_status') }}',
        type: 'POST',
        data: {
            id: el.value,
            status: status
        },
        success: function(response) {
            if(response == 1){
                AIZ.plugins.notify('success', '{{ translate('Status updated successfully') }}');
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                // Revert the toggle if update failed
                el.checked = !el.checked;
            }
        },
        error: function() {
            AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            // Revert the toggle if update failed
            el.checked = !el.checked;
        }
    });
}

</script>
@endsection
