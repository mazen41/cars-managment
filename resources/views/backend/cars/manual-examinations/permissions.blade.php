@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 mb-0">{{ __('manual_examinations.permissions_title') }}</h1>
            <div class="text-muted mt-1" style="font-size: 0.9rem;">
                {{ __('manual_examinations.permissions_subtitle') }}
            </div>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.manual-examinations.index') }}" class="btn btn-soft-primary">
                <i class="las la-arrow-left mr-1"></i> {{ __('manual_examinations.back_to_manual') }}
            </a>
        </div>
    </div>
</div>

<style>
    .perm-card {
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
    }
    .perm-header {
        background: linear-gradient(180deg, rgba(255,255,255,1), rgba(248,250,252,1));
        border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    }
    .perm-table td, .perm-table th {
        vertical-align: middle !important;
    }
    .perm-table thead th {
        background: #f8fafc;
        color: #0f172a;
        font-weight: 700;
        border-top: none !important;
    }
    .perm-table tbody tr:nth-child(even) {
        background: rgba(248, 250, 252, 0.55);
    }
    .perm-badge {
        border-radius: 999px;
        padding: 0.35rem 0.65rem;
        font-weight: 700;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: rgba(241, 245, 249, 0.75);
        color: #0f172a;
        white-space: nowrap;
    }
    .perm-badge.is-enabled {
        background: rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.35);
        color: #065f46;
    }
    .perm-badge.is-disabled {
        background: rgba(239, 68, 68, 0.10);
        border-color: rgba(239, 68, 68, 0.30);
        color: #7f1d1d;
    }
    .perm-switch {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        justify-content: flex-end;
    }
    .perm-toggle-actions {
        display: inline-flex;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }
    .perm-toggle-btn {
        border: none;
        background: transparent;
        color: #334155;
        padding: 0.32rem 0.72rem;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
    }
    .perm-toggle-btn + .perm-toggle-btn {
        border-left: 1px solid #e2e8f0;
    }
    .perm-toggle-btn.is-active[data-value="1"] {
        background: rgba(16, 185, 129, 0.14);
        color: #065f46;
    }
    .perm-toggle-btn.is-active[data-value="0"] {
        background: rgba(239, 68, 68, 0.12);
        color: #7f1d1d;
    }
    .perm-radio-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .perm-radio-input:checked + .perm-toggle-btn[data-value="1"] {
        background: rgba(16, 185, 129, 0.14);
        color: #065f46;
    }
    .perm-radio-input:checked + .perm-toggle-btn[data-value="0"] {
        background: rgba(239, 68, 68, 0.12);
        color: #7f1d1d;
    }
    .perm-alert {
        display: none;
        margin-bottom: 0.75rem;
        border-radius: 12px;
    }
    .perm-alert.show { display: block; }
    .perm-muted {
        font-size: 0.82rem;
        color: #64748b;
    }
    .perm-row-saving { opacity: 0.65; pointer-events: none; }
    .perm-types-wrap {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .perm-types-select {
        min-width: 260px;
        max-width: 360px;
        min-height: 120px;
        border-radius: 10px;
        border-color: #cbd5e1;
        background: #fff;
    }
    .perm-types-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.15rem rgba(37, 99, 235, 0.12);
    }

    .perm-save-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.38rem 0.95rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        background: linear-gradient(180deg, #1e293b, #0f172a);
        color: #fff;
        transition: background 0.15s, opacity 0.15s, transform 0.12s ease;
        white-space: nowrap;
    }
    .perm-save-btn:hover { background: linear-gradient(180deg, #334155, #1e293b); }
    .perm-save-btn:active { transform: translateY(1px); }
    .perm-save-btn.visible { display: inline-flex; }
    .perm-save-btn:disabled { opacity: 0.55; cursor: not-allowed; }
    .perm-cell-note {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.74rem;
        color: #64748b;
    }

    .perm-types-wrap form,
    .perm-switch form {
        margin: 0;
    }
</style>

<div class="card perm-card">
    <div class="card-header perm-header">
        <form action="{{ route('admin.manual-examinations.permissions.index') }}" method="GET" class="w-100">
            <div class="row gutters-5 align-items-center">
                <div class="col">
            <h5 class="mb-0 h6">{{ __('manual_examinations.inspection_centers') }}</h5>
                </div>
                <div class="col-md-5">
                    <input
                        type="text"
                        class="form-control"
                        name="search"
                        value="{{ $search }}"
                        placeholder="{{ __('manual_examinations.search_placeholder') }}"
                    />
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        <i class="las la-search mr-1"></i> {{ __('manual_examinations.search_button') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div id="permAlert" class="alert perm-alert" role="alert"></div>

        <div class="table-responsive">
            <table class="table aiz-table mb-0 perm-table">
                <thead>
                    <tr>
                        <th>{{ __('manual_examinations.col_center') }}</th>
                        <th>{{ __('manual_examinations.col_contact') }}</th>
                        <th>{{ __('manual_examinations.col_status') }}</th>
                        <th>{{ __('manual_examinations.col_allowed_types') }}</th>
                        <th class="text-right">{{ __('manual_examinations.col_enable_manual') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($centers as $center)
                        @php
                            $enabled = $center->manualExaminationPermission?->can_manual_examination ?? true;
                            $selectedTypeIds = $center->manualExaminationInspectionTypes->pluck('id')->map(fn($id) => (int) $id)->values()->all();
                        @endphp
                        <tr
                            data-center-id="{{ $center->id }}"
                            data-update-url="{{ route('admin.manual-examinations.permissions.update', ['center' => $center->id]) }}"
                        >
                            <td>
                                <div class="fw-700">{{ $center->shop_name ?? __('manual_examinations.na') }}</div>
                                <div class="perm-muted">
                                    {{ __('manual_examinations.owner_label') }}: {{ $center->user?->name ?? __('manual_examinations.na') }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-600">{{ $center->phone ?? '-' }}</div>
                                <div class="perm-muted">{{ $center->email ?? $center->user?->email ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="perm-badge {{ $enabled ? 'is-enabled' : 'is-disabled' }}" data-perm-badge>
                                    <span aria-hidden="true" style="font-size: 0.95rem;">●</span>
                                    <span data-perm-label>
                                        {{ $enabled ? __('manual_examinations.enabled') : __('manual_examinations.disabled') }}
                                    </span>
                                </span>
                            </td>
                            <td>
                                <form
                                    method="POST"
                                    action="{{ route('admin.manual-examinations.permissions.update', ['center' => $center->id]) }}"
                                    class="perm-types-wrap"
                                >
                                    @csrf
                                    <input type="hidden" name="inspection_type_ids[]" value="">
                                    <select
                                        class="form-control perm-types-select"
                                        name="inspection_type_ids[]"
                                        multiple
                                        size="5"
                                        aria-label="{{ __('manual_examinations.assign_types') }}"
                                    >
                                        @foreach($inspectionTypes as $type)
                                            <option value="{{ $type->id }}" @selected(in_array((int) $type->id, $selectedTypeIds, true))>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button
                                        type="submit"
                                        class="perm-save-btn"
                                        aria-label="{{ __('manual_examinations.save_types') }}"
                                    >
                                        <i class="las la-save" style="font-size:1rem;"></i>
                                        {{ __('manual_examinations.save_button') }}
                                    </button>
                                </form>
                                <span class="perm-cell-note">{{ __('manual_examinations.types_help') }}</span>
                            </td>
                            <td class="text-right">
                                <form
                                    method="POST"
                                    action="{{ route('admin.manual-examinations.permissions.update', ['center' => $center->id]) }}"
                                    class="perm-switch"
                                >
                                    @csrf
                                    <div class="perm-toggle-actions" role="group" aria-label="{{ __('manual_examinations.toggle_permission') }}">
                                        <input
                                            id="perm-enable-{{ $center->id }}"
                                            class="perm-radio-input"
                                            type="radio"
                                            name="can_manual_examination"
                                            value="1"
                                            @checked($enabled)
                                        >
                                        <label class="perm-toggle-btn" data-value="1" for="perm-enable-{{ $center->id }}">
                                            {{ __('manual_examinations.enabled') }}
                                        </label>
                                        <input
                                            id="perm-disable-{{ $center->id }}"
                                            class="perm-radio-input"
                                            type="radio"
                                            name="can_manual_examination"
                                            value="0"
                                            @checked(!$enabled)
                                        >
                                        <label class="perm-toggle-btn" data-value="0" for="perm-disable-{{ $center->id }}">
                                            {{ __('manual_examinations.disabled') }}
                                        </label>
                                    </div>
                                    <button
                                        type="submit"
                                        class="perm-save-btn"
                                        aria-label="{{ __('manual_examinations.save_permission') }}"
                                    >
                                        <i class="las la-save" style="font-size:1rem;"></i>
                                        {{ __('manual_examinations.save_button') }}
                                    </button>
                                </form>
                                <span class="perm-cell-note">{{ __('manual_examinations.toggle_help') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                {{ __('manual_examinations.no_centers') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="aiz-pagination mt-3">
            {{ $centers->links() }}
        </div>
    </div>
</div>
@endsection
