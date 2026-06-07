<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lettre de mise en congé — {{ $conge->user->nom }} {{ $conge->user->prenom }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; padding: 40px 60px; }
        .page { max-width: 720px; margin: 0 auto; }

        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 2px solid #760078; margin-bottom: 30px; }
        .logo { height: 52px; width: auto; object-fit: contain; }
        .company-sub  { font-size: 9px; color: #94a3b8; margin-top: 3px; }

        .ref-date { text-align: right; font-size: 10px; color: #64748b; margin-bottom: 20px; }

        .destinataire { margin-bottom: 30px; }
        .destinataire p { font-size: 11px; color: #334155; line-height: 1.8; }

        .objet { font-size: 11px; margin-bottom: 25px; }
        .objet span { font-weight: 700; color: #1e293b; }

        .body-text { font-size: 12px; line-height: 2; color: #334155; margin-bottom: 14px; text-align: justify; }
        .body-text .highlight { font-weight: 700; color: #1e293b; }

        .conge-box { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 20px 0; }
        .conge-item { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; background: #f8fafc; }
        .conge-item label { display: block; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 4px; }
        .conge-item .value { font-size: 13px; font-weight: 700; color: #760078; }

        .solde-box { border: 1px solid #d1fae5; border-radius: 8px; padding: 12px 16px; background: #f0fdf4; margin: 16px 0; }
        .solde-box p { font-size: 11px; color: #15803d; }
        .solde-box strong { color: #15803d; }

        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 50px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        .sig-block p { font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 4px; }
        .sig-block .mention { font-size: 9px; color: #94a3b8; margin-bottom: 50px; }
        .sig-line { border-top: 1px dashed #cbd5e1; padding-top: 4px; }
        .sig-line span { font-size: 9px; color: #94a3b8; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; background: #d1fae5; color: #15803d; margin-left: 8px; }

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
    </div>

    <div class="ref-date">
        Réf. CNG-{{ strtoupper(substr($conge->id, 0, 8)) }}<br>
        Abidjan, le {{ now()->format('d/m/Y') }}
    </div>

    <div class="destinataire">
        <p>
            <strong>{{ $conge->user->nom }} {{ $conge->user->prenom }}</strong><br>
            {{ $poste ?? '' }}{{ $departement ? ' — ' . $departement : '' }}<br>
            @if($conge->user->email){{ $conge->user->email }}<br>@endif
        </p>
    </div>

    <p class="objet">
        <span>Objet :</span> Autorisation de mise en congé
        @if($conge->motif)<span class="badge">{{ $conge->motif->nom }}</span>@endif
    </p>

    <p class="body-text">
        Monsieur / Madame <span class="highlight">{{ $conge->user->nom }} {{ $conge->user->prenom }}</span>,
    </p>

    <p class="body-text">
        Nous avons bien pris note de votre demande de congé
        @if($conge->motif) pour motif : <span class="highlight">{{ $conge->motif->nom }}</span>@endif.
        Après examen de votre dossier, nous avons le plaisir de vous informer que votre
        demande a été <span class="highlight">approuvée</span> par la Direction des Ressources Humaines.
    </p>

    <div class="conge-box">
        <div class="conge-item">
            <label>Date de début</label>
            <div class="value">{{ $conge->date_debut->format('d/m/Y') }}</div>
        </div>
        <div class="conge-item">
            <label>Date de fin</label>
            <div class="value">{{ $conge->date_fin ? $conge->date_fin->format('d/m/Y') : 'Non définie' }}</div>
        </div>
        <div class="conge-item">
            <label>Durée</label>
            <div class="value">
                @if($conge->date_fin)
                    {{ $conge->date_debut->diffInDays($conge->date_fin) + 1 }} jour(s)
                @else
                    À préciser
                @endif
            </div>
        </div>
        <div class="conge-item">
            <label>Statut</label>
            <div class="value" style="color: #15803d;">Approuvé ✓</div>
        </div>
    </div>

    @if($solde)
    <div class="solde-box">
        <p>
            <strong>Solde de congés {{ now()->year }} :</strong>
            {{ $solde['utilises'] }} jour(s) utilisé(s) sur {{ $solde['droit'] }} —
            <strong>{{ $solde['restant'] }} jour(s) restant(s)</strong> après cette période.
        </p>
    </div>
    @endif

    <p class="body-text">
        Vous voudrez bien prendre toutes les dispositions nécessaires pour assurer la continuité
        de votre service avant votre départ. Nous vous demandons également de vous présenter à
        la reprise à la date prévue, sauf empêchement majeur que vous nous signalerez dans
        les meilleurs délais.
    </p>

    <p class="body-text">
        Nous vous souhaitons un excellent repos et vous attendons à votre retour.
    </p>

    <p class="body-text">
        Veuillez agréer, Monsieur / Madame, l'expression de nos cordiales salutations.
    </p>

    <div class="signatures">
        <div class="sig-block">
            <p>Pour la Direction RH</p>
            <p>Eden Corpor@te</p>
            <div class="mention">Abidjan, le {{ now()->format('d/m/Y') }}</div>
            <div class="sig-line"><span>Nom, Signature & Cachet</span></div>
        </div>
        <div class="sig-block">
            <p>Accusé de réception</p>
            <div class="mention">{{ $conge->user->nom }} {{ $conge->user->prenom }}</div>
            <div class="sig-line"><span>Signature du salarié</span></div>
        </div>
    </div>

    <div class="footer">
        Généré le {{ now()->format('d/m/Y à H:i') }} — Système SIRH Eden Corpor@te · Réf. {{ strtoupper(substr($conge->id, 0, 8)) }}
    </div>

</div>
<script>window.onload = () => window.print();</script>
</body>
</html>
