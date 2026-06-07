<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de paie — {{ $bulletin->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
            padding: 20px;
        }

        .page {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
        }

        /* ── En-tête ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            border-bottom: 2px solid #760078;
            margin-bottom: 16px;
        }

        .company-sub  { font-size: 9px; color: #94a3b8; margin-top: 3px; }

        .title-block { text-align: right; }
        .title-block h1 { font-size: 18px; font-weight: 900; letter-spacing: -0.5px; color: #1e293b; }
        .title-block .period { font-size: 11px; color: #64748b; margin-top: 2px; }
        .title-block .ref { font-size: 10px; font-weight: 700; color: #760078; margin-top: 4px; font-family: monospace; }

        /* ── Section label ── */
        .section-label {
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: #94a3b8;
            background: #f8fafc;
            padding: 6px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* ── Info grille ── */
        .info-grid {
            display: grid; grid-template-columns: 1fr 1fr 1fr;
            gap: 8px 24px;
            padding: 12px 12px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 0;
        }

        .info-item label { display: block; font-size: 9px; color: #94a3b8; margin-bottom: 2px; }
        .info-item span  { font-size: 11px; font-weight: 600; color: #334155; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; }
        th {
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: #94a3b8;
            padding: 6px 12px; text-align: left;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        th.right { text-align: right; }
        th.center { text-align: center; }

        td { padding: 7px 12px; border-bottom: 1px solid #f1f5f9; }
        td.right { text-align: right; font-weight: 600; }
        td.center { text-align: center; color: #64748b; }
        td.muted { color: #64748b; }

        .tfoot-brut td {
            background: #fdf4ff; padding: 8px 12px;
            border-top: 2px solid #e9d5ea;
        }
        .tfoot-brut .label { font-weight: 800; font-size: 12px; color: #1e293b; }
        .tfoot-brut .amount { font-weight: 900; font-size: 13px; color: #760078; text-align: right; }

        .tfoot-retenues td {
            background: #fff5f5; padding: 8px 12px;
            border-top: 2px solid #fecaca;
        }
        .tfoot-retenues .label { font-weight: 800; font-size: 12px; color: #1e293b; }
        .tfoot-retenues .amount { font-weight: 900; font-size: 13px; color: #ef4444; text-align: right; }

        /* ── Net à payer ── */
        .net-block {
            display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(135deg, #760078, #7677B7);
            color: #fff;
            padding: 16px 20px;
            margin-bottom: 0;
        }
        .net-block .net-label { font-size: 16px; font-weight: 900; letter-spacing: -0.3px; }
        .net-block .net-sub   { font-size: 10px; opacity: 0.75; margin-top: 3px; }
        .net-block .net-amount { font-size: 22px; font-weight: 900; text-align: right; }
        .net-block .net-currency { font-size: 11px; opacity: 0.8; }

        /* ── Patronales ── */
        .patronales {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .patronales-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 6px 24px;
        }
        .patronales-item {
            display: flex; justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px dotted #e2e8f0;
            font-size: 10px;
        }
        .patronales-item .p-label { color: #64748b; }
        .patronales-item .p-val   { font-weight: 600; }
        .patronales-total {
            display: flex; justify-content: space-between;
            margin-top: 8px; padding-top: 8px;
            border-top: 2px solid #e2e8f0;
            font-weight: 700; font-size: 11px;
        }

        /* ── Signatures ── */
        .signatures {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 20px 12px 12px;
        }
        .sig-box .sig-title { font-weight: 700; font-size: 11px; color: #334155; margin-bottom: 30px; }
        .sig-line { border-top: 2px dashed #cbd5e1; padding-top: 6px; }
        .sig-line span { font-size: 9px; color: #94a3b8; }

        .footer-note {
            text-align: center;
            font-size: 9px; color: #cbd5e1;
            padding: 8px 0;
            border-top: 1px solid #f1f5f9;
            margin-top: 8px;
        }

        /* ── Print ── */
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
            @page { margin: 1cm; size: A4; }
        }

        /* ── Bouton print (visible uniquement à l'écran) ── */
        .print-bar {
            display: flex; justify-content: flex-end; gap: 8px;
            margin-bottom: 16px;
        }
        .btn-print {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 10px;
            background: linear-gradient(135deg, #760078, #7677B7);
            color: #fff; font-size: 12px; font-weight: 700;
            border: none; cursor: pointer;
            text-decoration: none;
        }
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 10px;
            background: #f1f5f9; color: #475569;
            font-size: 12px; font-weight: 700;
            border: none; cursor: pointer;
            text-decoration: none;
        }

        .section-wrapper { border: 1px solid #e2e8f0; border-radius: 0; overflow: hidden; margin-bottom: 0; }
    </style>
</head>
<body>
<div class="page">

    <!-- Barre d'actions (masquée à l'impression) -->
    <div class="print-bar no-print">
        <a href="{{ url('/bulletins-paie/' . $bulletin->id) }}" class="btn-back">
            ← Retour
        </a>
        <button onclick="window.print()" class="btn-print">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Imprimer / Sauvegarder PDF
        </button>
    </div>

    <div class="section-wrapper">

        <!-- En-tête -->
        <div class="header" style="padding:16px 16px 16px; border-bottom:2px solid #760078; margin:0">
            <div>
                <img src="{{ asset('images/logo.svg') }}" alt="EdenCorporate"
                     style="height:52px; width:auto; object-fit:contain; display:block;"/>
                <div class="company-sub">Abidjan, Côte d'Ivoire &nbsp;·&nbsp; RCCM : CI-ABJ-2024-B-XXXXX</div>
            </div>
            <div class="title-block">
                <h1>BULLETIN DE PAIE</h1>
                <div class="period">{{ $bulletin->mois }} {{ $bulletin->annee }}</div>
                <div class="ref">{{ $bulletin->reference }}</div>
            </div>
        </div>

        <!-- Informations salarié -->
        <div class="section-label">Informations du salarié</div>
        <div class="info-grid">
            <div class="info-item">
                <label>Nom &amp; Prénom</label>
                <span>{{ $bulletin->user?->nom }} {{ $bulletin->user?->prenom }}</span>
            </div>
            <div class="info-item">
                <label>Poste</label>
                <span>{{ $poste }}</span>
            </div>
            <div class="info-item">
                <label>Département</label>
                <span>{{ $dept }}</span>
            </div>
            <div class="info-item">
                <label>Type de contrat</label>
                <span>{{ $bulletin->user?->type_contrat === 'employe' ? 'CDI / CDD' : 'Prestataire' }}</span>
            </div>
            <div class="info-item">
                <label>Période de paie</label>
                <span>{{ $bulletin->mois }} {{ $bulletin->annee }}</span>
            </div>
            <div class="info-item">
                <label>Statut</label>
                <span>{{ ['brouillon'=>'Brouillon','valide'=>'Validé','paye'=>'Payé'][$bulletin->statut] ?? $bulletin->statut }}</span>
            </div>
        </div>

        <!-- Éléments de rémunération -->
        <div class="section-label">Éléments de rémunération</div>
        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="center">Base</th>
                    <th class="right">Montant (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ([
                    ['Salaire de base',          '',                                  $bulletin->salaire_base],
                    ['Prime de transport',        '',                                  $bulletin->prime_transport],
                    ['Prime de logement',         '',                                  $bulletin->prime_logement],
                    ['Prime de fonction',         '',                                  $bulletin->prime_fonction],
                    ['Prime de rendement',        '',                                  $bulletin->prime_rendement],
                    ['Prime de panier',           '',                                  $bulletin->prime_panier],
                    ['Bonus annuel',              '',                                  $bulletin->bonus_annuel],
                    ['Heures supplémentaires',    ($bulletin->heures_sup > 0 ? $bulletin->heures_sup . ' h' : ''), $bulletin->heures_sup * $bulletin->taux_heures_sup],
                    ['Avantages en nature',       '',                                  $bulletin->avantages_nature_montant],
                ] as [$label, $base, $val])
                    @if((float)$val > 0)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="center">{{ $base }}</td>
                        <td class="right">{{ number_format((float)$val, 0, ',', ' ') }}</td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr class="tfoot-brut">
                    <td class="label">Salaire Brut</td>
                    <td></td>
                    <td class="amount">{{ number_format((float)$bulletin->salaire_brut, 0, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Retenues -->
        <div class="section-label">Retenues salariales</div>
        <table>
            <tbody>
                @foreach ([
                    ['CNPS salarié',                          number_format((float)$bulletin->salaire_brut,0,',',' '), $bulletin->cnps_salarie],
                    ['Assurance maladie',                     number_format((float)$bulletin->salaire_brut,0,',',' '), $bulletin->assurance_maladie_salarie],
                    ['IGR (Impôt Général sur le Revenu)',     '',    $bulletin->igr],
                    ['IS (Impôt sur salaire)',                '',    $bulletin->is_salaire],
                    ['Avance sur salaire',                    '',    $bulletin->avance_salaire],
                    ['Prêt entreprise',                       '',    $bulletin->pret_entreprise],
                    ['Autres retenues',                       '',    $bulletin->autres_retenues],
                ] as [$label, $base, $val])
                    @if((float)$val > 0)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="center">{{ $base }}</td>
                        <td class="right" style="color:#ef4444">{{ number_format((float)$val, 0, ',', ' ') }}</td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr class="tfoot-retenues">
                    <td class="label">Total retenues</td>
                    <td></td>
                    <td class="amount" style="color:#ef4444">{{ number_format((float)$bulletin->total_retenues, 0, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Net à payer -->
        <div class="net-block">
            <div>
                <div class="net-label">NET À PAYER</div>
                <div class="net-sub">
                    Mode : {{ ['virement'=>'Virement bancaire','especes'=>'Espèces','mobile_money'=>'Mobile Money','cheque'=>'Chèque'][$bulletin->mode_paiement] ?? '—' }}
                    @if($bulletin->date_paiement) · {{ \Carbon\Carbon::parse($bulletin->date_paiement)->format('d/m/Y') }} @endif
                </div>
            </div>
            <div>
                <div class="net-amount">{{ number_format((float)$bulletin->net_a_payer, 0, ',', ' ') }}</div>
                <div class="net-currency" style="text-align:right">FCFA</div>
            </div>
        </div>

        <!-- Cotisations patronales -->
        <div class="section-label">Cotisations patronales (non déduites du salarié)</div>
        <div class="patronales">
            <div class="patronales-grid">
                @foreach ([
                    ['CNPS Employeur',              $bulletin->cnps_employeur],
                    ['Accident du travail',         $bulletin->accident_travail],
                    ['Prestations familiales',      $bulletin->prestations_familiales],
                    ['Formation professionnelle',   $bulletin->formation_professionnelle],
                ] as [$label, $val])
                <div class="patronales-item">
                    <span class="p-label">{{ $label }}</span>
                    <span class="p-val">{{ number_format((float)$val, 0, ',', ' ') }} FCFA</span>
                </div>
                @endforeach
            </div>
            <div class="patronales-total">
                <span>Coût total pour l'entreprise</span>
                <span>{{ number_format((float)$bulletin->cout_total_employeur, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="sig-box">
                <div class="sig-title">L'employeur</div>
                <div class="sig-line"><span>Nom &amp; Signature</span></div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Le salarié</div>
                <div class="sig-line"><span>{{ $bulletin->user?->nom }} {{ $bulletin->user?->prenom }}</span></div>
            </div>
        </div>

        <div class="footer-note">
            Bulletin généré le {{ now()->format('d/m/Y') }} · Conservez ce document pendant 5 ans · EdemERM — Système SIRH
        </div>

    </div>
</div>

<script>
    // Auto-déclencher l'impression si ?print=1 dans l'URL
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.onload = () => window.print();
    }
</script>
</body>
</html>
