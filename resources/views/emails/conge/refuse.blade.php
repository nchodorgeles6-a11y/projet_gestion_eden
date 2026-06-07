<x-mail::message>
# Bonjour {{ $employe?->prenom }},

Votre demande de congé a été **refusée**.

<x-mail::panel>
**Période demandée :** {{ $dateDebut }} → {{ $dateFin }}
**Motif :** {{ $motif }}
</x-mail::panel>

Pour toute question, rapprochez-vous du service RH.

Cordialement,
**L'équipe RH — EdenCorporate**
</x-mail::message>
