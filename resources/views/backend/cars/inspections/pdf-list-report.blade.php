<!doctype html>
<html dir="{{ $direction }}" lang="ar">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 120px 20px 120px 20px;
        }
        body {
            font-family: "{{ $font_family }}", sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            direction: {{ $direction }};
            text-align: {{ $text_align }};
            margin: 0;
            padding: 0;
        }
        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
        }
        header img {
            max-height: 80px;
        }
        footer {
            position: fixed;
            bottom: -100px;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
        }
        footer img {
            max-height: 80px;
        }
        h2, h3 {
            color: #333;
            margin: 15px 0 5px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 6px;
            vertical-align: top;
        }
        th {
            background-color: #e5e5e5;
            font-weight: bold;
            color: #000;
        }
        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .page-break {
            page-break-before: always;
        }
        .image-container {
            display: inline-block;
            width: 48%;
            margin: 1%;
            text-align: center;
            border: 1px solid #ccc;
            padding: 5px;
            background: #f9f9f9;
        }
        .image-container img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .image-label {
            font-weight: bold;
            font-size: 10px;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    @php
        $headerImage = pdf_safe_image_src('assets/img/one.jpeg');
        $footerImage = pdf_safe_image_src('assets/img/two.jpeg');
    @endphp

    <header>
        @if($headerImage)
            <img src="{{ $headerImage }}" alt="Header">
        @endif
    </header>
    <footer>
        @if($footerImage)
            <img src="{{ $footerImage }}" alt="Footer">
        @endif
    </footer>

    <main>

    @forelse($inspections as $index => $carInspection)
        @if($index > 0)
            <div class="page-break"></div>
        @endif
        @php
            $sectionData = $sectionDataByInspection[$carInspection->id] ?? [];
            $car = $carInspection->car;
            $reportDate = optional($carInspection->completed_at ?? $carInspection->created_at)->format('Y-m-d');
            $centerName = $carInspection->inspector?->shop_name ?? $carInspection->inspector?->user?->name ?? translate('Inspection Center');

            $images = collect();
            if (!empty($car?->main_photo)) {
                $images->push(['url' => uploaded_asset($car->main_photo), 'name' => translate('Main photo')]);
            }
            foreach (array_filter(explode(',', (string) ($car?->photos ?? ''))) as $photoId) {
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
                    $images->push(['url' => $stored, 'name' => $label]);
                }
            }

            $sectionPhotosMap = ($carInspection->metadata ?? [])['section_photos'] ?? [];
            foreach ($sectionPhotosMap as $sectionId => $items) {
                $sectionModel = $carInspection->inspectionType?->sections?->firstWhere('id', (int) $sectionId);
                $sectionLabel = $sectionModel->name ?? translate('Inspection section');
                foreach ((array) $items as $item) {
                    if (!empty($item['path'])) {
                        $images->push(['url' => $item['path'], 'name' => $sectionLabel]);
                    }
                }
            }

            foreach ($carInspection->fieldValues as $fieldValue) {
                foreach (($fieldValue->file_attachments ?? []) as $attachment) {
                    $attachmentUrl = $attachment['url'] ?? ($attachment['path'] ?? null);
                    if (!empty($attachmentUrl)) {
                        $images->push(['url' => $attachmentUrl, 'name' => $fieldValue->field?->name ?? translate('Inspection image')]);
                    }
                }
            }
            
            $reportUrl = manual_examination_report_public_url($carInspection);
            $qrSvg = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(80)->margin(0)->errorCorrection('M')->generate($reportUrl));
            $qrImage = 'data:image/svg+xml;base64,' . $qrSvg;
        @endphp

        <table style="border: 0; margin-bottom: 0;">
            <tr>
                <td style="border: 0; width: 20%; text-align: right; vertical-align: middle;">
                    @php $logoImage = pdf_safe_image_src('assets/img/logo.png'); @endphp
                    @if($logoImage)
                        <img src="{{ $logoImage }}" alt="Logo" style="max-width: 100px; max-height: 80px;">
                    @endif
                </td>
                <td style="border: 0; width: 60%; vertical-align: middle; text-align: center;">
                    <h1 style="margin: 0; color: #000;">تقرير الفحص الفني للمركبة</h1>
                    <p style="margin: 5px 0 0 0;">رقم التقرير: {{ $carInspection->inspection_number }}</p>
                </td>
                <td style="border: 0; width: 20%; text-align: left; vertical-align: middle;">
                    <img src="{{ $qrImage }}" alt="QR Code" style="width: 80px; height: 80px;">
                </td>
            </tr>
        </table>

        <h2>بيانات المركبة</h2>
        <table>
            <tr>
                <th>العلامة التجارية</th>
                <td>{{ $car?->brand?->name ?? '—' }}</td>
                <th>الموديل</th>
                <td>{{ $car?->model?->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>رقم الهيكل</th>
                <td>{{ $car?->vin ?? '—' }}</td>
                <th>لوحة المركبة</th>
                <td>{{ $car?->plate_number ?? '—' }}</td>
            </tr>
            <tr>
                <th>سنة الصنع</th>
                <td>{{ $car?->manufacture_year ?? '—' }}</td>
                <th>المسافة المقطوعة</th>
                <td>{{ $car?->milage ? number_format((float) $car->milage) . ' كم' : '—' }}</td>
            </tr>
        </table>

        <h2>نتائج الفحص</h2>
        <table>
            <tr>
                <th>تاريخ التقرير</th>
                <td>{{ $reportDate }}</td>
                <th>مركز الفحص</th>
                <td>{{ $centerName }}</td>
            </tr>
            <tr>
                <th>التقييم العام</th>
                <td>{{ $carInspection->condition_display ?? '—' }}</td>
                <th>الدرجة الإجمالية</th>
                <td>{{ $carInspection->total_score ?? '—' }}</td>
            </tr>
        </table>

        @foreach($sectionData as $data)
            <h3>{{ $data['section']->name }}</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">بند</th>
                        <th style="width: 50%;">القيمة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['fields'] as $fieldData)
                        @php $value = $fieldData['value']; @endphp
                        <tr>
                            <td>{{ $fieldData['field']->name }}</td>
                            <td>{{ $value?->formatted_value ?? $value?->value ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        @if($images->count() > 0)
            <div class="page-break"></div>
            <h2>صور المركبة المرفقة</h2>
            <table style="border:0; width: 100%;">
                @foreach($images->chunk(3) as $chunk)
                    <tr>
                        @foreach($chunk as $image)
                            @php $imageSrc = pdf_safe_image_src($image['url']); @endphp
                            <td style="border:0; width: 33.33%; text-align: center; padding: 5px;">
                                @if($imageSrc)
                                    <div style="border: 1px solid #ccc; padding: 5px; background: #f9f9f9;">
                                        <img src="{{ $imageSrc }}" alt="" style="max-width: 100%; height: 120px; object-fit: cover;">
                                        <div style="font-weight: bold; font-size: 10px; margin-top: 4px;">{{ $image['name'] }}</div>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                        @for($i = $chunk->count(); $i < 3; $i++)
                            <td style="border:0; width: 33.33%;"></td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        @endif

    @empty
        <p>لا توجد بيانات الفحص.</p>
    @endforelse
    </main>
</body>
</html>
