<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'id',
        'departement_id',
        'user_id',
        'type',
        'categorie',
        'montant',
        'date_transaction',
        'source',
        'description',
        'meta',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'meta'             => 'array',
            'date_transaction' => 'date',
            'montant'          => 'decimal:2',
        ];
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
