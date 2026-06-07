<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasUuids;

    protected $fillable = [
        'numero',
        'fournisseur',
        'description',
        'montant_ht',
        'tva',
        'montant_ttc',
        'date_facture',
        'date_echeance',
        'statut',
        'categorie',
        'departement_id',
    ];

    protected function casts(): array
    {
        return [
            'montant_ht'    => 'decimal:2',
            'tva'           => 'decimal:2',
            'montant_ttc'   => 'decimal:2',
            'date_facture'  => 'date',
            'date_echeance' => 'date',
        ];
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }
}
