@php
    $car = $carInspection->car;
    $reportDate = optional($carInspection->completed_at ?? $carInspection->created_at)->format('Y-m-d');
    $centerName = $carInspection->inspector?->shop_name ?? $carInspection->inspector?->user?->name ?? translate('Inspection Center');
    $centerPhone = $carInspection->inspector?->user?->phone ?? $carInspection->inspector?->phone ?? translate('N/A');
    $whatsappImageSrc = pdf_safe_image_src('assets/img/WhatsApp Image 2026-05-08 at 8.21.08 PM (2).jpeg');

    $vehicleImages = collect();
    if (!empty($car?->main_photo)) {
        $vehicleImages->push(['src' => uploaded_asset($car->main_photo), 'label' => translate('Main photo')]);
    }
    foreach (array_filter(explode(',', (string) ($car?->photos ?? ''))) as $photoId) {
        $vehicleImages->push(['src' => uploaded_asset(trim($photoId)), 'label' => translate('Vehicle photo')]);
    }

    $manualPhotoSlots = [
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

    foreach ($manualPhotoSlots as $column => $label) {
        if (!empty($carInspection->{$column})) {
            $vehicleImages->push(['src' => $carInspection->{$column}, 'label' => $label]);
        }
    }

    $sectionPhotosMap = ($carInspection->metadata ?? [])['section_photos'] ?? [];
    foreach ($sectionPhotosMap as $sectionId => $items) {
        $sectionModel = $carInspection->inspectionType?->sections?->firstWhere('id', (int) $sectionId);
        foreach ((array) $items as $item) {
            if (!empty($item['path'])) {
                $vehicleImages->push([
                    'src' => $item['path'],
                    'label' => ($sectionModel->name ?? translate('Section photo')),
                ]);
            }
        }
    }

    foreach (($carInspection->fieldValues ?? collect()) as $fieldValue) {
        foreach (($fieldValue->file_attachments ?? []) as $attachment) {
            $path = $attachment['url'] ?? $attachment['path'] ?? null;
            if ($path) {
                $vehicleImages->push([
                    'src' => $path,
                    'label' => $fieldValue->field?->name ?? translate('Field photo'),
                ]);
            }
        }
    }
@endphp
<!doctype html>
<html dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <title>{{ translate('Manual Examination Report') }}</title>
    <style>
        @page { margin: 28px 30px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #172033;
            background: #ffffff;
            font-family: "{{ $font_family }}";
            direction: {{ $direction }};
            text-align: {{ $text_align }};
            font-size: 11px;
            line-height: 1.55;
        }
        .header {
            border: 1px solid #d8e3ef;
            background: #f7fafc;
            padding: 16px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .header-table,
        .meta-table,
        .info-table,
        .image-table,
        .fields-table,
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-title {
            margin: 0 0 4px;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
        }
        .muted { color: #64748b; }
        .badge {
            display: inline-block;
            padding: 5px 9px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
        }
        .section {
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .section-title {
            margin: 0 0 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
            color: #0f172a;
            font-size: 15px;
            font-weight: 800;
        }
        .meta-table td {
            width: 25%;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .label {
            display: block;
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .value {
            display: block;
            margin-top: 3px;
            color: #111827;
            font-weight: 700;
        }
        .info-table th,
        .info-table td,
        .fields-table th,
        .fields-table td {
            padding: 7px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .info-table th,
        .fields-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: 800;
        }
        .image-table { margin-top: 10px; }
        .image-table td {
            width: 50%;
            padding: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .image-box {
            height: 155px;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            text-align: center;
            overflow: hidden;
        }
        .image-box img {
            width: 100%;
            height: 155px;
            object-fit: cover;
        }
        .caption {
            margin-top: 5px;
            font-weight: 700;
            color: #334155;
        }
        .page-break { page-break-after: always; }
        .notes-box,
        .disclaimer-box {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            margin-bottom: 9px;
        }
        .footer {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 2px solid #e2e8f0;
            page-break-inside: avoid;
        }
        .footer-image {
            max-width: 140px;
            max-height: 80px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td width="70%">
                    <h1 class="header-title">{{ translate('Manual Examination Report') }}</h1>
                    <div class="muted">{{ translate('Report Number') }}: {{ $carInspection->inspection_number }}</div>
                </td>
                <td width="30%" style="text-align: {{ $not_text_align }};">
                    <span class="badge">{{ $carInspection->status_display }}</span>
                </td>
            </tr>
        </table>
        <table class="meta-table" style="margin-top: 12px;">
            <tr>
                <td><span class="label">{{ translate('Report Date') }}</span><span class="value">{{ $reportDate }}</span></td>
                <td><span class="label">{{ translate('Inspection Center') }}</span><span class="value">{{ $centerName }}</span></td>
                <td><span class="label">{{ translate('Contact') }}</span><span class="value">{{ $centerPhone }}</span></td>
                <td><span class="label">{{ translate('Score') }}</span><span class="value">{{ $carInspection->total_score ?? translate('N/A') }}</span></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">{{ translate('Cars Section') }}</h2>
        <table class="info-table">
            <tr>
                <th>{{ translate('Brand') }}</th>
                <td>{{ $car?->brand?->name ?? translate('N/A') }}</td>
                <th>{{ translate('Model') }}</th>
                <td>{{ $car?->model?->name ?? translate('N/A') }}</td>
            </tr>
            <tr>
                <th>{{ translate('VIN') }}</th>
                <td>{{ $car?->vin ?? translate('N/A') }}</td>
                <th>{{ translate('Plate Number') }}</th>
                <td>{{ $car?->plate_number ?? translate('N/A') }}</td>
            </tr>
            <tr>
                <th>{{ translate('Manufacture Year') }}</th>
                <td>{{ $car?->manufacture_year ?? translate('N/A') }}</td>
                <th>{{ translate('Mileage') }}</th>
                <td>{{ $car?->milage ? number_format((float) $car->milage) . ' km' : translate('N/A') }}</td>
            </tr>
            <tr>
                <th>{{ translate('Color') }}</th>
                <td>{{ $car?->color?->name ?? translate('N/A') }}</td>
                <th>{{ translate('Condition') }}</th>
                <td>{{ $car?->condition ? translate(ucfirst($car->condition)) : translate('N/A') }}</td>
            </tr>
            <tr>
                <th>{{ translate('Transmission') }}</th>
                <td>{{ $car?->transmission ?? translate('N/A') }}</td>
                <th>{{ translate('Fuel Type') }}</th>
                <td>{{ $car?->fuel_type ?? translate('N/A') }}</td>
            </tr>
        </table>

        @if($vehicleImages->count() > 0)
            <table class="image-table">
                @foreach($vehicleImages->chunk(2) as $row)
                    <tr>
                        @foreach($row as $image)
                            @php $imageSrc = pdf_safe_image_src($image['src']); @endphp
                            <td>
                                <div class="image-box">
                                    @if($imageSrc !== '')
                                        <img src="{{ $imageSrc }}" alt="{{ $image['label'] }}">
                                    @else
                                        <div class="muted" style="padding-top: 65px;">{{ translate('Image unavailable') }}</div>
                                    @endif
                                </div>
                                <div class="caption">{{ $image['label'] }}</div>
                            </td>
                        @endforeach
                        @if($row->count() < 2)
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @else
            <div class="notes-box muted">{{ translate('No images are attached to this examination.') }}</div>
        @endif
    </div>

    <div class="page-break"></div>

    <div class="section">
        <h2 class="section-title">{{ translate('Examination Details') }}</h2>
        <table class="info-table">
            <tr>
                <th>{{ translate('Inspection Type') }}</th>
                <td>{{ $carInspection->inspectionType?->name ?? translate('N/A') }}</td>
                <th>{{ translate('Overall Condition') }}</th>
                <td>{{ $carInspection->condition_display ?? translate('N/A') }}</td>
            </tr>
            <tr>
                <th>{{ translate('Inspector Notes') }}</th>
                <td colspan="3">{{ $carInspection->inspector_notes ?? translate('N/A') }}</td>
            </tr>
            <tr>
                <th>{{ translate('Recommendations') }}</th>
                <td colspan="3">{{ $carInspection->recommendations ?? translate('N/A') }}</td>
            </tr>
        </table>

        @foreach($sectionData as $data)
            <div class="section">
                <h3 class="section-title">{{ $data['section']->name }}</h3>
                @if(!empty($data['section']->description))
                    <div class="muted" style="margin-bottom: 6px;">{{ $data['section']->description }}</div>
                @endif
                <table class="fields-table">
                    <thead>
                        <tr>
                            <th width="28%">{{ translate('Field') }}</th>
                            <th width="34%">{{ translate('Value') }}</th>
                            <th width="12%">{{ translate('Score') }}</th>
                            <th width="26%">{{ translate('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['fields'] as $fieldData)
                            @php $value = $fieldData['value']; @endphp
                            <tr>
                                <td>{{ $fieldData['field']->name }}</td>
                                <td>{{ $value?->formatted_value ?? $value?->value ?? translate('Not completed') }}</td>
                                <td>{{ $value?->score ?? translate('N/A') }}</td>
                                <td>
                                    {{ $value?->notes ?? translate('N/A') }}
                                    @if($value?->is_flagged)
                                        <div class="muted">{{ translate('Flagged') }}: {{ $value->flag_reason ?? translate('Requires review') }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <div class="section">
        <h2 class="section-title">{{ translate('Disclaimer Section') }}</h2>
        <div class="disclaimer-box">
            {{ translate('This manual examination is limited to the items explicitly listed in this report and reflects the vehicle condition at the inspection time.') }}
        </div>
        <div class="disclaimer-box">
            {{ translate('Intermittent or hidden issues may not appear during visual inspection. A road test and independent mechanical review are recommended before purchase.') }}
        </div>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td width="72%">
                    <strong>{{ translate('Prepared by') }} {{ $centerName }}</strong><br>
                    <span class="muted">{{ translate('Generated on') }} {{ now()->format('Y-m-d H:i') }}</span>
                </td>
                <td width="28%" style="text-align: {{ $not_text_align }};">
                    @if($whatsappImageSrc !== '')
                        <img class="footer-image" src="{{ $whatsappImageSrc }}" alt="WhatsApp">
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
