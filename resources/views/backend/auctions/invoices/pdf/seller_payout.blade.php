<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ translate('Seller Payout') }} #{{ $invoice->id }}</title>
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
            border-bottom: 2px solid #28a745;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
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
            color: #28a745;
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
            background-color: #28a745;
            color: white;
        }
        .commission-row {
            color: #dc3545;
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
        .payout-instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ get_setting('website_name', 'Samh Control Panel') }}</div>
        <div>{{ get_setting('site_motto', 'Auction Platform') }}</div>
    </div>

    <div class="invoice-title">{{ translate('SELLER PAYOUT STATEMENT') }}</div>

   <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:30px;">
    <tr>
        <td width="50%" valign="top">
            <strong>{{ translate('Payout To') }}:</strong><br>
            {{ $invoice->user->name }}<br>
            @if($invoice->user->email)
                {{ $invoice->user->email }}<br>
            @endif
            @if($invoice->user->phone)
                {{ $invoice->user->phone }}<br>
            @endif
        </td>

        <td width="50%" valign="top" align={{ $options['not_text_align'] ?? 'left' }}>
            <strong>{{ translate('Payout') }} #{{ $invoice->id }}</strong><br>
            {{ translate('Date') }}: {{ $invoice->created_at->format('M d, Y') }}<br>
            {{ translate('Due Date') }}:
            {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}<br>
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
                <td><strong>{{ translate('Item ID') }}:</strong></td>
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
                <td><strong>{{ translate('Final Sale Amount') }}:</strong></td>
                <td class="amount-row">{{ format_price($invoice->amount) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">{{ translate('Payout Calculation') }}</div>
        <table class="table">
            <tr>
                <td><strong>{{ translate('Gross Sale Amount') }}</strong></td>
                <td class="amount-row">{{ format_price($invoice->amount) }}</td>
            </tr>
            <tr class="commission-row">
                <td><strong>{{ translate('Platform Commission') }}</strong></td>
                <td class="amount-row">-{{ format_price($invoice->commission_amount ?? 0) }}</td>
            </tr>
            <tr class="total-amount">
                <td><strong>{{ translate('NET PAYOUT AMOUNT') }}</strong></td>
                <td class="amount-row">{{ format_price($invoice->net_amount) }}</td>
            </tr>
        </table>
    </div>

    @if($invoice->status == 'pending')
        <div class="payout-instructions">
            <strong>{{ translate('Payout Information') }}:</strong><br>
            {{ translate('Your payout will be processed according to our standard payout schedule. Please ensure your payment details are up to date in your seller account.') }}
        </div>
    @endif

    @if($invoice->payment)
        <div class="section">
            <div class="section-title">{{ translate('Payout Information') }}</div>
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
                    <td><strong>{{ translate('Payout Date') }}:</strong></td>
                    <td>{{ $invoice->payment->created_at->format('M d, Y h:i A') }}</td>
                </tr>
            </table>
        </div>
    @endif

    <div class="section">
        <div class="section-title">{{ translate('Commission Breakdown') }}</div>
        <table class="table">
            <tr>
                <td>{{ translate('Platform Commission Rate') }}</td>
                <td>
                    @if($invoice->amount > 0)
                        {{ number_format((($invoice->commission_amount ?? 0) / $invoice->amount) * 100, 2) }}%
                    @else
                        0%
                    @endif
                </td>
            </tr>
            <tr>
                <td>{{ translate('Commission Amount') }}</td>
                <td>{{ format_price($invoice->commission_amount ?? 0) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>{{ translate('Thank you for selling with us!') }}</p>
        <p>{{ translate('Generated on') }}: {{ now()->format('M d, Y h:i A') }}</p>
        @if(get_setting('contact_email'))
            <p>{{ translate('Questions? Contact us at') }}: {{ get_setting('contact_email') }}</p>
        @endif
    </div>
</body>
</html>
