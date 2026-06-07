<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestation de travail — {{ $user->nom }} {{ $user->prenom }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; padding: 40px 60px; }
        .page { max-width: 720px; margin: 0 auto; }

        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 2px solid #760078; margin-bottom: 30px; }
        .logo { height: 52px; width: auto; object-fit: contain; }
        .company-sub  { font-size: 9px; color: #94a3b8; margin-top: 3px; }
        .doc-ref { text-align: right; font-size: 9px; color: #94a3b8; }

        .main-title { text-align: center; margin-bottom: 40px; }
        .main-title h1 { font-size: 18px; font-weight: 900; text-transform: uppercase; letter-spacing: 3px; color: #1e293b; display: inline-block; border-bottom: 2px solid #760078; padding-bottom: 8px; margin-bottom: 6px; }
        .main-title .sous { font-size: 10px; color: #64748b; font-style: italic; }

        .body-text { font-size: 12px; line-height: 2; color: #334155; margin-bottom: 16px; }
        .body-text .highlight { font-weight: 700; color: #1e293b; font-size: 13px; }

        .info-box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 20px; margin: 20px 0; background: #f8fafc; }
        .info-row { display: flex; gap: 10px; padding: 4px 0; font-size: 11px; }
        .info-label { width: 180px; color: #64748b; flex-shrink: 0; }
        .info-value { font-weight: 600; color: #1e293b; }

        .purpose { margin: 30px 0; font-style: italic; color: #64748b; font-size: 11px; text-align: center; border: 1px dashed #cbd5e1; padding: 12px; border-radius: 6px; }

        .signatures { display: flex; justify-content: flex-end; margin-top: 50px; }
        .sig-block { text-align: center; }
        .sig-block p { font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 4px; }
        .sig-block .date { font-size: 10px; color: #94a3b8; margin-bottom: 50px; }
        .sig-line { border-top: 1px dashed #cbd5e1; padding-top: 4px; }
        .sig-line span { font-size: 9px; color: #94a3b8; }

        .footer { text-align: center; margin-top: 30px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }

        @media print { body { padding: 20px 30px; } @page { size: A4; margin: 2cm; } }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div>
            <img src="{{ asset('images/logo.svg') }}" alt="Eden Corpor@te" class="logo" />
            <div class="company-sub">Abidjan, Côte d'Ivoire · Direction des Ressources Humaines</div>
        </div>
        <div class="doc-ref">
            Réf. ATT-{{ strtoupper(substr($user->id, 0, 8)) }}<br>
            Abidjan, le {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <div class="main-title">
        <h1>Attestation de Travail</h1>
        <div class="sous">Délivrée à titre officiel par la Direction des Ressources Humaines</div>
    </div>

    <p class="body-text">
        La Direction des Ressources Humaines de la société <span class="highlight">Eden Corpor@te</span>,
        sise à Abidjan, Côte d'Ivoire, atteste par la présente que :
    </p>

    <p class="body-text" style="text-align:center; font-size: 14px; font-weight: 900; color: #760078; margin: 20px 0;">
        Monsieur / Madame {{ $user->nom }} {{ $user->prenom }}
    </p>

    <p class="body-text">
        est / a été employé(e) au sein de notre société en qualité de
        <span class="highlight">{{ $poste ?? 'Collaborateur(trice)' }}</span>
        au sein du département <span class="highlight">{{ $departement ?? 'à définir' }}</span>,
        @if($user->date_embauche)
        depuis le <span class="highlight">{{ $user->date_embauche->format('d/m/Y') }}</span>
        @endif
        @if($user->type_contrat === 'employe') dans le cadre d'un Contrat à Durée Indéterminée (CDI).
        @else dans le cadre d'un contrat de prestation de service.
        @endif
    </p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nom et Prénom :</span>
            <span class="info-value">{{ $user->nom }} {{ $user->prenom }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Poste occupé :</span>
            <span class="info-value">{{ $poste ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Département :</span>
            <span class="info-value">{{ $departement ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date d'entrée :</span>
            <span class="info-value">{{ $user->date_embauche ? $user->date_embauche->format('d/m/Y') : '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Type de contrat :</span>
            <span class="info-value">{{ $user->type_contrat === 'employe' ? 'CDI' : 'Prestation de service' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Salaire de base :</span>
            <span class="info-value">{{ number_format($user->salaire_base, 0, ',', ' ') }} FCFA / mois</span>
        </div>
        <div class="info-row">
            <span class="info-label">Affiliation CNPS :</span>
            <span class="info-value">{{ $user->cnps ? 'Oui — Affilié(e)' : 'Non' }}</span>
        </div>
    </div>

    <p class="body-text">
        La présente attestation est délivrée à l'intéressé(e) à sa demande et pour servir
        et valoir ce que de droit.
    </p>

    <div class="purpose">
        Cette attestation ne peut être utilisée qu'à des fins administratives légitimes.
        Elle est valable 3 mois à compter de sa date de délivrance.
    </div>

    <div class="signatures">
        <div class="sig-block">
            <p>Pour la Direction des Ressources Humaines</p>
            <p>Eden Corpor@te</p>
            <div class="date">Abidjan, le {{ now()->format('d/m/Y') }}</div>
            <div class="sig-line"><span>Signature & Cachet de l'entreprise</span></div>
        </div>
    </div>

    <div class="footer">
        Attestation générée le {{ now()->format('d/m/Y à H:i') }} — Système SIRH Eden Corpor@te · Document officiel
    </div>

</div>
<script>window.onload = () => window.print();</script>
</body>
</html>
