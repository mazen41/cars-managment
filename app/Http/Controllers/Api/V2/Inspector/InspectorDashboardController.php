<?php

namespace App\Http\Controllers\Api\V2\Inspector;

use App\Http\Controllers\Controller;
use App\Models\CarInspection;
use App\Models\CarInspectorPaymentHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InspectorDashboardController extends Controller
{
    /**
     * Get dashboard overview statistics
     */
    public function index(Request $request): JsonResponse
    {
        $inspector = $request->user()->carInspector;

        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        $currentMonth = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        // Get overview statistics
        $overview = $this->getOverviewStatistics($inspector->id, $currentMonth, $currentMonthEnd);

        return response()->json([
            'data' => [
                'overview' => $overview
            ]
        ]);
    }

    /**
     * Get analytics data for charts and trends
     */
    public function analytics(Request $request): JsonResponse
    {
        $inspector = $request->user()->carInspector;

        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        $request->validate([
            'period' => 'sometimes|string|in:week,month,quarter,year',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date'
        ]);

        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;

        // Set default date range based on period
        if (!$startDate || !$endDate) {
            [$startDate, $endDate] = $this->getDateRangeForPeriod($period);
        }

        $charts = [
            'monthly_inspections' => $this->getMonthlyInspectionTrend($inspector->id, $startDate, $endDate),
            'earnings_trend' => $this->getEarningsTrend($inspector->id, $startDate, $endDate),
            'completion_times' => $this->getCompletionTimesAnalytics($inspector->id, $startDate, $endDate),
            'status_distribution' => $this->getStatusDistribution($inspector->id, $startDate, $endDate)
        ];

        return response()->json([
            'data' => [
                'charts' => $charts,
                'period' => $period,
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString()
                ]
            ]
        ]);
    }

    /**
     * Get overview statistics for dashboard
     */
    private function getOverviewStatistics(int $inspectorId, Carbon $startDate, Carbon $endDate): array
    {
        // Total inspections this month
        $totalInspectionsThisMonth = CarInspection::where('inspector_id', $inspectorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Pending inspections
        $pendingInspections = CarInspection::where('inspector_id', $inspectorId)
            ->where('status', CarInspection::STATUS_PENDING)
            ->count();

        // Completed inspections this month
        $completedInspectionsThisMonth = CarInspection::where('inspector_id', $inspectorId)
            ->where('status', CarInspection::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->count();

        // Average completion time (in hours)
        $avgCompletionTime = CarInspection::where('inspector_id', $inspectorId)
            ->where('status', CarInspection::STATUS_COMPLETED)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_minutes')
            ->value('avg_minutes');

        $avgCompletionTimeFormatted = $avgCompletionTime
            ? $this->formatMinutesToHours($avgCompletionTime)
            : '0 hours';

        // Total earnings this month
        $totalEarningsThisMonth = CarInspectorPaymentHistory::where('car_inspector_id', $inspectorId)
            ->where('type', CarInspectorPaymentHistory::TYPE_EARNING)
            ->where('status', CarInspectorPaymentHistory::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Success rate (completed vs total started)
        $totalStartedInspections = CarInspection::where('inspector_id', $inspectorId)
            ->whereIn('status', [
                CarInspection::STATUS_COMPLETED,
                CarInspection::STATUS_CANCELLED,
                CarInspection::STATUS_FAILED
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $successRate = $totalStartedInspections > 0
            ? round(($completedInspectionsThisMonth / $totalStartedInspections) * 100, 1)
            : 0;

        return [
            'total_inspections_this_month' => $totalInspectionsThisMonth,
            'pending_inspections' => $pendingInspections,
            'completed_inspections' => $completedInspectionsThisMonth,
            'average_completion_time' => $avgCompletionTimeFormatted,
            'total_earnings_this_month' => round($totalEarningsThisMonth, 2),
            'success_rate' => $successRate
        ];
    }

    /**
     * Get monthly inspection trend data
     */
    private function getMonthlyInspectionTrend(int $inspectorId, Carbon $startDate, Carbon $endDate): array
    {
        $inspections = CarInspection::where('inspector_id', $inspectorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dateStr = $current->toDateString();
            $inspection = $inspections->firstWhere('date', $dateStr);

            $data[] = [
                'date' => $dateStr,
                'count' => $inspection ? $inspection->count : 0
            ];

            $current->addDay();
        }

        return $data;
    }

    /**
     * Get earnings trend data
     */
    private function getEarningsTrend(int $inspectorId, Carbon $startDate, Carbon $endDate): array
    {
        $earnings = CarInspectorPaymentHistory::where('car_inspector_id', $inspectorId)
            ->where('type', CarInspectorPaymentHistory::TYPE_EARNING)
            ->where('status', CarInspectorPaymentHistory::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dateStr = $current->toDateString();
            $earning = $earnings->firstWhere('date', $dateStr);

            $data[] = [
                'date' => $dateStr,
                'amount' => $earning ? round($earning->total, 2) : 0
            ];

            $current->addDay();
        }

        return $data;
    }

    /**
     * Get completion times analytics
     */
    private function getCompletionTimesAnalytics(int $inspectorId, Carbon $startDate, Carbon $endDate): array
    {
        $completionTimes = CarInspection::where('inspector_id', $inspectorId)
            ->where('status', CarInspection::STATUS_COMPLETED)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->selectRaw('
                TIMESTAMPDIFF(MINUTE, started_at, completed_at) as duration_minutes,
                DATE(completed_at) as date
            ')
            ->get();

        // Group by time ranges
        $ranges = [
            '0-30 min' => 0,
            '30-60 min' => 0,
            '1-2 hours' => 0,
            '2-4 hours' => 0,
            '4+ hours' => 0
        ];

        foreach ($completionTimes as $time) {
            $minutes = $time->duration_minutes;

            if ($minutes <= 30) {
                $ranges['0-30 min']++;
            } elseif ($minutes <= 60) {
                $ranges['30-60 min']++;
            } elseif ($minutes <= 120) {
                $ranges['1-2 hours']++;
            } elseif ($minutes <= 240) {
                $ranges['2-4 hours']++;
            } else {
                $ranges['4+ hours']++;
            }
        }

        return array_map(function($range, $count) {
            return [
                'range' => $range,
                'count' => $count
            ];
        }, array_keys($ranges), $ranges);
    }

    /**
     * Get status distribution for inspections
     */
    private function getStatusDistribution(int $inspectorId, Carbon $startDate, Carbon $endDate): array
    {
        $statusCounts = CarInspection::where('inspector_id', $inspectorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $data = [];
        foreach ($statusCounts as $status) {
            $data[] = [
                'status' => $status->status,
                'status_display' => CarInspection::STATUSES[$status->status] ?? ucfirst($status->status),
                'count' => $status->count
            ];
        }

        return $data;
    }

    /**
     * Get date range for specified period
     */
    private function getDateRangeForPeriod(string $period): array
    {
        $endDate = Carbon::now();

        $startDate = match($period) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth()
        };

        return [$startDate, $endDate];
    }

    /**
     * Format minutes to human readable hours format
     */
    private function formatMinutesToHours(float $minutes): string
    {
        if ($minutes < 60) {
            return round($minutes) . ' minutes';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes == 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        return $hours . 'h ' . round($remainingMinutes) . 'm';
    }
}
