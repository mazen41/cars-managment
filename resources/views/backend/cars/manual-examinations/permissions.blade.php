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
    .perm-types-col {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        min-width: 220px;
        max-width: 340px;
    }
    .perm-type-add-select {
        min-height: 38px;
        border-radius: 10px;
        border-color: #cbd5e1;
        background: #fff;
        font-size: 0.85rem;
    }
    .perm-type-add-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.15rem rgba(37, 99, 235, 0.12);
    }
    .perm-types-chips {
        min-height: 52px;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 0.45rem;
        background: #f8fafc;
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        align-items: flex-start;
        align-content: flex-start;
    }
    .perm-types-chips-empty {
        width: 100%;
        font-size: 0.72rem;
        color: #94a3b8;
        text-align: center;
        padding: 0.35rem 0.25rem;
        margin: 0;
    }
    .perm-types-chips:not(:empty) + .perm-types-chips-empty {
        display: none !important;
    }
    .perm-type-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.2rem 0.35rem 0.2rem 0.5rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: #0f172a;
        max-width: 100%;
    }
    .perm-type-chip-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 200px;
    }
    .perm-type-remove {
        border: none;
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
        cursor: pointer;
        padding: 0.1rem 0.35rem;
        border-radius: 6px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .perm-type-remove:hover {
        background: rgba(239, 68, 68, 0.2);
        color: #991b1b;
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
                            $selectedTypeIdsValid = collect($selectedTypeIds)
                                ->filter(fn ($id) => $inspectionTypes->contains('id', $id))
                                ->values()
                                ->all();
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
                                    class="perm-types-wrap perm-types-form"
                                >
                                    @csrf
                                    <div class="perm-types-col">
                                        <select
                                            class="form-control perm-type-add-select"
                                            data-type-picker
                                            aria-label="{{ __('manual_examinations.select_type') }}"
                                        >
                                            <option value="">{{ __('manual_examinations.select_type') }}</option>
                                            @foreach($inspectionTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        <div
                                            class="perm-types-chips {{ count($selectedTypeIdsValid) ? 'has-chips' : '' }}"
                                            data-chips
                                            aria-label="{{ __('manual_examinations.assign_types') }}"
                                        >
                                            @foreach($selectedTypeIdsValid as $tid)
                                                @php $typeRow = $inspectionTypes->firstWhere('id', $tid); @endphp
                                                @if($typeRow)
                                                    <span class="perm-type-chip" data-type-id="{{ (int) $tid }}">
                                                        <span class="perm-type-chip-text">{{ $typeRow->name }}</span>
                                                        <button
                                                            type="button"
                                                            class="perm-type-remove"
                                                            aria-label="{{ __('manual_examinations.remove_type') }}"
                                                            title="{{ __('manual_examinations.remove_type') }}"
                                                        >
                                                            <i class="las la-times"></i>
                                                        </button>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                        <p
                                            class="perm-types-chips-empty mb-0"
                                            data-chips-empty
                                            style="{{ count($selectedTypeIdsValid) ? 'display:none' : '' }}"
                                        >
                                            {{ __('manual_examinations.types_chips_empty') }}
                                        </p>
                                        <div class="perm-type-hidden-inputs" data-hidden-wrap>
                                            @forelse($selectedTypeIdsValid as $tid)
                                                <input type="hidden" name="inspection_type_ids[]" value="{{ (int) $tid }}">
                                            @empty
                                                <input type="hidden" name="inspection_type_ids[]" value="">
                                            @endforelse
                                        </div>
                                    </div>
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

@section('script')
<script>
(function () {
    var REMOVE_LABEL = @json(__('manual_examinations.remove_type'));

    function escapeHtml(s) {
        if (!s) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function syncChipsForm(form) {
        var chipsBox = form.querySelector('[data-chips]');
        var hiddenWrap = form.querySelector('[data-hidden-wrap]');
        var emptyHint = form.querySelector('[data-chips-empty]');
        if (!chipsBox || !hiddenWrap) return;

        var chips = chipsBox.querySelectorAll('.perm-type-chip');
        hiddenWrap.innerHTML = '';
        if (chips.length === 0) {
            var h0 = document.createElement('input');
            h0.type = 'hidden';
            h0.name = 'inspection_type_ids[]';
            h0.value = '';
            hiddenWrap.appendChild(h0);
            chipsBox.classList.remove('has-chips');
            if (emptyHint) emptyHint.style.display = '';
        } else {
            Array.prototype.forEach.call(chips, function (chip) {
                var id = chip.getAttribute('data-type-id');
                if (!id) return;
                var h = document.createElement('input');
                h.type = 'hidden';
                h.name = 'inspection_type_ids[]';
                h.value = id;
                hiddenWrap.appendChild(h);
            });
            chipsBox.classList.add('has-chips');
            if (emptyHint) emptyHint.style.display = 'none';
        }
    }

    document.querySelectorAll('.perm-types-form').forEach(function (form) {
        var picker = form.querySelector('[data-type-picker]');
        var chipsBox = form.querySelector('[data-chips]');
        if (!picker || !chipsBox) return;

        picker.addEventListener('change', function () {
            var val = picker.value;
            if (!val) return;
            if (chipsBox.querySelector('.perm-type-chip[data-type-id="' + val + '"]')) {
                picker.selectedIndex = 0;
                return;
            }
            var opt = picker.options[picker.selectedIndex];
            var label = opt ? opt.textContent.trim() : '';
            var span = document.createElement('span');
            span.className = 'perm-type-chip';
            span.setAttribute('data-type-id', val);
            span.innerHTML =
                '<span class="perm-type-chip-text">' + escapeHtml(label) + '</span>' +
                '<button type="button" class="perm-type-remove" aria-label="' + escapeHtml(REMOVE_LABEL) + '" title="' + escapeHtml(REMOVE_LABEL) + '">' +
                '<i class="las la-times"></i></button>';
            chipsBox.appendChild(span);
            picker.selectedIndex = 0;
            syncChipsForm(form);
        });

        chipsBox.addEventListener('click', function (e) {
            var btn = e.target.closest('.perm-type-remove');
            if (!btn) return;
            var chip = btn.closest('.perm-type-chip');
            if (chip) chip.remove();
            syncChipsForm(form);
        });
    });
})();
</script>
@endsection
