<?php

namespace App\Exceptions\Inspector;

class InspectionNotFoundException extends InspectorException
{
    protected string $errorCode = 'INSPECTION_NOT_FOUND';
    protected int $statusCode = 404;

    public function __construct(string $message = 'Inspection not found or not assigned to you.', array $details = [])
    {
        parent::__construct($message, $details);
    }
}