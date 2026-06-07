<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasUuids;

    protected $fillable = [
        'departement_id',
        'annee',
        'mois',
        'categorie',
        'montant',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'mois'    => 'integer',
            'annee'   => 'integer',
        ];
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }
}
