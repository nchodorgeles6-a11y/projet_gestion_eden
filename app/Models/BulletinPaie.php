<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulletinPaie extends Model
{
    protected $table = 'bulletins_paie';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'reference', 'user_id', 'mois', 'annee',
        'salaire_base', 'prime_transport', 'prime_logement', 'prime_fonction',
        'prime_rendement', 'prime_panier', 'bonus_annuel',
        'heures_sup', 'taux_heures_sup', 'avantages_nature_montant', 'salaire_brut',
        'cnps_salarie', 'assurance_maladie_salarie', 'igr', 'is_salaire',
        'avance_salaire', 'pret_entreprise', 'autres_retenues', 'total_retenues', 'net_a_payer',
        'cnps_employeur', 'accident_travail', 'prestations_familiales', 'formation_professionnelle', 'cout_total_employeur',
        'mode_paiement', 'date_paiement', 'statut',
    ];

    protected $casts = [
        'date_paiement' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
