<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class ServerRamUsageCheck extends Check
{
    public function run(): Result
    {
        $meminfo = file_get_contents("/proc/meminfo");

        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);

        $totalKb = (int) $total[1];
        $availableKb = (int) $available[1];

        $usedKb = $totalKb - $availableKb;

        $totalGb = round($totalKb / 1048576, 2);       // 1 GB = 1048576 KB
        $usedGb = round($usedKb / 1048576, 2);
        $usagePercent = round(($usedKb / $totalKb) * 100, 2);

        $result = Result::make()
            ->meta([
                'total_memory_gb' => $totalGb,
                'used_memory_gb' => $usedGb,
                'usage_percent' => $usagePercent,
            ])
            ->shortSummary("Server RAM usage: {$usedGb} GB / {$totalGb} GB ({$usagePercent}%)");

        if ($usagePercent >= 90) {
            return $result->failed("Server RAM usage is too high: {$usagePercent}% used.");
        } elseif ($usagePercent >= 70) {
            return $result->warning("Server RAM usage is high: {$usagePercent}% used.");
        }

        return $result->ok();
    }
}
