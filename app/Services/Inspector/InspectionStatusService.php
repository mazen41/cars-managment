<?php

namespace App\Services\Inspector;

use App\Exceptions\Inspector\InvalidStatusTransitionException;
use App\Models\CarInspection;

class InspectionStatusService
{
    /**
     * Valid status transitions
     */
    private const VALID_TRANSITIONS = [
        'scheduled' => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed' => [], // No transitions allowed from completed
        'cancelled' => [], // No transitions allowed from cancelled
    ];

    /**
     * Status descriptions
     */
    private const STATUS_DESCRIPTIONS = [
        'scheduled' => 'Inspection is scheduled and waiting to be started',
        'in_progress' => 'Inspection is currently being conducted',
        'completed' => 'Inspection has been completed successfully',
        'cancelled' => 'Inspection has been cancelled',
    ];

    /**
     * Validate and perform status transition
     */
    public function transitionStatus(CarInspection $inspection, string $newStatus, array $data = []): CarInspection
    {
        $currentStatus = $inspection->status;

        // Validate the transition
        $this->validateTransition($currentStatus, $newStatus);

        // Perform status-specific validations and actions
        switch ($newStatus) {
            case 'in_progress':
                $this->handleStartInspection($inspection, $data);
                break;
            case 'completed':
                $this->handleCompleteInspection($inspection, $data);
                break;
            case 'cancelled':
                $this->handleCancelInspection($inspection, $data);
                break;
        }

        // Update the status
        $inspection->status = $newStatus;
        $inspection->save();

        // Log the status change
        $this->logStatusChange($inspection, $currentStatus, $newStatus, $data);

        return $inspection;
    }

    /**
     * Validate status transition
     */
    private function validateTransition(string $currentStatus, string $newStatus): void
    {
        $allowedTransitions = self::VALID_TRANSITIONS[$currentStatus] ?? [];

        if (!in_array($newStatus, $allowedTransitions)) {
            throw new InvalidStatusTransitionException(
                $currentStatus,
                $newStatus,
                $allowedTransitions
            );
        }
    }

    /**
     * Handle starting an inspection
     */
    private function handleStartInspection(CarInspection $inspection, array $data): void
    {
        // Validate that inspection is scheduled for today or past
        if ($inspection->scheduled_at && $inspection->scheduled_at->isFuture()) {
            throw new InvalidStatusTransitionException(
                $inspection->status,
                'in_progress',
                [],
                [
                    'reason' => 'Cannot start inspection before scheduled time',
                    'scheduled_at' => $inspection->scheduled_at->toISOString(),
                    'current_time' => now()->toISOString()
                ]
            );
        }

        // Set start time
        $inspection->started_at = now();
        
        // Set inspector notes if provided
        if (isset($data['notes'])) {
            $inspection->inspector_notes = $data['notes'];
        }
    }

    /**
     * Handle completing an inspection
     */
    private function handleCompleteInspection(CarInspection $inspection, array $data): void
    {
        // Validate required completion data
        $this->validateCompletionData($inspection, $data);

        // Set completion time
        $inspection->completed_at = now();

        // Update inspection results
        if (isset($data['overall_condition'])) {
            $inspection->overall_condition = $data['overall_condition'];
        }

        if (isset($data['recommendations'])) {
            $inspection->recommendations = $data['recommendations'];
        }

        if (isset($data['inspector_notes'])) {
            $inspection->inspector_notes = $data['inspector_notes'];
        }

        // Validate that all required fields are completed
        $this->validateRequiredFields($inspection);
    }

    /**
     * Handle cancelling an inspection
     */
    private function handleCancelInspection(CarInspection $inspection, array $data): void
    {
        // Require cancellation reason
        if (empty($data['cancellation_reason'])) {
            throw new InvalidStatusTransitionException(
                $inspection->status,
                'cancelled',
                [],
                [
                    'reason' => 'Cancellation reason is required',
                    'required_fields' => ['cancellation_reason']
                ]
            );
        }

        $inspection->cancelled_at = now();
        $inspection->cancellation_reason = $data['cancellation_reason'];
        
        if (isset($data['inspector_notes'])) {
            $inspection->inspector_notes = $data['inspector_notes'];
        }
    }

    /**
     * Validate completion data
     */
    private function validateCompletionData(CarInspection $inspection, array $data): void
    {
        $requiredFields = ['overall_condition'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new InvalidStatusTransitionException(
                $inspection->status,
                'completed',
                [],
                [
                    'reason' => 'Missing required completion data',
                    'missing_fields' => $missingFields,
                    'required_fields' => $requiredFields
                ]
            );
        }

        // Validate overall_condition value
        $validConditions = ['excellent', 'good', 'fair', 'poor', 'failed'];
        if (!in_array($data['overall_condition'], $validConditions)) {
            throw new InvalidStatusTransitionException(
                $inspection->status,
                'completed',
                [],
                [
                    'reason' => 'Invalid overall condition value',
                    'provided_value' => $data['overall_condition'],
                    'valid_values' => $validConditions
                ]
            );
        }
    }

    /**
     * Validate required fields for completion
     */
    private function validateRequiredFields(CarInspection $inspection): void
    {
        // Check if inspection has required field values
        $requiredSections = $inspection->inspectionType->sections()
            ->where('is_required', true)
            ->get();

        $missingRequiredFields = [];

        foreach ($requiredSections as $section) {
            $requiredFields = $section->fields()
                ->where('is_required', true)
                ->get();

            foreach ($requiredFields as $field) {
                $fieldValue = $inspection->fieldValues()
                    ->where('field_id', $field->id)
                    ->first();

                if (!$fieldValue || empty($fieldValue->value)) {
                    $missingRequiredFields[] = [
                        'section' => $section->name,
                        'field' => $field->name,
                        'field_id' => $field->id
                    ];
                }
            }
        }

        if (!empty($missingRequiredFields)) {
            throw new InvalidStatusTransitionException(
                $inspection->status,
                'completed',
                [],
                [
                    'reason' => 'Missing required inspection field values',
                    'missing_fields' => $missingRequiredFields
                ]
            );
        }
    }

    /**
     * Log status change
     */
    private function logStatusChange(CarInspection $inspection, string $oldStatus, string $newStatus, array $data): void
    {
        \Log::info('Inspection status changed', [
            'inspection_id' => $inspection->id,
            'inspection_number' => $inspection->inspection_number,
            'inspector_id' => $inspection->inspector_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => auth('api')->id(),
            'change_data' => $data,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get valid transitions for a status
     */
    public function getValidTransitions(string $status): array
    {
        return self::VALID_TRANSITIONS[$status] ?? [];
    }

    /**
     * Get all valid statuses
     */
    public function getAllStatuses(): array
    {
        return array_keys(self::STATUS_DESCRIPTIONS);
    }

    /**
     * Get status description
     */
    public function getStatusDescription(string $status): string
    {
        return self::STATUS_DESCRIPTIONS[$status] ?? 'Unknown status';
    }

    /**
     * Check if transition is valid
     */
    public function isValidTransition(string $currentStatus, string $newStatus): bool
    {
        $allowedTransitions = self::VALID_TRANSITIONS[$currentStatus] ?? [];
        return in_array($newStatus, $allowedTransitions);
    }
}