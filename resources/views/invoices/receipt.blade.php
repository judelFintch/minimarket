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
                --screen-bg: linear-gradient(180deg, #f4efe7 0%, #e8f0ef 100%);
                --screen-text: #122027;
                --screen-muted: #58636a;
                --screen-panel: rgba(255, 255, 255, 0.88);
                --screen-border: rgba(18, 32, 39, 0.08);
            }
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                font-family: "Courier New", Courier, monospace;
                font-size: var(--font-size);
                color: #111;
                background: var(--screen-bg);
            }
            a {
                color: inherit;
                text-decoration: none;
            }
            .screen {
                min-height: 100vh;
                padding: 24px;
            }
            .screen-shell {
                max-width: 1120px;
                margin: 0 auto;
                display: grid;
                gap: 24px;
                align-items: start;
            }
            .screen-panel {
                border: 1px solid var(--screen-border);
                background: var(--screen-panel);
                backdrop-filter: blur(14px);
                border-radius: 28px;
                box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
            }
            .hero {
                padding: 28px;
                color: var(--screen-text);
            }
            .eyebrow {
                font-size: 11px;
                letter-spacing: 0.24em;
                text-transform: uppercase;
                color: var(--screen-muted);
            }
            .hero-grid {
                display: grid;
                gap: 24px;
                grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.9fr);
            }
            .hero-title {
                margin: 10px 0 0;
                font-size: 32px;
                line-height: 1.1;
                font-weight: 700;
            }
            .hero-copy {
                margin: 12px 0 0;
                max-width: 56ch;
                color: var(--screen-muted);
                font-size: 14px;
                line-height: 1.6;
            }
            .hero-metrics {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin-top: 22px;
            }
            .metric {
                border-radius: 18px;
                padding: 14px 16px;
                background: rgba(255, 255, 255, 0.78);
                border: 1px solid rgba(18, 32, 39, 0.06);
            }
            .metric-label {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.16em;
                color: var(--screen-muted);
            }
            .metric-value {
                margin-top: 8px;
                font-size: 18px;
                font-weight: 700;
                color: var(--screen-text);
            }
            .actions {
                display: grid;
                gap: 12px;
                align-content: start;
            }
            .action-card {
                border-radius: 22px;
                padding: 18px;
                background: rgba(13, 148, 136, 0.08);
                border: 1px solid rgba(15, 118, 110, 0.16);
            }
            .action-title {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.16em;
                color: var(--screen-muted);
            }
            .action-copy {
                margin-top: 10px;
                color: var(--screen-text);
                font-size: 14px;
                line-height: 1.6;
            }
            .button-group {
                display: grid;
                gap: 10px;
                margin-top: 18px;
            }
            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                min-height: 46px;
                border-radius: 14px;
                padding: 0 16px;
                font-size: 13px;
                font-weight: 700;
                transition: transform 120ms ease, box-shadow 120ms ease, border-color 120ms ease;
                border: 1px solid transparent;
            }
            .button:hover {
                transform: translateY(-1px);
            }
            .button-primary {
                color: #fff;
                background: linear-gradient(135deg, #0f766e, #155e75);
                box-shadow: 0 12px 24px rgba(15, 118, 110, 0.24);
            }
            .button-secondary {
                color: var(--screen-text);
                background: rgba(255, 255, 255, 0.92);
                border-color: rgba(18, 32, 39, 0.1);
            }
            .receipt-stage {
                padding: 0 28px 28px;
                display: flex;
                justify-content: center;
            }
            .receipt-frame {
                width: min(100%, 420px);
                padding: 28px 18px;
                border-radius: 28px;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(247, 247, 245, 0.96));
                border: 1px solid rgba(18, 32, 39, 0.08);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
            }
            .receipt {
                width: var(--paper-width);
                padding: 10px 8px;
                margin: 0 auto;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
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
            .screen-note {
                margin-top: 14px;
                text-align: center;
                font-size: 12px;
                color: var(--screen-muted);
            }
            @media (max-width: 900px) {
                .screen {
                    padding: 16px;
                }
                .hero {
                    padding: 22px;
                }
                .hero-grid {
                    grid-template-columns: 1fr;
                }
                .hero-title {
                    font-size: 28px;
                }
                .hero-metrics {
                    grid-template-columns: 1fr;
                }
                .receipt-stage {
                    padding: 0 22px 22px;
                }
            }
            @media print {
                @page {
                    size: 58mm auto;
                    margin: 0;
                }
                body {
                    width: var(--paper-width);
                    background: #fff;
                }
                .screen {
                    min-height: auto;
                    padding: 0;
                }
                .screen-shell,
                .screen-panel,
                .receipt-stage,
                .receipt-frame {
                    display: block;
                    max-width: none;
                    width: auto;
                    padding: 0;
                    margin: 0;
                    background: transparent;
                    border: 0;
                    box-shadow: none;
                    backdrop-filter: none;
                }
                .receipt {
                    margin: 0;
                    padding: 8px 6px;
                    border-radius: 0;
                    box-shadow: none;
                }
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="screen">
            <div class="screen-shell">
                <div class="screen-panel no-print">
                    <div class="hero">
                        <div class="hero-grid">
                            <div>
                                <div class="eyebrow">Ticket de caisse</div>
                                <h1 class="hero-title">Ticket pret a imprimer, avec retour rapide vers la vente.</h1>
                                <p class="hero-copy">
                                    L'affichage ecran est modernise pour la consultation et la navigation, tandis que l'impression conserve un format thermique compact de 58 mm.
                                </p>
                                <div class="hero-metrics">
                                    <div class="metric">
                                        <div class="metric-label">Facture</div>
                                        <div class="metric-value">{{ $invoice->invoice_number }}</div>
                                    </div>
                                    <div class="metric">
                                        <div class="metric-label">Montant total</div>
                                        <div class="metric-value">{{ number_format($invoice->total_amount, 2) }}</div>
                                    </div>
                                    <div class="metric">
                                        <div class="metric-label">Client</div>
                                        <div class="metric-value">{{ $invoice->sale->customer_name ?? 'Comptoir' }}</div>
                                    </div>
                                    <div class="metric">
                                        <div class="metric-label">Date</div>
                                        <div class="metric-value">{{ $invoice->issued_at?->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="actions">
                                <div class="action-card">
                                    <div class="action-title">Actions rapides</div>
                                    <div class="action-copy">
                                        Imprimez le ticket ou revenez directement a l'ecran de vente pour continuer les encaissements sans perdre de temps.
                                    </div>
                                    <div class="button-group">
                                        <button type="button" class="button button-primary" onclick="window.print()">Imprimer le ticket</button>
                                        <a href="{{ route('sales.index') }}" class="button button-secondary">Retour a la vente</a>
                                        <a href="{{ route('sales.history') }}" class="button button-secondary">Voir l'historique</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="receipt-stage">
                    <div class="receipt-frame">
                        <div class="receipt">
                            <div class="center">
                                <div class="item-name">{{ config('app.name') }}</div>
                                <div class="muted">Ticket thermique</div>
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

                            <div class="screen-note no-print">
                                Apercu ecran modernise. Impression conservee en format thermique.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
