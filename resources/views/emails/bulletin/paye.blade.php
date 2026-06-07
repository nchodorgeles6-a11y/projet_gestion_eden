<x-mail::message>
# Bulletin de paie disponible

Bonjour {{ $employe?->prenom }},

Votre bulletin de paie pour **{{ $bulletin->mois }} {{ $bulletin->annee }}** a été marqué comme payé.

<x-mail::panel>
**Référence :** {{ $bulletin->reference }}
**Net à payer :** {{ $netAPayer }} FCFA
**Mode :** {{ ['virement'=>'Virement bancaire','especes'=>'Espèces','mobile_money'=>'Mobile Money','cheque'=>'Chèque'][$bulletin->mode_paiement] ?? $bulletin->mode_paiement }}
</x-mail::panel>

Connectez-vous à l'application pour consulter votre bulletin complet.

Cordialement,
**L'équipe RH — EdenCorporate**
</x-mail::message>
