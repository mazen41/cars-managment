<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تقرير الفحص</title>
    <style>
        body { font-family: system-ui, "Segoe UI", Tahoma, sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
        .card { max-width: 560px; margin: 48px auto; padding: 28px 24px; background: #fff; border-radius: 16px;
            border: 1px solid #e2e8f0; box-shadow: 0 14px 40px rgba(15, 23, 42, 0.06); text-align: right; line-height: 1.65; }
        .h { margin: 0 0 8px; font-size: 1.35rem; }
        .muted { color: #64748b; font-size: 0.92rem; }
        .pill { display: inline-block; margin-top: 12px; padding: 8px 12px; background: #eff6ff; color: #1d4ed8;
            border-radius: 999px; font-weight: 700; font-size: 0.85rem; }
        code { direction: ltr; display: inline-block; font-size: 0.8rem; background: #f1f5f9; padding: 2px 8px;
            border-radius: 8px; }
    </style>
</head>
<body>
<div class="card">
    <h1 class="h">تقرير فحص يدوي</h1>
    <p class="muted">لم يتم تعيين عنوان التطبيق لإكمال الإحالة آلياً. اطلب من المسؤول ضبط
        <code>MANUAL_EXAMINATION_QR_REDIRECT_BASE</code> في الإعدادات.</p>
    <p><strong>رقم التقرير:</strong> {{ $carInspection->inspection_number }}</p>
    <p><strong>رابط التحقق (يمكن مشاركته يدوياً):</strong><br>
        <span class="muted" style="word-break: break-all;">{{ $verificationUrl }}</span>
    </p>
    <div class="pill">معرف التقرير: #{{ $carInspection->id }}</div>
</div>
</body>
</html>
