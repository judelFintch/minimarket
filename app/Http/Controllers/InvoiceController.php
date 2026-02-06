<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function download(Invoice $invoice)
    {
        $invoice->load(['sale.items.product']);
        $this->authorizeInvoice($invoice);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ]);

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function receipt(Invoice $invoice)
    {
        $invoice->load(['sale.items.product']);
        $this->authorizeInvoice($invoice);

        return view('invoices.receipt', [
            'invoice' => $invoice,
        ]);
    }

    private function authorizeInvoice(Invoice $invoice): void
    {
        $user = auth()->user();
        if (! $user || $user->isAdmin()) {
            return;
        }

        if ($invoice->sale?->user_id !== $user->id) {
            abort(403);
        }
    }
}
