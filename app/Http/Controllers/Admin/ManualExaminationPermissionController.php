<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarInspector;
use App\Models\CarInspectionType;
use App\Models\ManualExaminationPermission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManualExaminationPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_all_cars_inspections');
    }

    public function index(Request $request)
    {
        $search = (string) $request->get('search', '');

        $centers = CarInspector::query()
            ->with(['user', 'manualExaminationPermission', 'manualExaminationInspectionTypes:id'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('shop_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('shop_name')
            ->paginate(25)
            ->appends($request->query());

        $inspectionTypes = CarInspectionType::query()
            ->active()
            ->ordered()
            ->get(['id', 'name']);

        return view('backend.cars.manual-examinations.permissions', compact('centers', 'search', 'inspectionTypes'));
    }

    public function update(Request $request, CarInspector $center)
    {
        $validated = $request->validate([
            'can_manual_examination' => ['sometimes', 'boolean'],
            'inspection_type_ids' => ['sometimes', 'array'],
            'inspection_type_ids.*' => ['nullable', 'integer', Rule::exists('car_inspection_types', 'id')],
        ]);

        $hasPermissionUpdate = array_key_exists('can_manual_examination', $validated);
        $hasTypeUpdate = array_key_exists('inspection_type_ids', $validated);

        if (!$hasPermissionUpdate && !$hasTypeUpdate) {
            return response()->json([
                'error' => [
                    'message' => translate('No data provided to update'),
                    'code' => 'NO_UPDATE_FIELDS',
                ],
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        if ($hasPermissionUpdate) {
            ManualExaminationPermission::updateOrCreate(
                ['center_id' => $center->id],
                ['can_manual_examination' => (bool) $validated['can_manual_examination']]
            );
        }

        if ($hasTypeUpdate) {
            $typeIds = collect($validated['inspection_type_ids'] ?? [])
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            $center->manualExaminationInspectionTypes()->sync($typeIds);
        }

        $currentPermission = $hasPermissionUpdate
            ? (bool) $validated['can_manual_examination']
            : $center->manualExaminationPermission?->can_manual_examination ?? true;
        $currentTypeIds = $hasTypeUpdate
            ? collect($validated['inspection_type_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all()
            : $center->manualExaminationInspectionTypes()->pluck('car_inspection_types.id')->map(fn ($id) => (int) $id)->all();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => [
                    'center_id' => $center->id,
                    'can_manual_examination' => $currentPermission,
                    'inspection_type_ids' => $currentTypeIds,
                ],
                'message' => translate('Permission updated successfully'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        flash(translate('Permission updated successfully'))->success();
        return redirect()->back();
    }
}

