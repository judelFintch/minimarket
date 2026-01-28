<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function download(Invoice $invoice)
    {
        $invoice->load(['sale.items.product']);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ]);

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function receipt(Invoice $invoice)
    {
        $invoice->load(['sale.items.product']);

        return view('invoices.receipt', [
            'invoice' => $invoice,
        ]);
    }
}
