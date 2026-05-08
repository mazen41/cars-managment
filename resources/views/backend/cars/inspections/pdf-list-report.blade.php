<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 24px 28px 58px 28px; }
        * { box-sizing: border-box; }
        body {
            font-family: '<?php echo $font_family ?>';
            direction: <?php echo $direction ?>;
            text-align: <?php echo $text_align ?>;
            color: #1f2937;
            font-size: 11.5px;
            line-height: 1.55;
            margin: 0;
            background: #fff;
        }
        .footer {
            position: fixed;
            bottom: -45px;
            left: 0;
            right: 0;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            font-size: 9.5px;
            color: #6b7280;
        }
        .footer-row { width: 100%; }
        .footer-row td { vertical-align: middle; }
        .footer-copy { text-align: center; font-size: 10px; color: #374151; padding-top: 4px; }
        .footer-left { text-align: left; }
        .footer-right { text-align: right; }

        .report { page-break-after: always; }
        .report:last-child { page-break-after: auto; }
        .page-break { page-break-before: always; }

        .hero {
            border: 1px solid #dbeafe;
            background: #f8fbff;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 18px;
        }
        .hero-head { width: 100%; }
        .hero-head td { vertical-align: top; }
        .logo { height: 52px; }
        .report-title { text-align: right; }
        h1 { font-size: 24px; margin: 0; color: #0f172a; font-weight: 800; }
        .subtitle { color: #64748b; font-size: 11px; margin-top: 4px; }

        .company-chip {
            margin-top: 12px;
            background: #ffffff;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            padding: 10px 12px;
        }

        .meta { width: 100%; border-collapse: separate; border-spacing: 8px; margin-top: 12px; }
        .meta td { border: 1px solid #e2e8f0; background: #fff; border-radius: 8px; padding: 10px; }
        .label { color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: .06em; display: block; }
        .value { font-weight: 700; color: #0f172a; margin-top: 2px; display: block; font-size: 12px; }

        h2 {
            font-size: 15px; margin: 0 0 10px; color: #0f172a;
            border-bottom: 1px solid #e5e7eb; padding-bottom: 8px;
        }
        h3 { font-size: 13px; margin: 16px 0 6px; color: #111827; }
        .section {
            margin: 16px 0;
            page-break-inside: avoid;
            border: 1px solid #eef2f7;
            border-radius: 10px;
            padding: 14px;
            background: #fff;
        }

        .info-table { width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; }
        .info-table th, .info-table td { border: 1px solid #e5e7eb; padding: 8px 10px; vertical-align: top; }
        .info-table th { width: 33%; background: #f8fafc; font-weight: 700; color: #334155; }

        .summary-grid { width: 100%; border-collapse: separate; border-spacing: 8px; }
        .summary-grid td {
            width: 33.33%; border: 1px solid #dbeafe; background: linear-gradient(180deg, #f8fbff, #f0f7ff);
            border-radius: 8px; padding: 11px; text-align: center;
        }
        .summary-number { font-size: 19px; font-weight: 800; color: #1d4ed8; display: block; }
        .summary-label { color: #334155; font-size: 10px; margin-top: 4px; display: block; }

        .badge { display:inline-block; padding:4px 10px; border-radius:14px; background:#dcfce7; color:#166534; font-weight:700; font-size:10px; }
        .notes { background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:10px; margin-top:10px; }

        .images-grid { width:100%; border-collapse:separate; border-spacing:9px; }
        .images-grid td { width:33.33%; border:1px solid #e5e7eb; border-radius:8px; padding:7px; text-align:center; vertical-align:top; }
        .images-grid img { width:100%; height:132px; object-fit:cover; border-radius:6px; }
        .caption { color:#64748b; font-size:10px; margin-top:5px; word-break:break-word; }
        .muted { color:#6b7280; }

        .feature-banner {
            margin-top: 12px;
            border: 1px solid #e5e7eb;
            background: #fff;
            border-radius: 10px;
            padding: 8px;
            text-align: center;
        }
        .feature-banner img { width: 100%; max-height: 125px; object-fit: contain; }

        .online-card {
            margin-top: 14px;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            background: #eff6ff;
            padding: 12px;
        }
        .online-card .label-title { font-weight: 800; color: #1e3a8a; margin-bottom: 4px; }
        .online-card a { color: #1d4ed8; text-decoration: underline; font-size: 11px; }
    </style>
</head>
<body>
<div class="footer">
    <table class="footer-row">
        <tr>
            <td class="footer-left">{{ translate('Generated') }} {{ now()->format('Y-m-d H:i') }}</td>
            <td class="footer-right">{{ translate('Page') }} {PAGENO} {{ translate('of') }} {nbpg}</td>
        </tr>
    </table>
    <div class="footer-copy">جميع الحقوق محفوظة لمنصة سمح، ويحظر الاستخدام أو النشر بدون موافقة خطية.</div>
</div>

@forelse($inspections as $carInspection)
    @php
        $sectionData = $sectionDataByInspection[$carInspection->id] ?? [];
        $car = $carInspection->car;
        $sectionPhotosMap = ($carInspection->metadata ?? [])['section_photos'] ?? [];
        $isManual = (bool) ($carInspection->is_manual_examination ?? false);
        $reportPath = $isManual ? 'manual-examinations/' . $carInspection->id : 'inspections/' . $carInspection->id;
        $reportUrl = rtrim(config('app.url'), '/') . '/' . $reportPath;
        $centerName = $carInspection->inspector?->shop_name ?? $carInspection->inspector?->user?->name ?? translate('Inspection Center');
        $centerPhone = $carInspection->inspector?->user?->phone ?? $carInspection->inspector?->phone ?? translate('N/A');
        $importantImageSrc = pdf_safe_image_src('WhatsApp Image 2026-05-08 at 8.21.08 PM (2).jpeg');

        $images = collect();
        if (!empty($car?->main_photo)) {
            $images->push(['url' => uploaded_asset($car->main_photo), 'name' => translate('Main photo')]);
        }
        foreach (array_filter(explode(',', (string) $car?->photos)) as $photoId) {
            $images->push(['url' => uploaded_asset(trim($photoId)), 'name' => translate('Vehicle photo')]);
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
                $images->push(['url' => $stored, 'name' => $label, 'is_disk_path' => true]);
            }
        }

        foreach ($sectionPhotosMap as $sectionId => $items) {
            $sectionModel = $carInspection->inspectionType?->sections?->firstWhere('id', (int) $sectionId);
            $sectionLabel = $sectionModel->name ?? translate('Inspection section');

            foreach ($items as $item) {
                $path = $item['path'] ?? null;
                if (!empty($path)) {
                    $images->push([
                        'url' => $path,
                        'name' => $sectionLabel,
                        'is_disk_path' => true,
                    ]);
                }
            }
        }

        foreach ($carInspection->fieldValues as $fieldValue) {
            foreach (($fieldValue->file_attachments ?? []) as $attachment) {
                if (!empty($attachment['url'])) {
                    $images->push([
                        'url' => $attachment['url'],
                        'name' => $fieldValue->field?->name ?? translate('Inspection image'),
                    ]);
                }
            }
        }
        $inspectionCount = $car?->inspections_count ?? $car?->inspections()->count();
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
            <table class="hero-head">
                <tr>
                    <td>
                        @if($__heroLogoPdf !== '')
                            <img class="logo" src="{{ $__heroLogoPdf }}" alt="{{ get_setting('site_name') }}">
                        @endif
                    </td>
                    <td class="report-title">
                        <h1>{{ translate('Vehicle Examination Report') }}</h1>
                        <div class="subtitle">{{ translate('Professional inspection summary and technical diagnostics') }}</div>
                    </td>
                </tr>
            </table>

            <div class="company-chip">
                <strong>{{ $centerName }}</strong> · {{ translate('Phone') }}: {{ $centerPhone }}
            </div>

            <table class="meta">
                <tr>
                    <td><span class="label">{{ translate('Plate Number') }}</span><span class="value">{{ $car?->plate_number ?? translate('N/A') }}</span></td>
                    <td><span class="label">{{ translate('Report Number') }}</span><span class="value">{{ $carInspection->inspection_number }}</span></td>
                    <td><span class="label">{{ translate('Report Date') }}</span><span class="value">{{ optional($carInspection->completed_at ?? $carInspection->created_at)->format('Y-m-d') }}</span></td>
                </tr>
            </table>

            @if($importantImageSrc !== '')
                <div class="feature-banner">
                    <img src="{{ $importantImageSrc }}" alt="Inspection reference visual">
                </div>
            @endif
        </div>

        <div class="section">
            <h2>{{ translate('Vehicle Information') }}</h2>
            <table class="info-table">
                <tr><th>{{ translate('Manufacturer') }}</th><td>{{ $car?->brand?->name ?? translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Vehicle Type') }}</th><td>{{ $car?->category?->name ?? $carInspection->inspectionType?->name ?? translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Model') }}</th><td>{{ $car?->model?->name ?? translate('N/A') }}</td></tr>
                <tr><th>{{ translate('VIN') }}</th><td>{{ $car?->vin ?? translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Color') }}</th><td>{{ $car?->color?->name ?? translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Engine Size') }}</th><td>{{ $car?->engine_size ?? translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Fuel Type') }}</th><td>{{ $car?->fuel_type ? translate(ucfirst($car->fuel_type)) : translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Transmission') }}</th><td>{{ $car?->transmission ? translate(ucfirst($car->transmission)) : translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Mileage') }}</th><td>{{ $car?->milage ? number_format((float) $car->milage) . ' km' : translate('N/A') }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h2>{{ translate('Summary') }}</h2>
            <table class="summary-grid"><tr>
                <td><span class="summary-number">{{ $inspectionCount }}</span><span class="summary-label">{{ translate('Number of inspections') }}</span></td>
                <td><span class="summary-number">{{ $ownerCount }}</span><span class="summary-label">{{ translate('Number of owners') }}</span></td>
                <td><span class="summary-number">{{ $maxMileage ? number_format((float) $maxMileage) : translate('N/A') }}</span><span class="summary-label">{{ translate('Max mileage') }}</span></td>
            </tr><tr>
                <td><span class="summary-number">{{ $accidentCount }}</span><span class="summary-label">{{ translate('Accident count') }}</span></td>
                <td><span class="summary-number">{{ $warrantyStatus }}</span><span class="summary-label">{{ translate('Warranty status') }}</span></td>
                <td><span class="summary-number">{{ $carInspection->total_score ? number_format((float) $carInspection->total_score, 1) . '%' : translate('N/A') }}</span><span class="summary-label">{{ translate('Overall score') }}</span></td>
            </tr></table>
            @if($carInspection->inspector_notes)
                <div class="notes"><strong>{{ translate('Inspector Notes') }}:</strong> {{ $carInspection->inspector_notes }}</div>
            @endif
            @if($carInspection->recommendations)
                <div class="notes"><strong>{{ translate('Recommendations') }}:</strong> {{ $carInspection->recommendations }}</div>
            @endif
        </div>

        <div class="page-break"></div>
        <div class="section">
            <h2>{{ translate('Technical Specifications') }}</h2>
            <table class="info-table">
                <tr><th>{{ translate('Condition') }}</th><td><span class="badge">{{ $carInspection->condition_display ?? $car?->condition ?? translate('N/A') }}</span></td></tr>
                <tr><th>{{ translate('Inspection Type') }}</th><td>{{ $carInspection->inspectionType?->name ?? translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Inspector') }}</th><td>{{ $carInspection->inspector?->shop_name ?? $carInspection->inspector?->user?->name ?? translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Location') }}</th><td>{{ $car?->location ?? translate('N/A') }}</td></tr>
                <tr><th>{{ translate('Description') }}</th><td>{{ $car?->description ?? translate('N/A') }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h2>{{ translate('History / Records') }}</h2>
            @foreach($sectionData as $data)
                <h3>{{ $data['section']->name }}</h3>
                <table class="info-table">
                    @foreach($data['fields'] as $fieldData)
                        @php $value = $fieldData['value']; @endphp
                        <tr>
                            <th>{{ $fieldData['field']->name }}</th>
                            <td>
                                {{ $value?->formatted_value ?? $value?->value ?? translate('Not completed') }}
                                @if($value?->notes)
                                    <div class="muted">{{ translate('Notes') }}: {{ $value->notes }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endforeach
        </div>

        <div class="section">
            <h2>{{ translate('Damage / Accidents') }}</h2>
            @if($accidentCount > 0)
                <table class="info-table">
                    @foreach($carInspection->fieldValues->where('is_flagged', true) as $flagged)
                        <tr>
                            <th>{{ $flagged->field?->name ?? translate('Flagged item') }}</th>
                            <td>{{ $flagged->flag_reason ?? $flagged->notes ?? translate('Requires review') }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="muted">{{ translate('No accident records were flagged in this examination.') }}</p>
            @endif
        </div>

        <div class="section">
            <h2>{{ translate('Images Section') }}</h2>
            @if($images->count() > 0)
                <table class="images-grid">
                    @foreach($images->chunk(3) as $row)
                        <tr>
                            @foreach($row as $image)
                                @php $src = pdf_safe_image_src($image['url']); @endphp
                                <td>
                                    @if($src !== '') <img src="{{ $src }}" alt="{{ $image['name'] }}"> @else <span class="caption muted">{{ translate('Image unavailable') }}</span> @endif
                                    <div class="caption">{{ $image['name'] }}</div>
                                </td>
                            @endforeach
                            @for($i = $row->count(); $i < 3; $i++) <td></td> @endfor
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="muted">{{ translate('No images are attached to this examination.') }}</p>
            @endif
        </div>

        <div class="online-card">
            <div class="label-title">{{ translate('View Report Online') }} / {{ translate('عرض التقرير الكامل') }}</div>
            <a href="{{ $reportUrl }}">{{ $reportUrl }}</a>
        </div>
    </div>
@empty
    <h1>{{ translate('Vehicle Examination Report') }}</h1>
    <p class="muted">{{ translate('No examination records found.') }}</p>
@endforelse
</body>
</html>
