<?php

namespace App\Http\Controllers;

use App\Models\CarCustomField;
use App\Models\CarCustomFieldOption;
use App\Models\CarCustomFieldTranslation;
use App\Models\CarCustomFieldOptionTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarCustomFieldController extends Controller
{
    /**
     * Display a listing of the car custom fields.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = CarCustomField::with(['options'])->withCount(['values', 'options']);

        // Apply filters
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('required')) {
            if ($request->required === '1') {
                $query->required();
            } else {
                $query->optional();
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy ?? 'name', $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $customFields = $query->paginate($perPage);

        $availableTypes = CarCustomField::getAvailableTypes();

        if ($request->wantsJson()) {
            return response()->json([
                'custom_fields' => $customFields,
                'available_types' => $availableTypes
            ]);
        }

        return view('backend.cars.custom-fields.index', compact('customFields', 'availableTypes'));
    }

    /**
     * Show the form for creating a new car custom field.
     */
    public function create(): View
    {
        $availableTypes = CarCustomField::getAvailableTypes();
        $availableLanguages = config('app.available_languages', ['en', 'ar']);

        return view('backend.cars.custom-fields.create', compact('availableTypes', 'availableLanguages'));
    }

    /**
     * Store a newly created car custom field in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(CarCustomField::getAvailableTypes())),
            'order' => 'required|integer|min:0',
            'required' => 'boolean',
            'icon' => 'nullable|integer|exists:uplodas,id',
            'options' => 'nullable|array',
            'options.*.label' => 'required|string|max:255',
            'options.*.value' => 'required|string|max:255',
        ]);

        // Custom validation for field types that require options
        $validator->after(function ($validator) use ($request) {
            $typesThatRequireOptions = [
                CarCustomField::TYPE_SELECT,
                CarCustomField::TYPE_RADIO,
                CarCustomField::TYPE_CHECKBOX
            ];

            if (in_array($request->type, $typesThatRequireOptions)) {
                if (!$request->filled('options') || count($request->options) === 0) {
                    $validator->errors()->add('options', 'This field type requires at least one option.');
                }
            }
        });

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $fieldData = $request->only(['name', 'type', 'order', 'required']);

            $customField = CarCustomField::create($fieldData);

            // Handle options
            if ($request->filled('options')) {
                foreach ($request->options as $optionData) {
                    $option = CarCustomFieldOption::create([
                        'custom_field_id' => $customField->id,
                        'label' => $optionData['label'],
                        'value' => $optionData['value'],
                    ]);

                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car custom field created successfully',
                    'custom_field' => $customField->load(['options', 'translations'])
                ], 201);
            }
            flash()->success('Car custom field created successfully');
            return redirect()->route('admin.car-custom-fields.index')
                ->with('success', 'Car custom field created successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to create car custom field'], 500);
            }
            flash()->error($e->getMessage());
            return back()->with('error', 'Failed to create car custom field')->withInput();
        }
    }

    /**
     * Display the specified car custom field.
     */
    public function show(CarCustomField $carCustomField)
    {
       //
    }

    /**
     * Show the form for editing the specified car custom field.
     */
    public function edit(CarCustomField $carCustomField): View
    {
        $carCustomField->load(['options.translations', 'translations']);
        $availableTypes = CarCustomField::getAvailableTypes();

        return view('backend.cars.custom-fields.edit', compact('carCustomField', 'availableTypes'));
    }

    /**
     * Update the specified car custom field in storage.
     */
    public function update(Request $request, CarCustomField $carCustomField): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(CarCustomField::getAvailableTypes())),
            'order' => 'required|integer|min:0',
            'required' => 'boolean',
            'icon' => 'nullable|integer|exists:uplodas,id',
            'options' => 'nullable|array',
            'options.*.label' => 'required|string|max:255',
            'options.*.value' => 'required|string|max:255',
        ]);

        // Custom validation for field types that require options
        $validator->after(function ($validator) use ($request) {
            $typesThatRequireOptions = [
                CarCustomField::TYPE_SELECT,
                CarCustomField::TYPE_RADIO,
                CarCustomField::TYPE_CHECKBOX
            ];

            if (in_array($request->type, $typesThatRequireOptions)) {
                if (!$request->filled('options') || count($request->options) === 0) {
                    $validator->errors()->add('options', 'This field type requires at least one option.');
                }
            }
        });

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $fieldData = $request->only(['name', 'type', 'order', 'required']);

            if($request->lang && $request->lang != app()->getLocale()){
                unset($fieldData['name']);
            }

	    // Fix checkbox issue
	    $fieldData['required'] = $request->has('required');

            $carCustomField->update($fieldData);

            $carCustomField->translate(
                ['lang' => $request->lang?? app()->getLocale()],
                values: ['name' => $request->name]
            );

            // Handle options
            if ($request->has('options')) {
                // Delete existing options and their translations
                foreach ($carCustomField->options as $option) {
                    $option->translations()->delete();
                    $option->delete();
                }

                // Create new options
                if ($request->filled('options')) {
                    foreach ($request->options as $optionData) {
                        $option = CarCustomFieldOption::create([
                            'custom_field_id' => $carCustomField->id,
                            'label' => $optionData['label'],
                            'value' => $optionData['value'],
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Car custom field updated successfully',
                    'custom_field' => $carCustomField->load(['options', 'translations'])
                ]);
            }
            flash()->success('Car custom field updated successfully');
            return redirect()->route('admin.car-custom-fields.index')
                ->with('success', 'Car custom field updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update car custom field'], 500);
            }
            flash()->error($e->getMessage());
            return back()->with('error', 'Failed to update car custom field')->withInput();
        }
    }

    /**
     * Remove the specified car custom field from storage.
     */
    public function destroy(CarCustomField $carCustomField): RedirectResponse|JsonResponse
    {

        try {
            DB::beginTransaction();

            // Delete options and their translations
            foreach ($carCustomField->options as $option) {
                $option->translations()->delete();
                $option->delete();
            }
            // Delete custom field values
            $carCustomField->values()->delete();

            // Delete translations
            $carCustomField->translations()->delete();

            // Delete the custom field
            $carCustomField->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Car custom field deleted successfully']);
            }
            flash()->success('Car custom field deleted successfully');
            return redirect()->route('admin.car-custom-fields.index')
                ->with('success', 'Car custom field deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete car custom field'], 500);
            }
            flash()->error($e->getMessage());
            return back()->with('error', 'Failed to delete car custom field');
        }
    }

    /**
     * Update custom fields order.
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.*.id' => 'required|exists:car_custom_fields,id',
            'fields.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->fields as $fieldData) {
                CarCustomField::where('id', $fieldData['id'])
                    ->update(['order' => $fieldData['order']]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Custom fields order updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update fields order'], 500);
        }
    }

    /**
     * Get custom field statistics.
     */
    public function statistics(CarCustomField $carCustomField): JsonResponse
    {
        $totalValues = $carCustomField->values()->count();
        $uniqueValues = $carCustomField->getUniqueValues();
        $valueDistribution = [];

        if ($carCustomField->hasOptions()) {
            // For fields with options, get distribution
            foreach ($carCustomField->options as $option) {
                $count = $carCustomField->values()
                    ->where('value', $option->value)
                    ->count();
                $valueDistribution[$option->label] = $count;
            }
        } else {
            // For fields without options, get top values
            $topValues = $carCustomField->values()
                ->select('value', DB::raw('COUNT(*) as count'))
                ->groupBy('value')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            foreach ($topValues as $value) {
                $valueDistribution[$value->value] = $value->count;
            }
        }

        $stats = [
            'total_values' => $totalValues,
            'unique_values_count' => $uniqueValues->count(),
            'value_distribution' => $valueDistribution,
            'field_type' => $carCustomField->type,
            'is_required' => $carCustomField->required,
            'options_count' => $carCustomField->options()->count(),
            'recent_cars' => $carCustomField->values()
                ->with(['car.brand', 'car.model'])
                ->whereHas('car', function ($query) {
                    $query->published();
                })
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json(['statistics' => $stats]);
    }

    /**
     * Bulk delete custom fields.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'field_ids' => 'required|array',
            'field_ids.*' => 'exists:car_custom_fields,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $customFields = CarCustomField::whereIn('id', $request->field_ids)->get();


            // Delete fields
            foreach ($customFields as $field) {
                // Delete icon from storage
                if ($field->icon) {
                    Storage::disk('public')->delete($field->icon);
                }

                // Delete options and their translations
                foreach ($field->options as $option) {
                    $option->translations()->delete();
                    $option->delete();
                }
                // Delete values
                $field->values()->delete();

                // Delete translations
                $field->translations()->delete();

                // Delete the field
                $field->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Custom fields deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete custom fields'], 500);
        }
    }

    /**
     * Get field values for specific cars.
     */
    public function getFieldValues(Request $request, CarCustomField $carCustomField): JsonResponse
    {
        $query = $carCustomField->values()->with('car');

        if ($request->filled('car_ids')) {
            $query->whereIn('car_id', $request->car_ids);
        }

        $values = $query->get();

        return response()->json(['values' => $values]);
    }

    /**
     * Export field data.
     */
    public function export(Request $request): JsonResponse
    {
        $customFields = CarCustomField::with(['options', 'values.car'])
            ->ordered()
            ->get();

        $exportData = [];

        foreach ($customFields as $field) {
            $fieldData = [
                'id' => $field->id,
                'name' => $field->name,
                'type' => $field->type,
                'required' => $field->required,
                'order' => $field->order,
                'values_count' => $field->values()->count(),
                'options' => $field->options->map(function ($option) {
                    return [
                        'label' => $option->label,
                        'value' => $option->value,
                        'usage_count' => $option->usage_count,
                    ];
                }),
            ];

            if ($request->filled('include_values')) {
                $fieldData['values'] = $field->values->map(function ($value) {
                    return [
                        'value' => $value->value,
                        'car_id' => $value->car_id,
                        'car_name' => $value->car->name ?? null,
                    ];
                });
            }

            $exportData[] = $fieldData;
        }

        return response()->json(['export_data' => $exportData]);
    }
}
