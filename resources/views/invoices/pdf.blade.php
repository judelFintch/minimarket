<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Facture {{ $invoice->invoice_number }}</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
            .header { display: flex; justify-content: space-between; margin-bottom: 24px; }
            .title { font-size: 20px; font-weight: 700; }
            .muted { color: #6b7280; }
            table { width: 100%; border-collapse: collapse; margin-top: 16px; }
            th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; }
            th { background: #f9fafb; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
            .total { text-align: right; font-weight: 700; }
        </style>
    </head>
    <body>
        <div class="header">
            <div>
                <div class="title">Facture</div>
                <div class="muted">{{ config('app.name') }}</div>
            </div>
            <div>
                <div>Numero: {{ $invoice->invoice_number }}</div>
                <div>Date: {{ $invoice->issued_at?->format('Y-m-d') ?? '-' }}</div>
                <div>Client: {{ $invoice->sale->customer_name ?? 'Comptoir' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantite</th>
                    <th>Prix unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->sale->items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'Produit' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="total">Total: {{ number_format($invoice->total_amount, 2) }}</p>
        <p class="muted">Statut: {{ ucfirst($invoice->status) }}</p>
    </body>
</html>
