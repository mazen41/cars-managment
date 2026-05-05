<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ translate('Buyer Invoice') }} #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: {{ $options['font_family'] ?? 'Arial, sans-serif' }};
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            direction: {{ $options['direction'] ?? 'ltr' }};
            text-align: {{ $options['text_align'] ?? 'left' }};
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #007bff;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .amount-row {
            font-weight: bold;
            font-size: 16px;
        }
        .total-amount {
            background-color: #007bff;
            color: white;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-paid { background-color: #28a745; color: #fff; }
        .status-overdue { background-color: #dc3545; color: #fff; }
        .status-cancelled { background-color: #6c757d; color: #fff; }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .payment-instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
    </style>
</head>
<body>
     @php
        $logo = get_setting('header_logo');
    @endphp
    <div class="header">
        @if($logo != null)
            <img src="{{ uploaded_asset($logo) }}" height="50" style="display:inline-block;">
        @else
            <img src="{{ static_asset('assets/img/logo.png') }}" height="50" style="display:inline-block;">
        @endif
        <div>{{ get_setting('site_motto', 'Auction Platform') }}</div>
    </div>

    <div class="invoice-title">{{ translate('BUYER INVOICE') }}</div>

   <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:30px;">
    <tr>
        <td width="50%" valign="top">
            <strong>{{ translate('Invoice To') }}:</strong><br>
            {{ $invoice->user->name }}<br>
            @if ($invoice->user->email)
                {{ $invoice->user->email }}<br>
            @endif
            @if($invoice->user->phone)
                {{ $invoice->user->phone }}<br>
            @endif
        </td>

        <td width="50%" valign="top" align={{ $options['not_text_align'] ?? 'left' }}>
            <strong>{{ translate('Invoice') }} #{{ $invoice->id }}</strong><br>
            {{ translate('Date') }}: {{ $invoice->created_at->format('M d, Y') }}<br>
            {{ translate('Due Date') }}: {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}<br>
            {{ translate('Status') }}:
            <span class="status-badge status-{{ $invoice->status }}">
                {{ ucfirst($invoice->status) }}
            </span>
        </td>
    </tr>
</table>

    <div class="section">
        <div class="section-title">{{ translate('Auction Item Details') }}</div>
        <table class="table">
            <tr>
                <td><strong>{{ translate('Item Id') }}:</strong></td>
                <td>#{{ $invoice->auctionItem->id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>{{ translate('Car') }}:</strong></td>
                <td>{{ $invoice->auctionItem->car->car_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>{{ translate('Auction Room') }}:</strong></td>
                <td>{{ $invoice->auctionItem->auctionRoom->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>{{ translate('Winning Bid Amount') }}:</strong></td>
                <td class="amount-row">{{ format_price($invoice->amount) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">{{ translate('Payment Summary') }}</div>
        <table class="table">
            <tr>
                <td><strong>{{ translate('Winning Bid Amount') }}</strong></td>
                <td class="amount-row">{{ format_price($invoice->amount) }}</td>
            </tr>
            <tr class="total-amount">
                <td><strong>{{ translate('TOTAL AMOUNT DUE') }}</strong></td>
                <td class="amount-row">{{ format_price($invoice->amount) }}</td>
            </tr>
        </table>
    </div>

    @if($invoice->status == 'pending')
        <div class="payment-instructions">
            <strong>{{ translate('Payment Instructions') }}:</strong><br>
            {{ translate('Please complete your payment by the due date to avoid late fees. Contact our support team if you have any questions about this invoice.') }}
        </div>
    @endif

    @if($invoice->payment)
        <div class="section">
            <div class="section-title">{{ translate('Payment Information') }}</div>
            <table class="table">
                <tr>
                    <td><strong>{{ translate('Payment Method') }}:</strong></td>
                    <td>{{ $invoice->payment->method ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>{{ translate('Transaction ID') }}:</strong></td>
                    <td>{{ $invoice->payment->transaction_id ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>{{ translate('Payment Date') }}:</strong></td>
                    <td>{{ $invoice->payment->created_at->format('M d, Y h:i A') }}</td>
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>{{ translate('Thank you for participating in our auction!') }}</p>
        <p>{{ translate('Generated on') }}: {{ now()->format('M d, Y h:i A') }}</p>
        @if(get_setting('contact_email'))
            <p>{{ translate('Questions? Contact us at') }}: {{ get_setting('contact_email') }}</p>
        @endif
    </div>
</body>
</html>
