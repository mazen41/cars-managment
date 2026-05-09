@extends('backend.layouts.app')

@section('content')
<style>
    .me-titlebar {
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 16px;
        background: linear-gradient(180deg, rgba(255,255,255,1), rgba(248,250,252,1));
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        padding: 1rem 1rem;
    }
    .me-subtitle {
        color: #64748b;
        font-size: 0.9rem;
    }
    .me-card {
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }
    .me-card .card-header {
        background: linear-gradient(180deg, rgba(255,255,255,1), rgba(248,250,252,1));
        border-bottom: 1px solid rgba(148, 163, 184, 0.18);
    }
    .me-table th {
        font-weight: 800;
        color: #0f172a;
        white-space: nowrap;
    }
    .me-table td {
        vertical-align: middle !important;
    }
    .me-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .65rem;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: rgba(241, 245, 249, 0.7);
        color: #0f172a;
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .me-muted {
        color: #64748b;
        font-size: .82rem;
    }
</style>

<div class="me-titlebar mb-3">
    <div class="row align-items-center">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="mr-2">
                    <i class="las la-clipboard-check" style="font-size: 1.7rem; color: #2563eb;"></i>
                </div>
                <div>
                    <h1 class="h3 mb-0">{{ translate('Manual Examinations') }}</h1>
                    <div class="me-subtitle mt-1">
                        {{ translate('Review manual examination reports and download PDFs') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.manual-examinations.permissions.index') }}" class="btn btn-soft-primary">
                <i class="las la-user-shield mr-1"></i> {{ translate('Permissions') }}
            </a>
        </div>
    </div>
</div>

<div class="card me-card">
    <form id="sort_manual_examinations" action="" method="GET">
        <div class="card-header">
            <div class="row gutters-5 align-items-center">
                <div class="col">
                    <h5 class="mb-0 h6">{{ translate('Filters') }}</h5>
                    <div class="me-muted mt-1">{{ translate('Use filters to quickly find a report') }}</div>
                </div>
                <div class="col-md-2">
                    <select class="form-control aiz-selectpicker" name="status" onchange="sort_manual_examinations()">
                        <option value="">{{ translate('All statuses') }}</option>
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}" @selected(request('status') == $key)>{{ translate($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control aiz-selectpicker" name="inspector" data-live-search="true" onchange="sort_manual_examinations()">
                        <option value="">{{ translate('All inspectors') }}</option>
                        @foreach($inspectors as $inspector)
                            <option value="{{ $inspector->id }}" @selected(request('inspector') == $inspector->id)>
                                {{ $inspector->shop_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ translate('Search by VIN, plate, inspector, or number') }}"
                    >
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-filter mr-1"></i> {{ translate('Apply') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table aiz-table mb-0 me-table">
                <thead>
                    <tr>
                        <th>{{ translate('Inspection Center') }}</th>
                        <th>{{ translate('Car') }}</th>
                        <th>{{ translate('VIN / Plate') }}</th>
                        <th>{{ translate('Created') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th class="text-right">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($manualExaminations as $manualExamination)
                        <tr>
                            <td>
                                <div class="fw-700">{{ $manualExamination->inspector->shop_name ?? translate('N/A') }}</div>
                                <div class="me-muted">{{ $manualExamination->inspector->user->name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-700">
                                    {{ $manualExamination->car->brand->name ?? translate('N/A') }}
                                    {{ $manualExamination->car->model->name ?? '' }}
                                    @if($manualExamination->car?->manufacture_year)
                                        <span class="me-muted">({{ $manualExamination->car->manufacture_year }})</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="me-pill">
                                    <span>{{ translate('VIN') }}:</span>
                                    <span dir="ltr">{{ $manualExamination->car->vin ?? '-' }}</span>
                                </div>
                                <div class="me-muted mt-1">
                                    {{ translate('Plate') }}:
                                    <span dir="ltr">{{ $manualExamination->car->plate_number ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-600">{{ $manualExamination->created_at?->format('Y-m-d') }}</div>
                                <div class="me-muted">{{ $manualExamination->created_at?->format('H:i') }}</div>
                            </td>
                            <td>{!! $manualExamination->status_badge !!}</td>
                            <td class="text-right">
                                <a class="btn btn-soft-primary btn-sm" href="{{ route('admin.manual-examinations.show', $manualExamination->id) }}" title="{{ translate('View') }}">
                                    <i class="las la-eye mr-1"></i> {{ translate('View') }}
                                </a>
                                <a class="btn btn-soft-success btn-sm" href="{{ route('admin.manual-examinations.download', $manualExamination->id) }}" title="{{ translate('Download PDF') }}">
                                    <i class="las la-file-download mr-1"></i> {{ translate('PDF') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ translate('No manual examinations found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="aiz-pagination mt-3">
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
