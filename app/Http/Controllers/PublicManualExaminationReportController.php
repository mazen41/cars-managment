<?php

namespace App\Http\Controllers;

use App\Models\CarInspection;
use Illuminate\Http\Request;

/**
 * Lightweight public entry used by QR codes on manual examination PDFs.
 * Optionally redirects into the inspector / customer SPA; otherwise shows RTL Arabic fallback.
 */
class PublicManualExaminationReportController extends Controller
{
    public function resolve(Request $request, CarInspection $carInspection)
    {
        abort_unless((bool) $carInspection->is_manual, 404);

        $base = rtrim(trim((string) config('app.manual_examination_qr_redirect_base', '')), '/');

        if ($base !== '') {
            return redirect()->away($base . '/manual-examinations/' . $carInspection->id);
        }

        return response()->view('manual-examination-qr-redirect-fallback', [
            'carInspection' => $carInspection,
            'verificationUrl' => manual_examination_report_public_url($carInspection),
        ]);
    }
}
