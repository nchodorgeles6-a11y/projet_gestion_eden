<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departement extends Model
{
    //
    protected $fillable = [
        'id',
        'nom'
    ];

     public function postes()
    {
        return $this->hasMany(Poste::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public $incrementing = false;
    protected $keyType = 'string';


}
