<x-mail::message>
# Bonne nouvelle, {{ $employe?->prenom }} !

Votre demande de congé a été **approuvée**.

<x-mail::panel>
**Période :** {{ $dateDebut }} → {{ $dateFin }}
**Motif :** {{ $motif }}
</x-mail::panel>

Profitez bien de votre congé.

Cordialement,
**L'équipe RH — EdenCorporate**
</x-mail::message>
