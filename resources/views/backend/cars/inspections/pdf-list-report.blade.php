<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 26px 30px 42px 30px; }
        body {
            font-family: '<?php echo $font_family ?>';
            direction: <?php echo $direction ?>;
            text-align: <?php echo $text_align ?>;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.45;
        }
        .footer {
            position: fixed;
            bottom: -24px;
            left: 0;
            right: 0;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
        .report { page-break-after: always; }
        .report:last-child { page-break-after: auto; }
        .page-break { page-break-before: always; }
        .hero {
            border-bottom: 4px solid #2563eb;
            padding-bottom: 18px;
            margin-bottom: 22px;
        }
        .logo { height: 62px; margin-bottom: 12px; }
        h1 {
            font-size: 30px;
            margin: 0 0 12px;
            color: #111827;
            font-weight: 800;
        }
        h2 {
            font-size: 18px;
            margin: 0 0 12px;
            color: #111827;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 8px;
        }
        h3 {
            font-size: 15px;
            margin: 18px 0 8px;
            color: #111827;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .meta td {
            border: 1px solid #e5e7eb;
            padding: 9px 10px;
            background: #f9fafb;
        }
        .label {
            display: block;
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 3px;
        }
        .value { font-weight: 700; color: #111827; }
        .section {
            margin: 18px 0;
            page-break-inside: avoid;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table th,
        .info-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            vertical-align: top;
        }
        .info-table th {
            width: 32%;
            background: #f3f4f6;
            font-weight: 800;
            color: #374151;
        }
        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }
        .summary-grid td {
            width: 33.33%;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            padding: 14px;
            text-align: center;
        }
        .summary-number {
            font-size: 22px;
            font-weight: 800;
            color: #1d4ed8;
            display: block;
        }
        .summary-label {
            color: #374151;
            font-size: 11px;
            margin-top: 4px;
            display: block;
        }
        .badge {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 14px;
            background: #dcfce7;
            color: #166534;
            font-weight: 700;
            font-size: 10px;
        }
        .notes {
            background: #fffbeb;
            border: 1px solid #fde68a;
            padding: 12px;
            margin-top: 10px;
        }
        .images-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .images-grid td {
            width: 33.33%;
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: center;
            vertical-align: top;
            page-break-inside: avoid;
        }
        .images-grid img {
            width: 100%;
            height: 135px;
            object-fit: cover;
        }
        .caption {
            color: #6b7280;
            font-size: 10px;
            margin-top: 5px;
            word-break: break-word;
        }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
<div class="footer">{{ translate('Page') }} {PAGENO} {{ translate('of') }} {nbpg} | {{ translate('Generated') }} {{ now()->format('Y-m-d H:i') }}</div>

@forelse($inspections as $carInspection)
    @php
        $sectionData = $sectionDataByInspection[$carInspection->id] ?? [];
        $car = $carInspection->car;
        $sectionPhotosMap = ($carInspection->metadata ?? [])['section_photos'] ?? [];

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
            @if($__heroLogoPdf !== '')
                <img class="logo" src="{{ $__heroLogoPdf }}" alt="{{ get_setting('site_name') }}">
            @endif
            <h1>{{ translate('Vehicle Examination Report') }}</h1>
            <table class="meta">
                <tr>
                    <td><span class="label">{{ translate('Plate Number') }}</span><span class="value">{{ $car?->plate_number ?? translate('N/A') }}</span></td>
                    <td><span class="label">{{ translate('Report Number') }}</span><span class="value">{{ $carInspection->inspection_number }}</span></td>
                    <td><span class="label">{{ translate('Report Date') }}</span><span class="value">{{ optional($carInspection->completed_at ?? $carInspection->created_at)->format('Y-m-d') }}</span></td>
                </tr>
            </table>
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
            <table class="summary-grid">
                <tr>
                    <td><span class="summary-number">{{ $inspectionCount }}</span><span class="summary-label">{{ translate('Number of inspections') }}</span></td>
                    <td><span class="summary-number">{{ $ownerCount }}</span><span class="summary-label">{{ translate('Number of owners') }}</span></td>
                    <td><span class="summary-number">{{ $maxMileage ? number_format((float) $maxMileage) : translate('N/A') }}</span><span class="summary-label">{{ translate('Max mileage') }}</span></td>
                </tr>
                <tr>
                    <td><span class="summary-number">{{ $accidentCount }}</span><span class="summary-label">{{ translate('Accident count') }}</span></td>
                    <td><span class="summary-number">{{ $warrantyStatus }}</span><span class="summary-label">{{ translate('Warranty status') }}</span></td>
                    <td><span class="summary-number">{{ $carInspection->total_score ? number_format((float) $carInspection->total_score, 1) . '%' : translate('N/A') }}</span><span class="summary-label">{{ translate('Overall score') }}</span></td>
                </tr>
            </table>
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
                                @php
                                    $src = pdf_safe_image_src($image['url']);
                                @endphp
                                <td>
                                    @if($src !== '')
                                        <img src="{{ $src }}" alt="{{ $image['name'] }}">
                                    @else
                                        <span class="caption muted">{{ translate('Image unavailable') }}</span>
                                    @endif
                                    <div class="caption">{{ $image['name'] }}</div>
                                </td>
                            @endforeach
                            @for($i = $row->count(); $i < 3; $i++)
                                <td></td>
                            @endfor
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="muted">{{ translate('No images are attached to this examination.') }}</p>
            @endif
        </div>
    </div>
@empty
    <h1>{{ translate('Vehicle Examination Report') }}</h1>
    <p class="muted">{{ translate('No examination records found.') }}</p>
@endforelse
</body>
</html>
