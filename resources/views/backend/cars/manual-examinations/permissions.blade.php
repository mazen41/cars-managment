@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 mb-0">{{ translate('Manual Examination Permissions') }}</h1>
            <div class="text-muted mt-1" style="font-size: 0.9rem;">
                {{ translate('Control which inspection centers can access manual examinations') }}
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
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }
    .perm-header {
        background: linear-gradient(180deg, rgba(255,255,255,1), rgba(248,250,252,1));
        border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    }
    .perm-table td, .perm-table th {
        vertical-align: middle !important;
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

    /* Save button — hidden by default, shown when toggle changes */
    .perm-save-btn {
        display: none;
        padding: 0.3rem 0.85rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        background: #0f172a;
        color: #fff;
        transition: background 0.15s, opacity 0.15s;
        white-space: nowrap;
    }
    .perm-save-btn:hover { background: #1e293b; }
    .perm-save-btn.visible { display: inline-flex; align-items: center; gap: 0.35rem; }
    .perm-save-btn:disabled { opacity: 0.55; cursor: not-allowed; }

    /* Subtle highlight on the row when there are unsaved changes */
    tr.perm-row-dirty {
        background: rgba(249, 250, 251, 0.85);
        outline: 1.5px solid rgba(99, 102, 241, 0.18);
        outline-offset: -1px;
    }
</style>

<div class="card perm-card">
    <div class="card-header perm-header">
        <form action="{{ route('admin.manual-examinations.permissions.index') }}" method="GET" class="w-100">
            <div class="row gutters-5 align-items-center">
                <div class="col">
                    <h5 class="mb-0 h6">{{ translate('Inspection Centers') }}</h5>
                </div>
                <div class="col-md-5">
                    <input
                        type="text"
                        class="form-control"
                        name="search"
                        value="{{ $search }}"
                        placeholder="{{ translate('Search by center name, phone, email, or user') }}"
                    />
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        <i class="las la-search mr-1"></i> {{ translate('Search') }}
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
                        <th>{{ translate('Center') }}</th>
                        <th>{{ translate('Contact') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th class="text-right">{{ translate('Enable Manual Examinations') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($centers as $center)
                        @php
                            $enabled = $center->manualExaminationPermission?->can_manual_examination ?? true;
                        @endphp
                        <tr data-center-id="{{ $center->id }}">
                            <td>
                                <div class="fw-700">{{ $center->shop_name ?? translate('N/A') }}</div>
                                <div class="perm-muted">
                                    {{ translate('Owner') }}: {{ $center->user?->name ?? translate('N/A') }}
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
                            <td class="text-right">
                                <div class="perm-switch">
                                    <button
                                        type="button"
                                        class="perm-save-btn"
                                        data-perm-save
                                        aria-label="{{ translate('Save permission') }}"
                                    >
                                        <i class="las la-save" style="font-size:1rem;"></i>
                                        {{ translate('Save') }}
                                    </button>
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input
                                            type="checkbox"
                                            data-perm-toggle
                                            data-original="{{ $enabled ? '1' : '0' }}"
                                            @checked($enabled)
                                            aria-label="{{ translate('Toggle manual examination permission') }}"
                                        >
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                {{ translate('No inspection centers found') }}
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
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const alertEl = document.getElementById('permAlert');

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
                label.textContent = enabled ? @json(translate('Enabled')) : @json(translate('Disabled'));
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

                if (saveBtn) {
                    saveBtn.classList.toggle('visible', isDirty);
                }
                row.classList.toggle('perm-row-dirty', isDirty);

                // Update badge preview so user sees the pending state
                updateBadgeUI(row, input.checked);
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

                row.classList.add('perm-row-saving');
                saveBtn.disabled = true;

                try {
                    const res = await fetch(
                        @json(url('/admin/manual-examinations/permissions')) + '/' + centerId,
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf || '',
                            },
                            body: JSON.stringify({ can_manual_examination: enabled ? 1 : 0 }),
                        }
                    );

                    if (!res.ok) {
                        const payload = await res.json().catch(() => null);
                        const msg = payload?.error?.message || payload?.message || @json(translate('Failed to update permission'));
                        throw new Error(msg);
                    }

                    // Commit: update original value, hide save button, remove dirty state
                    toggle.setAttribute('data-original', enabled ? '1' : '0');
                    saveBtn.classList.remove('visible');
                    row.classList.remove('perm-row-dirty');
                    updateBadgeUI(row, enabled);

                    showAlert('success', @json(translate('Permission updated successfully')));
                } catch (err) {
                    // Revert toggle to last-saved value
                    const original = toggle.getAttribute('data-original');
                    toggle.checked = original === '1';
                    updateBadgeUI(row, toggle.checked);

                    // Keep save button visible so user can retry
                    saveBtn.classList.add('visible');
                    row.classList.add('perm-row-dirty');

                    showAlert('danger', err?.message || @json(translate('Failed to update permission')));
                } finally {
                    row.classList.remove('perm-row-saving');
                    saveBtn.disabled = false;
                }
            });
        });
    })();
</script>
@endsection