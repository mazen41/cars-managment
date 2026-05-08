<?php

namespace App\Services;

use App\Models\CarInspection;
use Illuminate\Database\Eloquent\Builder;

class ManualExaminationService
{
    public static function getFullData(int $id, ?int $inspectorId = null): array
    {
        $query = CarInspection::query()
            ->manual()
            ->with(self::relations());

        if ($inspectorId !== null) {
            $query->where('inspector_id', $inspectorId);
        }

        /** @var CarInspection $manualExamination */
        $manualExamination = $query->findOrFail($id);

        return [
            'manualExamination' => $manualExamination,
            'sectionData' => self::buildSectionData($manualExamination),
        ];
    }

    public static function relations(): array
    {
        return [
            'car.brand',
            'car.model',
            'car.category',
            'car.color',
            'car.country',
            'car.state',
            'car.city',
            'car.features.section',
            'car.customFieldValues.customField.options',
            'car.inspections',
            'inspector.user',
            'inspectionType.sections' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'inspectionType.sections.fields' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'fieldValues.field.section',
            'requester:id,name,email,phone',
        ];
    }

    public static function queryForInspector(int $inspectorId): Builder
    {
        return CarInspection::query()
            ->manual()
            ->where('inspector_id', $inspectorId)
            ->with([
                'car.brand:id,name',
                'car.model:id,name',
                'inspectionType:id,name',
            ]);
    }

    public static function buildSectionData(CarInspection $manualExamination): array
    {
        $sectionData = [];
        $sections = $manualExamination->inspectionType?->sections ?? collect();
        $fieldValues = $manualExamination->fieldValues ?? collect();

        foreach ($sections as $section) {
            $sectionData[$section->id] = [
                'section' => $section,
                'fields' => [],
                'completion' => $manualExamination->getSectionCompletion($section->id),
            ];

            foreach (($section->fields ?? collect()) as $field) {
                $sectionData[$section->id]['fields'][] = [
                    'field' => $field,
                    'value' => $fieldValues->firstWhere('field_id', $field->id),
                ];
            }
        }

        return $sectionData;
    }
}
