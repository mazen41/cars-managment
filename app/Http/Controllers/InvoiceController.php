<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Order;
use Session;
use PDF;
use Config;

class InvoiceController extends Controller
{
    public function invoice_download($id)
    {
        $config = [];
        $options = get_pdf_options();
        $order = Order::findOrFail($id);
        return PDF::loadView('backend.invoices.invoice', [
            'order' => $order,
            'font_family' => $options['font_family'],
            'direction' => $options['direction'],
            'text_align' => $options['text_align'],
            'not_text_align' => $options['not_text_align']
        ], [], $config)->download('order-' . $order->code . '.pdf');
    }

}
