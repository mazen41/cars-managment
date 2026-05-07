@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Manual Examinations') }}</h1>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_manual_examinations" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-0 h6">{{ translate('Manual Examinations') }}</h5>
            </div>
            <div class="col-md-2">
                <select class="form-control aiz-selectpicker" name="status" onchange="sort_manual_examinations()">
                    <option value="">{{ translate('Filter by status') }}</option>
                    @foreach($statuses as $key => $status)
                        <option value="{{ $key }}" @selected(request('status') == $key)>{{ translate($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control aiz-selectpicker" name="inspector" data-live-search="true" onchange="sort_manual_examinations()">
                    <option value="">{{ translate('Filter by inspector') }}</option>
                    @foreach($inspectors as $inspector)
                        <option value="{{ $inspector->id }}" @selected(request('inspector') == $inspector->id)>
                            {{ $inspector->shop_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset value="{{ request('search') }}" placeholder="{{ translate('Search') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
            </div>
        </div>
    </form>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>{{ translate('Inspector') }}</th>
                    <th>{{ translate('Car Info') }}</th>
                    <th>{{ translate('Plate Number') }}</th>
                    <th>{{ translate('Date Created') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th class="text-right">{{ translate('Options') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($manualExaminations as $manualExamination)
                    <tr>
                        <td>
                            <div class="fw-600">{{ $manualExamination->inspector->shop_name ?? translate('N/A') }}</div>
                            <small class="text-muted">{{ $manualExamination->inspector->user->name ?? '' }}</small>
                        </td>
                        <td>
                            <div class="fw-600">
                                {{ $manualExamination->car->brand->name ?? translate('N/A') }}
                                {{ $manualExamination->car->model->name ?? '' }}
                                @if($manualExamination->car?->manufacture_year)
                                    ({{ $manualExamination->car->manufacture_year }})
                                @endif
                            </div>
                            <small class="text-muted">{{ translate('VIN') }}: {{ $manualExamination->car->vin ?? translate('N/A') }}</small>
                        </td>
                        <td>{{ $manualExamination->car->plate_number ?? translate('N/A') }}</td>
                        <td>{{ $manualExamination->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{!! $manualExamination->status_badge !!}</td>
                        <td class="text-right">
                            @if($manualExamination->status === 'pending')
                                <a class="btn btn-soft-warning btn-icon btn-circle btn-sm"
                                   href="{{ route('admin.manual-examinations.schedule', $manualExamination->id) }}"
                                   title="{{ translate('Schedule') }}">
                                    <i class="las la-calendar"></i>
                                </a>
                            @endif
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('admin.manual-examinations.show', $manualExamination->id) }}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ translate('No manual examinations found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="aiz-pagination">
            {{ $manualExaminations->links() }}
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    function sort_manual_examinations() {
        $('#sort_manual_examinations').submit();
    }
</script>
@endsection
