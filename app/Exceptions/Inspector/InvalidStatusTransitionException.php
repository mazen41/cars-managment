<?php

namespace App\Exceptions\Inspector;

class InvalidStatusTransitionException extends InspectorException
{
    protected string $errorCode = 'INVALID_STATUS_TRANSITION';
    protected int $statusCode = 422;

    public function __construct(string $currentStatus, string $newStatus, array $allowedTransitions = [])
    {
        $message = "Cannot change inspection status from '{$currentStatus}' to '{$newStatus}'.";
        
        $details = [
            'current_status' => $currentStatus,
            'requested_status' => $newStatus,
        ];

        if (!empty($allowedTransitions)) {
            $details['allowed_transitions'] = $allowedTransitions;
            $message .= ' Allowed transitions: ' . implode(', ', $allowedTransitions);
        }

        parent::__construct($message, $details);
    }
}