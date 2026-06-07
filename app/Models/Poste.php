<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Poste extends Model
{
    //
    protected $fillable = [
        'id',
        'nom',
        'description',
        'departement_id'
    ];
        

     public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    public $incrementing = false;
    protected $keyType = 'string';

}
