<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarInspector;
use App\Models\ManualExaminationPermission;
use Illuminate\Http\Request;

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
            ->with(['user', 'manualExaminationPermission'])
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

        return view('backend.cars.manual-examinations.permissions', compact('centers', 'search'));
    }

    public function update(Request $request, CarInspector $center)
    {
        $validated = $request->validate([
            'can_manual_examination' => ['required', 'boolean'],
        ]);

        ManualExaminationPermission::updateOrCreate(
            ['center_id' => $center->id],
            ['can_manual_examination' => (bool) $validated['can_manual_examination']]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'data' => [
                    'center_id' => $center->id,
                    'can_manual_examination' => (bool) $validated['can_manual_examination'],
                ],
                'message' => translate('Permission updated successfully'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        flash(translate('Permission updated successfully'))->success();
        return redirect()->back();
    }
}

