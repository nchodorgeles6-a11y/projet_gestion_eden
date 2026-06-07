<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Annuel {{ $annee }} — EdenCorporate</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ── Page de couverture ─────────────────────────────────────────── */
        .cover {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #760078 0%, #4a0068 50%, #7677B7 100%);
            color: white;
            padding: 60px 70px;
            page-break-after: always;
            position: relative;
            overflow: hidden;
        }

        .cover::before {
            content: '';
            position: absolute;
            bottom: -80px; right: -80px;
            width: 360px; height: 360px;
            border-radius: 40px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
            transform: rotate(12deg);
        }
        .cover::after {
            content: '';
            position: absolute;
            top: 80px; right: 100px;
            width: 180px; height: 180px;
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
            transform: rotate(-6deg);
        }

        .cover-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: auto;
        }

        .logo-icon {
            position: relative;
            width: 48px; height: 40px;
        }
        .logo-sq1 {
            position: absolute;
            width: 24px; height: 24px;
            border: 3px solid rgba(255,255,255,0.8);
            border-radius: 6px;
            transform: rotate(12deg) translateX(-8px);
        }
        .logo-sq2 {
            position: absolute;
            width: 20px; height: 20px;
            border: 3px solid rgba(255,255,255,0.6);
            border-radius: 5px;
            transform: rotate(-6deg) translateX(12px) translateY(-8px);
        }

        .logo-text {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .logo-text span { font-weight: 300; opacity: 0.9; }

        .cover-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 0;
        }

        .cover-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 999px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 28px;
            width: fit-content;
        }
        .cover-badge-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #CDA071;
        }

        .cover-title {
            font-size: 52px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 12px;
            letter-spacing: -1px;
        }
        .cover-subtitle {
            font-size: 22px;
            font-weight: 300;
            opacity: 0.75;
            margin-bottom: 40px;
        }

        /* Verdict bénéficiaire/déficitaire */
        .cover-verdict {
            display: inline-flex;
            align-items: center;
            gap: 16px;
            padding: 20px 28px;
            border-radius: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
            margin-bottom: 32px;
            width: fit-content;
        }
        .verdict-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .verdict-icon.ben { background: rgba(110,231,183,0.2); }
        .verdict-icon.def { background: rgba(252,165,165,0.2); }
        .verdict-icon svg { width: 28px; height: 28px; }
        .verdict-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.75;
        }
        .verdict-text {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }
        .verdict-text.beneficiaire { color: #6ee7b7; }
        .verdict-text.deficitaire  { color: #fca5a5; }

        .cover-kpis {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            max-width: 540px;
        }
        .cover-kpi {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 16px;
            padding: 16px 20px;
        }
        .cover-kpi-val {
            font-size: 18px;
            font-weight: 900;
        }
        .cover-kpi-label {
            font-size: 10px;
            opacity: 0.65;
            margin-top: 4px;
            font-weight: 600;
        }

        .cover-footer {
            font-size: 10px;
            opacity: 0.4;
            margin-top: auto;
        }

        /* ── Contenu du rapport ─────────────────────────────────────────── */
        .report {
            padding: 40px 60px;
        }

        .section {
            margin-bottom: 36px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            color: #760078;
            border-bottom: 2px solid #760078;
            padding-bottom: 6px;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* KPIs */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .kpi-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            background: #f8fafc;
        }
        .kpi-card-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #64748b;
            margin-bottom: 6px;
        }
        .kpi-card-val {
            font-size: 15px;
            font-weight: 900;
        }
        .kpi-card-sub {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 2px;
        }
        .emerald { color: #059669; }
        .rose    { color: #e11d48; }
        .purple  { color: #760078; }
        .blue    { color: #2563eb; }

        /* Verdict résumé */
        .verdict-box {
            border-radius: 14px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
        }
        .verdict-box.ben { background: #ecfdf5; border: 1.5px solid #6ee7b7; }
        .verdict-box.def { background: #fff1f2; border: 1.5px solid #fca5a5; }
        .verdict-box-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .verdict-box-icon.ben { background: #d1fae5; }
        .verdict-box-icon.def { background: #fee2e2; }
        .verdict-box-icon svg { width: 28px; height: 28px; }
        .verdict-box-title { font-size: 16px; font-weight: 900; }
        .verdict-box-title.ben { color: #065f46; }
        .verdict-box-title.def { color: #881337; }
        .verdict-box-sub { font-size: 11px; color: #475569; margin-top: 4px; line-height: 1.5; }
        .verdict-box-amount { margin-left: auto; text-align: right; }
        .verdict-box-amount-val { font-size: 20px; font-weight: 900; }
        .verdict-box-amount-val.ben { color: #059669; }
        .verdict-box-amount-val.def { color: #e11d48; }
        .verdict-box-amount-label { font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }

        /* Tableaux */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        thead { background: #760078; }
        thead th {
            padding: 8px 12px;
            color: white;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: left;
        }
        thead th.right { text-align: right; }
        thead th.center { text-align: center; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 7px 12px; }
        tbody td.right { text-align: right; font-weight: 600; }
        tbody td.center { text-align: center; }
        tbody td.bold { font-weight: 700; }
        tfoot tr { background: #f1f5f9; border-top: 2px solid #cbd5e1; }
        tfoot td { padding: 8px 12px; font-weight: 800; font-size: 10px; }
        tfoot td.right { text-align: right; }

        .pos { color: #059669; }
        .neg { color: #e11d48; }

        /* Conclusion */
        .conclusion {
            background: linear-gradient(135deg, #faf5ff 0%, #f0f4ff 100%);
            border: 1px solid #ddd6fe;
            border-radius: 14px;
            padding: 24px 28px;
        }
        .conclusion h3 {
            font-size: 13px;
            font-weight: 800;
            color: #760078;
            margin-bottom: 10px;
        }
        .conclusion p {
            font-size: 11px;
            color: #334155;
            line-height: 1.8;
            margin-bottom: 8px;
        }

        /* Signatures */
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 32px;
        }
        .signature-box {
            border-top: 2px solid #cbd5e1;
            padding-top: 12px;
        }
        .signature-role {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #64748b;
            margin-bottom: 40px;
        }
        .signature-line {
            border-bottom: 1px dashed #cbd5e1;
            margin-bottom: 6px;
        }
        .signature-name {
            font-size: 9px;
            color: #94a3b8;
        }

        /* Bouton impression */
        .print-btn {
            position: fixed;
            top: 20px; right: 20px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #760078, #7677B7);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(118,0,120,0.3);
            z-index: 9999;
        }

        /* Info date/page */
        .report-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 60px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
        }
        .report-meta strong { color: #475569; }

        @media print {
            .print-btn { display: none; }
            body { font-size: 10px; }
            .cover { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .cover-verdict, .verdict-box, .kpi-card, thead { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .report { padding: 24px 40px; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">
    <svg style="display:inline;width:14px;height:14px;vertical-align:middle;margin-right:6px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
    </svg>
    Imprimer / Télécharger PDF
</button>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- PAGE DE COUVERTURE                                                      --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="cover">

    {{-- Logo --}}
    <div class="cover-logo">
        <div class="logo-icon">
            <div class="logo-sq1"></div>
            <div class="logo-sq2"></div>
        </div>
        <div class="logo-text">Eden<span>Corpor@te</span></div>
    </div>

    <div class="cover-body">

        <div class="cover-badge">
            <div class="cover-badge-dot"></div>
            Document confidentiel · Usage interne
        </div>

        <div class="cover-title">Rapport Annuel<br>{{ $annee }}</div>
        <div class="cover-subtitle">Bilan financier & activité de l'entreprise</div>

        {{-- Verdict --}}
        <div class="cover-verdict">
            @if($soldeNet >= 0)
                <div class="verdict-icon ben">
                    <svg fill="none" stroke="#6ee7b7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <div class="verdict-label">Verdict de l'exercice</div>
                    <div class="verdict-text beneficiaire">Bénéficiaire</div>
                </div>
            @else
                <div class="verdict-icon def">
                    <svg fill="none" stroke="#fca5a5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </div>
                <div>
                    <div class="verdict-label">Verdict de l'exercice</div>
                    <div class="verdict-text deficitaire">Déficitaire</div>
                </div>
            @endif
        </div>

        {{-- KPIs couverture --}}
        <div class="cover-kpis">
            <div class="cover-kpi">
                <div class="cover-kpi-val" style="color:#6ee7b7">{{ number_format($totalRevenus, 0, ',', ' ') }}</div>
                <div class="cover-kpi-label">FCFA Revenus</div>
            </div>
            <div class="cover-kpi">
                <div class="cover-kpi-val" style="color:#fca5a5">{{ number_format($totalDepenses, 0, ',', ' ') }}</div>
                <div class="cover-kpi-label">FCFA Dépenses</div>
            </div>
            <div class="cover-kpi">
                <div class="cover-kpi-val" style="color:{{ $soldeNet >= 0 ? '#6ee7b7' : '#fca5a5' }}">
                    {{ ($soldeNet >= 0 ? '+' : '') . number_format($soldeNet, 0, ',', ' ') }}
                </div>
                <div class="cover-kpi-label">FCFA Solde net</div>
            </div>
        </div>
    </div>

    <div class="cover-footer">
        Généré le {{ now()->locale('fr')->isoFormat('D MMMM YYYY [à] HH[h]mm') }} ·
        EdenCorporate — Système SIRH v4.10.0 ·
        Document confidentiel, ne pas diffuser
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- BARRE D'INFO (hors couverture)                                         --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report-meta">
    <span><strong>EdenCorporate</strong> · Rapport Financier Annuel {{ $annee }}</span>
    <span>Généré le {{ now()->locale('fr')->isoFormat('D MMMM YYYY') }} · Confidentiel</span>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- RAPPORT                                                                 --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report">

    {{-- ── 1. Verdict & Synthèse ──────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">1. Synthèse de l'exercice {{ $annee }}</div>

        {{-- Verdict --}}
        @if($soldeNet >= 0)
        <div class="verdict-box ben">
            <div class="verdict-box-icon ben">
                <svg fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <div class="verdict-box-title ben">Exercice {{ $annee }} : BÉNÉFICIAIRE</div>
                <div class="verdict-box-sub">
                    L'entreprise EdenCorporate clôture l'exercice {{ $annee }} avec un résultat positif.<br>
                    Les revenus ont excédé les dépenses de
                    <strong>{{ number_format($soldeNet, 0, ',', ' ') }} FCFA</strong>,
                    confirmant une activité financière saine.
                </div>
            </div>
            <div class="verdict-box-amount">
                <div class="verdict-box-amount-val ben">+{{ number_format($soldeNet, 0, ',', ' ') }}</div>
                <div class="verdict-box-amount-label">FCFA Bénéfice</div>
            </div>
        </div>
        @else
        <div class="verdict-box def">
            <div class="verdict-box-icon def">
                <svg fill="none" stroke="#e11d48" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div>
                <div class="verdict-box-title def">Exercice {{ $annee }} : DÉFICITAIRE</div>
                <div class="verdict-box-sub">
                    L'entreprise EdenCorporate clôture l'exercice {{ $annee }} avec un résultat négatif.<br>
                    Les dépenses ont excédé les revenus de
                    <strong>{{ number_format(abs($soldeNet), 0, ',', ' ') }} FCFA</strong>.
                    Des mesures correctives sont recommandées.
                </div>
            </div>
            <div class="verdict-box-amount">
                <div class="verdict-box-amount-val def">{{ number_format($soldeNet, 0, ',', ' ') }}</div>
                <div class="verdict-box-amount-label">FCFA Déficit</div>
            </div>
        </div>
        @endif

        {{-- KPIs principaux --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-card-label">Total Revenus</div>
                <div class="kpi-card-val emerald">{{ number_format($totalRevenus, 0, ',', ' ') }}</div>
                <div class="kpi-card-sub">FCFA · Entrées de trésorerie</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-label">Total Dépenses</div>
                <div class="kpi-card-val rose">{{ number_format($totalDepenses, 0, ',', ' ') }}</div>
                <div class="kpi-card-sub">FCFA · Sorties de trésorerie</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-label">Masse Salariale</div>
                <div class="kpi-card-val purple">{{ number_format($masseSalariale, 0, ',', ' ') }}</div>
                <div class="kpi-card-sub">FCFA · Bulletins payés {{ $annee }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-label">Effectif</div>
                <div class="kpi-card-val blue">{{ $nbEmployes }}</div>
                <div class="kpi-card-sub">Collaborateurs · {{ $nbDepartements }} département(s)</div>
            </div>
        </div>
    </div>

    {{-- ── 2. Évolution mensuelle ──────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">2. Évolution mensuelle de l'activité financière</div>
        <table>
            <thead>
                <tr>
                    <th>Mois</th>
                    <th class="right">Revenus (FCFA)</th>
                    <th class="right">Dépenses (FCFA)</th>
                    <th class="right">Solde mensuel (FCFA)</th>
                    <th class="center">Nb transactions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mensuel as $m)
                <tr>
                    <td class="bold">{{ ucfirst($m['mois']) }}</td>
                    <td class="right emerald">{{ $m['revenus'] > 0 ? number_format($m['revenus'], 0, ',', ' ') : '—' }}</td>
                    <td class="right rose">{{ $m['depenses'] > 0 ? number_format($m['depenses'], 0, ',', ' ') : '—' }}</td>
                    <td class="right {{ $m['solde'] >= 0 ? 'pos' : 'neg' }}">
                        {{ $m['solde'] != 0 ? ($m['solde'] >= 0 ? '+' : '') . number_format($m['solde'], 0, ',', ' ') : '—' }}
                    </td>
                    <td class="center">{{ $m['nb'] > 0 ? $m['nb'] : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="bold">TOTAL {{ $annee }}</td>
                    <td class="right emerald">{{ number_format($totalRevenus, 0, ',', ' ') }}</td>
                    <td class="right rose">{{ number_format($totalDepenses, 0, ',', ' ') }}</td>
                    <td class="right {{ $soldeNet >= 0 ? 'pos' : 'neg' }}">
                        {{ ($soldeNet >= 0 ? '+' : '') . number_format($soldeNet, 0, ',', ' ') }}
                    </td>
                    <td class="right">{{ $mensuel->sum('nb') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ── 3. Répartition par département ─────────────────────────────── --}}
    @if($parDept->count() > 0)
    <div class="section">
        <div class="section-title">3. Activité financière par département</div>
        <table>
            <thead>
                <tr>
                    <th>Département</th>
                    <th class="right">Revenus (FCFA)</th>
                    <th class="right">Dépenses (FCFA)</th>
                    <th class="right">Solde (FCFA)</th>
                    <th class="center">Transactions</th>
                    <th class="center">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parDept as $d)
                <tr>
                    <td class="bold">{{ $d['nom'] }}</td>
                    <td class="right emerald">{{ number_format($d['revenus'], 0, ',', ' ') }}</td>
                    <td class="right rose">{{ number_format($d['depenses'], 0, ',', ' ') }}</td>
                    <td class="right {{ $d['solde'] >= 0 ? 'pos' : 'neg' }}">
                        {{ ($d['solde'] >= 0 ? '+' : '') . number_format($d['solde'], 0, ',', ' ') }}
                    </td>
                    <td class="center">{{ $d['nb'] }}</td>
                    <td class="center {{ $d['solde'] >= 0 ? 'pos' : 'neg' }}" style="font-weight:700;">{{ $d['solde'] >= 0 ? 'Positif' : 'Négatif' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="bold">Total général</td>
                    <td class="right emerald">{{ number_format($totalRevenus, 0, ',', ' ') }}</td>
                    <td class="right rose">{{ number_format($totalDepenses, 0, ',', ' ') }}</td>
                    <td class="right {{ $soldeNet >= 0 ? 'pos' : 'neg' }}">
                        {{ ($soldeNet >= 0 ? '+' : '') . number_format($soldeNet, 0, ',', ' ') }}
                    </td>
                    <td class="right">{{ $parDept->sum('nb') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- ── 4. Données RH ────────────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">4. Indicateurs RH — Exercice {{ $annee }}</div>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-card-label">Effectif total</div>
                <div class="kpi-card-val blue">{{ $nbEmployes }}</div>
                <div class="kpi-card-sub">Collaborateurs actifs</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-label">Départements</div>
                <div class="kpi-card-val purple">{{ $nbDepartements }}</div>
                <div class="kpi-card-sub">Unités organisationnelles</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-label">Masse salariale</div>
                <div class="kpi-card-val rose">{{ number_format($masseSalariale, 0, ',', ' ') }}</div>
                <div class="kpi-card-sub">FCFA · Net versé {{ $annee }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card-label">Congés approuvés</div>
                <div class="kpi-card-val emerald">{{ $nbCongesApprouves }}</div>
                <div class="kpi-card-sub">Demandes traitées {{ $annee }}</div>
            </div>
        </div>

        @if($nbEmployes > 0 && $masseSalariale > 0)
        <p style="font-size:10px; color:#64748b; margin-top:8px; padding:10px 14px; background:#f8fafc; border-left:3px solid #760078; border-radius:0 8px 8px 0;">
            Coût moyen par collaborateur : <strong>{{ number_format($masseSalariale / $nbEmployes, 0, ',', ' ') }} FCFA</strong>
            &mdash; La masse salariale représente <strong>{{ $totalDepenses > 0 ? round($masseSalariale / $totalDepenses * 100, 1) : 0 }}%</strong> du total des dépenses.
        </p>
        @endif
    </div>

    {{-- ── 5. Conclusion ─────────────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">5. Conclusion & Recommandations</div>
        <div class="conclusion">
            <h3>Bilan de l'exercice {{ $annee }}</h3>
            @if($soldeNet >= 0)
            <p>
                L'entreprise <strong>EdenCorporate</strong> clôture l'exercice <strong>{{ $annee }}</strong> avec un bilan financier
                <strong style="color:#059669">positif</strong>. Le résultat net de
                <strong>+{{ number_format($soldeNet, 0, ',', ' ') }} FCFA</strong> confirme la solidité
                de l'activité et la maîtrise des charges.
            </p>
            <p>
                Les revenus totaux de <strong>{{ number_format($totalRevenus, 0, ',', ' ') }} FCFA</strong>
                ont largement couvert les dépenses de <strong>{{ number_format($totalDepenses, 0, ',', ' ') }} FCFA</strong>,
                dégageant ainsi une marge de
                <strong>{{ $totalRevenus > 0 ? round($soldeNet / $totalRevenus * 100, 1) : 0 }}%</strong>.
            </p>
            <p>
                <strong>Recommandations :</strong> Maintenir la dynamique de croissance des revenus,
                optimiser les charges opérationnelles et consolider les performances des départements les plus contributeurs.
            </p>
            @else
            <p>
                L'entreprise <strong>EdenCorporate</strong> clôture l'exercice <strong>{{ $annee }}</strong> avec un bilan financier
                <strong style="color:#e11d48">déficitaire</strong>. Le déficit de
                <strong>{{ number_format(abs($soldeNet), 0, ',', ' ') }} FCFA</strong>
                nécessite une attention particulière et la mise en œuvre de mesures correctives.
            </p>
            <p>
                Les dépenses de <strong>{{ number_format($totalDepenses, 0, ',', ' ') }} FCFA</strong>
                ont excédé les revenus de <strong>{{ number_format($totalRevenus, 0, ',', ' ') }} FCFA</strong>.
            </p>
            <p>
                <strong>Recommandations :</strong> Identifier et réduire les postes de dépenses non essentiels,
                développer de nouvelles sources de revenus et revoir la stratégie budgétaire pour l'exercice suivant.
            </p>
            @endif
        </div>
    </div>

    {{-- ── Signatures ─────────────────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-title">Approbation du rapport</div>
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-role">Directeur Général</div>
                <div class="signature-line"></div>
                <div class="signature-name">Nom & Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-role">Directeur Financier</div>
                <div class="signature-line"></div>
                <div class="signature-name">Nom & Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-role">Directeur des Ressources Humaines</div>
                <div class="signature-line"></div>
                <div class="signature-name">Nom & Signature</div>
            </div>
        </div>
        <p style="font-size:9px; color:#94a3b8; margin-top:20px; text-align:center;">
            Document généré automatiquement par le Système SIRH EdenCorporate · {{ now()->locale('fr')->isoFormat('D MMMM YYYY') }} ·
            Ce document est confidentiel et destiné exclusivement aux dirigeants autorisés.
        </p>
    </div>

</div>

<script>
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.onload = () => setTimeout(() => window.print(), 400);
    }
</script>

</body>
</html>
