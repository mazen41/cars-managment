<?php

namespace App\Http\Controllers;

use App\Models\CarInspection;
use Illuminate\Http\Request;

class PublicManualExaminationPhotoController extends Controller
{
    /**
     * Public streaming endpoint for manual examination photos.
     *
     * Why this exists:
     * - Browsers do not send custom headers (like System-Key) for <img src="..."> requests.
     * - Some deployments may not expose /storage via symlink/webserver correctly.
     * - PDF rendering already reads from storage directly; this mirrors that behavior for HTML views.
     */
    public function show(Request $request, int $manualExaminationId, string $encodedPath)
    {
        $manualExamination = CarInspection::with('fieldValues')
            ->where('is_manual', true)
            ->findOrFail($manualExaminationId);

        $path = decode_inspection_photo_path($encodedPath);
        abort_unless($path && $this->photoBelongsToManualExamination($manualExamination, $path), 404);

        foreach (inspection_photo_file_candidates($path) as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                return response()->file($candidate);
            }
        }

        abort(404);
    }

    private function photoBelongsToManualExamination(CarInspection $manualExamination, string $path): bool
    {
        $allowedPaths = [];
        $photoFields = [
            'photo_front',
            'photo_back',
            'photo_left',
            'photo_right',
            'photo_interior_front',
            'photo_interior_back',
            'photo_engine',
            'photo_trunk',
            'photo_odometer',
            'photo_dashboard',
            'photo_vin_plate',
            'photo_tires',
            'photo_undercarriage',
        ];

        foreach ($photoFields as $field) {
            if (!empty($manualExamination->{$field})) {
                $allowedPaths[] = ltrim((string) $manualExamination->{$field}, '/');
            }
        }

        foreach ((($manualExamination->metadata ?? [])['section_photos'] ?? []) as $sectionPhotos) {
            if (!is_array($sectionPhotos)) {
                continue;
            }

            foreach ($sectionPhotos as $sectionPhoto) {
                if (!empty($sectionPhoto['path'])) {
                    $allowedPaths[] = ltrim((string) $sectionPhoto['path'], '/');
                }
            }
        }

        foreach ($manualExamination->fieldValues as $fieldValue) {
            foreach (($fieldValue->file_attachments ?? []) as $attachment) {
                if (!empty($attachment['path'])) {
                    $allowedPaths[] = ltrim((string) $attachment['path'], '/');
                }
            }
        }

        return in_array($path, array_unique($allowedPaths), true);
    }
}

