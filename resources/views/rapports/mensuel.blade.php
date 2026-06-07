<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Mensuel {{ $moisLabel }} — EdenCorporate</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; font-size:11px; color:#1e293b; background:#fff; }

        @media print {
            .no-print { display:none!important; }
            .page-break { page-break-before: always; }
        }

        /* ── Bouton impression ── */
        .print-bar {
            position:fixed; top:0; left:0; right:0; z-index:999;
            background:#760078; color:#fff; padding:10px 24px;
            display:flex; align-items:center; justify-content:space-between;
        }
        .print-bar span { font-size:12px; font-weight:700; }
        .print-bar button {
            padding:6px 18px; background:#fff; color:#760078;
            border:none; border-radius:6px; font-weight:700; font-size:11px; cursor:pointer;
        }

        /* ── Cover ── */
        .cover {
            min-height:100vh; display:flex; flex-direction:column;
            background:linear-gradient(135deg,#760078 0%,#4a0068 50%,#7677B7 100%);
            color:#fff; padding:60px 70px; page-break-after:always; position:relative; overflow:hidden;
        }
        .cover::before {
            content:''; position:absolute; bottom:-80px; right:-80px;
            width:320px; height:320px; border-radius:40px;
            border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04);
            transform:rotate(12deg);
        }
        .logo-text { font-size:24px; font-weight:800; letter-spacing:-0.5px; margin-bottom:auto; }
        .logo-text span { font-weight:300; opacity:0.9; }
        .cover-title { font-size:42px; font-weight:900; letter-spacing:-1px; line-height:1.1; margin:40px 0 12px; }
        .cover-sub { font-size:15px; opacity:0.75; }
        .cover-meta {
            display:flex; gap:40px; margin-top:auto; padding-top:40px;
            border-top:1px solid rgba(255,255,255,0.2);
        }
        .cover-meta-item label { font-size:9px; font-weight:700; opacity:0.6; letter-spacing:1.5px; text-transform:uppercase; display:block; margin-bottom:3px; }
        .cover-meta-item span { font-size:13px; font-weight:700; }

        /* ── Contenu ── */
        .content { padding:48px 56px; }
        h2 { font-size:16px; font-weight:800; margin-bottom:6px; color:#0f172a; }
        .section-sub { font-size:10px; color:#64748b; margin-bottom:20px; }

        /* ── KPI grid ── */
        .kpi-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:32px; }
        .kpi { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px 20px; }
        .kpi label { font-size:9px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#94a3b8; display:block; margin-bottom:6px; }
        .kpi .val { font-size:22px; font-weight:900; }
        .kpi .sub { font-size:9px; color:#94a3b8; margin-top:2px; }
        .green { color:#10b981; }
        .red   { color:#ef4444; }

        /* ── Table ── */
        table { width:100%; border-collapse:collapse; margin-bottom:28px; }
        thead tr { background:#f8fafc; border-bottom:2px solid #e2e8f0; }
        th { padding:9px 12px; text-align:left; font-size:9px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#64748b; }
        th.right, td.right { text-align:right; }
        tbody tr { border-bottom:1px solid #f1f5f9; }
        tbody tr:hover { background:#f8fafc; }
        td { padding:8px 12px; font-size:10px; color:#475569; }
        td.nom { font-weight:600; color:#0f172a; }
        td.green { color:#10b981; font-weight:700; }
        td.red   { color:#ef4444; font-weight:700; }

        /* ── Section break ── */
        .section { margin-bottom:36px; }
        .divider { border:none; border-top:2px solid #e2e8f0; margin:32px 0; }

        /* ── Footer ── */
        .footer {
            margin-top:40px; padding-top:16px; border-top:1px solid #e2e8f0;
            display:flex; justify-content:space-between; font-size:9px; color:#94a3b8;
        }

        /* ── Badge ── */
        .badge { display:inline-block; padding:2px 8px; border-radius:99px; font-size:9px; font-weight:700; }
        .badge-green { background:#d1fae5; color:#065f46; }
        .badge-red   { background:#fee2e2; color:#991b1b; }
        .badge-blue  { background:#dbeafe; color:#1e40af; }

        /* ── Progress bar ── */
        .progress { background:#e2e8f0; border-radius:99px; height:6px; overflow:hidden; margin-top:4px; }
        .progress-bar { height:6px; border-radius:99px; background:linear-gradient(90deg,#760078,#7677B7); }
    </style>
</head>
<body>

<!-- Barre impression -->
<div class="print-bar no-print">
    <span>Rapport Mensuel · {{ $moisLabel }}</span>
    <button onclick="window.print()">⬇ Télécharger / Imprimer</button>
</div>

<!-- ── COUVERTURE ─────────────────────────────────────── -->
<div class="cover" style="margin-top:36px;">
    <div class="logo-text">Eden<span>Corporate</span></div>
    <div class="cover-title">Rapport<br>Mensuel</div>
    <div class="cover-sub">{{ ucfirst($moisLabel) }}</div>
    <div class="cover-meta">
        <div class="cover-meta-item">
            <label>Période</label>
            <span>{{ ucfirst($moisLabel) }}</span>
        </div>
        <div class="cover-meta-item">
            <label>Généré le</label>
            <span>{{ now()->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
        </div>
        <div class="cover-meta-item">
            <label>Transactions</label>
            <span>{{ $transactions->count() }}</span>
        </div>
    </div>
</div>

<!-- ── CONTENU ──────────────────────────────────────────── -->
<div class="content">

    <!-- KPIs -->
    <div class="section">
        <h2>Synthèse financière</h2>
        <p class="section-sub">{{ ucfirst($moisLabel) }} — {{ $transactions->count() }} transaction(s)</p>

        <div class="kpi-grid">
            <div class="kpi">
                <label>Revenus</label>
                <div class="val green">{{ number_format($totalRevenus, 0, ',', ' ') }} FCFA</div>
                <div class="sub">Entrées de trésorerie</div>
            </div>
            <div class="kpi">
                <label>Dépenses</label>
                <div class="val red">{{ number_format($totalDepenses, 0, ',', ' ') }} FCFA</div>
                <div class="sub">Sorties de trésorerie</div>
            </div>
            <div class="kpi">
                <label>Solde net</label>
                <div class="val {{ $soldeNet >= 0 ? 'green' : 'red' }}">{{ number_format($soldeNet, 0, ',', ' ') }} FCFA</div>
                <div class="sub">Revenus − Dépenses</div>
            </div>
            @if($masseSalariale > 0)
            <div class="kpi">
                <label>Masse salariale</label>
                <div class="val" style="color:#760078">{{ number_format($masseSalariale, 0, ',', ' ') }} FCFA</div>
                <div class="sub">Bulletins payés ce mois</div>
            </div>
            @endif
        </div>
    </div>

    <hr class="divider">

    <!-- Par département -->
    @if($parDept->isNotEmpty())
    <div class="section">
        <h2>Répartition par département</h2>
        <p class="section-sub">Synthèse des mouvements financiers par département</p>
        <table>
            <thead>
                <tr>
                    <th>Département</th>
                    <th class="right">Revenus</th>
                    <th class="right">Dépenses</th>
                    <th class="right">Solde</th>
                    <th class="right">Nb.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parDept as $d)
                <tr>
                    <td class="nom">{{ $d['nom'] }}</td>
                    <td class="right green">{{ number_format($d['revenus'], 0, ',', ' ') }}</td>
                    <td class="right red">{{ number_format($d['depenses'], 0, ',', ' ') }}</td>
                    <td class="right {{ $d['solde'] >= 0 ? 'green' : 'red' }}">{{ number_format($d['solde'], 0, ',', ' ') }}</td>
                    <td class="right">{{ $d['nb'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Par catégorie -->
    @if($parCategorie->isNotEmpty())
    <div class="section">
        <h2>Dépenses par catégorie</h2>
        <p class="section-sub">Répartition des sorties de trésorerie</p>
        <table>
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th class="right">Montant</th>
                    <th class="right">% du total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parCategorie as $c)
                <tr>
                    <td class="nom">{{ $c['categorie'] }}</td>
                    <td class="right red">{{ number_format($c['montant'], 0, ',', ' ') }} FCFA</td>
                    <td class="right">
                        @php $pct = $totalDepenses > 0 ? round($c['montant'] / $totalDepenses * 100, 1) : 0 @endphp
                        {{ $pct }}%
                        <div class="progress"><div class="progress-bar" style="width:{{ $pct }}%"></div></div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Détail transactions -->
    @if($transactions->isNotEmpty())
    <div class="section page-break">
        <h2>Détail des transactions</h2>
        <p class="section-sub">{{ $transactions->count() }} opération(s) ce mois</p>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Département</th>
                    <th>Catégorie</th>
                    <th>Type</th>
                    <th class="right">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $t)
                <tr>
                    <td style="white-space:nowrap">{{ $t->date_transaction?->format('d/m/Y') }}</td>
                    <td>{{ Str::limit($t->description, 50) }}</td>
                    <td>{{ $t->departement?->nom ?? '—' }}</td>
                    <td>{{ $t->categorie ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $t->type === 'revenu' ? 'badge-green' : 'badge-red' }}">
                            {{ $t->type === 'revenu' ? '↑ Entrée' : '↓ Sortie' }}
                        </span>
                    </td>
                    <td class="right {{ $t->type === 'revenu' ? 'green' : 'red' }}" style="font-weight:700">
                        {{ $t->type === 'revenu' ? '+' : '-' }}{{ number_format($t->montant, 0, ',', ' ') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <span>EdenCorporate · Rapport mensuel {{ $moisLabel }}</span>
        <span>Généré le {{ now()->locale('fr')->isoFormat('D MMMM YYYY à HH:mm') }}</span>
    </div>

</div>
</body>
</html>
