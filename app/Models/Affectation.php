<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Affectation extends Model
{
     protected $fillable = [
        'id',
        'user_id',
        'poste_id',
        'date_debut',
        'date_fin',
        'motif_changement',
        'salaire_capture',
        'primes_capture',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class);
    }

    protected function casts(): array
    {
        return [
            'date_debut'     => 'date',
            'date_fin'       => 'date',
            'primes_capture' => 'array',
        ];
    }

    public $incrementing = false;
    protected $keyType = 'string';

}
