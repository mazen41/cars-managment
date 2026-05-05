<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class PhpRamUsageCheck extends Check
{
    public function run(): Result
    {
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // in MB
        $memoryLimit = ini_get('memory_limit');

        $result = Result::make()
            ->meta([
                'memory_usage_mb' => round($memoryUsage, 2),
                'memory_limit' => $memoryLimit,
            ])->shortSummary('Memory usage is: ' . round($memoryUsage, 2) . ' MB');

        if ($memoryUsage > 500) {
            return $result->failed('Memory usage is too high!');
        }

        return $result->ok();
    }
}
