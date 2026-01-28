<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ticket {{ $invoice->invoice_number }}</title>
        <style>
            :root {
                --paper-width: 58mm;
                --font-size: 12px;
            }
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                font-family: "Courier New", Courier, monospace;
                font-size: var(--font-size);
                color: #111;
            }
            .receipt {
                width: var(--paper-width);
                padding: 8px 6px;
                margin: 0 auto;
            }
            .center {
                text-align: center;
            }
            .muted {
                color: #555;
            }
            .line {
                display: flex;
                justify-content: space-between;
                gap: 8px;
            }
            .divider {
                border-top: 1px dashed #333;
                margin: 6px 0;
            }
            .items {
                margin-top: 6px;
            }
            .item {
                margin-bottom: 4px;
            }
            .item-name {
                font-weight: 700;
            }
            .item-meta {
                display: flex;
                justify-content: space-between;
            }
            .total {
                font-size: 13px;
                font-weight: 700;
            }
            @media print {
                body {
                    width: var(--paper-width);
                }
                .receipt {
                    margin: 0;
                }
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="receipt">
            <div class="center">
                <div class="item-name">{{ config('app.name') }}</div>
                <div class="muted">Ticket Epson</div>
            </div>

            <div class="divider"></div>
            <div class="line">
                <span>Facture</span>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            <div class="line">
                <span>Date</span>
                <span>{{ $invoice->issued_at?->format('Y-m-d H:i') }}</span>
            </div>
            <div class="line">
                <span>Client</span>
                <span>{{ $invoice->sale->customer_name ?? 'Comptoir' }}</span>
            </div>

            <div class="divider"></div>
            <div class="items">
                @foreach ($invoice->sale->items as $item)
                    <div class="item">
                        <div class="item-name">{{ $item->product?->name ?? 'Produit' }}</div>
                        <div class="item-meta muted">
                            <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</span>
                            <span>{{ number_format($item->line_total, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="divider"></div>
            <div class="line">
                <span>Sous-total</span>
                <span>{{ number_format($invoice->sale->subtotal_amount ?? $invoice->total_amount, 2) }}</span>
            </div>
            <div class="line">
                <span>Remise</span>
                <span>-{{ number_format($invoice->sale->discount_amount ?? 0, 2) }}</span>
            </div>
            <div class="line">
                <span>TVA</span>
                <span>+{{ number_format($invoice->sale->tax_amount ?? 0, 2) }}</span>
            </div>
            <div class="divider"></div>
            <div class="line total">
                <span>Total</span>
                <span>{{ number_format($invoice->total_amount, 2) }}</span>
            </div>

            <div class="divider"></div>
            <div class="center muted">
                Merci pour votre achat !
            </div>

            <div class="center no-print" style="margin-top: 8px;">
                <button onclick="window.print()">Imprimer</button>
            </div>
        </div>
    </body>
</html>
