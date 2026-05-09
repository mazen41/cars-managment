@php
    $car = $carInspection->car;
    $reportDate = optional($carInspection->completed_at ?? $carInspection->created_at)->format('Y/m/d');
    $centerName = $carInspection->inspector?->shop_name ?? $carInspection->inspector?->user?->name ?? 'مركز الفحص';
    $centerPhone = $carInspection->inspector?->user?->phone ?? $carInspection->inspector?->phone ?? '—';

    $verificationUrl = $verificationUrl ?? manual_examination_report_public_url($carInspection);
    $qrDataUri = $qrDataUri ?? manual_examination_pdf_qr_data_uri((string) $verificationUrl);

    $whatsappFooterSrc = pdf_safe_image_src('assets/img/WhatsApp Image 2026-05-08 at 8.21.08 PM (2).jpeg');

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
    <title>تقرير الفحص اليدوي</title>
    <style>
        @page {
            margin: 22mm 24mm;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            color: #0f172a;
            background: #ffffff;
            font-family: "{{ $font_family }}", serif;
            direction: {{ $direction }};
            text-align: {{ $text_align }};
            font-size: 11px;
            line-height: 1.62;
            -webkit-font-smoothing: antialiased;
        }
        .pdf-root { position: relative; }
        table { border-collapse: collapse; border-spacing: 0; }
        .w-full { width: 100%; }
        .accent-bar {
            height: 4px;
            background: linear-gradient(90deg, #0ea5e9, #0369a1);
            margin-bottom: 12px;
        }
        .hero {
            padding: 4px 0 14px;
            page-break-inside: avoid;
        }
        .hero-title {
            margin: 0 0 2px;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .hero-sub {
            color: #64748b;
            font-size: 10.5px;
            margin: 4px 0 0;
        }
        .qr-wrap {
            text-align: center;
            vertical-align: top;
            padding: {{ $direction === 'rtl' ? '0 14px 0 0' : '0 0 0 14px' }};
            page-break-inside: avoid;
        }
        .qr-box {
            display: inline-block;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: linear-gradient(180deg, #f8fafc, #ffffff);
        }
        .qr-box img {
            width: 86px;
            height: 86px;
            display: block;
        }
        .qr-caption {
            margin-top: 6px;
            font-size: 9px;
            color: #64748b;
            font-weight: 700;
        }
        .verify-url {
            margin-top: 4px;
            font-size: 7.5px;
            color: #94a3b8;
            word-break: break-all;
            direction: ltr;
            text-align: left;
            max-width: 180px;
        }
        .meta-grid {
            width: 100%;
            margin-top: 12px;
        }
        .meta-grid td {
            width: 25%;
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            vertical-align: top;
            background: #fafafb;
            page-break-inside: avoid;
        }
        .label {
            font-size: 8.5px;
            font-weight: 800;
            color: #64748b;
            text-transform: none;
            display: block;
            margin-bottom: 3px;
        }
        .value {
            font-size: 11px;
            font-weight: 800;
            color: #111827;
        }
        .section {
            margin-top: 14px;
        }
        .section-heading {
            margin: 18px 0 9px;
            padding: 6px 0 8px;
            border-bottom: 2px solid #cbd5e1;
            font-size: 14.5px;
            font-weight: 800;
            color: #0f172a;
            page-break-after: avoid;
        }
        .info-table td,
        .info-table th,
        .fields-table td,
        .fields-table th {
            border: 1px solid #e2e8f0;
            padding: 8px;
            vertical-align: top;
        }
        .info-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            width: 16%;
            white-space: nowrap;
        }
        .muted { color: #64748b; }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d40af;
            font-weight: 800;
            font-size: 10px;
        }
        .image-grid {
            width: 100%;
            margin-top: 11px;
        }
        .image-grid td {
            width: 50%;
            vertical-align: top;
            padding: 7px;
            border: 1px solid #e2e8f0;
            page-break-inside: avoid;
        }
        .image-frame {
            height: 150px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            overflow: hidden;
            border-radius: 6px;
        }
        .image-frame img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .image-cap {
            margin-top: 5px;
            font-weight: 800;
            color: #334155;
            font-size: 10px;
        }
        .fields-table thead th {
            background: #0f172a;
            color: #fff;
            font-weight: 800;
            font-size: 10px;
            border-color: #0f172a;
        }
        .fields-table tbody tr:nth-child(even) td {
            background: #fbfbfd;
        }
        .page-break-before {
            page-break-before: always;
        }
        .disc-block {
            margin-bottom: 8px;
            padding: 10px 13px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            text-align: {{ $text_align }};
            page-break-inside: avoid;
        }
        .disc-title {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .footer-meta {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px dashed #cbd5e1;
            page-break-inside: avoid;
        }
        .annex-close {
            margin-top: 20px;
            padding-top: 14px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            page-break-inside: avoid;
        }
        .annex-img {
            width: 64px;
            max-height: 48px;
            height: auto;
            object-fit: contain;
            display: inline-block;
        }
        .ltr-crop { direction: ltr; text-align: left; unicode-bidi: embed; font-weight: 700; }
        .nested-section { margin-top: 14px; page-break-inside: avoid; }
    </style>
</head>
<body class="pdf-root">
    <div class="accent-bar"></div>

    <div class="hero">
        <table class="w-full">
            <tr>
                <td style="width:74%;vertical-align:top;">
                    <h1 class="hero-title">تقرير الفحص الفني اليدوي للمركبة</h1>
                    <p class="hero-sub">رقم التقرير: <strong>{{ $carInspection->inspection_number }}</strong></p>
                </td>
                <td class="qr-wrap" style="width:26%;">
                    <div class="qr-box">
                        @if(!empty($qrDataUri))
                            <img src="{{ $qrDataUri }}" alt="QR">
                        @endif
                        <div class="qr-caption">رمز QR للتحقق من التقرير</div>
                        <div class="verify-url">{{ $verificationUrl }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="meta-grid">
            <tr>
                <td><span class="label">تاريخ التقرير</span><span class="value">{{ $reportDate }}</span></td>
                <td><span class="label">مركز الفحص</span><span class="value">{{ $centerName }}</span></td>
                <td><span class="label">التواصل</span><span class="value ltr-crop">{{ $centerPhone }}</span></td>
                <td style="text-align:center;">
                    <span class="label">الدرجة الإجمالية</span><br>
                    <span class="badge">{{ $carInspection->total_score ?? '—' }}</span>
                    <div class="muted" style="margin-top:6px;font-size:9px;">{{ $carInspection->status_display }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2 class="section-heading">١. بيانات المركبة</h2>
        <table class="w-full info-table">
            <tr>
                <th>العلامة التجارية</th>
                <td>{{ $car?->brand?->name ?? '—' }}</td>
                <th>الموديل</th>
                <td>{{ $car?->model?->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>رقم الهيكل</th>
                <td class="ltr-crop">{{ $car?->vin ?? '—' }}</td>
                <th>لوحة المركبة</th>
                <td class="ltr-crop">{{ $car?->plate_number ?? '—' }}</td>
            </tr>
            <tr>
                <th>سنة الصنع</th>
                <td>{{ $car?->manufacture_year ?? '—' }}</td>
                <th>المسافة المقطوعة</th>
                <td>{{ $car?->milage ? number_format((float) $car->milage) . ' كم' : '—' }}</td>
            </tr>
            <tr>
                <th>اللون</th>
                <td>{{ $car?->color?->name ?? '—' }}</td>
                <th>حالة المركبة</th>
                <td>{{ $car?->condition ? ($car->condition === 'new' ? 'جديدة' : 'مستعملة') : '—' }}</td>
            </tr>
            <tr>
                <th>ناقل الحركة</th>
                <td>{{ $car?->transmission ?? '—' }}</td>
                <th>نوع الوقود</th>
                <td>{{ $car?->fuel_type ?? '—' }}</td>
            </tr>
        </table>

        @if($vehicleImages->count() > 0)
            <table class="image-grid">
                @foreach($vehicleImages->chunk(2) as $row)
                    <tr>
                        @foreach($row as $image)
                            @php $imageSrc = pdf_safe_image_src($image['src']); @endphp
                            <td>
                                <div class="image-frame">
                                    @if($imageSrc !== '')
                                        <img src="{{ $imageSrc }}" alt="">
                                    @else
                                        <div class="muted" style="padding:60px;text-align:center;">الصورة غير متوفرة</div>
                                    @endif
                                </div>
                                <div class="image-cap">{{ $image['label'] }}</div>
                            </td>
                        @endforeach
                        @if($row->count() < 2)
                            <td>&nbsp;</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @else
            <div class="disc-block muted">لا توجد صور مرفقة بهذا التقرير.</div>
        @endif
    </div>

    <div class="section page-break-before">
        <h2 class="section-heading">٢. نتائج الفحص</h2>
        <table class="w-full info-table" style="margin-bottom:14px;">
            <tr>
                <th>نوع الفحص</th>
                <td>{{ $carInspection->inspectionType?->name ?? '—' }}</td>
                <th>التقييم العام للحالة</th>
                <td>{{ $carInspection->condition_display ?? '—' }}</td>
            </tr>
            <tr>
                <th>ملاحظات المفتش</th>
                <td colspan="3">{{ $carInspection->inspector_notes ?? '—' }}</td>
            </tr>
            <tr>
                <th>التوصيات</th>
                <td colspan="3">{{ $carInspection->recommendations ?? '—' }}</td>
            </tr>
        </table>

        @foreach($sectionData as $data)
            <div class="nested-section">
                <h3 style="margin:10px 0 6px;font-size:13px;font-weight:800;color:#0f172a;">{{ $data['section']->name }}</h3>
                @if(!empty($data['section']->description))
                    <div class="muted" style="margin-bottom:6px;">{{ $data['section']->description }}</div>
                @endif
                <table class="w-full fields-table">
                    <thead>
                        <tr>
                            <th style="width:26%;">البند</th>
                            <th style="width:38%;">القيمة / المشاهدة</th>
                            <th style="width:12%;">الدرجة</th>
                            <th style="width:24%;">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['fields'] as $fieldData)
                            @php $value = $fieldData['value']; @endphp
                            <tr>
                                <td>{{ $fieldData['field']->name }}</td>
                                <td>{{ $value?->formatted_value ?? $value?->value ?? 'غير محدّد' }}</td>
                                <td>{{ $value?->score ?? '—' }}</td>
                                <td>
                                    {{ $value?->notes ?? '—' }}
                                    @if($value?->is_flagged)
                                        <div class="muted" style="margin-top:3px;color:#b91c1c;">تنبيه: {{ $value->flag_reason ?? 'يتطلب مراجعة إضافية' }}</div>
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
        <h2 class="section-heading">٣. إخلاء مسؤولية</h2>
        <div class="disc-block">
            <div class="disc-title">أولًا – نطاق التقرير</div>
            <div>
                يقتصر هذا التقرير على البنود والعناصر الواردة صراحةً في هذا المستند، ويعكس حالة المركبة في وقت
                الإجراء فقط وفق آلية الفحص البصري المتاحة لمركز الفحص وقت التنفيذ، ولا يُعد بديلًا عن تشخيص ميكانيكي
                كامل أو اختبار أداء على الطريق أو فحص داخلي عميق للإطارات والفرامل والمحرك ومستويات السوائل وجميع مكوّناتها الداخلية.
            </div>
        </div>
        <div class="disc-block">
            <div class="disc-title">ثانيًا – الأعطال المخفية والمتقطعة</div>
            <div>
                قد لا تظهر بعض الأعطال أو الانحرافات بسبب ظروف التشغيل أو درجة الحرارة أو عدد المرات المعتادة
                أثناء جلسة الفحص المرئي، وبعض المشاكل الكهربائية أو التشخيصية تتطلّب معدات متخصّصة ووقت تشغيل
                أطول لاكتشافها.
            </div>
        </div>
        <div class="disc-block">
            <div class="disc-title">ثالثًا – التوصية للمشتري أو المستخدم</div>
            <div>
                يُنصح باختبار المركبة على الطريق وفحص مستقل لدى فني مختص قبل الشراء أو الاعتماد التشغيلي النهائي،
                وذلك لتقليل المخاطر المرتبطة بالأجزاء غير المرئية أو الإصلاحات السابقة وغير المعلنة. مركز الفحص
                غير مسؤول عن أي قرار شراء يُتخذ اعتماداً حصريًا على هذا التقرير دون المراجعة الشخصية.
            </div>
        </div>
        <div class="disc-block">
            <div class="disc-title">رابعًا – دقة الوثيقة</div>
            <div>
                صُنع هذا المستند إلكترونيًا وفقًا لبيانات الإدخال المتاحة في النظام وقت الإصدار. أي تعديل لاحِق خارج
                قنوات المركز المعتمدة لا يُعتمد قانونيًا. للتحقق من صحة النسخة يُمكن مسح الرمز أعلى هذا التقرير.
            </div>
        </div>
    </div>

    <div class="footer-meta">
        <strong>أُعد هذا التقرير بواسطة: {{ $centerName }}</strong>
        <div class="muted" style="margin-top:4px;font-size:9.5px;">
            وقت الإنشاء الآلي للمستند: {{ now()->format('Y/m/d H:i') }}
        </div>
    </div>

    @if($whatsappFooterSrc !== '')
        <div class="annex-close">
            <img class="annex-img" src="{{ $whatsappFooterSrc }}" alt="">
        </div>
    @endif
</body>
</html>
