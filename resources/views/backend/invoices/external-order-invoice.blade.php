<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ translate('INVOICE') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8">
    <style media="all">
        @page {
            margin: 0;
            padding: 0;
        }

        body {
            font-size: 0.875rem;
            font-family: '<?php echo $font_family ?>';
            font-weight: normal;
            direction: <?php echo $direction ?>;
            text-align: <?php echo $text_align ?>;
            padding: 0;
            margin: 0;
        }

        .gry-color *,
        .gry-color {
            color: #000;
        }

        table {
            width: 100%;
        }

        table th {
            font-weight: normal;
        }

        table.padding th {
            padding: .25rem .7rem;
        }

        table.padding td {
            padding: .25rem .7rem;
        }

        table.sm-padding td {
            padding: .1rem .7rem;
        }

        .border-bottom td,
        .border-bottom th {
            border-bottom: 1px solid #eceff4;
        }

        .text-left {
            text-align: <?php echo $text_align ?>;
        }

        .text-right {
            text-align: <?php echo $not_text_align ?>;
        }
    </style>
</head>

<body>
    <div>

        @php
        $logo = get_setting('header_logo');
        @endphp

        <div style="background: #eceff4;padding: 1rem;">
            <table>
                <tr>
                    <td>
                        @if($logo != null)
                        <img src="{{ uploaded_asset($logo) }}" height="30" style="display:inline-block;">
                        @else
                        <img src="{{ static_asset('assets/img/logo.png') }}" height="30" style="display:inline-block;">
                        @endif
                    </td>
                    <td style="font-size: 1.5rem;" class="text-right strong">{{ translate('INVOICE') }}</td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="font-size: 1rem;" class="strong">{{ get_setting('site_name') }}</td>
                    <td class="text-right"></td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ get_setting('contact_address') }}</td>
                    <td class="text-right"></td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ translate('Email') }}: {{ get_setting('contact_email') }}</td>
                    <td class="text-right small"><span class="gry-color small">{{ translate('Order ID') }}:</span> <span
                            class="strong">{{ $order->code }}</span></td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ translate('Phone') }}: {{ get_setting('contact_phone') }}</td>
                    <td class="text-right small"><span class="gry-color small">{{ translate('Order Date') }}:</span>
                        <span dir="ltr" class=" strong">{{$order->created_at->format('g:i A d/m/Y') }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="gry-color small"></td>
                    <td class="text-right small">
                        <span class="gry-color small">
                            {{ translate('Provider') }}:
                        </span>
                        <span class="strong">
                            {{ translate(ucfirst(str_replace('_', ' ', $order->provider))) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="gry-color small"></td>
                    <td class="text-right small">
                        <span class="gry-color small">
                            {{ translate('Delivery status') }}:
                        </span>
                        <span class="strong">
                            {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="gry-color small"></td>
                    <td class="text-right small">
                        <span class="gry-color small">
                            {{ translate('Payment status') }}:
                        </span>
                        <span class="strong">
                            {{ translate(ucfirst(str_replace('_', ' ', $order->payment_status))) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="gry-color small"></td>
                    <td class="text-right small">
                        <span class="gry-color small">
                            {{ translate('Payment method') }}:
                        </span>
                        <span class="strong">
                            {{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="gry-color small"></td>
                    <td class="text-right small">
                        <span class="gry-color small">
                            {{ translate('Delivery Type') }}:
                        </span>
                        <span class="strong">
                            @if ($order->delivery_type != null && $order->delivery_type == 'home_delivery')
                                {{ translate('Home Delivery') }}
                            @elseif ($order->delivery_type == 'pickup_point')
                                @if ($order->pickup_point != null)
                                    {{ $order->pickup_point->getTranslation('name') }}
                                    ({{ translate('Pickup Point') }})
                                @else
                                    {{ translate('Pickup Point') }}
                                @endif
                                @elseif($order->delivery_type == 'carrier')
                                @if ($order->carrier != null)
                                    {{ $order->carrier->name }} ({{ translate('Carrier') }})
                                    <br>
                                    {{ translate('Transit Time').' - '.$order->carrier->transit_time }}
                                @else
                                {{ translate('Carrier') }}
                                @endif
                            @endif
                        </span>
                </tr>
                @if ($order->manual_payment && !empty($order->manual_payment_data))
                <tr>
                    <td colspan="2">
                        <div style="border-top: 1px solid #eceff4; margin: 8px 0;"></div>
                        <div style="font-weight: bold;">{{ translate('Payment Information') }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ translate('Name') }}</td>
                    <td class="text-right small strong">{{ $order->manual_payment_data->name }}</td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ translate('Method name') }}</td>
                    <td class="text-right small strong">{{ $order->manual_payment_data->method_name }}</td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ translate('Amount') }}</td>
                    <td class="text-right small strong">{{ format_price_in_usd($order->manual_payment_data->amount)
                        }}</td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ translate('TRX ID') }}</td>
                    <td class="text-right small strong">{{ $order->manual_payment_data->trx_id }}</td>
                </tr>
                @if ($order->manual_payment_data->photo)
                <tr>
                    <td class="gry-color small">{{ translate('Payment Proof') }}</td>
                    <td class="text-right small">
                        <img src="{{ uploaded_asset($order->manual_payment_data->photo) }}" alt="Payment Proof"
                            height="100">
                    </td>
                </tr>
                @endif
                @endif
            </table>

        </div>

        <div style="padding: 1rem;padding-bottom: 0">
            <table>

                <tr>
                    <td class="strong small gry-color">{{ translate('Bill to') }}:</td>
                </tr>
                <tr>
                    <td class="strong">{{ $order->shipping_address->name?? '-' }}</td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ $order->shipping_address->address ?? ''}}, {{
                        $order->shipping_address->city?? ''}}, {{
                        $order->shipping_address->state ?? ''}} - {{$order->shipping_address->country ?? ''}}</td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ translate('Email') }}: {{ $order->shipping_address->email ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="gry-color small">{{ translate('Phone') }}: {{ $order->shipping_address->phone ?? '-'}}
                    </td>
                </tr>
            </table>
        </div>

        <div style="padding: 1rem;">
            <table class="padding text-left small border-bottom">
                <thead>
                    <tr class="gry-color" style="background: #eceff4;">
                        <th width="10%" class="text-left">{{ translate('Image') }}</th>
                        <th width="30%" class="text-left">{{ translate('Product Name') }}</th>
                        <th width="10%" class="text-left">{{ translate('Qty') }}</th>
                        <th width="15%" class="text-left">{{ translate('Unit Price') }}</th>
                        <th width="10%" class="text-left">{{ translate('Variations') }}</th>
                        <th width="10%" class="text-left">{{ translate('Shipping Cost') }}</th>
                        <th width="15%" class="text-right">{{ translate('Total') }}</th>
                    </tr>
                </thead>
                <tbody class="strong">
                    @foreach ($order->products as $key => $orderDetail)
                    <tr class="">
                        <td>
                            <img src="{{ $orderDetail->image }}" alt="Product Image" style="max-width: 50px; max-height: 50px; object-fit: contain;">
                        </td>
                        <td>
                            <a href="{{ $orderDetail->url }}" target="_blank" class="text-muted">
                                {{ $orderDetail->title }}
                            </a>
                        </td>
                        <td class="">{{ $orderDetail->quantity }}</td>
                        <td class="currency">{{ format_price_in_usd($orderDetail->price) }}</td>
                        <td>
                            @foreach(json_decode($orderDetail->variations) as $key => $value)
                            <span>{{$key}}</span>
                            <span class="">
                                {{$value}}
                            </span>
                            <br>
                            @endforeach
                        </td>
                        <td class="text-right currency">{{ format_price_in_usd($orderDetail->shipping_fee) }}</td>
                        <td class="text-right currency">{{ format_price_in_usd($orderDetail->subtotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:0 1.5rem;">
            <table class="text-right sm-padding small strong">
                <thead>
                    <tr>
                        <th width="60%"></th>
                        <th width="40%"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-left">
                            @php
                                $qrCode = QrCode::size(100)->generate($order->code);
                                // Remove everything before the first <svg
                                $qrCode = substr($qrCode, strpos($qrCode, '<svg'));
                            @endphp
                            {!! $qrCode !!}
                        </td>
                        <td>
                            <table class="text-right sm-padding small strong">
                                <tbody>
                                    <tr>
                                        <th class="gry-color text-left">{{ translate('Sub Total') }}</th>
                                        <td class="currency">

                                            {{ format_price_in_usd($order->subtotal) }}
                                        </td>
                                    </tr>
                                    @foreach($order->getFormattedPriceAdjustments(false) as $priceAdjustment)
                                    <tr>
                                        <th class="gry-color text-left">{{ $priceAdjustment['key'] }}</th>
                                        <td class="currency">
                                            {{ $priceAdjustment['value'] }}
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <th class="text-left">{{ translate('Commission') }}</th>
                                        <td class="currency">{{ format_price_in_usd($order->commission) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left">{{ translate('Shipping Cost') }}</th>
                                        <td class="currency">{{ format_price_in_usd($order->shipping_fee) }}</td>
                                    </tr>
                                    @if ($order->should_check)
                                    <tr>
                                        <th class="text-left ">{{ translate('Order check fee') }}</th>
                                        <td class="currency">{{ format_price_in_usd($order->check_fee) }}</td>
                                    </tr>
                                    @endif
                                    @if ($order->delivery_fee > 0)
                                    <tr>
                                        <th class="text-left ">{{ translate('Delivery Fee') }}</th>
                                        <td class="currency">{{ format_price_in_usd($order->delivery_fee) }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th class="text-left strong">{{ translate('Grand Total') }}</th>
                                        <td class="currency">{{ format_price_in_usd($order->grand_total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>
