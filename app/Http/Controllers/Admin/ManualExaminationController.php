<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarInspection;
use App\Models\CarInspector;
use App\Services\ManualExaminationService;
use Illuminate\Http\Request;
use PDF;

class ManualExaminationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_car_inspection');
    }

    public function index(Request $request)
    {
        $query = CarInspection::with([
            'car.brand',
            'car.model',
            'inspector.user',
            'inspectionType',
        ])
            ->manual()
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('inspector')) {
            $query->where('inspector_id', $request->inspector);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inspection_number', 'like', "%{$search}%")
                    ->orWhereHas('car', function ($carQuery) use ($search) {
                        $carQuery->where('vin', 'like', "%{$search}%")
                            ->orWhere('plate_number', 'like', "%{$search}%")
                            ->orWhereHas('brand', function ($brandQuery) use ($search) {
                                $brandQuery->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('model', function ($modelQuery) use ($search) {
                                $modelQuery->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('inspector', function ($inspectorQuery) use ($search) {
                        $inspectorQuery->where('shop_name', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $manualExaminations = $query->paginate(15)->appends($request->query());
        $inspectors = CarInspector::active()->get();
        $statuses = CarInspection::STATUSES;

        return view('backend.cars.manual-examinations.index', compact(
            'manualExaminations',
            'inspectors',
            'statuses'
        ));
    }

    public function show(CarInspection $manualExamination)
    {
        abort_unless($manualExamination->is_manual, 404);

        ['manualExamination' => $manualExamination, 'sectionData' => $sectionData] =
            ManualExaminationService::getFullData((int) $manualExamination->id);

        return view('backend.cars.manual-examinations.show', compact('manualExamination', 'sectionData'));
    }

    public function download(CarInspection $manualExamination)
    {
        abort_unless($manualExamination->is_manual, 404);

        ['manualExamination' => $manualExamination, 'sectionData' => $sectionData] =
            ManualExaminationService::getFullData((int) $manualExamination->id);

        $pdfOptions = get_manual_examination_pdf_options();
        $verificationUrl = manual_examination_report_public_url($manualExamination);
        $qrDataUri = manual_examination_pdf_qr_data_uri($verificationUrl);

        $pdf = PDF::loadView('backend.cars.inspections.manual-pdf-report', [
            'carInspection' => $manualExamination,
            'sectionData' => $sectionData,
            'font_family' => $pdfOptions['font_family'],
            'direction' => $pdfOptions['direction'],
            'text_align' => $pdfOptions['text_align'],
            'not_text_align' => $pdfOptions['not_text_align'],
            'verificationUrl' => $verificationUrl,
            'qrDataUri' => $qrDataUri,
        ])->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => base_path(),
        ]);

        return $pdf->download('manual-examination-' . $manualExamination->id . '.pdf');
    }

    public function photo(CarInspection $manualExamination, string $encodedPath)
    {
        abort_unless($manualExamination->is_manual, 404);

        $path = $this->decodePhotoPath($encodedPath);
        abort_unless($path && $this->photoBelongsToManualExamination($manualExamination, $path), 404);

        $candidates = [
            storage_path('app/public/' . $path),
            storage_path('app/' . $path),
        ];

        if (str_starts_with($path, 'public/')) {
            $candidates[] = storage_path('app/' . $path);
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                return response()->file($candidate);
            }
        }

        abort(404);
    }

    private function decodePhotoPath(string $encodedPath): ?string
    {
        $base64 = strtr($encodedPath, '-_', '+/');
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);
        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', $decoded), '/');

        if ($path === '' || str_contains($path, '../') || str_contains($path, '..\\')) {
            return null;
        }

        return $path;
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

        $manualExamination->loadMissing('fieldValues');
        foreach ($manualExamination->fieldValues as $fieldValue) {
            foreach (($fieldValue->file_attachments ?? []) as $attachment) {
                if (!empty($attachment['path'])) {
                    $allowedPaths[] = ltrim((string) $attachment['path'], '/');
                }
            }
        }

        return in_array($path, array_unique($allowedPaths), true);
    }

    public function schedule(CarInspection $manualExamination)
    {
        abort_unless($manualExamination->is_manual, 404);

        $inspectors = CarInspector::active()->get();

        return view('backend.cars.manual-examinations.schedule', compact('manualExamination', 'inspectors'));
    }

    public function updateSchedule(Request $request, CarInspection $manualExamination)
    {
        abort_unless($manualExamination->is_manual, 404);

        $request->validate([
            'inspector_id' => 'required|exists:car_inspectors,id',
            'scheduled_at' => 'nullable|date',
        ]);

        $manualExamination->update([
            'inspector_id'  => $request->inspector_id,
            'scheduled_at'  => $request->scheduled_at,
        ]);

        flash(translate('Examination scheduled successfully'))->success();
        return redirect()->route('admin.manual-examinations.index');
    }
}
