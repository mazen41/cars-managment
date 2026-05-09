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
                <i class="las la-arrow-left mr-1"></i> {{ translate('Back to Manual Examinations') }}
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
    .perm-row-saving {
        opacity: 0.65;
        pointer-events: none;
    }
    .perm-types-wrap {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .perm-types-select {
        min-width: 260px;
        max-width: 360px;
        min-height: 110px;
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
    .perm-save-btn.is-dirty {
        background: linear-gradient(180deg, #2563eb, #1d4ed8);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
    }
    .perm-save-btn:disabled { opacity: 0.55; cursor: not-allowed; }
    .perm-cell-note {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.74rem;
        color: #64748b;
    }

    /* Subtle highlight on the row when there are unsaved changes */
    tr.perm-row-dirty {
        background: rgba(249, 250, 251, 0.85);
        outline: 1.5px solid rgba(99, 102, 241, 0.18);
        outline-offset: -1px;
    }
    .perm-unsaved-pill {
        display: none;
        margin-top: 0.35rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #1d4ed8;
        background: rgba(219, 234, 254, 0.8);
        border: 1px solid rgba(147, 197, 253, 0.9);
        border-radius: 999px;
        padding: 0.14rem 0.52rem;
    }
    tr.perm-row-dirty .perm-unsaved-pill {
        display: inline-block;
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
                                <div class="fw-700">{{ $center->shop_name ?? translate('N/A') }}</div>
                                <div class="perm-muted">
                                    {{ __('manual_examinations.owner_label') }}: {{ $center->user?->name ?? translate('N/A') }}
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
                                        {{ $enabled ? translate('Enabled') : translate('Disabled') }}
                                    </span>
                                </span>
                            </td>
                            <td>
                                <div class="perm-types-wrap">
                                    <button
                                        type="button"
                                        class="perm-save-btn"
                                        data-types-save
                                        aria-label="{{ __('manual_examinations.save_types') }}"
                                    >
                                        <i class="las la-save" style="font-size:1rem;"></i>
                                        {{ __('manual_examinations.save_button') }}
                                    </button>
                                    <select
                                        class="form-control perm-types-select"
                                        multiple
                                        size="4"
                                        data-types-select
                                        data-original='@json($selectedTypeIds)'
                                        aria-label="{{ __('manual_examinations.assign_types') }}"
                                    >
                                        @foreach($inspectionTypes as $type)
                                            <option value="{{ $type->id }}" @selected(in_array((int) $type->id, $selectedTypeIds, true))>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="perm-cell-note">{{ __('manual_examinations.types_help') }}</span>
                                <span class="perm-unsaved-pill">{{ __('manual_examinations.unsaved_changes') }}</span>
                            </td>
                            <td class="text-right">
                                <div class="perm-switch">
                                    <button
                                        type="button"
                                        class="perm-save-btn"
                                        data-perm-save
                                        aria-label="{{ __('manual_examinations.save_permission') }}"
                                    >
                                        <i class="las la-save" style="font-size:1rem;"></i>
                                        {{ __('manual_examinations.save_button') }}
                                    </button>
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input
                                            type="checkbox"
                                            data-perm-toggle
                                            data-original="{{ $enabled ? '1' : '0' }}"
                                            @checked($enabled)
                                            aria-label="{{ __('manual_examinations.toggle_permission') }}"
                                        >
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <span class="perm-cell-note">{{ __('manual_examinations.toggle_help') }}</span>
                                <span class="perm-unsaved-pill">{{ __('manual_examinations.unsaved_changes') }}</span>
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
        window.addEventListener('unhandledrejection', function (event) {
            const message = String(event?.reason?.message || event?.reason || '');
            if (message.includes("We weren't granted permission.")) {
                event.preventDefault();
            }
        });

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const alertEl = document.getElementById('permAlert');

        const MSG_ENABLED = @json(__('manual_examinations.enabled'));
        const MSG_DISABLED = @json(__('manual_examinations.disabled'));
        const MSG_SAVE_SUCCESS = @json(__('manual_examinations.save_success'));
        const MSG_SAVE_FAILED = @json(__('manual_examinations.save_failed'));

        function showAlert(type, message) {
            if (!alertEl) return;
            alertEl.className = `alert perm-alert show alert-${type}`;
            alertEl.textContent = message;
            window.clearTimeout(showAlert._t);
            showAlert._t = window.setTimeout(() => {
                alertEl.classList.remove('show');
            }, 3500);
        }

        function updateBadgeUI(row, enabled) {
            const badge = row.querySelector('[data-perm-badge]');
            const label = row.querySelector('[data-perm-label]');
            if (badge) {
                badge.classList.toggle('is-enabled', !!enabled);
                badge.classList.toggle('is-disabled', !enabled);
            }
            if (label) {
                label.textContent = enabled ? MSG_ENABLED : MSG_DISABLED;
            }
        }

        function normalizeTypeIds(ids) {
            return Array.from(new Set((ids || []).map((id) => Number(id)).filter((id) => Number.isInteger(id))))
                .sort((a, b) => a - b);
        }

        function readSelectedTypeIds(select) {
            return normalizeTypeIds(Array.from(select.selectedOptions || []).map((opt) => Number(opt.value)));
        }

        function getRowUpdateUrl(row) {
            return row.getAttribute('data-update-url') || '';
        }

        function getPermissionDirty(row) {
            const toggle = row.querySelector('[data-perm-toggle]');
            if (!toggle) return false;
            return (toggle.checked ? '1' : '0') !== (toggle.getAttribute('data-original') || '0');
        }

        function getTypesDirty(row) {
            const select = row.querySelector('[data-types-select]');
            if (!select) return false;
            const original = normalizeTypeIds(JSON.parse(select.getAttribute('data-original') || '[]'));
            const selected = readSelectedTypeIds(select);
            return JSON.stringify(selected) !== JSON.stringify(original);
        }

        function refreshRowDirtyState(row) {
            const permDirty = getPermissionDirty(row);
            const typesDirty = getTypesDirty(row);
            row.classList.toggle('perm-row-dirty', permDirty || typesDirty);

            const permBtn = row.querySelector('[data-perm-save]');
            if (permBtn) {
                permBtn.disabled = !permDirty;
                permBtn.classList.toggle('is-dirty', permDirty);
                permBtn.classList.add('visible');
            }

            const typesBtn = row.querySelector('[data-types-save]');
            if (typesBtn) {
                typesBtn.disabled = !typesDirty;
                typesBtn.classList.toggle('is-dirty', typesDirty);
                typesBtn.classList.add('visible');
            }
        }

        // When toggle changes: show Save button, mark row dirty (no request yet)
        document.querySelectorAll('[data-perm-toggle]').forEach((toggle) => {
            toggle.addEventListener('change', (e) => {
                const input = e.currentTarget;
                const row = input.closest('tr');
                if (!row) return;

                const saveBtn = row.querySelector('[data-perm-save]');
                const original = input.getAttribute('data-original');
                const isDirty = (input.checked ? '1' : '0') !== original;

                if (saveBtn) saveBtn.disabled = !isDirty;

                // Update badge preview so user sees the pending state
                updateBadgeUI(row, input.checked);
                refreshRowDirtyState(row);
            });
        });

        // Save button click: send the request
        document.querySelectorAll('[data-perm-save]').forEach((saveBtn) => {
            saveBtn.addEventListener('click', async () => {
                const row = saveBtn.closest('tr');
                if (!row) return;

                const toggle = row.querySelector('[data-perm-toggle]');
                if (!toggle) return;

                const centerId = row.getAttribute('data-center-id');
                const enabled = !!toggle.checked;
                const updateUrl = getRowUpdateUrl(row);
                if (!updateUrl) return;

                row.classList.add('perm-row-saving');
                saveBtn.disabled = true;

                try {
                    const res = await fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf || '',
                        },
                        body: JSON.stringify({ can_manual_examination: enabled ? 1 : 0 }),
                    });

                    if (!res.ok) {
                        const payload = await res.json().catch(() => null);
                        const msg = payload?.error?.message || payload?.message || @json(translate('Failed to update permission'));
                        throw new Error(msg);
                    }

                    // Commit: update original value, hide save button, remove dirty state
                    toggle.setAttribute('data-original', enabled ? '1' : '0');
                    updateBadgeUI(row, enabled);
                    refreshRowDirtyState(row);

                    showAlert('success', MSG_SAVE_SUCCESS);
                } catch (err) {
                    // Revert toggle to last-saved value
                    const original = toggle.getAttribute('data-original');
                    toggle.checked = original === '1';
                    updateBadgeUI(row, toggle.checked);

                    // Keep save button visible so user can retry
                    refreshRowDirtyState(row);

                    showAlert('danger', err?.message || MSG_SAVE_FAILED);
                } finally {
                    row.classList.remove('perm-row-saving');
                    saveBtn.disabled = false;
                }
            });
        });

        document.querySelectorAll('[data-types-select]').forEach((select) => {
            select.addEventListener('change', (e) => {
                const input = e.currentTarget;
                const row = input.closest('tr');
                if (!row) return;

                const saveBtn = row.querySelector('[data-types-save]');
                if (!saveBtn) return;

                const original = normalizeTypeIds(JSON.parse(input.getAttribute('data-original') || '[]'));
                const selected = readSelectedTypeIds(input);
                const isDirty = JSON.stringify(selected) !== JSON.stringify(original);

                saveBtn.disabled = !isDirty;
                refreshRowDirtyState(row);
            });
        });

        document.querySelectorAll('[data-types-save]').forEach((saveBtn) => {
            saveBtn.addEventListener('click', async () => {
                const row = saveBtn.closest('tr');
                if (!row) return;

                const select = row.querySelector('[data-types-select]');
                if (!select) return;

                const selectedTypeIds = readSelectedTypeIds(select);
                const updateUrl = getRowUpdateUrl(row);
                if (!updateUrl) {
                    showAlert('danger', MSG_SAVE_FAILED);
                    return;
                }

                row.classList.add('perm-row-saving');
                saveBtn.disabled = true;

                try {
                    const res = await fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf || '',
                        },
                        body: JSON.stringify({ inspection_type_ids: selectedTypeIds }),
                    });

                    if (!res.ok) {
                        const payload = await res.json().catch(() => null);
                        const msg = payload?.error?.message || payload?.message || @json(translate('Failed to update permission'));
                        throw new Error(msg);
                    }

                    select.setAttribute('data-original', JSON.stringify(selectedTypeIds));
                    refreshRowDirtyState(row);
                    showAlert('success', MSG_SAVE_SUCCESS);
                } catch (err) {
                    const original = normalizeTypeIds(JSON.parse(select.getAttribute('data-original') || '[]'));
                    Array.from(select.options).forEach((option) => {
                        option.selected = original.includes(Number(option.value));
                    });
                    refreshRowDirtyState(row);
                    showAlert('danger', err?.message || MSG_SAVE_FAILED);
                } finally {
                    row.classList.remove('perm-row-saving');
                    saveBtn.disabled = false;
                }
            });
        });

        document.querySelectorAll('.perm-table tbody tr[data-center-id]').forEach((row) => {
            refreshRowDirtyState(row);
        });
    })();
</script>
@endsection