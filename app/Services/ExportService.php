<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\NotificationType;
use App\Notifications\ExportCompletedNotification;

class ExportService
{
    /**
     * Handle bulk export with queuing for large datasets
     *
     * @param string $exportClass Fully qualified export class name
     * @param array|null $ids IDs to export
     * @param string $exportType Export type (PDF, XLS, CSV, XLSX)
     * @param string $filename Base filename without extension
     * @param bool $exportAll Whether to export all records
     * @param int $threshold Threshold for queuing (default 500)
     * @param array $filters Additional filters to pass to export class
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|array
     */
    public function handleExport(
        string $exportClass,
        ?array $ids,
        string $exportType,
        string $filename,
        bool $exportAll = false,
        int $threshold = 500,
        array $filters = []
    ) {
        try {
        $typeData = get_export_type($exportType);
        $type = $typeData['type'];
        $extension = $typeData['extension'];
        $fullFilename = $filename . '_' . now()->timestamp . '.' . $extension;

        // Check if PDF export is requested for large datasets
        if (($exportAll || count($ids ?? []) > $threshold) && $type === \Maatwebsite\Excel\Excel::MPDF) {
            return [
                'success' => false,
                'message' => translate('Exporting PDF is not available for large datasets'),
            ];
        }

        // Queue export for large datasets
        if ($exportAll || count($ids ?? []) > $threshold) {
            return $this->queueExport($exportClass, $ids, $type, $fullFilename, $filters);
        }

        // Direct download for small datasets
        return Excel::download(new $exportClass($ids, $filters), $fullFilename, $type);
        } catch (\Exception $e) {
            \Log::error('Export failed: ' . $e->getMessage(), [
                'export_class' => $exportClass,
                'export_type' => $exportType,
                'filename' => $filename,
                'export_all' => $exportAll,
            ]);
            return [
                'success' => false,
                'message' => translate('An error occurred while processing the export: ') . $e->getMessage(),
            ];
        }
    }

    /**
     * Queue export job and notify user when complete
     */
    protected function queueExport(
        string $exportClass,
        ?array $ids,
        string $type,
        string $filename,
        array $filters = []
    ): array {
        $user = Auth::user();
        $filepath = 'all/' . $filename;
        $notificationTypeId = NotificationType::where('type', 'export_file_ready')->first()?->id;

        Excel::queue(new $exportClass($ids, $filters), $filepath, 'exports', $type)
            ->chain([
                function () use ($user, $filename, $filepath, $notificationTypeId) {
                    $user->notify(new ExportCompletedNotification([
                        'notification_type_id' => $notificationTypeId,
                        'file_name' => $filename,
                        'file_path' => $filepath,
                        'user_id' => $user->id,
                    ]));
                }
            ]);

        return [
            'success' => true,
            'message' => translate('Exporting in progress. You will be notified when the file is ready for download.'),
        ];
    }

    /**
     * Validate export request
     */
    public function validateExportRequest(bool $exportAll, ?array $ids): ?string
    {
        if (!$exportAll && empty($ids)) {
            return translate('Please select items to export or choose export all option.');
        }

        return null;
    }
}
