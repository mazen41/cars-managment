@php
    $car = $carInspection->car;
    $reportDate = optional($carInspection->completed_at ?? $carInspection->created_at)->format('Y/m/d');
    $centerName = $carInspection->inspector?->shop_name ?? $carInspection->inspector?->user?->name ?? 'مركز الفحص';
    $centerPhone = $carInspection->inspector?->user?->phone ?? $carInspection->inspector?->phone ?? '—';

    $verificationUrl = $verificationUrl ?? manual_examination_report_public_url($carInspection);
    $qrDataUri = $qrDataUri ?? manual_examination_pdf_qr_data_uri((string) $verificationUrl);

    $headerImage = pdf_safe_image_src('assets/img/one.jpeg');
    $footerImage = pdf_safe_image_src('assets/img/two.jpeg');

    $vehicleImages = collect();
    if (!empty($car?->main_photo)) {
        $vehicleImages->push(['src' => uploaded_asset($car->main_photo), 'label' => 'صورة المركبة الرئيسية']);
    }
    foreach (array_filter(explode(',', (string) ($car?->photos ?? ''))) as $photoId) {
        $vehicleImages->push(['src' => uploaded_asset(trim($photoId)), 'label' => 'معرض المركبة']);
    }

    $manualPhotoSlots = [
        'photo_front' => 'الواجهة الأمامية',
        'photo_back' => 'الواجهة الخلفية',
        'photo_left' => 'الجانب الأيسر',
        'photo_right' => 'الجانب الأيمن',
        'photo_interior_front' => 'المقصورة (أمامي)',
        'photo_interior_back' => 'المقصورة (خلفي)',
        'photo_engine' => 'المحرك',
        'photo_trunk' => 'صندوق الأمتعة',
        'photo_odometer' => 'عداد المسافة',
        'photo_dashboard' => 'لوحة القيادة',
        'photo_vin_plate' => 'لوحة الهيكل',
        'photo_tires' => 'الإطارات',
        'photo_undercarriage' => 'الأسفل / الهيكل السفلي',
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
                    'label' => ($sectionModel->name ?? 'صور القسم'),
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
                    'label' => $fieldValue->field?->name ?? 'مرفق حقل فحص',
                ]);
            }
        }
    }
@endphp
<!doctype html>
<html dir="{{ $direction }}" lang="ar">
<head>
    <meta charset="utf-8">
    <title>تقرير الفحص</title>
    <style>
        @page {
            margin: 60px 20px 60px 20px;
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
            top: -40px;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
        }
        header img {
            max-height: 40px;
        }
        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
        }
        footer img {
            max-height: 40px;
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
                @if(!empty($qrDataUri))
                    <img src="{{ $qrDataUri }}" alt="QR" style="width: 80px; height: 80px;">
                @endif
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

    @if($vehicleImages->count() > 0)
        <div class="page-break"></div>
        <h2>صور المركبة المرفقة</h2>
        <table style="border:0; width: 100%;">
            @foreach($vehicleImages->chunk(3) as $chunk)
                <tr>
                    @foreach($chunk as $image)
                        @php $imageSrc = pdf_safe_image_src($image['src']); @endphp
                        <td style="border:0; width: 33.33%; text-align: center; padding: 5px;">
                            @if($imageSrc)
                                <div style="border: 1px solid #ccc; padding: 5px; background: #f9f9f9;">
                                    <img src="{{ $imageSrc }}" alt="" style="max-width: 100%; height: 120px; object-fit: cover;">
                                    <div style="font-weight: bold; font-size: 10px; margin-top: 4px;">{{ $image['label'] }}</div>
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
</body>
</html>
