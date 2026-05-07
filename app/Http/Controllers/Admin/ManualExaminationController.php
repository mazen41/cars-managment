<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarInspection;
use App\Models\CarInspector;
use Illuminate\Http\Request;

class ManualExaminationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_car_inspection');
    }

    public function index(Request $request)
    {
        $query = CarInspection::with([
            'car.brand',
            'car.model',
            'inspector.user',
            'inspectionType',
        ])
            ->where('is_manual', true)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('inspector')) {
            $query->where('inspector_id', $request->inspector);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inspection_number', 'like', "%{$search}%")
                    ->orWhereHas('car', function ($carQuery) use ($search) {
                        $carQuery->where('vin', 'like', "%{$search}%")
                            ->orWhere('plate_number', 'like', "%{$search}%")
                            ->orWhereHas('brand', function ($brandQuery) use ($search) {
                                $brandQuery->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('model', function ($modelQuery) use ($search) {
                                $modelQuery->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('inspector', function ($inspectorQuery) use ($search) {
                        $inspectorQuery->where('shop_name', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $manualExaminations = $query->paginate(15)->appends($request->query());
        $inspectors = CarInspector::active()->get();
        $statuses = CarInspection::STATUSES;

        return view('backend.cars.manual-examinations.index', compact(
            'manualExaminations',
            'inspectors',
            'statuses'
        ));
    }

    public function show(CarInspection $manualExamination)
    {
        abort_unless($manualExamination->is_manual, 404);

        $manualExamination->load([
            'car.brand',
            'car.model',
            'car.category',
            'car.color',
            'car.country',
            'car.state',
            'car.city',
            'car.features.section',
            'car.customFieldValues.customField.options',
            'inspector.user',
            'inspectionType.sections.fields',
            'fieldValues.field.section',
            'requester',
        ]);

        $sectionData = [];
        foreach ($manualExamination->inspectionType->sections as $section) {
            $sectionData[$section->id] = [
                'section' => $section,
                'fields' => [],
                'completion' => $manualExamination->getSectionCompletion($section->id),
            ];

            foreach ($section->fields as $field) {
                $sectionData[$section->id]['fields'][] = [
                    'field' => $field,
                    'value' => $manualExamination->fieldValues->where('field_id', $field->id)->first(),
                ];
            }
        }

        return view('backend.cars.manual-examinations.show', compact('manualExamination', 'sectionData'));
    }

    public function schedule(CarInspection $manualExamination)
    {
        abort_unless($manualExamination->is_manual, 404);

        $inspectors = CarInspector::active()->get();

        return view('backend.cars.manual-examinations.schedule', compact('manualExamination', 'inspectors'));
    }

    public function updateSchedule(Request $request, CarInspection $manualExamination)
    {
        abort_unless($manualExamination->is_manual, 404);

        $request->validate([
            'inspector_id' => 'required|exists:car_inspectors,id',
            'scheduled_at' => 'nullable|date',
        ]);

        $manualExamination->update([
            'inspector_id'  => $request->inspector_id,
            'scheduled_at'  => $request->scheduled_at,
        ]);

        flash(translate('Examination scheduled successfully'))->success();
        return redirect()->route('admin.manual-examinations.index');
    }
}
