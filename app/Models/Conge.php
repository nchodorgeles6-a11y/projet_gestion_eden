<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conge extends Model
{
     protected $fillable = [
        'id',
        'user_id',
        'motif_id',
        'date_debut',
        'date_fin',
        'statut',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function motif()
    {
        return $this->belongsTo(Motif::class);
    }

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    public $incrementing = false;
    protected $keyType = 'string';

}
