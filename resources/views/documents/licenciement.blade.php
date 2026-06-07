<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lettre de licenciement — {{ $user->nom }} {{ $user->prenom }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; padding: 40px 60px; }
        .page { max-width: 720px; margin: 0 auto; }

        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 2px solid #760078; margin-bottom: 30px; }
        .logo { height: 52px; width: auto; object-fit: contain; }
        .company-sub  { font-size: 9px; color: #94a3b8; margin-top: 3px; }

        .destinataire { margin-bottom: 30px; }
        .destinataire p { font-size: 11px; color: #334155; line-height: 1.8; }
        .destinataire strong { font-size: 12px; color: #1e293b; }

        .ref-date { text-align: right; font-size: 10px; color: #64748b; margin-bottom: 20px; }

        .objet { font-size: 11px; margin-bottom: 25px; }
        .objet span { font-weight: 700; color: #1e293b; }

        .body-text { font-size: 12px; line-height: 2; color: #334155; margin-bottom: 14px; text-align: justify; }
        .body-text .highlight { font-weight: 700; color: #1e293b; }

        .motif-box { border-left: 3px solid #ef4444; padding: 12px 16px; margin: 16px 0; background: #fef2f2; border-radius: 0 6px 6px 0; }
        .motif-box p { font-size: 11px; line-height: 1.8; color: #334155; }

        .preavis { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin: 20px 0; }
        .preavis h4 { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #760078; margin-bottom: 8px; }
        .preavis p { font-size: 11px; line-height: 1.7; color: #334155; }

        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 50px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        .sig-block p { font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 4px; }
        .sig-block .mention { font-size: 9px; color: #94a3b8; margin-bottom: 50px; }
        .sig-line { border-top: 1px dashed #cbd5e1; padding-top: 4px; }
        .sig-line span { font-size: 9px; color: #94a3b8; }

        .footer { text-align: center; margin-top: 20px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
        .warning { font-size: 10px; color: #ef4444; font-style: italic; text-align: center; margin-top: 16px; }

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
    </div>

    <div class="ref-date">
        Réf. LIC-{{ strtoupper(substr($user->id, 0, 8)) }}<br>
        Abidjan, le {{ now()->format('d/m/Y') }}
    </div>

    <div class="destinataire">
        <p>
            <strong>{{ $user->nom }} {{ $user->prenom }}</strong><br>
            @if($user->lieu_habitation){{ $user->lieu_habitation }}<br>@endif
            @if($user->email){{ $user->email }}<br>@endif
            @if($user->telephone)Tél. : {{ $user->telephone }}@endif
        </p>
    </div>

    <p class="objet">
        <span>Objet :</span> Notification de licenciement
    </p>

    <p class="body-text">
        Monsieur / Madame <span class="highlight">{{ $user->nom }} {{ $user->prenom }}</span>,
    </p>

    <p class="body-text">
        Nous avons l'honneur de vous informer que la Direction de la société <span class="highlight">Eden Corpor@te</span>
        a pris la décision de mettre fin à votre contrat de travail,
        @if($user->type_contrat === 'employe') à durée indéterminée (CDI),
        @else de prestation de service,
        @endif
        que vous occupez en qualité de <span class="highlight">{{ $poste ?? 'Collaborateur(trice)' }}</span>
        au sein du département <span class="highlight">{{ $departement ?? '' }}</span>
        depuis le <span class="highlight">{{ $user->date_embauche ? $user->date_embauche->format('d/m/Y') : '___/___/______' }}</span>.
    </p>

    <p class="body-text">Cette décision est motivée par les raisons suivantes :</p>

    <div class="motif-box">
        <p>
            <em>[ Motif à préciser par la Direction : faute grave / insuffisance professionnelle / motif économique / restructuration / fin de mission... ]</em>
        </p>
        <p style="margin-top: 8px;">
            ______________________________________________________________________<br>
            ______________________________________________________________________<br>
            ______________________________________________________________________
        </p>
    </div>

    <div class="preavis">
        <h4>Conditions de départ</h4>
        <p>
            Conformément aux dispositions du Code du Travail ivoirien, un préavis de
            <strong>[ durée à compléter ]</strong> vous est accordé à compter de la réception de la présente.
            @if($user->date_fin_contrat)
            Votre dernier jour de travail effectif est fixé au <strong>{{ $user->date_fin_contrat->format('d/m/Y') }}</strong>.
            @else
            Votre dernier jour de travail effectif sera précisé ultérieurement.
            @endif
        </p>
        <p style="margin-top: 8px;">
            Les documents de fin de contrat (certificat de travail, solde de tout compte, attestation Pôle Emploi)
            vous seront remis à la date de votre départ.
        </p>
    </div>

    <p class="body-text">
        Nous vous remercions pour votre contribution au sein d'Eden Corpor@te et vous souhaitons
        une bonne continuation dans vos projets professionnels.
    </p>

    <p class="body-text">
        Veuillez agréer, Monsieur / Madame, l'expression de nos salutations distinguées.
    </p>

    <div class="signatures">
        <div class="sig-block">
            <p>Pour la Direction</p>
            <p>Eden Corpor@te</p>
            <div class="mention">Signature & Cachet</div>
            <div class="sig-line"><span>Nom et qualité du signataire</span></div>
        </div>
        <div class="sig-block">
            <p>Accusé de réception</p>
            <div class="mention">{{ $user->nom }} {{ $user->prenom }} — Lu et pris connaissance</div>
            <div class="sig-line"><span>Signature + mention manuscrite</span></div>
        </div>
    </div>

    <p class="warning">⚠ Document à compléter et signer avant remise au salarié — Confidentiel RH</p>

    <div class="footer">
        Généré le {{ now()->format('d/m/Y à H:i') }} — Système SIRH Eden Corpor@te
    </div>

</div>
<script>window.onload = () => window.print();</script>
</body>
</html>
