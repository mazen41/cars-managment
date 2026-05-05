<?php

namespace App\Http\Resources\V2\Inspector;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'overview' => [
                'total_inspections_this_month' => $this->resource['total_inspections_this_month'] ?? 0,
                'pending_inspections' => $this->resource['pending_inspections'] ?? 0,
                'completed_inspections' => $this->resource['completed_inspections'] ?? 0,
                'in_progress_inspections' => $this->resource['in_progress_inspections'] ?? 0,
                'cancelled_inspections' => $this->resource['cancelled_inspections'] ?? 0,
                'average_completion_time' => $this->resource['average_completion_time'] ?? 0,
                'formatted_average_completion_time' => $this->formatDuration($this->resource['average_completion_time'] ?? 0),
                'total_earnings_this_month' => $this->resource['total_earnings_this_month'] ?? 0,
                'formatted_total_earnings_this_month' => format_price($this->resource['total_earnings_this_month'] ?? 0),
                'success_rate' => $this->resource['success_rate'] ?? 0,
                'completion_rate' => $this->resource['completion_rate'] ?? 0,
                'average_score' => $this->resource['average_score'] ?? 0,
                'total_earnings_all_time' => $this->resource['total_earnings_all_time'] ?? 0,
                'formatted_total_earnings_all_time' => format_price($this->resource['total_earnings_all_time'] ?? 0),
            ],
            
            'charts' => [
                'monthly_inspections' => $this->resource['charts']['monthly_inspections'] ?? [],
                'earnings_trend' => $this->resource['charts']['earnings_trend'] ?? [],
                'completion_times' => $this->resource['charts']['completion_times'] ?? [],
                'status_distribution' => $this->resource['charts']['status_distribution'] ?? [],
                'inspection_types' => $this->resource['charts']['inspection_types'] ?? [],
                'weekly_performance' => $this->resource['charts']['weekly_performance'] ?? [],
            ],
            
            'recent_activity' => [
                'recent_inspections' => $this->resource['recent_activity']['recent_inspections'] ?? [],
                'upcoming_inspections' => $this->resource['recent_activity']['upcoming_inspections'] ?? [],
                'overdue_inspections' => $this->resource['recent_activity']['overdue_inspections'] ?? [],
            ],
            
            'performance_metrics' => [
                'this_week' => [
                    'inspections_completed' => $this->resource['performance_metrics']['this_week']['inspections_completed'] ?? 0,
                    'earnings' => $this->resource['performance_metrics']['this_week']['earnings'] ?? 0,
                    'formatted_earnings' => format_price($this->resource['performance_metrics']['this_week']['earnings'] ?? 0),
                    'average_score' => $this->resource['performance_metrics']['this_week']['average_score'] ?? 0,
                ],
                'last_week' => [
                    'inspections_completed' => $this->resource['performance_metrics']['last_week']['inspections_completed'] ?? 0,
                    'earnings' => $this->resource['performance_metrics']['last_week']['earnings'] ?? 0,
                    'formatted_earnings' => format_price($this->resource['performance_metrics']['last_week']['earnings'] ?? 0),
                    'average_score' => $this->resource['performance_metrics']['last_week']['average_score'] ?? 0,
                ],
                'this_month' => [
                    'inspections_completed' => $this->resource['performance_metrics']['this_month']['inspections_completed'] ?? 0,
                    'earnings' => $this->resource['performance_metrics']['this_month']['earnings'] ?? 0,
                    'formatted_earnings' => format_price($this->resource['performance_metrics']['this_month']['earnings'] ?? 0),
                    'average_score' => $this->resource['performance_metrics']['this_month']['average_score'] ?? 0,
                ],
                'last_month' => [
                    'inspections_completed' => $this->resource['performance_metrics']['last_month']['inspections_completed'] ?? 0,
                    'earnings' => $this->resource['performance_metrics']['last_month']['earnings'] ?? 0,
                    'formatted_earnings' => format_price($this->resource['performance_metrics']['last_month']['earnings'] ?? 0),
                    'average_score' => $this->resource['performance_metrics']['last_month']['average_score'] ?? 0,
                ],
            ],
            
            'goals_and_targets' => [
                'monthly_inspection_target' => $this->resource['goals_and_targets']['monthly_inspection_target'] ?? 0,
                'monthly_earnings_target' => $this->resource['goals_and_targets']['monthly_earnings_target'] ?? 0,
                'formatted_monthly_earnings_target' => format_price($this->resource['goals_and_targets']['monthly_earnings_target'] ?? 0),
                'target_completion_rate' => $this->resource['goals_and_targets']['target_completion_rate'] ?? 0,
                'progress_to_monthly_target' => $this->resource['goals_and_targets']['progress_to_monthly_target'] ?? 0,
                'progress_to_earnings_target' => $this->resource['goals_and_targets']['progress_to_earnings_target'] ?? 0,
            ],
            
            'generated_at' => now()->toISOString(),
        ];
    }
    
    /**
     * Format duration from hours to human readable format
     */
    private function formatDuration($hours): string
    {
        if ($hours === 0) {
            return '0h 0m';
        }
        
        $wholeHours = floor($hours);
        $minutes = ($hours - $wholeHours) * 60;
        
        return $wholeHours . 'h ' . round($minutes) . 'm';
    }
}