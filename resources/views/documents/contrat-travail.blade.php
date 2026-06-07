<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat de travail — {{ $user->nom }} {{ $user->prenom }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; padding: 30px 40px; }
        .page { max-width: 800px; margin: 0 auto; }

        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 2px solid #760078; margin-bottom: 20px; }
        .logo { height: 52px; width: auto; object-fit: contain; }
        .company-sub  { font-size: 9px; color: #94a3b8; margin-top: 3px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 16px; font-weight: 900; color: #1e293b; text-transform: uppercase; letter-spacing: 1px; }
        .doc-title .ref { font-size: 9px; color: #94a3b8; margin-top: 4px; }

        .main-title { text-align: center; margin: 24px 0 20px; }
        .main-title h2 { font-size: 15px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; color: #1e293b; border-bottom: 2px solid #760078; display: inline-block; padding-bottom: 6px; }
        .main-title .type { font-size: 11px; color: #64748b; margin-top: 6px; }

        .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .partie-box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .partie-box h3 { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #760078; margin-bottom: 8px; }
        .partie-box p { font-size: 11px; color: #334155; line-height: 1.6; }
        .partie-box strong { color: #1e293b; }

        .section { margin-bottom: 16px; }
        .section h3 { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #760078; background: #faf5ff; padding: 6px 10px; border-left: 3px solid #760078; margin-bottom: 10px; }
        .section p { font-size: 11px; color: #334155; line-height: 1.8; margin-bottom: 6px; padding: 0 10px; }
        .section .highlight { font-weight: 700; color: #1e293b; }

        .table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10.5px; }
        .table th { background: #f8fafc; padding: 7px 10px; text-align: left; font-weight: 700; border: 1px solid #e2e8f0; color: #64748b; font-size: 9px; text-transform: uppercase; }
        .table td { padding: 7px 10px; border: 1px solid #e2e8f0; color: #334155; }
        .table tr:nth-child(even) td { background: #fafafa; }

        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        .sig-block p { font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 4px; }
        .sig-block .mention { font-size: 9px; color: #94a3b8; margin-bottom: 40px; }
        .sig-line { border-top: 1px dashed #cbd5e1; padding-top: 4px; }
        .sig-line span { font-size: 9px; color: #94a3b8; }

        .footer { text-align: center; margin-top: 20px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }

        @media print {
            body { padding: 15px 20px; }
            @page { size: A4; margin: 1.5cm; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div>
            <img src="{{ asset('images/logo.svg') }}" alt="Eden Corpor@te" class="logo" />
            <div class="company-sub">Abidjan, Côte d'Ivoire · Direction des Ressources Humaines</div>
        </div>
        <div class="doc-title">
            <h1>Contrat de Travail</h1>
            <div class="ref">Réf. CT-{{ strtoupper(substr($user->id, 0, 8)) }} · {{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="main-title">
        <h2>Contrat de Travail à {{ $user->type_contrat === 'employe' ? 'Durée Indéterminée (CDI)' : 'Prestation de Service' }}</h2>
        <div class="type">Entre les soussignés</div>
    </div>

    <div class="parties">
        <div class="partie-box">
            <h3>L'Employeur</h3>
            <p>
                <strong>Eden Corpor@te</strong><br>
                Société immatriculée en Côte d'Ivoire<br>
                Siège social : Abidjan, Côte d'Ivoire<br>
                Représentée par la Direction Générale<br>
                <em>Ci-après dénommé « L'Employeur »</em>
            </p>
        </div>
        <div class="partie-box">
            <h3>L'Employé</h3>
            <p>
                <strong>{{ $user->nom }} {{ $user->prenom }}</strong><br>
                @if($user->date_naissance) Né(e) le : {{ $user->date_naissance->format('d/m/Y') }}<br>@endif
                @if($user->lieu_habitation) Domicile : {{ $user->lieu_habitation }}<br>@endif
                Email : {{ $user->email }}<br>
                @if($user->telephone) Tél. : {{ $user->telephone }}<br>@endif
                <em>Ci-après dénommé(e) « Le Salarié »</em>
            </p>
        </div>
    </div>

    <div class="section">
        <h3>Article 1 — Engagement et prise de fonction</h3>
        <p>
            L'Employeur engage le Salarié <span class="highlight">{{ $user->nom }} {{ $user->prenom }}</span>
            à compter du <span class="highlight">{{ $user->date_embauche ? $user->date_embauche->format('d/m/Y') : '___/___/______' }}</span>
            pour occuper le poste de <span class="highlight">{{ $poste ?? 'à définir' }}</span>
            au sein du département <span class="highlight">{{ $departement ?? 'à définir' }}</span>.
        </p>
        @if($user->type_contrat !== 'employe' && $user->date_fin_contrat)
        <p>Le présent contrat prend fin le <span class="highlight">{{ $user->date_fin_contrat->format('d/m/Y') }}</span>.</p>
        @endif
    </div>

    <div class="section">
        <h3>Article 2 — Rémunération</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Élément de rémunération</th>
                    <th>Montant mensuel (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Salaire de base</td><td>{{ number_format($user->salaire_base, 0, ',', ' ') }} FCFA</td></tr>
                @if($user->prime_transport > 0)<tr><td>Prime de transport</td><td>{{ number_format($user->prime_transport, 0, ',', ' ') }} FCFA</td></tr>@endif
                @if($user->prime_logement > 0)<tr><td>Prime de logement</td><td>{{ number_format($user->prime_logement, 0, ',', ' ') }} FCFA</td></tr>@endif
                @if($user->prime_fonction > 0)<tr><td>Prime de fonction</td><td>{{ number_format($user->prime_fonction, 0, ',', ' ') }} FCFA</td></tr>@endif
                @if($user->prime_rendement > 0)<tr><td>Prime de rendement</td><td>{{ number_format($user->prime_rendement, 0, ',', ' ') }} FCFA</td></tr>@endif
                @if($user->prime_panier > 0)<tr><td>Prime de panier</td><td>{{ number_format($user->prime_panier, 0, ',', ' ') }} FCFA</td></tr>@endif
                @if($user->bonus_annuel > 0)<tr><td>Bonus annuel (mensualisé)</td><td>{{ number_format($user->bonus_annuel / 12, 0, ',', ' ') }} FCFA</td></tr>@endif
            </tbody>
        </table>
        <p>La rémunération sera versée par <span class="highlight">{{ $user->mode_paiement === 'fixe' ? 'virement bancaire mensuel' : 'paiement par prestation' }}</span>.</p>
    </div>

    <div class="section">
        <h3>Article 3 — Durée du travail</h3>
        <p>
            La durée légale du travail est fixée à <span class="highlight">40 heures par semaine</span> conformément
            au Code du Travail ivoirien. Les heures supplémentaires éventuelles seront rémunérées selon la grille en vigueur.
        </p>
    </div>

    <div class="section">
        <h3>Article 4 — Congés annuels</h3>
        <p>
            Le Salarié bénéficie d'un droit à congé payé de <span class="highlight">30 jours ouvrables par an</span>
            (2,5 jours par mois travaillé) conformément à la législation ivoirienne en vigueur.
        </p>
    </div>

    <div class="section">
        <h3>Article 5 — Protection sociale</h3>
        <p>
            Le Salarié bénéficie de l'affiliation à la CNPS (Caisse Nationale de Prévoyance Sociale)
            @if($user->cnps) — <span class="highlight">Affilié(e)</span>@endif.
            @if($user->assurance_maladie) Une assurance maladie complémentaire est prise en charge par l'Employeur.@endif
        </p>
    </div>

    <div class="section">
        <h3>Article 6 — Obligations des parties</h3>
        <p>
            Le Salarié s'engage à exercer ses fonctions avec diligence et loyauté, à respecter
            le règlement intérieur de l'entreprise et à observer la confidentialité des informations
            auxquelles il/elle aura accès dans le cadre de ses fonctions.
        </p>
        <p>
            L'Employeur s'engage à assurer des conditions de travail saines et sécurisées,
            à verser la rémunération aux échéances convenues et à respecter les droits du Salarié
            conformément à la législation ivoirienne.
        </p>
    </div>

    <div class="section">
        <h3>Article 7 — Droit applicable</h3>
        <p>
            Le présent contrat est régi par le Code du Travail de la République de Côte d'Ivoire
            et la Convention Collective Interprofessionnelle applicable. Tout litige sera soumis
            à la compétence des juridictions ivoiriennes.
        </p>
    </div>

    <div class="signatures">
        <div class="sig-block">
            <p>Pour l'Employeur</p>
            <div class="mention">Eden Corpor@te — Direction Générale</div>
            <div class="sig-line"><span>Nom, Signature & Cachet</span></div>
        </div>
        <div class="sig-block">
            <p>Le Salarié</p>
            <div class="mention">{{ $user->nom }} {{ $user->prenom }} — Lu et approuvé</div>
            <div class="sig-line"><span>Nom & Signature</span></div>
        </div>
    </div>

    <div class="footer">
        Contrat généré le {{ now()->format('d/m/Y à H:i') }} par le Système SIRH Eden Corpor@te · Confidentiel
    </div>

</div>
<script>window.onload = () => window.print();</script>
</body>
</html>
