<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait PaginationFilterTrait
{
    /**
     * Apply pagination to a query
     */
    protected function paginateQuery(Builder $query, Request $request, int $defaultPerPage = 15): LengthAwarePaginator
    {
        $perPage = $request->get('per_page', $defaultPerPage);
        $perPage = min(max($perPage, 1), 100); // Limit between 1 and 100
        
        return $query->paginate($perPage);
    }

    /**
     * Apply common filters to a query
     */
    protected function applyCommonFilters(Builder $query, Request $request): Builder
    {
        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $this->applySearchFilter($q, $searchTerm);
            });
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortOrder, ['asc', 'desc'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }

    /**
     * Apply inspection-specific filters
     */
    protected function applyInspectionFilters(Builder $query, Request $request): Builder
    {
        // Status filter
        if ($request->filled('status')) {
            $statuses = is_array($request->get('status')) 
                ? $request->get('status') 
                : [$request->get('status')];
            $query->whereIn('status', $statuses);
        }

        // Inspection type filter
        if ($request->filled('inspection_type_id')) {
            $query->where('inspection_type_id', $request->get('inspection_type_id'));
        }

        // Car filter
        if ($request->filled('car_id')) {
            $query->where('car_id', $request->get('car_id'));
        }

        // Scheduled date range
        if ($request->filled('scheduled_from')) {
            $query->whereDate('scheduled_at', '>=', $request->get('scheduled_from'));
        }

        if ($request->filled('scheduled_to')) {
            $query->whereDate('scheduled_at', '<=', $request->get('scheduled_to'));
        }

        // Overdue filter
        if ($request->boolean('overdue_only')) {
            $query->where('scheduled_at', '<', now())
                  ->whereIn('status', ['pending', 'in_progress']);
        }

        // Completed date range
        if ($request->filled('completed_from')) {
            $query->whereDate('completed_at', '>=', $request->get('completed_from'));
        }

        if ($request->filled('completed_to')) {
            $query->whereDate('completed_at', '<=', $request->get('completed_to'));
        }

        return $query;
    }

    /**
     * Apply payment-specific filters
     */
    protected function applyPaymentFilters(Builder $query, Request $request): Builder
    {
        // Payment type filter
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Payment status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        // Amount range filters
        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->get('amount_from'));
        }

        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->get('amount_to'));
        }

        return $query;
    }

    /**
     * Apply search filter - to be implemented by the using class
     */
    protected function applySearchFilter(Builder $query, string $searchTerm): Builder
    {
        // Default implementation - override in the using class for specific search logic
        return $query;
    }

    /**
     * Get filter summary for API response
     */
    protected function getFilterSummary(Request $request): array
    {
        $filters = [];

        if ($request->filled('search')) {
            $filters['search'] = $request->get('search');
        }

        if ($request->filled('status')) {
            $filters['status'] = $request->get('status');
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $filters['date_range'] = [
                'from' => $request->get('date_from'),
                'to' => $request->get('date_to'),
            ];
        }

        if ($request->filled('scheduled_from') || $request->filled('scheduled_to')) {
            $filters['scheduled_range'] = [
                'from' => $request->get('scheduled_from'),
                'to' => $request->get('scheduled_to'),
            ];
        }

        return $filters;
    }

    /**
     * Validate pagination parameters
     */
    protected function validatePaginationParams(Request $request): array
    {
        return $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'sort_by' => 'string',
            'sort_order' => 'in:asc,desc',
        ]);
    }

    /**
     * Validate common filter parameters
     */
    protected function validateCommonFilters(Request $request): array
    {
        return $request->validate([
            'search' => 'string|max:255',
            'date_from' => 'date',
            'date_to' => 'date|after_or_equal:date_from',
        ]);
    }

    /**
     * Validate inspection filter parameters
     */
    protected function validateInspectionFilters(Request $request): array
    {
        return $request->validate([
            'status' => 'array',
            'status.*' => 'in:pending,in_progress,completed,cancelled,failed',
            'inspection_type_id' => 'integer|exists:car_inspection_types,id',
            'car_id' => 'integer|exists:cars,id',
            'scheduled_from' => 'date',
            'scheduled_to' => 'date|after_or_equal:scheduled_from',
            'completed_from' => 'date',
            'completed_to' => 'date|after_or_equal:completed_from',
            'overdue_only' => 'boolean',
        ]);
    }

    /**
     * Validate payment filter parameters
     */
    protected function validatePaymentFilters(Request $request): array
    {
        return $request->validate([
            'type' => 'in:earning,payment,adjustment',
            'status' => 'in:pending,completed,failed,cancelled',
            'payment_method' => 'string|max:50',
            'amount_from' => 'numeric|min:0',
            'amount_to' => 'numeric|min:0|gte:amount_from',
        ]);
    }
}