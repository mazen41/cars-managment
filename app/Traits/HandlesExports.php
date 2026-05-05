<?php

namespace App\Traits;

use App\Services\ExportService;
use Illuminate\Http\Request;

trait HandlesExports
{
    /**
     * Handle bulk export with automatic queuing for large datasets
     *
     * @param Request $request
     * @param string $exportClass Fully qualified export class name
     * @param string $baseFilename Base filename without timestamp/extension
     * @param callable|null $getIdsCallback Callback to get IDs from request/query
     * @param int $threshold Threshold for queuing (default 500)
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function handleBulkExport(
        Request $request,
        string $exportClass,
        string $baseFilename,
        ?callable $getIdsCallback = null,
        int $threshold = 500,
        array $filters = []

    ) {
        $exportService = app(ExportService::class);

        $exportAll = $request->input('select_all', false);
        $ids = $request->id;

        // Validate request
        $error = $exportService->validateExportRequest($exportAll, $ids);
        if ($error) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }
            flash($error)->error();
            return back()->with('error', $error);
        }

        // Get IDs using callback if provided
        if ($exportAll && $getIdsCallback) {
            $ids = $getIdsCallback($request);
        }

        // Handle export
        $result = $exportService->handleExport(
            $exportClass,
            $ids,
            $request->export_type,
            $baseFilename,
            $exportAll,
            $threshold,
            $filters
        );

        // Handle response
        if (is_array($result)) {
            if ($request->expectsJson()) {
                return response()->json($result);
            }

            if ($result['success']) {
                flash($result['message'])->success();
            } else {
                flash($result['message'])->error();
            }

            return back();
        }

        return $result;
    }
}
