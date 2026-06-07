<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [

        'id',
        'user_id',
        'montant',
        'mois',
        'annee',
        'reference',
        'meta'

    ];

    protected $casts = [

        'meta' => 'array',

    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}