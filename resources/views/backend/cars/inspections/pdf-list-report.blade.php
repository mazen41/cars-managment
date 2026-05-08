<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 26px 28px 84px 28px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #1f2937;
            background: #ffffff;
            font-family: '<?php echo $font_family ?>';
            direction: <?php echo $direction ?>;
            text-align: <?php echo $text_align ?>;
            font-size: 11px;
            line-height: 1.6;
        }
        .footer {
            position: fixed;
            bottom: -58px;
            left: 0;
            right: 0;
            border-top: 1px solid #dbe3ef;
            padding-top: 8px;
            color: #475569;
            font-size: 9px;
        }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { vertical-align: middle; }
        .footer-center { text-align: center; }
        .footer-end { text-align: right; }
        .footer-copy {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #edf2f7;
            text-align: center;
            color: #334155;
            font-size: 9px;
        }
        .report { page-break-after: always; }
        .report:last-child { page-break-after: auto; }
        .hero {
            border: 1px solid #d8e3f2;
            background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 16px;
        }
        .hero-table,
        .meta-table,
        .stats-table,
        .detail-grid,
        .image-table,
        .qr-table,
        .endcap-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .hero-table td,
        .detail-grid td,
        .qr-table td,
        .endcap-table td { vertical-align: top; }
        .hero-brand {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #d8e3f2;
            color: #1d4ed8;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .logo {
            max-height: 54px;
            max-width: 150px;
            object-fit: contain;
        }
        h1 {
            margin: 10px 0 4px;
            font-size: 24px;
            color: #0f172a;
            font-weight: 800;
        }
        .subtitle {
            color: #64748b;
            font-size: 10px;
        }
        .score-chip {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background: #0f172a;
            color: #ffffff;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
        }
        .meta-table { margin-top: 16px; border-spacing: 10px; }
        .meta-table td {
            width: 33.33%;
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #d9e5f3;
            border-radius: 14px;
        }
        .eyebrow {
            display: block;
            color: #64748b;
            font-size: 8.5px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .value {
            display: block;
            margin-top: 4px;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
        }
        .section-card {
            margin-bottom: 14px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            page-break-inside: avoid;
        }
        .section-title {
            margin: 0 0 12px;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .section-note {
            margin: -4px 0 12px;
            color: #64748b;
            font-size: 10px;
        }
        .stats-table { border-spacing: 10px; }
        .stats-table td {
            width: 33.33%;
            padding: 12px;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: linear-gradient(180deg, #f8fbff 0%, #eff6ff 100%);
            text-align: center;
        }
        .stat-number {
            display: block;
            color: #1d4ed8;
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .stat-label {
            color: #334155;
            font-size: 9px;
        }
        .detail-grid td {
            width: 50%;
            padding: 0 6px 0 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }
        .info-table th,
        .info-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .info-table tr:last-child th,
        .info-table tr:last-child td { border-bottom: 0; }
        .info-table th {
            width: 34%;
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
        }
        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
            font-size: 9px;
            font-weight: 700;
        }
        .note-box {
            margin-top: 10px;
            padding: 11px 12px;
            border: 1px solid #fde68a;
            border-radius: 12px;
            background: #fffbea;
        }
        .note-label {
            display: block;
            color: #92400e;
            font-size: 9px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .records-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            margin-top: 10px;
        }
        .records-table th,
        .records-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .records-table tr:last-child th,
        .records-table tr:last-child td { border-bottom: 0; }
        .records-table th {
            width: 36%;
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
        }
        .muted { color: #64748b; }
        .image-table { border-spacing: 10px; }
        .image-table td {
            width: 50%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            vertical-align: top;
        }
        .image-frame {
            width: 100%;
            height: 180px;
            border-radius: 12px;
            overflow: hidden;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .image-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .image-caption {
            margin-top: 8px;
            font-size: 10px;
            color: #334155;
            font-weight: 700;
        }
        .image-meta {
            margin-top: 3px;
            font-size: 9px;
            color: #64748b;
        }
        .qr-wrap {
            padding: 16px;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: linear-gradient(180deg, #f8fbff 0%, #eff6ff 100%);
        }
        .qr-box {
            width: 128px;
            height: 128px;
            padding: 8px;
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid #d8e3f2;
            text-align: center;
        }
        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .qr-title {
            margin: 0 0 6px;
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
        }
        .qr-subtitle {
            color: #475569;
            font-size: 10px;
            margin-bottom: 6px;
        }
        .disclaimer-card {
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #ffffff;
        }
        .disclaimer-title {
            margin: 0 0 10px;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }
        .disclaimer-item {
            margin-bottom: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .disclaimer-item:last-child { margin-bottom: 0; }
        .disclaimer-label {
            display: block;
            margin-bottom: 4px;
            color: #1e3a8a;
            font-size: 10px;
            font-weight: 800;
        }
        .endcap-table td { vertical-align: middle; }
        .endcap-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-size: 9px;
            font-weight: 700;
        }
        .endcap-image {
            max-width: 110px;
            max-height: 60px;
            object-fit: contain;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            background: #ffffff;
        }
    </style>
</head>
<body>
<div class="footer">
    <table class="footer-table">
        <tr>
            <td>{{ translate('Report Date') }}: {{ now()->format('Y-m-d') }}</td>
            <td class="footer-center">{{ translate('Page') }} {PAGENO} / {nbpg}</td>
            <td class="footer-end">{{ translate('Report Number') }}: {{ $inspections->first()?->inspection_number ?? '-' }}</td>
        </tr>
    </table>
    <div class="footer-copy">جميع الحقوق محفوظة لمنصة سمح، ويحظر الاستخدام أو النشر بدون موافقة خطية.</div>
</div>

@forelse($inspections as $carInspection)
    @php
        $sectionData = $sectionDataByInspection[$carInspection->id] ?? [];
        $car = $carInspection->car;
        $sectionPhotosMap = ($carInspection->metadata ?? [])['section_photos'] ?? [];
        $isManual = (bool) ($carInspection->is_manual ?? false);
        $reportPath = $isManual ? 'manual-examinations/' . $carInspection->id : 'inspections/' . $carInspection->id;
        $reportUrl = rtrim(config('app.url'), '/') . '/' . $reportPath;
        $reportDate = optional($carInspection->completed_at ?? $carInspection->created_at)->format('Y-m-d');
        $centerName = $carInspection->inspector?->shop_name ?? $carInspection->inspector?->user?->name ?? translate('Inspection Center');
        $centerPhone = $carInspection->inspector?->user?->phone ?? $carInspection->inspector?->phone ?? translate('N/A');
        $importantImageSrc = pdf_safe_image_src('WhatsApp Image 2026-05-08 at 8.21.08 PM (2).jpeg');
        $qrSvg = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->margin(1)->errorCorrection('M')->generate($reportUrl));
        $qrImage = 'data:image/svg+xml;base64,' . $qrSvg;

        $images = collect();
        if (!empty($car?->main_photo)) {
            $images->push(['url' => uploaded_asset($car->main_photo), 'name' => translate('Main photo'), 'source' => translate('Vehicle profile')]);
        }
        foreach (array_filter(explode(',', (string) ($car?->photos ?? ''))) as $photoId) {
            $images->push(['url' => uploaded_asset(trim($photoId)), 'name' => translate('Vehicle photo'), 'source' => translate('Vehicle profile')]);
        }

        $manualSlotLabels = [
            'photo_front' => translate('Front view'),
            'photo_back' => translate('Rear view'),
            'photo_left' => translate('Left side'),
            'photo_right' => translate('Right side'),
            'photo_interior_front' => translate('Interior front'),
            'photo_interior_back' => translate('Interior rear'),
            'photo_engine' => translate('Engine'),
            'photo_trunk' => translate('Trunk'),
            'photo_odometer' => translate('Odometer'),
            'photo_dashboard' => translate('Dashboard'),
            'photo_vin_plate' => translate('VIN plate'),
            'photo_tires' => translate('Tires'),
            'photo_undercarriage' => translate('Undercarriage'),
        ];

        foreach ($manualSlotLabels as $column => $label) {
            $stored = $carInspection->{$column} ?? null;
            if (!empty($stored)) {
                $images->push(['url' => $stored, 'name' => $label, 'source' => translate('Manual upload')]);
            }
        }

        foreach ($sectionPhotosMap as $sectionId => $items) {
            $sectionModel = $carInspection->inspectionType?->sections?->firstWhere('id', (int) $sectionId);
            $sectionLabel = $sectionModel->name ?? translate('Inspection section');

            foreach ((array) $items as $item) {
                $path = $item['path'] ?? null;
                if (!empty($path)) {
                    $images->push([
                        'url' => $path,
                        'name' => $sectionLabel,
                        'source' => translate('Section photo'),
                    ]);
                }
            }
        }

        foreach ($carInspection->fieldValues as $fieldValue) {
            foreach (($fieldValue->file_attachments ?? []) as $attachment) {
                $attachmentUrl = $attachment['url'] ?? ($attachment['path'] ?? null);
                if (!empty($attachmentUrl)) {
                    $images->push([
                        'url' => $attachmentUrl,
                        'name' => $fieldValue->field?->name ?? translate('Inspection image'),
                        'source' => translate('Field attachment'),
                    ]);
                }
            }
        }

        $inspectionCount = $car?->inspections_count ?? ($car && method_exists($car, 'inspections') ? $car->inspections()->count() : 0);
        $ownerCount = 1;
        $maxMileage = $car?->milage;
        $accidentCount = $carInspection->fieldValues->filter(function ($value) {
            return $value->is_flagged || str_contains(strtolower((string) $value->field?->name), 'accident');
        })->count();
        $warrantyStatus = $carInspection->overall_condition === 'excellent' || $carInspection->overall_condition === 'good'
            ? translate('Available')
            : translate('Not available');
    @endphp

    <div class="report">
        <div class="hero">
            @php
                $__heroLogo = uploaded_asset(get_setting('site_icon'));
                $__heroLogoPdf = $__heroLogo ? pdf_safe_image_src($__heroLogo) : '';
            @endphp
            <table class="hero-table">
                <tr>
                    <td width="24%">
                        @if($__heroLogoPdf !== '')
                            <img class="logo" src="{{ $__heroLogoPdf }}" alt="{{ get_setting('site_name') }}">
                        @endif
                    </td>
                    <td width="76%" style="text-align: {{ $text_align }};">
                        <span class="hero-brand">{{ get_setting('site_name') ?: 'SAMH' }}</span>
                        <h1>{{ translate('Vehicle Examination Report') }}</h1>
                        <div class="subtitle">{{ translate('Professional inspection summary and technical diagnostics') }}</div>
                        <div class="score-chip">
                            {{ translate('Overall Score') }}:
                            {{ $carInspection->total_score ? number_format((float) $carInspection->total_score, 1) . '%' : translate('N/A') }}
                        </div>
                    </td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td>
                        <span class="eyebrow">{{ translate('Report Number') }}</span>
                        <span class="value">{{ $carInspection->inspection_number }}</span>
                    </td>
                    <td>
                        <span class="eyebrow">{{ translate('Report Date') }}</span>
                        <span class="value">{{ $reportDate }}</span>
                    </td>
                    <td>
                        <span class="eyebrow">{{ translate('Inspection Center') }}</span>
                        <span class="value">{{ $centerName }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-card">
            <div class="section-title">{{ translate('Executive Summary') }}</div>
            <table class="stats-table">
                <tr>
                    <td>
                        <span class="stat-number">{{ $inspectionCount }}</span>
                        <span class="stat-label">{{ translate('Number of inspections') }}</span>
                    </td>
                    <td>
                        <span class="stat-number">{{ $ownerCount }}</span>
                        <span class="stat-label">{{ translate('Number of owners') }}</span>
                    </td>
                    <td>
                        <span class="stat-number">{{ $maxMileage ? number_format((float) $maxMileage) : translate('N/A') }}</span>
                        <span class="stat-label">{{ translate('Mileage') }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="stat-number">{{ $accidentCount }}</span>
                        <span class="stat-label">{{ translate('Accident count') }}</span>
                    </td>
                    <td>
                        <span class="stat-number">{{ $warrantyStatus }}</span>
                        <span class="stat-label">{{ translate('Warranty status') }}</span>
                    </td>
                    <td>
                        <span class="stat-number">{{ $carInspection->condition_display ?? translate('N/A') }}</span>
                        <span class="stat-label">{{ translate('Overall condition') }}</span>
                    </td>
                </tr>
            </table>

            @if($carInspection->inspector_notes)
                <div class="note-box">
                    <span class="note-label">{{ translate('Inspector Notes') }}</span>
                    {{ $carInspection->inspector_notes }}
                </div>
            @endif

            @if($carInspection->recommendations)
                <div class="note-box">
                    <span class="note-label">{{ translate('Recommendations') }}</span>
                    {{ $carInspection->recommendations }}
                </div>
            @endif
        </div>

        <table class="detail-grid">
            <tr>
                <td>
                    <div class="section-card">
                        <div class="section-title">{{ translate('Vehicle Information') }}</div>
                        <table class="info-table">
                            <tr><th>{{ translate('Manufacturer') }}</th><td>{{ $car?->brand?->name ?? translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('Model') }}</th><td>{{ $car?->model?->name ?? translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('Vehicle Type') }}</th><td>{{ $car?->category?->name ?? $carInspection->inspectionType?->name ?? translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('VIN') }}</th><td>{{ $car?->vin ?? translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('Plate Number') }}</th><td>{{ $car?->plate_number ?? translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('Color') }}</th><td>{{ $car?->color?->name ?? translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('Description') }}</th><td>{{ $car?->description ?? translate('N/A') }}</td></tr>
                        </table>
                    </div>
                </td>
                <td>
                    <div class="section-card">
                        <div class="section-title">{{ translate('Technical Specifications') }}</div>
                        <table class="info-table">
                            <tr><th>{{ translate('Condition') }}</th><td><span class="pill">{{ $carInspection->condition_display ?? $car?->condition ?? translate('N/A') }}</span></td></tr>
                            <tr><th>{{ translate('Inspection Type') }}</th><td>{{ $carInspection->inspectionType?->name ?? translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('Report Date') }}</th><td>{{ $reportDate }}</td></tr>
                            <tr><th>{{ translate('Fuel Type') }}</th><td>{{ $car?->fuel_type ? translate(ucfirst($car->fuel_type)) : translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('Transmission') }}</th><td>{{ $car?->transmission ? translate(ucfirst($car->transmission)) : translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('Mileage') }}</th><td>{{ $car?->milage ? number_format((float) $car->milage) . ' km' : translate('N/A') }}</td></tr>
                            <tr><th>{{ translate('Contact') }}</th><td>{{ $centerPhone }}</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-card">
            <div class="section-title">{{ translate('History / Records') }}</div>
            @foreach($sectionData as $data)
                <div style="margin-bottom: 12px;">
                    <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px;">{{ $data['section']->name }}</div>
                    @if(!empty($data['section']->description))
                        <div class="section-note">{{ $data['section']->description }}</div>
                    @endif
                    <table class="records-table">
                        @foreach($data['fields'] as $fieldData)
                            @php $value = $fieldData['value']; @endphp
                            <tr>
                                <th>{{ $fieldData['field']->name }}</th>
                                <td>
                                    {{ $value?->formatted_value ?? $value?->value ?? translate('Not completed') }}
                                    @if($value?->notes)
                                        <div class="muted">{{ translate('Notes') }}: {{ $value->notes }}</div>
                                    @endif
                                    @if($value?->score !== null)
                                        <div class="muted">{{ translate('Score') }}: {{ number_format((float) $value->score, 1) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endforeach
        </div>

        <div class="section-card">
            <div class="section-title">{{ translate('Damage / Accidents') }}</div>
            @if($accidentCount > 0)
                <table class="records-table">
                    @foreach($carInspection->fieldValues->where('is_flagged', true) as $flagged)
                        <tr>
                            <th>{{ $flagged->field?->name ?? translate('Flagged item') }}</th>
                            <td>{{ $flagged->flag_reason ?? $flagged->notes ?? translate('Requires review') }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="muted">{{ translate('No accident records were flagged in this examination.') }}</div>
            @endif
        </div>

        <div class="section-card">
            <div class="section-title">{{ translate('Images Section') }}</div>
            @if($images->count() > 0)
                <table class="image-table">
                    @foreach($images->chunk(2) as $row)
                        <tr>
                            @foreach($row as $image)
                                @php $src = pdf_safe_image_src($image['url']); @endphp
                                <td>
                                    <div class="image-frame">
                                        @if($src !== '')
                                            <img src="{{ $src }}" alt="{{ $image['name'] }}">
                                        @else
                                            <div style="padding-top: 76px;" class="muted">{{ translate('Image unavailable') }}</div>
                                        @endif
                                    </div>
                                    <div class="image-caption">{{ $image['name'] }}</div>
                                    <div class="image-meta">{{ $image['source'] }}</div>
                                </td>
                            @endforeach
                            @if($row->count() < 2)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="muted">{{ translate('No images are attached to this examination.') }}</div>
            @endif
        </div>

        <div class="section-card">
            <table class="qr-table">
                <tr>
                    <td width="36%">
                        <div class="qr-box">
                            <img src="{{ $qrImage }}" alt="QR Code">
                        </div>
                    </td>
                    <td width="64%">
                        <div class="qr-wrap">
                            <div class="qr-title">عرض التقرير الكامل</div>
                            <div class="qr-subtitle">Scan to View Report</div>
                            <div class="muted">{{ translate('Scan the QR code to open the examination details page directly.') }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="disclaimer-card">
            <div class="disclaimer-title">إخلاء المسؤولية</div>
            <div class="disclaimer-item">
                <span class="disclaimer-label">نطاق الفحص</span>
                يقتصر هذا الفحص بشكل صارم على العناصر والمكونات المدرجة صراحةً في هذا التقرير، ولا يشمل أي أجزاء أو أنظمة أخرى لم يتم ذكرها.
            </div>
            <div class="disclaimer-item">
                <span class="disclaimer-label">الأعطال المتغيرة</span>
                لا يغطي هذا الفحص الأعطال أو المشاكل المتقطعة التي قد لا تظهر وقت إجراء عملية الفحص الفني.
            </div>
            <div class="disclaimer-item">
                <span class="disclaimer-label">حالة المركبة</span>
                النتائج تعكس حالة السيارة بناءً على المعاينة البصرية والاختبارات التي تمت في تاريخ الفحص المذكور. يُنصح دائماً بتجربة القيادة والفحص الميداني قبل إتمام عملية الشراء.
            </div>
        </div>

        <div class="section-card" style="margin-top: 14px;">
            <table class="endcap-table">
                <tr>
                    <td width="72%">
                        <span class="endcap-badge">{{ translate('Prepared by') }} {{ $centerName }}</span>
                        <div style="margin-top: 8px; color: #475569;">
                            {{ translate('This report keeps the original inspection data intact while presenting it in a production-ready visual format.') }}
                        </div>
                    </td>
                    <td width="28%" style="text-align: right;">
                        @if($importantImageSrc !== '')
                            <img class="endcap-image" src="{{ $importantImageSrc }}" alt="Inspection reference visual">
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
@empty
    <h1>{{ translate('Vehicle Examination Report') }}</h1>
    <p class="muted">{{ translate('No examination records found.') }}</p>
@endforelse
</body>
</html>
