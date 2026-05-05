<?php

namespace App\Exceptions\Inspector;

class InspectorAccessDeniedException extends InspectorException
{
    protected string $errorCode = 'INSPECTOR_ACCESS_DENIED';
    protected int $statusCode = 403;

    public function __construct(string $message = 'Access denied. Only active car inspectors can access this resource.', array $details = [])
    {
        parent::__construct($message, $details);
    }
}